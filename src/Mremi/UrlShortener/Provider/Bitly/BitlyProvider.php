<?php

namespace Mremi\UrlShortener\Provider\Bitly;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Http\ClientFactoryInterface;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Bit.ly provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyProvider implements UrlShortenerProviderInterface
{
    const API_BASE_URL = 'https://api-ssl.bitly.com';

    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param ClientFactoryInterface  $clientFactory A client factory instance
     * @param AuthenticationInterface $auth          An authentication instance
     * @param array                   $options       An array of options used to do the shorten/expand request
     */
    public function __construct(ClientFactoryInterface $clientFactory, AuthenticationInterface $auth, array $options = array())
    {
        $this->clientFactory = $clientFactory;
        $this->auth          = $auth;
        $this->options       = $options;
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
        $client = $this->clientFactory->create(self::API_BASE_URL);

        $request = $client->get(sprintf('/v3/shorten?access_token=%s&longUrl=%s&domain=%s',
            $this->auth->getAccessToken(),
            urlencode($link->getLongUrl()),
            $domain
        ), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link->setShortUrl($response->data->url);
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
        $client = $this->clientFactory->create(self::API_BASE_URL);

        $request = $client->get(sprintf('/v3/expand?access_token=%s&shortUrl=%s&hash=%s',
            $this->auth->getAccessToken(),
            urlencode($link->getShortUrl()),
            $hash
        ), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link->setLongUrl($response->data->expand[0]->long_url);
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
