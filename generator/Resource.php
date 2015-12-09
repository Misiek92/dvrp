<?php

class Resource implements JsonSerializable
{
    const TYPE = "Resource";
    private $id;
    private $latitude;
    private $longitude;

    /*
     * Settery
     */

    public function setId($id)
    {
        $this->id = $id;
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
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
        ];
    }

}
