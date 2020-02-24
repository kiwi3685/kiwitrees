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

// add resource to module_privacy components
try {
	self::exec("ALTER TABLE `##module_privacy` CHANGE component component ENUM('block', 'chart', 'menu', 'report', 'sidebar', 'tab', 'theme', 'widget', 'resource')");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Delete old menu settings
 try {
	 self::exec("DELETE FROM `##module_privacy` WHERE `module_name` = 'no_census' and `component` = 'menu'");
	 self::exec("UPDATE `##module` SET `menu_order` = null WHERE `module_name` = 'no_census'");
 } catch (PDOException $ex) {
 	// Perhaps we have already deleted this data?
 }

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
