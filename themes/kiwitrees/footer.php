<?php
// Footer for kiwitrees theme
//
// kiwitrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView and webtrees
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
// Copyright (C) 2010 to 2013  webtrees Development Team.  All rights reserved.
//
// This is free software;you can redistribute it and/or modify
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
// $Id: footer.php 13642 2012-03-24 13:06:08Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

echo '</div>'; // <div id="content">
if ($view!='simple') {
	echo '
		<div id="footer">';
			if (contact_links() != '') echo contact_links();
		echo '<p class="logo">', 
			WT_I18N::translate('Powered by '), '
			<a href="', WT_WEBTREES_URL, '" target="_blank" title="', WT_WEBTREES, ' ', WT_VERSION_TEXT, '">', WT_WEBTREES,'<span>&trade;</span></a>
		</p>';
		if (WT_DEBUG || get_gedcom_setting(WT_GED_ID, 'SHOW_STATS')) {
			echo execution_stats();
		}
		if (exists_pending_change()) {
			echo '
				<a href="#" onclick="window.open(\'edit_changes.php\', \'_blank\', chan_window_specs); return false;">
					<p class="error center">', WT_I18N::translate('There are pending changes for you to moderate.'), '</p>
				</a>
			';
		}
		echo '</div>
	';
}
echo '</div>'; // <div id="main_content">
