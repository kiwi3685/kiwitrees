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

// Add a "default" user, to store default settings
self::exec("INSERT IGNORE INTO `##user` (user_id, user_name, real_name, email, password) VALUES (-1, 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER')");

// Add the initial default block settings

self::exec("INSERT IGNORE INTO `##block` (user_id, location, block_order, module_name) VALUES (-1, 'main', 1, 'todays_events'), (-1, 'main', 2, 'user_messages'), (-1, 'main', 3, 'widget_favorites'), (-1, 'side', 1, 'user_welcome'), (-1, 'side', 2, 'random_media'), (-1, 'side', 3, 'upcoming_events'), (-1, 'side', 4, 'logged_in')");

// Add a "default" tree, to store default settings
self::exec("INSERT IGNORE INTO `##gedcom` (gedcom_id, gedcom_name) VALUES (-1, 'DEFAULT_TREE')");

// Add the initial default block settings
self::exec("INSERT IGNORE INTO `##block` (gedcom_id, location, block_order, module_name) VALUES (-1, 'main', 1, 'gedcom_stats'), (-1, 'main', 2, 'gedcom_news'), (-1, 'main', 3, 'gedcom_favorites'), (-1, 'main', 4, 'review_changes'), (-1, 'side', 1, 'gedcom_block'), (-1, 'side', 2, 'random_media'), (-1, 'side', 3, 'todays_events'), (-1, 'side', 4, 'logged_in')");

// Some modules (e.g. sitemap) require larger settings
self::exec("ALTER TABLE `##module_setting` CHANGE setting_value setting_value MEDIUMTEXT COLLATE utf8_unicode_ci NOT NULL");

// Update the version to indicate success
KT_Site::preference($schema_name, $next_version);

