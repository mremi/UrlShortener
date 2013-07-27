<?php

namespace Mremi\UrlShortener\Tests\Provider;

use Mremi\UrlShortener\Provider\ChainProvider;

/**
 * Tests ChainProvider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ChainProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests to add and get some providers
     */
    public function testAddAndGetProviders()
    {
        $chainProvider = new ChainProvider;

        $bitlyProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Bitly\BitlyProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $bitlyProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bitly'));

        $chainProvider->addProvider($bitlyProvider);

        $this->assertEquals($bitlyProvider, $chainProvider->getProvider('bitly'));
        $this->assertArrayHasKey('bitly', $chainProvider->getProviders());
        $this->assertCount(1, $chainProvider->getProviders());

        $googleProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Google\GoogleProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $googleProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('google'));

        $chainProvider->addProvider($googleProvider);

        $this->assertEquals($googleProvider, $chainProvider->getProvider('google'));
        $this->assertArrayHasKey('google', $chainProvider->getProviders());
        $this->assertCount(2, $chainProvider->getProviders());
    }
}
