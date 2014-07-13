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
     * Соотвествие игроков.
     *
     * @var array
     */
    private $players = [];

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
     * Файл соответствий текущих идентификаторов команд новым.
     *
     * @var string
     */
    private $teamsFile = '';

    /**
     * @var array
     */
    private $tagsFile = '';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rTeams: %d (%d). Players: %d (%d). Contracts: %d (%d). Players statistics: %d (%d). Teams statistics: %d (%d). Teams tags: %d (%d). Players tags: %d (%d).";

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
        $this->teamsFile   = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->playersFile = Yii::getPathOfAlias('accordance') . '/players.php';
        $this->tagsFile    = Yii::getPathOfAlias('accordance') . '/tags.php';

        // сезоны и чемпионаты уже должны быть перенесены
        $cc = new ChampsConverter();
        $this->seasons = $cc->getSeasons();
        $this->champs  = $cc->getChamps();
        $this->stages  = $cc->getStages();
        $this->players = $this->getPlayers();
        $this->teams   = $this->getTeams();
        $this->tags    = $this->getTags();
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $this->tags = [self::TAGS_TEAM => [], self::TAGS_PLAYER => [], MatchesConverter::TAGS_MATCH => []];
        $criteria = new CDbCriteria(
            [
                'select' => ['id', 'team', 'player', 'date_from', 'date_to', 'staff', 'number'],
                'with'   => ['playerTeam', 'playerPlayer'],
                'order'  => 't.player'
            ]
        );
        $src_contracts = new PlayersContracts();

        // игроки, у которых есть контракты
        foreach ($src_contracts->findAll($criteria) as $c) {
            $players = $this->savePlayer(
                $c->playerPlayer,
                isset(self::$ampluas[$c->playerPlayer->amplua]) ? self::$ampluas[$c->playerPlayer->amplua] : null
            );
            $teams = $this->saveTeam($c->playerTeam, $c->staff ? FcTeams::JUNIOR : FcTeams::MAIN);

            $contract = new FcContracts();
            $contract->team_id   = $teams[BaseFcModel::LANG_RU];
            $contract->person_id = $players[BaseFcModel::LANG_RU];
            $contract->fromtime  = $c->date_from;
            $contract->untiltime = $c->date_to;
            $contract->number    = $c->number;

            if (FcContracts::model()->findByAttributes($contract->getAttributes())) {
                continue;
            }

            if (!$contract->save()) {
                throw new CException(
                    'Player\'s contract not created.' . "\n" .
                    var_export($contract->getErrors(), true) . "\n" .
                    $c . "\n"
                );
            }

            $contract->setNew();
            $contract->team_id = $teams[BaseFcModel::LANG_EN];
            $contract->person_id = $players[BaseFcModel::LANG_EN];
            $contract->save();

            $this->doneContracts++;
            $this->progress();
        }

        // оставшиеся команды без связок с игроками
        $this->saveTeams();
        $this->saveTeamStat();

        // игроки, для которых нет контрактов, но они участвовали в матчах
        $this->saveMatchPlayers();
        $this->savePlayerStat();

        ksort($this->teams);
        ksort($this->players);
        file_put_contents($this->teamsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->teams, true)));
        file_put_contents($this->playersFile, sprintf(self::FILE_ACCORDANCE, var_export($this->players, true)));
        file_put_contents($this->tagsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->tags, true)));
    }

    public function getTeams()
    {
        return file_exists($this->teamsFile) ? include $this->teamsFile : [];
    }

    public function getPlayers()
    {
        return file_exists($this->playersFile) ? include $this->playersFile : [];
    }

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
            $p = Players::model()->findByPk($mp->player);
            if ($p && $prev != $mp->player . $mp->team . $mp->number) {
                $players = $this->savePlayer(
                    $p,
                    isset(self::$positions[$mp->position]) ? self::$positions[$mp->position] : null
                );
                /* @var Matches $m */
                $match = $mp->playerMatch;
                /* @var Schedule $sch */
                if ($sch = $match->sch) {
                    /* @var Tournaments $champ */
                    $champ = $sch->champ;
                    $teams = $this->getTrueTeam($mp->team, $champ->id);
                    $contract = new FcContracts();
                    $contract->team_id   = $teams[BaseFcModel::LANG_RU];
                    $contract->person_id = $players[BaseFcModel::LANG_RU];
                    $contract->number    = $mp->number;

                    if (!FcContracts::model()->findByAttributes($contract->getAttributes())) {
                        if (!$contract->save()) {
                            throw new CException(
                                'Player\'s contract not created.' . "\n" .
                                var_export($contract->getErrors(), true) . "\n" .
                                $mp . "\n"
                            );
                        }

                        $contract->setNew();
                        $contract->team_id = $teams[BaseFcModel::LANG_EN];
                        $contract->person_id = $players[BaseFcModel::LANG_EN];
                        $contract->save();

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
     * @return array
     *
     * @throws CException
     */
    private function savePlayer($p, $amplua = null)
    {
        if (isset($this->players[$p->id])) {
            return $this->players[$p->id];
        }

        $player = new FcPerson();
        //$player->importId   = $p->id;
        $player->writeFiles = $this->writeFiles;
        $player->filesUrl = Players::PHOTO_URL;
        $player->setFileParams($p->id);
        $player->setFileParams($p->id, FcPerson::FILE_LIST, 0, FcPerson::FILE_FIELD_LIST);
        $player->setFileParams($p->id, FcPerson::FILE_INFORMER, 0, FcPerson::FILE_FIELD_INFORMER);
        $player->firstname   = $p->first_name;
        $player->lastname    = $p->surname;
        $player->middlename  = $p->patronymic;
        $player->birthday    = $p->borned;
        $p_attrs = [
            'firstname'  => $player->firstname,
            'lastname'   => $player->lastname,
            'middlename' => $player->middlename,
            'birthday'   => $player->birthday
        ];

        // переносили уже
        if ($exists_player = FcPerson::model()->findByAttributes($p_attrs, new CDbCriteria(['order' => 'id']))) {
            $player->setOwner = $player->setMultilang = false;
            $player->setIsNewRecord(false);
            $player->setAttributes($exists_player->getAttributes());
            $player->id = $exists_player->id;
            // переделываем файлы
            $player->save();

            $ids = [BaseFcModel::LANG_RU => $exists_player->getId(), BaseFcModel::LANG_EN => $player->getPairId()];
            $this->players[$p->id] = $ids;

            return $ids;
        }

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

        $this->players[$p->id][BaseFcModel::LANG_RU] = $ru_id = (int) $player->id;
        $player->setNew();
        $player->fileParams = $fileparams;
        $player->save();
        $this->players[$p->id][BaseFcModel::LANG_EN] = $en_id = (int) $player->id;
        $ids = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];

        $this->donePlayers++;
        $this->progress();

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

        return $ids;
    }

    /**
     * Сохранение команды.
     *
     * @param Teams  $t
     * @param string $staff
     *
     * @return array
     *
     * @throws CException                                                                                 `
     */
    private function saveTeam($t, $staff)
    {
        if (!empty($this->teams[$t->id][$staff])) {
            return $this->teams[$t->id][$staff];
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
        $t_attrs = ['title' => $team->title, 'city' => $team->city, 'staff' => $team->staff];

        // переносили уже
        if ($exists_team = FcTeams::model()->findByAttributes($t_attrs)) {
            $team->setOwner = $team->setMultilang = false;
            $team->setIsNewRecord(false);
            $team->setAttributes($exists_team->getAttributes());
            $team->id = $exists_team->id;
            // переделываем файлы
            $team->save();

            $ids = [BaseFcModel::LANG_RU => $exists_team->getId(), BaseFcModel::LANG_EN => $team->getPairId()];
            $this->teams[$t->id][$staff] = $ids;

            return $ids;
        }
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

        $this->saveTags(
            self::TAGS_TEAM,
            $t->id,
            [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id],
            TagsCategories::TEAMS,
            implode(' ', [$team->title, $team->staff, '(' . $team->city . ')'])
        );

        return $this->teams[$t->id][$staff];
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
                'select'    => ['id', 'title', 'info', 'region', 'country'],
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
        $that = $this;
        $save_stat = function ($s, $teamId, $playerId, $langId) use ($that) {
            // пропускаем отсуствующие сезоны
            if (empty($that->seasons[$s->season][$langId])    ||
                empty($that->champs[$s->tournament][$langId])) {
                return false;
            }

            // пропускаем левую статистику
            $stat = FcPersonstat::model()->exists(
                new CDbCriteria(
                    [
                        'condition' => 'person_id=:player_id AND team_id=:team_id AND season_id=:season_id AND ' .
                            'championship_id=:champ_id',
                        'params' => [
                            ':player_id' => $playerId,
                            ':team_id'   => $teamId,
                            ':season_id' => $that->seasons[$s->season][$langId],
                            ':champ_id'  => $that->champs[$s->tournament][$langId]
                        ]
                    ]
                )
            );

            if ($stat) {
                return false;
            }

            $stat = new FcPersonstat();
            $stat->person_id        = $playerId;
            $stat->team_id          = $teamId;
            $stat->season_id        = $this->seasons[$s->season][$langId];
            $stat->championship_id  = $this->champs[$s->tournament][$langId];
            $stat->gamecount        = $s->played;
            $stat->startcount       = $s->begined;
            $stat->benchcount       = $s->wentin;
            $stat->replacementcount = $s->wentout;
            $stat->goalcount        = $s->goals;
            $stat->assistcount      = $s->helps;
            $stat->yellowcount      = $s->warnings;
            $stat->redcount         = $s->removed;
            $stat->playtime         = $s->timeplayed;

            if (!$stat->save()) {
                throw new CException(
                    'Player statistic not created.' . "\n" .
                    var_export($stat->getErrors(), true) . "\n" .
                    $s . "\n"
                );
            }

            return true;
        };

        foreach ($this->players as $p_id => $players) {
            $p = Players::model()->findByPk($p_id);

            foreach ($p->stat as $s) {
                $teams = $this->getTrueTeam($s->team, $s->tournament);
                $save_stat($s, $teams[BaseFcModel::LANG_RU], $players[BaseFcModel::LANG_RU], BaseFcModel::LANG_RU);
                $save_stat($s, $teams[BaseFcModel::LANG_EN], $players[BaseFcModel::LANG_EN], BaseFcModel::LANG_EN);

                $this->donePlayerStats++;
                $this->progress();
            }
        }
    }

    /**
     * Cохранение статистики команды.
     *
     * @throws CException
     */
    private function saveTeamStat()
    {
        $that = $this;
        $save_stat = function ($s, $teamId, $langId) use ($that) {
            // пропускаем левую статистику
            $stat = FcTeamstat::model()->exists(
                new CDbCriteria(
                    [
                        'condition' => 'team_id=:team_id AND season_id=:season_id AND stage_id=:stage_id',
                        'params' => [
                            ':team_id'   => $teamId,
                            ':season_id' => $that->seasons[$s->season][$langId],
                            ':stage_id'  => $that->stages[$s->stage][$langId],
                        ]
                    ]
                )
            );

            if ($stat) {
                return false;
            }

            $stat = new FcTeamstat();
            $stat->team_id       = $teamId;
            $stat->season_id     = $this->seasons[$s->season][$langId];
            $stat->stage_id      = $this->stages[$s->stage][$langId];
            $stat->gamecount     = $s->played;
            $stat->wincount      = $s->won;
            $stat->drawcount     = $s->drawn;
            $stat->losscount     = $s->lost;
            $stat->goalsconceded = $s->goalsfor;
            $stat->goals         = $s->goalsagainst;
            $stat->score         = $s->points;
            $stat->place         = (int) $s->ord;

            if (!$stat->save()) {
                throw new CException(
                    'Team statistic not created.' . "\n" .
                    var_export($stat->getErrors(), true) . "\n" .
                    $s . "\n"
                );
            }

            return true;
        };

        foreach ($this->teams as $t_id => $team_id) {
            $t = Teams::model()->findByPk($t_id);

            foreach ($t->stat as $s) {
                // пропускаем отсуствующие сезоны
                if (empty($this->seasons[$s->season])    ||
                    empty($this->champs[$s->tournament]) ||
                    empty($this->stages[$s->stage])) {
                    continue;
                }

                $teams = $this->getTrueTeam($s->team, $s->tournament);
                $save_stat($s, $teams[BaseFcModel::LANG_RU], BaseFcModel::LANG_RU);
                $save_stat($s, $teams[BaseFcModel::LANG_EN], BaseFcModel::LANG_EN);

                $this->doneTeamStats++;
                $this->progress();
            }
        }
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
        $tag = new Tags();
        $tag->category_id = $categoryId;
        $tag->name = substr(preg_replace('/(?!-)[\W]+/', '_', Utils::rus2lat($title)), 0, 255);
        $tag->title = $title . '_' .BaseFcModel::LANG_RU . '_' . rand(0, 500);
        $tag->publish = 1;
        $tag->priority = 0;

        if (!$tag->save()) {
            throw new CException('Tag not created.' . "\n" . var_export($tag->getErrors(), true) . "\n");
        }

        $ru_id = $tag->getId();
        $this->saveTagLinks($ru_id, $newEntities[BaseFcModel::LANG_RU]);
        $tag->setNew();
        $tag->title = $title . '_' .BaseFcModel::LANG_EN . '_' . rand(0, 500);
        $tag->save();
        $en_id = $tag->getId();
        $this->saveTagLinks($en_id, $newEntities[BaseFcModel::LANG_EN]);

        $this->tags[$entity][$entityId] = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];

        $entity == self::TAGS_TEAM ? $this->doneTeamTags++ : $this->donePlayerTags++;
        $this->progress();

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
    private function getTrueTeam ($teamId, $champId)
    {
        return Tournaments::isJunior($champId)
            ? $this->teams[$teamId][FcTeams::JUNIOR]
            : $this->teams[$teamId][FcTeams::MAIN];
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
            $this->doneContracts * 2,
            $this->donePlayerStats,
            $this->donePlayerStats * 2,
            $this->doneTeamStats,
            $this->doneTeamStats * 2,
            $this->doneTeamTags,
            $this->doneTeamTags * 2,
            $this->donePlayerTags,
            $this->donePlayerTags * 2
        );
    }
}
