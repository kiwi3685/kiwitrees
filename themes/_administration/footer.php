<?php
// Footer for webtrees administration theme
//
// kiwitrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
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
//
// $Id$

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo '</div>'; // id="admin_content"
if ($view!='simple') {
	echo '<div id="admin_footer">';
		echo '<p class="logo">';
		echo '<a href="', WT_KIWITREES_URL, '" target="_blank" title="', WT_KIWITREES, ' ', WT_VERSION_TEXT, '">', WT_KIWITREES,'</a>';
		echo '</p>';
		if (WT_DEBUG) {
			echo execution_stats();
		}
	echo '</div>'; // id="admin_footer"
}
