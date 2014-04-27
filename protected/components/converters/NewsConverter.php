<?php

/**
 * Конвертер новостей.
 *
 * @package    converter
 * @subpackage news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class NewsConverter implements IConverter
{
    /**
     * Сохранить файлы на диск.
     *
     * @var bool
     */
    public $writeFiles = false;

    /**
     * Файл сохраненных новостей.
     *
     * @var string
     */
    private $newsFile = '';

    /**
     * Сохраненные новости если было прерывание.
     *
     * @var array
     */
    private $savedNews = [];

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->newsFile = Yii::getPathOfAlias('accordance') . '/news.txt';

        if (file_exists($this->newsFile)) {
            $this->savedNews = file($this->newsFile);
        }
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        // категории и связанные с ними объекты
        $this->saveCategories(0, NewsCategories::CAT_NEWS_CAT);
        // объекты без категорий
        $this->saveObjects();

        // все прошло успешно, удаляем кеш сохраненных
        unlink($this->newsFile);
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
            $name = Utils::nameString($cat->name);
            $category = NewsCategories::model()->findByAttributes(['parent_id' => $newParent, 'name' => $name]);

            if (is_null($category)) {
                $category = new NewsCategories();
                $category->parent_id  = $newParent;
                $category->lang_id    = NewsCategories::LANG;
                $category->name       = $name;
                $category->title      = $cat->name;
                $category->content    = $cat->description ?: '';
                $category->publish    = 1;
                $category->sort       = $i + 1;
                $category->meta_title = $cat->name;

                if (!$category->save()) {
                    throw new CException($category->getErrorMsg('Category not created.', $cat));
                }
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
                'id', 'date', 'title', 'type', 'message', 'link', 'details', 'metadescription', 'metatitle',
                'metakeywords', 'priority'
            ];
            $criteria->condition = 'id NOT IN (SELECT news FROM ' . NewsLinks::model()->tableName() . ')';
            $criteria->addCondition('title!=\'\'');
            $objects = News::model()->findAll($criteria);

            foreach ($objects as $i => $obj) {
                $this->saveObject($obj, NewsCategories::CAT_NEWS_CAT, $i + 1);
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
        if (in_array($oldObject->id, $this->savedNews)) {
            return true;
        }

        $object = new NewsObjects();
        $object->writeFiles = $this->writeFiles;
        $this->setFilesParams($oldObject, $object);
        $object->main_category_id = $oldObject->isText()
            ? NewsCategories::CAT_NEWS
            : ($oldObject->isPhoto() ? NewsCategories::CAT_PHOTO : NewsCategories::CAT_VIDEO);
        $object->lang_id          = NewsObjects::LANG;
        $object->name             = Utils::nameString($oldObject->title);
        $object->title            = $oldObject->title;
        $object->announce         = $oldObject->message ?: '';
        $object->content          = $oldObject->details ?: '';
        $object->important        = (int) $oldObject->priority;
        $object->publish          = 1;
        $object->publish_date_on  = $oldObject->date ?: null;
        $object->created          = date('Y-m-d H:i:s');
        $object->meta_title       = $oldObject->metatitle;
        $object->meta_description = $oldObject->metadescription;
        $object->meta_keywords    = $oldObject->metakeywords;
        // поля для связки с категориями
        $object->minorCategoryId  = $categoryId;
        $object->sort             = $sort;

        if (!$object->save()) {
            throw new CException($object->getErrorMsg('Object is not created.', $oldObject));
        }

        $fh = fopen($this->newsFile, 'a');
        fwrite($fh, $oldObject->id . "\n");
        fclose($fh);

        return true;
    }

    /**
     * Установка параметров файлов.
     *
     * @param News        $oldObject Старый объект.
     * @param NewsObjects $object    Новый объект.
     */
    private function setFilesParams($oldObject, $object)
    {
        $object->filesUrl = $oldObject->isText()
            ? News::TEXT_URL
            : ($oldObject->isPhoto() ? News::PHOTO_URL : News::VIDEO_URL);

        // фотки обычных новостей
        if ($oldObject->isText()) {
            $object->setFileParams($oldObject->id);
        } elseif ($gallery_id = $oldObject->getGalleyId()) {// есть ли галерея
            if ($gallery = Gallery::model()->findByPk($gallery_id)) {
                $filename = $oldObject->isPhoto() ? NewsObjects::FILE_PHOTO : NewsObjects::FILE_VIDEO;
                $filename = str_replace(['{path}', '/res/'], [$gallery->location, ''], $filename);
                // собираем каждый файл галерии
                foreach ($gallery->files as $file) {
                    // тумбочка для видео
                    if ($oldObject->isVideo()) {
                        $object->setFileParams(
                            $file->id,
                            str_replace(['{path}', '/res/'], [$gallery->location, ''], NewsObjects::FILE_VIDEO_THUMB),
                            0,
                            null,
                            $file->caption,
                            $file->ord
                        );
                    }

                    $object->setFileParams(
                        $file->id,
                        $filename,
                        0,
                        null,
                        $file->caption,
                        $file->ord,
                        $oldObject->isVideo() ? $file->duration : 0
                    );
                }
            }
        }
    }
}
