<?php

namespace Mremi\UrlShortener;

/**
 * Url shortener interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface UrlShortenerInterface
{
    /**
     * Shorten the long given URL
     *
     * @param string $longUrl URL to shorten
     *
     * @return string
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    function shorten($longUrl);

    /**
     * Expands the short given URL
     *
     * @param string $shortUrl URL to expand
     *
     * @return string
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    function expand($shortUrl);
}