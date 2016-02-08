<?php

/**
 * Перенос новостей из одной категории в другую.
 *
 * @package    converter
 * @subpackage move_news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class KitMultilangConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rKit: %d. Banners: %d. Branches: %d. News: %d. Persons: %d. Press: %d";

    /**
     * @var integer
     */
    private $doneKit = 0;

    /**
     * @var integer
     */
    private $doneBanners = 0;

    /**
     * @var integer
     */
    private $doneBranches = 0;

    /**
     * @var integer
     */
    private $doneNews = 0;

    /**
     * @var integer
     */
    private $donePersons = 0;

    /**
     * @var integer
     */
    private $donePress = 0;

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();

        $criteria = new CDbCriteria();
        $criteria->select = ['module_id', 'name'];
        $criteria->addCondition('name=:name');
        $criteria->addCondition('is_kit=:is_kit', 'OR');
        $criteria->order = 'is_kit, name';
        $criteria->params = [':name' => 'kit', ':is_kit' => 1];
        $modules = CoreModules::model()->findAll($criteria);
        echo  "\n";
        foreach ($modules as $module) {
            $criteria = new CDbCriteria();
            $criteria->select = ['id', 'entity', 'import_id'];
            $criteria->addCondition('module_id=:module_id');
            $criteria->params = [':module_id' => $module->module_id];
            //$criteria->with = 'entities';
            /* @var CoreMultilang[] $multilangs */
            $multilangs = CoreMultilang::model()->find($criteria);
            if (!$multilangs) {
                continue;
            }
            foreach ($multilangs as $multilang) {
                echo "id: {$multilang->id}, import_id: {$multilang->import_id}\n";
                $entities = $multilang->entities;
                foreach ($entities as $entity) {
                    echo "id: {$multilang->id}, import_id: {$multilang->import_id}, entity_id: {$entity->entity_id}, ".
                        "lang_id: {$entity->lang_id}\n";
                }
            }
        }
    }

    /**
     * @return bool
     */
    private function countNewsTypes()
    {
        /*$criteria = new CDbCriteria();
        $criteria->select = ['publish_date_on', 'type'];
        //$criteria->condition = 'publish=1';
        $criteria->order = 'publish_date_on, type';
        $objects = new NewsObjects();
        $news = [];

        /* @var NewsObjects $n /
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

        return true;*/
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneKit,
            $this->doneBanners,
            $this->doneBranches,
            $this->doneNews,
            $this->donePersons,
            $this->donePress
        );
    }
}
