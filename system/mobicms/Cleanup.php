<?php

namespace Mobicms;

class Cleanup
{
    public function __construct(\PDO $db)
    {
        $db->exec('DELETE FROM `cms_sessions` WHERE `lastdate` < ' . (time() - 86400));
        $db->exec("DELETE FROM `cms_users_iphistory` WHERE `time` < " . (time() - 7776000));
        $db->query('OPTIMIZE TABLE `cms_sessions`, `cms_users_iphistory`, `cms_mail`, `cms_contact`');

        return true;
    }
}
