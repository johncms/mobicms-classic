<?php

define('MOBICMS', 1);

require('system/bootstrap.php');

$act = isset($_GET['act']) ? trim($_GET['act']) : '';


if (isset($_SESSION['ref'])) {
    unset($_SESSION['ref']);
}

if (isset($_GET['err'])) {
    $act = 404;
}

switch ($act) {
    case '404':
        /** @var Mobicms\Api\ToolsInterface $tools */
        $tools = App::getContainer()->get(Mobicms\Api\ToolsInterface::class);

        $headmod = 'error404';
        require('system/head.php');
        echo $tools->displayError(_t('The requested page does not exists'));
        break;

    default:
        // Главное меню сайта
        if (isset($_SESSION['ref'])) {
            unset($_SESSION['ref']);
        }
        $headmod = 'mainpage';
        require('system/head.php');
        include 'system/mainmenu.php';
}

require('system/end.php');
