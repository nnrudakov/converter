<?php

/**
 * Конвертер контрактов, персон и команд.
 *
 * @package    converter
 * @subpackage contracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ContractsConverter implements IConverter
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
     * Инициализация.
     */
    public function __construct()
    {
        $this->teamsFile   = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->playersFile = Yii::getPathOfAlias('accordance') . '/players.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $criteria = new CDbCriteria(
            [
                'select' => ['id', 'team', 'player', 'date_from', 'date_to', 'staff', 'number'],
                'with'   => ['playerTeam', 'playerPlayer'],
                'order'  => 't.player'
            ]
        );
        $src_contracts = new PlayersContracts();

        foreach ($src_contracts->findAll($criteria) as $c) {
            $person = $this->savePerson(
                $c->playerPlayer,
                isset(self::$ampluas[$c->playerPlayer->amplua]) ? self::$ampluas[$c->playerPlayer->amplua] : null
            );
            $team = $this->saveTeam($c->playerTeam, $c->staff ? FcTeams::JUNIOR : FcTeams::MAIN);

            $contract = new FcContracts();
            $contract->team_id   = $team->id;
            $contract->person_id = $person->id;
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
        }

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
     * Сохранение персоны.
     *
     * @param Players|Persons $p
     * @param string          $amplua
     *
     * @return FcPerson $person
     *
     * @throws CException
     */
    private function savePerson($p, $amplua)
    {
        $person = FcPerson::model()->findByAttributes(
            [
                'firstname'  => $p->first_name,
                'lastname'   => $p->surname,
                'middlename' => $p->patronymic,
                'birthday'   => $p->borned
            ]
        );

        if (is_null($person)) {
            $person = new FcPerson();
            $person->firstname   = $p->first_name;
            $person->lastname    = $p->surname;
            $person->middlename  = $p->patronymic;
            $person->birthday    = $p->borned;
            $person->citizenship = $p->citizenship;
            $person->biograpy    = Utils::clearText($p->bio);
            $person->profile     = self::PROFILE_PLAYER;
            $person->progress    = Utils::clearText($p->achivements);
            $person->resident    = $p->resident;
            $person->nickname    = $p->nickname;
            $person->height      = $p->height;
            $person->weight      = $p->weight;
            $person->amplua      = $amplua;
        }

        $person->writeFiles = $this->writeFiles;
        $person->filesUrl = Players::PHOTO_URL;
        $person->setFileParams($p->id, FcPerson::FILE);

        if (!$person->save()) {
            throw new CException(
                'Player not created.' . "\n" .
                var_export($person->getErrors(), true) . "\n" .
                $p . "\n"
            );
        }

        $this->players[$p->id] = (int) $person->id;

        return $person;
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

        if (is_null($team)) {
            $team = new FcTeams();
            $team->title   = $t->title;
            $team->info    = $t->info;
            $team->city    = $t->region;
            $team->staff   = $staff;
            $team->country = $t->country;
        }

        $team->writeFiles = $this->writeFiles;
        $team->filesUrl = Teams::PHOTO_URL;
        $team->setFileParams($t->id);

        if (!$team->save()) {
            throw new CException(
                'Team not created.' . "\n" .
                var_export($team->getErrors(), true) . "\n" .
                $t . "\n"
            );
        }

        $this->teams[$t->id] = (int) $team->id;

        return $team;
    }
}
