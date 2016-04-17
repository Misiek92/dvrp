<?php

require_once 'POI.php';

class Task extends POI implements JsonSerializable
{
    const TYPE = "Task";
    const TYPE_PICKUP = "pickup";
    const TYPE_DROP = "drop";

    protected $task;
    protected $type;
    protected $relatedTaskId;

    /*
     * Settery
     */

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
    
    function setRelatedTaskId($related)
    {
        $this->relatedTaskId = (int) $related;
    }

    /*
     * Gettery
     */

    public function getTask()
    {
        return $this->task;
    }

    public function getType()
    {
        return $this->type;
    }
    
    function getRelatedTaskId()
    {
        return $this->relatedTaskId;
    }

    
    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "task" => $this->task,
            "type" => $this->type,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "relatedTaskId" => $this->relatedTaskId
        ];
    }

}
