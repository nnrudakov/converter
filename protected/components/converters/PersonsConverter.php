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
     * Тренеры основной команды.
     *
     * @var string
     */
    const COACHES_MAIN = '634327703968554462';

    /**
     * Администаторы основной команды.
     *
     * @var string
     */
    const ADMINS_MAIN = '634327704292614462';

    /**
     * Медики основной команды.
     *
     * @var string
     */
    const MEDICS_MAIN = '634460819371416698';

    /**
     * Пресса основной команды.
     *
     * @var string
     */
    const PRESS_MAIN = '634460819777869946';

    /**
     * Селекционеры основной команды.
     *
     * @var string
     */
    const SELECT_MAIN = '634460820642589405';

    /**
     * Тренеры молодежной команды.
     *
     * @var string
     */
    const COACHES_JUNIOR = '634378824976006501';

    /**
     * Администаторы основной команды.
     *
     * @var string
     */
    const ADMINS_JUNIOR = '6340036650992571485';

    /**
     * Соответствие между текущими и новыми амплуа.
     *
     * @var array
     */
    public static $ampluas = [
        self::AMPLUA_CUR_FORWARD    => self::AMPLUA_NEW_FORWARD,
        self::AMPLUA_CUR_BACK       => self::AMPLUA_NEW_BACK,
        self::AMPLUA_CUR_GOALKEEPER => self::AMPLUA_NEW_GOALKEEPER,
        self::AMPLUA_CUR_HALFBACK   => self::AMPLUA_NEW_HALFBACK,
        self::AMPLUA_CUR_PZNP       => self::AMPLUA_NEW_PZNP,
        self::AMPLUA_CUR_FIELD      => self::AMPLUA_NEW_FIELD
    ];

    /**
     * Соответствие между текущим и новым профилем.
     *
     * @var array
     */
    public static $profiles = [
        self::COACHES_MAIN   => self::PROFILE_COACH,
        self::COACHES_JUNIOR => self::PROFILE_COACH,
        self::ADMINS_MAIN    => self::PROFILE_ADMINS,
        self::ADMINS_JUNIOR  => self::PROFILE_ADMINS,
        self::MEDICS_MAIN    => self::PROFILE_MEDIC,
        self::PRESS_MAIN     => self::PROFILE_PRESS,
        self::SELECT_MAIN    => self::PROFILE_SELECT
    ];

    /**
     * Переносимые персоны.
     *
     * @var array
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
     * Инициализация.
     *
     * @param string $persons Персоны (если не указано, то все):
     *                        <ul>
     *                          <li>players;</li>
     *                          <li>coaches;</li>
     *                          <li>admins;</li>
     *                          <li>medics;</li>
     *                          <li>press;</li>
     *                          <li>select.</li>
     *                        </ul>
     */
    public function __construct($persons = null)
    {
        $this->playersFile = __DIR__ . '/players.php';
        $this->personsFile = __DIR__ . '/persons.php';

        if (is_null($persons)) {
            $this->persons = array_merge([self::PROFILE_PLAYER], array_keys(self::$profiles));
        } else {
            switch ($persons) {
                case 'players':
                    $this->persons = [self::PROFILE_PLAYER];
                    break;
                case 'coaches':
                    $this->persons = [self::COACHES_MAIN, self::COACHES_JUNIOR];
                    break;
                case 'admins':
                    $this->persons = [self::ADMINS_MAIN, self::ADMINS_JUNIOR];
                    break;
                case 'medics':
                    $this->persons = [self::MEDICS_MAIN];
                    break;
                case 'press':
                    $this->persons = [self::PRESS_MAIN];
                    break;
                case 'select':
                    $this->persons = [self::SELECT_MAIN];
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        if (self::PROFILE_PLAYER == reset($this->persons)) {
            array_shift($this->persons);
            $this->convertPlayers();
        }

        if (!empty($this->persons)) {
            $this->convertPersons();
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

    /**
     * Перенос остальных персон.
     *
     * @return bool
     *
     * @throws CException
     */
    private function convertPersons()
    {
        $path = array_map(
            function ($p) {
                return 'CAST(' . $p .' AS VARCHAR)';
            },
            $this->persons
        );
        $criteria = new CDbCriteria([
            'select'    => [
                'id', 'citizenship', 'surname', 'first_name', 'patronymic', 'bio', 'borned', 'post', 'path',
                'achivements'
            ],
            'condition' => 'path IN (' . implode(', ', $path) . ')',
            'order'     => 'id'
        ]);
        $src_persons = new Persons();
        $persons = [];

        foreach ($src_persons->findAll($criteria) as $p) {
            if (empty($p->first_name) && empty($p->surname) && empty($p->patronymic)) {
                continue;
            }

            $person = new FcPerson();
            $person->firstname  = $p->first_name;
            $person->lastname   = $p->surname;
            $person->middlename = $p->patronymic;
            $person->birthday   = $p->borned;
            $person->country    = $p->citizenship;
            $person->biograpy   = $p->bio;
            $person->profile    = isset(self::$profiles[$p->path]) ? self::$profiles[$p->path] : null;
            $person->progress   = $p->achivements;
            $person->post       = $p->post;

            if (!$person->save()) {
                throw new CException(
                    'Person not created.' . "\n" .
                    var_export($person->getErrors(), true) . "\n" .
                    $p . "\n"
                );
            }

            $persons[$p->id] = $person->id;
        }

        return true;
    }
}
