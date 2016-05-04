<?php

/*
 * Heuristic algorithm
 * On each iteration compare actual distance + the shortest possible
 * With other paths, and choose the shortest or end task if is reachable
 *
 */

require_once 'Resource.php';
require_once 'Task.php';
require_once 'Distance.php';

class FinishFirst
{
    protected $multiplier;

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

    protected $microTime;

    /**
     * @var DateTime
     */
    protected $timeEnd;

    protected $microTimeEnd;
    /**
     * @var array
     */
    protected $theBestRoute;

    /**
     * Naive constructor.
     * @param $id
     * @param Task $tasks []
     * @param Resource $resources []
     * @param integer $multiplier
     */
    public function __construct($id, $tasks, $resources, $multiplier = 2)
    {
        $this->id = $id;
        $this->resources = $resources;
        $this->tasks = $tasks;
        $this->multiplier = $multiplier;
    }

    /**
     * @return array
     */
    public function getTheBestRoute()
    {
        return [
            "result" => $this->theBestRoute,
            "totalDistance" => round($this->totalDistance(), 2),
            "time" => $this->dateDifference(),
            "theLongest" => $this->theLongest()
        ];
    }

    public function theLongest()
    {
        $theLongest = 0;
        $id = null;
        foreach ($this->theBestRoute as $i => $route) {
            $distance = $route[0]->getDistance();
            if ($distance > $theLongest) {
                $theLongest = $distance;
                $id = $i;
            }
        }

        return round($theLongest, 2) . " (ID: " .$id.")";
    }

    private function dateDifference()
    {
        $miliseconds = $this->microTimeEnd - $this->microTime;

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
        $info .= "###         ALGORYTM `FINISH FIRST`       ### \r\n";
        $info .= "###       Priorytet konczenia zadan       ### \r\n";
        $info .= "############################################# \r\n";
        $info .= "# \r\n";
        $info .= "# Rozpoczecie obliczen: " . $this->time->format('H:i:s') . "\r\n";
        $info .= "# Ukonczenie:           " . $this->timeEnd->format('H:i:s') . "\r\n";
        $info .= "# Czas wykonywania:     " . $this->dateDifference() . "\r\n";
        $info .= "# Najdlugszy dystans:   " . $this->theLongest() . "\r\n";
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
        $fp = fopen('results/'.$this->id.'-finishFirst.json', 'w');
        //$fp = fopen('results/finishFirst.json', 'w');
        fwrite($fp, json_encode($this->getTheBestRoute()));
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
            $distancesAfterIteration = [];
            $finishDistancesAfterIteration = [];
            foreach ($this->resources as $i => $resource) {
                $distanceAfter = INF;
                $distanceRaw = null;
                $closestTaskIndex = null;
                $finishDistanceAfter = INF;
                $finishDistanceRaw = null;
                $finishClosestTaskIndex = null;
                foreach ($this->tasks as $index => $task) {
                    if ($this->checkIfPossible($paths[$i], $task)) {
                        if ($task->getType() == "pickup") {
                            $distanceObject = new Distance(end($paths[$i]), $task);
                            $distance = $distanceObject->euclides();
                            $distanceCheck = $distance + $resource->getDistance();
                            if ($distanceCheck < $distanceAfter) {
                                $distanceAfter = $distanceCheck;
                                $closestTaskIndex = $index;
                                $distanceRaw = $distance;
                            }
                        } else {
                            $distanceObject = new Distance(end($paths[$i]), $task);
                            $distance = $distanceObject->euclides();
                            $distanceCheck = $distance + $resource->getDistance();
                            if ($distanceCheck < $finishDistanceAfter) {
                                $finishDistanceAfter = $distanceCheck;
                                $finishClosestTaskIndex = $index;
                                $finishDistanceRaw = $distance;
                            }
                        }
                    }
                }
                $distancesAfterIteration[$i] = [
                    'distance' => $distanceAfter,
                    'rawDistance' => $distanceRaw,
                    'index' => $closestTaskIndex
                ];

                $finishDistancesAfterIteration[$i] = [
                    'distance' => $finishDistanceAfter,
                    'rawDistance' => $finishDistanceRaw,
                    'index' => $finishClosestTaskIndex
                ];
            }

            $choosenResourceIndex = null;
            $choosenTaskIndex = null;
            $distance = INF;
            $distanceRaw = null;
            foreach ($distancesAfterIteration as $key => $object) {
                if ($object['distance'] < $distance) {
                    $choosenResourceIndex = $key;
                    $choosenTaskIndex = $object['index'];
                    $distance = $object['distance'];
                    $distanceRaw = $object['rawDistance'];
                }
            }
            foreach ($finishDistancesAfterIteration as $key => $object) {
                if ($object['distance'] < ($distance * (int) $this->multiplier)) {
                    $choosenResourceIndex = $key;
                    $choosenTaskIndex = $object['index'];
                    $distance = $object['distance'];
                    $distanceRaw = $object['rawDistance'];
                }
            }

            $paths[$choosenResourceIndex][] = $this->tasks[$choosenTaskIndex];
            $this->resources[$choosenResourceIndex]->addDistance($distanceRaw);
            array_splice($this->tasks, $choosenTaskIndex, 1);

        }
        $this->theBestRoute = $paths;

        return true;
    }

    public function execute()
    {
        $this->time = new DateTime('now');
        $this->microTime = microtime(true);
        $this->find();
        $this->microTimeEnd = microtime(true);
        $this->timeEnd = new DateTime('now');
        $this->save();
    }
}