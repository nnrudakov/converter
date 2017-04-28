<?php

/**
 * Конвертер многоязычности ФК.
 *
 * @package    converter
 * @subpackage move_fc
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2017
 */
class FcMultilangConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rChampionships: %d. Events: %d. Matches: %d. Players: %d. Seasons: %d. Stages: %d. Teams: %d";

    /**
     * @var integer
     */
    private $doneChamps = 0;

    /**
     * @var integer
     */
    private $doneEvents = 0;

    /**
     * @var integer
     */
    private $doneMatches = 0;

    /**
     * @var integer
     */
    private $donePlayers = 0;

    /**
     * @var integer
     */
    private $doneSeasons = 0;

    /**
     * @var integer
     */
    private $doneStages = 0;

    /**
     * @var integer
     */
    private $doneTeams = 0;

    /**
     * Запуск преобразований.
     *
     * @throws CDbException
     */
    public function convert()
    {
        $this->progress();

        foreach ($this->getEntites() as $model_entity) {
            $model = $this->getModel($model_entity);
            foreach ($this->getMultilangs($model_entity) as $multilang) {
                $entities = $multilang->entities;
                foreach ($entities as $entity) {
                    $model->updateByPk(
                        $entity->entity_id,
                        ['multilang_id' => $multilang->id, 'lang_id' => $entity->lang_id],
                        'multilang_id=0'
                    );

                    switch ($model_entity) {
                        case FcChampionship::ENTITY:  $this->doneChamps++;  break;
                        case FcEvent::ENTITY:         $this->doneEvents++;  break;
                        case FcMatch::ENTITY:         $this->doneMatches++; break;
                        case FcPerson::ENTITY:        $this->donePlayers++; break;
                        case FcSeason::ENTITY:        $this->doneSeasons++; break;
                        case FcStage::ENTITY:         $this->doneStages++;  break;
                        case FcTeams::ENTITY:         $this->doneTeams++;   break;
                        default:                                            break;
                    }
                    $this->progress();
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getEntites()
    {
        return [
            FcChampionship::ENTITY, FcEvent::ENTITY, FcMatch::ENTITY, FcPerson::ENTITY, FcSeason::ENTITY,
            FcStage::ENTITY, FcTeams::ENTITY
        ];
    }

    /**
     * @param string $entity
     *
     * @return DestinationModel
     */
    private function getModel($entity)
    {
        $class = 'Fc' . ucfirst($entity === 'team' ? 'teams' : $entity);

        return new $class();
    }

    /**
     * @param string $entity
     *
     * @return CoreMultilang[]
     */
    private function getMultilangs($entity)
    {
        $criteria = new CDbCriteria();
        $criteria->select = ['id'];
        $criteria->addCondition('module_id=:module_id');
        $criteria->addCondition('entity=:entity');
        $criteria->params = [':module_id' => BaseFcModel::FC_MODULE_ID, ':entity' => $entity];
        $criteria->with = 'entities';
        $criteria->order = 'id';

        return CoreMultilang::model()->findAll($criteria);
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneChamps,
            $this->doneEvents,
            $this->doneMatches,
            $this->donePlayers,
            $this->doneSeasons,
            $this->doneStages,
            $this->doneTeams
        );
    }
}
