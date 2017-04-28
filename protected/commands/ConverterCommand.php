<?php

/**
 * Конвертация других систем в SamoletCMS.
 *
 * @package    converter
 * @subpackage console
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ConverterCommand extends CConsoleCommand
{
    /**
     * Начальное время.
     *
     * @var integer
     */
    private $startTime = 0;

    /**
     * Справка.
     *
     * @see CConsoleCommand::getHelp()
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
    yiic converter [action] [parameter]

DESCRIPTION
    Converter console launcher.

EXAMPLES
    * yiic converter all
        Convert all entities.

        Parameters:
            - writeFiles [*|news,persons,players]

    * yiic converter news
        Convert news.

        Parameters:
            - writeFiles

    * yiic converter persons
        Convert persons.

    * yiic converter players
        Convert players, teams, contracts, stats.

        Parameters:
            - writeFiles

    * yiic convert champs
        Convert seasons, championships and stages.

    * yiic converter matches
        Convert matches (inc. events, placments).

    * yiic converter files
        Convert files.

    * yiic converter branches.
        Convert branches.

EOD;
    }

    /**
     * Конвертация всего.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionAll($writeFiles = false)
    {
        if ('*' == $writeFiles || 1 == $writeFiles) {
            $writeFiles = true;
        } else {
            $writeFiles = explode(',', $writeFiles);
        }

        /*print "Action 'champs'.\n";
        $start = microtime(true);
        $this->actionChamps();
        $this->showTime($start);
        print "\n";

        print "Action 'persons'.\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('persons', $writeFiles);
        $this->actionPersons($writeFiles);
        $this->showTime($start);
        print "\n";*/

        print "Action 'players'\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('players', $writeFiles);
        $this->actionPlayers($writeFiles);
        $this->showTime($start);
        print "\n";

        print "Action 'matches'.\n";
        $start = microtime(true);
        $this->actionMatches();
        $this->showTime($start);
        print "\n";

        print "Action 'news'.\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('news', $writeFiles);
        $this->actionNews($writeFiles);
        $this->showTime($start);
    }

    /**
     * Конвертация новостей.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionNews($writeFiles = false)
    {
        $n = new NewsConverter();
        $n->writeFiles = $writeFiles;
        $n->convert();
    }

    /**
     * Конвертация персон.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionPersons($writeFiles = false)
    {
        $p = new PersonsConverter();
        $p->writeFiles = $writeFiles;
        $p->convert();
    }

    /**
     * Конвертация команд.
     */
    public function actionTeams()
    {
        $t = new TeamsConverter();
        $t->convert();
    }

    /**
     * Конвертация контрактов, игроков и команд.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     *
     * @throws CException
     */
    public function actionPlayers($writeFiles = false)
    {
        $c = new PlayersConverter();
        $c->writeFiles = $writeFiles;
        $c->convert();
    }

    /**
     * Конвертация сезонов, чемпионатов и этапов.
     *
     * @throws CException
     */
    public function actionChamps()
    {
        $c = new ChampsConverter();
        $c->convert();
    }

    /**
     * Конвертация матчей, их событий и расстановки.
     */
    public function actionMatches()
    {
        $m = new MatchesConverter();
        $m->convert();
    }

    /**
     * Перенос файлов из текущей структуры в новую.
     */
    public function actionFiles()
    {
        $f = new FilesConverter();
        $f->convert();
    }

    public function actionBranches()
    {
        $b = new BranchesConverter();
        $b->convert();
    }

    public function actionFail()
    {
        /*$players_names = [];
        $players = [[
            'player_id' => 'Ид игрока',
            'first_name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'borned' => 'ДР',
            'team_id' => 'Ид команды',
            'date_from' => 'Контракт "от"',
            'date_to' => 'Контракт "до"',
            'staff' => 'Состав',
            'number' => 'Номер',
            'team_title' => 'Команда',
            'region' => 'Город'
        ]];
        $db = Players::model()->dbConnection;
        $command = $db->createCommand(
            'SELECT
                CONCAT(first_name, surname, patronymic, to_char(borned, \'YYYY-MM-DD\')) AS str,
                COUNT(*) AS cnt
            FROM tsi.players
            WHERE first_name!=\'\' OR surname!=\'\' OR patronymic!=\'\'
            GROUP BY str
            ORDER BY cnt DESC, str'
        )->queryAll();


        foreach ($command as $row) {
            if ($row['cnt'] > 1) {
                $players_names[] = $row['str'];
            }
        }

        foreach ($players_names as $name) {
            $pc = $db->createCommand(
                'SELECT
                    id AS player_id, first_name, surname, patronymic, to_char(borned, \'YYYY-MM-DD\') AS borned
                 FROM
                    tsi.players
                 WHERE
                    CONCAT(first_name, surname, patronymic, to_char(borned, \'YYYY-MM-DD\'))=:name'
            )->queryAll(true, [':name' => $name]);

            foreach ($pc as $prow) {
                $full_row = [
                    'player_id' => '',
                    'first_name' => '',
                    'surname' => '',
                    'patronymic' => '',
                    'borned' => '',
                    'team_id' => '',
                    'date_from' => '',
                    'date_to' => '',
                    'staff' => '',
                    'number' => '',
                    'team_title' => '',
                    'region' => ''
                ];
                $full_row = array_merge($full_row, $prow);
                $cc = $db->createCommand(
                    'SELECT
                        team AS team_id, date_from, date_to, staff, number
                     FROM
                        tsi.contracts
                     WHERE
                        player=:player'
                )->queryAll(true, [':player' => $prow['player_id']]);
                foreach ($cc as $crow) {
                    $crow['staff'] = (int) $crow['staff'] ? 'моложедный' : 'основной';
                    $full_row = array_merge($full_row, $crow);
                    $tc = $db->createCommand(
                        'SELECT
                            title AS team_title, region
                         FROM
                            tsi.teams
                         WHERE
                            id=:team_id'
                    )->queryAll(true, [':team_id' => $crow['team_id']]);
                    foreach ($tc as $trow) {
                        $full_row = array_merge($full_row, $trow);
                    }
                }
                $players[] = $full_row;
            }
        }

        $fh = fopen(__DIR__ . '/players.csv', 'w');
        foreach ($players as $player) {
            fwrite($fh, implode(';', array_values($player)) . "\n");
        }
        fclose($fh);*/

        /*$teams = [[
            'id' => 'Ид команды',
            'title' => 'Название',
            'region' => 'Город'
        ]];
        $db = Teams::model()->dbConnection;
        $command = $db->createCommand(
            'SELECT
                id, title, region
            FROM tsi.teams
            WHERE
                id NOT IN (SELECT team FROM tsi.teamstats) AND
                id NOT IN (SELECT team FROM tsi.contracts) AND
                title!=\'\'
            ORDER BY title'
        )->queryAll();

        foreach ($command as $row) {
            $mc = $db->createCommand('SELECT COUNT(id) FROM tsi.matches WHERE team1=:team_id OR team2=:team_id')
                ->queryScalar([':team_id' => $row['id']]);
            if (!$mc) {
                $teams[] = $row;
            }
        }

        $fh = fopen(__DIR__ . '/teams.csv', 'w');
        foreach ($teams as $team) {
            fwrite($fh, implode(';', array_values($team)) . "\n");
        }
        fclose($fh);*/

        $players_csv = file(Yii::getPathOfAlias('accordance') . '/exclude_players.csv');
        $players = [];
        foreach ($players_csv as $line) {
            list($id,,,,,,,,,,,,$del) = explode(';', $line);
            if (mb_strpos($del, 'рохать')) {
                $players[] = (int) $id;
            }
        }
        sort($players);
        file_put_contents(Yii::getPathOfAlias('accordance') . '/exclude_players.php', sprintf("<?php\n\nreturn %s;\n", var_export($players, true)));
    }

    public function actionCountries()
    {
        $countries_ru = $countries_en = [];
        $lines = file(__DIR__ . '../../../tmp/countries.txt');
        foreach ($lines as $line) {
            list($name, , $english, $alpha2, , , ,) = explode("\t", $line);
            if ($name == 'name') {
                continue;
            }
            $alpha2 = strtolower($alpha2);
            $countries_ru[] = ['label' => $name, 'value' => $alpha2];
            $countries_en[] = ['label' => $english, 'value' => $alpha2];
        }
        file_put_contents(
            __DIR__ . '../../../tmp/countries.php',
            var_export($countries_ru, true) . "\n\n" . var_export($countries_en, true)
        );
    }

    public function actionDataNews()
    {
        $news = [[
            'object_id' => 'Ид новости в админке',
            'multilang_id' => 'Многоязычны ид',
            'import_id' => 'Ид импорта',
            'lang' => 'Язык',
            'date' => 'Дата',
            'title' => 'Заголовок',
            //'content' => 'Содержание',
            'categories' => 'Категории',
        ]];
        $db = NewsObjects::model()->dbConnection;
        $command = $db->createCommand(
            'SELECT n.object_id, ml.multilang_id, m.import_id, IF(ml.lang_id = 1, \'ru\', \'en\') AS lang,
    n.title, n.publish_date_on AS date, n.content
FROM fc__news__objects AS n
    JOIN fc__core__multilang_link AS ml ON ml.entity_id=n.object_id
    JOIN fc__core__multilang AS m ON m.id=ml.multilang_id AND m.module_id=27 AND m.entity=\'object\'
ORDER BY n.publish_date_on DESC'
        )->queryAll();

        foreach ($command as $row) {
            $categories = '';
            $cat_command = $db->createCommand(
                'SELECT c.category_id, c.title
    FROM fc__news__categories AS c
        JOIN fc__news__category_objects AS co ON co.category_id=c.category_id AND co.object_id='.$row['object_id']
            )->queryAll();

            foreach ($cat_command as $cat_row) {
                $categories .= $cat_row['title'] . ' (' . $cat_row['category_id'] . ') ';
            }

            $news[] = [
                'object_id' => $row['object_id'],
                'multilang_id' => $row['multilang_id'],
                'import_id' => $row['import_id'],
                'lang' => $row['lang'],
                'date' => $row['date'],
                'title' => $row['title'],
                //'content' => substr(strip_tags(str_replace("\t", '', $row['content'])), 0, 200),
                'categories' => $categories
            ];
        }

        $fh = fopen(__DIR__ . '/news.csv', 'w');
        foreach ($news as $n) {
            fwrite($fh, implode('#', array_values($n)) . "\n");
        }
        fclose($fh);
    }

    public function actionMoveNews()
    {
        $src_db = FilesBranches::model()->dbConnection;
        $dst_db = NewsObjects::model()->dbConnection;
        $new_files = [
            23612 => 35363,
            23613 => 35364,
            23616 => 35365,
            23619 => 35366,
            23621 => 35367,
            23622 => 35368,
            23623 => 35369,
            23625 => 35370,
            23628 => 35371,
            23631 => 35372,
            23634 => 35373,
            23635 => 35374,
            23637 => 35375
        ];
        $command = $src_db->createCommand(
            'SELECT * FROM fc__news__objects WHERE object_id IN (23612, 23613, 23616, 23619, 23621, 23622, 23623, 23625, 23628, 23631, 23634, 23635, 23637)'
        )->queryAll();
        $newss = [];
        foreach ($command as $row) {
            //print_r($row); continue;
            $object_id = $row['object_id'];
            /*unset($row['object_id']);
            $news = new NewsObjects();
            $news->setAttributes($row);
            $news->parents = $src_db->createCommand(
                'SELECT category_id FROM fc__news__category_objects WHERE object_id=:object_id'
            )->queryColumn([':object_id' => $object_id]);
            //print_r($news->getAttributes());
            if (!$news->save()) {
                die('dfasdfasdfas');
            }
            $newss[$object_id] = $news->getId();*/
            /*echo '('.$row['publish_date_on'].')';
            echo $news->getId().'--'.$news->getMultilangId().'--'.$news->publish_date_on.'--';*/
            $links = $src_db->createCommand('SELECT * FROM fc__files WHERE path=:path')
            ->queryAll(true, [
                ':path' => 'news/object/' . $object_id . '/'
            ]);
            $main = $sort = 1;
            foreach ($links as $src_link) {
                $src_link['file_id'] = null;
                $src_link['path'] = 'news/object/' . $new_files[$object_id] . '/';
                $file = new Files();
                $file->setAttributes($src_link);
                $file->save();

                echo $file->file_id.'===';
                $dst_db->createCommand(
                    'INSERT INTO fc__files__link SET file_id=:file_id, module_id=:module_id, category_id=:category_id,
                    object_id=:object_id, field_id=:field_id, main=:main, sort=:sort'
                )->execute(
                    [
                        ':file_id' => $file->file_id,
                        ':module_id' => BaseFcModel::NEWS_MODULE_ID,
                        ':category_id' => 0,
                        ':object_id' => $new_files[$object_id],
                        ':field_id' => 'file',
                        ':main' => $main,
                        ':sort' => $sort
                    ]
                );
                /*$link = new FilesLink();
                $link->file_id = $file->file_id ?: $dst_db->lastInsertID;
                $link->module_id = BaseFcModel::NEWS_MODULE_ID;
                $link->category_id = 0;
                $link->object_id = $new_files[$object_id];
                $link->file_id = 'file';
                $link->title = '';
                $link->descr = '';
                $link->main = $main;
                $link->sort = $sort;
                $link->save();
                $main = 0;
                $sort++;*/
            }
        }
        print_r($newss);
    }

    public function actionMoveNewsCategs()
    {
        $mn = new MoveNewsConverter();
        $mn->convert();
    }

    public function actionReverseGoals()
    {
        $i = 0;
        foreach (FcTeamstat::model()->findAll('season_id!=:season_id', [':season_id' => 47089]) as $stat) {
            $goals = $stat->goalsconceded;
            $goalsconceded = $stat->goals;
            $stat->saveAttributes(['goals' => $goals, 'goalsconceded' => $goalsconceded]);
            $i++;
            print "\r$i";
        }
    }

    public function actionMoveOldNews()
    {
        $mn = new MoveOldNewsConverter();
        $mn->convert();
    }

    public function actionKitMultilang()
    {
        $k = new KitMultilangConverter();
        $k->convert();
    }

    public function actionReplaceEvent()
    {
        $re = new ReplaceEventConverter();
        $re->convert();
    }

    public function actionFcMultilang()
    {
        $k = new FcMultilangConverter();
        $k->convert();
    }

    public function actionFcMultilangFix()
    {
        $k = new FcMultilangFixConverter();
        $k->convert();
    }

    protected function beforeAction($action, $params)
    {
        $this->ensureDirectory(Yii::getPathOfAlias('accordance'));
        $this->startTime = microtime(true);

        return parent::beforeAction($action, $params);
    }

    protected function afterAction($action, $params, $exitCode = 0)
    {
        $this->showTime();

        return parent::afterAction($action, $params, $exitCode);
    }

    private function showTime($start = 0)
    {
        $start = $start ?: $this->startTime;
        print "\n" . 'Done in ' . sprintf('%f', microtime(true) - $start) . ".\n";
    }
}
