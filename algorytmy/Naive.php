<?php

require_once 'Resource.php';
require_once 'Task.php';
require_once 'Distance.php';

class Naive
{
    protected $id;
    /**
     * @var Resource
     */
    protected $resources;

    /**
     * @var Task
     */
    protected $tasks;

    /**
     * @var Task
     */
    protected $pickups = [];

    /**
     * @var Task
     */
    protected $drops = [];

    /**
     * @var array
     */
    protected $theBestRoute;

    /**
     * @var DateTime
     */
    protected $time;

    protected $microTime;

    /**
     * @var DateTime
     */
    protected $timeEnd;

    protected $microTimeEnd;
    /**
     * @var array
     */
    /**
     * @return array
     */
    public function getTheBestRoute()
    {
        return [
            "result" => $this->theBestRoute,
            "totalDistance" => round($this->totalDistance(), 2),
            "time" => $this->dateDifference(),
            "theLongest" => $this->theLongest()
        ];
    }

    public function getTheLongestDistance()
    {
        $theLongest = 0;
        foreach ($this->theBestRoute as $i => $route) {
            $distance = $route[0]->getDistance();
            if ($distance > $theLongest) {
                $theLongest = $distance;
            }
        }

        return $theLongest;
    }

    public function theLongest()
    {
        $theLongest = 0;
        $id = null;
        foreach ($this->theBestRoute as $i => $route) {
            if (isset($route[0]) && $route[0]->getDistance() > $theLongest) {
                $theLongest = $route[0]->getDistance();
                $id = $i;
            }
        }

        return round($theLongest, 2) . " (ID: " . $id . ")";
    }

    /**
     * Naive constructor.
     * @param $id
     * @param Task $tasks []
     * @param Resource $resources []
     */
    public function __construct($id, $tasks, $resources)
    {
        $this->id = $id;
        $this->resources = $resources;
        $this->tasks = $tasks;
        foreach ($tasks as $task) {
            if ($task->getType() == Task::TYPE_PICKUP) {
                $this->pickups[] = $task;
            } else {
                $this->drops[] = $task;
            }
        }
    }

    private function dateDifference()
    {
        $miliseconds = $this->microTimeEnd - $this->microTime;

        return round($miliseconds, 4) . " sekund";
    }

    public function totalDistance()
    {
        $distance = 0;
        foreach ($this->theBestRoute as $route) {
            if (isset($route[0])) {
                $distance += $route[0]->getDistance();
            }
        }
        return $distance;
    }

    public function getInfo()
    {
        $info = "\r\n";
        $info .= "############################################# \r\n";
        $info .= "###            ALGORYTM NAIWNY            ### \r\n";
        $info .= "############################################# \r\n";
        $info .= "# \r\n";
        $info .= "# Rozpoczecie obliczen: " . $this->time->format('H:i:s') . "\r\n";
        $info .= "# Ukonczenie:           " . $this->timeEnd->format('H:i:s') . "\r\n";
        $info .= "# Czas wykonywania:     " . $this->dateDifference() . "\r\n";
        $info .= "# Najdlugszy dystans:   " . $this->theLongest() . "\r\n";
        $info .= "# Laczny dystans:       " . round($this->totalDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 1  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[0]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[0][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 2  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[1]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[1][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 3  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[2]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[2][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#####################################\r\n";
        print $info;
    }

    private function comb($m, $a)
    {
        if (!$m) {
            yield [];
            return;
        }
        if (!$a) {
            return;
        }
        $h = $a[0];
        $t = array_slice($a, 1);
        foreach ($this->comb($m - 1, $t) as $c)
            yield array_merge([$h], $c);
        foreach ($this->comb($m, $t) as $c)
            yield $c;
    }

    private function checkOrder(&$array)
    {
        $correctArray = [];
        $used = [];
        foreach ($array as $element) {
            if ($element->getType() == "pickup") {
                $used[] = $element;
            } else {
                $correctOrder = false;
                foreach ($used as $check) {
                    if ($check->getType() == "pickup" && $check->getTask() == $element->getTask()) {
                        $correctOrder = true;
                        break;
                    }
                }
                if (!$correctOrder) {
                    break;
                } else {
                    $used[] = $element;
                }
            }
            //array_shift($array);
        }
        if (count($used) == count($array)) {
            $correctArray[] = $used;
        }

        return $correctArray;
    }

    private function permute($items, $perms = array())
    {
        if (empty($items)) {
            $return = $this->checkOrder($perms);
        } else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, $this->permute($newitems, $newperms));
            }
        }
        return $return;
    }

    static function compare_objects($obj_a, $obj_b)
    {
        if (get_class($obj_a) == "Task" && get_class($obj_b) == "Task") {
            return $obj_a->getId() - $obj_b->getId();
        }
        return 1;
    }

    private function leaveTheBest($array, $resource)
    {
        $shortestDistance = INF;
        $shortestPermutation = null;
        for ($i = 0; $i < count($array); $i++) {
            $distance = 0;
            array_unshift($array[$i], $resource);
            for ($j = 1; $j < count($array[$i]); $j++) {
                $previous = $array[$i][$j - 1];
                $actual = $array[$i][$j];
                $distanceObject = new Distance($previous, $actual);
                $distance += $distanceObject->euclides();
            }
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $shortestPermutation = $i;
            }
        }
        return [$array[$shortestPermutation]];
    }

