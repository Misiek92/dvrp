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

function getNeighbours($list, $tasks)
{
    $neighbours = [];
    for ($i = count($list) - 1; $i >= 0; $i--) {
        for ($j = 0; $j < count($tasks); $j++) {
            if ($list[$i]->getTask() === $tasks[$j]->getTask() 
                    && $list[$i]->getId() != $tasks[$j]->getId()
                    && !in_array($tasks[$j], $list)) {
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
 * Algorytm naiwny dla 1 zasobu.
 */
$finalList = [];

for ($i = 0; $i < $pickupCount; $i++) {
    $list = [];
    while (checkList($list, $tasks)) {
        if (empty($list)) {
            $list[] = [$pickups[$i]];
        } else {
            /**
             * Rozszerzanie listy
             */
            $beginList = [];
            for ($j = 0; $j < count($list); $j++) {
                $neighbours = getNeighbours($list[$j], $tasks);
                for ($k = 0; $k < count($neighbours); $k++) {
                    $tmpArray = $list[$j];
                    $tmpArray[] = $neighbours[$k];
                    $beginList[] = $tmpArray;
                }
            }
            $list = $beginList;
        }
    }

    $finalList[] = $list;
}
print_r($finalList);


