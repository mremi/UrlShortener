<?php

namespace Mremi\UrlShortener\Tests\Provider\Bitly;

use Mremi\UrlShortener\Provider\Bitly\BitlyProvider;

/**
 * Tests BitlyProvider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the shorten method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfApiResponseIsString()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testShortenThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method with a valid Bit.ly's response
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
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

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $shortener = new BitlyProvider($this->getMockClientFactory($response), $this->getMockAuthentication());
        $this->assertEquals('http://bit.ly/ze6poY', $shortener->shorten('http://www.google.com/'));
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfApiResponseIsString()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $shortener = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $shortener->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method with a valid Bit.ly's response
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
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

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $shortener = new BitlyProvider($this->getMockClientFactory($response), $this->getMockAuthentication());
        $this->assertEquals('http://www.google.com/', $shortener->expand('http://bit.ly/ze6poY'));
    }

    /**
     * Gets a mocked response
     *
     * @return object
     */
    private function getBaseMockResponse()
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
    private function getMockResponseAsString()
    {
        $response = $this->getBaseMockResponse();

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
    private function getMockResponseAsInvalidObject()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
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
    private function getMockResponseWithInvalidStatusCode()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
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
    private function getMockClientFactory($response)
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
        return $this->getMock('Mremi\UrlShortener\Provider\Bitly\AuthenticationInterface');
    }
}
