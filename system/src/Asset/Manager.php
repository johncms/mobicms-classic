<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Asset;

/**
 * Class Manager
 *
 * @package Mobicms\Asset
 * @author  Oleg Kasyanov <dev@mobicms.net>
 */
class Manager
{
    private $namespaces = [];

    public function __construct($defaultAssetPath)
    {
        $this->addNamespace('system', $defaultAssetPath);
    }

    /**
     * @param string $namespace
     * @param string $path
     */
    public function addNamespace($namespace, $path)
    {
        if (isset($this->namespaces[$namespace])) {
            throw new \InvalidArgumentException('The namespace "' . $namespace . '" is already registered.');
        }

        $this->namespaces[$namespace] = $path;
    }

    /**
     * @param string      $file
     * @param null|string $namespace
     * @return mixed
     */
    public function get($file, $namespace = null)
    {
        return $file;
    }

    /**
     * @param string      $file
     * @param null|string $namespace
     * @return bool
     */
    public function has($file, $namespace = null)
    {
        return is_file($file);
    }

    /**
     * The <img> tag builder
     *
     * @param string      $src
     * @param null|string $namespace
     * @return Img
     */
    public function img($src, $namespace = null)
    {
        return new Img($src);
    }
}
