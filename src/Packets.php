<?php

namespace Wilkques\DNS;

use Wilkques\Helpers\Arrays;

class Packets
{
    /**
     * @var array
     */
    protected $packets = array();

    /**
     * @var array
     */
    public function __construct($packets)
    {
        $this->packets = $packets;
    }

    /**
     * @param callback $callBack
     * 
     * @return array
     */
    public function map($callBack)
    {
        return Arrays::map($this->packets, $callBack);
    }

    /**
     * @return array
     */
    public function rrdatas()
    {
        return Arrays::pluck($this->packets, 'rrdata');
    }
}
