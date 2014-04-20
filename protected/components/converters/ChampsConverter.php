<?php

/**
 * Конвертер сезонов, чемпионато и этапов.
 *
 * @package    converter
 * @subpackage contracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ChampsConverter implements IConverter
{
    /**
     * Соотвествие текущих сезонов новым.
     *
     * @var array
     */
    private $seasons = [];

    /**
     * Соотвествие чемпионатов.
     *
     * @var array
     */
    private $champs = [];

    /**
     * Соотвествие этапов.
     *
     * @var array
     */
    private $stages = [];

    /**
     * Файл соответствий текущих идентификаторов сезонов новым.
     *
     * @var string
     */
    private $seasonsFile = '';

    /**
     * Файл соответствий текущих идентификаторов чемпионатов новым.
     *
     * @var string
     */
    private $champsFile = '';

    /**
     * Файл соответствий текущих идентификаторов этапов новым.
     *
     * @var string
     */
    private $stagesFile = '';

    /**
     * Инициализация.
     *
     * @throws CException
     */
    public function __construct()
    {
        $this->seasonsFile = Yii::getPathOfAlias('accordance') . '/seasons.php';
        $this->champsFile  = Yii::getPathOfAlias('accordance') . '/champs.php';
        $this->stagesFile  = Yii::getPathOfAlias('accordance') . '/stages.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->convertSeasons();

        file_put_contents($this->seasonsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->seasons, true)));
    }

    /**
     * Перенос сезонов.
     */
    private function convertSeasons()
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['id', 'title', 'description', 'dts', 'dte'];
        $criteria->order = 'id';
        $src_seasons = new Seasons();

        foreach ($src_seasons->findAll($criteria) as $s) {
            $season = new FcSeason();
            $season->title   = $s->title;
            $season->description = $s->description;
            $season->fromtime  = $s->dts;
            $season->untiltime = $s->dte;

            if (!$season->save()) {
                throw new CException(
                    'Season not created.' . "\n" .
                    var_export($season->getErrors(), true) . "\n" .
                    $s . "\n"
                );
            }

            $this->seasons[$s->id] = (int) $season->id;
        }
    }

    /**
     * Перенос чемпионатов.
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
     * Перенос этапов.
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
        $person->filesUrl = $profile == PersonsConverter::PROFILE_PLAYER ? Players::PHOTO_URL : Persons::PHOTO_URL;
        $person->setFileParams(
            $p->id,
            $profile == PersonsConverter::PROFILE_PLAYER ? FcPerson::FILE_PLAYER : FcPerson::FILE_PERSON
        );

        if (!$person->save()) {
            throw new CException(
                'Player not created.' . "\n" .
                var_export($person->getErrors(), true) . "\n" .
                $p . "\n"
            );
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

        return $team;
    }
}
