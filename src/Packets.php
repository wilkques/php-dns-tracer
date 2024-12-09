<?php

namespace Wilkques\DNS;

use Wilkques\Helpers\Arrays;

class Packets
{
    /**
     * @var array
     */
    protected $packets;

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
}
