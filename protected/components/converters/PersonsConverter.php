<?php

/**
 * Общий класс переноса персон.
 *
 * @package    converter
 * @subpackage persons
 * @author     rudnik <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
abstract class PersonsConverter
{
    /**
     * Профиль тренеров.
     *
     * @var string
     */
    const PROFILE_COACH = 'тренерский штаб';

    /**
     * Профиль игроков.
     *
     * @var string
     */
    const PROFILE_PLAYER = 'футболист';

    /**
     * Профиль администраторов.
     *
     * @var string
     */
    const PROFILE_ADMINS = 'административная служба';

    /**
     * Профиль медиков.
     *
     * @var string
     */
    const PROFILE_MEDIC = 'медицинская служба';

    /**
     * Профиль прессы.
     *
     * @var string
     */
    const PROFILE_PRESS = 'пресс-служба';

    /**
     * Профиль селекционеров.
     *
     * @var string
     */
    const PROFILE_SELECT = 'селекционная служба';

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
    const AMPLUA_NEW_FORWARD = 'нападающий';

    /**
     * Новое амплуа защитника.
     *
     * @var string
     */
    const AMPLUA_NEW_BACK = 'защитник';

    /**
     * Новое амплуа вратаря.
     *
     * @var string
     */
    const AMPLUA_NEW_GOALKEEPER = 'вратарь';

    /**
     * Новое амплуа полузащитника.
     *
     * @var string
     */
    const AMPLUA_NEW_HALFBACK = 'полузащитник';

    /**
     * Новое амплуа пз/нп.
     *
     * @var string
     */
    const AMPLUA_NEW_PZNP = 'пз/нп';

    /**
     * Новое амплуа полевого игрока.
     *
     * @var string
     */
    const AMPLUA_NEW_FIELD = 'полевой игрок';

    /**
     * Соответствие сежду текущими и новыми амплуа.
     *
     * @var array
     */
    protected static $ampluas = [
        self::AMPLUA_CUR_FORWARD    => self::AMPLUA_NEW_FORWARD,
        self::AMPLUA_CUR_BACK       => self::AMPLUA_NEW_BACK,
        self::AMPLUA_CUR_GOALKEEPER => self::AMPLUA_NEW_GOALKEEPER,
        self::AMPLUA_CUR_HALFBACK   => self::AMPLUA_NEW_HALFBACK,
        self::AMPLUA_CUR_PZNP       => self::AMPLUA_NEW_PZNP,
        self::AMPLUA_CUR_FIELD      => self::AMPLUA_NEW_FIELD
    ];
}
