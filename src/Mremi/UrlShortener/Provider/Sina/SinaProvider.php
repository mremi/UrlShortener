<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) zacksleo <zacksleo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\Sina;

use GuzzleHttp\Client;
use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Sina provider class.
 *
 * @author zacksleo <zacksleo@gmail.com>
 */
class SinaProvider implements UrlShortenerProviderInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * Constructor.
     *
     * @param string $apiKey  A Sina API key, optional
     * @param array  $options An array of options used to do the shorten/expand request
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sina';
    }

    /**
     * {@inheritdoc}
     */
    public function shorten(LinkInterface $link)
    {
        $client = $this->createClient();

        $response = $client->get('shorten.json', [
            'query' => [
                'source' => $this->apiKey,
                'url_long' => $link->getLongUrl(),
            ]
        ]);

        $response = $this->validate($response->getBody()->getContents());

        $link->setShortUrl($response->urls[0]->url_short);
    }

    /**
     * {@inheritdoc}
     */
    public function expand(LinkInterface $link)
    {
        $client = $this->createClient();

        $response = $client->get('expand.json', [
            'query' => [
                'source' => $this->apiKey,
                'url_short' => $link->getShortUrl()
            ]
        ]);

        $response = $this->validate($response->getBody()->getContents());

        $link->setLongUrl($response->urls[0]->url_long);
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
            'base_uri' => 'https://api.weibo.com/2/short_url/',
        ));
    }

    /**
     * Validates the Google's response and returns it whether the status code is 200.
     *
     * @param string $apiRawResponse An API response, as it returned
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Baidu response is probably mal-formed because cannot be json-decoded.');
        }

        if (property_exists($response, 'error')) {
            throw new InvalidApiResponseException(sprintf(
                'Sina returned status code "%s" with message "%s".',
                property_exists($response, 'error_code') ? $response->error_code : '',
                property_exists($response, 'error') ? $response->error : ''
            ));
        }
        return $response;
    }
}
