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

/*
 * Obliczanie dystancu w metrach, wg metody
 * "as the crow flies"
 * można podstawić inne API, np. Google matrix
 */

function distance($lon1, $lat1, $lon2, $lat2)
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

/**
 * Obliczanie dystansu na podstawie wzoru pitagorsa,
 * bardziej adekwatny dla odległości przedstawionych na płaszczyźnie
 */
function distance2($lon1, $lat1, $lon2, $lat2)
{
    $lon = [$lon1, $lon2];
    sort($lon);
    $lat = [$lat1, $lat2];
    sort($lat);
    return pow(($lon[1] - $lon[0]), 2) + pow(($lat[1] - $lat[0]), 2);
}

/**
 * Ilość zaobów
 * wpierw dla jednego wszystkie scenariusze, potem dla 2... aż do wszystkich
 */
$possibleResourceCombination = [];

for ($i = 1; $i <= $resourceCount; $i++) {
    $possibleToVisit = $pickups;
    $visited = [];
    $tmpArray = [];
    $k = 0;
    /**
     * dla ilu zasobów
     */
    for ($j = 1; $j <= $i; $j++) {
        /**
         * Ile powtórzeń
         */
        for ($m = 0; $m < $resourceCount; $m++) {
            /**
             * Wypisywanie
             */
            for ($l = 0, $index = $j - 1 + $m; $l < $resourceCount - $m; $l++, $index++, $k++) {
                if ($index >= $resourceCount) {
                    $index = 0;
                }
                if (!isset($tmpArray[$k])) {
                    $tmpArray[$k] = [$resources[$m]->getId()];
                } else {
                    if (!in_array($resources[$index]->getId(), $tmpArray[$k])) {
                        $tmpArray[$k][] = $resources[$index]->getId();
                    }
                }
                sort($tmpArray[$k]);
            }
        }
        $k = 0;
    }
    /**
     * Tworzenie listy unikalnych list
     */
    for ($j = 0; $j < count($tmpArray); $j++) {
        if (!in_array($tmpArray[$j], $possibleResourceCombination)) {
            $possibleResourceCombination[] = $tmpArray[$j];
        }
    }
}
sort($possibleResourceCombination);


echo json_encode($possibleResourceCombination);
die();

/*
 * Schemat działania algorytmu dla 1 zasobu i 2 zadań
 * [ [0], [2]]
 * [ [ [0,1],[0,2] ], [[2,0], [2,3]] ]
 * [ [ [ [0,1,2] ], [ [0,2,1], [0,2,3] ], [ [2,0,1], [2,0,3] ], [ [2,3,0] ] ] ]
 * [[0,1,2,3], [0,2,1,3], [0,2,3,1], [2,0,1,3], [2,0,3,1], [2,3,0,1]]
 */

/**
 * Sprawdzanie, czy wszystkie elementy listy zawierają wszystkie zadania
 * @param type $list
 * @return boolean
 */
function checkList($list, $tasks)
{
    if (empty($list)) {
        return true;
    }
    foreach ($list as $l) {
        if (count($l) < count($tasks)) {
            return true;
        }
    }
    return false;
}

/**
 * Zwraca listę sąsiadów dla danego wierzchołka
 * @param type $list
 * @param type $tasks
 * @return type
 */
function getNeighbours($list, $tasks)
{
    $neighbours = [];
    for ($i = count($list) - 1; $i >= 0; $i--) {
        for ($j = 0; $j < count($tasks); $j++) {
            if ($list[$i]->getTask() === $tasks[$j]->getTask() && $list[$i]->getId() != $tasks[$j]->getId() && !in_array($tasks[$j], $list)) {
                $neighbours[] = $tasks[$j];
            }
        }
    }
    for ($i = 0; $i < count($tasks); $i++) {
        if (!in_array($tasks[$i], $list) && $tasks[$i]->getType() == Task::TYPE_PICKUP) {
            $neighbours[] = $tasks[$i];
        }
    }
    return $neighbours;
}

/**
 * Sortowanie na podstawie id obiektu
 * @param type $a
 * @param type $b
 * @return type
 */
function cmp($a, $b)
{
    return strcmp($a->getId(), $b->getId());
}

function subcmp($a, $b)
{
    return strcmp($a[0]->getId(), $b[0]->getId());
}

function getRelated($tasks, Task $obj)
{
    foreach ($tasks as $task) {
        if ($task->getId() == $obj->getRelatedTaskId() && $task != $obj) {
            return $task;
        }
    }
    return false;
}

