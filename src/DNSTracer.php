<?php

namespace Wilkques\DNS;

use Wilkques\Helpers\Arrays;

defined('DNS_CAA') or define('DNS_CAA', 8192);

class DNSTracer
{
    /**
     * show Top-Level Domain
     * 
     * @var bool|false
     */
    protected $showTLD = false;

    /**
     * DNS Record Types
     * 
     * @var array
     */
    protected $dnsRecordTypes = [
        DNS_A       => 'A',
        DNS_MX      => 'MX',
        DNS_CNAME   => 'CNAME',
        DNS_NS      => 'NS',
        DNS_PTR     => 'PTR',
        DNS_TXT     => 'TXT',
        DNS_HINFO   => 'HINFO',
        DNS_CAA     => 'CAA',
        DNS_SOA     => 'SOA',
        DNS_AAAA    => 'AAAA',
        DNS_A6      => 'A6',
        DNS_SRV     => 'SRV',
        DNS_NAPTR   => 'NAPTR',
    ];

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
     * @return static
     */
    public function showTopLevelDomain()
    {
        $this->showTLD = true;

        return $this;
    }

    /**
     * @return array
     */
    protected function rootNameserverIps()
    {
        return Arrays::map($this->rootNameservers, function ($host) {
            return gethostbyname($host);
        });
    }

    /**
     * @param string $domain
     * @param string $recordType
     * 
     * @return Packets
     */
    public function trace($domain, $recordType = 'CNAME')
    {
        $domainParts = explode('.', $domain);

        $traceResult = array();

        $currentNameservers = $this->rootNameserverIps();

        $showTld = $this->showTLD ? 1 : 2;

        for ($i = (count($domainParts) - $showTld); $i >= 0; $i--) {
            $currentDomain = implode('.', array_slice($domainParts, $i));

            $resolveResult = $this->resolveWithNameservers(
                $currentDomain,
                $recordType,
                $currentNameservers
            );

            $traceResult[] = $resolveResult;

            $resolveResultNameservers = Arrays::get($resolveResult, 'nameservers', []);

            if (!empty($resolveResultNameservers)) {
                $currentNameservers = $resolveResultNameservers;
            }

            if ($i == 0) break;
        }

        return new Packets(
            array_reverse($traceResult)
        );
    }

    /**
     * @param string $domain
     * @param string $recordType
     * @param array $nameservers
     * 
     * @throws ResolverException
     * 
     * @return array
     */
    private function resolveWithNameservers($domain, $recordType, $nameservers)
    {
        foreach ($nameservers as $nameserver) {
            try {
                $records = dns_get_record($domain, $this->getDNSRecordType($recordType));

                $nsRecords = dns_get_record($domain, DNS_ANY);

                $nextNameservers = array_column($nsRecords, 'target');

                return array(
                    'domain' => $domain,
                    'records' => new Packet($records),
                    'nameservers' => $nextNameservers,
                );
            } catch (\Exception $e) {
                continue;
            }
        }

        throw new ResolverException('DNS Resolve failed');
    }

    /**
     * @param int|string $dnsRecordType
     * @param int $default
     * 
     * @return int
     */
    public function getDnsRecordType($dnsRecordType, $default = DNS_CNAME)
    {
        $dnsRecordTypes = $this->dnsRecordTypes;

        if (is_numeric($dnsRecordType) && array_key_exists($dnsRecordType, $dnsRecordTypes)) {
            return $dnsRecordType;
        }

        $dnsRecordType = strtoupper($dnsRecordType);

        if (in_array($dnsRecordType, $dnsRecordTypes)) {
            $dnsRecordTypes = array_flip($dnsRecordTypes);

            return Arrays::get($dnsRecordTypes, $dnsRecordType);
        }

        return $default;
    }
}
