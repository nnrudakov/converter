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

        print "Action 'champs'.\n";
        $start = microtime(true);
        $this->actionChamps();
        $this->showTime($start);
        print "\n";

        /*print "Action 'persons'.\n";
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
    public function actionPersons ($writeFiles = false)
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
    public function actionPlayers ($writeFiles = false)
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
            $countries_ru[$alpha2] = $name;
            $countries_en[$alpha2] = $english;
        }
        file_put_contents(
            __DIR__ . '../../../tmp/countries.php',
            var_export($countries_ru, true) . "\n\n" . var_export($countries_en, true)
        );
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
