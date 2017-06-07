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

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $container->get(Mobicms\Api\UserInterface::class)->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;

use Library\Hashtags;
use Library\Rating;

echo '<div class="phdr"><strong><a href="?">' . _t('Library') . '</a></strong> | ' . _t('New Articles') . '</div>';

$total = $db->query("SELECT COUNT(*) FROM `library_texts` WHERE `time` > '" . (time() - 259200) . "' AND `premod`=1")->fetchColumn();
$page = $page >= ceil($total / $userConfig->kmess) ? ceil($total / $userConfig->kmess) : $page;
$start = $page == 1 ? 0 : ($page - 1) * $userConfig->kmess;
$sql = $db->query("SELECT `id`, `name`, `time`, `uploader`, `uploader_id`, `count_views`, `comments`, `comm_count`, `cat_id`, `announce` FROM `library_texts` WHERE `time` > '" . (time() - 259200) . "' AND `premod`=1 ORDER BY `time` DESC LIMIT " . $start . "," . $userConfig->kmess);
$nav = ($total > $userConfig->kmess) ? '<div class="topmenu">' . $tools->displayPagination('?act=new&amp;', $total) . '</div>' : '';
echo $nav;
if ($total) {
    $i = 0;
    while ($row = $sql->fetch()) {
        echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">'
            . (file_exists('../files/library/images/small/' . $row['id'] . '.png')
                ? '<div class="avatar"><img src="../files/library/images/small/' . $row['id'] . '.png" alt="screen" /></div>'
                : '')
            . '<div class="righttable"><h4><a href="index.php?id=' . $row['id'] . '">' . $tools->checkout($row['name']) . '</a></h4>'
            . '<div><small>' . $tools->checkout($row['announce'], 0, 2) . '</small></div></div>';

        // Описание к статье
        $obj = new Hashtags($row['id']);
        $rate = new Rating($row['id']);
        echo '<table class="desc">'
            // Раздел
            . '<tr>'
            . '<td class="caption">' . _t('Section') . ':</td>'
            . '<td><a href="?do=dir&amp;id=' . $row['cat_id'] . '">' . $tools->checkout($db->query("SELECT `name` FROM `library_cats` WHERE `id`=" . $row['cat_id'])->fetchColumn()) . '</a></td>'
            . '</tr>'
            // Тэги
            . ($obj->getAllStatTags() ? '<tr><td class="caption">' . _t('Tags') . ':</td><td>' . $obj->getAllStatTags(1) . '</td></tr>' : '')
            // Кто добавил?
            . '<tr>'
            . '<td class="caption">' . _t('Who added') . ':</td>'
            . '<td><a href="' . App::getContainer()->get('config')['mobicms']['homeurl'] . '/profile/?user=' . $row['uploader_id'] . '">' . $tools->checkout($row['uploader']) . '</a> (' . $tools->displayDate($row['time']) . ')</td>'
            . '</tr>'
            // Рейтинг
            . '<tr>'
            . '<td class="caption">' . _t('Rating') . ':</td>'
            . '<td>' . $rate->viewRate() . '</td>'
            . '</tr>'
            // Прочтений
            . '<tr>'
            . '<td class="caption">' . _t('Number of readings') . ':</td>'
            . '<td>' . $row['count_views'] . '</td>'
            . '</tr>'
            // Комментарии
            . '<tr>';
        if ($row['comments']) {
            echo '<td class="caption"><a href="?act=comments&amp;id=' . $row['id'] . '">' . _t('Comments') . '</a>:</td><td>' . $row['comm_count'] . '</td>';
        } else {
            echo '<td class="caption">' . _t('Comments') . ':</td><td>' . _t('Comments are closed') . '</td>';
        }
        echo '</tr></table>';

        echo '</div>';
    }
}
echo '<div class="phdr">' . _t('Total') . ': ' . intval($total) . '</div>';
echo $nav;
echo '<p><a href="?">' . _t('To Library') . '</a></p>';
