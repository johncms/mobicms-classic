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

use Psr\Container\ContainerInterface;

class Clean
{
    private $cacheFile = CACHE_PATH . 'cleanup.cache';

    public function __construct(ContainerInterface $container)
    {
        if (!file_exists($this->cacheFile) || filemtime($this->cacheFile) < (time() - 86400)) {
            /** @var \PDO $db */
            $db = $container->get(\PDO::class);

            $db->exec('DELETE FROM `cms_sessions` WHERE `lastdate` < ' . (time() - 86400));
            $db->query('OPTIMIZE TABLE `cms_sessions`, `cms_mail`, `cms_contact`');
            file_put_contents($this->cacheFile, time());
        }
    }
}
