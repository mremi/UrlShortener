<?php

namespace Mremi\UrlShortener\Provider\Bitly;

use Mremi\UrlShortener\Http\ClientFactoryInterface;

/**
 * OAuth client class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class OAuthClient implements AuthenticationInterface
{
    /**
     * @var ClientFactoryInterface
     */
    private $clientFactory;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Constructor
     *
     * @param ClientFactoryInterface $clientFactory A client factory instance
     * @param string                 $username      A valid Bit.ly username
     * @param string                 $password      A valid Bit.ly password
     */
    public function __construct(ClientFactoryInterface $clientFactory, $username, $password)
    {
        $this->clientFactory = $clientFactory;
        $this->username      = $username;
        $this->password      = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        $client = $this->clientFactory->create('https://api-ssl.bitly.com/oauth/access_token');

        $request = $client->post(null, null, null, array(
            'auth' => array(
                $this->username,
                $this->password,
            ),
        ));

        return $request->send()->getBody(true);
    }
}
