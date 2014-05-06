<?php

/**
 * Перенос команд.
 *
 * @package    converter
 * @subpackage teams
 * @author     Nikolaj Rudakov <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class TeamsConverter implements IConverter
{
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
     * Идентификатор команды "Краснодар".
     *
     * @var integer
     */
    const MAIN_ID = 537;

    /**
     * Идентификатор команды "Краснодар-2".
     *
     * @var integer
     */
    const JUNIOR_ID = 588;

    /**
     * Файл соответствий текущих идентификаторов команд новым.
     *
     * @var string
     */
    private $teamsFile = '';

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->teamsFile = __DIR__ . '/teams.php';
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $criteria = new CDbCriteria([
            'select'    => ['id', 'title', 'info', 'region', 'country', 'web'],
            'condition' => 'title!=\'\'',
            'order'     => 'id'
        ]);
        $src_teams = new Teams();
        $teams = [];

        foreach ($src_teams->findAll($criteria) as $t) {
            $team = new FcTeams();
            $team->title = $t->title;
            $team->info  = $t->info;
            $team->city  = $t->region;
            //$team->staff = $t->id == self::MAIN_ID ? self::MAIN : ($t->id == self::JUNIOR_ID ? self::JUNIOR : null);

            if (!$team->save()) {
                throw new CException(
                    'Team not created.' . "\n" .
                    var_export($team->getErrors(), true) . "\n" .
                    $t . "\n"
                );
            }

            $teams[$t->id] = $team->id;
        }

        //file_put_contents($this->teamsFile, '<?php return ' . var_export($teams, true) . ';' . "\n");
    }
}
