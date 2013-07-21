<?php

namespace Mremi\UrlShortener\Http;

/**
 * Client factory interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface ClientFactoryInterface
{
    /**
     * Creates a HTTP client
     *
     * @param string $baseUrl A base URL of the web service
     * @param mixed  $config  Configuration settings
     *
     * @return \Guzzle\Http\ClientInterface
     */
    public function create($baseUrl = '', $config = null);
}
