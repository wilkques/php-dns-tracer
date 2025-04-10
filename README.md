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