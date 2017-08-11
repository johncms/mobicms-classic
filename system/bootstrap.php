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
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

if (is_file(__DIR__ . '/mobicms-core/bootstrap.php')) {
    require __DIR__ . '/mobicms-core/bootstrap.php';
} else {
    die('<h1>Please install dependencies</h1>Run: composer install');
}
