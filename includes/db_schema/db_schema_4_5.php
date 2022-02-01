<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
	self::exec("ALTER TABLE `##gedcom` ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}

try {
	self::exec("ALTER TABLE `##gedcom` ADD INDEX ix1 (sort_order)");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}

// No longer used
self::exec("DELETE FROM `##gedcom_setting` WHERE setting_name IN ('PAGE_AFTER_LOGIN')");

// Change of defaults - do not add ASSO, etc. to NOTE objects
self::exec("UPDATE `##gedcom_setting` SET setting_value='SOUR' WHERE setting_value='ASSO,SOUR,NOTE,REPO' AND setting_name='NOTE_FACTS_ADD'");

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
