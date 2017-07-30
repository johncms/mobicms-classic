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

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var Mobicms\Checkpoint\UserConfig $userConfig */
$userConfig = $systemUser->getConfig();

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);
$start = $tools->getPgStart();

// Список своих смайлов
echo '<div class="phdr"><a href="?act=smilies"><b>' . _t('Smilies') . '</b></a> | ' . _t('My smilies') . '</div>';
$smileys = !empty($systemUser->smileys) ? unserialize($systemUser->smileys) : [];
$total = count($smileys);

if ($total) {
    echo '<form action="?act=set_my_sm&amp;start=' . $start . '" method="post">';
}

if ($total > $userConfig->kmess) {
    $smileys = array_chunk($smileys, $userConfig->kmess, true);

    if ($start) {
        $key = ($start - $start % $userConfig->kmess) / $userConfig->kmess;
        $smileys_view = $smileys[$key];

        if (!count($smileys_view)) {
            $smileys_view = $smileys[0];
        }

        $smileys = $smileys_view;
    } else {
        $smileys = $smileys[0];
    }
}

$i = 0;

foreach ($smileys as $value) {
    $smile = ':' . $value . ':';
    echo ($i % 2 ? '<div class="list2">' : '<div class="list1">') .
        '<input type="checkbox" name="delete_sm[]" value="' . $value . '" />&#160;' .
        $tools->smilies($smile, $systemUser->rights >= 1 ? 1 : 0) . '&#160;' . $smile . ' ' . _t('or') . ' ' . $tools->trans($smile) . '</div>';
    $i++;
}

if ($total) {
    echo '<div class="rmenu"><input type="submit" name="delete" value=" ' . _t('Delete') . ' "/></div></form>';
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '<br /><a href="?act=smilies">' . _t('Add Smilies') . '</a></p></div>';
}

echo '<div class="phdr">' . _t('Total') . ': ' . $total . ' / ' . $user_smileys . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=my_smileys&amp;', $total) . '</div>';
}

echo '<p><a href="' . $_SESSION['ref'] . '">' . _t('Back') . '</a></p>';
