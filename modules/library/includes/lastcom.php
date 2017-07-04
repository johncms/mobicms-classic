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

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

echo '<div class="phdr"><strong><a href="?">' . _t('Library') . '</a></strong> | ' . _t('Latest comments') . '</div>';

$stmt = $db->query('SELECT `libcomm`.`user_id`, `libcomm`.`text`, `libcomm`.`time`, `libtxt`.`name`, `libtxt`.`comm_count`, `libtxt`.`id`
FROM `cms_library_comments` libcomm
JOIN `library_texts` libtxt ON `libcomm`.`sub_id`=`libtxt`.`id`
JOIN (
    SELECT `sub_id`, MAX( `time` ) AS `mtime` FROM `cms_library_comments` GROUP BY `sub_id`) AS tmp
    ON `libcomm`.`sub_id`=`tmp`.`sub_id` AND `libcomm`.`time`=`tmp`.`mtime`
ORDER BY `libcomm`.`time` DESC LIMIT 20');

if (count($res = $stmt->fetchAll())) {
    $i = 0;
    foreach ($res as $row) {
        echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
            . (file_exists('../uploads/library/images/small/' . $row['id'] . '.png')
                ? '<div class="avatar"><img src="../uploads/library/images/small/' . $row['id'] . '.png" alt="screen" /></div>'
                : '')
            . '<div class="righttable"><a href="?act=comments&amp;id=' . $row['id'] . '">' . $tools->checkout($row['name']) . '</a>'
            . '<div>' . $tools->checkout(substr($row['text'], 0, 500), 0, 2) . '</div></div>'
            . '<div class="sub">' . _t('Who added') . ': ' . $tools->checkout($db->query("SELECT `name` FROM `users` WHERE `id` = " . $row['user_id'])->fetchColumn()) . ' (' . $tools->displayDate($row['time']) . ')</div>'
            . '</div>';
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="phdr"><a href="?">' . _t('Back') . '</a></div>' . PHP_EOL;
