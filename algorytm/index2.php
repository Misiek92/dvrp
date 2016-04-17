<?php

require_once 'Resource.php';
require_once 'Task.php';
require_once 'Route.php';
require_once 'Distance.php';

        const DATA_FILE = "../data.json";

$file = file_get_contents(DATA_FILE);
$data = json_decode($file, true);
$resources = [];
$tasks = [];
$startRoutes = [];
$routes = [];
$pickups = [];
$drops = [];
$taskCount = count($data['tasks']);
$resourceCount = count($data['resources']);
$pickupCount;
$dropCount;

foreach ($data['resources'] as $r) {
    $resource = new Resource();
    $resource->setId($r['id']);
    $resource->setLatitude($r['latitude']);
    $resource->setLongitude($r['longitude']);
    $resources[] = $resource;
}

for ($i = 0; $i < $taskCount; $i++) {
    $t = $data['tasks'][$i];
    $task = new Task();
    $task->setId($t['id']);
    $task->setLatitude($t['latitude']);
    $task->setLongitude($t['longitude']);
    $task->setTask($t['task']);
    $task->setType($t['type']);
    if ($t['type'] == "pickup") {
        $pickups[] = $task;
    } else {
        $drops[] = $task;
        $task->setRelatedTaskId($tasks[$i - 1]->getId());
        $tasks[$i - 1]->setRelatedTaskId($task->getId());
    }
    $tasks[] = $task;
}

$pickupCount = count($pickups);
$dropCount = count($drops);

function dd($element) {
    die(json_encode($element));
}

/*
 * Obliczanie dystancu w metrach, wg metody
 * "as the crow flies"
 * można podstawić inne API, np. Google matrix
 */

function distance($lon1, $lat1, $lon2, $lat2) {
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

/**
 * Obliczanie dystansu na podstawie wzoru pitagorsa,
 * bardziej adekwatny dla odległości przedstawionych na płaszczyźnie
 */
function distance2($lon1, $lat1, $lon2, $lat2) {
//    $lon = [$lon1, $lon2];
//    sort($lon);
//    $lat = [$lat1, $lat2];
//    sort($lat);
//    return pow(($lon[1] - $lon[0]), 2) + pow(($lat[1] - $lat[0]), 2);
    
    $x = abs($lon1 - $lon2);
    $y = abs($lat1 - $lat2);
    
    return sqrt(pow($x, 2) + pow($y, 2));
}

function comb($m, $a) {
    if (!$m) {
        yield [];
        return;
    }
    if (!$a) {
        return;
    }
    $h = $a[0];
    $t = array_slice($a, 1);
    foreach (comb($m - 1, $t) as $c)
        yield array_merge([$h], $c);
    foreach (comb($m, $t) as $c)
        yield $c;
}

function checkOrder($arrays) {
    $correctArray = [];
    foreach ($arrays as $array) {
        $used = [];
        if (count($array) % 2 == 0) {
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
        }
    }
    return $correctArray;
}

function permute($items, $perms = array()) {
    if (empty($items)) {
        $return = array($perms);
    } else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $return = array_merge($return, permute($newitems, $newperms));
        }
    }
    return $return;
}

function flattenArray(array $array) {
    $ret_array = array();
    foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
        $ret_array[] = $value;
    }
    return $ret_array;
}

function compare_objects($obj_a, $obj_b) {
    return $obj_a->getId() - $obj_b->getId();
}

$test = [1, 2, 3, 4, 5];
$first = [];
$second = [];
$all = [];

foreach (range(2, $taskCount) as $n) {
    if ($n % 2 == 0) {
        $combinations = comb($n, $tasks);
        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $permutations = checkOrder($permutations);
            //dd($permutations);
            if (count($permutations) > 0) {
                $first[] = $permutations;
            }
        }
    }
}

foreach ($first as $used) {
    $free = array_merge(array_udiff($tasks, $used[0], 'compare_objects'));
    foreach (range(1, count($free)) as $n) {
        $combinations = comb($n, $free);

        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $permutations = checkOrder($permutations);
            if (count($permutations) > 0) {
                $second[] = [$used, $permutations];
            }
        }
    }
}
//die(json_encode($second));

foreach ($second as $used) {

    //$flattenUsedValues = array_merge(flattenArray($used[0]), flattenArray($used[1]));
    $free = array_merge(array_udiff($tasks, $used[0][0], 'compare_objects'));
    $free = array_merge(array_udiff($free, $used[1][0], 'compare_objects'));
//    die(json_encode($permutations ));

    if (count($free) > 0) {
        $combinations = comb(count($free), $free);
        foreach ($combinations as $combination) {
            $permutations = permute($combination);
            $permutations = checkOrder($permutations);
            if (count($permutations) > 0) {
                $all[] = [$used[0], $used[1], $permutations];
            }
        }
    } else {
        $all[] = [$used[0], $used[1], []];
    }
}

//dd($all[0][0][0][0]);
$theLongestDistance = [INF];
$theBestRoute;


foreach ($all as $key => $comb) {
    $shortestDistanceFirst = INF;
    $shortestPermutationFirst;
    $shortestDistanceSecond = INF;
    $shortestPermutationSecond;
    $shortestDistanceThird = INF;
    $shortestPermutationThird;
    if (count($comb[0]) > 0) {
        for ($i = 0; $i < count($comb[0]); $i++) {
            $distance = 0;
            array_unshift($comb[0][$i], $resources[0]);
            for ($j = 1; $j < count($comb[0][$i]); $j++) {
                $previous = $comb[0][$i][$j - 1];
                $actual = $comb[0][$i][$j];
                $distance += distance2($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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
            array_unshift($comb[1][$i], $resources[1]);
            for ($j = 1; $j < count($comb[1][$i]); $j++) {
                $previous = $comb[1][$i][$j - 1];
                $actual = $comb[1][$i][$j];
                $distance += distance2($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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
            array_unshift($comb[2][$i], $resources[2]);
            for ($j = 1; $j < count($comb[2][$i]); $j++) {
                $previous = $comb[2][$i][$j - 1];
                $actual = $comb[2][$i][$j];
                $distance += distance2($previous->getLongitude(), $previous->getLatitude(), $actual->getLongitude(), $actual->getLatitude());
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

//    /dd([$shortestDistanceFirst, $shortestDistanceSecond, $shortestDistanceThird]);
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


$fp = fopen('result.json', 'w');
fwrite($fp, json_encode($theBestRoute));
fclose($fp);

dd($theBestRoute);


    