<?php
// Footer for Simpl_grey
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: footer.php 11933 2011-07-01 10:16:07Z greg $
 
if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
echo '</div>';// closing div id="content"
if ($view!='simple') {
	echo '<div id="footer" class="width99 center">';
	if (contact_links() != '') echo contact_links();
	//whoisonline
	if (WT_USER_ID) {
		echo '<div style="padding:5px;">';
			echo '<div style="display:inline; font-weight:700;">'. WT_I18N::translate('Who is online'). '</div>:&nbsp;&nbsp';
			echo whoisonline();
		echo '</div>';
	}
	//--------------
	echo '<p class="logo"><a href="', WT_WEBTREES_URL, '" target="_blank" title="', WT_WEBTREES, ' ', WT_VERSION_TEXT , '" >', WT_WEBTREES, '<span>&#8482;</span></a></p>';
	if (WT_DEBUG || get_gedcom_setting(WT_GED_ID, 'SHOW_STATS')) {
		echo execution_stats();
	}
	if (exists_pending_change()) {
		echo '<a href="javascript:;" onclick="window.open(\'edit_changes.php\', \'_blank\', \'width=600, height=500, resizable=1, scrollbars=1\'); return false;">';
		echo '<p class="error center">', WT_I18N::translate('There are pending changes for you to moderate.'), '</p>';
		echo '</a>';
	}
	echo '</div>'; // close div id="footer"
}