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
     * @var string
     */
    private $newsFile = '';

    /**
     * @var array
     */
    private $news = [];

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rCategories: %d. News total: %d. News: %d. Tags links: %d.";

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
    private $doneAll = 0;

    /**
     * @var integer
     */
    private $doneTags = 0;

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->newsFile = Yii::getPathOfAlias('accordance') . '/news.php';
        $this->news = $this->getNews();
        if (!$this->news) {
            $this->news = [News::TYPE_TEXT => [], News::TYPE_PHOTO => [], News::TYPE_VIDEO => []];
        }
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        //$this->removeEn();
        //$this->removeDouble();
        $this->removeOld();
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
     *
     * @return bool
     *
     * @throws CException
     */
    private function saveCategories($oldParent, $newParentRu)
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
            if (!isset($this->categories[$cat->id])) {
                $this->categories[$cat->id] = $category_ru->getId();
            }

            $this->saveCategories($cat->id, $category_ru->getId());
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
        //$criteria->addCondition('id>63400');
        $criteria->with = ['links'];
        //$criteria->addCondition('type=\'video\'');
        //$criteria->addCondition('date>\'2014-08-14 05:00:00.0\'');
        //$criteria->addCondition('XMLSERIALIZE(CONTENT(tags) AS text)!=\'\'');
        //$criteria->addCondition('XMLSERIALIZE(CONTENT(tags) AS text)!=\'<tags />\'                                                                                                                                                                                                                                                                                    ');
        //$criteria->addCondition('id=58098');
        $criteria->order = 'id';
        //$criteria->limit = 20;
        $news = News::model()->findAll($criteria);

        /* @var News $n */
        foreach ($news as $i => $n) {
            $this->doneAll++;
            $this->progress();
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
                        return isset($this->categories[$id]) ? $this->categories[$id] : 0;
                    },
                    $ids
                );
            };
            //if ($n->links) {
                $pn_ru = array_unique($get_pn($pn, BaseFcModel::LANG_RU));
                $this->saveObject($n, $pn_ru, $i + 1, BaseFcModel::LANG_RU);
            //}
            /*if ($multilang_id !== true) {
                $this->saveObject($n, $pn_en, $i + 1, BaseFcModel::LANG_EN, $multilang_id);
            }*/
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
     *
     * @return integer|bool
     *
     * @throws CException
     */
    private function saveObject(News $oldObject, $parents, $sort, $langId)
    {
        if (isset($this->news[$oldObject->getType()][$oldObject->id])) {
            return true;
        }

        $this->news[$oldObject->getType()][$oldObject->id] = ['new_id' => 0, 'files'  => []];

        $object = new NewsObjects();
        $object->importId   = $oldObject->id;
        $object->writeFiles = $this->writeFiles;
        $object->parents = $parents;
        $tags = $this->setFilesParams($oldObject, $object);
        $object->main_category_id = $oldObject->isText()
            ? NewsCategories::CAT_NEWS_RU
            : ($oldObject->isPhoto() ? NewsCategories::CAT_PHOTO_RU : NewsCategories::CAT_VIDEO_RU);
        $object->parents[] = $object->main_category_id;
        $object->lang_id = $langId;
        $object->name    = rtrim(Utils::nameString($oldObject->title), '-');
        $object->publish_date_on = $oldObject->date ?: null;

        $exists_news = null;/*NewsObjects::model()->find(
            new CDbCriteria(
                [
                    'condition' => 'main_category_id=:category_id AND name=:name AND lang_id=:lang_id ' .
                        'AND publish_date_on=:date',
                    'params' => [
                        ':category_id' => $object->main_category_id,
                        ':name'        => $object->name,
                        ':lang_id'     => $object->lang_id,
                        ':date'        => $object->publish_date_on
                    ]
                ]
            )
        );*/

        if ($exists_news) {
            /*$exists_news->fileParams = $object->fileParams;
            $exists_news->setOwner = $exists_news->setMultilang = $exists_news->setParents = false;
            $exists_news->announce = Utils::clearText($oldObject->message);
            $exists_news->content  = Utils::clearText($oldObject->details);
            $exists_news->save(false);
            $this->news[$oldObject->getType()][$oldObject->id]['new_id'] = (int) $exists_news->getId();
            $this->news[$oldObject->getType()][$oldObject->id]['files']  = $exists_news->savedFiles;*/
            $object->object_id = $exists_news->getId();
            $object->multilangId = $exists_news->getMultilangId();
        } else {
            $object->title            = $oldObject->title;
            $object->announce         = Utils::clearText($oldObject->message);
            $object->content          = Utils::clearText($oldObject->details);
            $object->important        = (int) $oldObject->priority;
            $object->publish          = 1;
            $object->created          = date('Y-m-d H:i:s');
            $object->meta_title       = $oldObject->metatitle;
            $object->meta_description = $oldObject->metadescription;
            $object->meta_keywords    = $oldObject->metakeywords;
            $object->sort             = $sort;

            if (!$object->save()) {
                throw new CException($object->getErrorMsg('Object is not created.', $oldObject));
            }

            $this->news[$oldObject->getType()][$oldObject->id]['new_id'] = (int) $object->getId();
            $this->news[$oldObject->getType()][$oldObject->id]['files']  = $object->savedFiles;

            $this->doneNews++;
            $this->progress();
        }

        $object->savedFiles = [];

        if ($tag = str_replace('<tags />', '', trim($oldObject->tags))) {
            $this->saveObjectTags([$oldObject->tags], $object->getMultilangId(), $langId);
        }

        if ($tags) {
            $this->saveObjectTags($tags, $object->getMultilangId(), $langId);
        }

        file_put_contents($this->newsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->news, true)));

        return true;
    }

    /**
     * Установка параметров файлов.
     *
     * @param News        $oldObject Старый объект.
     * @param NewsObjects $object    Новый объект.
     *
     * @return array
     */
    private function setFilesParams($oldObject, $object)
    {
        $tags = [];

        if ($oldObject->isText()) {
            $sort = 1;
            $object->setFileParams(
                $oldObject->id,
                sprintf(NewsObjects::FILE, $oldObject->id),
                0,
                NewsObjects::FILE_FIELD,
                '',
                $sort,
                0,
                4,
                FilesConverter::SRC_PERSONS_NEWS_DIR
            );

            preg_match_all('/<img .*?src="(.*?)"/', $oldObject->details, $m);
            if (isset($m[1])) {
                foreach ($m[1] as $file) {
                    $object->setFileParams(
                        $oldObject->id,
                        preg_replace('/.+?\//', '', $file),
                        0,
                        NewsObjects::FILE_FIELD,
                        '',
                        ++$sort,
                        0,
                        4,
                        FilesConverter::SRC_DATA_MEDIA . str_replace('/data/media', '', $file)
                    );
                }
            }
        } elseif ($gallery_id = $oldObject->getGalleryId()) {// есть ли галерея
            if ($gallery = Gallery::model()->findByPk($gallery_id)) {
                if ($tag = str_replace('<tags />', '', trim($gallery->tags))) {
                    $tags[] = $tag;
                }
                // собираем каждый файл галерии
                foreach ($gallery->files as $file) {
                    // тумбочка для видео
                    if ($oldObject->isVideo()) {
                        $object->setFileParams(
                            $oldObject->id,
                            sprintf(NewsObjects::FILE_VIDEO_THUMB, $file->id),
                            0,
                            NewsObjects::FILE_FIELD,
                            $file->caption,
                            $file->ord ?: 1,
                            0,
                            4,
                            FilesConverter::SRC_NEWS_PHOTO_DIR . str_replace('/res/galleries', '', $gallery->location)
                        );
                    }
                    $object->setFileParams(
                        $oldObject->id,
                        sprintf($oldObject->isPhoto() ? NewsObjects::FILE_PHOTO : NewsObjects::FILE_VIDEO, $file->id),
                        0,
                        NewsObjects::FILE_FIELD,
                        $file->caption,
                        $file->ord ?: 1,
                        $oldObject->isVideo() ? $file->duration : 0,
                        $oldObject->isVideo() ? 0 : 4,
                        FilesConverter::SRC_NEWS_PHOTO_DIR . str_replace('/res/galleries', '', $gallery->location)
                    );

                    if ($tag = str_replace('<tags />', '', trim($file->tags))) {
                        $tags[] = $tag;
                    }
                }
            }
        }

        return $tags;
        /*$object->filesUrl = $oldObject->isText()
            ? News::TEXT_URL
            : ($oldObject->isPhoto() ? News::PHOTO_URL : News::VIDEO_URL);

        // фотки обычных новостей
        if ($oldObject->isText()) {
            $object->setFileParams($oldObject->id, null, 0, null, '', 1, 0, 4);
        } elseif ($gallery_id = $oldObject->getGalleryId()) {// есть ли галерея
            if ($gallery = Gallery::model()->findByPk($gallery_id)) {
                if ($tag = str_replace('<tags />', '', trim($gallery->tags))) {
                    $tags[] = $tag;
                }
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
                            4
                        );
                    }

                    $object->setFileParams(
                        $file->id,
                        $filename,
                        0,
                        null,
                        $file->caption,
                        $file->ord,
                        $oldObject->isVideo() ? $file->duration : 0,
                        $oldObject->isVideo() ? 0 : 4
                    );

                    if ($tag = str_replace('<tags />', '', trim($file->tags))) {
                        $tags[] = $tag;
                    }
                }
            }
        }

        return $tags;*/
    }

    /**
     * @param array $tags
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

        foreach ($tags as $text) {
            $doc->loadXML($text);
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

                /*if ($type == MatchesConverter::TAGS_MATCH) {
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
                }*/
                $tag_id = isset($this->tags[$type][$id]) ? (int) $this->tags[$type][$id][BaseFcModel::LANG_RU] : 0;

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
        }

        return true;
    }

    /**
     * @return bool
     */
    private function removeEn()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('lang_id=:lang_id');
        $criteria->params = [':lang_id' => BaseFcModel::LANG_EN];
        $criteria->order = 'publish_date_on DESC';
        $criteria->limit = 100;
        $news = NewsObjects::model()->findAll($criteria);

        /* @var NewsObjects $n */
        foreach ($news as $n) {
            $object_id = (int) $n->getId();
            // владелец
            AdminUsersOwners::model()->deleteByPk(
                [
                    'module_id' => BaseFcModel::NEWS_MODULE_ID,
                    'object_id' => $object_id,
                    'extend_id' => '',
                    'user_id'   => NewsObjects::ADMIN_ID
                ]
            );
            // многоязычность
            $criteria = new CDbCriteria();
            $criteria->addCondition('multilang_id=:multi');
            $criteria->addCondition('entity_id=:entity_id');
            $criteria->addCondition('lang_id=:lang_id');
            $criteria->params = [
                ':multi' => $n->getMultilangId(),
                ':entity_id' => $object_id,
                ':lang_id' => BaseFcModel::LANG_EN
            ];
            CoreMultilangLink::model()->deleteAll($criteria);
            // теги
            NewsObjects::model()->dbConnection->createCommand(
                'DELETE
                    `o`
                FROM
                    `fc__tags__modules` AS `m`
                    LEFT JOIN `fc__tags__objects` AS `o`
                        ON `o`.`link_id`=`m`.`link_id`
                WHERE
                    `m`.`module_id`=:module_id AND `o`.`object_id`=:object_id'
            )->execute([':module_id' => BaseFcModel::NEWS_MODULE_ID, ':object_id' => $object_id]);
            // файлы
            $criteria = new CDbCriteria();
            $criteria->addCondition('module_id=:module_id');
            $criteria->addCondition('category_id=:category_id');
            $criteria->addCondition('object_id=:object_id');
            $criteria->params = [
                ':module_id' => BaseFcModel::NEWS_MODULE_ID,
                ':category_id' => 0,
                ':object_id' => $object_id
            ];
            $files = [];
            foreach (FilesLink::model()->findAll($criteria) as $link) {
                if (!in_array($link->file_id, $files)) {
                    $files[] = $link->file_id;
                }
                $link->delete();
            }
            foreach ($files as $file_id) {
                if (0 == (int) FilesLink::model()->countByAttributes(['file_id' => $file_id])) {
                    $file = Files::model()->findByPk($file_id);
                    foreach (['name', 'thumb1', 'thumb2', 'thumb3', 'thumb4'] as $name) {
                        $path = FilesConverter::DST_DIR . $file->path . $file->$name;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $file->delete();
                }
            }
            // сами обекты
            NewsCategoryObjects::model()->deleteAllByAttributes(['object_id' => $n->getId()]);
            $n->delete();

            $this->doneNews++;
            $this->progress();
        }

        return true;
    }

    public function removeDouble()
    {
        $db = NewsObjects::model()->dbConnection;
        $news = $db->createCommand(
            'SELECT o.object_id, COUNT(co.category_id) AS cnt
	FROM fc__news__objects AS o
	JOIN fc__news__category_objects AS co ON co.object_id=o.object_id
	GROUP BY o.object_id
	HAVING cnt=1'
        )->queryColumn();

        foreach ($news as $object_id) {
            $n = NewsObjects::model()->findByPk($object_id);
            // владелец
            AdminUsersOwners::model()->deleteByPk(
                [
                    'module_id' => BaseFcModel::NEWS_MODULE_ID,
                    'object_id' => $object_id,
                    'extend_id' => '',
                    'user_id'   => NewsObjects::ADMIN_ID
                ]
            );
            // многоязычность
            $criteria = new CDbCriteria();
            $criteria->addCondition('multilang_id=:multi');
            $criteria->addCondition('entity_id=:entity_id');
            $criteria->params = [
                ':multi' => $n->getMultilangId(),
                ':entity_id' => $object_id
            ];
            CoreMultilangLink::model()->deleteAll($criteria);
            // теги
            NewsObjects::model()->dbConnection->createCommand(
                'DELETE
                    `o`
                FROM
                    `fc__tags__modules` AS `m`
                    LEFT JOIN `fc__tags__objects` AS `o`
                        ON `o`.`link_id`=`m`.`link_id`
                WHERE
                    `m`.`module_id`=:module_id AND `o`.`object_id`=:object_id'
            )->execute([':module_id' => BaseFcModel::NEWS_MODULE_ID, ':object_id' => $object_id]);
            // файлы
            $criteria = new CDbCriteria();
            $criteria->addCondition('module_id=:module_id');
            $criteria->addCondition('category_id=:category_id');
            $criteria->addCondition('object_id=:object_id');
            $criteria->params = [
                ':module_id' => BaseFcModel::NEWS_MODULE_ID,
                ':category_id' => 0,
                ':object_id' => $object_id
            ];
            $files = [];
            foreach (FilesLink::model()->findAll($criteria) as $link) {
                if (!in_array($link->file_id, $files)) {
                    $files[] = $link->file_id;
                }
                $link->delete();
            }
            foreach ($files as $file_id) {
                if (0 == (int) FilesLink::model()->countByAttributes(['file_id' => $file_id])) {
                    $file = Files::model()->findByPk($file_id);
                    foreach (['name', 'thumb1', 'thumb2', 'thumb3', 'thumb4'] as $name) {
                        $path = FilesConverter::DST_DIR . $file->path . $file->$name;
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                    $file->delete();
                }
            }
            // сами обекты
            NewsCategoryObjects::model()->deleteAllByAttributes(['object_id' => $object_id]);
            $n->delete();

            $this->doneNews++;
            $this->progress();
        }

        return true;
    }

    private function removeOld()
    {
        $db = NewsObjects::model()->dbConnection;
        //choose
        $news = $db->createCommand(
            'SELECT ml.entity_id
             FROM fc__core__multilang_link AS ml
JOIN fc__core__multilang AS m ON m.id=ml.multilang_id AND m.module_id=' . BaseFcModel::NEWS_MODULE_ID . ' AND
m.entity=\'' . NewsObjects::ENTITY . '\' AND m.import_id!=0'
        )->queryColumn();
        $news = implode(',', $news);
        // owners
        $db->createCommand(
            'DELETE FROM fc__admin_users__owners WHERE module_id=' . BaseFcModel::NEWS_MODULE_ID . '
            AND object_id IN (' . $news . ')'
        )->execute();
        // multilang
        $db->createCommand(
            'DELETE m, ml
             FROM fc__core__multilang AS m
JOIN fc__core__multilang_link AS ml ON ml.multilang_id=m.id AND m.module_id=' . BaseFcModel::NEWS_MODULE_ID . ' AND
m.entity=\'' . NewsObjects::ENTITY . '\' AND m.import_id!=0'
        )->execute();
        // tags
        /*$db->createCommand(
            'DELETE
                `m`, `o`
            FROM
                `fc__tags__modules` AS `m`
                LEFT JOIN `fc__tags__objects` AS `o`
                    ON `o`.`link_id`=`m`.`link_id`
            WHERE
                `m`.`module_id`=' . BaseFcModel::NEWS_MODULE_ID . ' AND `o`.`object_id` IN (' . $news . ')'
        )->execute();*/
        // files
        $db->createCommand(
            'DELETE
                `f`, `fl`
            FROM
                `fc__files` AS `f`
                LEFT JOIN `fc__files__link` AS `fl`
                    ON `fl`.`file_id`=`f`.`file_id`
            WHERE
                `fl`.`module_id`=' . BaseFcModel::NEWS_MODULE_ID . ' AND `fl`.`object_id` IN (' . $news . ')'
        )->execute();
        // objects
        $db->createCommand(
            'DELETE FROM fc__news__category_objects WHERE object_id IN (' . $news . ')'
        )->execute();
        $db->createCommand(
            'DELETE FROM fc__news__objects WHERE object_id IN (' . $news . ')'
        )->execute();
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneCats, $this->doneAll, $this->doneNews, $this->doneTags);
    }

    /**
     * @return array
     */
    public function getNews()
    {
        return file_exists($this->newsFile) ? include $this->newsFile : [];
    }
}
