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

/**
 * Interface UserInterface
 *
 * @package Mobicms\Api
 *
 * @property $id
 * @property $name
 * @property $name_lat
 * @property $password
 * @property $rights
 * @property $failed_login
 * @property $imname
 * @property $sex
 * @property $komm
 * @property $postforum
 * @property $postguest
 * @property $yearofbirth
 * @property $datereg
 * @property $lastdate
 * @property $mail
 * @property $icq
 * @property $skype
 * @property $jabber
 * @property $www
 * @property $about
 * @property $live
 * @property $mibile
 * @property $status
 * @property $ip
 * @property $ip_via_proxy
 * @property $browser
 * @property $preg
 * @property $regadm
 * @property $mailvis
 * @property $dayb
 * @property $monthb
 * @property $sestime
 * @property $total_on_site
 * @property $lastpost
 * @property $rest_code
 * @property $rest_time
 * @property $movings
 * @property $place
 * @property $set_user
 * @property $set_forum
 * @property $set_mail
 * @property $karma_plus
 * @property $karma_minus
 * @property $karma_time
 * @property $karma_off
 * @property $comm_count
 * @property $comm_old
 * @property $smileys
 * @property $ban
 */
interface UserInterface
{
    public function isValid();

    public function getConfig();
}
