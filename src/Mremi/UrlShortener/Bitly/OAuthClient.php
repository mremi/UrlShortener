<?php

namespace Mremi\UrlShortener\Bitly;

use Guzzle\Http\Client;

/**
 * OAuth client class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class OAuthClient implements AuthenticationInterface
{
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
     * @param string $username A valid Bit.ly username
     * @param string $password A valid Bit.ly password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        $client = new Client('https://api-ssl.bitly.com/oauth/access_token');

        $request = $client->post(null, null, null, [
            'auth' => [
                $this->username,
                $this->password,
            ],
        ]);

        return $request->send()->getBody(true);
    }
}