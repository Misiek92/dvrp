<?php

require_once 'JSONGenerator.php';
require_once 'Resource.php';
require_once 'Task.php';

// liczba zasobów do wygenerowania
$numberOfResources = isset($_GET['resources']) ? (int) $_GET['resources'] : 3;

// liczba zadań do wygenerowania
$numberOfTasks = isset($_GET['tasks']) ? (int) $_GET['tasks']*2 : 8;

// tu będą przechowywane dane
$data = [];

try {
    $resources = new JSONGenerator($numberOfResources, "Resource");
    $data['resources'] = $resources->generate();
    $tasks = new JSONGenerator($numberOfTasks, "Task");
    $data['tasks'] = $tasks->generate();
} catch (Exception $ex) {
    echo "Wystąpił błąd podczas próby wygenerowania zleceń";
}

$fp = fopen('../data.json', 'w');
fwrite($fp, json_encode($data));
fclose($fp);

echo json_encode($data);



