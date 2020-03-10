<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\Bitly;

use GuzzleHttp\Client;
use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Bit.ly provider class.
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyProvider implements UrlShortenerProviderInterface
{
    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param AuthenticationInterface $auth    An authentication instance
     * @param array                   $options An array of options used to do the shorten/expand request
     */
    public function __construct(AuthenticationInterface $auth, array $options = [])
    {
        $this->auth    = $auth;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bitly';
    }

    /**
     * {@inheritdoc}
     *
     * @param LinkInterface $link   A link instance
     * @param string        $domain The domain to use, optional (bit.ly | j.mp | bitly.com)
     *
     * @throws InvalidApiResponseException
     */
    public function shorten(LinkInterface $link, $domain = null)
    {
        $client = $this->createClient();

        $this->options['headers']['Authorization'] = 'Bearer ' . $this->auth->getAccessToken();

        $response = $client->post('/v4/shorten', array_merge(
            array(
                'json' => array(
                    'domain' => $domain,
                    'long_url' => $link->getLongUrl(),
                ),
            ),
            $this->options
        ));

        $response = $this->validate($response->getBody()->getContents(), $response->getStatusCode());

        $link->setShortUrl($response->link);
    }

    /**
     * {@inheritdoc}
     *
     * @param LinkInterface $link A link instance
     * @param string        $hash A Bit.ly hash
     *
     * @throws InvalidApiResponseException
     */
    public function expand(LinkInterface $link, $hash = null)
    {
        $client = $this->createClient();

        $this->options['headers']['Authorization'] = 'Bearer ' . $this->auth->getAccessToken();

        $response = $client->post('/v4/expand', array_merge(
            array(
                'json' => array(
                    'bitlink_id' => $hash ?: $link->getShortUrl(),
                ),
            ),
            $this->options
        ));

        $response = $this->validate($response->getBody()->getContents(), $response->getStatusCode());

        $link->setLongUrl($response->long_url);
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
        return new Client([
            'base_uri' => 'https://api-ssl.bitly.com',
        ]);
    }

    /**
     * Validates the Bit.ly's response and returns it whether the status code is 200.
     *
     * @param string  $apiRawResponse
     * @param integer $statusCode
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse, $statusCode)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Bit.ly response is probably mal-formed because cannot be json-decoded.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new InvalidApiResponseException(sprintf('Bit.ly returned status code "%s" with message "%s"',
                $statusCode,
                property_exists($response, 'message') ? $response->message : ''
            ));
        }

        return $response;
    }
}