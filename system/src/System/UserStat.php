<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\System;

use Mobicms\Api\UserInterface;
use Mobicms\Http\Request;
use Psr\Container\ContainerInterface;

class UserStat
{
    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var UserInterface
     */
    private $systemUser;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get(\PDO::class);
        $this->request = $container->get(Request::class);
        $this->systemUser = $container->get(UserInterface::class);

        if ($this->systemUser->isValid()) {
            $this->processUser();
        } else {
            $this->processGuest();
        }
    }

    private function processUser()
    {
        $sql = '';
        $movings = $this->systemUser->movings;

        if ($this->systemUser->lastdate < (time() - 300)) {
            $movings = 0;
            $sql .= " `sestime` = " . time() . ", ";
        }

        //if ($this->systemUser->place != $headmod) {
        //    ++$movings;
        //    $sql .= " `place` = " . $this->db->quote($headmod) . ", ";
        //}

        if ($this->systemUser->ip != $this->request->ip()
            || $this->systemUser->ip_via_proxy != $this->request->ipViaProxy()
        ) {
            $sql .= " `ip` = " . $this->db->quote($this->request->ip())
                . ", `ip_via_proxy` = " . $this->db->quote($this->request->ipViaProxy()) . ", ";
        }

        if ($this->systemUser->browser != $this->request->userAgent()) {
            $sql .= " `browser` = " . $this->db->quote($this->request->userAgent()) . ", ";
        }

        $totalonsite = $this->systemUser->total_on_site;

        if ($this->systemUser->lastdate > (time() - 300)) {
            $totalonsite = $totalonsite + time() - $this->systemUser->lastdate;
        }

        $this->db->query("UPDATE `users` SET
          $sql
          `movings` = '$movings',
          `total_on_site` = '$totalonsite',
          `lastdate` = '" . time() . "'
          WHERE `id` = " . $this->systemUser->id);
    }

    private function processGuest()
    {
        $sql = '';
        $movings = 0;
        $session = md5($this->request->ip() . $this->request->ipViaProxy() . $this->request->userAgent());
        $req = $this->db->query("SELECT * FROM `cms_sessions` WHERE `session_id` = " . $this->db->quote($session) . " LIMIT 1");

        if ($req->rowCount()) {
            // Если есть в базе, то обновляем данные
            $res = $req->fetch();
            $movings = ++$res['movings'];

            if ($res['sestime'] < (time() - 300)) {
                $movings = 1;
                $sql .= " `sestime` = '" . time() . "', ";
            }

            //if ($res['place'] != $headmod) {
            //    $sql .= " `place` = " . $db->quote($headmod) . ", ";
            //}

            $this->db->exec("UPDATE `cms_sessions` SET $sql
            `movings` = '$movings',
            `lastdate` = '" . time() . "'
            WHERE `session_id` = " . $this->db->quote($session) . "
        ");
        } else {
            // Если еще небыло в базе, то добавляем запись
            $this->db->exec("INSERT INTO `cms_sessions` SET
            `session_id` = '" . $session . "',
            `ip` = '" . $this->request->ip() . "',
            `ip_via_proxy` = '" . $this->request->ipViaProxy() . "',
            `browser` = " . $this->db->quote($this->request->userAgent()) . ",
            `lastdate` = '" . time() . "',
            `sestime` = '" . time() . "',
            `place` = " . $this->db->quote('') . "
        ");
        }
    }
}
