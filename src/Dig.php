<?php

namespace Wilkques\DNS;

class Dig extends \Net_DNS2_Resolver
{
    /**
     * root nameserver
     * 
     * @var array
     * 
     * @see [iana-office](https://www.iana.org/domains/root/servers)
     */
    protected $hostName = array(
        "a.root-servers.net", // Verisign, Inc.
        "b.root-servers.net", // University of Southern California, Information Sciences Institute
        "c.root-servers.net", // Cogent Communications
        "d.root-servers.net", // University of Maryland
        "e.root-servers.net", // NASA (Ames Research Center)
        "f.root-servers.net", // Internet Systems Consortium, Inc.
        "g.root-servers.net", // US Department of Defense (NIC)
        "h.root-servers.net", // US Army (Research Lab)
        "i.root-servers.net", // Netnod
        "j.root-servers.net", // Verisign, Inc.
        "k.root-servers.net", // RIPE NCC
        "l.root-servers.net", // ICANN
        "m.root-servers.net", // WIDE Project
    );

    /**
     * @param array $nameserver
     * 
     * @return static
     */
    public function setNameserver($nameservers)
    {
        $this->setServers($nameservers);

        return $this;
    }

    /**
     * @return array
     */
    protected function rootServers()
    {
        return array_map(function ($host) {
            return gethostbyname($host);
        }, $this->hostName);
    }

    /**
     * @param \Net_DNS2_Packet_Response $resolver
     * 
     * @return array
     */
    public function nameserver($resolver)
    {
        if ($resolver->additional) {
            $nameservers = array_map(function ($resolver) {
                return $resolver->address;
            }, $resolver->additional);

            return array_values(
                array_filter($nameservers)
            );
        }

        return array_map(function ($item) {
            return gethostbyname($item->nsdname);
        }, $resolver->authority);
    }

    /**
     * @param string $domain
     * @param string $type
     * @param string $class
     * 
     * @return array
     */
    public function trace($domain, $type = 'NS', $class = 'IN')
    {
        // root
        $resolver = $this->setNameserver(
            $this->rootServers()
        )->query($domain, 'NS', $class);

        // gtld
        $resolver = $this->setNameserver(
            $this->nameserver($resolver)
        )->query($domain, 'NS', $class);

        // answer
        $resolver = $this->setNameserver(
            $this->nameserver($resolver)
        )->query($domain, $type, $class);

        return new Packets(
            array_map(function ($item) {
                $item = $item->asArray();

                return new Packet(
                    array(
                        'rrname'      => $item['name'],
                        'rrclass'     => $item['class'],
                        'rrttl'       => $item['ttl'],
                        'rrtype'      => $item['type'],
                        'rrdata'      => $item['rdata'],
                    )
                );
            }, $resolver->answer)
        );
    }
}
