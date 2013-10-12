<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $provider = new GoogleProvider($this->getBaseMockClientFactory());

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
        $provider = new GoogleProvider($this->getBaseMockClientFactory());

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
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), 'secret');

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
        $provider = new GoogleProvider($this->getBaseMockClientFactory(), 'secret');

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
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'post'));
        $provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'post'));
        $provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoId()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'post'));
        $provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'post'));
        $provider->shorten($this->getBaseMockLink());
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

        $link = $this->getMockLongLink();
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://goo.gl/fbsS'));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'post'));

        $provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseAsString(), 'get'));
        $provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithError(), 'get'));
        $provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoId()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoId(), 'get'));
        $provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $provider = new GoogleProvider($this->getMockClientFactory($this->getMockResponseWithNoLongUrl(), 'get'));
        $provider->expand($this->getBaseMockLink());
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

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'));
        $provider->expand($this->getBaseMockLink());
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

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'));
        $provider->expand($this->getBaseMockLink());
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

        $link = $this->getMockShortLink();
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $provider = new GoogleProvider($this->getMockClientFactory($response, 'get'));

        $provider->expand($link);
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
            ->will($this->returnValue('http://goo.gl/fbsS'));

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
