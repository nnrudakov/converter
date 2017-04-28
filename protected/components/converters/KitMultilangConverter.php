<?php

/**
 * Конвертер многоязычности конструктора.
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
     *
     * @throws CDbException
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

        foreach ($modules as $module) {
            $cmodel = ucfirst($module->name) . 'Categories';
            /* @var KitCategories $category */
            $category = new $cmodel;
            $omodel = ucfirst($module->name) . 'Objects';
            /* @var KitObjects $object */
            $object = new $omodel;
            $criteria = new CDbCriteria();
            $criteria->select = ['id', 'entity', 'import_id'];
            $criteria->addCondition('module_id=:module_id');
            $criteria->params = [':module_id' => $module->module_id];
            $criteria->with = 'entities';
            /* @var CoreMultilang[] $multilangs */
            $multilangs = CoreMultilang::model()->findAll($criteria);
            foreach ($multilangs as $multilang) {
                $multilang_id = $multilang->import_id ?: $multilang->id;
                $entities = $multilang->entities;
                foreach ($entities as $entity) {
                    if ($multilang->entity === 'category') {
                        $category->updateByPk(
                            $entity->entity_id,
                            ['multilang_id' => $multilang_id],
                            'lang_id=:lang_id',
                            [':lang_id' => $entity->lang_id]
                        );
                    } else {
                        $object->updateByPk(
                            $entity->entity_id,
                            ['multilang_id' => $multilang_id],
                            'lang_id=:lang_id',
                            [':lang_id' => $entity->lang_id]
                        );
                    }
                    $entity->delete();

                    switch ($module->name) {
                        case 'banners':  $this->doneBanners++;  break;
                        case 'branches': $this->doneBranches++; break;
                        case 'kit':      $this->doneKit++;      break;
                        case 'news':     $this->doneNews++;     break;
                        case 'persons':  $this->donePersons++;  break;
                        case 'press':    $this->donePress++;    break;
                        default:                                break;
                    }
                    $this->progress();
                }

                $multilang->delete();
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
