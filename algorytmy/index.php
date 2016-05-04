<?php

require_once '../generator/index.php';
require_once 'Resource.php';
require_once 'Task.php';
require_once 'Naive.php';
require_once 'GreedyOne.php';
require_once 'GreedyTwo.php';
require_once 'GreedyThree.php';
require_once 'FinishFirst.php';

const DATA_FILE = "../data.json";

$algorithms = ["#1 Idz do najblizszego", "#2 Najblizszy na danym etapie", "#3 Najmniej wydluzajacy sciezke", "#4 Priorytet dla konczenia zadan"];

$file = file_get_contents(DATA_FILE);
$data = json_decode($file, true);
$date = new DateTime('now');

function parseResources($data)
{
    foreach ($data['resources'] as $r) {
        $resource = new Resource();
        $resource->setId($r->getId());
        $resource->setLatitude($r->getLatitude());
        $resource->setLongitude($r->getLongitude());
        $resources[] = $resource;
    }

    return $resources;
}

function parseTasks($data)
{
    for ($i = 0; $i < count($data['tasks']); $i++) {
        $t = $data['tasks'][$i];
        $task = new Task();
        $task->setId($t->getId());
        $task->setLatitude($t->getLatitude());
        $task->setLongitude($t->getLongitude());
        $task->setTask($t->getTask());
        $task->setType($t->getType());
        $tasks[] = $task;
    }

    return $tasks;
}

function clearDistances(&$resources)
{
    foreach ($resources as $resource) {
        $resource->setDistance(0);
    }
}

function dd($element)
{
    die(json_encode($element));
}

function findTheBest($scenarios)
{
    $theShortestDistance = INF;
    $choosenScenarioIndex = null;
    $theShortestTotalDistance = INF;
    foreach ($scenarios as $key => $scenario) {
        $scenarioIndex = null;
        $theLongestDistance = 0;
        $scenario = json_decode($scenario);
        $totalDistance = 0;
        foreach ($scenario as $path) {
            if ($path[0]->distance > $theLongestDistance) {
                $theLongestDistance = $path[0]->distance;
                $scenarioIndex = $key;
            }
            $totalDistance += $path[0]->distance;
        }
        if ($theLongestDistance < $theShortestDistance) {
            $theShortestDistance = $theLongestDistance;
            $choosenScenarioIndex = $scenarioIndex;
            $theShortestTotalDistance = $totalDistance;
        } elseif ($theLongestDistance = $theShortestDistance){
            if ($totalDistance < $theShortestTotalDistance) {
                $theShortestDistance = $theLongestDistance;
                $choosenScenarioIndex = $scenarioIndex;
                $theShortestTotalDistance = $totalDistance;
            }
        }
    }
    return $choosenScenarioIndex;
}

function statistics($results)
{
    print_r("\r\n");
    print_r("################################ \r\n");
    print_r("########## STATYSTYKI ########## \r\n");
    print_r("################################ \r\n");
    print_r("\r\n");
    $count = [];
    foreach ($results as $result) {
        if (!isset($count[$result])) {
            $count[$result] = 1;
        } else {
            $count[$result]++;
        }
    }

    foreach ($count as $index => $value) {
        $i = $index + 1;
        print_r("#" . $i . " = " . $value . " (" . ($value / count($results)) * 100 . "%) \r\n");
    }

    print_r("\r\n");
    print_r("################################ \r\n");
}

$usedAlgorithms = [];

for ($i = 0; $i < 1; $i++) {

    $unique = rand(10000, 99999);

    $data = generateJSON(3, 12);
    saveJSON($data, $date->format('YmdHis') .".". $unique);

    $resources = parseResources($data);
    $tasks = parseTasks($data);
    $scenarios = [];

    $one = new GreedyOne($date->format('YmdHis') .".". $unique, $tasks, $resources);
    $one->execute();
    $one->getInfo();
    $result = $one->getTheBestRoute();
    $scenarios[] = json_encode($result['result']);
    clearDistances($resources);

    $two = new GreedyTwo($date->format('YmdHis') .".". $unique, $tasks, $resources);
    $two->execute();
    $two->getInfo();
    $result = $two->getTheBestRoute();
    $scenarios[] = json_encode($result['result']);
    clearDistances($resources);

    $three = new GreedyThree($date->format('YmdHis') .".". $unique, $tasks, $resources);
    $three->execute();
    $three->getInfo();
    $result = $three->getTheBestRoute();
    $scenarios[] = json_encode($result['result']);
    clearDistances($resources);

    $finishFirst = new FinishFirst($date->format('YmdHis') .".". $unique, $tasks, $resources, 2);
    $finishFirst->execute();
    $finishFirst->getInfo();
    $result = $finishFirst->getTheBestRoute();
    $scenarios[] = json_encode($result['result']);
    clearDistances($resources);

    $naive = new Naive($date->format('YmdHis') .".". $unique, $tasks, $resources);
    $naive->execute();
    $naive->getInfo();
    clearDistances($resources);

    $algorithm = findTheBest($scenarios);
    $usedAlgorithms[] = $algorithm;
    print_r("\r\n");
    print_r($algorithms[$algorithm] . "\r\n");
    $file = file_put_contents('results/prefixes.txt', "\r\n" . $date->format('YmdHis') .".". $unique ." ".$algorithms[$algorithm], FILE_APPEND);


}

statistics($usedAlgorithms);
