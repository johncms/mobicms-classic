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

/** @var Mobicms\Http\Response $response */
$response = $container->get(Mobicms\Http\Response::class);

/** @var Mobicms\Api\ToolsInterface $tools */
$tools = $container->get(Mobicms\Api\ToolsInterface::class);

if (empty($_GET['n'])) {
    require ROOT_PATH . 'system/head.php';
    echo $tools->displayError(_t('Wrong data'));
    require ROOT_PATH . 'system/end.php';
    exit;
}

$n = trim($_GET['n']);
$o = opendir("../uploads/forum/topics");

while ($f = readdir($o)) {
    if ($f != "." && $f != ".." && $f != "index.php" && $f != ".htaccess") {
        $ff = pathinfo($f, PATHINFO_EXTENSION);
        $f1 = str_replace(".$ff", "", $f);
        $a[] = $f;
        $b[] = $f1;
    }
}

$tt = count($a);

if (!in_array($n, $b)) {
    require ROOT_PATH . 'system/head.php';
    echo $tools->displayError(_t('Wrong data'));
    require ROOT_PATH . 'system/end.php';
    exit;
}

for ($i = 0; $i < $tt; $i++) {
    $tf = pathinfo($a[$i], PATHINFO_EXTENSION);
    $tf1 = str_replace(".$tf", "", $a[$i]);
    if ($n == $tf1) {
        $response->redirect("../uploads/forum/topics/$n.$tf")->sendHeaders();
    }
}
