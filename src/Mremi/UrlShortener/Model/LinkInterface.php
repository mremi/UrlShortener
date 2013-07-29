<?php

namespace Mremi\UrlShortener\Model;

/**
 * Class LinkInterface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface LinkInterface
{
    /**
     * Sets the created at
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Gets the created at
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the long URL
     *
     * @param string $longUrl
     */
    public function setLongUrl($longUrl);

    /**
     * Gets the long URL
     *
     * @return string
     */
    public function getLongUrl();

    /**
     * Sets the provider name
     *
     * @param string $providerName
     */
    public function setProviderName($providerName);

    /**
     * Gets the provider name
     *
     * @return string
     */
    public function getProviderName();

    /**
     * Sets the short URL
     *
     * @param string $shortUrl
     */
    public function setShortUrl($shortUrl);

    /**
     * Gets the short URL
     *
     * @return string
     */
    public function getShortUrl();
}
