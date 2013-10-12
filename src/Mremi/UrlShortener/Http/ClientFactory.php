<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Http;

use Guzzle\Http\Client;

/**
 * Client factory class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create($baseUrl = '', $config = null)
    {
        return new Client($baseUrl, $config);
    }
}
