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

// add widgets to module table
try {
	self::exec("ALTER TABLE `##module` ADD COLUMN widget_order INTEGER NULL");
} catch (PDOException $ex) {
	// If this fails, it has probably already been done.
}

//self::exec("ALTER TABLE `##module_privacy` CHANGE component component ENUM('block', 'chart', 'menu', 'report', 'sidebar', 'tab', 'theme', 'widget')");

// Delete redundant user blocks
self::exec("DELETE FROM `##block_setting` WHERE block_id IN (SELECT block_id FROM `##block` WHERE user_id > 0)");
self::exec("DELETE FROM `##block` WHERE user_id > 0");

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
