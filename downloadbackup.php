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

define('KT_SCRIPT_NAME', 'downloadbackup.php');
require './includes/session.php';

$fname=safe_GET('fname');

if (!KT_USER_GEDCOM_ADMIN || !preg_match('/\.zip$/', $fname)) {
	$controller = new KT_Controller_Page();
	$controller
		->setPageTitle(KT_I18N::translate('Error'))
		->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('You do not have permission to view this page.'), '</p>';
	exit;
}

header('Pragma: public'); // required
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false); // required for certain browsers
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$fname.'"');
header('Content-length: '.filesize(KT_DATA_DIR.$fname));
header('Content-Transfer-Encoding: binary');
readfile(KT_DATA_DIR.$fname);
