<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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

// remove no longer used user settings
try {
	self::exec("DELETE FROM `##user_setting` WHERE `setting_name` IN ('comment_exp','sync_gedcom','theme')");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// update messging option for removed choices
try {
	self::exec("UPDATE `##user_setting` SET `setting_value` = REPLACE(`setting_value`, 'messaging2', 'messaging')");
	self::exec("UPDATE `##user_setting` SET `setting_value` = REPLACE(`setting_value`, 'messaging3', 'messaging')");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// tidy up module_privacy table
try {
	self::exec("DELETE FROM `##module_privacy` WHERE `component` = ''");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
