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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// change _WT_USER to _KT_USER in datbase (missed on 3.3.2 upgrade)
try {
	self::exec("UPDATE '##individuals' SET 'i_gedcom' = replace('i_gedcom', '_WT_USER', '_KT_USER')");
	self::exec("UPDATE '##families' SET 'f_gedcom' = replace('f_gedcom', '_WT_USER', '_KT_USER')");
	self::exec("UPDATE '##media' SET 'm_gedcom' = replace('m_gedcom', '_WT_USER', '_KT_USER')");
	self::exec("UPDATE '##other' SET 'o_gedcom' = replace('o_gedcom', '_WT_USER', '_KT_USER')");
	self::exec("UPDATE '##sources' SET 's_gedcom' = replace('s_gedcom', '_WT_USER', '_KT_USER')");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
