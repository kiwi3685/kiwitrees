<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class KT_Site {
	static $setting = null;

	// Get and Set the site's configuration settings
	public static function preference($setting_name, $setting_value=null) {
		// There are lots of settings, and we need to fetch lots of them on every page
		// so it is quicker to fetch them all in one go.
		if (self::$setting === null) {
			self::$setting = KT_DB::prepare(
				"SELECT SQL_CACHE setting_name, setting_value FROM `##site_setting`"
			)->fetchAssoc();
		}

		// If $setting_value is null, then GET the setting
		if ($setting_value === null) {
			// If parameter two is not specified, GET the setting
			if (!array_key_exists($setting_name, self::$setting)) {
				self::$setting[$setting_name]=null;
			}
			return self::$setting[$setting_name];
		} else {
			// If parameter two is specified, then SET the setting
			if (self::preference($setting_name)!=$setting_value) {
				// Audit log of changes
				AddToLog('Site setting "'.$setting_name.'" set to "'.$setting_value.'"', 'config');
			}
			KT_DB::prepare(
				"REPLACE INTO `##site_setting` (setting_name, setting_value) VALUES (?, LEFT(?, 255))"
			)->execute(array($setting_name, $setting_value));
			self::$setting[$setting_name]=$setting_value;
		}
	}
}
