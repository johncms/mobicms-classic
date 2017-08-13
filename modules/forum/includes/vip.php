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
 * @var int                       $id
 * @var array                     $queryParams
 *
 * @var PDO                       $db
 * @var Mobicms\Api\UserInterface $systemUser
 */

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    if (!$id) {
        exit(_t('Wrong data'));
    }

    if ($db->query("SELECT COUNT(*) FROM `forum` WHERE `id` = '" . $id . "' AND `type` = 't'")->fetchColumn()) {
        $db->exec("UPDATE `forum` SET  `vip` = '" . (isset($queryParams['vip']) ? '1' : '0') . "' WHERE `id` = '$id'");
        header('Location: ?id=' . $id);
    } else {
        exit(_t('Wrong data'));
    }
}
