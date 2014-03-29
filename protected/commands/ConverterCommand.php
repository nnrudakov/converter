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

    * yiic converter persons
        Convert persons.

        Parameters:

        - persons: players, coaches, admins, medics, press, select.

EOD;
    }

    /**
     * Конвертация новостей.
     */
    public function actionNews()
    {
        $this->startTime();
        $n = new NewsConverter();
        $n->convert();

        print 'Done in ' . $this->getTime() . ".\n";
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

        $this->startTime();
        $p = new PersonsConverter($persons);
        $p->convert();

        print 'Done in ' . $this->getTime() . ".\n";
    }

    /**
     * Начало отстчета времени выполнения.
     */
    private function startTime()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Получение времени выполнения.
     *
     * @return string Строка секунд.
     */
    private function getTime()
    {
        return sprintf('%f', microtime(true) - $this->startTime);
    }
}
