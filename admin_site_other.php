<?php
// Miscellaneous administrative functions
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Partly Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
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

define('WT_SCRIPT_NAME', 'admin_site_other.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller=new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Add unlinked records'))
	->pageHeader();

?>
<div id="other">
	<h3><?php echo WT_I18N::translate('Add unlinked records'); ?></h3>
	<form method="post" action="#" name="tree">
		<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM, ' onchange="tree.submit();"'); ?>
	</form>
	<table id="other">
		<tr>
			<td>
				<a href="#" onclick="addnewchild(''); return false;">
					<?php echo /* I18N: An individual that is not linked to any other record */ WT_I18N::translate('Create a new individual'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewnote(''); return false;">
					<?php echo /* I18N: An note that is not linked to any other record */ WT_I18N::translate('Create a new note'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewsource(''); return false;">
					<?php echo /* I18N: A source that is not linked to any other record */ WT_I18N::translate('Create a new source'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewrepository(''); return false;">
					<?php echo /* I18N: A repository that is not linked to any other repository */ WT_I18N::translate('Create a new repository'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="addmedia.php?action=showmediaform&amp;linktoid=new" target="_blank">
					<?php echo /* I18N: A media object that is not linked to any other record */ WT_I18N::translate('Create a new media object'); ?>
				</a>
			</td>
		</tr>
	</table>
</div>
