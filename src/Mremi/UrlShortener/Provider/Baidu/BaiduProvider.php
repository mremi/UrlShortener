<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) zacksleo <zacksleo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\Baidu;

use GuzzleHttp\Client;
use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Baidu provider class.
 *
 * @author zacksleo <zacksleo@gmail.com>
 */
class BaiduProvider implements UrlShortenerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'baidu';
    }

    /**
     * {@inheritdoc}
     */
    public function shorten(LinkInterface $link)
    {
        $client = $this->createClient();

        $response = $client->post('/create.php', [
            'json' => [
                'url' => $link->getLongUrl(),
            ]
        ]);

        $response = $this->validate($response->getBody()->getContents(), true);

        $link->setShortUrl($response->tinyurl);
    }

    /**
     * {@inheritdoc}
     */
    public function expand(LinkInterface $link)
    {
        $client = $this->createClient();

        $response = $client->post('/query.php', [
            'json' => [
                'tinyUrl' => $link->getShortUrl()
            ]
        ]);

        $response = $this->validate($response->getBody()->getContents(), true);

        $link->setLongUrl($response->longurl);
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
            'base_uri' => 'http://dwz.cn',
        ));
    }

    /**
     * Validates the Google's response and returns it whether the status code is 200.
     *
     * @param string $apiRawResponse An API response, as it returned
     * @param bool   $checkStatus    TRUE whether the status code has to be checked, default FALSE
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse, $checkStatus = false)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Baidu response is probably mal-formed because cannot be json-decoded.');
        }

        if (!$checkStatus) {
            return $response;
        }

        if (!property_exists($response, 'status')) {
            throw new InvalidApiResponseException('Property "status" does not exist within Baidu response.');
        }

        if (0 !== $response->status) {
            throw new InvalidApiResponseException(sprintf('Baidu returned status code "%s: %s".', $response->status, $response->err_msg));
        }

        return $response;
    }
}
