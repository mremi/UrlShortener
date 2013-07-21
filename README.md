URL shortener library
=====================

This library allows you to shorten a URL, reverse is also possible.

**Basic Docs**

* [Installation](#installation)
* [Bit.ly API](#bitly-api)

<a name="installation"></a>

## Installation

Only 1 step:

### Download UrlShortener using composer

Add UrlShortener in your composer.json:

```js
{
    "require": {
        "mremi/url-shortener": "dev-master"
    }
}
```

Now tell composer to download the library by running the command:

``` bash
$ php composer.phar update mremi/url-shortener
```

Composer will install the library to your project's `vendor/mremi` directory.

<a name="bitly-api"></a>

## Bit.ly API

```php
<?php

use Mremi\UrlShortener\Bitly\BitlyShortener;
use Mremi\UrlShortener\Bitly\OAuthClient;
use Mremi\UrlShortener\Http\ClientFactory;

$clientFactory = new ClientFactory;

$shortener = new BitlyShortener($clientFactory, new OAuthClient($clientFactory, 'username', 'password'));

$shortened = $shortener->shorten('http://www.google.com');

$expanded = $shortener->expand('http://bit.ly/13TE0qU');
```
