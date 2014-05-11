<?php

/**
 * Модель таблицы "fc__persons__categories".
 *
 * Доступные поля таблицы "fc__persons__categories":
 *
 * @property string  $category_id      Идентификатор категории.
 * @property string  $parent_id        Идентификатор родительской категории.
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
     * Категории клуба.
     */
    const CLUB_LEADS    = 6;
    const CLUB_SPORT    = 7;
    const CLUB_LAW      = 8;
    const CLUB_SECURITY = 9;
    const CLUB_MARKET   = 10;
    const CLUB_TECH     = 11;
    const CLUB_MEDIC    = 19;

    /**
     * Категории основного состава команды.
     */
    const FC_COACHES = 12;
    const FC_ADMIN   = 13;
    const FC_MEDIC   = 14;
    const FC_PRESS   = 15;
    const FC_SELECT  = 16;

    /**
     * Категории молодежного состава.
     */
    const FCM_COACHES = 17;
    const FCM_PERSONS = 18;

    /**
     * Категории второй команды.
     */
    const FC2_COACHES = 20;
    const FC2_PERSONS = 21;

    /**
     * Категории команды академии.
     */
    const A_LEADS   = 22;
    const A_COACHES = 23;
    const A_PERSONS = 24;

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
