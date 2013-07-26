<?php

namespace Mremi\UrlShortener\Provider\Google;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Http\ClientFactoryInterface;
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
     * @var string
     */
    private $apiKey;

    /**
     * Constructor
     *
     * @param ClientFactoryInterface $clientFactory A client factory instance
     * @param string                 $apiKey        A Google API key, optional
     */
    public function __construct(ClientFactoryInterface $clientFactory, $apiKey = null)
    {
        $this->clientFactory = $clientFactory;
        $this->apiKey        = $apiKey;
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
        )));

        $response = $this->validate($request->send()->getBody(true));

        return $response->id;
    }

    /**
     * {@inheritdoc}
     */
    public function expand($shortUrl)
    {
        $client = $this->clientFactory->create(self::API_URL);

        $request = $client->get($this->getUri(array(
            'shortUrl' => $shortUrl,
        )));

        $response = $this->validate($request->send()->getBody(true), true);

        return $response->longUrl;
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
