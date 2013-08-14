<?php

namespace Mremi\UrlShortener\Provider;

use Mremi\UrlShortener\Model\LinkInterface;

/**
 * Url shortener provider interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface UrlShortenerProviderInterface
{
    /**
     * Gets the provider name
     *
     * @return string
     */
    public function getName();

    /**
     * Shortens the long given URL
     *
     * @param LinkInterface $link A link instance
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    public function shorten(LinkInterface $link);

    /**
     * Expands the short given URL
     *
     * @param LinkInterface $link A link instance
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    public function expand(LinkInterface $link);
}
