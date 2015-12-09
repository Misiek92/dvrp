<?php

class Task implements JsonSerializable
{
    const TYPE = "Task";
    const TYPE_PICKUP = "pickup";
    const TYPE_DROP = "drop";

    private $id;
    private $task;
    private $type;
    private $latitude;
    private $longitude;

    /*
     * Settery
     */

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /*
     * Gettery
     */

    public function getId()
    {
        return $this->id;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "task" => $this->task,
            "type" => $this->type,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
        ];
    }

}
