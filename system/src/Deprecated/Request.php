<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Deprecated;

use Klein\Request as KleinRequest;

/**
 * Class Request
 *
 * @package Mobicms\Deprecated
 * @deprecated
 */
class Request extends KleinRequest
{
    protected $ipViaProxy;
    protected $userAgent;

    /**
     * Gets the request IP address
     *
     * @return string
     */
    public function ip()
    {
        return filter_var($this->server->get('REMOTE_ADDR'), FILTER_VALIDATE_IP);
    }

    /**
     * Gets the request IP via Proxy address (if exists)
     *
     * @return string
     */
    public function ipViaProxy()
    {
        if ($this->ipViaProxy !== null) {
            return $this->ipViaProxy;
        } elseif ($this->server->exists('X_FORWARDED_FOR')
            && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',
                filter_var($this->server->get('X_FORWARDED_FOR'), FILTER_SANITIZE_STRING),
                $vars
            )
        ) {
            foreach ($vars[0] AS $var) {
                $ipViaProxy = filter_var($var, FILTER_VALIDATE_IP);

                if ($ipViaProxy !== false
                    && $ipViaProxy != $this->ip()
                    && !preg_match('#^(10|172\.16|192\.168)\.#', $var)
                ) {
                    return $this->ipViaProxy = $ipViaProxy;
                }
            }
        }

        return $this->ipViaProxy = '';
    }

    /**
     * Gets the request user agent
     *
     * @return string
     */
    public function userAgent()
    {
        if ($this->userAgent !== null) {
            return $this->userAgent;
        }

        if ($this->headers->exists('X_OPERAMINI_PHONE_UA')) {
            $this->userAgent = 'Opera Mini: ' . $this->headers->get('X_OPERAMINI_PHONE_UA');
        } else {
            $this->userAgent = $this->headers->get('USER_AGENT', 'Not Recognised');
        }

        return $this->userAgent = mb_substr(filter_var($this->userAgent, FILTER_SANITIZE_SPECIAL_CHARS), 0, 180);
    }
}
