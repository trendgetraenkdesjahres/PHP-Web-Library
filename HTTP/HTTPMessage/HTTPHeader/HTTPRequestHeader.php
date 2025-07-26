<?php

namespace PHP_Library\HTTP\HTTPMessage\HTTPHeader;

/**
 * Represents HTTP request headers.  
 * Extends {@see HTTPHeader} with the RFC 7231/7230 §5 request‑specific fields.
 */
#[\AllowDynamicProperties]
class HTTPRequestHeader extends HTTPHeader
{
    /* ---------------------------------------------------------
     *  Accept* / Content negotiation
     * --------------------------------------------------------- */

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.2 */
    public array|string|null $accept = null;                 // list of media ranges

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.3 */
    public array|null $accept_charset = null;         // list of charsets

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.4 */
    public array|null $accept_encoding = null;        // list of codings

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.5 */
    public array|null $accept_language = null;        // list of languages


    /* ---------------------------------------------------------
     *  Authentication & identity
     * --------------------------------------------------------- */

    /** @link https://datatracker.ietf.org/doc/html/rfc7235#section-4.2 */
    public string|null $authorization = null;         // singular credentials

    /** @link https://datatracker.ietf.org/doc/html/rfc7235#section-4.4 */
    public string|null $proxy_authorization = null;   // singular credentials

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.5.1 */
    public string|null $from = null;                  // single mailbox

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.5.3 */
    public string|null $user_agent = null;            // singular product string


    /* ---------------------------------------------------------
     *  Conditional requests
     * --------------------------------------------------------- */

    /** @link https://datatracker.ietf.org/doc/html/rfc7232#section-3.1 */
    public array|null  $if_match = null;              // list of ETags

    /** @link https://datatracker.ietf.org/doc/html/rfc7232#section-3.3 */
    public string|null $if_modified_since = null;     // HTTP‑date

    /** @link https://datatracker.ietf.org/doc/html/rfc7232#section-3.2 */
    public array|null  $if_none_match = null;         // list of ETags or "*"

    /** @link https://datatracker.ietf.org/doc/html/rfc7233#section-3.2 */
    public string|null $if_range = null;              // ETag or HTTP‑date

    /** @link https://datatracker.ietf.org/doc/html/rfc7232#section-3.4 */
    public string|null $if_unmodified_since = null;   // HTTP‑date


    /* ---------------------------------------------------------
     *  Request routing / control
     * --------------------------------------------------------- */

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.4 */
    public string|null $host = null;                  // singular host:port

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.4 */
    public string|null $referer = null;               // singular absolute‑URI

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.1 */
    public array|null  $range = null;                 // list of byte‑ranges

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.2.1.1 */
    public string|null $expect = null;                // usually "100-continue"

    /** @link https://datatracker.ietf.org/doc/html/rfc7231#section-5.1.2 */
    public string|null $max_forwards = null;          // integer hop‑count

    /** @link https://datatracker.ietf.org/doc/html/rfc7230#section-4.3 */
    public array|null  $te = null;                    // list of transfer codings


    /**
     * @param string[] $header_lines Raw request header lines.
     * @param bool     $is_mutable  Leave mutable by default; set to false to freeze.
     */
    public function __construct(array $header_lines = [], bool $is_mutable = true)
    {
        parent::__construct($header_lines);
        $this->is_mutable = $is_mutable;
    }
}
