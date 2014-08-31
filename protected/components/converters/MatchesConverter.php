<?php

/**
 * Конвертер матчей
 *
 * @package    converter
 * @subpackage matches
 * @author     Nikolaj Rudakov <n.rudakov@bstsoft.ru>
 * @copyright  2014
 */
class MatchesConverter implements IConverter
{
    /**
     * @var string
     */
    const TAGS_MATCH = 'match';

    /**
     * Команды.
     *
     * @var array
     */
    private $teams = [];

    /**
     * @var array
     */
    private $teamsM = [];

    /**
     * Игроки.
     *
     * @var array
     */
    private $players = [];

    /**
     * @var array
     */
    private $playersM = [];

    /**
     * Сезоны.
     *
     * @var array
     */
    private $seasons = [];

    /**
     * Чемпионаты.
     *
     * @var array
     */
    private $champs = [];

    /**
     * Этапы.
     *
     * @var array
     */
    private $stages = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $matches = [];

    /**
     * @var array
     */
    private $tagsFile = '';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rMatches: %d (%d). Events: %d (%d). Placements: %d. Tags: %d (%d)";

    /**
     * @var integer
     */
    private $doneMatches = 0;

    /**
     * @var integer
     */
    private $doneEvents = 0;

    /**
     * @var integer
     */
    private $donePlacements = 0;

    /**
     * @var integer
     */
    private $doneTags = 0;

    /**
     * @var array
     */
    private static $types = [
        'timeout'           => FcEvent::TYPE_TIMEOUT,
        'goal'              => FcEvent::TYPE_GOAL,
        'autogoal'          => FcEvent::TYPE_AUTOGOAL,
        'goalfrompenalty'   => FcEvent::TYPE_GOALPENALTY,
        'pin'               => FcEvent::TYPE_CAMEOFFBENCH,
        'yc'                => FcEvent::TYPE_YELLOWCARD,
        'ycyc'              => FcEvent::TYPE_SECONDYELLOWCARD,
        'rc'                => FcEvent::TYPE_REDCARD,
        'unrealizedpenalty' => FcEvent::TYPE_MISSEDPENALTY,
        'pout'              => FcEvent::TYPE_LEFTONBENCH,
        'cornergoal'        => FcEvent::TYPE_GOALCORNER,
        'finegoal'          => FcEvent::TYPE_GOALSHTRAFNOY,
        'help'              => FcEvent::TYPE_ASSISTS
    ];

    /**
     * Инициализация.
     */
    public function __construct()
    {
        $this->tagsFile = Yii::getPathOfAlias('accordance') . '/tags.php';

        // команды и игрока уже должны быть перенесены
        $pc = new PlayersConverter();
        $this->teams    = $pc->getTeams();
        $this->teamsM   = $pc->getTeamsM();
        $this->players  = $pc->getPlayers();
        $this->playersM = $pc->getPlayersM();
        $this->tags     = $pc->getTags();
        $this->tags[self::TAGS_MATCH] = [];

        // сезоны и чемпионаты уже должны быть пересены
        $cc = new ChampsConverter();
        $this->seasons = $cc->getSeasons();
        $this->champs  = $cc->getChamps();
        $this->stages  = $cc->getStages();
    }

    /**
     * Запуск преобразований.
     */
    public function convert()
    {
        $this->progress();
        /*$this->removeMatches();
        sleep(10);
        $this->saveMatches();*/
        //$this->saveMultilang();
        $this->missedMatches();
    }

    private function saveMultilang()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'matchtime DESC, id ASC';
        $criteria->limit = 40;
        $src_match = new FcMatch();
        $en_id = 0;

