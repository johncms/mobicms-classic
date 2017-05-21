<?php

defined('MOBICMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

if (($systemUser->rights != 3 && $systemUser->rights < 6) || !$id) {
    echo _t('Access denied');
    require('../system/end.php');
    exit;
}

$req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND (`type` = 't' OR `type` = 'm')");

if ($req->rowCount()) {
    $res = $req->fetch();
    $db->exec("UPDATE `forum` SET `close` = '0', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");

    if ($res['type'] == 't') {
        header('Location: index.php?id=' . $id);
    } else {
        $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $id . "'")->fetchColumn() / $kmess);
        header('Location: index.php?id=' . $res['refid'] . '&page=' . $page);
    }
} else {
    header('Location: index.php');
}
