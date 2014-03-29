<?php

/**
 * Конвертер игроков.
 *
 * @package    converter
 * @subpackage person
 * @author     rudnik <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class PlayersConverter extends PersonsConverter implements IConverter
{
    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $criteria = new CDbCriteria([
            'select' => [
                'id', 'amplua', 'citizenship', 'resident', 'bio', 'surname', 'first_name', 'patronymic', 'nickname',
                'borned', 'height', 'weight', 'achivements'
            ],
            'order'  => 'id'
        ]);
        $src_players = new Players();

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
                    'Person not created.' . "\n" .
                    var_export($person->getErrors(), true) . "\n" .
                    $player . "\n"
                );
            }
        }
    }
}
