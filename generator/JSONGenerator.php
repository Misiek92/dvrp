<?php

require_once 'Resource.php';
require_once 'Task.php';

class JSONGenerator
{
    const MIN_LATITUDE = 0;
    const MAX_LATITUDE = 900;
    const MIN_LONGITUDE = 0;
    const MAX_LONGITUDE = 1800;
   
    private $repeats;
    private $type;
    private $data = [];

    public function setRepeats($repeats)
    {
        $this->repeats = $repeats;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function possibleTypes()
    {
        return [
            Task::TYPE,
            Resource::TYPE
        ];
    }

    public function __construct($repeats, $type)
    {
        if (!in_array($type, $this->possibleTypes())) {
            throw new Exception("Zły typ");
        }

        $this->repeats = $repeats;
        $this->type = $type;
    }

    public function generate()
    {
        for ($i = 0; $i < $this->repeats; $i++) {
             $object = new $this->type();
             $object->setId($i);
             $object->setLatitude(rand(self::MIN_LATITUDE, self::MAX_LATITUDE));
             $object->setLongitude(rand(self::MIN_LONGITUDE, self::MAX_LONGITUDE));
             if ($this->type === Task::TYPE) {
                 $object->setTask(floor($i/2));
                 $type = $i % 2 == 0 ? Task::TYPE_PICKUP : Task::TYPE_DROP;
                 $object->setType($type);
             }
             $this->data[] = $object;
        }
        
        return $this->data;
    }
    


}
