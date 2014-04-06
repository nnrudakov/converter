<?php

/**
 * Конвертер новостей.
 *
 * @package    converter
 * @subpackage news
 * @author     Nikolaj Rudakov <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class NewsConverter implements IConverter
{
    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        // категории и связанные с ними объекты
        $this->saveCategories(0, 0);
        // объекты без категорий
        $this->saveObjects();
    }

    /**
     * Сохранение категорий.
     *
     * @param integer $oldParent Идентификатор старого родителя.
     * @param integer $newParent Идентификатор нового родителя.
     *
     * @return bool
     *
     * @throws CException
     */
    private function saveCategories($oldParent, $newParent)
    {
        $criteria = new CDbCriteria([
            'select' => ['id', 'name', 'description'],
            'order'  => 'id'
        ]);
        if ($oldParent) {
            $criteria->addCondition('parentid=:parent');
            $criteria->params = [':parent' => $oldParent];
        } else {
            $criteria->addCondition('parentid IS NULL');
        }
        $src_cats = new NewsCategs();

        foreach ($src_cats->findAll($criteria) as $i => $cat) {
            $category = new NewsCategories();
            $category->parent_id  = $newParent;
            $category->lang_id    = NewsCategories::LANG;
            $category->name       = Utils::nameString($cat->name);
            $category->title      = $cat->name;
            $category->content    = $cat->description ?: '';
            $category->publish    = 1;
            $category->sort       = $i + 1;
            $category->meta_title = $cat->name;

            if (!$category->save()) {
                throw new CException($category->getErrorMsg('Category not created.', $cat));
            }

            $this->saveObjects($cat, $category);
            $this->saveCategories($cat->id, $category->category_id);
        }

        return true;
    }

    /**
     * Сохранение объектов.
     *
     * @param NewsCategs     $oldCategory Старая категория.
     * @param NewsCategories $newCategory Новая категория.
     *
     * @return bool
     */
    private function saveObjects($oldCategory = null, $newCategory = null)
    {
        if (is_null($oldCategory) && is_null($newCategory)) {
            $criteria = new CDbCriteria();
            $criteria->select = [
                'id', 'date', 'title', 'message', 'link', 'details', 'metadescription', 'metatitle', 'metakeywords',
                'priority'
            ];
            $criteria->condition = 'id NOT IN (SELECT news FROM ' . NewsLinks::model()->tableName() . ')';
            $objects = News::model()->findAll($criteria);

            foreach ($objects as $i => $obj) {
                $this->saveObject($obj, 0, $i + 1);
            }
        } else {
            /* @var NewsLinks $link */
            foreach ($oldCategory->links as $link) {
                foreach ($link->news_obj as $i => $obj) {
                    $this->saveObject($obj, $newCategory->getId(), $i + 1);
                }
            }
        }

        return true;
    }

    /**
     * Сохранение объекта.
     *
     * @param News    $oldObject  Объект.
     * @param integer $categoryId Идентификатор категории.
     * @param integer $sort       Порядк в категории.
     *
     * @return bool
     *
     * @throws CException
     */
    private function saveObject(News $oldObject, $categoryId, $sort)
    {
        $object = new NewsObjects();
        $object->setFileParams($oldObject->id, null, $categoryId);
        $object->main_category_id = $categoryId;
        $object->lang_id          = NewsObjects::LANG;
        $object->name             = Utils::nameString($oldObject->title);
        $object->title            = $oldObject->title;
        $object->announce         = $oldObject->message ?: '';
        $object->content          = $oldObject->details ?: '';
        $object->important        = (int) $oldObject->priority;
        $object->publish          = 1;
        $object->publish_date_on  = $oldObject->date ?: null;
        $object->source_link      = $oldObject->link;
        $object->created          = date('Y-m-d H:i:s');
        $object->meta_title       = $oldObject->metatitle;
        $object->meta_description = $oldObject->metadescription;
        $object->meta_keywords    = $oldObject->metakeywords;
        $object->sort             = $sort;

        if (!$object->save()) {
            throw new CException($object->getErrorMsg('Object is not created.', $oldObject));
        }

        return true;
    }
}
