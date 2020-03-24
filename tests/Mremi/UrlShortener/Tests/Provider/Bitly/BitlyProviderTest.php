<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\Bitly;

use GuzzleHttp\ClientInterface;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\Bitly\AuthenticationInterface;
use Mremi\UrlShortener\Provider\Bitly\BitlyProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests BitlyProvider class.
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyProviderTest extends TestCase
{
    /**
     * @var object
     */
    private $provider;

    /**
     * Tests the shorten method throws exception if Bit.ly returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfApiResponseIsString()
    {
        $this->mockClient($this->getMockResponseAsString());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns a response with no status_code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $this->mockClient($this->getMockResponseAsInvalidObject());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid status code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testShortenThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $this->mockClient($this->getMockResponseWithInvalidStatusCode());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method with a valid Bit.ly's response.
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://www.google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  },
  "status_code": 200,
  "status_txt": "OK"
}
JSON;

        $stream = $this->getBaseMockStream();
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($apiRawResponse));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($stream));

        $link = $this->getMockLongLink();
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://bit.ly/ze6poY'));

        $this->mockClient($response);

        $this->provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfApiResponseIsString()
    {
        $this->mockClient($this->getMockResponseAsString());

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a response with no status_code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $this->mockClient($this->getMockResponseAsInvalidObject());

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid status code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $this->mockClient($this->getMockResponseWithInvalidStatusCode());

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method with a valid Bit.ly's response.
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
  "data": {
    "expand": [
      {
        "global_hash": "900913",
        "long_url": "http://www.google.com/",
        "short_url": "http://bit.ly/ze6poY",
        "user_hash": "ze6poY"
      }
    ]
  },
  "status_code": 200,
  "status_txt": "OK"
}
JSON;

        $stream = $this->getBaseMockStream();
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($apiRawResponse));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($stream));

        $link = $this->getMockShortLink();
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $this->mockClient($response);

        $this->provider->expand($link);
    }

    /**
     * Initializes the provider.
     */
    protected function setUp()
    {
        $auth = $this->createMock(AuthenticationInterface::class);

        $this->provider = $this->getMockBuilder(BitlyProvider::class)
            ->setConstructorArgs([$auth])
            ->setMethods(['createClient'])
            ->getMock();
    }

    /**
     * Cleanups the provider.
     */
    protected function tearDown()
    {
        $this->provider = null;
    }

    /**
     * Gets mock of response.
     *
     * @return object
     */
    private function getBaseMockResponse()
    {
        return $this->createMock(ResponseInterface::class);
    }

    /**
     * Gets mock of stream.
     *
     * @return object
     */
    private function getBaseMockStream()
    {
        return $this->createMock(StreamInterface::class);
    }

    /**
     * Returns an invalid response string.
     *
     * @return object
     */
    private function getMockResponseAsString()
    {
        $stream = $this->getBaseMockStream();
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue('foo'));

        $response = $this->getBaseMockResponse();

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($stream));

        return $response;
    }

    /**
     * Returns an invalid response object.
     *
     * @return object
     */
    private function getMockResponseAsInvalidObject()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://www.google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  }
}
JSON;

        $stream = $this->getBaseMockStream();
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($apiRawResponse));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($stream));

        return $response;
    }

    /**
     * Returns a response with an invalid status code.
     *
     * @return object
     */
    private function getMockResponseWithInvalidStatusCode()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://www.google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  },
  "status_code": 500,
  "status_txt": "KO"
}
JSON;

        $stream = $this->getBaseMockStream();
        $stream
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue($apiRawResponse));

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($stream));

        return $response;
    }

    /**
     * Mocks the client.
     *
     * @param object $response
     */
    private function mockClient($response)
    {
        $client = $this->getMockBuilder(ClientInterface::class)
            ->setMethods(['send', 'sendAsync', 'request', 'requestAsync', 'getConfig', 'get', 'post'])
            ->getMock();
        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($response));

        $this->provider
            ->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($client));
    }

    /**
     * Gets mock of link.
     *
     * @return object
     */
    private function getBaseMockLink()
    {
        return $this->createMock(LinkInterface::class);
    }

    /**
     * Gets mock of short link.
     *
     * @return object
     */
    private function getMockShortLink()
    {
        $link = $this->getBaseMockLink();

        $link
            ->expects($this->once())
            ->method('getShortUrl')
            ->will($this->returnValue('http://bit.ly/ze6poY'));

        return $link;
    }

    /**
     * Gets mock of long link.
     *
     * @return object
     */
    private function getMockLongLink()
    {
        $link = $this->getBaseMockLink();

        $link
            ->expects($this->once())
            ->method('getLongUrl')
            ->will($this->returnValue('http://www.google.com/'));

        return $link;
    }
}
