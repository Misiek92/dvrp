<?php

require_once 'POI.php';

class Resource extends POI implements JsonSerializable
{
    const TYPE = "Resource";

    public function jsonSerialize()
    {
        return [
            "id" => $this->id,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "distances" => $this->distances
        ];
    }

}
