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

$pageTitle = _t('Mail');
ob_start();

echo '<div class="phdr"><h3>' . _t('Deleting messages') . '</h3></div>';

if ($id) {
    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Mobicms\Api\UserInterface $systemUser */
    $systemUser = $container->get(Mobicms\Api\UserInterface::class);

    /** @var Mobicms\Api\ToolsInterface $tools */
    $tools = $container->get(Mobicms\Api\ToolsInterface::class);

    //Проверяем наличие сообщения
    $req = $db->query("SELECT * FROM `cms_mail` WHERE (`user_id`='" . $systemUser->id . "' OR `from_id`='" . $systemUser->id . "') AND `id` = '$id' AND `delete`!='" . $systemUser->id . "' LIMIT 1");

    if (!$req->rowCount()) {
        exit(_t('Message does not exist'));
    }

    $res = $req->fetch();

    if (isset($_POST['submit'])) { //Если кнопка "Подвердить" нажата
        //Удаляем системное сообщение
        if ($res['sys']) {
            $db->exec("DELETE FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `id` = '$id' AND `sys`='1' LIMIT 1");
            echo '<div class="gmenu">' . _t('Message deleted') . '</div>';
            echo '<div class="bmenu"><a href="index.php?act=systems">' . _t('Back') . '</a></div>';
        } else {
            //Удаляем непрочитанное сообщение
            if ($res['read'] == 0 && $res['user_id'] == $systemUser->id) {

                //Удаляем файл
                if ($res['file_name']) {
                    @unlink(UPLOAD_PATH . 'mail/' . $res['file_name']);
                }

                $db->exec("DELETE FROM `cms_mail` WHERE `user_id`='" . $systemUser->id . "' AND `id` = '$id' LIMIT 1");
            } else {
                //Удаляем остальные сообщения
                if ($res['delete']) {

                    //Удаляем файл
                    if ($res['file_name']) {
                        @unlink(UPLOAD_PATH . 'mail/' . $res['file_name']);
                    }

                    $db->exec("DELETE FROM `cms_mail` WHERE (`user_id`='" . $systemUser->id . "' OR `from_id`='" . $systemUser->id . "') AND `id` = '$id' LIMIT 1");
                } else {
                    $db->exec("UPDATE `cms_mail` SET `delete` = '" . $systemUser->id . "' WHERE `id` = '$id' LIMIT 1");
                }
            }

            echo '<div class="gmenu">' . _t('Message deleted') . '</div>';
            echo '<div class="bmenu"><a href="index.php?act=write&amp;id=' . ($res['user_id'] == $systemUser->id ? $res['from_id'] : $res['user_id']) . '">' . _t('Back') . '</a></div>';
        }
    } else {
        echo '<div class="gmenu"><form action="index.php?act=delete&amp;id=' . $id . '" method="post"><div>
		' . _t('You really want to remove the message?') . '<br />
		<input type="submit" name="submit" value="' . _t('Delete') . '"/>
		</div></form></div>';
    }
} else {
    echo '<div class="rmenu">' . _t('The message for removal isn\'t chosen') . '</div>';
}

echo '<div class="bmenu"><a href="../profile/?act=office">' . _t('Personal') . '</a></div>';
