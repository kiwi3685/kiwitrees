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

try {
	self::exec("INSERT INTO `##site_setting` (setting_name, setting_value) VALUES ('USE_HONEYPOT', '1')");
} catch (PDOException $ex) {
	// Perhaps we have already changed this data?
}
try {
	self::exec("INSERT INTO `##site_setting` (setting_name, setting_value) VALUES ('USE_RECAPTCHA', '0')");
} catch (PDOException $ex) {
	// Perhaps we have already changed this data?
}

$settings = self::prepare(
	"SELECT gedcom_id, setting_value FROM `##gedcom_setting`"
)->fetchAssoc();

foreach ($settings as $gedcom_id=>$setting) {
	try {
		self::exec("INSERT INTO `##gedcom_setting` (gedcom_id, setting_name, setting_value) VALUES ($gedcom_id, 'COMMON_TYPES_THRESHOLD', 6)");
	} catch (PDOException $ex) {
		// Perhaps we have already changed this data?
	}
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
