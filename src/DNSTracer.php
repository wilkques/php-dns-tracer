<?php

namespace Wilkques\DNS;

class DNSTracer
{
    /**
     * DNS Record Types
     * 
     * @var array
     */
    protected $dnsRecordTypes = [
        DNS_CNAME   => 'CNAME',
        DNS_NS      => 'NS',
        DNS_A       => 'A',
        DNS_TXT     => 'TXT',
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
     * @return array
     */
    protected function rootNameserverIps()
    {
        return array_map(function ($host) {
            return gethostbyname($host);
        }, $this->rootNameservers);
    }

    /**
     * 模擬 dig +trace 功能
     * @param string $domain 要解析的域名
     * @param string $recordType 記錄類型，默認為 A
     * @return array 解析追蹤結果
     */
    public function trace($domain, $recordType = 'A')
    {
        $domainParts = explode('.', $domain);

        $traceResult = array();

        // 從根域名服務器開始解析
        $currentNameservers = $this->rootNameserverIps();

        for ($i = count($domainParts) - 1; $i >= 0; $i--) {
            $currentDomain = implode('.', array_slice($domainParts, $i));

            // 嘗試從當前可用的域名服務器解析
            $resolveResult = $this->resolveWithNameservers(
                $currentDomain,
                $recordType,
                $currentNameservers
            );

            // 記錄當前級別的解析信息
            $traceResult[] = $resolveResult;

            // 更新下一級的域名服務器
            if (!empty($resolveResult['nameservers'])) {
                $currentNameservers = $resolveResult['nameservers'];
            }

            // 如果已經解析到具體域名，則可以提前結束
            if ($i == 0) break;
        }

        return new Packets(
            array_reverse($traceResult)
        );
    }

    /**
     * 使用指定的域名服務器解析域名
     * @param string $domain 域名
     * @param string $recordType 記錄類型
     * @param array $nameservers 可用的域名服務器
     * @return array 解析結果
     */
    private function resolveWithNameservers($domain, $recordType, $nameservers)
    {
        foreach ($nameservers as $nameserver) {
            try {
                // 模擬 DNS 查詢
                $records = dns_get_record($domain, $this->getDNSRecordType($recordType));

                // 提取下一級授權域名服務器
                $nsRecords = dns_get_record($domain, DNS_ANY);

                $nextNameservers = array_column($nsRecords, 'target');

                return array(
                    'domain' => $domain,
                    'records' => new Packet($records),
                    'nameservers' => $nextNameservers,
                );
            } catch (\Exception $e) {
                // 解析失敗，嘗試下一個域名服務器
                continue;
            }
        }

        throw new ResolverException('DNS解析失敗');
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

            return $dnsRecordTypes[$dnsRecordType];
        }

        return $default;
    }
}
