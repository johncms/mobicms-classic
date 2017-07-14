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

/** @var Mobicms\Api\ConfigInterface $config */
$config = $container->get(Mobicms\Api\ConfigInterface::class);

echo '</div><div class="fmenu">';
echo '<div><a href=\'' . $config->homeurl . '\'>' . $tools->image('images/menu_home.png') . _t('Home', 'system') . '</a></div>';
echo '<div>' . $container->get('counters')->online() . '</div>' .
    '</div>' .
    '<div style="text-align:center">' .
    '<p><b>' . $config->copyright . '</b></p>';

// Счетчики каталогов
$req = $db->query('SELECT * FROM `cms_counters` WHERE `switch` = 1 ORDER BY `sort` ASC');

if ($req->rowCount()) {
    while ($res = $req->fetch()) {
        $link1 = ($res['mode'] == 1 || $res['mode'] == 2) ? $res['link1'] : $res['link2'];
        $link2 = $res['mode'] == 2 ? $res['link1'] : $res['link2'];
        $count = $link1; //TODO: доработать показ на главной-остальных страницах

        if (!empty($count)) {
            echo $count;
        }
    }
}

/*
-----------------------------------------------------------------
ВНИМАНИЕ!!!
Данный копирайт нельзя убирать в течение 90 дней с момента установки скриптов
-----------------------------------------------------------------
ATTENTION!!!
The copyright could not be removed within 90 days of installation scripts
-----------------------------------------------------------------
*/
echo '<div><small>&copy; <a href="https://mobicms.org">mobiCMS</a></small></div>' .
    '</div></body></html>';
