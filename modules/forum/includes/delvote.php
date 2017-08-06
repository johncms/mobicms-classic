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

/** @var Psr\Http\Message\ServerRequestInterface $request */
$request = $container->get(Psr\Http\Message\ServerRequestInterface::class);
$queryParams = $request->getQueryParams();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    $topic_vote = $db->query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic` = '$id'")->fetchColumn();
    require ROOT_PATH . 'system/head.php';

    if ($topic_vote == 0) {
        echo $tools->displayError(_t('Wrong data'));
        require ROOT_PATH . 'system/end.php';
        exit;
    }

    if (isset($queryParams['yes'])) {
        $db->exec("DELETE FROM `cms_forum_vote` WHERE `topic` = '$id'");
        $db->exec("DELETE FROM `cms_forum_vote_users` WHERE `topic` = '$id'");
        $db->exec("UPDATE `forum` SET  `realid` = '0'  WHERE `id` = '$id'");
        echo _t('Poll deleted') . '<br /><a href="' . $_SESSION['prd'] . '">' . _t('Continue') . '</a>';
    } else {
        echo '<p>' . _t('Do you really want to delete a poll?') . '</p>';
        echo '<p><a href="?act=delvote&amp;id=' . $id . '&amp;yes">' . _t('Delete') . '</a><br />';
        echo '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Cancel') . '</a></p>';
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
    }
}
