<?php

/**
 * Конвертер файлов.
 *
 * @package    converter
 * @subpackage contracts
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class BranchesConverter implements IConverter
{
    /**
     * @var string
     */
    const MODULE_NAME = 'branches';

    /**
     * @var string
     */
    const ENTITY = 'object';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rCategories: %d. Branches: %d (%d). Files: %d (%d)";

    /**
     * @var integer
     */
    private $doneCategories = 0;

    /**
     * @var integer
     */
    private $doneBranches = 0;

    /**
     * @var integer
     */
    private $doneFiles = 0;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var array
     */
    private $branches = [];

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        //$this->saveCategories();
        //$this->saveObjects();
        $this->saveFiles();
    }

    private function saveCategories()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'category_id';
        $src_categories = new BranchesCategories();

        foreach ($src_categories->findAll($criteria) as $c) {
            $ru_id = $c->getId();
            $c->setNew();
            $c->parent_id = isset($this->categories[$c->parent_id]) ? $this->categories[$c->parent_id] : 0;
            $c->save();
            $this->categories[$ru_id] = $c->getId();

            $this->doneCategories++;
            $this->progress();
        }
    }

    private function saveObjects()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'object_id';
        $src_branches = new BranchesObjectsSrc();
        $branches = [];

        foreach ($src_branches->findAll($criteria) as $b) {
            $branch = BranchesObjects::model()->findByAttributes(
                ['object_id' => $b->object_id, 'lang_id' => BaseFcModel::LANG_RU]
            );
            $cl = $b->catLink;
            /* @var BranchesCategoryObjectsSrc $cl */
            $cl = array_shift($cl);

            if (!$branch) {
                $branch = new BranchesObjects();
                $branch->setAttributes($b->getAttributes());
                $branch->minorCategoryId = $branch->main_category_id;

                if (!$branch->save()) {
                    throw new CException(
                        'Branch not created.' . "\n" .
                        var_export($branch->getErrors(), true) . "\n" .
                        $b . "\n"
                    );
                };

                $this->doneBranches++;
                $this->progress();
            }

            /*$ru_id = $branch->getId();
            $branch->setNew();
            $branch->main_category_id = $this->categories[$b->main_category_id];
            $branch->minorCategoryId = $branch->main_category_id;
            $branch->save();*/
            $this->branches[$b->getId()] = [BaseFcModel::LANG_RU => $branch->getId(), BaseFcModel::LANG_EN => 0];
        }
    }

    private function saveFiles()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('module_id=' . BranchesObjects::MODULE_ID);
        $criteria->order = 'file_id, object_id';
        $src_links = new FilesLink();
        $path = self::MODULE_NAME . '/' . self::ENTITY . '/';
        $files_dir = Yii::app()->params['files_dir'];

        foreach ($src_links->findAll($criteria) as $l) {
            /* @var Files $file */
            $file = $l->file;
            $file->path = $path . $l->object_id . '/';
            $file->save();
            Utils::makeDir($files_dir . $file->path);

            if (file_exists($files_dir . $file->name)) {
                $admin_name = str_replace('.' . $file->ext, '', $file->name);
                $admin_name .= '_admin.' . $file->ext;
                copy($files_dir . $file->name, $files_dir . $file->path . $file->name);
                copy($files_dir . $admin_name, $files_dir . $file->path . $admin_name);
            }

            $this->doneFiles++;
            $this->progress();
        }
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneCategories,
            $this->doneBranches,
            $this->doneBranches * 2,
            $this->doneFiles,
            $this->doneFiles * 2
        );
    }
}
