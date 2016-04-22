<?php

require_once 'Resource.php';
require_once 'Task.php';

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
     * @var DateTime
     */
    protected $time;

    /**
     * @var array
     */
    protected $theBestRoute;

    /**
     * @return array
     */
    public function getTheBestRoute()
    {
        return $this->theBestRoute;
    }

    /**
     * Naive constructor.
     * @param $id
     * @param Task $tasks[]
     * @param Resource $resources[]
     */
    public function __construct($id, $tasks, $resources)
    {
        $this->id = $id;
        $this->resources = $resources;
        $this->tasks = $tasks;
        $this->time = new DateTime('now');
    }

    public function euclidesDistance($lon1, $lat1, $lon2, $lat2)
    {
        $x = abs($lon1 - $lon2);
        $y = abs($lat1 - $lat2);

        return sqrt(pow($x, 2) + pow($y, 2));
    }

    private function geographicDistance($lon1, $lat1, $lon2, $lat2)
    {
        return round(acos(
                cos($lat1 * (PI() / 180)) *
                cos($lon1 * (PI() / 180)) *
                cos($lat2 * (PI() / 180)) *
                cos($lon2 * (PI() / 180)) +
                cos($lat1 * (PI() / 180)) *
                sin($lon1 * (PI() / 180)) *
                cos($lat2 * (PI() / 180)) *
                sin($lon2 * (PI() / 180)) +
                sin($lat1 * (PI() / 180)) *
                sin($lat2 * (PI() / 180))
            ) * 6371 * 1000);
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

    private function checkOrder($array)
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
                $distance += $this->euclidesDistance($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
            }
            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $shortestPermutation = $i;
            }
        }
        return [$array[$shortestPermutation]];
    }

    private function firstResource()
    {
        $first = [];
        foreach (range(0, count($this->tasks)) as $n) {
            if ($n % 2 == 0) {
                $combinations = $this->comb($n, $this->tasks);
                foreach ($combinations as $combination) {
                    $permutations = $this->permute($combination);
                    if (count($permutations) > 0) {
                        $first[] = $this->leaveTheBest($permutations, $this->resources[0]);
                    }
                }
            }
        }
        return $first;
    }

    private function secondResource(&$first)
    {
        $second = [];
        foreach ($first as $used) {
            $free = array_merge(array_udiff($this->tasks, $used[0], "Naive::compare_objects"));
            foreach (range(0, count($free)) as $n) {
                if ($n % 2 == 0) {
                    $combinations = $this->comb($n, $free);

                    foreach ($combinations as $combination) {
                        $permutations = $this->permute($combination);
                        if (count($permutations) > 0) {
                            $second[] = [$used, $this->leaveTheBest($permutations, $this->resources[1])];
                        }
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
            $free = array_merge(array_udiff($this->tasks, $used[0][0], "Naive::compare_objects"));
            $free = array_merge(array_udiff($free, $used[1][0], "Naive::compare_objects"));

            if (count($free) > 0) {

                $combinations = $this->comb(count($free), $free);
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
        //die(json_encode($all));
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
                        $distance += $this->euclidesDistance($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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
                        $distance += $this->euclidesDistance($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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
                        $distance += $this->euclidesDistance($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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
            } elseif ($theLongestDistance[0] == $distances[0]) {
                $sum1 = $theLongestDistance[1] + $theLongestDistance[2];
                $sum2 = $distances[1] + $distances[2];
                if ($sum2 < $sum1) {
                    $theLongestDistance = $distances;
                    $theBestRoute = $route;
                }
            }
        }
        $this->theBestRoute = $theBestRoute;
        return true;
    }

    private function save()
    {
        $fp = fopen('result.json', 'w');
        fwrite($fp, json_encode($this->theBestRoute));
        fclose($fp);
    }

    public function execute()
    {
        $first = $this->firstResource();
        $second = $this->secondResource($first);
        unset($first);
        $all = $this->thirdResource($second);
        unset($second);
        $this->calculate($all);
        unset($all);
        $this->save();

    }
}