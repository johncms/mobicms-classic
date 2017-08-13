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
 * @var int                           $id
 * @var array                         $set_forum
 *
 * @var PDO                           $db
 * @var Mobicms\Api\UserInterface     $systemUser
 * @var Mobicms\Checkpoint\UserConfig $userConfig
 */

$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;

if (($systemUser->rights != 3 && $systemUser->rights < 6) || !$id) {
    exit(_t('Access denied'));
}

$req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND (`type` = 't' OR `type` = 'm')");

if ($req->rowCount()) {
    $res = $req->fetch();
    $db->exec("UPDATE `forum` SET `close` = '0', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");

    if ($res['type'] == 't') {
        header('Location: ?id=' . $id);
    } else {
        $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $id . "'")->fetchColumn() / $userConfig->kmess);
        header('Location: ?id=' . $res['refid'] . '&page=' . $page);
    }
} else {
    header('Location: ?');
}
