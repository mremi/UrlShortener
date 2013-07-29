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
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testShortenThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->shorten('http://www.google.com/');
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

        $link = $this->getMock('Mremi\UrlShortener\Model\LinkInterface');
        $link
            ->expects($this->once())
            ->method('setProviderName')
            ->with($this->equalTo('bitly'));
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://bit.ly/ze6poY'));
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $linkManager = $this->getMockLinkManager();

        $linkManager
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($link));

        $provider = new BitlyProvider($this->getMockClientFactory($response), $linkManager, $this->getMockAuthentication());

        $this->assertInstanceOf('Mremi\UrlShortener\Model\LinkInterface', $provider->shorten('http://www.google.com/'));
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfApiResponseIsString()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsString()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns a response with no status_code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status_code" does not exist within Bit.ly response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseAsInvalidObject()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->expand('http://bit.ly/ze6poY');
    }

    /**
     * Tests the expand method throws exception if Bit.ly returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Bit.ly returned status code "500" with message "KO"
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $provider = new BitlyProvider($this->getMockClientFactory($this->getMockResponseWithInvalidStatusCode()), $this->getMockLinkManager(), $this->getMockAuthentication());
        $provider->expand('http://bit.ly/ze6poY');
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

        $link = $this->getMock('Mremi\UrlShortener\Model\LinkInterface');
        $link
            ->expects($this->once())
            ->method('setProviderName')
            ->with($this->equalTo('bitly'));
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://bit.ly/ze6poY'));
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $linkManager = $this->getMockLinkManager();

        $linkManager
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($link));

        $provider = new BitlyProvider($this->getMockClientFactory($response), $linkManager, $this->getMockAuthentication());

        $this->assertInstanceOf('Mremi\UrlShortener\Model\LinkInterface', $provider->expand('http://bit.ly/ze6poY'));
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
     * Gets mock of link manager
     *
     * @return object
     */
    private function getMockLinkManager()
    {
        return $this->getMock('Mremi\UrlShortener\Model\LinkManagerInterface');
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
}
