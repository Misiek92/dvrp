<?php

require_once 'Task.php';

class Route
{
    private $distance;
    /**
     * @var Task[] 
     */
    private $tasks = [];
    
    function getDistance()
    {
        return $this->distance;
    }

    function getTasks()
    {
        return $this->tasks;
    }

    function addDistance($distance)
    {
        $this->distance += $distance;
    }

    function addTask(Task $task)
    {
        $this->tasks[] = $task;
    }

    function getTasksId()
    {
        $list = [];
        foreach ($this->tasks as $task) {
            $list[] = $task->getId();
        }
        return $list;
    }
    
}
