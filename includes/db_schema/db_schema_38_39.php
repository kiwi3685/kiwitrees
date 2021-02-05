<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

// Add new site setting (revised version from previous scheme - due to error in column names in that)
// add spam to log table components
try {
	self::exec("ALTER TABLE `##log` CHANGE  `log_type` `log_type` ENUM('auth', 'config', 'debug', 'edit', 'error', 'media', 'search', 'spam')");
} catch (PDOException $ex) {
	// Perhaps we have already changed this data?
}
// transfer spam errors to spam type
try {
	self::exec("UPDATE `##log` SET `log_type` = 'spam' WHERE
		`log_message` LIKE '%spam%' OR
		`log_message` LIKE '%external links%'  OR 
		`log_message` LIKE '%Invalid email address%' AND
		`log_type` LIKE ''
	");
	} catch (PDOException $ex) {
		// Perhaps we have already changed this data?
}
// remove old message "Possible spam - "
try {
	self::exec("UPDATE `##log` SET `log_message` = REPLACE(`log_message`, 'Possible spam - ', '') WHERE `log_type` LIKE 'spam'");
} catch (PDOException $ex) {
	// Perhaps we have already changed this data?
}
// set new message to title case
try {
	self::exec("UPDATE `##log` SET `log_message` = CONCAT(UPPER(LEFT(`log_message`, 1)), SUBSTRING(`log_message`, 2)) WHERE `log_type` LIKE 'spam'");
} catch (PDOException $ex) {
	// Perhaps we have already changed this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
