<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\Sina;

use Mremi\UrlShortener\Provider\Sina\SinaProvider;

/**
 * Tests SinaProvider class.
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class SinaProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    private $provider;

    /**
     * Initializes the provider.
     */
    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Sina\SinaProvider')
            ->setMethods(array('createClient'))
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
     * Tests the shorten method throws exception if Sina returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'get');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Sina returns an error response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina returned status code "10006" with message "source paramter(appkey) is missing
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'get');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Sina returns a response with no id.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina returned status code "10006" with message "source paramter(appkey) is missing
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoId()
    {
        $this->mockClient($this->getMockResponseWithNoId(), 'get');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Sina returns a response with no longUrl.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Sina response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $this->mockClient($this->getMockResponseWithNoLongUrl(), 'get');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method with a valid Sina's response.
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "urls": [
        {
            "result": true,
            "url_short": "http://t.cn/h5ef4",
            "url_long": "http://www.google.com/",
            "object_type": "webpage",
            "type": 39,
            "object_id": "3000001411:ff90821feeb2b02a33a6f9fc8e5f3fcd"
        }
    ]
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
            ->with($this->equalTo('http://t.cn/h5ef4'));

        $this->mockClient($response, 'get');

        $this->provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Sina returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Sina returns an error response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina returned status code "10006" with message "source paramter(appkey) is missing"
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Sina returns a response with no id.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina returned status code "10006" with message "source paramter(appkey) is missing"
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoId()
    {
        $this->mockClient($this->getMockResponseWithNoId(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Sina returns a response with no longUrl.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Sina response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $this->mockClient($this->getMockResponseWithNoLongUrl(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Sina returns an invalid status code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Sina returned status code "21506" with message "Error: Parameter value is not valid!
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
"error": "Error: Parameter value is not valid!",
"error_code": 21506,
"request": "/2/sinaurl/public/expand.json"
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

        $this->mockClient($response, 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method with a valid Sina's response.
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "urls": [
        {
            "result": true,
            "url_short": "http://t.cn/h5ef4",
            "url_long": "http://www.google.com/",
            "transcode": 0,
            "type": 39
        }
    ]
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

        $this->mockClient($response, 'get');

        $this->provider->expand($link);
    }

    /**
     * Gets a mocked response.
     *
     * @return object
     */
    private function getBaseMockResponse()
    {
        return $this->getMock('Psr\Http\Message\ResponseInterface');
    }

    /**
     * Gets mock of stream.
     *
     * @return object
     */
    private function getBaseMockStream()
    {
        return $this->getMock('Psr\Http\Message\StreamInterface');
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
     * Returns a response object with "error" node.
     *
     * @return object
     */
    private function getMockResponseWithError()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "error": "source paramter(appkey) is missing",
    "error_code": 10006,
    "request": "/2/short_url/expand.json"
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
     * Returns a response object with no "id" node.
     *
     * @return object
     */
    private function getMockResponseWithNoId()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
"error": "source paramter(appkey) is missing",
"error_code": 10006,
"request": "/2/short_url/shorten.json"
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
     * Returns a response object with no "longUrl" node.
     *
     * @return object
     */
    private function getMockResponseWithNoLongUrl()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "urls": [
        {
            "result": true,
            "url_short": "http://t.cn/RkXgjKTd",
            "url_long": "",
            "transcode": 0,
            "type": 0
        }
    ]
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
     * @param object $response      A mocked response
     * @param string $requestMethod A request method (get|post)
     */
    private function mockClient($response, $requestMethod)
    {
        $client = $this->getMockBuilder('GuzzleHttp\ClientInterface')
            ->setMethods(array('send', 'sendAsync', 'request', 'requestAsync', 'getConfig', 'get', 'post'))
            ->getMock();
        $client
            ->expects($this->once())
            ->method($requestMethod)
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
        return $this->getMock('Mremi\UrlShortener\Model\LinkInterface');
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
            ->will($this->returnValue('http://t.cn/h5ef4'));

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
