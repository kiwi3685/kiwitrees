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

// Tree settings become site settings
self::exec(
	"INSERT IGNORE INTO `##site_setting` (setting_name, setting_value)" .
	" SELECT setting_name, MIN(setting_value)" . // Can't use ANY_VALUE() until MySQL5.7
	" FROM `##gedcom_setting`" .
	" WHERE setting_name IN ('SHOW_REGISTER_CAUTION', 'WELCOME_TEXT_CUST_HEAD') OR setting_name LIKE 'WELCOME_TEXT_AUTH_MODE%'" .
	" GROUP BY setting_name"
);

self::exec(
	"DELETE FROM `##gedcom_setting` WHERE setting_name IN ('ALLOW_EDIT_GEDCOM', 'SHOW_REGISTER_CAUTION', 'WELCOME_TEXT_CUST_HEAD') OR setting_name like 'WELCOME_TEXT_AUTH_MODE%'"
);

self::exec(
	"DELETE FROM `##site_setting` WHERE setting_name IN ('STORE_MESSAGES')"
);

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
