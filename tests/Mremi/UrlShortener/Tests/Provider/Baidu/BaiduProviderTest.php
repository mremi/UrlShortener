<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\Baidu;

use Mremi\UrlShortener\Provider\Baidu\BaiduProvider;

/**
 * Tests BaiduProvider class.
 *
 * @author zacksleo <zacksleo@gmail.com>
 */
class BaiduProviderTest extends \PHPUnit_Framework_TestCase
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
        $this->provider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Baidu\BaiduProvider')
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
     * Tests the shorten method throws exception if Baidu returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Baidu returns an error response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu returned status code "-1: long url is not valid
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Baidu returns a response with no short url.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu returned status code "-1: unsafe url
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoShortUrl()
    {
        $this->mockClient($this->getMockResponseWithNoShortUrl(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method with a valid Baidu's response.
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
 "status": 0,
 "tinyurl": "http://dwz.cn/OErDnjcx",
 "longUrl": "http://www.google.com/",
 "err_msg": ""
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
            ->with($this->equalTo('http://dwz.cn/OErDnjcx'));

        $this->mockClient($response, 'post');

        $this->provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Baidu returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'post');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Baidu returns an error response.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu returned status code "-1: long url is not valid
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'post');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Baidu returns a response with no longUrl.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Baidu returned status code "-2: short url dose not exist
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $this->mockClient($this->getMockResponseWithNoLongUrl(), 'post');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Baidu returns a response with no status.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status" does not exist within Baidu response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatus()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
"tinyurl": "http://dwz.cn/OErDnjcx",
"longurl": "http://www.google.com/",
"err_msg": ""
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

        $this->mockClient($response, 'post');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method with a valid Baidu's response.
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
"status": 0,
"tinyurl": "http://dwz.cn/OErDnjcx",
"longurl": "http://www.google.com/",
"err_msg": ""
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

        $this->mockClient($response, 'post');

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
"status": -1,
"tinyurl": "http://www.google.com/",
"longurl": ":",
"err_msg": "long url is not valid"
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
    private function getMockResponseWithNoShortUrl()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
 "status": -1,
 "longUrl": "http://www.google.com/",
 "err_msg": "unsafe url"
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
"status": -2,
"tinyurl": "http://dwz.cn/OErDnjcxd",
"longurl": "",
"err_msg": "short url dose not exist"
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
            ->will($this->returnValue('http://dwz.cn/OErDnjcx'));

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
