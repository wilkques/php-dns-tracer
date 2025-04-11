# DNS Tracer

[![Latest Stable Version](https://poser.pugx.org/wilkques/dns-tracer/v/stable)](https://packagist.org/packages/wilkques/dns-tracer)
[![License](https://poser.pugx.org/wilkques/dns-tracer/license)](https://packagist.org/packages/wilkques/dns-tracer)

## Description
Use PHP to simulate the functionality of DNS tools like DIG or Nslookup

## Installation
`composer require wilkques/dns-tracer`

## How to use
```php
$dig = new \Wilkques\DNS\DNSTracer;

$resolve = $dig->trace('<host name>', '<dns type>');

var_dump(
    $resolve
);
```

## Other keys in associative arrays dependent on dns type

| Type  | Extra Columns |
|-------|---------------|
| **A** | `ip`: An IPv4 address in dotted decimal notation. |
| **MX** | `pri`: Priority of mail exchanger. Lower numbers indicate greater priority. <br> `target`: FQDN of the mail exchanger. |
| **CNAME** | `target`: FQDN of location in DNS namespace to which the record is aliased. |
| **NS** | `target`: FQDN of the name server which is authoritative for this hostname. |
| **PTR** | `target`: Location within the DNS namespace to which this record points. |
| **TXT** | `txt`: Arbitrary string data associated with this record. |
| **HINFO** | `cpu`: IANA number designating the CPU of the machine referenced by this record. <br> `os`: IANA number designating the Operating System on the machine referenced by this record. See IANA's  [» Operating System Names](https://www.iana.org/assignments/operating-system-names/operating-system-names.xhtml) for the meaning of these values. |
| **CAA** | `flags`: A one-byte bitfield; currently only bit 0 is defined, meaning 'critical'; other bits are reserved and should be ignored. <br> `tag`: The CAA tag name (alphanumeric ASCII string). <br> `value`: The CAA tag value (binary string, may use subformats).For additional information See [» RFC 6844](https://datatracker.ietf.org/doc/html/rfc6844). |
| **SOA** | `mname`: FQDN of the machine from which the resource records originated. <br> `rname`: Email address of the administrative contact for this domain.  <br> `serial`: Serial # of this revision of the requested domain. <br> `refresh`: Refresh interval (seconds) secondary name servers should use when updating remote copies of this domain. <br> `retry`: Length of time (seconds) to wait after a failed refresh before making a second attempt. <br> `expire`: Maximum length of time (seconds) a secondary DNS server should retain remote copies of the zone data without a successful refresh before discarding. <br> `minimum-ttl`:  Minimum length of time (seconds) a client can continue to use a DNS resolution before it should request a new resolution from the server. Can be overridden by individual resource records. |
| **AAAA** | `ipv6`: IPv6 address. |
| **A6** | `masklen`: Length (in bits) to inherit from the target specified by **chain**. <br> `ipv6`: Address for this specific record to merge with **chain**. <br> `chain`: Parent record to merge with **ipv6** data. |
| **SRV** | `pri`: (Priority) lowest priorities should be used first. <br> `weight`: Ranking to weight which of commonly prioritized **targets** should be chosen at random. `target` and `port`: hostname and port where the requested service can be found. For additional information see: [» RFC 2782](https://datatracker.ietf.org/doc/html/rfc2782). |
| **NAPTR** | `order` and `pref`: Equivalent to **pri** and **weight** above. `flags`, `services`, `regex`, and `replacement`: Parameters as defined by [» RFC 2915](https://datatracker.ietf.org/doc/html/rfc2915). |