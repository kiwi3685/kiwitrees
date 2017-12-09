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

self::exec(
	"INSERT IGNORE INTO `##user_gedcom_setting` (user_id, gedcom_id, setting_name, setting_value)".
	" SELECT u.user_id, g.gedcom_id, 'RELATIONSHIP_PATH_LENGTH', LEAST(us1.setting_value, gs1.setting_value)".
	" FROM   `##user` u".
	" CROSS  JOIN `##gedcom` g".
	" LEFT   JOIN `##user_setting`   us1 ON (u.user_id  =us1.user_id   AND us1.setting_name='max_relation_path')".
	" LEFT   JOIN `##user_setting`   us2 ON (u.user_id  =us2.user_id   AND us2.setting_name='relationship_privacy')".
	" LEFT   JOIN `##gedcom_setting` gs1 ON (g.gedcom_id=gs1.gedcom_id AND gs1.setting_name='MAX_RELATION_PATH_LENGTH')".
	" LEFT   JOIN `##gedcom_setting` gs2 ON (g.gedcom_id=gs2.gedcom_id AND gs2.setting_name='USE_RELATIONSHIP_PRIVACY')".
	" WHERE  us2.setting_value AND gs2.setting_value"
);

// Delete old/unused settings
self::exec(
	"DELETE FROM `##site_setting` WHERE setting_name IN ('SESSION_SAVE_PATH')"
);
self::exec(
	"DELETE FROM `##gedcom_setting` WHERE setting_name IN ('HOME_SITE_TEXT', 'HOME_SITE_URL', 'CHECK_MARRIAGE_RELATIONS', 'MAX_RELATION_PATH_LENGTH', 'USE_RELATIONSHIP_PRIVACY')"
);
self::exec(
	"DELETE FROM `##user_setting` WHERE setting_name IN ('loggedin', 'relationship_privacy', 'max_relation_path_length')"
);

// Fix Mc/Mac problems - See SVN9701
self::exec(
	"UPDATE `##name` SET n_surn=CONCAT('MC', SUBSTRING(n_surn, 4)) WHERE n_surn LIKE 'MC0%'"
);

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
