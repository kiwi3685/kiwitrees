<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_trees_addunlinked.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Add unlinked records'))
	->pageHeader();

?>
<div id="other">
	<h3><?php echo KT_I18N::translate('Add unlinked records'); ?></h3>
	<form method="post" action="#" name="tree">
		<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM, ' onchange="tree.submit();"'); ?>
	</form>
	<table id="other">
		<tr>
			<td>
				<a href="#" onclick="addnewchild(''); return false;">
					<?php echo /* I18N: An individual that is not linked to any other record */ KT_I18N::translate('Create a new individual'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewnote(''); return false;">
					<?php echo /* I18N: An note that is not linked to any other record */ KT_I18N::translate('Create a new note'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewsource(''); return false;">
					<?php echo /* I18N: A source that is not linked to any other record */ KT_I18N::translate('Create a new source'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="#" onclick="addnewrepository(''); return false;">
					<?php echo /* I18N: A repository that is not linked to any other repository */ KT_I18N::translate('Create a new repository'); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td>
				<a href="addmedia.php?action=showmediaform&amp;linktoid=new" target="_blank" rel="noopener noreferrer">
					<?php echo /* I18N: A media object that is not linked to any other record */ KT_I18N::translate('Create a new media object'); ?>
				</a>
			</td>
		</tr>
	</table>
</div>
