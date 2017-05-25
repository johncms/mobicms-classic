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

require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);
$start = $tools->getPgStart();

if ($systemUser->rights >= 7) {
    $req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'");

    if (!$req->rowCount() || $systemUser->rights < 7) {
        echo $tools->displayError(_t('Topic has been deleted or does not exists'));
        require('../system/end.php');
        exit;
    }

    $topic = $req->fetch();
    $req = $db->query("SELECT `forum`.*, `users`.`id`
        FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
        WHERE `forum`.`refid`='$id' AND `users`.`rights` < 6 AND `users`.`rights` != 3 GROUP BY `forum`.`from` ORDER BY `forum`.`from`");
    $total = $req->rowCount();
    echo '<div class="phdr"><a href="index.php?id=' . $id . '&amp;start=' . $start . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Curators') . '</div>' .
        '<div class="bmenu">' . $topic['text'] . '</div>';
    $curators = [];
    $users = !empty($topic['curators']) ? unserialize($topic['curators']) : [];

    if (isset($_POST['submit'])) {
        $users = isset($_POST['users']) ? $_POST['users'] : [];
        if (!is_array($users)) {
            $users = [];
        }
    }

    if ($total > 0) {
        echo '<form action="index.php?act=curators&amp;id=' . $id . '&amp;start=' . $start . '" method="post">';
        $i = 0;

        while ($res = $req->fetch()) {
            $checked = array_key_exists($res['user_id'], $users) ? true : false;

            if ($checked) {
                $curators[$res['user_id']] = $res['from'];
            }

            echo ($i++ % 2 ? '<div class="list2">' : '<div class="list1">') .
                '<input type="checkbox" name="users[' . $res['user_id'] . ']" value="' . $res['from'] . '"' . ($checked ? ' checked="checked"' : '') . '/>&#160;' .
                '<a href="../profile/?user=' . $res['user_id'] . '">' . $res['from'] . '</a></div>';
        }

        echo '<div class="gmenu"><input type="submit" value="' . _t('Assign') . '" name="submit" /></div></form>';

        if (isset($_POST['submit'])) {
            $db->exec("UPDATE `forum` SET `curators`=" . $db->quote(serialize($curators)) . " WHERE `id` = '$id'");
        }

    } else {
        echo $tools->displayError(_t('The list is empty'));
    }
    echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>' .
        '<p><a href="index.php?id=' . $id . '&amp;start=' . $start . '">' . _t('Back') . '</a></p>';
}