if ($taskCount > 10 || $resourceCount > 3) {
    echo "Zablokowano możliwość wywołania skryptu dla więcej niż 5 zadań i 3 zasobów";
    die();
}
$possible = [];
for ($m = 0; $m <= floor($pickupCount / 2) || $m === 0; $m++) {
    $removed = [];
    $otherSolutions = true;
    $specialTaskList = $tasks;
    $specialPickupList = $pickups;
    while ($otherSolutions) {
        if ($m != 0) {
            if (empty($removed)) {
                for ($o = 0; $o < $m; $o++) {
                    $removed[] = $pickups[$o];
                }
                array_splice($specialTaskList, 0, $m * 2);
                array_splice($specialPickupList, 0, $m);
            } else {

                $index = count($removed) - 1;
                $changedRemoved = false;
                $nothingToChange = false;
                while (!$changedRemoved && !$nothingToChange) {
                    $stopFlag = true;
                    for ($c = count($removed) - 1, $d = 1; $c >= 0; $c--, $d++) {
                        if (array_search($removed[$c], $pickups) !== count($pickups) - $d) {
                            $stopFlag = false;
                        }
                    }

                    if ($stopFlag) {
                        $nothingToChange = true;
                        $otherSolutions = false;
                        break;
                    }

                    if (array_search($removed[$index], $pickups) !== count($pickups) - 1 && $index >= 0) {
                        $rawPosition = array_search($removed[$index], $pickups);
                        $position = $rawPosition + 1;
                        if (!isset($removed[$index + 1]) || $removed[$index + 1] != $pickups[$position]) {
                            $specialPickupList[] = $removed[$index];
                            $specialTaskList[] = $tasks[$rawPosition * 2];
                            $specialTaskList[] = $tasks[$rawPosition * 2 + 1];
                            usort($specialPickupList, "cmp");
                            usort($specialTaskList, "cmp");
                            $removed[$index] = $pickups[$position];

                            array_splice($specialPickupList, array_search($removed[$index], $specialPickupList), 1);
                            array_splice($specialTaskList, array_search($removed[$index], $specialTaskList), 2);
                            $changedRemoved = true;
                        } else {
                            $index--;
                            break;
                        }
                        if ($index != count($removed) - 1) {
                            for ($o = $index + 1, $inc = 1; $o < count($removed); $o++, $inc++) {
                                $rawPosition = array_search($removed[$o], $pickups);
                                $specialPickupList[] = $removed[$o];
                                $specialTaskList[] = $tasks[$rawPosition * 2];
                                $specialTaskList[] = $tasks[$rawPosition * 2 + 1];
                                usort($specialPickupList, "cmp");
                                usort($specialTaskList, "cmp");
                                $removed[$o] = $pickups[$position + $inc];
                                array_splice($specialPickupList, array_search($removed[$o], $specialPickupList), 1);
                                array_splice($specialTaskList, array_search($removed[$o], $specialTaskList), 2);
                            }
                        }
                    } elseif ($index > 0) {
                        $index--;
                    } else {
                        $nothingToChange = true;
                    }
                }
                if ($nothingToChange) {
                    $otherSolutions = false;
                    break;
                }
            }
        } else {
            $otherSolutions = false;
        }

        // old
        $oneList = [];
        for ($i = 0; $i < count($specialPickupList); $i++) {
            $list = [];
            while (checkList($list, $specialTaskList)) {
                if (empty($list)) {
                    $list[] = [$specialPickupList[$i]];
                } else {
                    /**
                     * Rozszerzanie listy
                     */
                    $beginList = [];
                    for ($j = 0; $j < count($list); $j++) {
                        $neighbours = getNeighbours($list[$j], $specialTaskList);
                        for ($k = 0; $k < count($neighbours); $k++) {
                            $tmpArray = $list[$j];
                            $tmpArray[] = $neighbours[$k];
                            $beginList[] = $tmpArray;
                        }
                    }
                    $list = $beginList;
                }
            }
            $oneList = array_merge($oneList, $list);
        }
        for ($i = 0; $i < count($oneList); $i++) {
            $tmpArray = [];
            foreach ($removed as $r) {
                $tmpArray[] = $r;
                $tmpArray[] = getRelated($tasks, $r);
            }
            $oneList[$i] = [$oneList[$i], $tmpArray, []];
        }
        $possible = array_merge($possible, $oneList);
    }
    if ($resourceCount == 1) {
        $m = 9;
    }
}
$possibleCount = count($possible);

