<?php

namespace Mremi\UrlShortener\Bitly;

use Guzzle\Http\Client;

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

        $response = json_decode($request->send()->getBody(true));

        if (!$this->isOk($response)) {
            throw new \RuntimeException(sprintf('Bit.ly returned status code "%s" with message "%s"',
                $response->status_code,
                $response->status_txt
            ));
        }

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

        $response = json_decode($request->send()->getBody(true));

        if (!$this->isOk($response)) {
            throw new \RuntimeException(sprintf('Bit.ly returned status code "%s" with message "%s"',
                $response->status_code,
                $response->status_txt
            ));
        }

        return $response->data->expand[0]->long_url;
    }

    /**
     * Returns TRUE whether the status code of API response is 200
     *
     * @param object $apiResponse
     *
     * @return boolean
     */
    private function isOk($apiResponse)
    {
        return 200 === $apiResponse->status_code;
    }
}