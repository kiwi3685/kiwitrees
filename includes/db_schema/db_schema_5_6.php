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

$settings=self::prepare(
	"SELECT gedcom_id, setting_value FROM `##gedcom_setting` WHERE setting_name='SHOW_RELATIVES_EVENTS'"
)->fetchAssoc();

foreach ($settings as $gedcom_id=>$setting) {
	// Delete old settings
	$setting=preg_replace('/_(BIRT|MARR|DEAT)_(COUS|MSIB|FSIB|GGCH|NEPH|GGPA)/', '', $setting);
	$setting=preg_replace('/_FAMC_(RESI_EMIG)/', '', $setting);
	// Rename settings
	$setting=preg_replace('/_MARR_(MOTH|FATH|FAMC)/', '_MARR_PARE', $setting);
	$setting=preg_replace('/_DEAT_(MOTH|FATH)/', '_DEAT_PARE', $setting);
	// Remove duplicates
	preg_match_all('/[_A-Z]+/', $setting, $match);
	// And save
	set_gedcom_setting($gedcom_id, 'SHOW_RELATIVES_EVENTS', implode(',', array_unique($match[0])));
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
