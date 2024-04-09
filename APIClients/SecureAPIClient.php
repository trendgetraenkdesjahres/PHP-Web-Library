<?php

namespace PHP_Library\APIClients;

use PHP_Library\Notices\Warning;
use PHP_Library\Settings\Settings;

class SecureAPIClient extends APIClient
{
    public string $cookie_file;

    public function __construct(
        public string $user,
        public string $secret,
        public string $token_url,
        ?string $host = null,
        protected bool $talking_json = true
    ) {
        if ($host) $this->host = (substr($host, -1) != '/') ? "$host/" : $host;
        $this->access_token = $this->get_token($user,  $secret,  $token_url);
        $this->cookie_file = Settings::get('cookie_file');
    }

    private function get_token(string $user, string $secret, string $token_url)
    {
        $client = new APIClient();
        $response = $client->http_post(
            post_fields: ['grant_type=client_credentials'],
            http_headers: [
                'Authorization: Basic ' . base64_encode($user . ':' . $secret),
                'Content-Type: application/x-www-form-urlencoded',
            ],
            target_url: $token_url
        )->curl_response_body;

        if (isset($response['access_token'])) {
            return $response['access_token'];
        };
        PHP_Library\Warning::trigger("Could not receive access_token at $token_url for $user");
        return false;
    }
}
