# Dig

[![Latest Stable Version](https://poser.pugx.org/wilkques/dns-dig/v/stable)](https://packagist.org/packages/wilkques/dns-dig)
[![License](https://poser.pugx.org/wilkques/dns-dig/license)](https://packagist.org/packages/wilkques/dns-dig)

## Installation
`composer require wilkques/php-dns-dig`

## How to use
```php
$dig = new \Wilkques\DNS\Dig;

$resolve = $dig->trace('<host name>', '<dns type>');

var_dump(
    $resolve
);
```