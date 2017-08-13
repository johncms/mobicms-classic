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

/**
 * @var int                        $id
 * @var string                     $do
 *
 * @var PDO                        $db
 * @var Mobicms\Api\ToolsInterface $tools
 * @var League\Plates\Engine       $view
 */


if (!$id) {
    exit(_t('Wrong data'));
}

ob_start();
$start = $tools->getPgStart();

switch ($do) {
    case 'unset':
        // Удаляем фильтр
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
        header('Location: ?id=' . $id);
        break;

    case 'set':
        // Устанавливаем фильтр по авторам
        $users = isset($_POST['users']) ? $_POST['users'] : '';

        if (empty($_POST['users'])) {
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You have not selected any author'), '<a href="index.php?act=filter&amp;id=' . $id . '&amp;start=' . $start . '">' . _t('Back') . '</a>'),
            ]);
            exit;
        }

        $array = [];

        foreach ($users as $val) {
            $array[] = intval($val);
        }

        $_SESSION['fsort_id'] = $id;
        $_SESSION['fsort_users'] = serialize($array);
        header('Location: ?id=' . $id);
        break;

    default :
        // Показываем список авторов темы, с возможностью выбора
        $req = $db->query("SELECT *, COUNT(`from`) AS `count` FROM `forum` WHERE `refid` = '$id' GROUP BY `from` ORDER BY `from`");
        $total = $req->rowCount();

        if ($total) {
            echo '<div class="phdr"><a href="index.php?id=' . $id . '&amp;start=' . $start . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Filter by author') . '</div>' .
                '<form action="index.php?act=filter&amp;id=' . $id . '&amp;start=' . $start . '&amp;do=set" method="post">';
            $i = 0;

            while ($res = $req->fetch()) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                echo '<input type="checkbox" name="users[]" value="' . $res['user_id'] . '"/>&#160;' .
                    '<a href="../profile/?user=' . $res['user_id'] . '">' . $res['from'] . '</a> [' . $res['count'] . ']</div>';
                ++$i;
            }

            echo '<div class="gmenu"><input type="submit" value="' . _t('Filter') . '" name="submit" /></div>' .
                '<div class="phdr"><small>' . _t('Filter will be display posts from selected authors only') . '</small></div>' .
                '</form>';
        } else {
            echo $tools->displayError(_t('Wrong data'));
        }
}

echo '<p><a href="index.php?id=' . $id . '&amp;start=' . $start . '">' . _t('Back to topic') . '</a></p>';
