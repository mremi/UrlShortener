<?php

namespace Mremi\UrlShortener\Provider;

/**
 * Chain provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ChainProvider
{
    /**
     * @var array
     */
    private $providers = array();

    /**
     * Adds the given provider to the chain
     *
     * @param UrlShortenerProviderInterface $provider A provider instance
     */
    public function addProvider(UrlShortenerProviderInterface $provider)
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * Gets a provider by name
     *
     * @param string $name A provider name
     *
     * @return UrlShortenerProviderInterface
     *
     * @throws \RuntimeException If the provider does not exist
     */
    public function getProvider($name)
    {
        if (!array_key_exists($name, $this->providers)) {
            throw new \RuntimeException(sprintf('Unable to retrieve the provider named: "%s"', $name));
        }

        return $this->providers[$name];
    }

    /**
     * Gets the chain providers
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }
}
