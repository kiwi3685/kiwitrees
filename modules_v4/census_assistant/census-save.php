<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

global $controller;

/** @global Tree $KT_TREE */
global $KT_TREE;

if (!KT_Filter::checkCsrf()) {
	require KT_ROOT . KT_MODULES_DIR . 'census_assistant/census-edit.php';

	return;
}

// We are creating a CENS/NOTE record linked to these individuals
$pid_array = KT_Filter::post('pid_array');

if (empty($pid_array)) {
	$xref = '';
} else {
	$NOTE   = KT_Filter::post('NOTE');
	$gedcom = '0 @XREF@ NOTE ' . preg_replace('/\r?\n/', "\n1 CONT ", trim($NOTE));
	$xref   = $KT_TREE->createRecord($gedcom)->getXref();
}

$controller
	->addInlineJavascript('window.opener.set_pid_array("' . $pid_array . '");')
	->addInlineJavascript('openerpasteid("' . $xref . '");')
	->setPageTitle(KT_I18N::translate('Create a shared note using the census assistant'))
	->pageHeader();
?>

<div id="edit_interface-page">
	<h4><?php echo $controller->getPageTitle() ?></h4>
</div>
