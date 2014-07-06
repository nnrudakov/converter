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
    * yiic converter all
        Convert all entities.

        Parameters:
            - writeFiles [*|news,persons,players]

    * yiic converter news
        Convert news.

        Parameters:
            - writeFiles

    * yiic converter persons
        Convert persons.

    * yiic convert players
        Convert players, teams, contracts, stats.

        Parameters:
            - writeFiles

    * yiic convert champs
        Convert seasons, championships and stages.

    * yiic convert matches
        Convert matches (inc. events, placments).

    * yiic convert files
        Convert files.

EOD;
    }

    /**
     * Конвертация всего.
     *
     * @param bool $writeFiles Сохранить файлы на диск.
     */
    public function actionAll($writeFiles = false)
    {
        if ('*' == $writeFiles || 1 == $writeFiles) {
            $writeFiles = true;
        } else {
            $writeFiles = explode(',', $writeFiles);
        }

        print "Action 'champs'.\n";
        $start = microtime(true);
        $this->actionChamps();
        $this->showTime($start);
        print "\n";

        print "Action 'persons'.\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('persons', $writeFiles);
        $this->actionPersons($writeFiles);
        $this->showTime($start);
        print "\n";

        print "Action 'players'\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('players', $writeFiles);
        $this->actionPlayers($writeFiles);
        $this->showTime($start);
        print "\n";

        print "Action 'matches'.\n";
        $start = microtime(true);
        $this->actionMatches();
        $this->showTime($start);
        print "\n";

        print "Action 'news'.\n";
        $start = microtime(true);
        $writeFiles = is_bool($writeFiles) ? $writeFiles : in_array('news', $writeFiles);
        $this->actionNews($writeFiles);
        $this->showTime($start);
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
    public function actionPlayers ($writeFiles = false)
    {
        $c = new PlayersConverter();
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

    /**
     * Конвертация матчей, их событий и расстановки.
     */
    public function actionMatches()
    {
        $m = new MatchesConverter();
        $m->convert();
    }

    /**
     * Перенос файлов из текущей структуры в новую.
     */
    public function actionFiles()
    {
        $f = new FilesConverter();
        $f->convert();
    }

    protected function beforeAction($action, $params)
    {
        $this->ensureDirectory(Yii::getPathOfAlias('accordance'));
        $this->startTime = microtime(true);

        return parent::beforeAction($action, $params);
    }

    protected function afterAction($action, $params, $exitCode = 0)
    {
        $this->showTime();

        return parent::afterAction($action, $params, $exitCode);
    }

    private function showTime($start = 0)
    {
        $start = $start ?: $this->startTime;
        print "\n" . 'Done in ' . sprintf('%f', microtime(true) - $start) . ".\n";
    }
}
