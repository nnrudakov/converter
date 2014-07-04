<?php

/**
 * Конвертер матчей
 *
 * @package    converter
 * @subpackage
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
     * Игроки.
     *
     * @var array
     */
    private $players = [];

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
    private $tagsFile = '';

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rMatches: %d (%d). Events: %d (%d). Placements: %d (%d). Tags: %d (%d)";

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
     * Инициализация.
     */
    public function __construct()
    {
        $this->tagsFile = Yii::getPathOfAlias('accordance') . '/tags.php';

        // команды и игрока уже должны быть перенесены
        $pc = new PlayersConverter();
        $this->teams   = $pc->getTeams();
        $this->players = $pc->getPlayers();
        $this->tags    = $pc->getTags();

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
        $this->saveMatches();
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
            'id', 'season', 'tournament', 'stage', 'circle', 'team1', 'team2', 'region', 'stadium',
            'country'
        ];
        $criteria->addCondition(
            [$criteria->alias . '.team1 IN(' . $teams . ')', $criteria->alias . '.team2 IN(' . $teams . ')'],
            'OR'
        );
        $criteria->addInCondition($criteria->alias . '.season', array_keys($this->seasons));
        $criteria->addInCondition($criteria->alias . '.tournament', array_keys($this->champs));
        $criteria->addInCondition($criteria->alias . '.stage', array_keys($this->stages));
        $criteria->with = [
            'match' => [
                'select' => [
                    'audience', 'mainreferee', 'linereferee1', 'linereferee2', 'sparereferee', 'delegate', 'inspector',
                    'summary', 'weather', 'state', 'date'
                ]
            ]
        ];
        $criteria->order = $criteria->alias . '.id';
        $src_matches = new Schedule();

        foreach ($src_matches->findAll($criteria) as $s) {
            $home_team = $this->getTrueTeam($s->team1, $s->tournament);
            $guest_team = $this->getTrueTeam($s->team2, $s->tournament);
            /* @var Matches $m */
            $m = $s->match;
            $match = new FcMatch();
            $match->importId            = $m->id;
            $match->championship_id     = $this->champs[$s->tournament][BaseFcModel::LANG_RU];
            $match->season_id           = $this->seasons[$s->season][BaseFcModel::LANG_RU];
            $match->stage_id            = $this->stages[$s->stage][BaseFcModel::LANG_RU];
            $match->tour                = $s->circle;
            $match->home_team_id        = $home_team[BaseFcModel::LANG_RU];
            $match->guest_team_id       = $guest_team[BaseFcModel::LANG_RU];
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
            $match->held                = $m->state > 1 ? 1 : $m->state;
            $match->matchtime           = $m->date;
            preg_match_all('/>(\d+)/', $m->summary, $score);

            if (isset($score[1]) && isset($score[1][0]) && isset($score[1][1])) {
                $match->home_score  = (int) $score[1][0];
                $match->guest_score = (int) $score[1][1];
            }

            if (!$match->save()) {
                throw new CException(
                    'Match not created.' . "\n" .
                    var_export($match->getErrors(), true) . "\n" .
                    $s . "\n" . $m . "\n"
                );
            }

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

            $this->saveMatchEvents($m->events, $matches, $s->tournament);
            $this->saveMatchPlaces($m->players, $matches, $s->tournament);
            $this->saveTags(
                $m->id,
                $matches,
                implode(
                    ' ',
                    [
                        $s->champ->title,
                        $s->s->title,
                        ($s->st ? $s->st->title : ''),
                        $s->homeTeam->title . ':' . $s->guestTeam->title
                    ]
                )
            );
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
            if (!isset($this->teams[$e->team]) || !isset($this->players[$e->player])) {
                continue;
            }

            $teams = $this->getTrueTeam($e->team, $champId);
            $players = $this->players[$e->player];

            $event = new FcEvent();
            $event->importId = $e->id;
            $event->match_id = $matches[BaseFcModel::LANG_RU];
            $event->team_id = $teams[BaseFcModel::LANG_RU];
            $event->person_id = $players[BaseFcModel::LANG_RU];
            $event->gametime     = $e->firetime;
            $event->gametimeplus = $e->injurytime;
            $event->comment      = $e->comment;

            foreach ($e->getAttributes() as $n => $v) {
                if (!(bool) $v) {
                    continue;
                }

                switch ($n) {
                    case 'timeout':
                        $event->type = FcEvent::TYPE_TIMEOUT;
                        break;
                    case 'goal':
                        $event->type = FcEvent::TYPE_GOAL;
                        break;
                    case 'autogoal':
                        $event->type = FcEvent::TYPE_AUTOGOAL;
                        break;
                    case 'goalfrompenalty':
                        $event->type = FcEvent::TYPE_GOALPENALTY;
                        break;
                    case 'pin':
                        $event->type = FcEvent::TYPE_CAMEOFFBANCH;
                        break;
                    case 'yc':
                        $event->type = FcEvent::TYPE_YELLOWCARD;
                        break;
                    case 'ycyc':
                        $event->type = FcEvent::TYPE_SECONDYELLOWCARD;
                        break;
                    case 'rc':
                        $event->type = FcEvent::TYPE_REDCARD;
                        break;
                    case 'unrealizedpenalty':
                        $event->type = FcEvent::TYPE_MISSEDPENALTY;
                        break;
                    case 'pout':
                        $event->type = FcEvent::TYPE_LEFTONBENCH;
                        break;
                    case 'cornergoal':
                        $event->type = FcEvent::TYPE_GOALCORNER;
                        break;
                    case 'finegoal':
                        $event->type = FcEvent::TYPE_TIMEOUT;
                        break;
                    case 'help':
                        $event->type = FcEvent::TYPE_GOALSHTRAFNOY;
                        break;
                    default:
                        break;
                }
            }

            if (!$event->type) {
                $event->type = FcEvent::TYPE_COMMENT;
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
            $event->team_id = $teams[BaseFcModel::LANG_EN];
            $event->person_id = $players[BaseFcModel::LANG_EN];
            $event->save();

            $this->doneEvents++;
            $this->progress();
        }
    }

    /**
     * Сохранение расстановки матча.
     *
     * @param Matchplayers[] $matchPlayers
     * @param array $matches
     * @param integer $champId
     *
     * @throws CException
     */
    private function saveMatchPlaces($matchPlayers, $matches, $champId)
    {
        foreach ($matchPlayers as $p) {
            if (!isset($this->teams[$p->team]) || !isset($this->players[$p->player])) {
                continue;
            }

            $teams = $this->getTrueTeam($p->team, $champId);
            $players = $this->players[$p->player];

            $placement = new FcPlacement();
            $placement->match_id  = $matches[BaseFcModel::LANG_RU];
            $placement->team_id   = $teams[BaseFcModel::LANG_RU];
            $placement->person_id = $players[BaseFcModel::LANG_RU];
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

            $placement->setNew();
            $placement->match_id  = $matches[BaseFcModel::LANG_EN];
            $placement->team_id   = $teams[BaseFcModel::LANG_EN];
            $placement->person_id = $players[BaseFcModel::LANG_EN];
            $placement->save();

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
    private function getTrueTeam ($teamId, $champId)
    {
        return Tournaments::isJunior($champId)
            ? $this->teams[$teamId][FcTeams::JUNIOR]
            : $this->teams[$teamId][FcTeams::MAIN];
    }

    /**
     * @param integer $entityId
     * @param array   $newEntities
     * @param string  $title
     *
     * @return bool
     * @throws CException
     */
    private function saveTags($entityId, $newEntities, $title)
    {
        $tag = new Tags();
        $tag->category_id = TagsCategories::MATCHES;
        $tag->name = substr(preg_replace('/(?!-)[\W]+/', '_', Utils::rus2lat($title)), 0, 50);
        $tag->title = $title . '_' .BaseFcModel::LANG_RU . '_' . rand(0, 100);
        $tag->publish = 1;
        $tag->priority = 0;

        if (!$tag->save()) {
            throw new CException('Tag not created.' . "\n" . var_export($tag->getErrors(), true) . "\n");
        }

        $ru_id = $tag->getId();
        $this->saveTagLinks($ru_id, $newEntities[BaseFcModel::LANG_RU]);
        $tag->setNew();
        $tag->title = $title . '_' .BaseFcModel::LANG_EN . '_' . rand(0, 100);
        $tag->save();
        $en_id = $tag->getId();
        $this->saveTagLinks($en_id, $newEntities[BaseFcModel::LANG_EN]);

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

    private function progress()
    {
        printf(
            $this->progressFormat,
            $this->doneMatches,
            $this->doneMatches * 2,
            $this->doneEvents,
            $this->doneEvents * 2,
            $this->donePlacements,
            $this->donePlacements * 2,
            $this->doneTags,
            $this->doneTags * 2
        );
    }
}
