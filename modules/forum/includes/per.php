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
 * @var int                                     $id
 * @var array                                   $queryParams
 *
 * @var PDO                                     $db
 * @var Psr\Http\Message\ServerRequestInterface $request
 * @var Mobicms\Api\UserInterface               $systemUser
 */

$postParams = $request->getParsedBody();

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    if (!$id) {
        exit(_t('Wrong data'));
    }

    $typ = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'");

    if (!$typ->rowCount()) {
        exit(_t('Wrong data'));
    }

    if (isset($postParams['submit'])) {
        $razd = isset($postParams['razd']) ? abs(intval($postParams['razd'])) : false;

        if (!$razd) {
            exit(_t('Wrong data'));
        }

        $typ1 = $db->query("SELECT * FROM `forum` WHERE `id` = '$razd' AND `type` = 'r'");

        if (!$typ1->rowCount()) {
            exit(_t('Wrong data'));
        }

        $db->exec("UPDATE `forum` SET
            `refid` = '$razd'
            WHERE `id` = '$id'
        ");
        header('Location: ?id=' . $id);
    } else {
        // Перенос темы
        $ms = $typ->fetch();
        ob_start();

        if (empty($queryParams['other'])) {
            $rz1 = $db->query("SELECT * FROM `forum` WHERE id='" . $ms['refid'] . "'")->fetch();
            $other = $rz1['refid'];
        } else {
            $other = intval($queryParams['other']);
        }

        $fr1 = $db->query("SELECT * FROM `forum` WHERE id='" . $other . "'")->fetch();
        echo '<div class="phdr"><a href="index.php?id=' . $id . '"><b>' . _t('Forum') . '</b></a> | ' . _t('Move Topic') . '</div>' .
            '<form action="index.php?act=per&amp;id=' . $id . '" method="post">' .
            '<div class="gmenu"><p>' .
            '<h3>' . _t('Category') . '</h3>' . $fr1['text'] . '</p>' .
            '<p><h3>' . _t('Section') . '</h3>' .
            '<select name="razd">';
        $raz = $db->query("SELECT * FROM `forum` WHERE `refid` = '$other' AND `type` = 'r' AND `id` != '" . $ms['refid'] . "' ORDER BY `realid` ASC");

        while ($raz1 = $raz->fetch()) {
            echo '<option value="' . $raz1['id'] . '">' . $raz1['text'] . '</option>';
        }

        echo '</select></p>' .
            '<p><input type="submit" name="submit" value="' . _t('Move') . '"/></p>' .
            '</div></form>' .
            '<div class="phdr">' . _t('Other categories') . '</div>';
        $frm = $db->query("SELECT * FROM `forum` WHERE `type` = 'f' AND `id` != '$other' ORDER BY `realid` ASC");
        $i = 0;
        while ($frm1 = $frm->fetch()) {
            echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
            echo '<a href="index.php?act=per&amp;id=' . $id . '&amp;other=' . $frm1['id'] . '">' . $frm1['text'] . '</a></div>';
            ++$i;
        }

        echo '<div class="phdr"><a href="index.php">' . _t('Back') . '</a></div>';
    }
}
