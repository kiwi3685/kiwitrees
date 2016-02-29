<?php
// Update the database schema from version 14 to 15
// - delete old config settings
// - update existing ones - we changed the default, but there is no GUI to edit it
//
// The script should assume that it can be interrupted at
// any point, and be able to continue by re-running the script.
// Fatal errors, however, should be allowed to throw exceptions,
// which will be caught by the framework.
// It shouldn't do anything that might take more than a few
// seconds, for systems with low timeout values.
//
// Derived from webtrees
// Copyright (C) 2011 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Remove the i_isdead column
self::exec("DELETE FROM `##gedcom_setting` WHERE setting_name IN('GEDCOM_DEFAULT_TAB', 'LINK_ICONS', 'ZOOM_BOXES')");
self::exec("DELETE FROM `##user_setting` WHERE setting_name='default'");

// There is no way to add a RESN tag to NOTE objects
self::exec("UPDATE `##gedcom_setting` SET setting_value='SOUR,RESN' WHERE setting_name='NOTE_FACTS_ADD' AND setting_value='SOUR'");

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);

