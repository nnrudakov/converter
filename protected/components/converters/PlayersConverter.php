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
    private $progressFormat = "\rTeams: %d. Players: %d. Contracts: %d. Players statistics: %d";

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
    private $doneStats = 0;

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->teamsFile   = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->playersFile = Yii::getPathOfAlias('accordance') . '/players.php';

        $cc = new ChampsConverter();
        $this->seasons = $cc->getSeasons();
        $this->champs  = $cc->getChamps();
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

        foreach ($src_contracts->findAll($criteria) as $c) {
            $player = $this->savePlayer(
                $c->playerPlayer,
                isset(self::$ampluas[$c->playerPlayer->amplua]) ? self::$ampluas[$c->playerPlayer->amplua] : null
            );
            $team = $this->saveTeam($c->playerTeam, $c->staff ? FcTeams::JUNIOR : FcTeams::MAIN);

            $contract = new FcContracts();
            $contract->team_id   = $team->id;
            $contract->person_id = $player->id;
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

            $this->doneContracts++;
            $this->progress();
        }

        ksort($this->players);
        ksort($this->teams);
        file_put_contents($this->playersFile, sprintf(self::FILE_ACCORDANCE, var_export($this->players, true)));
        file_put_contents($this->teamsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->teams, true)));

        $this->saveStat();
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
     * Сохранение персоны.
     *
     * @param Players $p
     * @param string  $amplua
     *
     * @return FcPerson $player
     *
     * @throws CException
     */
    private function savePlayer($p, $amplua)
    {
        $player = FcPerson::model()->findByAttributes(
            [
                'firstname'  => $p->first_name,
                'lastname'   => $p->surname,
                'middlename' => $p->patronymic,
                'birthday'   => $p->borned
            ]
        );

        if (!is_null($player)) {
            return $player;
        }

        $player = new FcPerson();
        $player->writeFiles = $this->writeFiles;
        $player->filesUrl = Players::PHOTO_URL;
        $player->setFileParams($p->id, FcPerson::FILE);
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

        if (!$player->save()) {
            throw new CException(
                'Player not created.' . "\n" .
                var_export($player->getErrors(), true) . "\n" .
                $p . "\n"
            );
        }

        $this->donePlayers++;
        $this->progress();

        $this->players[$p->id] = (int) $player->id;

        return $player;
    }

    /**
     * Сохранение команды.
     *
     * @param Teams  $t
     * @param string $staff
     *
     * @return FcTeams $team
     *
     * @throws CException
     */
    private function saveTeam($t, $staff)
    {
        $team = FcTeams::model()->findByAttributes(
            [
                'title' => $t->title,
                'city'  => $t->region,
                'staff' => $staff
            ]
        );

        if (!is_null($team)) {
            return $team;
        }

        $team = new FcTeams();
        $team->writeFiles = $this->writeFiles;
        $team->filesUrl = Teams::PHOTO_URL;
        $team->setFileParams($t->id);
        $team->title   = $t->title;
        $team->info    = $t->info;
        $team->city    = $t->region;
        $team->staff   = $staff;
        $team->country = $t->country;

        if (!$team->save()) {
            throw new CException(
                'Team not created.' . "\n" .
                var_export($team->getErrors(), true) . "\n" .
                $t . "\n"
            );
        }

        $this->doneTeams++;
        $this->progress();
        $this->teams[$t->id] = (int) $team->id;

        return $team;
    }

    /**
     * Сохранение статистики игрока.
     *
     * @throws CException
     */
    private function saveStat()
    {
        foreach ($this->players as $p_id => $player_id) {
            $p = Players::model()->findByPk($p_id);

            foreach ($p->stat as $s) {
                if (empty($this->seasons[$s->season])    ||
                    empty($this->champs[$s->tournament]) ||
                    empty($this->teams[$s->team])) {
                    continue;
                }

                $stat = FcPersonstat::model()->exists(
                    new CDbCriteria([
                        'condition' => 'person_id=:player_id AND team_id=:team_id AND season_id=:season_id AND ' .
                            'championship_id=:champ_id',
                        'params' => [
                            ':player_id' => $player_id,
                            ':team_id'   => $this->teams[$s->team],
                            ':season_id' => $this->seasons[$s->season],
                            ':champ_id'  => $this->champs[$s->tournament]
                        ]
                    ])
                );

                if ($stat) {
                    continue;
                }

                $stat = new FcPersonstat();
                $stat->person_id        = $player_id;
                $stat->team_id          = $this->teams[$s->team];
                $stat->season_id        = $this->seasons[$s->season];
                $stat->championship_id  = $this->champs[$s->tournament];
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
                        'Statistic not created.' . "\n" .
                        var_export($stat->getErrors(), true) . "\n" .
                        $s . "\n"
                    );
                }

                $this->doneStats++;
                $this->progress();
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneTeams, $this->donePlayers, $this->doneContracts, $this->doneStats);
    }
}
