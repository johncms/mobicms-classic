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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Asset\Manager $asset */
$asset = $container->get(Mobicms\Asset\Manager::class);

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

// Закрываем от неавторизованных юзеров
if (!$systemUser->isValid()) {
    echo $view->render('system::app/legacy', [
        'title'   => _t('Profile'),
        'content' => $tools->displayError(_t('For registered users only')),
    ]);
    exit;
}

// Получаем данные пользователя
$user = $tools->getUser(isset($_REQUEST['user']) ? abs(intval($_REQUEST['user'])) : 0);

if (!$user) {
    echo $view->render('system::app/legacy', [
        'title'   => _t('Profile'),
        'content' => $tools->displayError(_t('This User does not exists')),
    ]);
    exit;
}

/**
 * Находится ли выбранный пользователь в контактах и игноре?
 *
 * @param int $id Идентификатор пользователя, которого проверяем
 * @return int Результат запроса:
 *                0 - не в контактах
 *                1 - в контактах
 *                2 - в игноре у меня
 */
function is_contact($id = 0, $db, $systemUser)
{
    static $user_id = null;
    static $return = 0;

    if (!$systemUser->isValid() && !$id) {
        return 0;
    }

    if (is_null($user_id) || $id != $user_id) {
        $user_id = $id;
        $req = $db->query("SELECT * FROM `cms_contact` WHERE `user_id` = '" . $systemUser->id . "' AND `from_id` = '$id'");

        if ($req->rowCount()) {
            $res = $req->fetch();
            if ($res['ban'] == 1) {
                $return = 2;
            } else {
                $return = 1;
            }
        } else {
            $return = 0;
        }
    }

    return $return;
}

// Переключаем режимы работы
$array = [
    'activity',
    'ban',
    'edit',
    'images',
    'info',
    'ip',
    'guestbook',
    'office',
    'password',
    'reset',
    'settings',
    'stat',
];

if (in_array($act, $array) && is_file(__DIR__ . '/includes/' . $act . '.php')) {
    require __DIR__ . '/includes/' . $act . '.php';
} else {
    // Анкета пользователя
    $pageTitle = _t('Profile') . ': ' . htmlspecialchars($user['name']);
    ob_start();
    echo '<div class="phdr"><b>' . ($user['id'] != $systemUser->id ? _t('User Profile') : _t('My Profile')) . '</b></div>';

    // Меню анкеты
    $menu = [];

    if ($user['id'] == $systemUser->id || $systemUser->rights == 9 || ($systemUser->rights == 7 && $systemUser->rights > $user['rights'])) {
        $menu[] = '<a href="?act=edit&amp;user=' . $user['id'] . '">' . _t('Edit') . '</a>';
    }

    if ($user['id'] != $systemUser->id && $systemUser->rights >= 7 && $systemUser->rights > $user['rights']) {
        $menu[] = '<a href="' . $config['homeurl'] . '/admin/index.php?act=usr_del&amp;id=' . $user['id'] . '">' . _t('Delete') . '</a>';
    }

    if ($user['id'] != $systemUser->id && $systemUser->rights > $user['rights']) {
        $menu[] = '<a href="?act=ban&amp;mod=do&amp;user=' . $user['id'] . '">' . _t('Ban') . '</a>';
    }

    if (!empty($menu)) {
        echo '<div class="topmenu">' . implode(' | ', $menu) . '</div>';
    }

    //Уведомление о дне рожденья
    if ($user['dayb'] == date('j', time()) && $user['monthb'] == date('n', time())) {
        echo '<div class="gmenu">' . _t('Birthday') . '!!!</div>';
    }

    // Информация о юзере
    $arg = [
        'lastvisit' => 1,
        'iphist'    => 1,
        'header'    => '<b>ID:' . $user['id'] . '</b>',
    ];

    if ($user['id'] != $systemUser->id) {
        $arg['footer'] = '<span class="gray">' . _t('Where?') . ':</span> ' . $tools->displayPlace($user['place'], $user['id']);
    }

    echo '<div class="user"><p>' . $tools->displayUser($user, $arg) . '</p></div>';

    // Если юзер ожидает подтверждения регистрации, выводим напоминание
    if ($systemUser->rights >= 7 && !$user['preg'] && empty($user['regadm'])) {
        echo '<div class="rmenu">' . _t('Pending confirmation') . '</div>';
    }

    // Меню выбора
    $total_photo = $db->query("SELECT COUNT(*) FROM `cms_album_files` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();
    echo '<div class="list2"><p>' .
        '<div>' . $asset->img('contacts.png')->class('icon') . '<a href="?act=info&amp;user=' . $user['id'] . '">' . _t('Information') . '</a></div>' .
        '<div>' . $asset->img('activity.gif')->class('icon') . '<a href="?act=activity&amp;user=' . $user['id'] . '">' . _t('Activity') . '</a></div>' .
        '<div>' . $asset->img('rate.gif')->class('icon') . '<a href="?act=stat&amp;user=' . $user['id'] . '">' . _t('Statistic') . '</a></div>';
    $bancount = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();

    if ($bancount) {
        echo '<div>' . $asset->img('block.gif')->class('icon') . '&#160;<a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Violations') . '</a> (' . $bancount . ')</div>';
    }

    echo '<br />' .
        '<div>' . $asset->img('photo.gif')->class('icon') . '<a href="../album/index.php?act=list&amp;user=' . $user['id'] . '">' . _t('Photo Album') . '</a>&#160;(' . $total_photo . ')</div>' .
        '<div>' . $asset->img('guestbook.gif')->class('icon') . '<a href="?act=guestbook&amp;user=' . $user['id'] . '">' . _t('Guestbook') . '</a>&#160;(' . $user['comm_count'] . ')</div>' .
        '</p></div>';
    if ($user['id'] != $systemUser->id) {
        echo '<div class="menu"><p>';
        // Контакты
        if (is_contact($user['id'], $db, $systemUser) != 2) {
            if (!is_contact($user['id'], $db, $systemUser)) {
                echo '<div>' . $asset->img('users.png')->class('icon') . '&#160;<a href="../mail/index.php?id=' . $user['id'] . '">' . _t('Add to Contacts') . '</a></div>';
            } else {
                echo '<div>' . $asset->img('users.png')->class('icon') . '&#160;<a href="../mail/index.php?act=deluser&amp;id=' . $user['id'] . '">' . _t('Remove from Contacts') . '</a></div>';
            }
        }

        if (is_contact($user['id'], $db, $systemUser) != 2) {
            echo '<div>' . $asset->img('del.png')->class('icon') . '&#160;<a href="../mail/index.php?act=ignor&amp;id=' . $user['id'] . '&amp;add">' . _t('Block User') . '</a></div>';
        } else {
            echo '<div>' . $asset->img('del.png')->class('icon') . '&#160;<a href="../mail/index.php?act=ignor&amp;id=' . $user['id'] . '&amp;del">' . _t('Unlock User') . '</a></div>';
        }

        echo '</p>';

        if (!$tools->isIgnor($user['id'])
            && is_contact($user['id'], $db, $systemUser) != 2
            && !isset($systemUser->ban['1'])
            && !isset($systemUser->ban['3'])
        ) {
            echo '<p><form action="../mail/index.php?act=write&amp;id=' . $user['id'] . '" method="post"><input type="submit" value="' . _t('Write') . '" style="margin-left: 18px"/></form></p>';
        }

        echo '</div>';
    }
}

echo $view->render('system::app/legacy', [
    'title'   => $pageTitle,
    'content' => ob_get_clean(),
]);
