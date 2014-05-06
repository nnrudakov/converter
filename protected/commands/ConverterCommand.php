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

    * yiic converter teams
        Parameters:
            - writeFiles

    * yiic convert contracts
        Convert contracts.

        Parameters:
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
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionPersons ($writeFiles = false)
    {
        $p = new PersonsConverter();
        $p->writeFiles = $writeFiles;
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
     * Конвертация контрактов, игроков и команд.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     *
     * @throws CException
     */
    public function actionContracts ($writeFiles = false)
    {
        $c = new ContractsConverter();
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
