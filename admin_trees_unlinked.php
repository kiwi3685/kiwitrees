<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2006-2009 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License or,
// at your discretion, any later version.
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
//$Id$

define('WT_SCRIPT_NAME', 'admin_trees_unlinked.php');

require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller=new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Find unlinked individuals'))
	->pageHeader();

$action		= safe_GET('action');
$gedcom_id	= safe_GET('gedcom_id', array_keys(WT_Tree::getAll()), WT_GED_ID);

// the sql query used to identify unlinked indis
$sql = "
	SELECT i_id
	FROM `##individuals`
	 WHERE i_gedcom NOT LIKE '%1 FAM%'
	 AND i_file = ".$gedcom_id."
";

echo '<div id="admin_unlinked">
	<h2>' .$controller->getPageTitle(). '</h2>
	<form method="get" name="unlinked_form" action="', WT_SCRIPT_NAME, '">
		<div class="gm_check">
			<div id="famtree">
				<label>', WT_I18N::translate('Family tree'), '</label>
				<select name="ged">';
				foreach (WT_Tree::getAll() as $tree) {
					echo '<option value="', $tree->tree_name_html, '"';
					if (empty($ged) && $tree->tree_id==WT_GED_ID || !empty($ged) && $ged==$tree->tree_name) {
						echo ' selected="selected"';
					}
					echo ' dir="auto">', $tree->tree_title_html, '</option>';
				}
				echo '</select>
				<input type="submit" name="action" value="',WT_I18N::translate('View'),'">
			</div>
		</div>
	</form>';
	// START OUTPUT
	if ($action == 'View') {
		$rows=WT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		if ($rows) {
			foreach ($rows as $row) {
				$id = $row['i_id'];
				$person = WT_Person::getInstance($id);
				$fullname =  $person->getFullName();
				echo '<p><a href="', $person->getHtmlUrl(), '" target="_blank">', $fullname, ' (', $id, ')</p>';
			}
		} else {
			echo '<h4>', WT_I18N::translate('No unlinked individuals to display'), '</h4></div>';
		}
	}
echo '</div>';
