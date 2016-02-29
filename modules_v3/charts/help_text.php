<?php
// Module help text.
//
// This file is included from the application help_text.php script.
// It simply needs to set $title and $text for the help topic $help_topic
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'index_charts':
	$title=WT_I18N::translate('Charts');
	$text=WT_I18N::translate('This block allows a pedigree, descendancy, or hourglass chart to appear the Home page.  Because of space limitations, the charts should be placed only on the left side of the page or at full width.<br /><br />When this block appears the root person and the type of chart to be displayed are determined by the administrator.<br /><br />The behavior of these charts is identical to their behavior when they are called up from the menus.  Click on the box of a person to see more details about them.');
	break;
}
