ALTER TABLE `cms_sessions` CHANGE `ip` `ip` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `cms_sessions` CHANGE `ip_via_proxy` `ip_via_proxy` VARCHAR(20) NOT NULL DEFAULT '';

ALTER TABLE `cms_users_iphistory` CHANGE `ip` `ip` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `cms_users_iphistory` CHANGE `ip_via_proxy` `ip_via_proxy` VARCHAR(20) NOT NULL DEFAULT '';

ALTER TABLE `forum` CHANGE `ip` `ip` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `forum` CHANGE `ip_via_proxy` `ip_via_proxy` VARCHAR(20) NOT NULL DEFAULT '';

ALTER TABLE `guest` CHANGE `ip` `ip` VARCHAR(20) NOT NULL DEFAULT '';

ALTER TABLE `users` CHANGE `ip` `ip` VARCHAR(20) NOT NULL DEFAULT '';
ALTER TABLE `users` CHANGE `ip_via_proxy` `ip_via_proxy` VARCHAR(20) NOT NULL DEFAULT '';
