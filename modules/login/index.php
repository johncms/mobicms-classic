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

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = App::getContainer()->get(Mobicms\Api\UserInterface::class);

require __DIR__ . '/includes/' . ($systemUser->isValid() ? 'logout.php' : 'login.php');