        /* @var FcMatch $match */
        foreach ($src_match->findAll($criteria) as $match) {
            if ($match->getId() == $en_id) {
                continue;
            }
            if (!$match->getMultilangId()) {
                $multilang = new CoreMultilang();
                $multilang->module_id = $match->module->module_id;
                $multilang->entity = FcMatch::ENTITY;
                $multilang->save();
                $multilang_link = new CoreMultilangLink();
                $multilang_link->multilang_id = $multilang->getId();
                $multilang_link->entity_id = $match->getId();
                $multilang_link->lang_id = BaseFcModel::LANG_RU;
                $multilang_link->save();
                $multilang_link->setIsNewRecord(true);
                $multilang_link->entity_id = $match->getId() + 1;
                $multilang_link->lang_id = BaseFcModel::LANG_EN;
                $multilang_link->save();
                $en_id = $multilang_link->entity_id;
                $this->doneMatches++;
                $this->progress();
            }
        }
    }

    private function removeMatches()
    {
        $db = FcMatch::model()->dbConnection;
        /*$tags = $db->createCommand(
            'SELECT tag_id FROM fc__tags WHERE category_id=' . TagsCategories::MATCHES
        )->queryColumn();
        $tags = implode(',', $tags);
        $links = $db->createCommand(
            'SELECT link_id FROM fc__tags__modules WHERE tag_id IN (' . $tags . ')'
        )->queryColumn();
        $links = implode(',', $links);
        $db->createCommand(
            'DELETE FROM fc__tags__sources WHERE link_id IN (' . $links . ')'
        )->execute();
        $db->createCommand(
            'DELETE FROM fc__tags__objects WHERE link_id IN (' . $links . ')'
        )->execute();
        $db->createCommand(
            'DELETE FROM fc__tags__modules WHERE link_id IN (' . $links . ')'
        )->execute();
        $db->createCommand(
            'DELETE FROM fc__tags WHERE tag_id IN (' . $tags . ')'
        )->execute();*/
        $db->createCommand(
            'DELETE m, ml
            FROM fc__core__multilang AS m
            LEFT JOIN fc__core__multilang_link AS ml ON ml.multilang_id=m.id
            WHERE m.entity=:match OR m.entity=:event'
        )->execute([':match' => 'match', ':event' => 'event']);
        $db->createCommand('TRUNCATE TABLE fc__fc__match')->execute();
        $db->createCommand('TRUNCATE TABLE fc__fc__event')->execute();
        $db->createCommand('TRUNCATE TABLE fc__fc__placement')->execute();

        return true;
    }

    /**
     * Сохранение матчей.
     *
     * @throws CException
     */
    private function saveMatches()
    {
        $teams = implode(',', array_keys($this->teams));
        $this->stages[0] = 0;
        $criteria = new CDbCriteria();
        $criteria->alias = 'sch';
        $criteria->select = [
            'id', 'season', 'tournament', 'stage', 'circle', 'team1', 'team2', 'date', 'region', 'stadium',
            'country'
        ];
        $criteria->addCondition(
            [$criteria->alias . '.team1 IN(' . $teams . ')', $criteria->alias . '.team2 IN(' . $teams . ')'],
            'OR'
        );
        $criteria->addInCondition($criteria->alias . '.season', array_keys($this->seasons));
        $criteria->addInCondition($criteria->alias . '.tournament', array_keys($this->champs));
        $criteria->addInCondition($criteria->alias . '.stage', array_keys($this->stages));
        /*$criteria->with = [
            'match' => [
                'select' => [
                    'audience', 'mainreferee', 'linereferee1', 'linereferee2', 'sparereferee', 'delegate', 'inspector',
                    'summary', 'weather', 'state', 'date'
                ]
            ]
        ];*/
        $criteria->order = $criteria->alias . '.id';
        $src_matches = new Schedule();

        $get_score = function ($score) {
            $home = $guest = 0;

            $doc = new DOMDocument();
            $doc->loadXML($score);
            /* @var DomElement $tag */
            foreach ($doc->documentElement->childNodes as $tag) {
                if ($tag->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                $value = (int) $tag->nodeValue;

                if ($tag->nodeName == 'goals1') {
                    $home = $value;
                } else {
                    $guest = $value;
                }
            }

            return [$home, $guest];
        };

        foreach ($src_matches->findAll($criteria) as $s) {
            $home_team = $this->getTrueTeam($s->team1, $s->tournament);
            $guest_team = $this->getTrueTeam($s->team2, $s->tournament);
            /* @var Matches $m */
            $m = $s->match;
            if ($m) {
                $m->id = (int) $m->id;
            } else {
                $m = new Matches();
                $m->id = 0;
                $m->date = $s->date;
            }
            $match = new FcMatch();
            $match->importId        = $m->id;
            $match->championship_id = $this->champs[$s->tournament][BaseFcModel::LANG_RU];
            $match->season_id       = $this->seasons[$s->season][BaseFcModel::LANG_RU];
            $match->stage_id        = $this->stages[$s->stage][BaseFcModel::LANG_RU];
            $match->home_team_id    = $home_team[BaseFcModel::LANG_RU];
            $match->guest_team_id   = $guest_team[BaseFcModel::LANG_RU];

            // переносили уже
            $exists_match = FcMatch::model()->find(
                new CDbCriteria(
                    [
                        'condition' => 'season_id=:season_id AND stage_id=:stage_id AND home_team_id=:home_team_id ' .
                            'AND guest_team_id=:guest_team_id',
                        'params' => [
                            ':season_id'     => $match->season_id,
                            ':stage_id'      => $match->stage_id,
                            ':home_team_id'  => $match->home_team_id,
                            ':guest_team_id' => $match->guest_team_id
                        ]
                    ]
                )
            );

            if ($exists_match) {
                /*if ($m->id) {  echo 1;
                    /*$matches = [
                        BaseFcModel::LANG_RU => $exists_match->getId(),
                        BaseFcModel::LANG_EN => $exists_match->getPairId()
                    ];
                    $multilang = CoreMultilang::model()->findByPk($exists_match->getMultilangId());
                    $multilang->import_id = $m->id;
                    $multilang->save();*
                    $this->saveMatchPlaces($m->players, $exists_match->getMultilangId(), $s->tournament);
                    $this->doneMatches--;
                    $this->progress();
                }*/
                /*if ($m->summary) {
                    list($exists_match->home_score, $exists_match->guest_score) = $get_score($m->summary);
                    $exists_match->save(false);
                    // сохраняем счет для англ версии если она есть
                    if ($exists_match_en = FcMatch::model()->findByPk($exists_match->getId() + 1)) {
                        list($exists_match_en->home_score, $exists_match_en->guest_score) = [
                            $exists_match->home_score, $exists_match->guest_score
                        ];
                        $exists_match_en->save(false);
                    }
                    $this->doneMatches--;
                    $this->progress();
                }*/
                continue;
            } else {
                $match->tour                = $s->circle;
                $match->city                = $s->region;
                $match->stadium             = $s->stadium;
                $match->viewers             = $m->audience;
                $match->referee_main        = $m->mainreferee;
                $match->referee_line_1      = $m->linereferee1;
                $match->referee_line_2      = $m->linereferee2;
                $match->referee_main_helper = $m->sparereferee;
                $match->delegate            = $m->delegate;
                $match->inspector           = $m->inspector;
                $match->weather             = $m->weather;
                $match->held                = $m->state > 1 ? 1 : (int) $m->state;
                $match->matchtime           = $m->date;

                if ($m->summary) {
                    list($match->home_score, $match->guest_score) = $get_score($m->summary);
                }

                if (!$match->save()) {
                    throw new CException(
                        'Match not created.' . "\n" .
                        var_export($match->getErrors(), true) . "\n" .
                        $s . "\n" . $m . "\n"
                    );
                }

                $this->matches[$m->id] = $match->getMultilangId();

                $matches[BaseFcModel::LANG_RU] = $match->id;
                $match->setNew();
                $match->championship_id     = $this->champs[$s->tournament][BaseFcModel::LANG_EN];
                $match->season_id           = $this->seasons[$s->season][BaseFcModel::LANG_EN];
                $match->stage_id            = $this->stages[$s->stage][BaseFcModel::LANG_EN];
                $match->home_team_id        = $home_team[BaseFcModel::LANG_EN];
                $match->guest_team_id       = $guest_team[BaseFcModel::LANG_EN];
                $match->save();
                $matches[BaseFcModel::LANG_EN] = $match->id;

                $this->doneMatches++;
                $this->progress();
            }

            if ($m->id) {
                if (!$exists_match) {
                    $this->saveMatchEvents($m->events, $matches, $s->tournament);
                    $this->saveMatchPlaces($m->players, $match->getMultilangId(), $s->tournament);
                }
                $this->saveTags(
                    $m->id,
                    $match->getMultilangId(),
                    implode(
                        ' ',
                        [
                            date('Ymd', strtotime($match->matchtime)),
                            str_replace(['«', '»'], '', $s->homeTeam->title . ':' . $s->guestTeam->title),
                            $s->champ->title,
                            $s->s->title,
                            ($s->st ? $s->st->title : '')
                        ]
                    )
                );
            }
        }

        file_put_contents($this->tagsFile, sprintf(self::FILE_ACCORDANCE, var_export($this->tags, true)));
    }

    /**
     * Сохранение событий матча.
     *
     * @param Matchevents[] $events
     * @param array $matches
     * @param integer $champId
     *
     * @throws CException
     */
    private function saveMatchEvents($events, $matches, $champId)
    {
        foreach ($events as $e) {
            $event = new FcEvent();
            //$event->importId = $e->id;
            $event->match_id = $matches[BaseFcModel::LANG_RU];
            $event->team_id = 0;
            $event->person_id = 0;
            if (isset($this->teams[$e->team])) {
                $teams = $this->getTrueTeam($e->team, $champId);
                $event->team_id = $teams[BaseFcModel::LANG_RU];
            }
            if (isset($this->players[$e->player])) {
                $players = $this->players[$e->player];
                $event->person_id = $players[BaseFcModel::LANG_RU];
            }
            $event->gametime     = $e->firetime;
            $event->gametimeplus = $e->injurytime;
            $event->comment      = $e->comment;
            $event->type         = FcEvent::TYPE_COMMENT;

            foreach (array_keys(self::$types) as $type) {
                if ((bool) $e->$type) {
                    $event->type = isset(self::$types[$type]) ? self::$types[$type] : FcEvent::TYPE_COMMENT;
                    break;
                }
            }

            if (!$event->save()) {
                throw new CException(
                    'Match event not created.' . "\n" .
                    var_export($event->getErrors(), true) . "\n" .
                    $e . "\n"
                );
            }

            $event->setNew();
            $event->match_id = $matches[BaseFcModel::LANG_EN];
            $event->team_id = isset($teams) ? $teams[BaseFcModel::LANG_EN] : 0;
            $event->person_id = isset($players) ? $players[BaseFcModel::LANG_EN] : 0;
            $event->save();

            $this->doneEvents++;
            $this->progress();
        }
    }

    /**
     * Сохранение расстановки матча.
     *
     * @param Matchplayers[] $matchPlayers
     * @param integer $matchId
     * @param integer $champId
     *
     * @throws CException
     */
    private function saveMatchPlaces($matchPlayers, $matchId, $champId)
    {
        foreach ($matchPlayers as $p) {
            if (!isset($this->teamsM[$p->team]) || !isset($this->playersM[$p->player])) {
                continue;
            }

            $placement = new FcPlacement();
            $placement->match_id  = $matchId;
            $placement->team_id   = $this->getTrueTeamM($p->team, $champId);
            $placement->person_id = $this->playersM[$p->player];
            $placement->captain   = (int) $p->captain;
            $placement->xpos      = (int) $p->schemaleft;
            $placement->ypos      = (int) $p->schematop;
            $placement->staff     = $p->isMain() ? FcPlacement::STAFF_MAIN : FcPlacement::STAFF_SPARE;

            if (!$placement->save()) {
                throw new CException(
                    'Match placement not created.' . "\n" .
                    var_export($placement->getErrors(), true) . "\n" .
                    $p . "\n"
                );
            }

            /*$placement->setNew();
            $placement->match_id  = $matches[BaseFcModel::LANG_EN];
            $placement->team_id   = $teams[BaseFcModel::LANG_EN];
            $placement->person_id = $players[BaseFcModel::LANG_EN];
            $placement->save();*/

            $this->donePlacements++;
            $this->progress();
        }
    }

    /**
     * @param integer $teamId
     * @param integer $champId
     *
     * @return integer
     */
    private function getTrueTeam($teamId, $champId)
    {
        return Tournaments::isJunior($champId)
            ? $this->teams[$teamId][FcTeams::JUNIOR]
            : $this->teams[$teamId][FcTeams::MAIN];
    }

    /**
     * @param integer $teamId
     * @param integer $champId
     *
     * @return integer
     */
    private function getTrueTeamM($teamId, $champId)
    {
        return Tournaments::isJunior($champId)
            ? $this->teamsM[$teamId][FcTeams::JUNIOR]
            : $this->teamsM[$teamId][FcTeams::MAIN];
    }

    /**
     * @param integer $entityId
     * @param integer $multilangId
     * @param string  $title
     *
     * @return bool
     * @throws CException
     */
    private function saveTags($entityId, $multilangId, $title)
    {
        $name = substr(preg_replace('/(?!-)[\W]+/', '_', Utils::rus2lat($title)), 0, 255) . '_';
        $title .= '_';
        $tag = new Tags();
        $tag->category_id = TagsCategories::MATCHES;
        $tag->name = $name . BaseFcModel::LANG_RU;
        $tag->title = $title . BaseFcModel::LANG_RU;

        // переносили уже
        $exists_tag = Tags::model()->find(
            new CDbCriteria(['condition' => 'title=:title', 'params' => [':title' => $tag->title]])
        );

        if ($exists_tag) {
            return true;
        }

        $tag->publish = 1;
        $tag->priority = 0;

        if (!$tag->save()) {
            throw new CException('Tag not created.' . "\n" . var_export($tag->getErrors(), true) . "\n");
        }

        $ru_id = (int) $tag->getId();
        $this->saveTagLinks($ru_id, $multilangId);
        $tag->setNew();
        $tag->name = $name . BaseFcModel::LANG_EN;
        $tag->title = $title . BaseFcModel::LANG_EN;
        $tag->save();
        $en_id = (int) $tag->getId();
        $this->saveTagLinks($en_id, $multilangId);

        $this->tags[self::TAGS_MATCH][$entityId] = [BaseFcModel::LANG_RU => $ru_id, BaseFcModel::LANG_EN => $en_id];

        $this->doneTags++;
        $this->progress();

        return true;
    }

    /**
     * @param integer $tagId
     * @param integer $objectId
     *
     * @return bool
     */
    private function saveTagLinks($tagId, $objectId)
    {
        $modules = new TagsModules();
        $modules->tag_id = $tagId;
        $modules->module_id = BaseFcModel::FC_MODULE_ID;
        $modules->publish = 1;
        $modules->is_default = 0;
        $modules->save();
        $objects = new TagsSources();
        $objects->link_id = $modules->link_id;
        $objects->object_id = $objectId;
        $objects->save();
        $modules->setNew();
        $modules->module_id = BaseFcModel::NEWS_MODULE_ID;
        $modules->save();

        return true;
    }

    private function missedMatches()
    {
        $criteria = new CDbCriteria();
        $criteria->order = 'id DESC';
        $matches = new FcMatch();

        /* @var FcMatch $match */
        foreach ($matches->findAll($criteria) as $match) {
            $tag_name = implode(
                ' ',
                [
                    date('Ymd', strtotime($match->matchtime)),
                    $match->homeTeam->title . ':' . $match->guestTeam->title,
                    $match->champ->title,
                    $match->season->title,
                    ($match->stage ? $match->stage->title : '')
                ]
            );
            $tag_name .= ' _1';
            echo "$tag_name\n";
            $tag = Tags::model()->findByAttributes(['title' => $tag_name]);
            if ($tag) {
                echo 1;
            }
        }
    }

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneMatches,
            $this->doneMatches * 2,
            $this->doneEvents,
            $this->doneEvents * 2,
            $this->donePlacements,
            $this->doneTags,
            $this->doneTags * 2
        );
    }
}
