<?php

namespace PHP_Library\HTTP\HTTPClient\APIClient\ImplementedServices;

use PHP_Library\HTTP\HTTPClient\APIClient\APIClient;
use PHP_Library\HTTP\HTTPClient\APIClient\Pagination\CursorPagination;
use PHP_Library\HTTP\HTTPClient\Auth\Identification;

class YoutubeClient extends APIClient
{
    public function __construct(string $api_key)
    {
        $google_pagination = new CursorPagination(cursor_field: 'nextPageToken');
        parent::__construct('https://youtube.googleapis.com/v1', pagination: $google_pagination, auth: new Identification($api_key, 'key'));
    }
}
