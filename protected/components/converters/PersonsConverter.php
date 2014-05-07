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
     * Запуск преобразований.
     */
    public function convert()
    {
        $criteria = new CDbCriteria();
        $criteria->select = [
            'id', 'citizenship', 'surname', 'first_name', 'patronymic', 'bio', 'borned', 'post', 'path', 'achivements'
        ];
        $criteria->condition = 'surname!=\'\' AND first_name!=\'\' AND patronymic!=\'\'';
        $criteria->order = 'id';
        $src_persons = new Persons();

        foreach ($src_persons->findAll($criteria) as $p) {
            $person = new FcPerson();
            $person->firstname  = $p->first_name;
            $person->lastname   = $p->surname;
            $person->middlename = $p->patronymic;
            $person->birthday   = $p->borned;
            $person->country    = $p->citizenship;
            $person->biograpy   = Utils::clearText($p->bio);
            $person->profile    = isset(self::$profiles[$p->path]) ? self::$profiles[$p->path] : null;
            $person->progress   = Utils::clearText($p->achivements);
            $person->post       = $p->post;

            if (!$person->save()) {
                throw new CException(
                    'Person not created.' . "\n" .
                    var_export($person->getErrors(), true) . "\n" .
                    $p . "\n"
                );
            }
        }
    }

    /**
     * Перенос игроков.
     *
     * @return bool
     *
     * @throws CException
     */
    private function convertPlayers()
    {
        $criteria = new CDbCriteria([
            'select' => [
                'id', 'amplua', 'citizenship', 'resident', 'bio', 'surname', 'first_name', 'patronymic', 'nickname',
                'borned', 'height', 'weight', 'achivements'
            ],
            'order'  => 'id'
        ]);
        $src_players = new Players();
        $players = [];

        foreach ($src_players->findAll($criteria) as $player) {
            if (empty($player->first_name) && empty($player->surname) && empty($player->patronymic)) {
                continue;
            }

            $person = new FcPerson();
            $person->firstname  = $player->first_name;
            $person->lastname   = $player->surname;
            $person->middlename = $player->patronymic;
            $person->birthday   = $player->borned;
            $person->country    = $player->citizenship;
            $person->resident   = $player->resident;
            $person->biograpy   = $player->bio;
            $person->profile    = self::PROFILE_PLAYER;
            $person->progress   = $player->achivements;
            $person->nickname   = $player->nickname;
            $person->height     = $player->height;
            $person->weight     = $player->weight;
            $person->amplua     = isset(self::$ampluas[$player->amplua]) ? self::$ampluas[$player->amplua] : null;

            if (!$person->save()) {
                throw new CException(
                    'Player not created.' . "\n" .
                    var_export($person->getErrors(), true) . "\n" .
                    $player . "\n"
                );
            }

            $players[$player->id] = $person->id;
        }

        return true;
    }
}
