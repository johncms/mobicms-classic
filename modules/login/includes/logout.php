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

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

$referer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : $config->homeurl;

if (isset($_POST['submit'])) {
    session_destroy();
    setcookie('cuid', '', strtotime('-1 Year', time()), '/');
    setcookie('cups', '', strtotime('-1 Year', time()), '/');
    header('Location: ' . $config->homeurl);
    exit;
} else {
    require ROOT_PATH . 'system/head.php';
    echo '<div class="rmenu">' .
        '<p>' . _t('Are you sure you want to leave the site?', 'system') . '</p>' .
        '<form action="?" method="post"><p><input type="submit" name="submit" value="' . _t('Logout', 'system') . '" /></p></form>' .
        '<p><a href="' . $referer . '">' . _t('Cancel', 'system') . '</a></p>' .
        '</div>';
    require ROOT_PATH . 'system/end.php';
}
