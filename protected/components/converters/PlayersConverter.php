<?php

/**
 * Конвертер контрактов, персон и команд.
 *
 * @package    converter
 * @subpackage players
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PlayersConverter implements IConverter
{
    /**
     * Профиль игроков.
     *
     * @var string
     */
    const PROFILE_PLAYER = 'player';

    /**
     * Текущее амплуа нападающего.
     *
     * @var string
     */
    const AMPLUA_CUR_FORWARD = 1;

    /**
     * Текущее амплуа защитника.
     *
     * @var string
     */
    const AMPLUA_CUR_BACK = 2;

    /**
     * Текущее амплуа вратаря.
     *
     * @var string
     */
    const AMPLUA_CUR_GOALKEEPER = 3;

    /**
     * Текущее амплуа полузащитника.
     *
     * @var string
     */
    const AMPLUA_CUR_HALFBACK = 4;

    /**
     * Текущее амплуа пз/нп.
     *
     * @var string
     */
    const AMPLUA_CUR_PZNP = 5;

    /**
     * Текущее амплуа полевого игрока.
     *
     * @var string
     */
    const AMPLUA_CUR_FIELD = 7;

    /**
     * Новое амплуа нападающего.
     *
     * @var string
     */
    const AMPLUA_NEW_FORWARD = 'striker';

    /**
     * Новое амплуа защитника.
     *
     * @var string
     */
    const AMPLUA_NEW_BACK = 'defender';

    /**
     * Новое амплуа вратаря.
     *
     * @var string
     */
    const AMPLUA_NEW_GOALKEEPER = 'goalkeeper';

    /**
     * Новое амплуа полузащитника.
     *
     * @var string
     */
    const AMPLUA_NEW_HALFBACK = 'midfielder';

    /**
     * Новое амплуа пз/нп.
     *
     * @var string
     */
    const AMPLUA_NEW_PZNP = 'mf/str';

    /**
     * Новое амплуа полевого игрока.
     *
     * @var string
     */
    const AMPLUA_NEW_FIELD = 'fielder';

    /**
     * Вратарь на матч.
     *
     * @var integer
     */
    const POSITION_GOALKEEPER = 1;

    /**
     * Нападающий на матч.
     *
     * @var integer
     */
    const POSITION_FORWARD = 4;

    /**
     * Защитник на матч.
     *
     * @var integer
     */
    const POSITION_BACK = 2;

    /**
     * Полузащитник на матч.
     *
     * @var integer
     */
    const POSITION_HALFBACK = 3;

    /**
     * @var string
     */
    const TAGS_TEAM = 'team';

    /**
     * @var string
     */
    const TAGS_PLAYER_FC = 'player';

    /**
     * @var string
     */
    const TAGS_PLAYER = 'person';

    /**
     * Соответствие между текущими и новыми амплуа.
     *
     * @var array
     */
    public static $ampluas = [
        self::AMPLUA_CUR_FORWARD    => self::AMPLUA_NEW_FORWARD,
        self::AMPLUA_CUR_BACK       => self::AMPLUA_NEW_BACK,
        self::AMPLUA_CUR_GOALKEEPER => self::AMPLUA_NEW_GOALKEEPER,
        self::AMPLUA_CUR_HALFBACK   => self::AMPLUA_NEW_HALFBACK,
        self::AMPLUA_CUR_PZNP       => self::AMPLUA_NEW_PZNP,
        self::AMPLUA_CUR_FIELD      => self::AMPLUA_NEW_FIELD
    ];

    /**
     * Соответствия позизиям на матче амплуа.
     *
     * @var array
     */
    public static $positions = [
        self::POSITION_GOALKEEPER => self::AMPLUA_NEW_GOALKEEPER,
        self::POSITION_FORWARD    => self::AMPLUA_NEW_FORWARD,
        self::POSITION_BACK       => self::AMPLUA_NEW_BACK,
        self::POSITION_HALFBACK   => self::AMPLUA_NEW_HALFBACK
    ];

    /**
     * Сохранить файлы на диск.
     *
     * @var bool
     */
    public $writeFiles = false;

    /**
     * Соотвествие текущих команд новым.
     *
     * @var array
     */
    private $teams = [];

    /**
     * @var array
     */
    private $teamsM = [];

    /**
     * Соотвествие игроков.
     *
     * @var array
     */
    private $players = [];

    /**
     * @var array
     */
    private $playersM = [];

    /**
     * @varr array
     */
    private $excludePlayers = [];

    /**
     * Сезоны для статистики.
     *
     * @var array
     */
    private $seasons = [];

    /**
     * Чемпионаты для статистики.
     *
     * @var array
     */
    private $champs = [];

    /**
     * Этапы для статистики.
     *
     * @var array
     */
    private $stages = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * Файл соответствий текущих идентификаторов игроков новым.
     *
     * @var string
     */
    private $playersFile = '';

    /**
     * @var string
     */
    private $playersFileM = '';

    /**
     * Файл соответствий текущих идентификаторов команд новым.
     *
     * @var string
     */
    private $teamsFile = '';

    /**
     * @var string
     */
    private $teamsFileM = '';

    /**
     * @var array
     */
    private $tagsFile = '';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rTeams: %d (%d). Players: %d (%d). Contracts: %d. Players statistics: %d. Teams statistics: %d. Teams tags: %d (%d). Players tags: %d (%d).";

    /**
     * @var integer
     */
    private $doneTeams = 0;

    /**
     * @var integer
     */
    private $donePlayers = 0;

    /**
     * @var integer
     */
    private $doneContracts = 0;

    /**
     * @var integer
     */
    private $donePlayerStats = 0;

    /**
     * @var integer
     */
    private $doneTeamStats = 0;

    /**
     * @var integer
     */
    private $doneTeamTags = 0;

    /**
     * @var integer
     */
    private $donePlayerTags = 0;

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->teamsFile    = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->teamsFileM   = Yii::getPathOfAlias('accordance') . '/teams_m.php';
        $this->playersFile  = Yii::getPathOfAlias('accordance') . '/players.php';
        $this->playersFileM = Yii::getPathOfAlias('accordance') . '/players_m.php';
        $this->tagsFile     = Yii::getPathOfAlias('accordance') . '/tags.php';
        $this->excludePlayers = include Yii::getPathOfAlias('accordance') . '/exclude_players.php';

        // сезоны и чемпионаты уже должны быть перенесены
        $cc = new ChampsConverter();
        $this->seasons  = $cc->getSeasonsM();
        $this->champs   = $cc->getChampsM();
        $this->stages   = $cc->getStagesM();
        $this->players  = $this->getPlayers();
        $this->playersM = $this->getPlayersM();
        $this->teams    = $this->getTeams();
        $this->teamsM   = $this->getTeamsM();
        $this->tags     = $this->getTags();
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $this->resaveTags();
        /*$this->tags = [self::TAGS_TEAM => [], self::TAGS_PLAYER => [], MatchesConverter::TAGS_MATCH => []];
        $criteria = new CDbCriteria();
        $criteria->select = ['id', 'team', 'player', 'date_from', 'date_to', 'staff', 'number'];
        $criteria->with = ['playerTeam', 'playerPlayer'];
        $criteria->order = 't.player';
        $src_contracts = new PlayersContracts();

        // игроки, у которых есть контракты
        foreach ($src_contracts->findAll($criteria) as $c) {
            if (in_array($c->playerPlayer->id, $this->excludePlayers)) {
                continue;
            }
            $player_id = $this->savePlayer(
                $c->playerPlayer,
                isset(self::$ampluas[$c->playerPlayer->amplua]) ? self::$ampluas[$c->playerPlayer->amplua] : null
            );
            $team_id = $this->saveTeam($c->playerTeam, $c->staff ? FcTeams::JUNIOR : FcTeams::MAIN);

            $contract = new FcContracts();
            $contract->team_id   = $team_id;
            $contract->person_id = $player_id;
            $contract->fromtime  = date('Y-m-d', strtotime($c->date_from));
            $contract->untiltime = date('Y-m-d', strtotime($c->date_to));
            $contract->number    = $c->number;

            $exists_contract = FcContracts::model()->exists(
                new CDbCriteria(
                    [
                        'condition' => 'team_id=:team_id AND person_id=:person_id AND fromtime=:fromtime ' .
                            'AND untiltime=:untiltime AND number=:number',
                        'params' => [
                            ':team_id'   => $contract->team_id,
                            ':person_id' => $contract->person_id,
                            ':fromtime'  => $contract->fromtime,
                            ':untiltime' => $contract->untiltime,
                            ':number'    => $contract->number
                        ]
                    ]
                )
            );

            if ($exists_contract) {
                continue;
            }

            if (!$contract->save()) {
                throw new CException(
                    'Player\'s contract not created.' . "\n" .
                    var_export($contract->getErrors(), true) . "\n" .
                    $c . "\n"
                );
            }

            /*$contract->setNew();
            $contract->team_id = $teams[BaseFcModel::LANG_EN];
            $contract->person_id = $players[BaseFcModel::LANG_EN];
            $contract->save();*

            $this->doneContracts++;
            $this->progress();
        }*/

        // оставшиеся команды без связок с игроками
        //$this->saveTeams();
        //$this->saveTeamStat();

        // игроки, для которых нет контрактов, но они участвовали в матчах
        //$this->saveMatchPlayers();
        //$this->savePlayerStat();

        /*ksort($this->teams);
        ksort($this->teamsM);*/
        /*ksort($this->players);
        ksort($this->playersM);*/
        //file_put_contents($this->teamsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->teams, true)));
        //file_put_contents($this->teamsFileM, sprintf(self::FILE_ACCORDANCE, var_export($this->teamsM, true)));
        /*file_put_contents($this->playersFile, sprintf(self::FILE_ACCORDANCE, var_export($this->players, true)));
        file_put_contents($this->playersFileM, sprintf(self::FILE_ACCORDANCE, var_export($this->playersM, true)));*/
        //file_put_contents($this->tagsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->tags, true)));
    }

    private function resaveTags()
    {
        $this->tags[self::TAGS_TEAM] = [];
        $this->tags[self::TAGS_PLAYER] = [];
        foreach ($this->teams as $tid => $t) {
            foreach ($t as $teams) {
                $multilang_id = null;
                foreach ($teams as $lang_id => $id) {
                    if ($team = FcTeams::model()->findByPk($id)) {
                        list($tag_id, $multilang_id) = $this->resaveTag(
                            TagsCategories::TEAMS,
                            $team->getMultilangId(),
                            $team->title . ' ' . $team->staff,
                            $lang_id,
                            $multilang_id
                        );
                        $this->tags[self::TAGS_TEAM][$tid][$lang_id] = $tag_id;
                        $this->doneTeams++;
                        $this->progress();
                    }
                }
            }
        }
        foreach ($this->players as $pid => $p) {
            foreach ($p as $lang_id => $id) {
                $multilang_id = null;
                if ($player = FcPerson::model()->findByPk($id)) {
                    list($tag_id, $multilang_id) = $this->resaveTag(
                        TagsCategories::PLAYERS,
                        $player->getMultilangId(),
                        implode(
                            ' ',
                            [
                                date('Ymd', strtotime($player->birthday)),
                                $player->lastname,
                                $player->firstname,
                                $player->middlename
                            ]
                        ),
                        $lang_id,
                        $multilang_id
                    );
                    $this->tags[self::TAGS_PLAYER][$pid][$lang_id] = $tag_id;
                    $this->donePlayers++;
                    $this->progress();
                }
            }
        }
        file_put_contents($this->tagsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->tags, true)));
    }

    private function resaveTag($categoryId, $objectId, $title, $langId, $multilangId)
    {
        $name = Utils::nameString($title);
        $title .= ' (' . $langId . ')';
        $tag = Tags::model()->find(new CDbCriteria(['condition' => 'title=:title', 'params' => [':title' => $title]]));
        if (!$tag) {
            $tag = new Tags();
            $tag->category_id = $categoryId;
            $tag->name = $name;
            $tag->title = $title;
            $tag->publish = 1;
        }
        if ($multilangId) {
            $tag->multilangId = $multilangId;
        }
        $tag->lang = $langId;
        $tag->save();
        $link = TagsModules::model()->find(
            new CDbCriteria(
                [
                    'condition' => 'tag_id=:tag_id AND module_id=:module_id',
                    'params' => [':tag_id' => $tag->getId(), ':module_id' => BaseFcModel::FC_MODULE_ID]
                ]
            )
        );
        if (!$link) {
            $link = new TagsModules();
            $link->tag_id = $tag->getId();
            $link->module_id = BaseFcModel::FC_MODULE_ID;
            $link->publish = 1;
            $link->save();
        }
        $source = TagsSources::model()->find(
            new CDbCriteria(
                [
                    'condition' => 'link_id=:link_id AND object_id=:object_id',
                    'params' => [':link_id' => $link->getId(), ':object_id' => $objectId]
                ]
            )
        );
        if (!$source) {
            $source = new TagsSources();
            $source->link_id = $link->getId();
            $source->object_id = $objectId;
            $source->save();
        }

        return [$tag->getId(), $tag->multilangId];
    }

    /**
     * @return array
     */
    public function getTeams()
    {
        return file_exists($this->teamsFile) ? include $this->teamsFile : [];
    }

    /**
     * @return array
     */
    public function getTeamsM()
    {
        return file_exists($this->teamsFileM) ? include $this->teamsFileM : [];
    }

    /**
     * @return array
     */
    public function getPlayers()
    {
        return file_exists($this->playersFile) ? include $this->playersFile : [];
    }

    /**
     * @return array
     */
    public function getPlayersM()
    {
        return file_exists($this->playersFileM) ? include $this->playersFileM : [];
    }

    /**
     * @return array|mixed
     */
    public function getTags()
    {
        return file_exists($this->tagsFile) ? include $this->tagsFile : [];
    }

    /**
     * Игроки, для которых нет контрактов, но они участвовали в матчах.
     */
    private function saveMatchPlayers()
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['match', 'team', 'player', 'number', 'position'];
        $criteria->condition = 'match!=0 AND player NOT IN (' . implode(',', array_keys($this->players)) . ')';
        $criteria->addInCondition('team', array_keys($this->teams));
        $criteria->order = 'player';
        $src_players = new Matchplayers();
        $prev = '';

        foreach ($src_players->findAll($criteria) as $mp) {
            if (in_array($mp->player, $this->excludePlayers)) {
                continue;
            }
            $p = Players::model()->findByPk($mp->player);
            if ($p && $prev != $mp->player . $mp->team . $mp->number) {
                $player_id = $this->savePlayer(
                    $p,
                    isset(self::$positions[$mp->position]) ? self::$positions[$mp->position] : null
                );
                /* @var Matches $m */
                $match = $mp->playerMatch;
                /* @var Schedule $sch */
                if ($sch = $match->sch) {
                    /* @var Tournaments $champ */
                    $champ = $sch->champ;
                    $contract = new FcContracts();
                    $attrs = [
                        'team_id'   => $this->getTrueTeam($mp->team, $champ->id),
                        'person_id' => $player_id,
                        'number'    => $mp->number
                    ];

                    if (!FcContracts::model()->findByAttributes($attrs)) {
                        $contract->setAttributes($attrs);
                        if (!$contract->save()) {
                            throw new CException(
                                'Player\'s contract not created.' . "\n" .
                                var_export($contract->getErrors(), true) . "\n" .
                                $mp . "\n"
                            );
                        }

                        /*$contract->setNew();
                        $contract->team_id = $team_id;
                        $contract->person_id = $player_id;
                        $contract->save();*/

                        $this->doneContracts++;
                        $this->progress();
                    }
                }

                $prev = $mp->player . $mp->team . $mp->number;
            }
        }

        return true;
    }

    /**
     * Сохранение персоны.
     *
     * @param Players $p
     * @param string  $amplua
     *
     * @return integer
     *
     * @throws CException
     */
    private function savePlayer($p, $amplua = null)
    {
        if (isset($this->players[$p->id])) {
            $player = FcPerson::model()->findByPk($this->players[$p->id][BaseFcModel::LANG_RU]);
            return $player->getMultilangId();
        }

        $player = new FcPerson();
        //$player->importId   = $p->id;
        $player->writeFiles = $this->writeFiles;
        $player->filesUrl = Players::PHOTO_URL;
        /*$player->setFileParams($p->id);
        $player->setFileParams($p->id, FcPerson::FILE_LIST, 0, FcPerson::FILE_FIELD_LIST);
        $player->setFileParams($p->id, FcPerson::FILE_INFORMER, 0, FcPerson::FILE_FIELD_INFORMER);*/
        $player->firstname   = $p->first_name;
        $player->lastname    = $p->surname;
        $player->middlename  = $p->patronymic;
        $player->birthday    = date('Y-m-d', strtotime($p->borned));
        $p_attrs = [
            'firstname'  => $player->firstname,
            'lastname'   => $player->lastname,
            'middlename' => $player->middlename,
            'birthday'   => $player->birthday
        ];

        // переносили уже
        if ($exists_player = FcPerson::model()->findByAttributes($p_attrs, new CDbCriteria(['order' => 'id']))) {
            $player->fileParams = [];
            $player->setOwner = $player->setMultilang = false;
            $player->setIsNewRecord(false);
            $player->setAttributes($exists_player->getAttributes());
            $player->id = $exists_player->id;
            // переделываем файлы
            $player->save();

            $ids = [BaseFcModel::LANG_RU => (int)$exists_player->getId(), BaseFcModel::LANG_EN => $player->getPairId()];
            $this->players[$p->id] = $ids;
            $this->playersM[$p->id] = $player->getMultilangId();

            //return $player->getMultilangId();
        } else {
            $player->citizenship = $p->citizenship;
            $player->biograpy    = Utils::clearText($p->bio);
            $player->profile     = self::PROFILE_PLAYER;
            $player->progress    = Utils::clearText($p->achivements);
            $player->resident    = $p->resident;
            $player->nickname    = $p->nickname;
            $player->height      = $p->height;
            $player->weight      = $p->weight;
            $player->amplua      = $amplua;
            $fileparams = $player->fileParams;

            if (!$player->save()) {
                throw new CException(
                    'Player not created.' . "\n" .
                    var_export($player->getErrors(), true) . "\n" .
                    $p . "\n"
                );
            }

            $this->playersM[$p->id] = $player->getMultilangId();

            $this->players[$p->id][BaseFcModel::LANG_RU] = $ru_id = (int) $player->id;
            $player->setNew();
            $player->fileParams = $fileparams;
            $player->save();
            $this->players[$p->id][BaseFcModel::LANG_EN] = $en_id = (int) $player->id;
            $ids = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];

            $this->donePlayers++;
            $this->progress();
        }

        $this->saveTags(
            self::TAGS_PLAYER,
            $p->id,
            $ids,
            TagsCategories::PLAYERS,
            implode(
                ' ',
                [
                    $player->lastname,
                    $player->firstname,
                    $player->middlename,
                    '(' . preg_replace('/\s\d{2}:\d{2}:\d{2}/', '', $player->birthday) . ')'
                ]
            )
        );

        return $player->getMultilangId();
    }

    /**
     * Сохранение команды.
     *
     * @param Teams  $t
     * @param string $staff
     *
     * @return integer
     *
     * @throws CException                                                                                 `
     */
    private function saveTeam($t, $staff)
    {
        if (!empty($this->teams[$t->id][$staff])) {
            $team = FcTeams::model()->findByPk($this->teams[$t->id][$staff][BaseFcModel::LANG_RU]);
            return $team->getMultilangId();
        }

        $team = new FcTeams();
        /*if ($staff == FcTeams::MAIN) {
            $team->importId = $t->id;
        }*/
        $team->writeFiles = $this->writeFiles;
        $team->filesUrl = Teams::PHOTO_URL;
        $team->setFileParams($t->id);
        $team->setFileParams($t->id, FcTeams::FILE_LOGO_SMALL, 0, FcTeams::FILE_FIELD_LOGO_SMALL);
        $team->setFileParams($t->id, FcTeams::FILE_LOGO_BIG, 0, FcTeams::FILE_FIELD_LOGO_BIG);
        $team->title   = $t->title;
        $team->city    = $t->region;
        $team->staff   = $staff;
        $team->site    = $t->web;
        $t_attrs = ['title' => $team->title, 'city' => $team->city, 'staff' => $team->staff];

        // переносили уже
        if ($exists_team = FcTeams::model()->findByAttributes($t_attrs)) {
            /*$team->fileParams = [];
            $team->setOwner = $team->setMultilang = false;
            $team->setIsNewRecord(false);
            $team->setAttributes($exists_team->getAttributes());
            $team->id = $exists_team->id;
            $team->info = Utils::clearText($t->info);
            $team->site = $t->web;
            // переделываем файлы
            $team->save();*/

            $ids = [BaseFcModel::LANG_RU => (int) $exists_team->getId(), BaseFcModel::LANG_EN => $exists_team->getPairId()];
            $this->teams[$t->id][$staff] = $ids;
            $this->teamsM[$t->id][$staff] = $team->getMultilangId();

            return $team->getMultilangId();
        } else {
            $team->info    = Utils::clearText($t->info);
            $team->country = $t->country;
            $fileparams = $team->fileParams;

            if (!$team->save()) {
                throw new CException(
                    'Team not created.' . "\n" .
                    var_export($team->getErrors(), true) . "\n" .
                    $t . "\n"
                );
            }

            $this->teamsM[$t->id][$staff] = $team->getMultilangId();

            $ru_id = (int) $team->id;
            $team->setNew();
            $team->fileParams = $fileparams;
            $team->save();
            $en_id = (int) $team->id;

            // в основной команде 2 состава
            $teams = isset($this->teams[$t->id]) ? $this->teams[$t->id] : [FcTeams::MAIN => [], FcTeams::JUNIOR => []];
            $teams[$staff][BaseFcModel::LANG_RU] = $ru_id;
            $teams[$staff][BaseFcModel::LANG_EN] = $en_id;
            $this->teams[$t->id] = $teams;

            $this->doneTeams++;
            $this->progress();
            $ids = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];
        }

        $this->saveTags(
            self::TAGS_TEAM,
            $t->id,
            $ids,
            TagsCategories::TEAMS,
            implode(' ', [$team->title, $team->staff, '(' . $team->city . ')'])
        );

        return $team->getMultilangId();
    }

    /**
     * Сохранение команд, для которых нет игроков.
     *
     * @return bool
     */
    private function saveTeams()
    {
        $criteria = new CDbCriteria(
            [
                'select'    => ['id', 'title', 'info', 'region', 'country', 'web'],
                'condition' => 'title!=\'\'',
                'order'     => 'id'
            ]
        );
        $src_teams = new Teams();
        $conn = $src_teams->getDbConnection();
        $champs = implode(',', Tournaments::$junior);

        foreach ($src_teams->findAll($criteria) as $t) {
            $count_matches = function ($teamId, $isMain) use ($conn, $champs) {
                return $conn->createCommand(
                    'SELECT
                        COUNT(id)
                    FROM
                        tsi.schedule
                    WHERE
                        (team1=:team OR team2=:team)
                        AND tournament ' . ($isMain ? 'NOT ' : '') . 'IN (' . $champs . ')'
                )->queryScalar([':team' => $teamId]);
            };

            // если есть кол-во матчей в основных первенствах, пишем как основную команду
            if ($count_matches($t->id, true)) {
                $this->saveTeam($t, FcTeams::MAIN);
            }

            // если есть кол-во матчей молодежки, пишем как молодежку
            if ($count_matches($t->id, false)) {
                $this->saveTeam($t, FcTeams::JUNIOR);
            }
        }

        return true;
    }

    /**
     * Сохранение статистики игрока.
     *
     * @throws CException
     */
    private function savePlayerStat()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'id';
        $src_stats = new Playerstats();

        foreach ($src_stats->findAll($criteria) as $ps) {
            // пропускаем отсуствующие сезоны
            if (empty($this->seasons[$ps->season])    ||
                empty($this->champs[$ps->tournament]) ||
                empty($this->playersM[$ps->player])) {
                continue;
            }

            $team_id = $this->getTrueTeam($ps->team, $ps->tournament);
            $player_id = $this->playersM[$ps->player];
            // пропускаем левую статистику
            $stat = FcPersonstat::model()->exists(
                new CDbCriteria(
                    [
                        'condition' => 'person_id=:player_id AND team_id=:team_id AND season_id=:season_id AND ' .
                            'championship_id=:champ_id',
                        'params' => [
                            ':player_id' => $player_id,
                            ':team_id'   => $team_id,
                            ':season_id' => $this->seasons[$ps->season],
                            ':champ_id'  => $this->champs[$ps->tournament]
                        ]
                    ]
                )
            );

            if ($stat) {
                continue;
            }

            $stat = new FcPersonstat();
            $stat->person_id        = $player_id;
            $stat->team_id          = $team_id;
            $stat->season_id        = $this->seasons[$ps->season];
            $stat->championship_id  = $this->champs[$ps->tournament];
            $stat->gamecount        = $ps->played;
            $stat->startcount       = $ps->begined;
            $stat->benchcount       = $ps->wentin;
            $stat->replacementcount = $ps->wentout;
            $stat->goalcount        = $ps->goals;
            $stat->assistcount      = $ps->helps;
            $stat->yellowcount      = $ps->warnings;
            $stat->redcount         = $ps->removed;
            $stat->playtime         = $ps->timeplayed;

            if (!$stat->save()) {
                throw new CException(
                    'Player statistic not created.' . "\n" .
                    var_export($stat->getErrors(), true) . "\n" .
                    $ps . "\n"
                );
            }

            $this->donePlayerStats++;
            $this->progress();
        }

        return true;
    }

    /**
     * Cохранение статистики команды.
     *
     * @throws CException
     */
    private function saveTeamStat()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'id';
        $src_teamstats = new Teamstats();

        foreach ($src_teamstats->findAll($criteria) as $ts) {
            $team_id = $this->getTrueTeam($ts->team, $ts->tournament);
            // пропускаем левую статистику
            /*$stat = FcTeamstat::model()->exists(
                new CDbCriteria(
                    [
                        'condition' => 'team_id=:team_id AND season_id=:season_id AND stage_id=:stage_id',
                        'params' => [
                            ':team_id'   => $team_id,
                            ':season_id' => $this->seasons[$ts->season],
                            ':stage_id'  => $this->stages[$ts->stage],
                        ]
                    ]
                )
            );

            if ($stat) {   echo 1;
                continue;
            }*/

            $stat = new FcTeamstat();
            $stat->team_id       = $team_id;
            $stat->season_id     = $this->seasons[$ts->season];
            $stat->stage_id      = $this->stages[$ts->stage];
            $stat->gamecount     = $ts->played;
            $stat->wincount      = $ts->won;
            $stat->drawcount     = $ts->drawn;
            $stat->losscount     = $ts->lost;
            $stat->goalsconceded = $ts->goalsfor;
            $stat->goals         = $ts->goalsagainst;
            $stat->score         = $ts->points;
            $stat->place         = (int) $ts->ord;

            if (!$stat->save()) {
                throw new CException(
                    'Team statistic not created.' . "\n" .
                    var_export($stat->getErrors(), true) . "\n" .
                    $ts . "\n"
                );
            }

            $this->doneTeamStats++;
            $this->progress();
        }

        return true;
    }

    /**
     * @param string  $entity
     * @param integer $entityId
     * @param array   $newEntities
     * @param integer $categoryId
     * @param string  $title
     *
     * @return bool
     * @throws CException
     */
    private function saveTags($entity, $entityId, $newEntities, $categoryId, $title)
    {
        $tag = Tags::model()->findByAttributes(['title' => $title . '_' .BaseFcModel::LANG_RU]);
        $ru_exists = true;

        if (!$tag) {
            $ru_exists = false;
            $tag = new Tags();
            $tag->category_id = $categoryId;
            $tag->name = substr(preg_replace('/(?!-)[\W]+/', '_', Utils::rus2lat($title)), 0, 255);
            $tag->title = $title . '_' .BaseFcModel::LANG_RU;
            $tag->publish = 1;
            $tag->priority = 0;

            if (!$tag->save()) {
                throw new CException('Tag not created.' . "\n" . var_export($tag->getErrors(), true) . "\n");
            }
        }

        $ru_id = $tag->getId();
        if (!$ru_exists) {
            $this->saveTagLinks($ru_id, $newEntities[BaseFcModel::LANG_RU]);
        }
        if ($tag_en = Tags::model()->findByAttributes(['title' => $title . '_' .BaseFcModel::LANG_EN])) {
            $en_id = $tag_en->getId();
        } else {
            $tag->setNew();
            $tag->title = $title . '_' .BaseFcModel::LANG_EN;
            $tag->save();
            $en_id = $tag->getId();
            $this->saveTagLinks($en_id, $newEntities[BaseFcModel::LANG_EN]);

            $entity == self::TAGS_TEAM ? $this->doneTeamTags++ : $this->donePlayerTags++;
            $this->progress();
        }

        $this->tags[$entity][$entityId] = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];

        return true;
    }

    /**
     * @param integer $tagId
     * @param integer $objectId
     *
     * @return bool
     */
    private function saveTagLinks($tagId, $objectId)
    {
        $modules = new TagsModules();
        $modules->tag_id = $tagId;
        $modules->module_id = BaseFcModel::FC_MODULE_ID;
        $modules->publish = 1;
        $modules->is_default = 0;
        $modules->save();
        $objects = new TagsSources();
        $objects->link_id = $modules->link_id;
        $objects->object_id = $objectId;
        $objects->save();
        $modules->setNew();
        $modules->module_id = BaseFcModel::NEWS_MODULE_ID;
        $modules->save();

        return true;
    }

    /**
     * @param integer $teamId
     * @param integer $champId
     *
     * @return integer
     */
    private function getTrueTeam($teamId, $champId)
    {
        return Tournaments::isJunior($champId)
            ? $this->teamsM[$teamId][FcTeams::JUNIOR]
            : $this->teamsM[$teamId][FcTeams::MAIN];
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneTeams,
            $this->doneTeams * 2,
            $this->donePlayers,
            $this->donePlayers * 2,
            $this->doneContracts,
            $this->donePlayerStats,
            $this->doneTeamStats,
            $this->doneTeamTags,
            $this->doneTeamTags * 2,
            $this->donePlayerTags,
            $this->donePlayerTags * 2
        );
    }
}
