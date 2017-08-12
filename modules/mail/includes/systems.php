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

$out = '';
$total = 0;
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

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

if ($mod == 'clear') {
    if (isset($_POST['clear'])) {
        $count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1';")->fetchColumn();

        if ($count_message) {
            $req = $db->query("SELECT `id` FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' LIMIT " . $count_message);
            $mass_del = [];

            while ($row = $req->fetch()) {
                $mass_del[] = $row['id'];
            }

            if ($mass_del) {
                $result = implode(',', $mass_del);
                $db->exec("DELETE FROM `cms_mail` WHERE `id` IN (" . $result . ")");
            }
        }
        $out .= '<div class="gmenu">' . _t('Messages are deleted') . '</div>';
    } else {
        $out .= '
		<div class="rmenu">' . _t('Confirm the deletion of messages') . '</div>
		<div class="gmenu">
		<form action="index.php?act=systems&amp;mod=clear" method="post"><div>
		<input type="submit" name="clear" value="' . _t('Delete') . '"/>
		</div></form>
		</div>';
    }
} else {
    $total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `delete`!='" . $systemUser->id . "'")->fetchColumn();

    if ($total) {
        function time_parce($var)
        {
            global $tools;

            return $tools->displayDate($var[1]);
        }

        if ($total > $userConfig->kmess) {
            $out .= '<div class="topmenu">' . $tools->displayPagination('index.php?act=systems&amp;', $total) . '</div>';
        }

        $req = $db->query("SELECT * FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `delete`!='" . $systemUser->id . "' ORDER BY `time` DESC" . $tools->getPgStart(true));
        $mass_read = [];

        for ($i = 0; ($row = $req->fetch()) !== false; ++$i) {
            $out .= $i % 2 ? '<div class="list1">' : '<div class="list2">';

            if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
                $mass_read[] = $row['id'];
            }

            $post = $row['text'];
            $post = $tools->checkout($post, 1, 1);
            $post = $tools->smilies($post);
            $out .= '<strong>' . $tools->checkout($row['them']) . '</strong> (' . $tools->displayDate($row['time']) . ')<br />';
            $post = preg_replace_callback("/{TIME=(.+?)}/usi", 'time_parce', $post);
            $out .= $post;
            $out .= '<div class="sub"><a href="index.php?act=delete&amp;id=' . $row['id'] . '">' . _t('Delete') . '</a></div>';
            $out .= '</div>';
        }

        //Ставим метку о прочтении
        if ($mass_read) {
            $result = implode(',', $mass_read);
            $db->exec("UPDATE `cms_mail` SET `read`='1' WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `id` IN (" . $result . ")");
        }
    } else {
        $out .= '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
    }

    $out .= '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $userConfig->kmess) {
        $out .= '<div class="topmenu">' . $tools->displayPagination('index.php?act=systems&amp;', $total) . '</div>';
        $out .= '<p><form action="index.php" method="get">
			<input type="hidden" name="act" value="systems"/>
			<input type="text" name="page" size="2"/>
			<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
    }
}

$pageTitle = _t('Mail');
ob_start();
echo '<div class="phdr"><b>' . _t('System messages') . '</b></div>';
echo $out;
echo '<p>';

if ($total) {
    echo '<a href="index.php?act=systems&amp;mod=clear">' . _t('Clear messages') . '</a><br>';
}

echo '<a href="../profile/?act=office">' . _t('Personal') . '</a></p>';
