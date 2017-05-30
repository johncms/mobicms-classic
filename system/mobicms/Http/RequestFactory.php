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

use Mobicms\Api\ConfigInterface;
use Psr\Container\ContainerInterface;

class RequestFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $basePath = $container->get(ConfigInterface::class)->base_path;
        $request = Request::createFromGlobals();

        if (!empty($basePath)) {
            $basePath = trim($basePath, '/') . '/';
            $uri = $request->server()->get('REQUEST_URI');
            $request->server()->set('REQUEST_URI', substr($uri, strlen($basePath)));
        }

        return $request;
    }
}
