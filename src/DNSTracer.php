<?php

namespace Wilkques\DNS;

use Wilkques\Helpers\Arrays;
use Wilkques\Helpers\Objects;

class DNSTracer extends \Net_DNS2_Resolver
{
    /**
     * show Top-Level Domain
     * 
     * @var bool|false
     */
    protected $showTLD = false;

    /**
     * root nameserver
     * 
     * @var array
     * 
     * @see [iana-office](https://www.iana.org/domains/root/servers)
     */
    protected $rootNameservers = array(
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
    protected function rootNameserverIps()
    {
        return array_values(
            $this->hostToIps($this->rootNameservers)
        );
    }

    /**
     * @param array $hosts
     * 
     * @return array
     */
    protected function hostToIps($hosts)
    {
        $ips = Arrays::map($hosts, function ($host) {
            if (is_object($host)) {
                $host = Objects::get($host, 'nsdname', Objects::get($host, 'mname'));
            }

            return gethostbyname($host);
        });

        return array_values(
            Arrays::filter($ips)
        );
    }

    /**
     * @param \Net_DNS2_Packet_Response $resolver
     * 
     * @return array
     */
    public function nameservers($resolver)
    {
        if ($resolver->additional) {
            $nameservers = Arrays::map($resolver->additional, function ($resolver) {
                return $resolver->address;
            });

            return array_values(
                Arrays::filter($nameservers)
            );
        }

        return $this->hostToIps($resolver->authority);
    }

    /**
     * @param string $domain
     * @param array|[] $options
     * 
     * @return array
     */
    public function dnsTLDNameServers($domain, $class = 'IN')
    {
        // root
        $resolver = $this->setNameserver(
            $this->rootNameserverIps()
        )->query($domain, 'NS', $class);

        // gtld
        $resolver = $this->setNameserver(
            $this->nameservers($resolver)
        )->query($domain, 'NS', $class);

        if ($resolver->additional) {
            return $this->nameservers($resolver);
        }

        return $this->hostToIps($resolver->authority);
    }

    /**
     * @param string $domain
     * @param string $type
     * 
     * @return Packets
     */
    public function trace($domain, $type = 'CNAME', $class = 'IN')
    {
        $domainParts = explode('.', $domain);

        $this->setNameserver(
            $this->dnsTLDNameServers($domain, $class)
        );

        for ($i = (count($domainParts) - 2); $i >= 0; $i--) {
            // answer
            $resolver = $this->query($domain, $type, $class);

            $nameservers = $this->nameservers($resolver);

            if (!empty($nameservers))
                $this->setNameserver($nameservers);

            if (!empty($resolver->answer)) {
                return new Packets(
                    Arrays::map($resolver->answer, function ($answer) {
                        $answer = $answer->asArray();

                        return new Packet(
                            array(
                                'rrname'    => $answer['name'],
                                'rrclass'   => $answer['class'],
                                'rrttl'     => $answer['ttl'],
                                'rrtype'    => $answer['type'],
                                'rrdata'    => $answer['rdata'],
                            )
                        );
                    })
                );
            }
        }

        throw new ResolverException('DNS Resolve failed');
    }
}
