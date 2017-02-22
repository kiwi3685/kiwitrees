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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Delete any data that might violate the new constraints
WT_DB::exec(
	"DELETE FROM `##news`".
	" WHERE user_id   NOT IN (SELECT user_id   FROM `##user`  )".
	" OR    gedcom_id NOT IN (SELECT gedcom_id FROM `##gedcom`)"
);

// Add the new constraints
try {
	WT_DB::exec(
		"ALTER TABLE `##news`".
		" ADD FOREIGN KEY news_fk1 (user_id  ) REFERENCES `##user`   (user_id)   ON DELETE CASCADE,".
		" ADD FOREIGN KEY news_fk2 (gedcom_id) REFERENCES `##gedcom` (gedcom_id) ON DELETE CASCADE"
	);
} catch (PDOException $ex) {
	// Already updated?
}

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