    private function findAndPushDrops($combinations)
    {
        $arrayCombinations = [];
        foreach ($combinations as $pickups) {
            $array = $pickups;
            foreach ($pickups as $pickup) {
                foreach ($this->tasks as $task) {
                    if ($task->getTask() == $pickup->getTask() && $task->getType() != $pickup->getType()) {
                        $array[] = $task;
                        break;
                    }
                }
            }
            $arrayCombinations[] = $array;
        }
        return $arrayCombinations;
    }

    private function findAndRemoveDrops($tasks)
    {
        $array = [$tasks[0]];
        for ($i = 1; $i < count($tasks); $i++) {
            if ($tasks[$i]->getType() == Task::TYPE_PICKUP) {
                $array[] = $tasks[$i];
            }
        }
        return $array;
    }

    private function firstResource()
    {
        $first = [];
        foreach (range(1, count($this->pickups)) as $n) {
            $combinations = $this->comb($n, $this->pickups);
            $combinations = $this->findAndPushDrops($combinations);
            foreach ($combinations as $combination) {
                $permutations = $this->permute($combination);
                if (count($permutations) > 0) {
                    $first[] = $this->leaveTheBest($permutations, $this->resources[0]);
                }
            }
        }
        return $first;
    }

    private function secondResource(&$first)
    {
        $second = [];
        foreach ($first as $used) {
            $usedWithoutDrops = $this->findAndRemoveDrops($used[0]);
            $free = array_merge(array_udiff($this->pickups, $usedWithoutDrops, "Naive::compare_objects"));
            foreach (range(1, count($free)) as $n) {
                $combinations = $this->comb($n, $free);
                $combinations = $this->findAndPushDrops($combinations);
                foreach ($combinations as $combination) {
                    $permutations = $this->permute($combination);
                    if (count($permutations) > 0) {
                        $second[] = [$used, $this->leaveTheBest($permutations, $this->resources[1])];
                    }
                }

            }
        }
        return $second;
    }

    private function thirdResource(&$second)
    {
        $all = [];
        foreach ($second as $used) {
            $usedWithoutDropsOne = $this->findAndRemoveDrops($used[0][0]);
            $usedWithoutDropsTwo = $this->findAndRemoveDrops($used[1][0]);
            $free = array_merge(array_udiff($this->pickups, $usedWithoutDropsOne, "Naive::compare_objects"));
            $free = array_merge(array_udiff($free, $usedWithoutDropsTwo, "Naive::compare_objects"));

            if (count($free) > 0) {

                $combinations = $this->comb(count($free), $free);
                $combinations = $this->findAndPushDrops($combinations);
                foreach ($combinations as $combination) {
                    $permutations = $this->permute($combination);

                    if (count($permutations) > 0) {
                        $all[] = [$used[0], $used[1], $this->leaveTheBest($permutations, $this->resources[2])];
                    }
                }
            } else {
                $all[] = [$used[0], $used[1], []];
            }
        }
        return $all;
    }

