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
