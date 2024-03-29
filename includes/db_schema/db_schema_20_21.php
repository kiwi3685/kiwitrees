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

// Delete old settings
self::exec("DELETE FROM `##gedcom_setting` WHERE setting_name IN ('MEDIA_EXTERNAL')");

// Delete old table
self::exec("DROP TABLE IF EXISTS `##media_mapping`");

// Make this table look like all the others
try {
	self::exec(
		"ALTER TABLE `##media`" .
		" DROP   m_id," .
		" CHANGE m_media   m_id       VARCHAR(20)  COLLATE utf8_unicode_ci NOT NULL," .
		" CHANGE m_file    m_filename VARCHAR(512) COLLATE utf8_unicode_ci DEFAULT NULL," .
		" CHANGE m_gedfile m_file     INTEGER                              NOT NULL," .
		" CHANGE m_gedrec  m_gedcom   MEDIUMTEXT   COLLATE utf8_unicode_ci DEFAULT NULL," .
		" ADD    m_type               VARCHAR(20)  COLLATE utf8_unicode_ci NULL AFTER m_ext,".
		" ADD    PRIMARY KEY     (m_file, m_id)," .
		" ADD            KEY ix2 (m_ext, m_type)," .
		" ADD            KEY ix3 (m_titl)"
	);
} catch (PDOException $ex) {
	// Assume we've already done this
}

// Populate the new column
self::exec("UPDATE `##media` SET m_type = SUBSTRING_INDEX(SUBSTRING_INDEX(m_gedcom, '\n3 TYPE ', -1), '\n', 1) WHERE m_gedcom like '%\n3 TYPE %'");

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);

