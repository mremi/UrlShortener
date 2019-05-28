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

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\Link;
use Mremi\UrlShortener\Provider\ShortCm\ShortCmProvider;

/**
 * Tests ShortCmProvider class.
 */
class ShortCmProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the expand method with a valid API response.
     */
    public function testExpand()
    {
        $provider = $this->getMock(ShortCmProvider::class, array('createClient'), array(
            'ABCD1234',
            'abc.de',
        ));
        $client = $this->getMock(Client::class, array('get'));

        $response = new Response(
            200,
            array(),
            '{"id":1234567890,"path":"pgsYuBjuGtzn","title":null,"icon":null,"archived":false,"originalURL":"http://perdu.com?k=12345678901234567890","iphoneURL":null,"androidURL":null,"splitURL":null,"expiresAt":null,"expiredURL":null,"redirectType":null,"cloaking":null,"source":null,"AutodeletedAt":null,"createdAt":"2019-05-10T05:22:09.000Z","updatedAt":"2019-05-10T05:22:09.000Z","DomainId":98765,"OwnerId":12345,"tags":[],"secureShortURL":"https://abc.de/pgsYuBjuGtzn","shortURL":"https://abc.de/pgsYuBjuGtzn"}'
        );

        $client
            ->expects($this->once())
            ->method('get')
            ->with(
                '/links/expand',
                array(
                    'query' => array(
                        'domain' => 'abc.de',
                        'path'   => 'pgsYuBjuGtzn',
                    ),
                )
            )
            ->will($this->returnValue($response));

        $provider
            ->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($client));

        $link = new Link();
        $link->setShortUrl('http://abc.de/pgsYuBjuGtzn');
        $provider->expand($link);

        $this->assertSame('http://perdu.com?k=12345678901234567890', $link->getLongUrl());
    }

    /**
     * Tests the expand response validation method with a valid API response.
     */
    public function testValidateResponseExpand()
    {
        $response = new Response(
            200,
            array(),
            '{"id":1234567890,"path":"pgsYuBjuGtzn","title":null,"icon":null,"archived":false,"originalURL":"http://perdu.com?k=12345678901234567890","iphoneURL":null,"androidURL":null,"splitURL":null,"expiresAt":null,"expiredURL":null,"redirectType":null,"cloaking":null,"source":null,"AutodeletedAt":null,"createdAt":"2019-05-10T05:22:09.000Z","updatedAt":"2019-05-10T05:22:09.000Z","DomainId":98765,"OwnerId":12345,"tags":[],"secureShortURL":"https://abc.de/pgsYuBjuGtzn","shortURL":"https://abc.de/pgsYuBjuGtzn"}'
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $method = new \ReflectionMethod($provider, 'validateResponseExpand');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    /**
     * Tests the expand response validation method with an incomplete API response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Short.cm API could not expand this URL
     */
    public function testValidateResponseExpandIncomplete()
    {
        $response = new Response(
            200,
            array(),
            '{"id":1234567890,"path":"pgsYuBjuGtzn","title":null,"icon":null,"archived":false,"originalURL":"","iphoneURL":null,"androidURL":null,"splitURL":null,"expiresAt":null,"expiredURL":null,"redirectType":null,"cloaking":null,"source":null,"AutodeletedAt":null,"createdAt":"2019-05-10T05:22:09.000Z","updatedAt":"2019-05-10T05:22:09.000Z","DomainId":98765,"OwnerId":12345,"tags":[],"secureShortURL":"https://abc.de/pgsYuBjuGtzn","shortURL":"https://abc.de/pgsYuBjuGtzn"}'
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException(InvalidApiResponseException::class);

        $method = new \ReflectionMethod($provider, 'validateResponseExpand');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    /**
     * Tests the expand response validation method with a API response for a non-existing URL.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Short.cm API returned unexpected status code 404
     */
    public function testValidateResponseExpandError()
    {
        $response = new Response(
            404
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException(InvalidApiResponseException::class);

        $method = new \ReflectionMethod($provider, 'validateResponseExpand');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    /**
     * Tests the shorten method with a valid API response.
     */
    public function testShorten()
    {
        $provider = $this->getMock(ShortCmProvider::class, array('createClient'), array(
            'ABCD1234',
            'abc.de',
        ));
        $client = $this->getMock(Client::class, array('post'));

        $response = new Response(
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
                        'domain'      => 'abc.de',
                        'originalURL' => 'http://perdu.com?k=12345678901234567890',
                    ),
                )
            )
            ->will($this->returnValue($response));

        $provider
            ->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($client));

        $link = new Link();
        $link->setLongUrl('http://perdu.com?k=12345678901234567890');
        $provider->shorten($link);

        $this->assertSame('https://abc.de/pgsYuBjuGtzn', $link->getShortUrl());
    }

    /**
     * Tests the shorten response validation method with a valid API response.
     */
    public function testValidate()
    {
        $response = new Response(
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
    
    /**
     * Tests the shorten response validation method with a incomplete API response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Short.cm API returned unexpected status code 409
     */
    public function testValidateIncomplete()
    {
        $response = new Response(
            200,
            array(),
            '{"id":12345678,"originalURL":"http://perdu.com?k=12345678901234567890","DomainId":98765,"archived":false,"path":"pgsYuBjuGtzn","redirectType":null,"OwnerId":12345,"updatedAt":"2019-05-10T05:18:24.928Z","createdAt":"2019-05-10T05:18:24.928Z","secureShortURL":"","shortURL":"","duplicate":false}'
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException(InvalidApiResponseException::class);

        $method = new \ReflectionMethod($provider, 'validate');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }

    /**
     * Tests the shorten response validation method with a error API response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Short.cm API returned unexpected status code 409
     */
    public function testValidateError()
    {
        $response = new Response(
            409
        );

        $provider = new ShortCmProvider(
            'ABCD1234',
            'abc.de'
        );

        $this->setExpectedException(InvalidApiResponseException::class);

        $method = new \ReflectionMethod($provider, 'validate');
        $method->setAccessible(true);
        $method->invoke($provider, $response);
    }
}
