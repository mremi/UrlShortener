<?php

namespace Mremi\UrlShortener\Tests\Provider\Google;

use Mremi\UrlShortener\Provider\Google\GoogleProvider;

/**
 * Tests GoogleProvider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class GoogleProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the getUri method with no API key and no parameters
     */
    public function testGetUriWithNoApiKeyAndNoParameters()
    {
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), $this->getMockLinkManager());

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider);

        $this->assertNull($uri);
    }

    /**
     * Tests the getUri method with no API key and some parameters
     */
    public function testGetUriWithNoApiKeyAndSomeParameters()
    {
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), $this->getMockLinkManager());

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider, array('foo' => 'bar'));

        $this->assertEquals('?foo=bar', $uri);
    }

    /**
     * Tests the getUri method with API key and no parameters
     */
    public function testGetUriWithApiKeyAndNoParameters()
    {
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), $this->getMockLinkManager(), 'secret');

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider);

        $this->assertEquals('?key=secret', $uri);
    }

    /**
     * Tests the getUri method with API key and some parameters
     */
    public function testGetUriWithApiKeyAndSomeParameters()
    {
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), $this->getMockLinkManager(), 'secret');

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider, array('foo' => 'bar'));

        $this->assertEquals('?foo=bar&key=secret', $uri);
    }

    /**
     * Tests the shorten method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfResponseApiIsString()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'post'), $this->getMockLinkManager());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'post'), $this->getMockLinkManager());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoId()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'post'), $this->getMockLinkManager());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'post'), $this->getMockLinkManager());
        $provider->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method with a valid Google's response
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/"
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
            ->with($this->equalTo('google'));
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://goo.gl/fbsS'));
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $linkManager = $this->getMockLinkManager();

        $linkManager
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($link));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'post'), $linkManager);

        $this->assertInstanceOf('Mremi\UrlShortener\Model\LinkInterface', $provider->shorten('http://www.google.com/'));
    }

    /**
     * Tests the expand method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoId()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no status
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatus()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "KO".
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/",
 "status": "KO"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'), $this->getMockLinkManager());
        $provider->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method with a valid Google's response
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/",
 "status": "OK"
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
            ->with($this->equalTo('google'));
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://goo.gl/fbsS'));
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $linkManager = $this->getMockLinkManager();

        $linkManager
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($link));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'), $linkManager);

        $this->assertInstanceOf('Mremi\UrlShortener\Model\LinkInterface', $provider->expand('http://goo.gl/fbsS'));
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
     * Returns a response object with "error" node
     *
     * @return object
     */
    private function getMockResponseWithError()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "error": {
  "errors": [
   {
    "domain": "global",
    "reason": "required",
    "message": "Required",
    "locationType": "parameter",
    "location": "resource.longUrl"
   }
  ],
  "code": 400,
  "message": "Required"
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
     * Returns a response object with no "id" node
     *
     * @return object
     */
    private function getMockResponseWithNoId()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "longUrl": "http://www.google.com/"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Returns a response object with no "longUrl" node
     *
     * @return object
     */
    private function getMockResponseWithNoLongUrl()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Gets a base mock of client factory
     *
     * @return object
     */
    private function getBaseMockClientFactory()
    {
        return $this->getMock('Mremi\UrlShortener\Http\ClientFactoryInterface');
    }

    /**
     * Gets mock of client factory
     *
     * @param object $response      A mocked response
     * @param string $requestMethod A request method (get|post)
     *
     * @return object
     */
    private function getMockClientFactory($response, $requestMethod)
    {
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\Http\ClientInterface');
        $client
            ->expects($this->once())
            ->method($requestMethod)
            ->will($this->returnValue($request));

        $clientFactory = $this->getBaseMockClientFactory();
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
}
