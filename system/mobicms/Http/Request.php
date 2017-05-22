<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Http;

use Klein\Request as KleinRequest;

class Request extends KleinRequest
{
    protected $ipViaProxy;

    /**
     * Gets the request IP address
     *
     * @return string
     */
    public function ip()
    {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
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
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && preg_match_all(
                '#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',
                filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_SANITIZE_STRING),
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
}
