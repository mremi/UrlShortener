<?php

namespace Mremi\UrlShortener\Tests\Model;

use Mremi\UrlShortener\Model\Link;

/**
 * Tests Link class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the createdAt property
     */
    public function testCreatedAt()
    {
        $link = new Link;

        $this->assertInstanceOf('\DateTime', $link->getCreatedAt());
    }
}
