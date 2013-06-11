URL shortener library
=====================

This library allows you to shorten a URL, reverse is also possible.

```php
<?php

use Mremi\UrlShortener\Bitly\BitlyShortener;
use Mremi\UrlShortener\Bitly\OAuthClient;

$shortener = new BitlyShortener(new OAuthClient('username', 'password'));

$shortened = $shortener->shorten('http://www.google.com');

$expanded = $shortener->expand('http://bit.ly/13TE0qU');
```