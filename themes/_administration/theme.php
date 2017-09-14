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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Theme name - this needs double quotes, as file is scanned/parsed by script
$theme_name = "_administration"; /* I18N: Name of a theme. */ KT_I18N::translate('_administration');

$headerfile = KT_THEME_DIR . 'header.php';
$footerfile = KT_THEME_DIR . 'footer.php';

//- main icons
$KT_IMAGES = array(
	// lightbox module uses this in manage media links, and also admin_media.php for delete folder.
	'remove' => KT_THEME_URL.'images/delete.png',

	// need different sizes before moving to CSS
	'default_image_F' => KT_THEME_URL . 'images/silhouette_female.png',
	'default_image_M' => KT_THEME_URL . 'images/silhouette_male.png',
	'default_image_U' => KT_THEME_URL . 'images/silhouette_unknown.png',
);
