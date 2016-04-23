<?php

require_once 'Resource.php';
require_once 'Task.php';
require_once 'Route.php';
require_once 'Naive.php';
require_once 'GreedyOne.php';
require_once 'GreedyTwo.php';
require_once 'GreedyThree.php';

const DATA_FILE = "../data.json";

$file = file_get_contents(DATA_FILE);
$data = json_decode($file, true);
$resources = [];
$tasks = [];
//$pickups = [];
//$drops = [];
$taskCount = count($data['tasks']);
$resourceCount = count($data['resources']);
$date = new DateTime('now');

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
//    if ($t['type'] == "pickup") {
//        $pickups[] = $task;
//    } else {
//        $drops[] = $task;
//        $task->setRelatedTaskId($tasks[$i - 1]->getId());
//        $tasks[$i - 1]->setRelatedTaskId($task->getId());
//    }
    $tasks[] = $task;
}

function dd($element)
{
    die(json_encode($element));
}

$naive = new GreedyThree($date->format('Ymdhis'), $tasks, $resources);
$naive->execute();

$naive->getInfo();


