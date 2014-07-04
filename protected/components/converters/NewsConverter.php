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
     * @var array
     */
    private $tags = [];

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rCategories: %d. News: %d. Tags links: %d.";

    /**
     * @var integer
     */
    private $doneCats = 0;

    /**
     * @var integer
     */
    private $doneNews = 0;

    /**
     * @var integer
     */
    private $doneTags = 0;

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
        $pc = new PlayersConverter();
        $this->tags = $pc->getTags();

        $this->progress();
        // категории и связанные с ними объекты
        $this->saveCategories(0, NewsCategories::CAT_NEWS_CAT_RU, NewsCategories::CAT_NEWS_CAT_EN);
        // объекты без категорий
        $this->saveObjects();

        // все прошло успешно, удаляем кеш сохраненных
        //unlink($this->newsFile);
    }

    /**
     * Сохранение категорий.
     *
     * @param integer $oldParent   Идентификатор старого родителя.
     * @param integer $newParentRu Идентификатор нового родителя.
     * @param integer $newParentEn Идентификатор нового родителя.
     *
     * @return bool
     *
     * @throws CException
     */
    private function saveCategories($oldParent, $newParentRu, $newParentEn)
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['id', 'name', 'description'];
        $criteria->order  = 'id';
        if ($oldParent) {
            $criteria->addCondition('parentid=:parent');
            $criteria->params = [':parent' => $oldParent];
        } else {
            $criteria->addCondition('parentid IS NULL');
        }
        $src_cats = new NewsCategs();
        $nc = new NewsCategories();
        $that = $this;

        foreach ($src_cats->findAll($criteria) as $i => $cat) {
            $name = Utils::nameString($cat->name);
            $save_cat = function ($parentId, $langId, $multilangId = 0) use ($that, $cat, $name, $i) {
                $category = new NewsCategories();
                if ($multilangId) {
                    $category->setNew();
                    $category->multilangId = $multilangId;
                }
                $category->importId   = $cat->id;
                $category->parent_id  = $parentId;
                $category->lang_id    = $langId;
                $category->name       = $name;
                $category->title      = $cat->name;
                $category->content    = $cat->description ?: '';
                $category->publish    = 1;
                $category->sort       = $i + 1;
                $category->meta_title = $cat->name;

                if (!$category->save()) {
                    throw new CException($category->getErrorMsg('Category not created.', $cat));
                }

                $this->doneCats++;
                $this->progress();

                return $category;
            };

            /* @var NewsCategories $category_ru */
            $category_ru = $nc->findByAttributes(['parent_id' => $newParentRu, 'name' => $name]);
            if (is_null($category_ru)) {
                $category_ru = $save_cat($newParentRu, BaseFcModel::LANG_RU);
            }
            /* @var NewsCategories $category_en */
            $category_en = $nc->findByAttributes(['parent_id' => $newParentEn, 'name' => $name]);
            if (is_null($category_en)) {
                $category_en = $save_cat($newParentEn, BaseFcModel::LANG_EN, $category_ru->multilangId);
            }

            $this->saveObjects($cat, $category_ru->getId(), $category_en->getId());
            $this->saveCategories($cat->id, $category_ru->getId(), $category_en->getId());
        }

        return true;
    }

    /**
     * Сохранение объектов.
     *
     * @param NewsCategs $oldCategory   Старая категория.
     * @param integer    $newCategoryRu Новая категория.
     * @param integer    $newCategoryEn Новая категория.
     *
     * @return bool
     */
    private function saveObjects($oldCategory = null, $newCategoryRu = null, $newCategoryEn = null)
    {
        if (is_null($oldCategory) && is_null($newCategoryRu) && is_null($newCategoryEn)) {
            $criteria = new CDbCriteria();
            $criteria->select = [
                'id', 'date', 'title', 'type', 'message', 'link', 'details', 'metadescription', 'metatitle',
                'metakeywords', 'priority', 'tags'
            ];
            $criteria->condition = 'id NOT IN (SELECT news FROM ' . NewsLinks::model()->tableName() . ')';
            $criteria->addCondition('title!=\'\'');
            $criteria->order = 'id';
            $objects = News::model()->findAll($criteria);

            foreach ($objects as $i => $obj) {
                $multilang_id = $this->saveObject($obj, NewsCategories::CAT_NEWS_CAT_RU, $i + 1, BaseFcModel::LANG_RU);
                $this->saveObject($obj, NewsCategories::CAT_NEWS_CAT_EN, $i + 1, BaseFcModel::LANG_EN, $multilang_id);
            }
        } else {
            /* @var NewsLinks $link */
            foreach ($oldCategory->links as $link) {
                foreach ($link->news_obj as $i => $obj) {
                    $multilang_id = $this->saveObject($obj, $newCategoryRu, $i + 1, BaseFcModel::LANG_RU);
                    $this->saveObject($obj, $newCategoryEn, $i + 1, BaseFcModel::LANG_EN, $multilang_id);
                }
            }
        }

        return true;
    }

    /**
     * Сохранение объекта.
     *
     * @param News    $oldObject   Объект.
     * @param integer $categoryId  Идентификатор категории.
     * @param integer $sort        Порядк в категории.
     * @param integer $langId      Язык.
     * @param integer $multilangId
     *
     * @return integer
     *
     * @throws CException
     */
    private function saveObject(News $oldObject, $categoryId, $sort, $langId, $multilangId = 0)
    {
        /*if (in_array($oldObject->id, $this->savedNews)) {
            return true;
        }*/

        $object = new NewsObjects();
        if ($multilangId) {
            $object->setNew();
            $object->multilangId = $multilangId;
        }
        $object->importId   = $oldObject->id;
        $object->writeFiles = $this->writeFiles;
        $this->setFilesParams($oldObject, $object);
        $object->main_category_id = $oldObject->isText()
            ? ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_NEWS_CAT_RU : NewsCategories::CAT_NEWS_CAT_EN)
            : ($oldObject->isPhoto()
                ? ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_PHOTO_RU : NewsCategories::CAT_PHOTO_EN)
                : ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_VIDEO_RU : NewsCategories::CAT_VIDEO_EN)
            );
        $object->lang_id          = $langId;
        $object->name             = Utils::nameString($oldObject->title);
        $object->title            = $oldObject->title;
        $object->announce         = Utils::clearText($oldObject->message);
        $object->content          = Utils::clearText($oldObject->details);
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

        $this->doneNews++;
        $this->progress();

        if ($oldObject->tags) {
            $this->saveObjectTags($oldObject->tags, $object->getId(), $langId);
        }

        /*$fh = fopen($this->newsFile, 'a');
        fwrite($fh, $oldObject->id . "\n");
        fclose($fh);*/

        return $object->multilangId;
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

    /**
     * @param string $tags
     * @param integer $objectId
     * @param integer $langId
     *
     * @return bool
     */
    private function saveObjectTags($tags, $objectId, $langId)
    {
        $doc = new DOMDocument();
        $doc->loadXML($tags);
        /* @var DomElement $tag */
        foreach ($doc->documentElement->childNodes as $tag) {
            if ($tag->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            $type = $tag->getAttribute('type');

            if (!in_array($type, [PlayersConverter::TAGS_TEAM, PlayersConverter::TAGS_PLAYER, MatchesConverter::TAGS_MATCH])) {
                continue;
            }

            $id = (int) $tag->getAttribute('id');
            if (isset($this->tags[$type][$id])) {
                $attrs = ['tag_id' => $this->tags[$type][$id][$langId], 'module_id' => BaseFcModel::NEWS_MODULE_ID];
                $module_link = TagsModules::model()->findByAttributes($attrs);
                if (!$module_link) {
                    $module_link = new TagsModules();
                    $module_link->tag_id = $this->tags[$type][$id][$langId];
                    $module_link->module_id = BaseFcModel::NEWS_MODULE_ID;
                    $module_link->publish = 1;
                    $module_link->is_default = 0;
                    $module_link->save();
                }
                $object_link = new TagsObjects();
                $object_link->link_id = $module_link->link_id;
                $object_link->object_id = $objectId;
                $object_link->publish = 1;
                $object_link->save();

                $this->doneTags++;
                $this->progress();
            }
        }

        return true;
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneCats, $this->doneNews, $this->doneTags);
    }
}
