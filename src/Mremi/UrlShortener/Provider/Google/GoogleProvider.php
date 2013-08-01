<?php

namespace Mremi\UrlShortener\Provider\Google;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Http\ClientFactoryInterface;
use Mremi\UrlShortener\Model\LinkManagerInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Google provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class GoogleProvider implements UrlShortenerProviderInterface
{
    const API_URL = 'https://www.googleapis.com/urlshortener/v1/url';

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var LinkManagerInterface
     */
    private $linkManager;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param ClientFactoryInterface $clientFactory A client factory instance
     * @param LinkManagerInterface   $linkManager   A link manager instance
     * @param string                 $apiKey        A Google API key, optional
     * @param array                  $options       An array of options used to do the shorten/expand request
     */
    public function __construct(ClientFactoryInterface $clientFactory, LinkManagerInterface $linkManager, $apiKey = null, array $options = array())
    {
        $this->clientFactory = $clientFactory;
        $this->linkManager   = $linkManager;
        $this->apiKey        = $apiKey;
        $this->options       = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    public function shorten($longUrl)
    {
        $client = $this->clientFactory->create(self::API_URL);

        $request = $client->post($this->getUri(), array(
            'Content-Type' => 'application/json'
        ), json_encode(array(
            'longUrl' => $longUrl,
        )), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link = $this->linkManager->create();
        $link->setProviderName($this->getName());
        $link->setShortUrl($response->id);
        $link->setLongUrl($longUrl);

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function expand($shortUrl)
    {
        $client = $this->clientFactory->create(self::API_URL);

        $request = $client->get($this->getUri(array(
            'shortUrl' => $shortUrl,
        )), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true), true);

        $link = $this->linkManager->create();
        $link->setProviderName($this->getName());
        $link->setShortUrl($shortUrl);
        $link->setLongUrl($response->longUrl);

        return $link;
    }

    /**
     * Gets the URI
     *
     * @param array $parameters An array of parameters, optional
     *
     * @return null|string
     */
    private function getUri(array $parameters = array())
    {
        if ($this->apiKey) {
            $parameters = array_merge($parameters, array('key' => $this->apiKey));
        }

        if (0 === count($parameters)) {
            return null;
        }

        return sprintf('?%s', http_build_query($parameters));
    }

    /**
     * Validates the Google's response and returns it whether the status code is 200
     *
     * @param string  $apiRawResponse An API response, as it returned
     * @param boolean $checkStatus    TRUE whether the status code has to be checked, default FALSE
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse, $checkStatus = false)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Google response is probably mal-formed because cannot be json-decoded.');
        }

        if (property_exists($response, 'error')) {
            throw new InvalidApiResponseException(sprintf('Google returned status code "%s" with message "%s".',
                property_exists($response->error, 'code') ? $response->error->code : '',
                property_exists($response->error, 'message') ? $response->error->message : ''
            ));
        }

        if (!property_exists($response, 'id')) {
            throw new InvalidApiResponseException('Property "id" does not exist within Google response.');
        }

        if (!property_exists($response, 'longUrl')) {
            throw new InvalidApiResponseException('Property "longUrl" does not exist within Google response.');
        }

        if (!$checkStatus) {
            return $response;
        }

        if (!property_exists($response, 'status')) {
            throw new InvalidApiResponseException('Property "status" does not exist within Google response.');
        }

        if ('OK' !== $response->status) {
            throw new InvalidApiResponseException(sprintf('Google returned status code "%s".', $response->status));
        }

        return $response;
    }
}
