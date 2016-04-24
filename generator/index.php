<?php

require_once 'JSONGenerator.php';
require_once 'Resource.php';
require_once 'Task.php';

function generateJSON($numberOfResources = 3, $numberOfTasks = 5)
{
    $data = [];

    try {
        $resources = new JSONGenerator($numberOfResources, "Resource");
        $data['resources'] = $resources->generate();
        $tasks = new JSONGenerator($numberOfTasks, "Task");
        $data['tasks'] = $tasks->generate();
    } catch (Exception $ex) {
        echo "Wystąpił błąd podczas próby wygenerowania zleceń";
    }

    return $data;
}

function saveJSON($data, $id = '')
{
    $fp = fopen('../algorytmy/results/'.$id.'-data.json', 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
}

//saveJSON(generateJSON());



