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

$pageTitle = _t('Mail') . ' | ' . _t('Files');
ob_start();

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

echo '<div class="phdr"><b>' . _t('Files') . '</b></div>';

//Отображаем список файлов
$total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE (`user_id`='" . $systemUser->id . "' OR `from_id`='" . $systemUser->id . "') AND `delete`!='" . $systemUser->id . "' AND `file_name`!=''")->fetchColumn();

if ($total) {
    if ($total > $userConfig->kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=files&amp;', $total) . '</div>';
    }

    $req = $db->query("SELECT `cms_mail`.*, `users`.`name`
        FROM `cms_mail`
        LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
	    WHERE (`cms_mail`.`user_id`='" . $systemUser->id . "' OR `cms_mail`.`from_id`='" . $systemUser->id . "')
	    AND `cms_mail`.`delete`!='" . $systemUser->id . "'
	    AND `cms_mail`.`file_name`!=''
	    ORDER BY `cms_mail`.`time` DESC" . $tools->getPgStart(true));

    for ($i = 0; ($row = $req->fetch()) !== false; ++$i) {
        echo $i % 2 ? '<div class="list1">' : '<div class="list2">';
        echo '<a href="../profile/?user=' . $row['user_id'] . '"><b>' . $row['name'] . '</b></a>:: <a href="index.php?act=load&amp;id=' . $row['id'] . '">' . $row['file_name'] . '</a> (' . formatsize($row['size']) . ') (' . $row['count'] . ')';
        echo '</div>';
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}


echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=files&amp;', $total) . '</div>';
    echo '<p><form action="index.php" method="get">
		<input type="hidden" name="act" value="files"/>
		<input type="text" name="page" size="2"/>
		<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
}

echo '<p><a href="../profile/?act=office">' . _t('Personal') . '</a></p>';
