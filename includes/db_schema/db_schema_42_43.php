<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

try {
	self::exec("
		ALTER TABLE `##session`
		CHANGE `session_id` `session_id` CHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		CHANGE `session_time` `session_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		CHANGE `session_data` `session_data` LONGBLOB NOT NULL;
	");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}

try {
	self::exec("
		ALTER TABLE `ktn_log`
		CHANGE `log_id` `log_id` INT(11) NOT NULL AUTO_INCREMENT,
		CHANGE `log_time` `log_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		CHANGE `log_message` `log_message` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
	");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}


// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
