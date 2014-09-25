<?php

/**
 * Перенос новостей из одной категории в другую.
 *
 * @package    converter
 * @subpackage move_news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class MoveNewsConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rNews: %d.";

    /**
     * @var integer
     */
    private $doneNews = 0;

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();

        // "Чемпионат края" - в "Краснодар" - новость
        $this->doMove(29, 43, NewsCategories::CAT_NEWS_RU);
        // "Фотоотчеты" - в "Краснодар" - фоторепортаж
        $this->doMove(23, 43, NewsCategories::CAT_PHOTO_RU);
        // "Футбольная школа" -в "Общие новости Академии" - новость
        $this->doMove(25, 53, NewsCategories::CAT_NEWS_RU);
        // "Онлайн-архив" - в "Краснодар" - новость
        $this->doMove(37, 43, NewsCategories::CAT_NEWS_RU);
        // "Пресса о нас" - в "Краснодар" - новость
        $this->doMove(19, 43, NewsCategories::CAT_NEWS_RU);
        // "Школьные команды" - в "Общие новости Академии" - новость
        $this->doMove(31, 53, NewsCategories::CAT_NEWS_RU);
        // "Свисток" - в "Краснодар" - новость
        $this->doMove(39, 43, NewsCategories::CAT_NEWS_RU);
        // "Трансферы" - в "Краснодар" - новость
        $this->doMove(21, 43, NewsCategories::CAT_NEWS_RU);
        // "Трофеи и достижения" - в "Краснодар" - новость
        $this->doMove(41, 43, NewsCategories::CAT_NEWS_RU);
        // "Турнирные вехи" - в "Краснодар" - новость
        $this->doMove(35, 43, NewsCategories::CAT_NEWS_RU);
        // "Зимний турнир Краснодара" - в "Краснодар" - новость
        $this->doMove(27, 43, NewsCategories::CAT_NEWS_RU);
        // "Видео отчет" в "Краснодар"  -видео
        $this->doMove(13, 43, NewsCategories::CAT_VIDEO_RU);
    }

    /**
     * @param integer $from
     * @param integer $to
     * @param integer $main
     *
     * @return bool
     */
    private function doMove($from, $to, $main)
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['object_id', 'main_category_id'];
        $criteria->with = ['links' => ['select' => [], 'condition' => 'links.category_id=:category_id']];
        $criteria->params = [':category_id' => $from];
        $news = new NewsObjects();

        /* @var NewsObjects $n */
        foreach ($news->findAll($criteria) as $n) {
            foreach ($n->links as $link) {
                $gotcha = false;
                if ($link->category_id == $from) {
                    $link->category_id = $to;
                    $gotcha = true;
                } elseif ($link->category_id == $n->main_category_id) {
                    $link->category_id = $main;
                    $gotcha = true;
                }
                if ($gotcha) {
                    if ($link->findByPk(['category_id' => $to, 'object_id' => $n->getId()])) {
                        $link->deleteByPk(['category_id' => $from, 'object_id' => $n->getId()]);
                    } else {
                        $link->save(false);
                    }
                }
            }

            $this->doneNews++;
            $this->progress();
        }

        return true;
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneNews);
    }
}
