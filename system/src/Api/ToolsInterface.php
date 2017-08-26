<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Api;

interface ToolsInterface
{
    public function antiflood();

    public function checkout($string, $br = 0, $tags = 0);

    public function displayDate($var);

    public function displayError($error, $link = '');

    public function displayPagination($url, $total, $listSize = null, $offset = null);

    public function displayPlace($place, $userId = 0);

    public function displayUser($user, array $arg = []);

    public function getFlag($locale);

    public function getPgStart($db = false);

    public function getSkin();

    public function getUser($id);

    public function isIgnor($id);

    public function rusLat($str);

    public function smilies($string, $adm = false);

    public function timecount($var);

    public function trans($str); // DEPRECATED!!!
}
