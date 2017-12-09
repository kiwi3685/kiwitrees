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

define('KT_SCRIPT_NAME', 'admin_trees_export.php');
require './includes/session.php';

$controller = new KT_Controller_Ajax();
$controller
	->pageHeader()
	->requireManagerLogin();

$filename = KT_DATA_DIR . $KT_TREE->tree_name;
// Force a ".ged" suffix
if (strtolower(substr($filename, -4)) != '.ged') {
	$filename .= '.ged';
}

if ($KT_TREE->exportGedcom($filename)) {
	echo '<p>', /* I18N: %s is a filename */ KT_I18N::translate('Family tree exported to %s.', '<span dir="ltr">' . $filename . '</span>'), '</p>';
} else {
	echo '<p class="error">', /* I18N: %s is a filename */ KT_I18N::translate('Unable to create %s.  Check the permissions.', $filename), '</p>';
}