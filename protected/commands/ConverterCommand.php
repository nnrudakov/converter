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

    * yiic converter players
        Convert players.

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
     */
    public function actionPlayers ()
    {
        $this->startTime();
        $p = new PlayersConverter();
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
