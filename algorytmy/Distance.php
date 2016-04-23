<?php

class Distance
{
    protected $from;
    protected $to;

    /**
     * @return POI
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return POI
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Distance constructor.
     * @param POI $from
     * @param POI $to
     */
    public function __construct(POI $from, POI $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return float
     */
    public function euclides()
    {
        $lat1 = $this->from->getLatitude();
        $lon1 = $this->from->getLongitude();
        $lat2 = $this->to->getLatitude();
        $lon2 = $this->to->getLongitude();
        $x = abs($lon1 - $lon2);
        $y = abs($lat1 - $lat2);

        return sqrt(pow($x, 2) + pow($y, 2));
    }

    /**
     * @return float
     */
    private function geographic()
    {
        $lat1 = $this->from->getLatitude();
        $lon1 = $this->from->getLongitude();
        $lat2 = $this->to->getLatitude();
        $lon2 = $this->to->getLongitude();

        return round(acos(
                cos($lat1 * (PI() / 180)) *
                cos($lon1 * (PI() / 180)) *
                cos($lat2 * (PI() / 180)) *
                cos($lon2 * (PI() / 180)) +
                cos($lat1 * (PI() / 180)) *
                sin($lon1 * (PI() / 180)) *
                cos($lat2 * (PI() / 180)) *
                sin($lon2 * (PI() / 180)) +
                sin($lat1 * (PI() / 180)) *
                sin($lat2 * (PI() / 180))
            ) * 6371 * 1000);
    }
}