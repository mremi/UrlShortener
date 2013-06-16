<?php

namespace Mremi\UrlShortener\Bitly;

use Guzzle\Http\Client;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\UrlShortenerInterface;

/**
 * Bit.ly shortener class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyShortener implements UrlShortenerInterface
{
    const BASE_URL = 'https://api-ssl.bitly.com';

    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * Constructor
     *
     * @param AuthenticationInterface $auth An authentication instance
     */
    public function __construct(AuthenticationInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $longUrl URL to shorten
     * @param string $domain  The domain to use, optional (bit.ly | j.mp | bitly.com)
     */
    public function shorten($longUrl, $domain = null)
    {
        $client = new Client(self::BASE_URL);

        $request = $client->get(sprintf('/v3/shorten?access_token=%s&longUrl=%s&domain=%s',
            $this->auth->getAccessToken(),
            urlencode($longUrl),
            $domain
        ));

        $response = $this->validate($request->send()->getBody(true));

        return $response->data->url;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $shortUrl URL to expand
     * @param string $hash     A Bit.ly hash
     */
    public function expand($shortUrl, $hash = null)
    {
        $client = new Client(self::BASE_URL);

        $request = $client->get(sprintf('/v3/expand?access_token=%s&shortUrl=%s&hash=%s',
            $this->auth->getAccessToken(),
            urlencode($shortUrl),
            $hash
        ));

        $response = $this->validate($request->send()->getBody(true));

        return $response->data->expand[0]->long_url;
    }

    /**
     * Validates the Bit.ly's response and returns it whether the status code is 200
     *
     * @param string $apiRawResponse
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Bit.ly response is probably mal-formed because cannot be json-decoded.');
        }

        if (!property_exists($response, 'status_code')) {
            throw new InvalidApiResponseException('Property "status_code" does not exist within Bit.ly response.');
        }

        if (200 !== $response->status_code) {
            throw new InvalidApiResponseException(sprintf('Bit.ly returned status code "%s" with message "%s"',
                $response->status_code,
                property_exists($response, 'status_txt') ? $response->status_txt : ''
            ));
        }

        return $response;
    }
}