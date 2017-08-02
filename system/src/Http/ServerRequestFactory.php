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

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory as Factory;

/**
 * Class ServerRequestFactory
 *
 * @package Mobicms\Http
 */
class ServerRequestFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $request = Factory::fromGlobals();
        $request = $this->normalizeBasePath($request, $container);
        $request = $this->determineIp($request);
        $request = $this->determineIpViaProxy($request);

        return $request;
    }

    /**
     * Determine the client IP address and stores it as an ServerRequest attribute
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function determineIp(ServerRequestInterface $request)
    {
        $serverParams = $request->getServerParams();
        $ipAddress = isset($serverParams['REMOTE_ADDR']) && $this->isValidIpAddress($serverParams['REMOTE_ADDR'])
            ? $serverParams['REMOTE_ADDR']
            : null;

        return $request->withAttribute('ip', $ipAddress);
    }

    /**
     * Determine the client IP via Proxy address and stores it as an ServerRequest attribute
     *
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    private function determineIpViaProxy(ServerRequestInterface $request)
    {
        $ipAddress = null;

        if ($request->hasHeader('X-Forwarded-For')
            && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s',
                $request->getHeader('X-Forwarded-For'),
                $vars
            )
        ) {
            foreach ($vars[0] AS $var) {
                if ($this->isValidIpAddress($var)
                    && $var != $request->getAttribute('ip')
                    && !preg_match('#^(10|172\.16|192\.168)\.#', $var)
                ) {
                    $ipAddress = $var;
                    break;
                }
            }
        }

        return $request->withAttribute('ip_via_proxy', $ipAddress);
    }

    /**
     * Remove a path prefix from a request uri
     *
     * @param ServerRequestInterface $request
     * @param ContainerInterface     $container
     * @return ServerRequestInterface
     */
    private function normalizeBasePath(ServerRequestInterface $request, ContainerInterface $container)
    {
        $config = $container->get('config');

        if (empty($config['mobicms']['base_path'])) {
            return $request;
        }

        $basePath = '/' . trim($config['mobicms']['base_path'], '/');

        $uri = $request->getUri();
        $path = substr($uri->getPath(), strlen($basePath)) ?: '/';
        $request = $request->withUri($uri->withPath($path));

        return $request;
    }

    /**
     * Check that a given string is a valid IP address
     *
     * @param  string $ip
     * @return boolean
     */
    private function isValidIpAddress($ip)
    {
        $flags = FILTER_FLAG_IPV4;

        return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false ?: true;
    }
}
