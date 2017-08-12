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

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

ob_start();
require dirname(__DIR__) . '/classes/download.php';

$pageTitle = _t('Files on moderation');

if ($systemUser->rights == 4 || $systemUser->rights >= 6) {
    echo '<div class="phdr"><a href="?"><b>' . _t('Downloads') . '</b></a> | ' . $pageTitle . '</div>';

    if ($id) {
        $db->exec("UPDATE `download__files` SET `type` = 2 WHERE `id` = '" . $id . "' LIMIT 1");
        echo '<div class="gmenu">' . _t('File accepted') . '</div>';
    } else {
        if (isset($_POST['all_mod'])) {
            $db->exec("UPDATE `download__files` SET `type` = 2 WHERE `type` = '3'");
            echo '<div class="gmenu">' . _t('All files accepted') . '</div>';
        }
    }

    $total = $db->query("SELECT COUNT(*) FROM `download__files` WHERE `type` = '3'")->fetchColumn();

    // Навигация
    if ($total > $userConfig->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('?act=mod_files&amp;', $total) . '</div>';
    }

    $i = 0;

    if ($total) {
        $req_down = $db->query("SELECT * FROM `download__files` WHERE `type` = '3' ORDER BY `time` DESC" . $tools->getPgStart(true));
        while ($res_down = $req_down->fetch()) {
            echo (($i++ % 2) ? '<div class="list2">' : '<div class="list1">') . Download::displayFile($res_down) .
                '<div class="sub"><a href="?act=mod_files&amp;id=' . $res_down['id'] . '">' . _t('Accept') . '</a> | ' .
                '<span class="red"><a href="?act=delete_file&amp;id=' . $res_down['id'] . '">' . _t('Delete') . '</a></span></div></div>';
        }

        echo '<div class="rmenu"><form name="" action="?act=mod_files" method="post"><input type="submit" name="all_mod" value="' . _t('Accept all files') . '"/></form></div>';
    } else {
        echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
    }

    echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    // Навигация
    if ($total > $userConfig->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('?act=mod_files&amp;', $total) . '</div>' .
            '<p><form action="?" method="get">' .
            '<input type="hidden" value="top_users" name="act" />' .
            '<input type="text" name="page" size="2"/><input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
    }

    echo '<p><a href="?">' . _t('Downloads') . '</a></p>';
}

echo $view->render('system::app/legacy', [
    'title'   => $pageTitle,
    'content' => ob_get_clean(),
]);
