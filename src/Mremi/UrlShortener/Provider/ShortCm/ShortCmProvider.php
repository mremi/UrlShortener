<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\ShortCm;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Short.cm provider class.
 */
class ShortCmProvider implements UrlShortenerProviderInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $domain;

    /**
     * Constructor.
     *
     * @param string $apiKey An API key
     * @param string $domain Domain name you added to short.cm
     */
    public function __construct($apiKey, $domain)
    {
        $this->apiKey  = $apiKey;
        $this->domain  = $domain;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'shortcm';
    }

    /**
     * {@inheritdoc}
     */
    public function shorten(LinkInterface $link)
    {
        $client = $this->createClient();

        $data = array(
            'domain'      => $this->domain,
            'originalURL' => $link->getLongUrl(),
        );

        $response = $client->post('/links', array(
            'json' => $data,
        ));

        $this->validate($response);

        $body = json_decode($response->getBody()->__toString(), true);

        $url = !empty($body['secureShortURL'])
            ? $body['secureShortURL']
            : !empty($body['shortURL'])
                ? $body['shortURL']
                : null;

        $link->setShortUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function expand(LinkInterface $link)
    {
        $client = $this->createClient();

        $parsed = parse_url($link->getShortUrl());
        $path = preg_replace('@^/(.*)$@', '$1', $parsed['path']);

        $data = array(
            'domain' => $this->domain,
            'path'   => $path,
        );

        $response = $client->get('/links/expand', array(
            'query' => $data,
        ));

        $this->validateResponseExpand($response);

        $body = json_decode($response->getBody()->__toString(), true);

        $url = !empty($body['originalURL'])
            ? $body['originalURL']
            : null;

        $link->setLongUrl($url);
    }

    /**
     * Creates a client.
     *
     * This method is mocked in unit tests in order to not make a real request,
     * so visibility must be protected or public.
     *
     * @return Client
     */
    protected function createClient()
    {
        return new Client(array(
            'base_uri' => 'https://api.short.cm',
            'headers'  => array(
                'Authorization' => $this->apiKey,
            ),
        ));
    }

    /**
     * Validates the API response.
     *
     * @param Response $response API response
     *
     * @throws InvalidApiResponseException
     */
    private function validate(Response $response)
    {
        if ($response->getStatusCode() !== 200) {
            throw new InvalidApiResponseException('Short.cm API returned unexpected status code '.$response->getStatusCode());
        }

        $body = $response->getBody()->__toString();

        if (empty($body)) {
            throw new InvalidApiResponseException('Short.cm API returned an empty body');
        }

        $body = json_decode($body, true);

        if (empty($body)) {
            throw new InvalidApiResponseException('Short.cm API response body isnt valid JSON');
        }

        if (empty($body['secureShortURL']) && empty($body['shortURL'])) {
            throw new InvalidApiResponseException('Short.cm API could not generate a short URL');
        }
    }

    /**
     * Validates the API response for an Expand request.
     *
     * @param Response $response API response
     *
     * @throws InvalidApiResponseException
     */
    private function validateResponseExpand(Response $response)
    {
        if ($response->getStatusCode() !== 200) {
            throw new InvalidApiResponseException('Short.cm API returned unexpected status code '.$response->getStatusCode());
        }

        $body = $response->getBody()->__toString();

        if (empty($body)) {
            throw new InvalidApiResponseException('Short.cm API returned an empty body');
        }

        $body = json_decode($body, true);

        if (empty($body)) {
            throw new InvalidApiResponseException('Short.cm API response body isnt valid JSON');
        }

        if (empty($body['originalURL'])) {
            throw new InvalidApiResponseException('Short.cm API could not expand this URL');
        }
    }
}
