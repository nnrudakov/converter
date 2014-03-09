<?php

/**
 * Конвертация других систем в SamoletCMS.
 *
 * @package    converter
 * @subpackage console
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class ConverterCommand extends CConsoleCommand
{
    /**
     * Справка.
     *
     * @see CConsoleCommand::getHelp()
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
    yiic converter [action] [parameter]

DESCRIPTION
    Converter console launcher.

EXAMPLES
    * yiic converter news
        Convert news.

        Parameters:

        --.

EOD;
    }

    /**
     * Конвертация новостей.
     */
    public function actionNews()
    {
        $this->saveCategories();

        print "Done.\n";
    }

    /**
     * Сохранение категорий.
     *
     * @param integer $oldParent Идентификатор старого родителя.
     * @param integer $newParent Идентификатор новго родителя.
     */
    private function saveCategories($oldParent = 0, $newParent = 0)
    {
        $criteria = new CDbCriteria([
            'select'    => ['id', 'name', 'description'],
            'order'     => 'id'
        ]);
        if ($oldParent) {
            $criteria->addCondition('parentid=:parent');
            $criteria->params = [':parent' => $oldParent];
        } else {
            $criteria->addCondition('parentid IS NULL');
        }
        $src_news = new NewsCategs();

        foreach ($src_news->findAll($criteria) as $i => $cat) {
            $category = new NewsCategories();
            $category->setAttributes(
                [
                    'parent_id'  => $newParent,
                    'lang_id'    => 1,
                    'name'       => Utils::nameString($cat->name),
                    'title'      => $cat->name,
                    'content'    => $cat->description ?: '',
                    'publish'    => 1,
                    'sort'       => $i + 1,
                    'meta_title' => $cat->name
                ]
            );
            $category->save();
            $this->saveCategories($cat->id, $category->category_id);
        }
    }
}
