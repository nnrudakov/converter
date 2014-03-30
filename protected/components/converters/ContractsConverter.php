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
        $teams   = include $this->teamsFile;
        $players = include $this->playersFile;

        $criteria = new CDbCriteria(
            [
                'select'    => ['id', 'team', 'player', 'date_from', 'date_to', 'number'],
                'condition' => 'team!=0',
                'order'     => 'id'
            ]
        );
        $src_contracts = new PlayersContracts();

        foreach ($src_contracts->findAll($criteria) as $c) {
            $t = Teams::model()->findByPk($c->team);
            $p = Players::model()->findByPk($c->player);

            if (!$t || !$p) {
                echo 'Current team "' . $c->team . '" or player "' . $c->player .'" not found.' . "\n";
                continue;
            }

            if (!isset($teams[$t->id]) || !isset($players[$p->id])) {
                echo 'New team "' . $c->team . '" or player "' . $c->player .'" not found.' . "\n";
                continue;
            }

            $contract = new FcContracts();
            $contract->team_id   = $teams[$c->team];
            $contract->person_id = $players[$c->player];
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
        $teams   = include $this->teamsFile;
        $persons = include $this->personsFile;

        $criteria = new CDbCriteria(
            [
                'select'    => ['id', 'person', 'team', 'position', 'datefrom', 'dateto'],
                'condition' => 'person!=0',
                'order'     => 'id'
            ]
        );
        $src_contracts = new PersonsContracts();

        foreach ($src_contracts->findAll($criteria) as $c) {
            $t = Teams::model()->findByPk($c->team);
            $p = Persons::model()->findByPk($c->person);

            if (!$t || !$p) {
                echo 'Current team "' . $c->team . '" or person "' . $c->person .'" not found.' . "\n";
                continue;
            }

            if (!isset($teams[$t->id]) || !isset($players[$p->id])) {
                echo 'New team "' . $c->team . '" or person "' . $c->person .'" not found.' . "\n";
                continue;
            }

            $contract = new FcContracts();
            $contract->team_id   = $teams[$c->team];
            $contract->person_id = $persons[$c->person];
            $contract->fromtime  = $c->datefrom;
            $contract->untiltime = $c->dateto;
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
}
