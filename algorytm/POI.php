<?php

require_once 'Distance.php';

class POI
{
    protected $id;
    protected $latitude;
    protected $longitude;
    protected $distances = [];

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
    
    public function addDistance(Distance $distance)
    {
        $this->distances[] = $distance;
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
    
    /**
     * 
     * @return Distance[];
     */
    public function getDistances()
    {
        return $this->distances;
    }
}
