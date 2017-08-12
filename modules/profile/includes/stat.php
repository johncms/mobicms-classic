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

/** @var Mobicms\Asset\Manager $asset */
$asset = $container->get(Mobicms\Asset\Manager::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

// Статистика
$pageTitle = htmlspecialchars($user['name']) . ': ' . _t('Statistic');
ob_start();
echo '<div class="phdr"><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a> | ' . _t('Statistic') . '</div>' .
    '<div class="user"><p>' . $tools->displayUser($user, ['iphide' => 1,]) . '</p></div>' .
    '<div class="list2">' .
    '<p><h3>' . $asset->img('rate.gif')->class('icon') . _t('Statistic') . '</h3><ul>';

if ($systemUser->rights >= 7) {
    if (!$user['preg'] && empty($user['regadm'])) {
        echo '<li>' . _t('Pending confirmation') . '</li>';
    } elseif ($user['preg'] && !empty($user['regadm'])) {
        echo '<li>' . _t('Registration confirmed') . ': ' . $user['regadm'] . '</li>';
    } else {
        echo '<li>' . _t('Free registration') . '</li>';
    }
}

echo '<li><span class="gray">' . _t('Registered') . ':</span> ' . date("d.m.Y", $user['datereg']) . '</li>';
echo '<li><span class="gray">' . ($user['sex'] == 'm' ? _t('He stay on the site') : _t('She stay on the site')) . ':</span> ' . $tools->timecount($user['total_on_site']) . '</li>';
$lastvisit = time() > $user['lastdate'] + 300 ? date("d.m.Y (H:i)", $user['lastdate']) : false;

if ($lastvisit) {
    echo '<li><span class="gray">' . _t('Last visit') . ':</span> ' . $lastvisit . '</li>';
}

echo '</ul></p><p>' .
    '<h3>' . $asset->img('activity.gif')->class('icon') . _t('Activity') . '</h3><ul>' .
    '<li><span class="gray">' . _t('Forum') . ':</span> <a href="?act=activity&amp;user=' . $user['id'] . '">' . $user['postforum'] . '</a></li>' .
    '<li><span class="gray">' . _t('Guestbook') . ':</span> <a href="?act=activity&amp;mod=comments&amp;user=' . $user['id'] . '">' . $user['postguest'] . '</a></li>' .
    '<li><span class="gray">' . _t('Comments') . ':</span> ' . $user['komm'] . '</li>' .
    '</ul></p>' .
    '<p><h3>' . $asset->img('award.png')->class('icon') . _t('Achievements') . '</h3>';
$num = [
    50,
    100,
    500,
    1000,
    5000,
];
$query = [
    'postforum' => _t('Forum'),
    'postguest' => _t('Guestbook'),
    'komm'      => _t('Comments'),
];
echo '<table border="0" cellspacing="0" cellpadding="0"><tr>';

foreach ($num as $val) {
    echo '<td width="28" align="center"><small>' . $val . '</small></td>';
}

echo '<td></td></tr>';

foreach ($query as $key => $val) {
    echo '<tr>';

    foreach ($num as $achieve) {
        echo '<td align="center">' . $asset->img(($user[$key] >= $achieve ? 'green' : 'red') . '.gif')->class('icon') . '</td>';
    }

    echo '<td><small><b>' . $val . '</b></small></td></tr>';
}

echo '</table></p></div><div class="phdr"><a href="?user=' . $user['id'] . '">' . _t('Back') . '</a></div>';
