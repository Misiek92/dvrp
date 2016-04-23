<?php

/*
 * This is the easiest heuristic algorithm
 * Script on each iteration takes the closest point
 * for each resource
 */

require_once 'Resource.php';
require_once 'Task.php';
require_once 'Distance.php';

class GreedyOne
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
     * @var DateTime
     */
    protected $timeEnd;

    protected $microtTimeEnd;
    /**
     * @var array
     */
    protected $theBestRoute;

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
    }

    /**
     * @return array
     */
    public function getTheBestRoute()
    {
        return $this->theBestRoute;
    }

    public function microtime_float()
    {
        //list($usec, $sec) = explode(" ", microtime());
        //return ((float)$usec + (float)$sec);
    }
    
    private function dateDifference()
    {
        $miliseconds = $this->microtTimeEnd - $_SERVER["REQUEST_TIME_FLOAT"];

        return round($miliseconds, 4) . " sekund";
    }

    private function totalDistance()
    {
        $distance = 0;
        foreach ($this->theBestRoute as $route) {
            $distance += $route[0]->getDistance();
        }
        return $distance;
    }

    public function getInfo()
    {
        $info = "\r\n";
        $info .= "############################################# \r\n";
        $info .= "###     PODSTAWOWY ALGORYTM ZACHLANNY     ### \r\n";
        $info .= "### Odcinek dla kazdego zasobu w iteracji ### \r\n";
        $info .= "############################################# \r\n";
        $info .= "# \r\n";
        $info .= "# Rozpoczecie obliczen: " . $this->time->format('H:i:s') . "\r\n";
        $info .= "# Ukonczenie:           " . $this->timeEnd->format('H:i:s') . "\r\n";
        $info .= "# Czas wykonywania:     " . $this->dateDifference() . "\r\n";
        $info .= "# Laczny dystans:       " . round($this->totalDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 1  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[0]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[0][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 2  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[1]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[1][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#############  ZASOB 3  #############\r\n";
        $info .= "# \r\n";
        $info .= "# Przypietych zadan:    " . (count($this->theBestRoute[2]) - 1) / 2 . "\r\n";
        $info .= "# Dystans :             " . round($this->theBestRoute[2][0]->getDistance(), 2) . "\r\n";
        $info .= "# \r\n";
        $info .= "#####################################\r\n";
        print $info;
    }

    private function save()
    {
        $fp = fopen('result.json', 'w');
        fwrite($fp, json_encode($this->theBestRoute));
        fclose($fp);
    }

    private function checkIfPossible($actual, Task $task)
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
                        $distanceObject = new Distance($resource, $task);
                        $actualDistance = $distanceObject->euclides();
                        if ($actualDistance < $distance) {
                            $resource->addDistance($actualDistance);
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
        $this->time = new DateTime('now');
        $this->find();
        $this->save();
        $this->microtTimeEnd = microtime(true);
        $this->timeEnd = new DateTime('now');
    }
}