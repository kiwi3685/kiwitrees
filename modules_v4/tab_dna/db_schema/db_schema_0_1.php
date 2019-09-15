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

KT_DB::exec(
	"CREATE TABLE IF NOT EXISTS `##dna` (
		`dna_id`	int(11) NOT NULL AUTO_INCREMENT,
	    `id_a`	varchar(20) NOT NULL,
	    `id_b`	varchar(20) NOT NULL,
	    `cms`		int(10) NOT NULL,
	    `seg`		int(10) NOT NULL,
	    `source`	varchar(20) DEFAULT NULL,
	    `note`	varchar(256) DEFAULT NULL,
		`date`	timestamp NOT NULL DEFAULT current_timestamp(),
	    UNIQUE KEY dna_id (dna_id),
	    KEY id_a (id_a,id_b)
	) COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
