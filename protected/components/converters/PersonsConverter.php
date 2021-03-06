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
        '634003665154257148'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_LEADS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_LEADS_EN
        ],
        '634327702653514462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_SPORT_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_SPORT_EN
        ],
        '634327702802704462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_LAW_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_LAW_EN
        ],
        '634327702966294462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_SECURITY_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_SECURITY_EN
        ],
        '634327703093084462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_MARKET_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_MARKET_EN
        ],
        '634327703204764462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_TECH_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_TECH_EN
        ],
        '634327703371394462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::CLUB_MEDIC_RU,
            BaseFcModel::LANG_EN => PersonsCategories::CLUB_MEDIC_EN
        ],
        '634327703968554462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_COACHES_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_COACHES_EN
        ],
        '634327704292614462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_ADMIN_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_ADMIN_EN
        ],
        '634460819371416698'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_MEDIC_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_MEDIC_EN
        ],
        '634460819777869946'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_PRESS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_PRESS_EN
        ],
        '634460820642589405'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_SELECT_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_SELECT_EN
        ],
        '634080561412439670'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC_SELECT_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC_SELECT_EN
        ],
        '634378824976006501'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FCM_COACHES_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FCM_COACHES_EN
        ],
        '6340036650992571485' => [
            BaseFcModel::LANG_RU => PersonsCategories::FCM_PERSONS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FCM_PERSONS_EN
        ],
        '635091372580586616'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC2_COACHES_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC2_COACHES_EN
        ],
        '635091373206982443'  => [
            BaseFcModel::LANG_RU => PersonsCategories::FC2_PERSONS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::FC2_PERSONS_EN
        ],
        '634080560806044986'  => [
            BaseFcModel::LANG_RU => PersonsCategories::A_LEADS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::A_LEADS_EN
        ],
        '634327711464684462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::A_COACHES_RU,
            BaseFcModel::LANG_EN => PersonsCategories::A_COACHES_EN
        ],
        '634327711323574462'  => [
            BaseFcModel::LANG_RU => PersonsCategories::A_PERSONS_RU,
            BaseFcModel::LANG_EN => PersonsCategories::A_PERSONS_EN
        ]
    ];

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rPersons: %d (%d).";

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
            'id', 'citizenship', 'surname', 'first_name', 'patronymic', 'bio', 'borned', 'post', 'path', 'achivements',
            'ord'
        ];
        $criteria->condition = 'surname!=\'\' AND first_name!=\'\' AND patronymic!=\'\'';
        $criteria->order = 'id';
        $src_persons = new Persons();

        foreach ($src_persons->findAll($criteria) as $p) {
            list($persons, $exists) = $this->savePerson($p);
            //if (!$exists) {
                $this->saveData($p, $persons);
            //}
        }

        $criteria = new CDbCriteria();
        $criteria->condition = 'MOD(object_id, 2)=0';
        $criteria->order = 'object_id';
        foreach (PersonsCategoryObjects::model()->findAll($criteria) as $co) {
            if ($prev_co = PersonsCategoryObjects::model()->findByAttributes(['object_id' => $co->object_id - 1])) {
                $co->sort = $prev_co->sort;
                $co->save();
            }
        }
    }

    /**
     * Сохранение объекта.
     *
     * @param Persons $p
     *
     * @return array
     *
     * @throws CException
     */
    private function savePerson(Persons $p)
    {
        $person = new PersonsObjects();
        //$person->importId   = $p->id;
        $person->writeFiles = $this->writeFiles;
        $person->filesUrl = Persons::PHOTO_URL;
        $person->main_category_id = isset(self::$categories[$p->path])
            ? self::$categories[$p->path][BaseFcModel::LANG_RU]
            : PersonsCategories::NO_CAT;
        $person->setFileParams(
            $p->id,
            in_array(
                $person->main_category_id,
                [
                    PersonsCategories::CLUB_LEADS_RU, PersonsCategories::A_LEADS_RU,
                    PersonsCategories::CLUB_LEADS_EN, PersonsCategories::A_LEADS_EN
                ]
            ) ? PersonsObjects::FILE_LEADER : PersonsObjects::FILE
        );
        $person->setFileParams(
            $p->id,
            in_array(
                $person->main_category_id,
                [
                    PersonsCategories::CLUB_LEADS_RU, PersonsCategories::A_LEADS_RU,
                    PersonsCategories::CLUB_LEADS_EN, PersonsCategories::A_LEADS_EN
                ]
            ) ? PersonsObjects::FILE_LEADER_LIST : PersonsObjects::FILE_LIST,
            0,
            PersonsObjects::FILE_FIELD_LIST
        );
        $person->title = $p->first_name . ' ' . $p->patronymic . ' ' . $p->surname;
        $person->name = Utils::nameString($person->title);
        $person->lang_id = BaseFcModel::LANG_RU;
        $p_attrs = [
            'main_category_id' => $person->main_category_id,
            'name'             => $person->name,
            'lang_id'          => $person->lang_id
        ];

        // переносили уже
        if ($exists_person = PersonsObjects::model()->findByAttributes($p_attrs)) {
            $person->setOwner = $person->setMultilang = false;
            $person->setIsNewRecord(false);
            $person->setAttributes($exists_person->getAttributes());
            $person->object_id = $exists_person->object_id;
            $person->sort = $p->ord;
            // переделываем файлы
            $person->save();

            return [
                [BaseFcModel::LANG_RU => $exists_person->getId(), BaseFcModel::LANG_EN => $person->getPairId()],
                true
            ];
        }

        $person->content = Utils::clearText($p->bio);
        $person->publish = 1;
        $person->publish_date_on = date('Y-m-d H:i:s');
        $person->created = $person->publish_date_on;
        $person->sort = $p->ord;
        $fileparams = $person->fileParams;

        if (!$person->save()) {
            throw new CException(
                'Person not created.' . "\n" .
                var_export($person->getErrors(), true) . "\n" .
                $p . "\n"
            );
        }

        $ru_id = $person->getId();
        $person->setNew();
        $person->fileParams = $fileparams;
        $person->main_category_id = isset(self::$categories[$p->path])
            ? self::$categories[$p->path][BaseFcModel::LANG_EN]
            : PersonsCategories::NO_CAT;
        $person->save();
        $en_id = $person->getId();

        $this->donePersons++;
        $this->progress();

        return [[BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id], false];
    }

    /**
     * Сохранение свойств.
     *
     * @param Persons $p
     * @param array $personId
     *
     * @throws CException
     */
    private function saveData(Persons $p, $personId)
    {
        $set = new PersonsSets();
        $set = $set->findByPk(PersonsSets::SET);
        $exists_link = PersonsObjectSets::model()->findByPk(
            ['object_id' => $personId[BaseFcModel::LANG_RU], 'set_id' => $set->getId()]
        );

        if ($exists_link) {
            return true;
        }

        $object_set = new PersonsObjectSets();
        $object_set->object_id = $personId[BaseFcModel::LANG_RU];
        $object_set->set_id = $set->getId();
        $object_set->save();
        return true;
        $object_set->setNew();
        $object_set->object_id = $personId[BaseFcModel::LANG_EN];
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
                $object_data->object_id = $personId[BaseFcModel::LANG_RU];
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

                $object_data->object_id = $personId[BaseFcModel::LANG_EN];
                $object_data->setNew();
                $object_data->save();
                $object_value = new PersonsObjectDataText();
                $object_value->data_id = $object_data->getId();
                $object_value->data = $value;
            }
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->donePersons, $this->donePersons * 2);
    }
}
