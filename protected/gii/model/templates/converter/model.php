<?php
/**
 * This is the template for generating the model class of a specified table.
 * - $this: the ModelCode object
 * - $tableName: the table name for this class (prefix is already removed if necessary)
 * - $modelClass: the model class name
 * - $columns: list of table columns (name=>CDbColumnSchema)
 * - $labels: list of attribute labels (name=>label)
 * - $rules: list of validation rules
 * - $relations: list of relations (name=>relation declaration)
 */
?>
<?php echo "<?php\n"; ?>

/**
 * Модель таблицы "<?php echo $tableName; ?>".
 *
 * Доступные поля таблицы "<?php echo $tableName; ?>":
<?php foreach($columns as $column): ?>
<?$labels[$column->name] = $column->comment;?>
 * @property <?php echo $column->type.' $'.$column->name.' '.$labels[$column->name].".\n"; ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
 * Доступные отношения:
<?php foreach($relations as $name=>$relation): ?>
 * @property <?php
    if (preg_match("~^array\(self::([^,]+), '([^']+)', '([^']+)'\)$~", $relation, $matches))
    {
        $relationType = $matches[1];
        $relationModel = $matches[2];

        switch($relationType){
            case 'HAS_ONE':
                echo $relationModel.' $'.$name."\n";
            break;
            case 'BELONGS_TO':
                echo $relationModel.' $'.$name."\n";
            break;
            case 'HAS_MANY':
                echo $relationModel.'[] $'.$name."\n";
            break;
            case 'MANY_MANY':
                echo $relationModel.'[] $'.$name."\n";
            break;
            default:
                echo 'mixed $'.$name."\n";
        }
    }
    ?>
<?php endforeach; ?>
<?php endif; ?>
 *
 * @package    converter
 * @subpackage <?php echo strtolower($modelClass)."\n"; ?>
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2014
 */
class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
    /**
     * @return string Таблица модели
     */
    public function tableName()
    {
        return '<?php echo $tableName; ?>';
    }

    /**
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
<?php foreach($rules as $rule): ?>
            <?php echo str_replace(['array(', '),'], ['[', '],'], $rule.",\n"); ?>
<?php endforeach; ?>
            ['<?php echo implode(', ', array_keys($columns)); ?>', 'safe', 'on'=>'search'],
        ];
    }

    /**
     * @return array Отношения модели.
     */
    public function relations()
    {
<?php if ($relations): ?>
        return [
<?php foreach($relations as $name=>$relation): ?>
            <?php echo "'$name' => $relation,\n"; ?>
<?php endforeach; ?>
        ];
<?php else: ?>
        return [];
<?php endif; ?>
    }

    /**
     * @return array Атрибуты модели.
     */
    public function attributeLabels()
    {
        return [
<?php foreach($labels as $name=>$label): ?>
            <?php echo "'$name' => '$label',\n"; ?>
<?php endforeach; ?>
        ];
    }

<?php if($connectionId!='db'):?>
    /**
     * @return CDbConnection Класс соединения с БД.
     */
    public function getDbConnection()
    {
        return Yii::app()-><?php echo $connectionId ?>;
    }

<?php endif?>
    /**
     * Статический метод возвращения модели.
     *
     * @param string $className Имя класса.
     * @return <?php echo $modelClass; ?> Модель.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }
}
