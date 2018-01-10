<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

try {
	self::exec(
		"ALTER TABLE `##places`".
		" DROP       KEY ix1,".
		" DROP       KEY ix2,".
		" DROP       KEY ix3,".
		" DROP       KEY ix4,".
		" DROP       p_level,".                              // Not needed - implicit from p_parent
		" ADD        KEY ix1 (p_file, p_place),".            // autocomplete.php, find.php
		" ADD UNIQUE KEY ux1 (p_parent_id, p_file, p_place)" // placelist.php
	);
} catch (PDOException $ex) {
	// Already done?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);

