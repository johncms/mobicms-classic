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
    private $homeUrl;
    private $namespaces = [];

    public function __construct($homeUrl)
    {
        $this->homeUrl = $homeUrl;
    }

    /**
     * Add namespace path
     *
     * @param string $namespace
     * @param string $path
     */
    public function addNamespace($namespace, $path)
    {
        if ($this->hasNamespace($namespace)) {
            throw new \InvalidArgumentException('The namespace "' . $namespace . '" is already registered.');
        }

        $this->namespaces[$namespace] = empty($path) ? '' : trim($path, '/') . '/';
    }

    /**
     * Get namespace path
     *
     * @param string $namespace
     * @return string
     */
    public function getNamespace($namespace)
    {
        if (null === $namespace) {
            $namespace = 'system';
        }

        if (!$this->hasNamespace($namespace)) {
            throw new \InvalidArgumentException('The namespace "' . $namespace . '" does not exist.');
        }

        return $this->namespaces[$namespace];
    }

    public function hasNamespace($namespace)
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Get a link to the asset file
     *
     * @param string      $file
     * @param null|string $namespace
     * @return mixed
     */
    public function getAsset($file, $namespace = null)
    {
        if (!$this->hasAsset($file, $namespace)) {
            throw new \InvalidArgumentException('Invalid image file "' . $this->getNamespace($namespace) . $file . '"');
        }

        return $this->homeUrl . '/' . $this->getNamespace($namespace) . $file;
    }

    /**
     * @param string      $file
     * @param null|string $namespace
     * @return bool
     */
    public function hasAsset($file, $namespace = null)
    {
        return is_file(ROOT_PATH . $this->getNamespace($namespace) . $file);
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
        $src = trim($src, '/');

        return new Img($this->getAsset('images/' . $src, $namespace));
    }
}
