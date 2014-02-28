<?php
// A sidebar to show extra/non-genealogical information about an individual
// 
// Copyright (C) 2013 Nigel Osborne and kiwtrees.net. All rights reserved.
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class simpl_unlinked_WT_Module extends WT_Module implements WT_Module_Config {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Find unlinked individuals');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Extra information" module */ WT_I18N::translate('Produce a list of all individuals not connected to any family or other individual.');
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_NONE; // access for managers & admins only.
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_unlinked';
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_unlinked':
			$this->config();
			break;
		}
	}

	private function config() {
		global $GEDCOM;
		
		$ged		= $GEDCOM;
		$action		= safe_GET('action');
		$gedcom_id	= safe_GET('gedcom_id', array_keys(WT_Tree::getAll()), WT_GED_ID);

		if (!empty($WT_SESSION['unlinked_gedcom_id'])) {
			$gedcom_id = $WT_SESSION['unlinked_gedcom_id'];
		} else {
			$WT_SESSION['unlinked_gedcom_id'] = $gedcom_id;
		}

		// the sql query used to identify unlinked indis
		$sql = "
			SELECT i_id
				FROM `##individuals` 
				 LEFT OUTER JOIN `##link` 
				 ON (##individuals.i_id = ##link.l_to) 
				 WHERE ##link.l_to IS NULL
				 AND i_file = ".$gedcom_id."
			";

		$controller=new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle(WT_I18N::translate('Find unlinked individuals'))
			->pageHeader();
		echo '
			<div id="sim_unlinked">
				<h3>',$this->getDescription(),'</h3>
				<form method="get" name="unlinked_form" action="module.php">
				<input type="hidden" name="mod" value="', $this->getName(), '">
				<input type="hidden" name="mod_action" value="admin_unlinked">
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
	}

}
