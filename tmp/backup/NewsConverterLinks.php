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
     * @var array
     */
    private $categories = [];

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
            } elseif (!$category_ru->getMultilangId()) {
                $category_ru->save();
            }
            /* @var NewsCategories $category_en */
            $category_en = $nc->findByAttributes(['parent_id' => $newParentEn, 'name' => $name]);
            if (is_null($category_en)) {
                $category_en = $save_cat($newParentEn, BaseFcModel::LANG_EN, $category_ru->multilangId);
            } elseif (!$category_en->getMultilangId()) {
                $category_en->multilangId = $category_ru->multilangId;
                $category_en->lang = BaseFcModel::LANG_EN;
                $category_en->save();
            }

            //$this->saveObjects($cat, $category_ru->getId(), $category_en->getId());
            if (!isset($this->categories[$cat->id])) {
                $this->categories[$cat->id] = [
                    BaseFcModel::LANG_RU => $category_ru->getId(), BaseFcModel::LANG_EN => $category_en->getId()
                ];
            }

            $this->saveCategories($cat->id, $category_ru->getId(), $category_en->getId());
        }

        return true;
    }

    /**
     * Сохранение объектов.
     *
     * @return bool
     */
    private function saveObjects()
    {
        $criteria = new CDbCriteria();
        $criteria->select = [
            'id', 'date', 'title', 'type', 'message', 'link', 'details', 'metadescription', 'metatitle',
            'metakeywords', 'priority', 'tags'
        ];
        $criteria->addCondition('title!=\'\'');
        //$criteria->addCondition('date>\'2014-08-10 00:00:00.0\'');
        //$criteria->addCondition('XMLSERIALIZE(CONTENT(tags) AS text)!=\'\'');
        //$criteria->addCondition('XMLSERIALIZE(CONTENT(tags) AS text)!=\'<tags />\'');
        //$criteria->addCondition('id=58098');
        $criteria->order = 'id';
        $news = News::model()->findAll($criteria);

        /* @var News $n */
        foreach ($news as $i => $n) {
            $pn = array_map(
                function ($l) {
                    /* @var NewsLinks $l */
                    return $l->category;
                },
                $n->links
            );
            $get_pn = function ($ids, $langId) {
                return array_map(
                    function ($id) use ($langId) {
                        return isset($this->categories[$id]) ? $this->categories[$id][$langId] : 0;
                    },
                    $ids
                );
            };
            $pn_ru = array_unique($get_pn($pn, BaseFcModel::LANG_RU));
            $pn_en = array_unique($get_pn($pn, BaseFcModel::LANG_EN));
            $multilang_id = $this->saveObject($n, $pn_ru, $i + 1, BaseFcModel::LANG_RU);
            if ($multilang_id !== true) {
                //$this->saveObject($n, $pn_en, $i + 1, BaseFcModel::LANG_EN, $multilang_id);
            }
        }

        return true;
    }

    /**
     * Сохранение объекта.
     *
     * @param News    $oldObject   Объект.
     * @param array $parents  Идентификатор категории.
     * @param integer $sort        Порядк в категории.
     * @param integer $langId      Язык.
     * @param integer $multilangId
     *
     * @return integer|bool
     *
     * @throws CException
     */
    private function saveObject(News $oldObject, $parents, $sort, $langId, $multilangId = 0)
    {
        $object = new NewsObjects();
        if ($multilangId) {
            $object->setNew();
            $object->multilangId = $multilangId;
        }
        $object->importId   = $oldObject->id;
        $object->writeFiles = $this->writeFiles;
        $object->parents = $parents;
        $tags = $this->setFilesParams($oldObject, $object);
        $object->main_category_id = $oldObject->isText()
            ? ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_NEWS_RU : NewsCategories::CAT_NEWS_EN)
            : ($oldObject->isPhoto()
                ? ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_PHOTO_RU : NewsCategories::CAT_PHOTO_EN)
                : ($langId == BaseFcModel::LANG_RU ? NewsCategories::CAT_VIDEO_RU : NewsCategories::CAT_VIDEO_EN)
            );
        $object->parents[] = $object->main_category_id;
        $object->lang_id = $langId;
        $object->name    = Utils::nameString($oldObject->title);

        $exists_news = NewsObjects::model()->find(
            new CDbCriteria(
                [
                    'condition' => 'main_category_id=:category_id AND name=:name AND lang_id=:lang_id',
                    'params' => [
                        ':category_id' => $object->main_category_id,
                        ':name'        => $object->name,
                        ':lang_id'     => $object->lang_id
                    ]
                ]
            )
        );

        if ($exists_news) {
            $exists_news->announce = Utils::clearText($oldObject->message);
            $exists_news->content  = Utils::clearText($oldObject->details);
            $exists_news->save(false);
            $this->doneNews++;
            $this->progress();
            return true;
            //$object->fileParams = [];
            $object->object_id = $exists_news->getId();
        } else {
            return true;
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
            $object->sort             = $sort;

            if (!$object->save()) {
                throw new CException($object->getErrorMsg('Object is not created.', $oldObject));
            }

            $this->doneNews++;
            $this->progress();
        }

        if (str_replace('<tags />', '', trim($oldObject->tags))) {
            $this->saveObjectTags($oldObject->tags, $object->getId(), $langId);
        }

        if ($tags) {
            $this->saveObjectTags($tags, $object->getId(), $langId);
        }

        return $object->multilangId;
    }

    /**
     * Установка параметров файлов.
     *
     * @param News        $oldObject Старый объект.
     * @param NewsObjects $object    Новый объект.
     *
     * @return string
     */
    private function setFilesParams($oldObject, $object)
    {
        $tags = '';
        $object->filesUrl = $oldObject->isText()
            ? News::TEXT_URL
            : ($oldObject->isPhoto() ? News::PHOTO_URL : News::VIDEO_URL);

        // фотки обычных новостей
        if ($oldObject->isText()) {
            $object->setFileParams($oldObject->id, null, 0, null, '', 1, 0, 3);
        } elseif ($gallery_id = $oldObject->getGalleryId()) {// есть ли галерея
            if ($gallery = Gallery::model()->findByPk($gallery_id)) {
                $tags = str_replace('<tags />', '', trim($gallery->tags));
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
                            $file->ord,
                            0,
                            3
                        );
                        if (!$tags) {
                            $tags = str_replace('<tags />', '', trim($file->tags));
                        }
                    }

                    $object->setFileParams(
                        $file->id,
                        $filename,
                        0,
                        null,
                        $file->caption,
                        $file->ord,
                        $oldObject->isVideo() ? $file->duration : 0,
                        $oldObject->isVideo() ? 0 : 3
                    );
                }
            }
        }

        return $tags;
    }

    /**
     * @param string $tags
     * @param integer $objectId
     * @param integer $langId
     *
     * @return bool
     *
     * @throws CException
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

            if (!in_array($type, [PlayersConverter::TAGS_TEAM, PlayersConverter::TAGS_PLAYER_FC, MatchesConverter::TAGS_MATCH])) {
                continue;
            }

            /*if ($type != MatchesConverter::TAGS_MATCH) {
                continue;
            }*/

            if ($type == PlayersConverter::TAGS_PLAYER_FC) {
                $type = PlayersConverter::TAGS_PLAYER;
            }

            $id = (int) $tag->getAttribute('id');

            if ($type == MatchesConverter::TAGS_MATCH) {
                $tag_id = Tags::model()->getDbConnection()->createCommand(
                    'SELECT
                        `t`.`tag_id`
                    FROM `fc__tags` AS `t`
                        JOIN `fc__tags__categories` AS `tc` ON `tc`.`category_id`=`t`.`category_id`
                            AND `tc`.`category_id`=5
                    WHERE
                        `t`.`title` LIKE :title'
                )->queryScalar([':title' => '%' . $id . '_' . '%']);
            } else {
                $tag_id = isset($this->tags[$type][$id]) ? (int) $this->tags[$type][$id] : 0;
            }

            if ($tag_id) {
                $attrs = ['tag_id' => $tag_id, 'module_id' => BaseFcModel::NEWS_MODULE_ID];
                $module_link = TagsModules::model()->findByAttributes($attrs);
                if (!$module_link) {
                    $module_link = new TagsModules();
                    $module_link->tag_id = $tag_id;
                    $module_link->module_id = BaseFcModel::NEWS_MODULE_ID;
                    $module_link->publish = 1;
                    $module_link->is_default = 0;
                    $module_link->save();
                }

                $attrs = ['link_id' => $module_link->link_id, 'object_id' => $objectId];
                $object_link = TagsObjects::model()->findByPk($attrs);

                if (!$object_link) {
                    $object_link = new TagsObjects();
                    $object_link->setAttributes($attrs);
                    $object_link->publish = 1;

                    if (!$object_link->save()) {
                        throw new CException($object_link->getErrorMsg('Tag link is not created.', $object_link));
                    }

                    $this->doneTags++;
                    $this->progress();
                }
            }
        }

        return true;
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneCats, $this->doneNews, $this->doneTags);
    }
}
