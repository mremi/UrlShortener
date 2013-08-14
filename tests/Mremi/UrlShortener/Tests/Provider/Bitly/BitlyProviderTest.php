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
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockAuthentication());
        $provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockAuthentication());
        $provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testShortenThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $provider->shorten($this->getBaseMockLink());
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

        $link = $this->getMockLongLink();
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://bit.ly/ze6poY'));

        $provider = new BitlyProvider($this->getMockClientFactory($response), $this->getMockAuthentication());

        $provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfApiResponseIsString()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockAuthentication());
        $provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockAuthentication());
        $provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockAuthentication());
        $provider->expand($this->getBaseMockLink());
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

        $link = $this->getMockShortLink();
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $provider = new BitlyProvider($this->getMockClientFactory($response), $this->getMockAuthentication());

        $provider->expand($link);
    }

    /**
     * Gets mock of response
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
     * Gets mock of client factory
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
     * Gets mock of authentication
     *
     * @return object
     */
    private function getMockAuthentication()
    {
        return $this->getMock('Mremi\UrlShortener\Provider\Bitly\AuthenticationInterface');
    }

    /**
     * Gets mock of link
     *
     * @return object
     */
    private function getBaseMockLink()
    {
        return $this->getMock('Mremi\UrlShortener\Model\LinkInterface');
    }

    /**
     * Gets mock of short link
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
     * Gets mock of long link
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
