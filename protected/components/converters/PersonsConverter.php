<?php

/**
 * Конвертер персон.
 *
 * @package    converter
 * @subpackage person
 * @author     rudnik <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class PersonsConverter implements IConverter
{
    /**
     * Сохранить файлы на диск.
     *
     * @var bool
     */
    public $writeFiles = false;

    /**
     * Соответствие между категориями.
     *
     * @var array
     */
    public static $categories = [
        '634003665154257148'  => PersonsCategories::CLUB_LEADS,
        '634327702653514462'  => PersonsCategories::CLUB_SPORT,
        '634327702802704462'  => PersonsCategories::CLUB_LAW,
        '634327702966294462'  => PersonsCategories::CLUB_SECURITY,
        '634327703093084462'  => PersonsCategories::CLUB_MARKET,
        '634327703204764462'  => PersonsCategories::CLUB_TECH,
        '634327703371394462'  => PersonsCategories::CLUB_MEDIC,
        '634327703968554462'  => PersonsCategories::FC_COACHES,
        '634327704292614462'  => PersonsCategories::FC_ADMIN,
        '634460819371416698'  => PersonsCategories::FC_MEDIC,
        '634460819777869946'  => PersonsCategories::FC_PRESS,
        '634460820642589405'  => PersonsCategories::FC_SELECT,
        '634080561412439670'  => PersonsCategories::FC_SELECT,
        '634378824976006501'  => PersonsCategories::FCM_COACHES,
        '6340036650992571485' => PersonsCategories::FCM_PERSONS,
        '635091372580586616'  => PersonsCategories::FC2_COACHES,
        '635091373206982443'  => PersonsCategories::FC2_PERSONS,
        '634080560806044986'  => PersonsCategories::A_LEADS,
        '634327711464684462'  => PersonsCategories::A_COACHES,
        '634327711323574462'  => PersonsCategories::A_PERSONS

    ];

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rPersons: %d.";

    /**
     * @var integer
     */
    private $donePersons = 0;

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        $criteria = new CDbCriteria();
        $criteria->select = [
            'id', 'citizenship', 'surname', 'first_name', 'patronymic', 'bio', 'borned', 'post', 'path', 'achivements'
        ];
        $criteria->condition = 'surname!=\'\' AND first_name!=\'\' AND patronymic!=\'\'';
        $criteria->order = 'id';
        $src_persons = new Persons();
        $sort = 1;

        foreach ($src_persons->findAll($criteria) as $p) {
            $person = $this->savePerson($p, $sort);
            $this->saveData($p, $person->getId());
            $this->donePersons++;
            $this->progress();
            $sort++;
        }
    }

    /**
     * Сохранение объекта.
     *
     * @param Persons $p
     * @param integer $sort
     *
     * @return PersonsObjects
     *
     * @throws CException
     */
    private function savePerson(Persons $p, $sort)
    {
        $person = new PersonsObjects();
        $person->writeFiles = $this->writeFiles;
        $person->filesUrl = Persons::PHOTO_URL;
        $person->setFileParams($p->id, PersonsObjects::FILE);
        $person->title = $p->first_name . ' ' . $p->patronymic . ' ' . $p->surname;
        $person->name = Utils::nameString($person->title);
        $person->main_category_id = isset(self::$categories[$p->path])
            ? self::$categories[$p->path]
            : PersonsCategories::NO_CAT;
        $person->lang_id = PersonsObjects::LANG;
        $person->content = Utils::clearText($p->bio);
        $person->publish = 1;
        $person->publish_date_on = date('Y-m-d H:i:s');
        $person->created = $person->publish_date_on;
        $person->sort = $sort;

        if (!$person->save()) {
            throw new CException(
                'Person not created.' . "\n" .
                var_export($person->getErrors(), true) . "\n" .
                $p . "\n"
            );
        }

        return $person;
    }

    /**
     * Сохранение свойств.
     *
     * @param Persons $p
     * @param integer $personId
     *
     * @throws CException
     */
    private function saveData(Persons $p, $personId)
    {
        $set = new PersonsSets();
        $set = $set->findByPk(PersonsSets::SET);
        $object_set = new PersonsObjectSets();
        $object_set->object_id = $personId;
        $object_set->set_id = $set->getId();
        $object_set->save();

        foreach ($set->properties as $prop) {
            switch ($prop->name) {
                case 'city':
                    $value = $p->citizenship;
                    break;
                case 'birthday':
                    $value = $p->borned;
                    break;
                case 'post':
                    $value = $p->post;
                    break;
                case 'progress':
                    $value = Utils::clearText($p->achivements);
                    break;
                default:
                    $value = '';
                    break;
            }

            if ($value) {
                $object_data = new PersonsObjectData();
                $object_data->object_id = $personId;
                $object_data->property_id = $prop->getId();

                if (!$object_data->save()) {
                    throw new CException(
                        'Person\'s data not created.' . "\n" .
                        var_export($object_data->getErrors(), true) . "\n"
                    );
                }

                $object_value = new PersonsObjectDataText();
                $object_value->data_id = $object_data->getId();
                $object_value->data = $value;

                if (!$object_value->save()) {
                    throw new CException(
                        'Person\'s value not created.' . "\n" .
                        var_export($object_value->getErrors(), true) . "\n"
                    );
                }
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->donePersons);
    }
}
