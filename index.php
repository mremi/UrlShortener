<?php  

require __DIR__ . '/vendor/autoload.php';


use Mremi\UrlShortener\Model\Link;
use Mremi\UrlShortener\Provider\Bitly\BitlyProvider;
use Mremi\UrlShortener\Provider\Bitly\OAuthClient;

$link = new Link;
$link->setLongUrl('https://github.com/madeny/lhttps');

// die(var_dump($link));

$bitlyProvider = new BitlyProvider(new OAuthClient('califian', 'fx4gqBMA'), // or new GenericAccessTokenAuthenticator('generic_access_token')
    array('connect_timeout' => 10, 'timeout' => 10)
);

die(var_dump($bitlyProvider->shorten($link)));
