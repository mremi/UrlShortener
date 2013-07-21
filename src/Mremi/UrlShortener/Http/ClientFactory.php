<?php

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
