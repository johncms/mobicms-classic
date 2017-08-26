<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Checkpoint;

use Mobicms\Api\UserInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class UserStat
{
    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var UserInterface
     */
    private $systemUser;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get(\PDO::class);
        $this->request = $container->get(ServerRequestInterface::class);
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

        if ($this->systemUser->lastdate < (time() - 300)) {
            $sql .= " `sestime` = " . time() . ", ";
        }

        $place = $this->formatPlace();
        if ($this->systemUser->place != $place) {
            $this->systemUser->offsetSet('place', $place);
            $sql .= " `place` = " . $this->db->quote($place) . ", ";
        }

        if ($this->systemUser->ip != $this->request->getAttribute('ip')
            || $this->systemUser->ip_via_proxy != $this->request->getAttribute('ip_via_proxy')
        ) {
            $sql .= " `ip` = " . $this->db->quote($this->request->getAttribute('ip')) . ", `ip_via_proxy` = " . $this->db->quote($this->request->getAttribute('ip_via_proxy')) . ", ";
        }

        if ($this->systemUser->browser != $this->request->getAttribute('user_agent')) {
            $sql .= " `browser` = " . $this->db->quote($this->request->getAttribute('user_agent')) . ", ";
        }

        $totalonsite = $this->systemUser->total_on_site;

        if ($this->systemUser->lastdate > (time() - 300)) {
            $totalonsite = $totalonsite + time() - $this->systemUser->lastdate;
        }

        $this->db->query("UPDATE `users` SET
          $sql
          `total_on_site` = '$totalonsite',
          `lastdate` = '" . time() . "'
          WHERE `id` = " . $this->systemUser->id);
    }

    private function processGuest()
    {
        $sql = '';
        $session = md5($this->request->getAttribute('ip') . $this->request->getAttribute('ip_via_proxy') . $this->request->getAttribute('user_agent'));
        $req = $this->db->query("SELECT * FROM `cms_sessions` WHERE `session_id` = " . $this->db->quote($session) . " LIMIT 1");

        if ($req->rowCount()) {
            // Если есть в базе, то обновляем данные
            $res = $req->fetch();

            if ($res['sestime'] < (time() - 300)) {
                $sql .= " `sestime` = '" . time() . "', ";
            }

            $place = $this->formatPlace();
            $this->systemUser->offsetSet('place', $place);

            if ($res['place'] != $place) {
                $sql .= " `place` = " . $this->db->quote($place) . ", ";
            }

            $this->db->exec("UPDATE `cms_sessions` SET $sql
            `lastdate` = '" . time() . "'
            WHERE `session_id` = " . $this->db->quote($session) . "
        ");
        } else {
            // Если еще небыло в базе, то добавляем запись
            $this->db->exec("INSERT INTO `cms_sessions` SET
            `session_id` = '" . $session . "',
            `ip` = '" . $this->request->getAttribute('ip') . "',
            `ip_via_proxy` = '" . $this->request->getAttribute('ip_via_proxy') . "',
            `browser` = " . $this->db->quote($this->request->getAttribute('user_agent')) . ",
            `lastdate` = '" . time() . "',
            `sestime` = '" . time() . "',
            `place` = " . $this->db->quote($this->formatPlace()) . "
        ");
        }
    }

    private function formatPlace()
    {
        $path = trim(str_ireplace('index.php', '', $this->request->getUri()->getPath()), '/');
        $queryParams = $this->request->getQueryParams();
        $act = $queryParams['act'] ?? '';
        $id = $queryParams['id'] ?? '';

        $query = [];

        if (!empty($act)) {
            $query[] = 'act=' . substr($act, 0, 15);
        }

        if (!empty($id)) {
            $query[] = 'id=' . abs(intval($id));
        }

        return '/' . $path . (!empty($query) ? '?' . implode('&', $query) : '');
    }
}
