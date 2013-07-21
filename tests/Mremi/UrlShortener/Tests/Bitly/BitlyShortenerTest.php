<?php

namespace Mremi\UrlShortener\Tests\Bitly;

use Mremi\UrlShortener\Bitly\BitlyShortener;

/**
 * Tests BitlyShortener class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyShortenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the shorten method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfStringFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseAsString()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid object
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testShortenThrowsExceptionIfInvalidObjectFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseAsInvalidObject()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testShortenThrowsExceptionIfInvalidStatusCodeFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method with a valid Bit.ly's response
     */
    public function testShortenWithValidResponseFromApi()
    {
        $response = $this->getMockResponse();

        $apiRawResponse = <<<JSON
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  },
  "status_code": 200,
  "status_txt": "OK"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $shortener = new BitlyShortener($this->getClientFactory($response), $this->getMockAuthentication());
        $this->assertEquals('http://bit.ly/ze6poY', $shortener->shorten('http://www.google.com/'));
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfStringFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseAsString()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid object
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testExpandThrowsExceptionIfInvalidObjectFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseAsInvalidObject()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testExpandThrowsExceptionIfInvalidStatusCodeFromApi()
    {
        $shortener = new BitlyShortener($this->getClientFactory($this->getResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method with a valid Bit.ly's response
     */
    public function testExpandWithValidResponseFromApi()
    {
        $response = $this->getMockResponse();

        $apiRawResponse = <<<JSON
{
  "data": {
    "expand": [
      {
        "global_hash": "900913",
        "long_url": "http://google.com/",
        "short_url": "http://bit.ly/ze6poY",
        "user_hash": "ze6poY"
      }
    ]
  },
  "status_code": 200,
  "status_txt": "OK"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $shortener = new BitlyShortener($this->getClientFactory($response), $this->getMockAuthentication());
        $this->assertEquals('http://google.com/', $shortener->expand('http://bit.ly/ze6poY'));
    }

    /**
     * Gets a mocked response
     *
     * @return object
     */
    private function getMockResponse()
    {
        return $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Returns an invalid response string
     *
     * @return object
     */
    private function getResponseAsString()
    {
        $response = $this->getMockResponse();

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('foo'));

        return $response;
    }

    /**
     * Returns an invalid response object
     *
     * @return object
     */
    private function getResponseAsInvalidObject()
    {
        $response = $this->getMockResponse();

        $apiRawResponse = <<<JSON
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  }
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Returns a response with an invalid status code
     *
     * @return object
     */
    private function getResponseWithInvalidStatusCode()
    {
        $response = $this->getMockResponse();

        $apiRawResponse = <<<JSON
{
  "data": {
    "global_hash": "900913",
    "hash": "ze6poY",
    "long_url": "http://google.com/",
    "new_hash": 0,
    "url": "http://bit.ly/ze6poY"
  },
  "status_code": 500,
  "status_txt": "KO"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Gets a client factory
     *
     * @param object $response
     *
     * @return object
     */
    private function getClientFactory($response)
    {
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\Http\ClientInterface');
        $client
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($request));

        $clientFactory = $this->getMock('Mremi\UrlShortener\Http\ClientFactoryInterface');
        $clientFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($client));

        return $clientFactory;
    }

    /**
     * Gets a mocked authentication
     *
     * @return object
     */
    private function getMockAuthentication()
    {
        return $this->getMock('Mremi\UrlShortener\Bitly\AuthenticationInterface');
    }
}
