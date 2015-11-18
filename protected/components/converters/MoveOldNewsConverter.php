<?php

/**
 * Перенос новостей из одной категории в другую.
 *
 * @package    converter
 * @subpackage move_news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class MoveOldNewsConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rNews: %d. Group months: %d";

    /**
     * @var integer
     */
    private $doneNews = 0;

    /**
     * @var integer
     */
    private $doneMonths = 0;

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();

        // выборка по месяцам количества типов новостей
        $this->countNewsTypes();
    }

    /**
     * @return bool
     */
    private function countNewsTypes()
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['publish_date_on', 'type'];
        //$criteria->condition = 'publish=1';
        $criteria->order = 'publish_date_on, type';
        $objects = new NewsObjects();
        $news = [];

        /* @var NewsObjects $n */
        foreach ($objects->findAll($criteria) as $n) {
            $date = new \DateTime($n->publish_date_on);
            $key = $date->format('Y-m') . '|' . $n->type;
            if (!isset($news[$key])) {
                $news[$key] = 0;
            }

            $news[$key]++;
            $this->doneNews++;
            $this->progress();
        }

        $fp = fopen(Yii::getPathOfAlias('accordance') . '/count_types.csv', 'w');
        fputcsv($fp, ['Date', 'Type', 'Count']);
        foreach ($news as $k => $count) {
            list($date, $type) = explode('|', $k);
            fputcsv($fp, [$date, $type, $count]);

            $this->doneMonths++;
            $this->progress();
        }
        fclose($fp);

        return true;
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneNews, $this->doneMonths);
    }
}
