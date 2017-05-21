<?php

defined('MOBICMS') or die('Error: restricted access');

$textl = htmlspecialchars($user['name']) . ': ' . _t('IP History');
require('../system/head.php');

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

// Проверяем права доступа
if (!$systemUser->rights && $systemUser->id != $user['id']) {
    echo $tools->displayError(_t('Access forbidden'));
    require('../system/end.php');
    exit;
}

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

// История IP адресов
echo '<div class="phdr"><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a> | ' . _t('IP History') . '</div>';
echo '<div class="user"><p>';
$arg = array(
    'lastvisit' => 1,
    'header' => '<b>ID:' . $user['id'] . '</b>'
);
echo $tools->displayUser($user, $arg);
echo '</p></div>';

$total = $db->query("SELECT COUNT(*) FROM `cms_users_iphistory` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();

if ($total) {
    $req = $db->query("SELECT * FROM `cms_users_iphistory` WHERE `user_id` = '" . $user['id'] . "' ORDER BY `time` DESC LIMIT $start, $userConfig->kmess");
    $i = 0;

    while ($res = $req->fetch()) {
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        $link = $systemUser->rights ? '<a href="' . $config['homeurl'] . '/admin/index.php?act=search_ip&amp;mod=history&amp;ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a>' : long2ip($res['ip']);
        echo $link . ' <span class="gray">(' . date("d.m.Y / H:i", $res['time']) . ')</span></div>';
        ++$i;
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $userConfig->kmess) {
    echo '<p>' . $tools->displayPagination('?act=ip&amp;user=' . $user['id'] . '&amp;', $start, $total, $userConfig->kmess) . '</p>';
    echo '<p><form action="?act=ip&amp;user=' . $user['id'] . '" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/>' .
        '</form></p>';
}
