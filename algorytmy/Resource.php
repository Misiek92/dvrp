<?php

require_once 'POI.php';

class Resource extends POI implements JsonSerializable
{
    const TYPE = "Resource";

    protected $distance = 0;

    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param $value
     */
    public function addDistance($value)
    {
        $this->distance += $value;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "distance" => $this->distance
        ];
    }

}
