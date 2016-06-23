<?php

/**
 * Перенос новостей из одной категории в другую.
 *
 * @package    converter
 * @subpackage move_news
 * @author     rudnik <nnrudakov@gmail.com>
 * @copyright  2016
 */
class ReplaceEventConverter implements IConverter
{
    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rCollected: %d. Not RU: %d. Stored: %d.";

    /**
     * @var integer
     */
    private $doneCollected = 0;

    /**
     * @var integer
     */
    private $doneNotRu = 0;

    /** @var integer */
    private $doneStored = 0;

    /**
     * Запуск преобразований.
     *
     * @throws \CException
     */
    public function convert()
    {
        $this->storeEvents($this->collectAndGroupEvents());
    }

    /**
     * @return FcEvent[]
     *
     * @throws \CException
     */
    private function collectAndGroupEvents()
    {
        $criteria = new CDbCriteria();
        $criteria->addCondition('type=:out');
        $criteria->addCondition('type=:in', 'OR');
        $criteria->order = 'match_id, gametime, gametimeplus, id';
        $criteria->params = [':out' => FcEvent::TYPE_LEFTONBENCH, ':in' => FcEvent::TYPE_CAMEOFFBENCH];
        print "Finding...\n";
        $events_all = FcEvent::model()->findAll($criteria);
        $events = [];
        $i = 0;
        $this->progress();

        /** @var FcEvent $event */
        foreach ($events_all as $event) {
            if ($event->getMultilangLangId() !== BaseFcModel::LANG_RU) {
                $event->delete();
                $this->doneNotRu++;
                $this->progress();
                continue;
            }
            // group by match
            if (!array_key_exists($event->match_id, $events)) {
                $events[$event->match_id] = [];
            }
            // group by game time
            if (!array_key_exists($event->gametime, $events[$event->match_id])) {
                $events[$event->match_id][$event->gametime] = [];
            }
            // group by teams
            if (!array_key_exists($event->team_id, $events[$event->match_id][$event->gametime])) {
                $i = 0;
                $events[$event->match_id][$event->gametime][$event->team_id][$i] = [];
            } elseif (!array_key_exists($i, $events[$event->match_id][$event->gametime][$event->team_id])) {
                $events[$event->match_id][$event->gametime][$event->team_id][$i] = [];
            }
            // collect events
            if (count($events[$event->match_id][$event->gametime][$event->team_id][$i]) === 2) {
                $i++;
            }
            $events[$event->match_id][$event->gametime][$event->team_id][$i][] = $event;

            $this->doneCollected++;
            $this->progress();
        }

        return $events;
    }

    /**
     * @param FcEvent[] $collectedEvents
     *
     * @throws \CException
     */
    private function storeEvents($collectedEvents)
    {
        $bad_matches = [];
        foreach ($collectedEvents as $match_id => $times) {
            foreach ($times as $time => $teams) {
                foreach ($teams as $team_id => $events_arr) {
                    foreach ($events_arr as $events) {
                        if (count($events) !== 2) {
                            $bad_matches[] = $match_id;
                            continue;
                        }
                        /** @var FcEvent $out */
                        /** @var FcEvent $in */
                        if (FcEvent::TYPE_LEFTONBENCH === $events[0]->type) {
                            list($out, $in) = $events;
                        } else {
                            list($in, $out) = $events;
                        }
                        $event = new FcEvent();
                        $event->setAttributes(
                            [
                                'match_id'      => $match_id,
                                'type'          => FcEvent::TYPE_REPLACE,
                                'gametime'      => $time,
                                'gametimeplus'  => $out->gametimeplus ?: $in->gametimeplus,
                                'team_id'       => $team_id,
                                'person_id'     => 0,
                                'person_id_out' => $out->person_id,
                                'person_id_in'  => $in->person_id,
                                'comment'       => implode(' ', [trim($out->comment), trim($in->comment)])
                            ],
                            false
                        );
                        $event->save();
                        $in->delAllMultilang = $out->delAllMultilang = true;
                        $in->delete();
                        $out->delete();
                        $this->doneStored++;
                        $this->progress();
                    }
                }
            }
        }

        if ($bad_matches) {
            $fh = fopen(Yii::getPathOfAlias('bad_events') . '/bad_matches.csv', 'w');
            fputcsv($fh, array_unique($bad_matches));
            fclose($fh);
        }
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneCollected, $this->doneNotRu, $this->doneStored);
    }
}
