<?php

namespace Wilkques\DNS;

use Wilkques\Helpers\Arrays;

class Packet
{
    /**
     * @var array
     */
    protected $packet;

    /**
     * @var array
     */
    public function __construct($packet)
    {
        $this->packet = $packet;
    }

    /**
     * @return string
     */
    public function rrname()
    {
        return Arrays::get($this->packet, 'rrname');
    }

    /**
     * @return int
     */
    public function rrttl()
    {
        return Arrays::get($this->packet, 'rrttl');
    }

    /**
     * @return string
     */
    public function rrclass()
    {
        return Arrays::get($this->packet, 'rrclass');
    }

    /**
     * @return string
     */
    public function rrtype()
    {
        return Arrays::get($this->packet, 'rrtype');
    }

    /**
     * @return string
     */
    public function rrdata()
    {
        return Arrays::get($this->packet, 'rrdata');
    }

    /**
     * @param callback $callBack
     * 
     * @return array
     */
    public function map($callBack)
    {
        return Arrays::map($this->packet, $callBack);
    }

    /**
     * @param string $key
     * 
     * @return mixed
     */
    public function __get($key)
    {
        return Arrays::get($this->packet, $key);
    }
}
