<?php

namespace Mremi\UrlShortener\Model;

/**
 * Link manager interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface LinkManagerInterface
{
    /**
     * Creates and returns a new link instance
     *
     * @return LinkInterface
     */
    public function create();

    /**
     * Finds one link by a provider and a short URL
     *
     * @param string $providerName A provider name
     * @param string $shortUrl     A short URL
     *
     * @return LinkInterface
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    public function findOneByProviderAndShortUrl($providerName, $shortUrl);

    /**
     * Finds one link by a provider and a long URL
     *
     * @param string $providerName A provider name
     * @param string $longUrl      A long URL
     *
     * @return LinkInterface
     *
     * @throws \Mremi\UrlShortener\Exception\InvalidApiResponseException
     */
    public function findOneByProviderAndLongUrl($providerName, $longUrl);
}
