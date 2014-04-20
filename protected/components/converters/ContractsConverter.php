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
     * Сохранить файлы на диск.
     *
     * @var bool
     */
    public $writeFiles = false;

    /**
     * Сущности для переноса.
     *
     * @var string
     */
    private $entity = null;

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
     *
     * @param string $entity Персоны (если не указано, то все):
     *                       <ul>
     *                         <li>players;</li>
     *                         <li>persons.</li>
     *                       </ul>
     */
    public function __construct($entity = null)
    {
        $this->entity = $entity;
        $this->teamsFile   = Yii::getPathOfAlias('accordance') . '/teams.php';
        $this->playersFile = Yii::getPathOfAlias('accordance') . '/players.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        if (!$this->entity || 'players' == $this->entity) {
            $this->convertPlayers();
        }

        if (!$this->entity || 'persons' == $this->entity) {
            $this->convertPersons();
        }

        $format  = "<?php\n\nreturn %s;\n";
        file_put_contents($this->playersFile, sprintf($format, var_export($this->players, true)));
        file_put_contents($this->teamsFile, sprintf($format, var_export($this->teams, true)));
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
     * Перенос контрактов игроков.
     */
    private function convertPlayers()
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
                PersonsConverter::PROFILE_PLAYER,
                isset(PersonsConverter::$ampluas[$c->playerPlayer->amplua])
                    ? PersonsConverter::$ampluas[$c->playerPlayer->amplua]
                    : null
            );
            $team = $this->saveTeam($c->playerTeam, $c->staff ? TeamsConverter::JUNIOR : TeamsConverter::MAIN);

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
    }

    /**
     * Перенос контрактов персон.
     */
    private function convertPersons()
    {
        $criteria = new CDbCriteria(
            [
                'select' => ['id', 'team', 'person', 'datefrom', 'dateto', 'position'],
                'with'   => ['personTeam', 'personPerson'],
                'order'  => 't.person'
            ]
        );
        $src_contracts = new PersonsContracts();

        foreach ($src_contracts->findAll($criteria) as $c) {
            $person = $this->savePerson(
                $c->personPerson,
                isset(PersonsConverter::$profiles[$c->personPerson->path])
                    ? PersonsConverter::$profiles[$c->personPerson->path]
                    : null,
                null
            );
            $team = $this->saveTeam($c->personTeam, null);

            $contract = new FcContracts();
            $contract->team_id   = $team->id;
            $contract->person_id = $person->id;
            $contract->fromtime  = $c->datefrom;
            $contract->untiltime = $c->dateto;
            $contract->number    = 0;
            $contract->position  = $c->position;

            if (!$contract->save()) {
                throw new CException(
                    'Person\'s contract not created.' . "\n" .
                    var_export($contract->getErrors(), true) . "\n" .
                    $c . "\n"
                );
            }
        }
    }

    /**
     * Сохранение персоны.
     *
     * @param Players|Persons $p
     * @param string          $profile
     * @param string          $amplua
     *
     * @return FcPerson $person
     *
     * @throws CException
     */
    private function savePerson($p, $profile, $amplua)
    {
        $is_player = PersonsConverter::PROFILE_PLAYER;
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
            $person->firstname  = $p->first_name;
            $person->lastname   = $p->surname;
            $person->middlename = $p->patronymic;
            $person->birthday   = $p->borned;
            $person->country    = $p->citizenship;
            $person->biograpy   = $p->bio;
            $person->profile    = $profile;
            $person->progress   = $p->achivements;

            if ($p instanceof Players) {
                $person->resident   = $p->resident;
                $person->nickname   = $p->nickname;
                $person->height     = $p->height;
                $person->weight     = $p->weight;
                $person->amplua     = $amplua;
            } else {
                $person->post = $p->post;
            }
        }

        $person->writeFiles = $this->writeFiles;
        $person->filesUrl = $is_player ? Players::PHOTO_URL : Persons::PHOTO_URL;
        $person->setFileParams($p->id, $profile == $is_player ? FcPerson::FILE_PLAYER : FcPerson::FILE_PERSON);

        if (!$person->save()) {
            throw new CException(
                'Player not created.' . "\n" .
                var_export($person->getErrors(), true) . "\n" .
                $p . "\n"
            );
        }

        if ($is_player) {
            $this->players[$p->id] = $person->id;
        }

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
            $team->title = $t->title;
            $team->info  = $t->info;
            $team->city  = $t->region;
            $team->staff = $staff;
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

        $this->teams[$t->id] = $team->id;

        return $team;
    }
}