    private function calculate(&$all)
    {
        $theLongestDistance = [INF];
        $theBestRoute = null;
        foreach ($all as $key => $comb) {
            $shortestDistanceFirst = INF;
            $shortestPermutationFirst = null;
            $shortestDistanceSecond = INF;
            $shortestPermutationSecond = null;
            $shortestDistanceThird = INF;
            $shortestPermutationThird = null;
            if (count($comb[0]) > 0) {
                for ($i = 0; $i < count($comb[0]); $i++) {
                    $distance = 0;
                    for ($j = 1; $j < count($comb[0][$i]); $j++) {
                        $previous = $comb[0][$i][$j - 1];
                        $actual = $comb[0][$i][$j];
                        $distanceObject = new Distance($previous, $actual);
                        $distance += $distanceObject->euclides();
                    }
                    if ($distance < $shortestDistanceFirst) {
                        $shortestDistanceFirst = $distance;
                        $shortestPermutationFirst = $i;
                    }

                }
            } else {
                $shortestDistanceFirst = 0;
                $shortestPermutationFirst = null;
            }
            if (count($comb[1]) > 0) {
                for ($i = 0; $i < count($comb[1]); $i++) {
                    $distance = 0;
                    for ($j = 1; $j < count($comb[1][$i]); $j++) {
                        $previous = $comb[1][$i][$j - 1];
                        $actual = $comb[1][$i][$j];
                        $distanceObject = new Distance($previous, $actual);
                        $distance += $distanceObject->euclides();
                    }
                    if ($distance < $shortestDistanceSecond) {
                        $shortestDistanceSecond = $distance;
                        $shortestPermutationSecond = $i;
                    }

                }
            } else {
                $shortestDistanceSecond = 0;
                $shortestPermutationSecond = null;
            }
            if (count($comb[2]) > 0) {
                for ($i = 0; $i < count($comb[2]); $i++) {
                    $distance = 0;
                    for ($j = 1; $j < count($comb[2][$i]); $j++) {
                        $previous = $comb[2][$i][$j - 1];
                        $actual = $comb[2][$i][$j];
                        $distanceObject = new Distance($previous, $actual);
                        $distance += $distanceObject->euclides();
                    }
                    if ($distance < $shortestDistanceThird) {
                        $shortestDistanceThird = $distance;
                        $shortestPermutationThird = $i;
                    }
                }
            } else {
                $shortestDistanceThird = 0;
                $shortestPermutationThird = null;
            }

            $routeFirst = isset($comb[0][$shortestPermutationFirst]) ? $comb[0][$shortestPermutationFirst] : [];
            $routeSecond = isset($comb[1][$shortestPermutationSecond]) ? $comb[1][$shortestPermutationSecond] : [];
            $routeThird = isset($comb[2][$shortestPermutationThird]) ? $comb[2][$shortestPermutationThird] : [];
            $distances = [$shortestDistanceFirst, $shortestDistanceSecond, $shortestDistanceThird];
            rsort($distances);
            $route = [$routeFirst, $routeSecond, $routeThird];
            if ($theLongestDistance[0] > $distances[0]) {
                $theLongestDistance = $distances;
                $theBestRoute = $route;
                $this->resources[0]->setDistance($shortestDistanceFirst);
                $this->resources[1]->setDistance($shortestDistanceSecond);
                $this->resources[2]->setDistance($shortestDistanceThird);
            } elseif ($theLongestDistance[0] == $distances[0]) {
                $sum1 = $theLongestDistance[1] + $theLongestDistance[2];
                $sum2 = $distances[1] + $distances[2];
                if ($sum2 < $sum1) {
                    $theLongestDistance = $distances;
                    $theBestRoute = $route;
                    $this->resources[0]->setDistance($shortestDistanceFirst);
                    $this->resources[1]->setDistance($shortestDistanceSecond);
                    $this->resources[2]->setDistance($shortestDistanceThird);
                }
            }
        }
        $this->theBestRoute = $theBestRoute;
        return true;
    }

    private function save()
    {
        $fp = fopen('results/' . $this->id . '-naive.json', 'w');
        fwrite($fp, json_encode($this->getTheBestRoute()));
        fclose($fp);
    }

    public function execute()
    {
        $this->time = new DateTime('now');
        $this->microTime = microtime(true);
        $first = $this->firstResource();
        $second = $this->secondResource($first);
        unset($first);
        $all = $this->thirdResource($second);
        unset($second);
        $this->calculate($all);
        unset($all);
        $this->microTimeEnd = microtime(true);
        $this->timeEnd = new DateTime('now');
        $this->save();
    }
}