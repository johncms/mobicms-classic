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

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    // Массовое удаление выбранных постов форума
    require ROOT_PATH . 'system/head.php';

    if (isset($queryParams['yes'])) {
        $dc = $_SESSION['dc'];
        $prd = $_SESSION['prd'];

        if (!empty($dc)) {
            $db->exec("UPDATE `forum` SET
                `close` = '1',
                `close_who` = '" . $systemUser->name . "'
                WHERE `id` IN (" . implode(',', $dc) . ")
            ");
        }

        echo _t('Marked posts are deleted') . '<br><a href="' . $prd . '">' . _t('Back') . '</a><br>';
    } else {
        if (empty($postParams['delch'])) {
            echo '<p>' . _t('You did not choose something to delete') . '<br><a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Back') . '</a></p>';
            require ROOT_PATH . 'system/end.php';
            exit;
        }

        foreach ($postParams['delch'] as $v) {
            $dc[] = intval($v);
        }

        $_SESSION['dc'] = $dc;
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
        echo '<p>' . _t('Do you really want to delete?') . '<br><a href="index.php?act=massdel&amp;yes">' . _t('Delete') . '</a> | ' .
            '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Cancel') . '</a></p>';
    }
}
