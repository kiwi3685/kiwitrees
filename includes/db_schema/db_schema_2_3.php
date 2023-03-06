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

self::exec(
	"CREATE TABLE IF NOT EXISTS `##gedcom_chunk` (".
	" gedcom_chunk_id INTEGER AUTO_INCREMENT NOT NULL,".
	" gedcom_id       INTEGER                NOT NULL,".
	" chunk_data      MEDIUMBLOB             NOT NULL,".
	" imported        BOOLEAN                NOT NULL DEFAULT FALSE,".
	" PRIMARY KEY     (gedcom_chunk_id),".
	"         KEY ix1 (gedcom_id, imported),".
	" FOREIGN KEY fk1 (gedcom_id) REFERENCES `##gedcom` (gedcom_id)".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

try {
	self::exec(
		"ALTER TABLE `##gedcom` DROP import_gedcom, DROP import_offset"
	);
} catch (PDOException $ex) {
	// Perhaps we have already deleted these columns?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
