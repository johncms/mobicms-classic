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

/** @var Mobicms\Http\Request $request */
$request = $container->get(Mobicms\Http\Request::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

$act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$textl = isset($textl) ? $textl : $config['copyright'];
$keywords = isset($keywords) ? htmlspecialchars($keywords) : $config->meta_key;
$descriptions = isset($descriptions) ? htmlspecialchars($descriptions) : $config->meta_desc;

echo '<!DOCTYPE html>' .
    "\n" . '<html lang="' . $config->lng . '">' .
    "\n" . '<head>' .
    "\n" . '<meta charset="utf-8">' .
    "\n" . '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
    "\n" . '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes">' .
    "\n" . '<meta name="HandheldFriendly" content="true">' .
    "\n" . '<meta name="MobileOptimized" content="width">' .
    "\n" . '<meta content="yes" name="apple-mobile-web-app-capable">' .
    "\n" . '<meta name="Generator" content="mobiCMS, https://mobicms.org">' .
    "\n" . '<meta name="keywords" content="' . $keywords . '">' .
    "\n" . '<meta name="description" content="' . $descriptions . '">' .
    "\n" . '<link rel="stylesheet" href="' . $config->homeurl . '/themes/' . $tools->getSkin() . '/css/legacy.css">' .
    "\n" . '<link rel="shortcut icon" href="' . $config->homeurl . '/favicon.ico">' .
    "\n" . '<link rel="alternate" type="application/rss+xml" title="RSS | ' . _t('Site News', 'system') . '" href="' . $config->homeurl . '/rss/">' .
    "\n" . '<title>' . $textl . '</title>' .
    "\n" . '</head><body>';

// Выводим логотип и переключатель языков
echo '<table style="width: 100%;" class="logo"><tr>' .
    '<td valign="bottom"><a href="' . $config['homeurl'] . '">' . $tools->image('images/logo.png', ['class' => '']) . '</a></td>';

if (count($config->lng_list) > 1) {
    $locale = App::getTranslator()->getLocale();
    echo '<td align="right"><a href="' . $config->homeurl . '/go.php?lng"><b>' . strtoupper($locale) . '</b></a>&#160;<a href="' . $config->homeurl . '/go.php?lng">' . $tools->getFlag($locale) . '</a></td>';
}

echo '</tr></table>';

// Выводим верхний блок с приветствием
//echo '<div class="header"> ' . _t('Hi', 'system') . ', ' . ($systemUser->id ? '<b>' . $systemUser->name . '</b>!' : _t('Guest', 'system') . '!') . '</div>';

// Главное меню пользователя
echo '<div class="tmn">' .
    '<a href=\'' . $config['homeurl'] . '\'>' . $tools->image('images/menu_home.png') . _t('Home', 'system') . '</a><br>' .
    '<a href="' . $config['homeurl'] . '/profile/?act=office">' . $tools->image('images/menu_cabinet.png') . $systemUser->name . ' <small style="color: #a8b5c4">(' . _t('Personal', 'system') . ')</small></a><br>' .
    (!$systemUser->isValid() ? $tools->image('images/menu_login.png') . '<a href="' . $config['homeurl'] . '/login/">' . _t('Login', 'system') . '</a>' : '') .
    '</div><div class="maintxt">';

// Выводим сообщение о Бане
if (!empty($systemUser->ban)) {
    echo '<div class="alarm">' . _t('Ban', 'system') . '&#160;<a href="' . $config['homeurl'] . '/profile/?act=ban">' . _t('Details', 'system') . '</a></div>';
}

// Ссылки на непрочитанное
if ($systemUser->id) {
    $list = [];
    $new_sys_mail = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `read`='0' AND `sys`='1' AND `delete`!='" . $systemUser->id . "'")->fetchColumn();

    if ($new_sys_mail) {
        $list[] = '<a href="' . $config['homeurl'] . '/mail/index.php?act=systems">' . _t('System', 'system') . '</a> (+' . $new_sys_mail . ')';
    }

    $new_mail = $db->query("SELECT COUNT(*) FROM `cms_mail`
                            LEFT JOIN `cms_contact` ON `cms_mail`.`user_id`=`cms_contact`.`from_id` AND `cms_contact`.`user_id`='" . $systemUser->id . "'
                            WHERE `cms_mail`.`from_id`='" . $systemUser->id . "'
                            AND `cms_mail`.`sys`='0'
                            AND `cms_mail`.`read`='0'
                            AND `cms_mail`.`delete`!='" . $systemUser->id . "'
                            AND `cms_contact`.`ban`!='1'")->fetchColumn();

    if ($new_mail) {
        $list[] = '<a href="' . $config['homeurl'] . '/mail/index.php?act=new">' . _t('Mail', 'system') . '</a> (+' . $new_mail . ')';
    }

    if ($systemUser->comm_count > $systemUser->comm_old) {
        $list[] = '<a href="' . $config['homeurl'] . '/profile/?act=guestbook&amp;user=' . $systemUser->id . '">' . _t('Guestbook', 'system') . '</a> (' . ($systemUser->comm_count - $systemUser->comm_old) . ')';
    }

    $new_album_comm = $db->query('SELECT COUNT(*) FROM `cms_album_files` WHERE `user_id` = ' . $systemUser->id . ' AND `unread_comments` = 1')->fetchColumn();

    if ($new_album_comm) {
        $list[] = '<a href="' . $config['homeurl'] . '/album/index.php?act=top&amp;mod=my_new_comm">' . _t('Comments', 'system') . '</a>';
    }

    if (!empty($list)) {
        echo '<div class="rmenu">' . _t('Unread', 'system') . ': ' . implode(', ', $list) . '</div>';
    }
}
