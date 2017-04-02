<?php

namespace Mobicms\Api;

interface ToolsInterface
{
    public function antiflood();

    public function checkout($string, $br, $tags);

    public function displayDate($var);

    public function displayError($error, $link = '');

    public function displayPagination($url, $start, $total, $kmess);

    public function displayPlace($user_id, $place, $headmod);

    public function displayUser($user, array $arg = []);

    public function getFlag($locale);

    public function getSkin();

    public function getUser($id);

    public function image($name, array $args);

    public function isIgnor($id);

    public function rusLat($str);

    public function smilies($string, $adm);

    public function timecount($var);

    public function trans($str); // DEPRECATED!!!
}
