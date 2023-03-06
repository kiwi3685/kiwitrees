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

// Use LONGTEXT instead of TEXT and MEDIUMTEXT, and make NOT NULL.
try {
	self::exec("UPDATE `##news`   SET body          = '' WHERE body          IS NULL");
	self::exec("UPDATE `##other`  SET o_gedcom      = '' WHERE o_gedcom      IS NULL");
	self::exec("UPDATE `##places` SET p_std_soundex = '' WHERE p_std_soundex IS NULL");
	self::exec("UPDATE `##places` SET p_dm_soundex  = '' WHERE p_dm_soundex  IS NULL");
	self::exec("ALTER TABLE `##block_setting`  CHANGE setting_value setting_value LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##change`         CHANGE new_gedcom    new_gedcom    LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##change`         CHANGE old_gedcom    old_gedcom    LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##families`       CHANGE f_gedcom      f_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##individuals`    CHANGE i_gedcom      i_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##log`            CHANGE log_message   log_message   LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##media`          CHANGE m_gedcom      m_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##message`        CHANGE body          body          LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##module_setting` CHANGE setting_value setting_value LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##news`           CHANGE body          body          LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##other`          CHANGE o_gedcom      o_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##places`         CHANGE p_std_soundex p_std_soundex LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##places`         CHANGE p_dm_soundex  p_dm_soundex  LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
	self::exec("ALTER TABLE `##sources`        CHANGE s_gedcom      s_gedcom      LONGTEXT COLLATE utf8_unicode_ci NOT NULL");
} catch (PDOException $ex) {
	// Perhaps we have already deleted this data?
}

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);
