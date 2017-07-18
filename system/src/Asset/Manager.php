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

class Manager
{
    private $namespaces = [];

    public function __construct()
    {
        $this->namespaces['system'] = ROOT_PATH . '';
    }

    public function addNamespace($namespace, $path)
    {
        if (isset($this->namespaces[$namespace])) {
            throw new \InvalidArgumentException('The namespace "' . $namespace . '" is already registered.');
        }

        $this->namespaces[$namespace] = $path;
    }

    public function asset($file, $namespace = null)
    {
        return $file;
    }

    /**
     * @param $src
     * @return Img
     */
    public function img($src, $namespace = null)
    {
        return new Img($src);
    }
}
