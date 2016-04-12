<?php

/**
 * Модель таблицы "fc__persons__categories".
 *
 * Доступные поля таблицы "fc__persons__categories":
 *
 * @property string  $category_id      Идентификатор категории.
 * @property string  $parent_id        Идентификатор родительской категории.
 * @property integer $multilang_id     Id.
 * @property integer $lang_id          Идентификатор языка.
 * @property string  $name             Имя для URL.
 * @property string  $title            Заголовок категории.
 * @property string  $content          Описание категории.
 * @property integer $publish          Флаг публикации.
 * @property integer $share            Доступна подмодулям.
 * @property integer $sort             Порядок сортировки.
 * @property string  $meta_title       SEO заголовок.
 * @property string  $meta_description SEO описание.
 * @property string  $meta_keywords    SEO ключевые слова.
 *
 * Доступные отношения:
 * @property PersonsCategoryObjects[] $links Связка с объектами.
 *
 * @package    converter
 * @subpackage personscategories
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class PersonsCategories extends KitCategories
{
    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'persons';

    /**
     * @var integer
     */
    const MODULE_ID = 28;

    /**
     * Категории клуба.
     */
    const CLUB_LEADS_RU    = 6;
    const CLUB_SPORT_RU    = 7;
    const CLUB_LAW_RU      = 8;
    const CLUB_SECURITY_RU = 9;
    const CLUB_MARKET_RU   = 10;
    const CLUB_TECH_RU     = 11;
    const CLUB_MEDIC_RU    = 19;
    const CLUB_LEADS_EN    = 30;
    const CLUB_SPORT_EN    = 31;
    const CLUB_LAW_EN      = 32;
    const CLUB_SECURITY_EN = 33;
    const CLUB_MARKET_EN   = 34;
    const CLUB_TECH_EN     = 35;
    const CLUB_MEDIC_EN    = 36;

    /**
     * Категории основного состава команды.
     */
    const FC_COACHES_RU = 12;
    const FC_ADMIN_RU   = 13;
    const FC_MEDIC_RU   = 14;
    const FC_PRESS_RU   = 15;
    const FC_SELECT_RU  = 16;
    const FC_COACHES_EN = 37;
    const FC_ADMIN_EN   = 38;
    const FC_MEDIC_EN   = 39;
    const FC_PRESS_EN   = 40;
    const FC_SELECT_EN  = 41;

    /**
     * Категории молодежного состава.
     */
    const FCM_COACHES_RU = 17;
    const FCM_PERSONS_RU = 18;
    const FCM_COACHES_EN = 42;
    const FCM_PERSONS_EN = 43;

    /**
     * Категории второй команды.
     */
    const FC2_COACHES_RU = 20;
    const FC2_PERSONS_RU = 21;
    const FC2_COACHES_EN = 44;
    const FC2_PERSONS_EN = 45;

    /**
     * Категории команды академии.
     */
    const A_LEADS_RU   = 22;
    const A_COACHES_RU = 23;
    const A_PERSONS_RU = 24;
    const A_LEADS_EN   = 46;
    const A_COACHES_EN = 47;
    const A_PERSONS_EN = 48;

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{persons__categories}}';
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'links' => [self::HAS_MANY, 'PersonsCategoryObjects', 'category_id']
        ];
    }
}
