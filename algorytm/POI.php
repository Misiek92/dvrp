<?php

class POI
{

    protected $id;
    protected $latitude;
    protected $longitude;

    public function __toString()
    {
        return $this->id;
    }

    /*
     * Settery
     */

    public function setId($id)
    {
        $this->id = (int) $id;
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


}
