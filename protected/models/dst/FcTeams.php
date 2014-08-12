<?php

/**
 * Модель таблицы "{{fc__teams}}".
 *
 * Доступные поля таблицы "{{fc__teams}}":
 *
 * @property integer $id      Идентификатор.
 * @property string  $title   Название.
 * @property string  $info    Информация о команде.
 * @property string  $city    Город.
 * @property string  $staff   Состав.
 * @property string  $country Страна.
 * @property string  $site    Сайт команды.
 *
 * Доступные отношения:
 * @property FcContracts[] $contracts
 * @property FcPerson[] $persons
 *
 * @package    converter
 * @subpackage fcteams
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class FcTeams extends DestinationModel
{
    /**
     * Имя сущности для многоязычности.
     *
     * @var string
     */
    const ENTITY = 'team';

    /**
     * Модуль.
     *
     * @var string
     */
    const MODULE = 'fc';

    /**
     * Имя файла оригинала.
     *
     * @var string
     */
    const FILE = 'images/teams.image.%d.900x598.jpg';

    /**
     * Имя файла маленького логотипа.
     *
     * @var string
     */
    const FILE_LOGO_SMALL = 'images/teams.logo.%d.45x45.png';

    /**
     * Имя файла большого логотипа.
     *
     * @var string
     */
    const FILE_LOGO_BIG = 'images/teams.logo.%d.162x162.png';

    /**
     * Имя поля связки основного файла.
     *
     * @param string
     */
    const FILE_FIELD = 'team_file';

    /**
     * Имя поля связки маленького логтипа.
     *
     * @param string
     */
    const FILE_FIELD_LOGO_SMALL = 'team_logo_small';

    /**
     * Имя поля связки большого логотипа.
     *
     * @param string
     */
    const FILE_FIELD_LOGO_BIG = 'team_logo_big';

    /**
     * Основной состав.
     *
     * @var string
     */
    const MAIN = 'basic';

    /**
     * Молодёжный состав.
     *
     * @var string
     */
    const JUNIOR = 'youth';

    /**
     * Список персон команды.
     *
     * @var FcPerson[]
     */
    private $personsList = null;

    public function __get($name)
    {
        if ('persons' == $name) {
            if (is_null($this->personsList)) {
                $this->personsList = [];

                foreach ($this->contracts as $contract) {
                    $this->personsList[] = $contract->person;
                }
            }

            return $this->personsList;
        }

        return parent::__get($name);
    }

    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '{{fc__teams}}';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            ['title', 'required'],
            ['title, city, country', 'length', 'max'=>128],
            ['site', 'length', 'max'=>255],
            ['staff', 'length', 'max'=>20],
            ['info', 'safe'],
            ['id, title, info, city, staff, country, site', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
        return [
            'contracts' => [self::HAS_MANY, 'FcContracts', 'id']
        ];
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
            'id'      => 'Идентификатор',
            'title'   => 'Название',
            'info'    => 'Информация о команде',
            'city'    => 'Город',
            'staff'   => 'Состав',
            'country' => 'Страна'
        ];
    }

    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return FcTeams Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
