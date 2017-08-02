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
use UnexpectedValueException;

/**
 * Class ServerRequestFactory
 *
 * @package Mobicms\Http
 */
class ServerRequestFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $server = Factory::normalizeServer($_SERVER);
        $headers = Factory::marshalHeaders($server);

        $request = new ServerRequest(
            $server,
            Factory::normalizeFiles($_FILES),
            Factory::marshalUriFromServer($server, $headers),
            Factory::get('REQUEST_METHOD', $server, 'GET'),
            'php://input',
            $headers,
            $_COOKIE,
            $_GET,
            $_POST,
            $this->marshalProtocolVersion($server)
        );

        return $this->setBasePath($request, $container);
    }

    /**
     * Remove a path prefix from a request uri
     *
     * @param ServerRequestInterface $request
     * @param ContainerInterface     $container
     * @return ServerRequestInterface
     */
    private function setBasePath(ServerRequestInterface $request, ContainerInterface $container)
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
     * Return HTTP protocol version (X.Y)
     *
     * @param array $server
     * @return string
     */
    private function marshalProtocolVersion(array $server)
    {
        if (!isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (!preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }
}
