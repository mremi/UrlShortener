<?php

namespace Mremi\UrlShortener\Bitly;

/**
 * Authentication interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface AuthenticationInterface
{
    /**
     * Calls Bit.ly API to get an access token
     *
     * @return string
     */
    function getAccessToken();
}