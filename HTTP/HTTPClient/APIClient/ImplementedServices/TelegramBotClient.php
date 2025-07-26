<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\ImplementedServices;

use PHP_Library\HTTP\HTTPClient\APIClient\APIClient;

class TelegramBotClient extends APIClient {
    public function __construct(string $token)
    {
        parent::__construct("https://api.telegram.org/bot{$token}");
    }
}