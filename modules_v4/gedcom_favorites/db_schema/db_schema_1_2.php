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

// Add the new columns
try {
	KT_DB::exec(
		"ALTER TABLE `##favorites`".
		" CHANGE fv_id    favorite_id   INTEGER AUTO_INCREMENT NOT NULL,".
		" CHANGE fv_gid   xref          VARCHAR(20) NULL,".
		" CHANGE fv_type  favorite_type ENUM('INDI', 'FAM', 'SOUR', 'REPO', 'OBJE', 'NOTE', 'URL') NOT NULL,".
		" CHANGE fv_url   url           VARCHAR(255) NULL,".
		" CHANGE fv_title title         VARCHAR(255) NULL,".
		" CHANGE fv_note  note          VARCHAR(1000) NULL,".
		" ADD user_id   INTEGER     NULL AFTER favorite_id,".
		" ADD gedcom_id INTEGER NOT NULL AFTER user_id,".
		" DROP KEY ix1,".
		" ADD KEY news_ix1 (gedcom_id, user_id)"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Migrate data from the old columns to the new ones
try {
	KT_DB::exec(
		"UPDATE `##favorites` f".
		" LEFT JOIN `##gedcom` g ON (f.fv_file    =g.gedcom_name)".
		" LEFT JOIN `##user`   u ON (f.fv_username=u.user_name)".
		" SET f.gedcom_id=g.gedcom_id, f.user_id=u.user_id"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Delete orphaned rows
try {
	KT_DB::exec(
		"DELETE FROM `##favorites` WHERE user_id IS NULL AND gedcom_id IS NULL"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Delete the old column
try {
	KT_DB::exec(
		"ALTER TABLE `##favorites` DROP fv_username, DROP fv_file"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Rename the table
try {
	KT_DB::exec(
		"RENAME TABLE `##favorites` TO `##favorite`"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
