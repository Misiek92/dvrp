<?php

require_once 'Resource.php';
require_once 'Task.php';

class One
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
     * @param Task $tasks []
     * @param Resource $resources []
     */
    public function __construct($id, $tasks, $resources)
    {
        $this->id = $id;
        $this->resources = $resources;
        $this->tasks = $tasks;
        $this->time = new DateTime('now');
    }

    public function euclidesDistance($first, $second)
    {
        $lat1 = $first->getLatitude();
        $lon1 = $first->getLongitude();
        $lat2 = $second->getLatitude();
        $lon2 = $second->getLongitude();
        $x = abs($lon1 - $lon2);
        $y = abs($lat1 - $lat2);

        return sqrt(pow($x, 2) + pow($y, 2));
    }

    /**
     * @param $lon1
     * @param $lat1
     * @param $lon2
     * @param $lat2
     * @return float
     */
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

    private function save()
    {
        $fp = fopen('result.json', 'w');
        fwrite($fp, json_encode($this->theBestRoute));
        fclose($fp);
    }

    private function checkIfPossible($actual, $task)
    {
        if ($task->getType() == "pickup") {
            return true;
        }

        for ($i = 1; $i < count($actual); $i++) {
            if ($actual[$i]->getTask() == $task->getTask()) {
                return true;
            }
        }
        return false;
    }

    private function find()
    {
        $paths = [];
        foreach ($this->resources as $i => $resource) {
            $paths[$i] = [$resource];
        }

        while (!empty($this->tasks)) {
            foreach ($this->resources as $i => $resource) {
                $distance = INF;
                $closestTaskIndex = null;
                $closestTask = null;
                foreach ($this->tasks as $index => $task) {
                    if ($this->checkIfPossible($paths[$i], $task)) {
                        $actualDistance = $this->euclidesDistance($resource, $task);
                        if ($actualDistance < $distance) {
                            $distance = $actualDistance;
                            $closestTaskIndex = $index;
                            $closestTask = $task;
                        }
                    }
                }
                if ($closestTask) {
                    $paths[$i][] = $closestTask;
                    array_splice($this->tasks, $closestTaskIndex, 1);
                }
            }
        }
        $this->theBestRoute = $paths;
        return true;
    }

    public function execute()
    {

        $this->find();
        $this->save();

    }
}