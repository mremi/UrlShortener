<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\ShortCm;

use Mremi\UrlShortener\Provider\ShortCm\ShortCmProvider;

/**
 * Tests ShortCmProvider class.
 */
class ShortCmProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testShorten() {

        $provider = $this->getMock('\Mremi\UrlShortener\Provider\ShortCm\ShortCmProvider', array('createClient'), array(
            'ABCD1234',
            'abc.de',
        ));
        $client = $this->getMock('\GuzzleHttp\Client', array('post'));

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            array(),
            '{"id":12345678,"originalURL":"http://perdu.com?k=12345678901234567890","DomainId":98765,"archived":false,"path":"pgsYuBjuGtzn","redirectType":null,"OwnerId":12345,"updatedAt":"2019-05-10T05:18:24.928Z","createdAt":"2019-05-10T05:18:24.928Z","secureShortURL":"https://abc.de/pgsYuBjuGtzn","shortURL":"https://abc.de/pgsYuBjuGtzn","duplicate":false}'
        );

        $client
            ->expects($this->once())
            ->method('post')
            ->with(
                '/links',
                array(
                    'json' => array(
                        'domain' => 'abc.de',
                        'originalURL' => 'http://perdu.com?k=12345678901234567890',
                    ),
                )
            )
            ->will($this->returnValue($response));

        $provider
            ->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($client));

        $link = new \Mremi\UrlShortener\Model\Link;
        $link->setLongUrl('http://perdu.com?k=12345678901234567890');
        $provider->shorten($link);

        $this->assertSame('https://abc.de/pgsYuBjuGtzn', $link->getShortUrl());
    }

    public function testValidate() {

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            array(),
            '{"id":12345678,"originalURL":"http://perdu.com?k=12345678901234567890","DomainId":98765,"archived":false,"path":"pgsYuBjuGtzn","redirectType":null,"OwnerId":12345,"updatedAt":"2019-05-10T05:18:24.928Z","createdAt":"2019-05-10T05:18:24.928Z","secureShortURL":"https://abc.de/pgsYuBjuGtzn","shortURL":"https://abc.de/pgsYuBjuGtzn","duplicate":false}'
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $method = new \ReflectionMethod($provider, 'validate');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    public function testValidateIncomplete() {

        $response = new \GuzzleHttp\Psr7\Response(
            200,
            array(),
            '{"id":12345678,"originalURL":"http://perdu.com?k=12345678901234567890","DomainId":98765,"archived":false,"path":"pgsYuBjuGtzn","redirectType":null,"OwnerId":12345,"updatedAt":"2019-05-10T05:18:24.928Z","createdAt":"2019-05-10T05:18:24.928Z","secureShortURL":"","shortURL":"","duplicate":false}'
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException('\Mremi\UrlShortener\Exception\InvalidApiResponseException');

        $method = new \ReflectionMethod($provider, 'validate');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    public function testValidateError() {

        $response = new \GuzzleHttp\Psr7\Response(
            409
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException('\Mremi\UrlShortener\Exception\InvalidApiResponseException');

        $method = new \ReflectionMethod($provider, 'validate');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

}
