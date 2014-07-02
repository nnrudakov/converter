<?php

/**
 * Конвертер контрактов, персон и команд.
 *
 * @package    converter
 * @subpackage contracts
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
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rTeams: %d (%d). Players: %d (%d). Contracts: %d (%d). Players statistics: %d (%d). Teams statistics: %d (%d).";

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
     * Инициализация.
     */
    public function __construct()
    {
        $this->teamsFile   = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->playersFile = Yii::getPathOfAlias('accordance') . '/players.php';

        // сезоны и чемпионаты уже должны быть перенесены
        $cc = new ChampsConverter();
        $this->seasons = $cc->getSeasons();
        $this->champs  = $cc->getChamps();
        $this->stages  = $cc->getStages();
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
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

        ksort($this->players);
        ksort($this->teams);
        file_put_contents($this->playersFile, sprintf(self::FILE_ACCORDANCE, var_export($this->players, true)));
        file_put_contents($this->teamsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->teams, true)));
    }

    public function getTeams()
    {
        return file_exists($this->teamsFile) ? include $this->teamsFile : [];
    }

    public function getPlayers()
    {
        return file_exists($this->playersFile) ? include $this->playersFile : [];
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
        $player->importId   = $p->id;
        $player->writeFiles = $this->writeFiles;
        $player->filesUrl = Players::PHOTO_URL;
        $player->setFileParams($p->id);
        $player->setFileParams($p->id, FcPerson::FILE_LIST, 0, FcPerson::FILE_FIELD_LIST);
        $player->setFileParams($p->id, FcPerson::FILE_INFORMER, 0, FcPerson::FILE_FIELD_INFORMER);
        $player->firstname   = $p->first_name;
        $player->lastname    = $p->surname;
        $player->middlename  = $p->patronymic;
        $player->birthday    = $p->borned;
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

        $this->donePlayers++;
        $this->progress();

        return [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];
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
        if ($staff == FcTeams::MAIN) {
            $team->importId = $t->id;
        }
        $team->writeFiles = $this->writeFiles;
        $team->filesUrl = Teams::PHOTO_URL;
        $team->setFileParams($t->id);
        $team->setFileParams($t->id, FcTeams::FILE_LOGO_SMALL, 0, FcTeams::FILE_FIELD_LOGO_SMALL);
        $team->setFileParams($t->id, FcTeams::FILE_LOGO_BIG, 0, FcTeams::FILE_FIELD_LOGO_BIG);
        $team->title   = $t->title;
        $team->info    = Utils::clearText($t->info);
        $team->city    = $t->region;
        $team->staff   = $staff;
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
            if (empty($this->seasons[$langId][$s->season])    ||
                empty($this->champs[$langId][$s->tournament]) ||
                empty($this->teams[$langId][$s->team])) {
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
                            ':season_id' => $this->seasons[$langId][$s->season],
                            ':champ_id'  => $this->champs[$langId][$s->tournament]
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
            $stat->season_id        = $this->seasons[$langId][$s->season];
            $stat->championship_id  = $this->champs[$langId][$s->tournament];
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
                            ':season_id' => $this->seasons[$langId][$s->season],
                            ':stage_id'  => $this->stages[$langId][$s->stage],
                        ]
                    ]
                )
            );

            if ($stat) {
                return false;
            }

            $stat = new FcTeamstat();
            $stat->team_id       = $teamId;
            $stat->season_id     = $this->seasons[$langId][$s->season];
            $stat->stage_id      = $this->stages[$langId][$s->stage];
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
            $this->doneTeamStats * 2
        );
    }
}
