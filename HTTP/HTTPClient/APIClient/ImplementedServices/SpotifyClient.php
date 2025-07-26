<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\ImplementedServices;

use PHP_Library\HTTP\HTTPClient\APIClient\APIClient;
use PHP_Library\HTTP\HTTPClient\Auth\OAuth2;

class SpotifyClient extends APIClient {
    public function __construct(string $client_id, string $client_secret, string $redirect_uri, string ...$scopes)
    {
        parent::__construct('https://api.spotify.com/v1', auth: new OAuth2(
            $client_id,
            $client_secret,
            'https://accounts.spotify.com/api/token',
            $redirect_uri,
            ...$scopes
        ));
    }
}