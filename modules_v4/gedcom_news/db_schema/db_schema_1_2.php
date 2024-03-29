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

// Add new columns
try {
	KT_DB::exec(
		"ALTER TABLE `##news`".
		" ADD user_id INTEGER NULL AFTER n_id,".
		" ADD gedcom_id INTEGER NULL AFTER user_id,".
		" ADD updated TIMESTAMP ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,".
		" ADD KEY news_ix1 (user_id, updated),".
		" ADD KEY news_ix2 (gedcom_id, updated)"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Migrate data from the old columns to the new ones
try {
	KT_DB::exec(
		"UPDATE `##news` n".
		" LEFT JOIN `##gedcom` g ON (n.n_username=g.gedcom_name)".
		" LEFT JOIN `##user` u ON (n.n_username=u.user_name)".
		" SET n.gedcom_id=g.gedcom_id, n.user_id=u.user_id, updated=FROM_UNIXTIME(n_date)"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Delete orphaned rows
try {
	KT_DB::exec(
		"DELETE FROM `##news` WHERE user_id IS NULL AND gedcom_id IS NULL"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Delete/rename old columns
try {
	KT_DB::exec(
		"ALTER TABLE `##news`".
		" DROP n_username, DROP n_date,".
		" CHANGE n_id news_id INTEGER NOT NULL AUTO_INCREMENT,".
		" CHANGE n_title subject VARCHAR(255) COLLATE utf8_unicode_ci,".
		" CHANGE n_text body TEXT COLLATE utf8_unicode_ci"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
