<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

defined('MOBICMS') or die('Error: restricted access');
define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));

// Check the current PHP version
if (version_compare(PHP_VERSION, '7.0', '<')) {
    // If the version below 7, we stops the script and displays an error
    die('<div style="text-align: center; font-size: xx-large">'
        . '<h3 style="color: #dd0000">ERROR: outdated version of PHP</h3>'
        . 'Your needs PHP 7.0 or higher'
        . '</div>');
}

// System constants
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
const CACHE_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
const CONFIG_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
const LOG_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
const UPLOAD_PATH = ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR;

// If there are no dependencies, we stop the script and displays an error
if (!is_file(__DIR__ . '/vendor/autoload.php')) {
    die('<div style="text-align: center; font-size: xx-large">'
        . '<h3 style="color: #dd0000">ERROR: missing dependencies</h3>'
        . 'Please run: <strong>composer install</strong>'
        . '</div>');
}

// Include autoloader
require __DIR__ . '/vendor/autoload.php';
