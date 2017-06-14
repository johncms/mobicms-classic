<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms;

use Mobicms\Http\Request;
use Psr\Container\ContainerInterface;

class Environment implements Api\EnvironmentInterface
{
    private $ipCount = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $this->ipLog(ip2long($container->get(Request::class)->ip()));

        return $this;
    }

    public function getIpLog()
    {
        return $this->ipCount;
    }

    private function ipLog($ip)
    {
        $file = CACHE_PATH . 'ip_flood.dat';
        $tmp = [];
        $requests = 1;

        if (!file_exists($file)) {
            $in = fopen($file, "w+");
        } else {
            $in = fopen($file, "r+");
        }

        flock($in, LOCK_EX) or die("Cannot flock ANTIFLOOD file.");
        $now = time();

        while ($block = fread($in, 8)) {
            $arr = unpack("Lip/Ltime", $block);

            if (($now - $arr['time']) > 60) {
                continue;
            }

            if ($arr['ip'] == $ip) {
                $requests++;
            }

            $tmp[] = $arr;
            $this->ipCount[] = $arr['ip'];
        }

        fseek($in, 0);
        ftruncate($in, 0);

        for ($i = 0; $i < count($tmp); $i++) {
            fwrite($in, pack('LL', $tmp[$i]['ip'], $tmp[$i]['time']));
        }

        fwrite($in, pack('LL', $ip, $now));
        fclose($in);
    }
}
