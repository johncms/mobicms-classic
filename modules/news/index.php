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

/** @var Psr\Http\Message\ServerRequestInterface $request */
$request = $container->get(Psr\Http\Message\ServerRequestInterface::class);
$queryParams = $request->getQueryParams();
$postParams = $request->getParsedBody();

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

/** @var League\Plates\Engine $view */
$view = $container->get(League\Plates\Engine::class);

$id = isset($queryParams['id']) ? abs(intval($queryParams['id'])) : 0;
$mod = $queryParams['mod'] ?? '';
$do = $queryParams['do'] ?? '';

ob_start();

switch ($do) {
    case 'add':
        // Добавление новости
        if ($systemUser->rights >= 6) {
            echo '<div class="phdr"><a href="index.php"><b>' . _t('News') . '</b></a> | ' . _t('Add') . '</div>';
            $old = 20;

            if (isset($postParams['submit'])) {
                $error = [];
                $name = isset($postParams['name']) ? htmlspecialchars(trim($postParams['name'])) : false;
                $text = isset($postParams['text']) ? trim($postParams['text']) : false;

                if (!$name) {
                    $error[] = _t('You have not entered news title');
                }

                if (!$text) {
                    $error[] = _t('You have not entered news text');
                }

                $flood = $tools->antiflood();

                if ($flood) {
                    $error[] = sprintf(_t('You cannot add the message so often. Please, wait %d seconds.'), $flood);
                }

                if (!$error) {
                    $rid = 0;

                    if (!empty($postParams['pf']) && ($postParams['pf'] != '0')) {
                        $pf = intval($postParams['pf']);
                        $rz = $postParams['rz'];
                        $pr = $db->query("SELECT * FROM `forum` WHERE `refid` = '$pf' AND `type` = 'r'");

                        while ($pr1 = $pr->fetch()) {
                            $arr[] = $pr1['id'];
                        }

                        foreach ($rz as $v) {
                            if (in_array($v, $arr)) {
                                $db->prepare('
                                  INSERT INTO `forum` SET
                                  `refid` = ?,
                                  `type` = \'t\',
                                  `time` = ?,
                                  `user_id` = ?,
                                  `from` = ?,
                                  `text` = ?,
                                  `soft` = \'\',
                                  `edit` = \'\',
                                  `curators` = \'\'
                                ')->execute([
                                    $v,
                                    time(),
                                    $systemUser->id,
                                    $systemUser->name,
                                    $name,
                                ]);

                                $rid = $db->lastInsertId();

                                $db->prepare('
                                  INSERT INTO `forum` SET
                                  `refid` = ?,
                                  `type` = \'m\',
                                  `time` = ?,
                                  `user_id` = ?,
                                  `from` = ?,
                                  `ip` = ?,
                                  `soft` = ?,
                                  `text` = ?,
                                  `edit` = \'\',
                                  `curators` = \'\'
                                ')->execute([
                                    $rid,
                                    time(),
                                    $systemUser->id,
                                    $systemUser->name,
                                    $request->getAttribute('ip'),
                                    $request->getAttribute('user_agent'),
                                    $text,
                                ]);
                            }
                        }
                    }

                    $db->prepare('
                      INSERT INTO `news` SET
                      `time` = ?,
                      `avt` = ?,
                      `name` = ?,
                      `text` = ?,
                      `kom` = ?
                    ')->execute([
                        time(),
                        $systemUser->name,
                        $name,
                        $text,
                        $rid,
                    ]);

                    $db->exec('UPDATE `users` SET `lastpost` = ' . time() . ' WHERE `id` = ' . $systemUser->id);
                    echo '<p>' . _t('News added') . '<br /><a href="index.php">' . _t('Back to news') . '</a></p>';
                } else {
                    echo $tools->displayError($error, '<a href="index.php">' . _t('Back to news') . '</a>');
                }
            } else {
                echo '<form action="index.php?do=add" method="post"><div class="menu">' .
                    '<p><h3>' . _t('Title') . '</h3>' .
                    '<input type="text" name="name"/></p>' .
                    '<p><h3>' . _t('Text') . '</h3>' .
                    '<textarea rows="' . $userConfig->fieldHeight . '" name="text"></textarea></p>' .
                    '<p><h3>' . _t('Discussion') . '</h3>';
                $fr = $db->query("SELECT * FROM `forum` WHERE `type` = 'f'");
                echo '<input type="radio" name="pf" value="0" checked="checked" />' . _t('Do not discuss') . '<br />';

                while ($fr1 = $fr->fetch()) {
                    echo '<input type="radio" name="pf" value="' . $fr1['id'] . '"/>' . $fr1['text'] . '<select name="rz[]">';
                    $pr = $db->query("SELECT * FROM `forum` WHERE `type` = 'r' AND `refid` = '" . $fr1['id'] . "'");

                    while ($pr1 = $pr->fetch()) {
                        echo '<option value="' . $pr1['id'] . '">' . $pr1['text'] . '</option>';
                    }
                    echo '</select><br>';
                }

                echo '</p></div><div class="bmenu">' .
                    '<input type="submit" name="submit" value="' . _t('Save') . '"/>' .
                    '</div></form>' .
                    '<p><a href="index.php">' . _t('Back to news') . '</a></p>';
            }
        }
        break;

    case 'edit':
        // Редактирование новости
        if ($systemUser->rights >= 6) {
            echo '<div class="phdr"><a href="index.php"><b>' . _t('News') . '</b></a> | ' . _t('Edit') . '</div>';

            if (!$id) {
                exit(_t('Wrong data'));
            }

            if (isset($postParams['submit'])) {
                $error = [];

                if (empty($postParams['name'])) {
                    $error[] = _t('You have not entered news title');
                }

                if (empty($postParams['text'])) {
                    $error[] = _t('You have not entered news text');
                }

                $name = htmlspecialchars(trim($postParams['name']));
                $text = trim($postParams['text']);

                if (!$error) {
                    $db->prepare('
                      UPDATE `news` SET
                      `name` = ?,
                      `text` = ?
                      WHERE `id` = ?
                    ')->execute([
                        $name,
                        $text,
                        $id,
                    ]);
                } else {
                    echo $tools->displayError($error, '<a href="index.php?act=edit&amp;id=' . $id . '">' . _t('Repeat') . '</a>');
                }
                echo '<p>' . _t('Article changed') . '<br /><a href="index.php">' . _t('Continue') . '</a></p>';
            } else {
                $res = $db->query("SELECT * FROM `news` WHERE `id` = '$id'")->fetch();

                echo '<div class="menu"><form action="index.php?do=edit&amp;id=' . $id . '" method="post">' .
                    '<p><h3>' . _t('Title') . '</h3>' .
                    '<input type="text" name="name" value="' . $res['name'] . '"/></p>' .
                    '<p><h3>' . _t('Text') . '</h3>' .
                    '<textarea rows="' . $userConfig->fieldHeight . '" name="text">' . htmlentities($res['text'], ENT_QUOTES, 'UTF-8') . '</textarea></p>' .
                    '<p><input type="submit" name="submit" value="' . _t('Save') . '"/></p>' .
                    '</form></div>' .
                    '<div class="phdr"><a href="index.php">' . _t('Back to news') . '</a></div>';
            }
        }
        break;

    case 'clean':
        // Чистка новостей
        if ($systemUser->rights >= 7) {
            echo '<div class="phdr"><a href="index.php"><b>' . _t('News') . '</b></a> | ' . _t('Clear') . '</div>';

            if (isset($postParams['submit'])) {
                $cl = isset($postParams['cl']) ? intval($postParams['cl']) : '';

                switch ($cl) {
                    case '1':
                        // Чистим новости, старше 1 недели
                        $db->query("DELETE FROM `news` WHERE `time` <= " . (time() - 604800));
                        $db->query("OPTIMIZE TABLE `news`");

                        echo '<p>' . _t('Delete all news older than 1 week') . '</p><p><a href="index.php">' . _t('Back to news') . '</a></p>';
                        break;

                    case '2':
                        // Проводим полную очистку
                        $db->query("TRUNCATE TABLE `news`");

                        echo '<p>' . _t('Delete all news') . '</p><p><a href="index.php">' . _t('Back to news') . '</a></p>';
                        break;
                    default :
                        // Чистим сообщения, старше 1 месяца
                        $db->query("DELETE FROM `news` WHERE `time` <= " . (time() - 2592000));
                        $db->query("OPTIMIZE TABLE `news`;");

                        echo '<p>' . _t('Delete all news older than 1 month') . '</p><p><a href="index.php">' . _t('Back to news') . '</a></p>';
                }
            } else {
                echo '<div class="menu"><form id="clean" method="post" action="index.php?do=clean">' .
                    '<p><h3>' . _t('Clearing parameters') . '</h3>' .
                    '<input type="radio" name="cl" value="0" checked="checked" />' . _t('Older than 1 month') . '<br />' .
                    '<input type="radio" name="cl" value="1" />' . _t('Older than 1 week') . '<br />' .
                    '<input type="radio" name="cl" value="2" />' . _t('Clear all') . '</p>' .
                    '<p><input type="submit" name="submit" value="' . _t('Clear') . '" /></p>' .
                    '</form></div>' .
                    '<div class="phdr"><a href="index.php">' . _t('Cancel') . '</a></div>';
            }
        }
        break;

    case 'del':
        // Удаление новости
        if ($systemUser->rights >= 6) {
            echo '<div class="phdr"><a href="index.php"><b>' . _t('News') . '</b></a> | ' . _t('Delete') . '</div>';

            if (isset($queryParams['yes'])) {
                $db->query("DELETE FROM `news` WHERE `id` = '$id'");

                echo '<p>' . _t('Article deleted') . '<br><a href="index.php">' . _t('Back to news') . '</a></p>';
            } else {
                echo '<p>' . _t('Do you really want to delete?') . '<br>' .
                    '<a href="index.php?do=del&amp;id=' . $id . '&amp;yes">' . _t('Delete') . '</a> | <a href="index.php">' . _t('Cancel') . '</a></p>';
            }
        }
        break;

    default:
        // Вывод списка новостей
        echo '<div class="phdr"><b>' . _t('News') . '</b></div>';

        if ($systemUser->rights >= 6) {
            echo '<div class="topmenu"><a href="index.php?do=add">' . _t('Add') . '</a> | <a href="index.php?do=clean">' . _t('Clear') . '</a></div>';
        }

        $total = $db->query("SELECT COUNT(*) FROM `news`")->fetchColumn();
        $req = $db->query("SELECT * FROM `news` ORDER BY `time` DESC" . $tools->getPgStart(true));
        $i = 0;

        while ($res = $req->fetch()) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            $text = $tools->checkout($res['text'], 1, 1);
            $text = $tools->smilies($text, 1);
            echo '<h3>' . $res['name'] . '</h3>' .
                '<span class="gray"><small>' . _t('Author') . ': ' . $res['avt'] . ' (' . $tools->displayDate($res['time']) . ')</small></span>' .
                '<br />' . $text . '<div class="sub">';

            if ($res['kom'] != 0 && $res['kom'] != "") {
                $komm = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '" . $res['kom'] . "'")->fetchColumn();

                if ($komm >= 0) {
                    echo '<a href="../forum/?id=' . $res['kom'] . '">' . _t('Discuss in Forum') . ' (' . $komm . ')</a><br>';
                }
            }

            if ($systemUser->rights >= 6) {
                echo '<a href="index.php?do=edit&amp;id=' . $res['id'] . '">' . _t('Edit') . '</a> | ' .
                    '<a href="index.php?do=del&amp;id=' . $res['id'] . '">' . _t('Delete') . '</a>';
            }

            echo '</div></div>';
            ++$i;
        }
        echo '<div class="phdr">' . _t('Total') . ':&#160;' . $total . '</div>';

        if ($total > $userConfig->kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?', $total) . '</div>' .
                '<p><form action="index.php" method="post">' .
                '<input type="text" name="page" size="2"/>' .
                '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
        }
}

echo $view->render('system::app/legacy', [
    'title'   => _t('News'),
    'content' => ob_get_clean(),
]);
