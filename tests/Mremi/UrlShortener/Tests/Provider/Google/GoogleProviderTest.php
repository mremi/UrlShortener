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
        $shortener = new GoogleProvider($this->getBaseMockClientFactory());

        $method = new \ReflectionMethod($shortener, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($shortener);

        $this->assertNull($uri);
    }

    /**
     * Tests the getUri method with no API key and some parameters
     */
    public function testGetUriWithNoApiKeyAndSomeParameters()
    {
        $shortener = new GoogleProvider($this->getBaseMockClientFactory());

        $method = new \ReflectionMethod($shortener, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($shortener, array('foo' => 'bar'));

        $this->assertEquals('?foo=bar', $uri);
    }

    /**
     * Tests the getUri method with API key and no parameters
     */
    public function testGetUriWithApiKeyAndNoParameters()
    {
        $shortener = new GoogleProvider($this->getBaseMockClientFactory(), 'secret');

        $method = new \ReflectionMethod($shortener, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($shortener);

        $this->assertEquals('?key=secret', $uri);
    }

    /**
     * Tests the getUri method with API key and some parameters
     */
    public function testGetUriWithApiKeyAndSomeParameters()
    {
        $shortener = new GoogleProvider($this->getBaseMockClientFactory(), 'secret');

        $method = new \ReflectionMethod($shortener, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($shortener, array('foo' => 'bar'));

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
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'post'));
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'post'));
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoId()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'post'));
        $shortener->shorten('http://www.google.com/');
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'post'));
        $shortener->shorten('http://www.google.com/');
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

        $shortener = new GoogleProvider($this->getMockClientFactory($response, 'post'));
        $this->assertEquals('http://goo.gl/fbsS', $shortener->shorten('http://www.google.com/'));
    }

    /**
     * Tests the expand method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'get'));
        $shortener->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'get'));
        $shortener->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoId()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'get'));
        $shortener->expand('http://goo.gl/fbsS');
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $shortener = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'get'));
        $shortener->expand('http://goo.gl/fbsS');
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

        $shortener = new GoogleProvider($this->getMockClientFactory($response, 'get'));
        $shortener->expand('http://goo.gl/fbsS');
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

        $shortener = new GoogleProvider($this->getMockClientFactory($response, 'get'));
        $shortener->expand('http://goo.gl/fbsS');
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

        $shortener = new GoogleProvider($this->getMockClientFactory($response, 'get'));
        $this->assertEquals('http://www.google.com/', $shortener->expand('http://goo.gl/fbsS'));
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
     * Gets a client factory
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
}
