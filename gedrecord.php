<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'gedrecord.php');
require './includes/session.php';

$controller = new KT_Controller_Page();

$pid = KT_Filter::get('pid', KT_REGEX_XREF);

$obj = KT_GedcomRecord::getInstance($pid);

// Special case - allow raw editting of SUBM records
if ($obj && $obj->getType() == 'SUBM' ) {
	header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH . 'edit_interface.php?action=editraw&pid=' . $pid);
	exit;
} else {
	if (
		$obj instanceof KT_Person ||
		$obj instanceof KT_Family ||
		$obj instanceof KT_Source ||
		$obj instanceof KT_Repository ||
		$obj instanceof KT_Note ||
		$obj instanceof KT_Media
	) {
		header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH . $obj->getRawUrl());
		exit;
	} elseif (!$obj || !$obj->canDisplayDetails()) {
		$controller->pageHeader();
		print_privacy_error();
	} else {
		$controller->pageHeader();
		echo
			'<pre style="white-space:pre-wrap; word-wrap:break-word;">',
			preg_replace(
				'/@(' . KT_REGEX_XREF . ')@/', '@<a href="gedrecord.php?pid=$1">$1</a>@',
				htmlspecialchars($obj->getGedcomRecord())
			),
			'</pre>';
	}
}
