<?php

/**
 * Конвертация других систем в SamoletCMS.
 *
 * @package    converter
 * @subpackage console
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ConverterCommand extends CConsoleCommand
{
    /**
     * Начальное время.
     *
     * @var integer
     */
    private $startTime = 0;

    /**
     * Справка.
     *
     * @see CConsoleCommand::getHelp()
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
    yiic converter [action] [parameter]

DESCRIPTION
    Converter console launcher.

EXAMPLES
    * yiic converter news
        Convert news.

        Parameters:

        - writeFiles

    * yiic converter persons
        Convert persons.

        Parameters:

        - persons: players, coaches, admins, medics, press, select.

    * yiic converter teams
        Convert teams.

    * yiic convert contracts
        Convert contracts.


        - persons: players, persons;
        - writeFiles

    * yiic convert champs
        Convert seasons, championships and stages.

EOD;
    }

    /**
     * Конвертация новостей.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionNews($writeFiles = false)
    {
        $n = new NewsConverter();
        $n->writeFiles = $writeFiles;
        $n->convert();
    }

    /**
     * Конвертация персон.
     *
     * @param string $persons Персоны:
     *                        <ul>
     *                          <li>players;</li>
     *                          <li>coaches;</li>
     *                          <li>admins;</li>
     *                          <li>medics;</li>
     *                          <li>press;</li>
     *                          <li>select.</li>
     *                        </ul>
     *
     * @throws CException
     */
    public function actionPersons ($persons = null)
    {
        if (!is_null($persons) && !in_array($persons, ['players', 'coaches', 'admins', 'medics', 'press', 'select'])) {
            throw new CException('Wrong "persons".' . "\n");
        }

        $p = new PersonsConverter($persons);
        $p->convert();
    }

    /**
     * Конвертация команд.
     */
    public function actionTeams()
    {
        $t = new TeamsConverter();
        $t->convert();
    }

    /**
     * Конвертация контрактов.
     *
     * @param string $persons Персоны:
     *                        <ul>
     *                          <li>players;</li>
     *                          <li>persons.</li>
     *                        </ul>
     * @param bool $writeFiles Сохранить файлы на диск.
     *
     * @throws CException
     */
    public function actionContracts ($persons = null, $writeFiles = false)
    {
        if (!is_null($persons) && !in_array($persons, ['players', 'persons'])) {
            throw new CException('Wrong "persons".' . "\n");
        }

        $c = new ContractsConverter($persons);
        $c->writeFiles = $writeFiles;
        $c->convert();
    }

    /**
     * Конвертация сезонов, чемпионатов и этапов.
     *
     * @throws CException
     */
    public function actionChamps()
    {
        $c = new ChampsConverter();
        $c->convert();
    }

    protected function beforeAction($action, $params)
    {
        $this->ensureDirectory(Yii::getPathOfAlias('accordance'));
        $this->startTime = microtime(true);

        return parent::beforeAction($action, $params);
    }

    protected function afterAction($action, $params, $exitCode = 0)
    {
        print 'Done in ' . sprintf('%f', microtime(true) - $this->startTime) . ".\n";

        return parent::afterAction($action, $params, $exitCode);
    }
}
