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

// Create all of the tables needed for this module
KT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##placelocation` (".
	" pl_id        INTEGER      NOT NULL,".
	" pl_parent_id INTEGER          NULL,".
	" pl_level     INTEGER          NULL,".
	" pl_place     VARCHAR(255)     NULL,".
	" pl_long      VARCHAR(30)      NULL,".
	" pl_lati      VARCHAR(30)      NULL,".
	" pl_zoom      INTEGER          NULL,".
	" pl_icon      VARCHAR(255)     NULL,".
	" PRIMARY KEY     (pl_id),".
	"         KEY ix1 (pl_level),".
	"         KEY ix2 (pl_long),".
	"         KEY ix3 (pl_lati),".
	"         KEY ix4 (pl_place),".
	"         KEY ix5 (pl_parent_id)".
	") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
