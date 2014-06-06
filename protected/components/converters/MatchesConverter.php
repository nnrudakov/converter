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
     * Соотвествие матчей и чемпионатов для верного определения команды событий и расстановки.
     *
     * @var array
     */
    private $matches = [];

    /**
     * Инициализация.
     */
    public function __construct()
    {
        // команды и игрока уже должны быть перенесены
        $pc = new PlayersConverter();
        $this->teams   = $pc->getTeams();
        $this->players = $pc->getPlayers();

        // сезоны и чемпионаты уже должны быть пересены
        $cc = new ChampsConverter();
        $this->seasons = $cc->getSeasons();
        $this->champs  = $cc->getChamps();
        $this->stages  = $cc->getStages();
    }

    /**
     * Строка для прогресс-бара.
     *
     * @var string
     */
    private $progressFormat = "\rMatches: %d. Events: %d. Placements: %d.";

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
            /* @var Matches $m */
            $m = $s->match;
            $match = new FcMatch();
            $match->championship_id     = $this->champs[$s->tournament];
            $match->season_id           = $this->seasons[$s->season];
            $match->stage_id            = $this->stages[$s->stage];
            $match->tour                = $s->circle;
            $match->home_team_id        = $this->getTrueTeam($s->team1, $s->tournament);
            $match->guest_team_id       = $this->getTrueTeam($s->team2, $s->tournament);
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

            $this->matches[$match->id] = $s->tournament;

            $this->doneMatches++;
            $this->progress();

            $this->saveMatchEvents($m, $match);
            $this->saveMatchPlaces($m, $match);
        }
    }

    /**
     * Сохранение событий матча.
     *
     * @param Matches $m
     * @param FcMatch $match
     *
     * @throws CException
     */
    private function saveMatchEvents(Matches $m, FcMatch $match)
    {
        foreach ($m->events as $e) {
            $event = new FcEvent();
            $event->match_id = $match->id;

            if (isset($this->teams[$e->team])) {
                $event->team_id = $this->getTrueTeam($e->team, $this->matches[$match->id]);
            }

            if (isset($this->players[$e->player])) {
                $event->person_id = $this->players[$e->player];
            }

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

            $this->doneEvents++;
            $this->progress();
        }
    }

    /**
     * Сохранение расстановки матча.
     *
     * @param Matches $m
     * @param FcMatch $match
     *
     * @throws CException
     */
    private function saveMatchPlaces(Matches $m, FcMatch $match)
    {
        foreach ($m->players as $p) {
            if (!isset($this->teams[$p->team]) || !isset($this->players[$p->player])) {
                continue;
            }

            $placement = new FcPlacement();
            $placement->match_id  = $match->id;
            $placement->team_id   = $this->getTrueTeam($p->team, $this->matches[$match->id]);
            $placement->person_id = $this->players[$p->player];
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
        if (!is_array($this->teams[$teamId])) {
            return $this->teams[$teamId];
        }

        return Tournaments::isJunior($champId)
            ? $this->teams[$teamId][FcTeams::JUNIOR]
            : $this->teams[$teamId][FcTeams::MAIN];
    }

    private function progress()
    {
        printf($this->progressFormat, $this->doneMatches, $this->doneEvents, $this->donePlacements);
    }
}
