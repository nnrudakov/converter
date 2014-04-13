<?php

/**
 * Файл, бля, для чего?
 *
 * @package    converter
 * @subpackage contracts
 * @author     Nikolaj Rudakov <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class ContractsConverter implements IConverter
{
    /**
     * Сущности для переноса.
     *
     * @var string
     */
    private $entity = null;

    /**
     * Соотвествие текущих команд новым.
     *
     * @var Teams[]|FcTeams[]
     */
    private $teams = [];

    /**
     * Соотвествие персон.
     *
     * @var Players[]|Persons[]|FcPerson[]
     */
    private $persons = [];

    /**
     * Файл соответствий текущих идентификаторов игроков новым.
     *
     * @var string
     */
    private $playersFile = '';

    /**
     * Файл соответствий текущих идентификаторов персон новым.
     *
     * @var string
     */
    private $personsFile = '';

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
     *
     * @throws CException
     */
    public function __construct($entity = null)
    {
        $this->entity = $entity;
        $this->teamsFile   = __DIR__ . '/teams.php';
        $this->playersFile = __DIR__ . '/players.php';
        $this->personsFile = __DIR__ . '/persons.php';

        if (!file_exists($this->teamsFile)) {
            throw new CException('File with teams does not exists.');
        }

        if ((!$this->entity || 'players' == $this->entity) && !file_exists($this->playersFile)) {
            throw new CException('File with players does not exists.');
        }

        if ((!$this->entity || 'persons' == $this->entity) && !file_exists($this->personsFile)) {
            throw new CException('File with persons does not exists.');
        }
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
                'order'  => 't.id'
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
                'select' => ['id', 'team', 'player', 'date_from', 'date_to', 'staff', 'number'],
                'with'   => ['personTeam', 'personPerson'],
                'order'  => 't.id'
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
     * @param Players $p
     * @param string  $profile
     * @param string  $amplua
     *
     * @return FcPerson $person
     *
     * @throws CException
     */
    private function savePerson($p, $profile, $amplua)
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
            $person->setFileParams(
                $p->id,
                $profile == PersonsConverter::PROFILE_PLAYER ? FcPerson::FILE_PLAYER : FcPerson::FILE_PERSON
            );
            $person->firstname  = $p->first_name;
            $person->lastname   = $p->surname;
            $person->middlename = $p->patronymic;
            $person->birthday   = $p->borned;
            $person->country    = $p->citizenship;
            $person->resident   = $p->resident;
            $person->biograpy   = $p->bio;
            $person->profile    = $profile;
            $person->progress   = $p->achivements;
            $person->nickname   = $p->nickname;
            $person->height     = $p->height;
            $person->weight     = $p->weight;
            $person->amplua     = $amplua;

            if (!$person->save()) {
                throw new CException(
                    'Player not created.' . "\n" .
                    var_export($person->getErrors(), true) . "\n" .
                    $p . "\n"
                );
            }
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
            $team->setFileParams($t->id);
            $team->title = $t->title;
            $team->info  = $t->info;
            $team->city  = $t->region;
            $team->staff = $staff;

            if (!$team->save()) {
                throw new CException(
                    'Team not created.' . "\n" .
                    var_export($team->getErrors(), true) . "\n" .
                    $t . "\n"
                );
            }
        }

        return $team;
    }
}