/**
 * Wersje dla 3 zasobu
 */
if ($resourceCount > 2) {
    for ($i = 0; $i < $possibleCount; $i++) {
        if (!empty($possible[$i][1])) {
            for ($j = 0; $j < floor(count($possible[$i][0]) / 4); $j++) {
                $tmp = $possible[$i];
                $removedTask = [];
                for ($k = $j; $k >= 0; $k--) {
                    $removedTask[] = $tmp[0][$k];
                    $relatedPosition = array_search(getRelated($tmp[0], $tmp[0][$k]), $tmp[0]);
                    $removedTask[] = $tmp[0][$relatedPosition];
                    array_splice($tmp[0], $relatedPosition, 1);
                    array_splice($tmp[0], $k, 1);
                }
                $tmp[2] = $removedTask;
                usort($tmp, "subcmp");
                // Prostsza wersja usuwania dubli, moze byc nieskuteczna w 100%
                if ($tmp != $possible[count($possible) - 1]) {
                    $possible[] = $tmp;
                }
            }
        }
    }
    /**
     * usuwanie dubli bardzo zwalnia
     */
//    for ($i = count($possible) - 1; $i >= 0; $i--) {
//        if (array_search($possible[$i], $possible) != $i) {
//            array_splice($possible, $i, 1);
//        }
//    }
}

function permutation($items, $perms = array())
{
    if (empty($items)) {
        $return = array($perms);
    } else {
        $return = array();
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newitems = $items;
            $newperms = $perms;
            list($foo) = array_splice($newitems, $i, 1);
            array_unshift($newperms, $foo);
            $return = array_merge($return, permutation($newitems, $newperms));
        }
    }
    return $return;
}

$resourceIds = [];
for ($i = 0; $i < count($resources); $i++) {
    $resourceIds[] = $i;
}
//print_r($tasks[2]);
//print_r($resources[1]);
//print_r($resources[2]);
////echo distance($resources[0]->getLongitude(), $resources[0]->getLatitude(), $pickups[1]->getLongitude(), $pickups[1]->getLatitude());
//die();

$resourcePermutations = permutation($resourceIds);
$theLongestDistance = [INF];
$theBestRoute;
$operacji = 0;

for ($i = 0; $i < count($resourcePermutations); $i++) {
    for ($j = 0; $j < count($possible); $j++) {
        $distances = [];
        for ($k = 0; $k < count($possible[$j]); $k++) {
            $distance = 0;
            if (count($possible[$j][$k]) > 0) {
                $resLon = $resources[$resourcePermutations[$i][$k]]->getLongitude();
                $resLat = $resources[$resourcePermutations[$i][$k]]->getLatitude();
                $posLon = $possible[$j][$k][0]->getLongitude();
                $posLat = $possible[$j][$k][0]->getLatitude();
                $distance += distance2($resLon, $resLat, $posLon, $posLat);
                for ($m = 0; $m < count($possible[$j][$k]) - 1; $m++) {
                    $operacji++;
                    $posLon1 = $possible[$j][$k][$m]->getLongitude();
                    $posLat1 = $possible[$j][$k][$m]->getLatitude();
                    $posLon2 = $possible[$j][$k][$m + 1]->getLongitude();
                    $posLat2 = $possible[$j][$k][$m + 1]->getLatitude();
                    $distance += distance2($posLon1, $posLat1, $posLon2, $posLat2);
                }
            }
            $distances[] = $distance;
        }
        rsort($distances);
        if ($distances[0] < $theLongestDistance[0]) {
            $theLongestDistance = $distances;
            $tmp = $possible[$j];
            $resource1 = $resources[$resourcePermutations[$i][0]];
            if (isset($resourcePermutations[$i][1])) {
                $resource2 = $resources[$resourcePermutations[$i][1]];
                array_unshift($tmp[1], $resource2);
            }
            if (isset($resourcePermutations[$i][2])) {
                $resource3 = $resources[$resourcePermutations[$i][2]];
                array_unshift($tmp[2], $resource3);
            }
            array_unshift($tmp[0], $resource1);

            $theBestRoute = $tmp;
        }
    }
}
print_r($theBestRoute);

//echo json_encode($possible);
//echo "Wyników: " . count($possible);


