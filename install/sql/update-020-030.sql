DROP TABLE ` cms_ads`;
DROP TABLE `karma_users`;
DROP TABLE ` cms_users_iphistory`;
ALTER TABLE `users` DROP `karma_off`;
ALTER TABLE `users` DROP `karma_time`;
ALTER TABLE `users` DROP `karma_minus`;
ALTER TABLE `users` DROP `karma_plus`;
# 29.07.2017
ALTER TABLE `users` DROP `movings`;
ALTER TABLE `cms_sessions` DROP `movings`;
ALTER TABLE `cms_sessions` DROP INDEX `place`, ADD INDEX `place` (`place`) USING BTREE;
UPDATE `users` SET `place`='';
