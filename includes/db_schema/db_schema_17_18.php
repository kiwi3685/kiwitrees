<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

self::exec(
	"CREATE TABLE IF NOT EXISTS `##site_access_rule` (".
	" site_access_rule_id INTEGER          NOT NULL AUTO_INCREMENT,".
	" ip_address_start     INTEGER UNSIGNED NOT NULL DEFAULT 0,".
	" ip_address_end       INTEGER UNSIGNED NOT NULL DEFAULT 4294967295,".
	" user_agent_pattern   VARCHAR(255)     NOT NULL,".
	" rule                 ENUM('allow', 'deny', 'robot', 'unknown') NOT NULL DEFAULT 'unknown',".
	" comment              VARCHAR(255)     NOT NULL,".
	" updated              TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,".
	" PRIMARY KEY     (site_access_rule_id),".
	" UNIQUE  KEY ix1 (user_agent_pattern, ip_address_start, ip_address_end),".
	"         KEY ix2 (ip_address_start),".
	"         KEY ix3 (ip_address_end),".
	"         KEY ix4 (rule),".
	"         KEY ix5 (user_agent_pattern),".
	"         KEY ix6 (updated)".
	") ENGINE=InnoDB COLLATE=utf8_unicode_ci"
);

self::exec(
	"INSERT IGNORE INTO `##site_access_rule` (user_agent_pattern, rule, comment) VALUES".
	" ('Mozilla/5.0 (%) Gecko/% %/%', 'allow', 'Gecko-based browsers'),".
	" ('Mozilla/5.0 (%) AppleWebKit/% (KHTML, like Gecko)%', 'allow', 'WebKit-based browsers'),".
	" ('Opera/% (%) Presto/% Version/%', 'allow', 'Presto-based browsers'),".
	" ('Mozilla/% (compatible; MSIE %', 'allow', 'Trident-based browsers'),".
	" ('Mozilla/5.0 (compatible; Konqueror/%', 'allow', 'Konqueror browser')"
);

// Don't do this.  We can't easily/safely migrate the data, and the user may
// wish to migrate it manually....
//self::exec("DROP TABLE IF EXISTS `##ip_address`");

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
