<?php

class Distance implements JsonSerializable
{
    private $value;
    private $related;
    
    function getValue()
    {
        return $this->value;
    }

    function getRelated()
    {
        return $this->related;
    }

    function setValue($value)
    {
        $this->value = $value;
    }

    function setRelated($related)
    {
        $this->related = $related;
    }

    public function jsonSerialize()
    {
        return [
            "value" => $this->value,
            "related" => $this->related,
        ];
    }
}
