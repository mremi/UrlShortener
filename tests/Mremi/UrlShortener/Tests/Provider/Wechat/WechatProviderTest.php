<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\Wechat;

use Mremi\UrlShortener\Provider\Wechat\WechatProvider;

/**
 * Tests WechatProvider class.
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class WechatProviderTest extends \PHPUnit_Framework_TestCase
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
        $auth = $this->getMock('Mremi\UrlShortener\Provider\Bitly\AuthenticationInterface');

        $this->provider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Wechat\WechatProvider')
            ->setConstructorArgs(array($auth))
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
     * Tests the shorten method throws exception if Wechat returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Wechat response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfApiResponseIsString()
    {
        $this->mockClient($this->getMockResponseAsString());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Wechat returns a response with no errcode.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "errcode" does not exist within Wechat response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoErrorCode()
    {
        $this->mockClient($this->getMockResponseAsInvalidObject());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Wechat returns an invalid status code.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Wechat returned status code "40001" with message "invalid credential, access_token is invalid or not latest hint: [miSs30226vr31!]"
     */
    public function testShortenThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $this->mockClient($this->getMockResponseWithInvalidStatusCode());

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method with a valid Wechat's response.
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "errcode": 0,
    "errmsg": "ok",
    "short_url": "https://w.url.cn/s/ATYfzFm"
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
            ->with($this->equalTo('https://w.url.cn/s/ATYfzFm'));

        $this->mockClient($response);

        $this->provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Wechat returns a string.
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Wechat does not support expand url yet.
     */
    public function testExpandThrowsExceptionIfApiResponseIsString()
    {
        //$this->mockClient($this->getMockResponseAsString());

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Gets mock of response.
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
     * Returns an invalid response object.
     *
     * @return object
     */
    private function getMockResponseAsInvalidObject()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<'JSON'
{
    "errmsg": "ok",
    "url": "https://w.url.cn/s/AK0wSn1"
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
    "errcode": 40001,
    "errmsg": "invalid credential, access_token is invalid or not latest hint: [miSs30226vr31!]"
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
        $client = $this->getMockBuilder('GuzzleHttp\ClientInterface')
            ->setMethods(array('send', 'sendAsync', 'request', 'requestAsync', 'getConfig', 'get', 'post'))
            ->getMock();
        $client
            ->expects($this->once())
            ->method('post')
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
            ->will($this->returnValue('https://w.url.cn/s/ATYfzFm'));

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
