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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// change module anmes
$names_array = array (
	'resource_'					=> 'report_',
	'no_census'					=> 'report_ukcensus',
	'uk_register'				=> 'report_ukregister',
	'simpl_research'			=> 'research_links',
	'simpl_pages'				=> 'pages',
	'simpl_privacy'				=> 'privacy',
	'fancy_treeview'			=> 'fancy_treeview_descendants',
	'fancy_treeview_pedigree'	=> 'fancy_treeview_ancestors',
);

self::exec("SET FOREIGN_KEY_CHECKS=0;");

foreach ($names_array as $key => $value) {
	try {
		self::exec("UPDATE `##module` SET `module_name` = REPLACE(`module_name`, ' . $key . ', ' . $value . ')");
	} catch (PDOException $ex) {
		// Perhaps we have already deleted this data?
	}
	try {
		self::exec("UPDATE `##module_privacy` SET `module_name` = REPLACE(`module_name`, ' . $key . ', ' . $value . ')");
	} catch (PDOException $ex) {
		// Perhaps we have already deleted this data?
	}
	try {
		self::exec("UPDATE `##module_setting` SET `module_name` = REPLACE(`module_name`, ' . $key . ', ' . $value . ')");
	} catch (PDOException $ex) {
		// Perhaps we have already deleted this data?
	}
}

// change module component 'resource' to 'report'
try {
	self::exec("UPDATE `##module_privacy` SET `component` = REPLACE(`component`, 'resource', 'report') WHERE `module_name` LIKE '%report%'");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// remove resource to module_privacy components
self::exec("ALTER TABLE `##module_privacy` CHANGE component component ENUM('block', 'chart', 'list', 'menu', 'report', 'sidebar', 'tab', 'widget')");

// remove no longer used gedcom settings
try {
	self::exec("DELETE FROM `##gedcom_setting` WHERE `setting_name` LIKE 'SHOW_STATS'");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Udate WEBTREES_EMAIL to KIWITREES_email
try {
	self::exec("UPDATE`##gedcom_setting` SET `setting_name` = REPLACE(`setting_name`, 'WEBTREES_EMAIL', 'KIWITREES_EMAIL')");
} catch (PDOException $ex) {
// Perhaps we have already deleted this data?
}

self::exec("SET FOREIGN_KEY_CHECKS=1;");

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
