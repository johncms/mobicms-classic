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

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

ob_start();
require dirname(__DIR__) . '/classes/download.php';

// Топ файлов
if ($id == 2) {
    $pageTitle = _t('Most Commented');
} elseif ($id == 1) {
    $pageTitle = _t('Most Downloaded');
} else {
    $pageTitle = _t('Popular Files');
}

$linkTopComments = $config['mod_down_comm'] || $systemUser->rights >= 7 ? '<br><a href="?act=top_files&amp;id=2">' . _t('Most Commented') . '</a>' : '';
echo '<div class="phdr"><a href="?"><b>' . _t('Downloads') . '</b></a> | ' . $pageTitle . ' (' . $set_down['top'] . ')</div>';

if ($id == 2 && ($config['mod_down_comm'] || $systemUser->rights >= 7)) {
    echo '<div class="gmenu"><a href="?act=top_files&amp;id=0">' . _t('Popular Files') . '</a><br>' .
        '<a href="?act=top_files&amp;id=1">' . _t('Most Downloaded') . '</a></div>';
    $sql = '`comm_count`';
} elseif ($id == 1) {
    echo '<div class="gmenu"><a href="?act=top_files&amp;id=0">' . _t('Popular Files') . '</a>' . $linkTopComments . '</div>';
    $sql = '`field`';
} else {
    echo '<div class="gmenu"><a href="?act=top_files&amp;id=1">' . _t('Most Downloaded') . '</a>' . $linkTopComments . '</div>';
    $sql = '`rate`';
}

// Выводим список
$req_down = $db->query("SELECT * FROM `download__files` WHERE `type` = 2 ORDER BY $sql DESC LIMIT " . $set_down['top']);
$i = 0;

while ($res_down = $req_down->fetch()) {
    echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down, 1) . '</div>';
}

echo '<div class="phdr"><a href="?">' . _t('Downloads') . '</a></div>';

echo $view->render('system::app/legacy', [
    'title'   => $pageTitle,
    'content' => ob_get_clean(),
]);
