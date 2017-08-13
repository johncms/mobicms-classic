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
 * @var Mobicms\Api\ToolsInterface              $tools
 * @var League\Plates\Engine                    $view
 */

$postParams = $request->getParsedBody();

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    // Массовое удаление выбранных постов форума
    ob_start();

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
            echo $view->render('system::app/legacy', [
                'title'   => _t('Forum'),
                'content' => $tools->displayError(_t('You did not choose something to delete'), '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Back') . '</a>'),
            ]);
            exit;
        }

        $dc = [];

        foreach ($postParams['delch'] as $v) {
            $dc[] = intval($v);
        }

        $_SESSION['dc'] = $dc;
        $_SESSION['prd'] = htmlspecialchars(getenv("HTTP_REFERER"));
        echo '<p>' . _t('Do you really want to delete?') . '<br><a href="index.php?act=massdel&amp;yes">' . _t('Delete') . '</a> | ' .
            '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Cancel') . '</a></p>';
    }
}
