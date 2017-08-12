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

// Каталог Админских Смайлов
if ($systemUser->rights < 1) {
    exit(_t('Wrong data'));
}

echo '<div class="phdr"><a href="?act=smilies"><b>' . _t('Smilies') . '</b></a> | ' . _t('For administration') . '</div>';
$user_sm = unserialize($systemUser->smileys);

if (!is_array($user_sm)) {
    $user_sm = [];
}

echo '<div class="topmenu"><a href="?act=my_smilies">' . _t('My smilies') . '</a>  (' . count($user_sm) . ' / ' . $user_smileys . ')</div>' .
    '<form action="?act=set_my_sm&amp;start=' . $start . '&amp;adm" method="post">';
$array = [];
$dir = opendir(ROOT_PATH . 'assets/smilies/admin');

while (($file = readdir($dir)) !== false) {
    if (($file != '.') && ($file != "..") && ($file != "name.dat") && ($file != ".svn") && ($file != "index.php")) {
        $array[] = $file;
    }
}

closedir($dir);
$total = count($array);

if ($total > 0) {
    $end = $start + $userConfig->kmess;

    if ($end > $total) {
        $end = $total;
    }

    for ($i = $start; $i < $end; $i++) {
        $smile = preg_replace('#^(.*?).(gif|jpg|png)$#isU', '$1', $array[$i], 1);
        echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
        $smileys = (in_array($smile, $user_sm) ? ''
            : '<input type="checkbox" name="add_sm[]" value="' . $smile . '" />&#160;');
        echo $smileys . '<img src="../assets/smilies/admin/' . $array[$i] . '" alt="" /> - :' . $smile . ': ' . _t('or') . ' :' . $tools->trans($smile) . ':</div>';
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '<div class="gmenu"><input type="submit" name="add" value=" ' . _t('Add') . ' "/></div></form>';
echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $userConfig->kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=admsmilies&amp;', $total) . '</div>';
    echo '<p><form action="?act=admsmilies" method="post">' .
        '<input type="text" name="page" size="2"/>' .
        '<input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
}

echo '<p><a href="' . $_SESSION['ref'] . '">' . _t('Back') . '</a></p>';
