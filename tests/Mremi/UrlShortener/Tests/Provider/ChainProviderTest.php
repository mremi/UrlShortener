<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider;

use Mremi\UrlShortener\Provider\ChainProvider;

/**
 * Tests ChainProvider class.
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ChainProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that an unknown provider throws an exception.
     *
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Unable to retrieve the provider named: "foo"
     */
    public function testUnknownProvider()
    {
        $chainProvider = new ChainProvider();
        $chainProvider->getProvider('foo');
    }

    /**
     * Tests to add and get some providers.
     */
    public function testAddAndGetProviders()
    {
        $chainProvider = new ChainProvider();

        $bitlyProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Bitly\BitlyProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $bitlyProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bitly'));

        $chainProvider->addProvider($bitlyProvider);

        $this->assertSame($bitlyProvider, $chainProvider->getProvider('bitly'));
        $this->assertArrayHasKey('bitly', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('bitly'));
        $this->assertCount(1, $chainProvider->getProviders());

        $googleProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Google\GoogleProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $googleProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('google'));

        $chainProvider->addProvider($googleProvider);

        $this->assertSame($googleProvider, $chainProvider->getProvider('google'));
        $this->assertArrayHasKey('google', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('google'));
        $this->assertCount(2, $chainProvider->getProviders());

        $baiduProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Baidu\BaiduProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $baiduProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('baidu'));

        $chainProvider->addProvider($baiduProvider);

        $this->assertSame($baiduProvider, $chainProvider->getProvider('baidu'));
        $this->assertArrayHasKey('baidu', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('baidu'));
        $this->assertCount(3, $chainProvider->getProviders());

        $sinaProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Sina\SinaProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $sinaProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('sina'));

        $chainProvider->addProvider($sinaProvider);

        $this->assertSame($sinaProvider, $chainProvider->getProvider('sina'));
        $this->assertArrayHasKey('sina', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('sina'));
        $this->assertCount(4, $chainProvider->getProviders());

        $wechatProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Wechat\WechatProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $wechatProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('wechat'));

        $chainProvider->addProvider($wechatProvider);

        $this->assertSame($wechatProvider, $chainProvider->getProvider('wechat'));
        $this->assertArrayHasKey('wechat', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('google'));
        $this->assertCount(5, $chainProvider->getProviders());
    }
}
