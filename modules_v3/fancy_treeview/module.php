<?php
// Fancy Tree View Module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from JustCarmen
// Copyright (C) 2015 JustCarmen
//
// Derived from webtrees
// Copyright (C) 2014 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Update database for version 1.5
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'fancy_treeview/db_schema/', 'FTV_SCHEMA_VERSION', 8);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class fancy_treeview_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Menu, WT_Module_Resources {

	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of the module */ WT_I18N::translate('Descendants');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('A Fancy overview of the descendants of one family(branch) in a narrative way');
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Get module options
	private function options($value = '') {
		$FTV_OPTIONS = unserialize(get_module_setting($this->getName(), 'FTV_OPTIONS'));

		$key = WT_TREE::getIdFromName(WT_Filter::get('ged'));
		if (empty($key)) {
			$key = WT_GED_ID;
		}

		if (empty($FTV_OPTIONS) || (is_array($FTV_OPTIONS) && !array_key_exists($key, $FTV_OPTIONS))) {
			$FTV_OPTIONS[0] = array(
				'USE_FULLNAME' 			=> '0',
				'NUMBLOCKS'				=> '0',
				'CHECK_RELATIONSHIP' 	=> '0',
				'SHOW_SINGLES'			=> '0',
				'SHOW_PLACES' 			=> '1',
				'USE_GEDCOM_PLACES'		=> '0',
				'COUNTRY' 				=> '',
				'SHOW_OCCU' 			=> '1',
				'RESIZE_THUMBS'			=> '1',
				'THUMB_SIZE'			=> '60',
				'THUMB_RESIZE_FORMAT'	=> '2',
				'USE_SQUARE_THUMBS'		=> '1'
			);
			$key = 0;
		}

		// country could be disabled and thus not set
		if ($value == 'country' && !array_key_exists(strtoupper($value), $FTV_OPTIONS[$key])) {
			return '';
		} elseif ($value) {
			return($FTV_OPTIONS[$key][strtoupper($value)]);
		} else {
			return $FTV_OPTIONS[$key];
		}
	}

	// Get Indis from surname input
	private function indis_array($surname, $soundex_std, $soundex_dm) {
		$sql=
			"SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom".
			" FROM `##individuals`".
			" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
			" WHERE n_file=?".
			" AND n_type!=?".
			" AND (n_surn=? OR n_surname=?";
		$args=array(WT_GED_ID, '_MARNM', $surname, $surname);
		if ($soundex_std) { // works only with latin letters. For other letters it outputs the code '0000'.
			foreach (explode(':', WT_Soundex::soundex_std($surname)) as $value) {
				if ($value != '0000') {
					$sql .= " OR n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
					$args[]=$value;
				}
			}
		}
		if ($soundex_dm) { // works only with predefined letters and lettercombinations. Fot other letters it outputs the code '000000'.
			foreach (explode(':', WT_Soundex::soundex_dm($surname)) as $value) {
				if ($value != '000000') {
					$sql .= " OR n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
					$args[]=$value;
				}
			}
		}
		$sql .= ')';
		$rows=
			WT_DB::prepare($sql)
			->execute($args)
			->fetchAll();
		$data=array();
		foreach ($rows as $row) {
			$data[]=WT_Person::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
		}
		return $data;
	}

	// Get surname from pid
	private function getSurname($pid) {
		$sql= "SELECT n_surname AS surname FROM `##name` WHERE n_file=? AND n_id=? AND n_type=?";
		$args = array(WT_GED_ID, $pid, 'NAME');
		$data= WT_DB::prepare($sql)->execute($args)->fetchOne();
		return $data;
	}

	// Add error or success message
	private function addMessage($controller, $type, $msg) {
		if ($type == "success") {
			$class = "ui-state-highlight";
		}
		if ($type == "error") {
			$class = "ui-state-error";
		}
		$controller->addInlineJavaScript('
			jQuery("#error").text("' . $msg . '").addClass("' . $class . '").show("normal");
			setTimeout(function() {
				jQuery("#error").hide("normal");
			}, 10000);
		');
	}

	// Search within a multiple dimensional array
	private function searchArray($array, $key, $value) {
		$results = array();
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}
			foreach ($array as $subarray) {
				$results = array_merge($results, $this->searchArray($subarray, $key, $value));
			}
		}
		return $results;
	}

	// Sort the array according to the $key['SORT'] input.
	private function sortArray($array, $sort_by){

		$array_keys = array('tree', 'surname', 'display_name', 'pid', 'access_level', 'sort');

		foreach ($array as $pos =>  $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		$return_array = array();
		foreach ($tmp_array as $pos => $val){
			foreach ($array_keys as $key) {
				$key = strtoupper($key);
				$return_array[$pos][$key] = $array[$pos][$key];
			}
		}
		return array_values($return_array);
    }

	private function getCountryList() {
		$list='';
		$countries=
			WT_DB::prepare("SELECT SQL_CACHE p_place as country FROM `##places` WHERE p_parent_id=? AND p_file=?")
			->execute(array('0', WT_GED_ID))->fetchAll(PDO::FETCH_ASSOC);

		foreach ($countries as $country) {
			$list[$country['country']] = $country['country']; // set the country as key to display as option value.
		}
		return $list;
	}

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$this->ftv_reset();
			$this->config();
			break;
		case 'admin_delete':
			$this->delete();
			$this->config();
			break;
		case 'show':
			$this->show();
			break;
		case 'image_data':
			$this->getImageData();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Reset all settings to default
	private function ftv_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'FTV%'")->execute();
		AddToLog($this->getTitle() . ' reset to default values', 'auth');
	}

	// Delete item
	private function delete() {
		$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));
		unset($FTV_SETTINGS[WT_Filter::getInteger('key')]);
		$NEW_FTV_SETTINGS = array_merge($FTV_SETTINGS);
		set_module_setting($this->getName(), 'FTV_SETTINGS',  serialize($NEW_FTV_SETTINGS));
		AddToLog($this->getTitle() . ' item deleted', 'auth');
	}

	// Actions from the configuration page
	private function config() {

		require WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle('Fancy Tree View')
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js');

		if (WT_Filter::postBool('save')) {
			$surname = WT_Filter::post('NEW_FTV_SURNAME');
			$root_id = strtoupper(WT_Filter::post('NEW_FTV_ROOTID', WT_REGEX_XREF));
			if($surname || $root_id) {
				if($surname) {
					$soundex_std = WT_Filter::postBool('soundex_std');
					$soundex_dm = WT_Filter::postBool('soundex_dm');

					$indis = $this->indis_array($surname, $soundex_std, $soundex_dm);
					usort($indis, array('WT_Person', 'CompareBirtDate'));

					if (isset($indis) && count($indis) > 0) {
						$pid = $indis[0]->getXref();
					}
					else {
						$this->addMessage($controller, 'error', WT_I18N::translate('Error: The surname you entered doesn’t exist in this tree.'));
					}
				}

				if($root_id) {
					if ($this->getSurname($root_id)) {
						// check if this person has a spouse and/or children
						$person = $this->get_person($root_id);
						if(!$person->getSpouseFamilies()) {
							$this->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add has no partner and/or children. It is not possible to set this individual as root person.'));
						}
						else {
							$pid = $root_id;
						}
					}
					else {
						$this->addMessage($controller, 'error', WT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $root_id));
					}
				}

				if(isset($pid)) {
					$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));

					if(!empty($FTV_SETTINGS)) {
						$i = 0;
						foreach ($FTV_SETTINGS as $FTV_ITEM) {
							if ($FTV_ITEM['TREE'] == WT_Filter::postInteger('NEW_FTV_TREE')) {
								if($FTV_ITEM['PID'] == $pid) {
									$error = true;
									break;
								}
								else {
									$i++;
								}
							}
						}
						$count = $i + 1;
					}
					else {
						$count = 1;
					}
					if(isset($error) && $error == true) {
						if($surname) {
							$this->addMessage($controller, 'error', WT_I18N::translate('Error: The root person belonging to this surname already exists'));
						}
						if($root_id) {
							$this->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add already exists'));
						}
					}
					else {
						$NEW_FTV_SETTINGS = $FTV_SETTINGS;
						$NEW_FTV_SETTINGS[] = array(
							'TREE' 			=> WT_Filter::postInteger('NEW_FTV_TREE'),
							'SURNAME' 		=> $this->getSurname($pid),
							'DISPLAY_NAME'	=> $this->getSurname($pid),
							'PID'			=> $pid,
							'ACCESS_LEVEL'	=> '2', // default access level = show to visitors
							'SORT'			=> $count
						);
						set_module_setting($this->getName(), 'FTV_SETTINGS',  serialize($NEW_FTV_SETTINGS));
						AddToLog($this->getTitle() . ' config updated', 'config');
					}
				}
			}

			$new_pids = WT_Filter::postArray('NEW_FTV_PID'); $new_display_name = WT_Filter::postArray('NEW_FTV_DISPLAY_NAME'); $new_access_level = WT_Filter::postArray('NEW_FTV_ACCESS_LEVEL'); $new_sort = WT_Filter::postArray('NEW_FTV_SORT');

			if($new_pids || $new_display_name || $new_access_level || $new_sort) {
				// retrieve the array again from the database because it could have been changed due to an add action.
				$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));
				foreach ($new_pids as $key => $new_pid) {
					if(!empty($new_pid)) {
						$new_pid = strtoupper($new_pid); // make sure the PID is entered in the format I200 and not i200.
						if($FTV_SETTINGS[$key]['PID'] != $new_pid) {
							if (!$this->searchArray($FTV_SETTINGS, 'PID', $new_pid)) {
								if($this->getSurname($new_pid)) {
									// check if this person has a spouse and/or children
									$person = $this->get_person($new_pid);
									if(!$person->getSpouseFamilies()) {
										$this->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add has no partner and/or children. It is not possible to set this individual as root person.'));
									}
									else {
										$FTV_SETTINGS[$key]['SURNAME'] = $this->getSurname($new_pid);
										$FTV_SETTINGS[$key]['DISPLAY_NAME'] = $this->getSurname($new_pid);
										$FTV_SETTINGS[$key]['PID'] = $new_pid;
									}
								}
								else {
									$this->addMessage($controller, 'error', WT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $new_pid));
								}
							}
						}
						else {
							$FTV_SETTINGS[$key]['DISPLAY_NAME'] = $new_display_name[$key];
						}
					}
				}

				foreach ($new_access_level as $key => $new_access_level) {
					$FTV_SETTINGS[$key]['ACCESS_LEVEL'] = $new_access_level;
				}

				foreach ($new_sort as $key => $new_sort) {
					$FTV_SETTINGS[$key]['SORT'] = $new_sort;
				}

				$NEW_FTV_SETTINGS = $this->sortArray($FTV_SETTINGS, 'SORT');
				set_module_setting($this->getName(), 'FTV_SETTINGS',  serialize($NEW_FTV_SETTINGS));
			}
			// retrieve the current options from the database
			$FTV_OPTIONS = unserialize(get_module_setting($this->getName(), 'FTV_OPTIONS'));
			$key = WT_Filter::postInteger('NEW_FTV_TREE');
			// check if options are not empty and if the options for the tree are already set. If not add them to the array.
			if ($FTV_OPTIONS) {
				// check if options are changed for the specific key (tree_id)
				if(!array_key_exists($key, $FTV_OPTIONS) || $FTV_OPTIONS[$key] != WT_Filter::postArray('NEW_FTV_OPTIONS')) {
					$NEW_FTV_OPTIONS = $FTV_OPTIONS;
					$NEW_FTV_OPTIONS[WT_Filter::postInteger('NEW_FTV_TREE')] = WT_Filter::postArray('NEW_FTV_OPTIONS');
				}
			}
			else {
				$NEW_FTV_OPTIONS[WT_Filter::postInteger('NEW_FTV_TREE')] = WT_Filter::postArray('NEW_FTV_OPTIONS');
			}
			if(isset($NEW_FTV_OPTIONS)) {
				set_module_setting($this->getName(), 'FTV_OPTIONS',  serialize($NEW_FTV_OPTIONS));
				AddToLog($this->getTitle() . ' config updated', 'config');
			}
		}

		// get module settings (options are coming from function options)
		$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));

		$controller
			->addExternalJavascript(WT_MODULES_DIR . $this->getName() . '/js/ftv.js')
			->addInlineJavascript('autocomplete();')
			->addInlineJavascript('
				var OptionsNumBlocks = ' . $this->options('numblocks') . ';
				var TextOk				= "' . WT_I18N::translate('ok') . '";
				var TextCancel			= "' . WT_I18N::translate('cancel') . '";
				', WT_Controller_Base::JS_PRIORITY_HIGH);

		// Admin page content
		$html = '
			<div id="fancy_treeview-config"><div id="error"></div><h2>' . $this->getTitle() . '</h2>
			<form method="post" id="ftv-options-form" name="configform" action="' . $this->getConfigLink() . '">
				<input type="hidden" name="save" value="1">
				<div id="top">
					<label for="NEW_FTV_TREE" class="label">' . WT_I18N::translate('Family tree') . '</label>
					<select name="NEW_FTV_TREE" id="NEW_FTV_TREE" class="tree">';
						foreach (WT_Tree::getAll() as $tree):
							if($tree->tree_id == WT_GED_ID) {
								$html .= '<option value="' . $tree->tree_id . '" data-ged="' . $tree->tree_name . '" selected="selected">' . $tree->tree_title . '</option>';
							} else {
								$html .= '<option value="' . $tree->tree_id . '" data-ged="' . $tree->tree_name . '">' . $tree->tree_title . '</option>';
							}
						endforeach;
		$html .= '	</select>
					<div class="field">
						<label for="NEW_FTV_SURNAME" class="label">' . WT_I18N::translate('Add a surname') . '</label>
						<input data-autocomplete-type="SURN" type="text" id="NEW_FTV_SURNAME" class="surname" name="NEW_FTV_SURNAME" value="" />
						<label>'.checkbox('soundex_std').WT_I18N::translate('Russell') . '</label>
						<label>'.checkbox('soudex_dm').WT_I18N::translate('Daitch-Mokotoff') . '</label>
					</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Or manually add a root person').checkbox('unlock_field') . '</label>
						<input data-autocomplete-type="INDI" type="text" name="NEW_FTV_ROOTID" id="NEW_FTV_ROOTID" class="root_id" value="" size="5" maxlength="20"/>'.
						print_findindi_link('NEW_FTV_ROOTID');
		$html .= '	</div>
				</div>';
				if (!empty($FTV_SETTINGS) && $this->searchArray($FTV_SETTINGS, 'TREE', WT_GED_ID)):
					global $WT_IMAGES, $WT_TREE;
		$html .= '<table id="fancy_treeview-table" class="modules_table ui-sortable">
					<tr>
						<th>' . WT_I18N::translate('Surname') . '</th>
						<th>' . WT_I18N::translate('Root person') . '</th>
						<th>' . WT_I18N::translate('Menu') . '</th>
						<th>' . WT_I18N::translate('Edit Root person') . '</th>
						<th>' . WT_I18N::translate('Access level') . '</th>
						<th>' . WT_I18N::translate('Delete') . '</th>
					</tr>';
					foreach ($FTV_SETTINGS as $key=>$FTV_ITEM):
						if($FTV_ITEM['TREE'] == WT_GED_ID):
							if(WT_Person::getInstance($FTV_ITEM['PID'])):
		$html .= '				<tr class="sortme">
									<td><input type="hidden" name="NEW_FTV_SORT[' . $key.']" id="NEW_FTV_SORT[' . $key.']" value="' . $FTV_ITEM['SORT'] . '" />
										<span class="showname">' . $FTV_ITEM['DISPLAY_NAME'] . '</span>
										<span class="editname"><input type="text" name="NEW_FTV_DISPLAY_NAME[' . $key.']" id="NEW_FTV_DISPLAY_NAME[' . $key.']" value="' . $FTV_ITEM['DISPLAY_NAME'] . '"/></span>
									</td>
									<td>' . WT_Person::getInstance($FTV_ITEM['PID'])->getFullName() . ' (' . $FTV_ITEM['PID'].')</td>
									<td>
										<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . $WT_TREE->tree_name.'&amp;rootid='.($FTV_ITEM['PID']) . '" target="_blank">';
										if($this->options('use_fullname') == true) {
											$html .= WT_I18N::translate('Descendants of %s', WT_Person::getInstance($FTV_ITEM['PID'])->getFullName());
										}
										else {
											$html .= WT_I18N::translate('Descendants of the %s family', $FTV_ITEM['DISPLAY_NAME']);
										}
		$html .=						'</a>
									</td>
									<td class="wrap">
										<input data-autocomplete-type="INDI" type="text" name="NEW_FTV_PID[' . $key.']" id="NEW_FTV_PID[' . $key.']" value="' . $FTV_ITEM['PID'] . '" size="5" maxlength="20">'.
											print_findindi_link('NEW_FTV_PID[' . $key.']');
		$html .= '					</td>
									<td>' . edit_field_access_level('NEW_FTV_ACCESS_LEVEL[' . $key.']', $FTV_ITEM['ACCESS_LEVEL']) . '</td>
									<td><a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_delete&amp;key=' . $key . '"><i class="fa fa-trash"/></i></td>
								</tr>';
							else:
		$html .= '				<tr>
									<td class="error">
										<input type="hidden" name="NEW_FTV_PID[' . $key.']" value="' . $FTV_ITEM['PID'] . '">
										<input type="hidden" name="NEW_FTV_ACCESS_LEVEL[' . $key.']" value="' . WT_PRIV_HIDE . '">
										<input type="hidden" name="NEW_FTV_DISPLAY_NAME[' . $key.']" value="' . $FTV_ITEM['DISPLAY_NAME'] . '">
										' . $FTV_ITEM['DISPLAY_NAME'] . '</td>
									<td colspan="4" class="error">
										' . WT_I18N::translate('The person with root id %s doesn’t exist anymore in this tree', $FTV_ITEM['PID']) . '
									</td>
									<td><a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_delete&amp;key=' . $key . '"><img src="' . $WT_IMAGES['remove'] . '" alt="icon-delete"/></a></td>';
		$html .= '				</tr>';
							endif;
						endif;
					endforeach;
		$html .='</table>
				<div class="field fullname">
					<label class="label">' . WT_I18N::translate('Use fullname in menu') . '</label>' .
					edit_field_yes_no('NEW_FTV_OPTIONS[USE_FULLNAME]', $this->options('use_fullname')) . '
				</div>';
				endif;
		$html .='<hr/>
				<h3>' . WT_I18N::translate('General Options') . '</h3>
				<div id="bottom">
					<div class="field">
						<label class="label">' . WT_I18N::translate('Number of generation blocks to show') . '</label>'.
						select_edit_control('NEW_FTV_OPTIONS[NUMBLOCKS]', array(WT_I18N::translate('All'), '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'), null, $this->options('numblocks')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Number of generation blocks to show” configuration setting */ WT_I18N::translate('This option is useful for large trees. Set the number of generation blocks to a low level to avoid slow page loading. Below the last generation block a button will appear to add the next set of generation blocks. The new blocks will be added to the blocks already loaded. Click on a “follow” link in the last visible generation block to load the next set of generation blocks.') . '
							</span>
						</div>
					</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Check relationship between partners') . '</label>'.
						edit_field_yes_no('NEW_FTV_OPTIONS[CHECK_RELATIONSHIP]', $this->options('check_relationship')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Check relationship between partners” configuration setting */ WT_I18N::translate('With this option turned on, the script checks if a married couple has the same ancestors. If a relationship between the partners is found a text will appear between brackets after the spouses’ name to indicate the blood relationship. <br><b>Note:</b> this option can be time and/or memory consuming on large trees. It can cause very slow page loading or an ’execution time out error’ on your server. If you notice such a behavior reduce the number of generation blocks to load at once or don’t use it in combination with the option to show singles (see the previous options). If you still experience any problems, don’t use this option at all.') . '
							</span>
						</div>
					</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Show single persons') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_SINGLES]', $this->options('show_singles')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Show single persons” configuration setting */ WT_I18N::translate('Turn this option on to show people who have no partner or children. With this option turned on every child of a family will be shown in a detailed way in the next generation block.') . '
							</span>
						</div>
					</div>
					<div id="ftv_places" class="field">
						<label class="label">' . WT_I18N::translate('Show places') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_PLACES]', $this->options('show_places')) . '
					</div>
					<div id="gedcom_places" class="field">
						<label class="label">' . WT_I18N::translate('Use default family tree settings to abbreviate place names') . '</label>' . edit_field_yes_no('NEW_FTV_OPTIONS[USE_GEDCOM_PLACES]', $this->options('use_gedcom_places')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Use default Gedcom settings to abbreviate place names” configuration setting */ WT_I18N::translate('If you have ticked the “Show places” option you can choose to use the default family tree settings to abbreviate placenames. If you don’t set this option full place names will be shown.') . '
							</span>
						</div>
					</div>
					<div id="country_list" class="field">
							<label class="label">' . WT_I18N::translate('Select your country') . '</label>' .
							select_edit_control('NEW_FTV_OPTIONS[COUNTRY]', $this->getCountryList(), '', $this->options('country')) . '
							<div class="help_text">
								<span class="help_content">' .
									/* I18N: Help text for the “Select your country” configuration setting */ WT_I18N::translate('If you have ticked the “Show places” option but NOT the option to abbreviate placenames, you can set your own country here. Full places will be listed on the Descendancy pages, but when a place includes the name of your own country, this name will be left out. If you don’t select a country then all countries will be shown, including your own.') . '
								</span>
							</div>
						</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Show occupations') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_OCCU]', $this->options('show_occu')) . '
					</div>
					<div id="resize_thumbs" class="field">
						<label class="label">' . WT_I18N::translate('Resize thumbnails') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[RESIZE_THUMBS]', $this->options('resize_thumbs')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Resize thumbnails */ WT_I18N::translate('Choose “yes” to resize the default thumbnails for the Descendency pages. You can set a custom size in percentage or in pixels.<dl><dt>Size in percentage</dt><dd>The original thumbnails will be proportionally resized. This may result in a different width and height for each thumbnail.</dd><dt>Size in pixels</dt><dd>The longest side of the image will be resized to match the size in pixels. The other side will be resized proportionally.</dd><dt>Square thumbs</dt><dd>When you use a square thumbnail, all thumbnails will have the same width and height and the thumbnails will be cropped.</dd></dl>If you choose “no” the default thumbnails will be used with the formats you have set on the family tree configuration page.') . '
							</span>
						</div>
					</div>
					<div id="thumb_size" class="field">
						<label class="label">' . WT_I18N::translate('Thumbnail size') . '</label>
						<input type="text" size="3" id="NEW_FTV_OPTIONS[THUMB_SIZE]" name="NEW_FTV_OPTIONS[THUMB_SIZE]" value="' . $this->options('thumb_size') . '" />
						&nbsp;' .
						select_edit_control('NEW_FTV_OPTIONS[THUMB_RESIZE_FORMAT]', array('1' => WT_I18N::translate('percent'), '2' => WT_I18N::translate('pixels')), null, $this->options('thumb_resize_format')) . '
					</div>
					<div id="square_thumbs" class="field">
						<label class="label">' . WT_I18N::translate('Use square thumbnails') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[USE_SQUARE_THUMBS]', $this->options('use_square_thumbs')) . '
					</div>
				</div>
				<hr/>
				<div class="buttons">
					<button class="btn btn-primary save" type="submit" ">
						<i class="fa fa-floppy-o"></i>'.
						WT_I18N::translate('save') . '
					</button>
					<button class="btn btn-primary reset" type="reset">
						<i class="fa fa-refresh"></i>'.
						WT_I18N::translate('reset') . '
					</button>
					<div id="dialog-confirm" title="' . WT_I18N::translate('reset') . '" style="display:none">
						<p>' . WT_I18N::translate('The settings will be reset to default (for all trees). Are you sure you want to do this?') . '</p>
					</div>
	 			</div>
			</form>
			</div>';

	// output
	ob_start();
	$html .= ob_get_clean();
	echo $html;
	}

	// ************************************************* START OF FRONT PAGE ********************************* //

	// Show
	private function show() {
		global $controller;
		$root			= WT_Filter::get('rootid', WT_REGEX_XREF); // the first pid
		$root_person	= $this->get_person($root);
		$controller		= new WT_Controller_Page;

		if($root_person && $root_person->canDisplayName()) {
			$controller
				->setPageTitle(/* I18N: %s is the surname of the root individual */ WT_I18N::translate('Descendants of %s', $root_person->getFullName()))
				->pageHeader()
				->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
				->addExternalJavascript(WT_MODULES_DIR . $this->getName() . '/js/ftv.js')
				->addInlineJavascript('
					var RootID				= "' . $root . '";
					var ModuleName			= "' . $this->getName() . '";
					var OptionsNumBlocks	= ' . $this->options('numblocks') . ';
					var TextFollow			= "' . WT_I18N::translate('follow') . '";
					', WT_Controller_Base::JS_PRIORITY_HIGH
				)
				->addInlineJavascript('
					autocomplete();

					// submit form to change root id
				    jQuery( "form#change_root" ).submit(function(e) {
				        e.preventDefault();
				        var new_rootid = jQuery("form #new_rootid").val();
						var url = jQuery(location).attr("pathname") + "?mod=' . $this->getName() . '&mod_action=show&rootid=" + new_rootid;
				        jQuery.ajax({
				            url: url,
				            csrf: WT_CSRF_TOKEN,
				            success: function() {
				                window.location = url;
				            },
				            statusCode: {
				                404: function() {
				                    var msg = "' . WT_I18N::translate('This individual does not exist or you do not have permission to view it.') . '";
				                    jQuery("#error").text(msg).addClass("ui-state-error").show();
				                    setTimeout(function() {
				                        jQuery("#error").fadeOut("slow");
				                    }, 3000);
				                    jQuery("form #new_rootid")
				                        .val("")
				                        .focus();
				                }
				            }
				        });
				    });
				');

			// Start page content
			?>
			<div id="fancy_treeview-page">
				<div class="chart_options noprint">
					<form id="change_root">
						<label class="label"><?php echo WT_I18N::translate('Change root person'); ?></label>
						<input type="text" name="new_rootid" id="new_rootid" data-autocomplete-type="INDI">
						<?php echo print_findindi_link('new_rootid'); ?>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
				</div>
				<div id="error"></div>
				<hr class="noprint">
				<div id="page-header">
					<h2><?php echo $controller->getPageTitle(); ?></h2>
				</div>
				<div id="page-body">
					<ol id="fancy_treeview"><?php echo $this->print_page(); ?></ol>
					<div id="btn_next">
						<input type="button" name="next" value="<?php echo WT_I18N::translate('next'); ?>"/>
					</div>
				</div>
			</div>
		<?php } else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				$controller->pageHeader(); ?>
				<p class="ui-state-error"><?php echo WT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
			<?php exit;
		}
	}

	// Print functions
	private function print_page() {
		$root 		= WT_Filter::get('rootid', WT_REGEX_XREF);
		$gen  		= WT_Filter::get('gen', WT_REGEX_INTEGER);
		$pids 		= WT_Filter::get('pids');
		$numblocks  = $this->options('numblocks');

		if ($numblocks == 0) {
			$numblocks = 99;
		}

		$html = '';
		if(!isset($gen) && !isset($pids)) {
			$gen = 1;
			$numblocks = $numblocks - 1;
			$generation = array($root);
			$html .= $this->print_generation($generation, $gen);
		}

		else {
			$generation = explode('|', $pids);
		}

		$lastblock = $gen + $numblocks + 1;// + 1 to get one hidden block.
		while (count($generation) > 0 && $gen < $lastblock) {
			$pids = $generation;
			unset($generation);

			foreach ($pids as $pid) {
				$next_gen[] = $this->get_next_gen($pid);
			}

			foreach($next_gen as $descendants) {
				if(count($descendants) > 0) {
					foreach ($descendants as $descendant) {
						if($this->options('show_singles') == true || $descendant['desc'] == 1) {
							$generation[] = $descendant['pid'];
						}
					}
				}
			}

			if(!empty($generation)) {
				$gen++;
				$html .= $this->print_generation($generation, $gen);
				unset($next_gen, $descendants, $pids);
			}
			else {
				break;
			}
		}
		return $html;
	}

	private function print_generation($generation, $i) {
		// added data attributes to retrieve values easily with jquery (for scroll reference en next generations).
		$html = '<li class="block generation-block" data-gen="' . $i . '" data-pids="' . implode('|', $generation) . '">
					<div class="blockheader ui-state-default">
						<span class="header-title">' . WT_I18N::translate('Generation') . ' ' . $i . '</span>';
			if($i > 1) {
				$html .= '<a href="#body" class="header-link scroll">' . WT_I18N::translate('back to top') . '</a>';
			}
		$html .= '	</div>';

		if ($this->check_privacy($generation, true)) {
			$html .= '<div class="blockcontent generation private">' . WT_I18N::translate('The details of this generation are private.') . '</div>';
		}

		else {
			$html .= '<ol class="blockcontent generation">';
			$generation = array_unique($generation); // needed to prevent the same family added twice to the generation block (this is the case when parents have the same ancestors and are both members of the previous generation).

			foreach ($generation as $pid) {
				$person = $this->get_person($pid);

				// only list persons without parents in the same generation - if they have they will be listed in the next generation anyway.
				// This prevents double listings
				if(!$this->has_parents_in_same_generation($person, $generation)) {
					$family = $this->get_family($person);
					if(!empty($family)) {
						$id = $family->getXref();
					}
					else {
						if ($this->options('show_singles') == true || !$person->getSpouseFamilies()) {
							$id = 'S' . $pid;
						} // Added prefix (S = Single) to prevent double id's.
					}
					$class = $person->canDisplayDetails() ? 'family' : 'family private';
					$html .= '<li id="' . $id . '" class="' . $class . '">' . $this->print_person($person) . '</li>';
				}
			}
			$html .= '</ol></li>';
		}
		return $html;
	}

	private function print_person($person) {
		global $SHOW_PRIVATE_RELATIONSHIPS;

		if($person->canDisplayDetails()) {
			$resize = $this->options('resize_thumbs') === 1 ? true : false;
			$html = '<div class="parents">' . $this->print_thumbnail($person, $this->options('thumb_size'), $this->options('thumb_resize_format'), $this->options('use_square_thumbs'), $resize) . '<a id="' . $person->getXref() . '" href="' . $person->getHtmlUrl() . '"><p class="desc">' . $person->getFullName() . '</a>';
			if ($this->options('show_occu') == true) {
				$html .= $this->printFact($person, 'OCCU');
			}

			$html .= $this->printParents($person).$this->printLifespan($person);

			// get a list of all the spouses
			// First, determine the true number of spouses by checking the family gedcom
			$spousecount = 0;
			foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $i => $family) {
				$spouse = $family->getSpouse($person);
				if ($spouse && $spouse->canDisplayDetails() && ($family->getMarriage() || $family->isNotMarried())) {
					$spousecount++;
				}
			}
      /*
       * Now iterate thru spouses
       * $spouseindex is used for ordinal rather than array index
       * as not all families have a spouse
       * $spousecount is passed rather than doing each time inside function get_spouse
      */
			if($spousecount > 0) {
				$spouseindex = 0;
				foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $i => $family) {
					$spouse = $family->getSpouse($person);
					if ($spouse && $spouse->canDisplayDetails()) {
						$marriage = $family->getMarriage();
						if ($marriage && $marriage->canShow()) {
							$html .= $this->printSpouse($family, $person, $spouse, $spouseindex, $spousecount);
							$spouseindex++;
						} else {
							$html .= $this->printPartner($family, $person, $spouse);
						}

					}
				}
			}

			$html .= '</p></div>';

			// get children for each couple (could be none or just one, $spouse could be empty, includes children of non-married couples)
			foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $family) {
				$spouse = $family->getSpouse($person);
				$html .= $this->printChildren($family, $person, $spouse);
			}

			return $html;
		}
		else {
			if ($SHOW_PRIVATE_RELATIONSHIPS) {
				return WT_I18N::translate('The details of this family are private.');
			}
		}
	}

	private function printSpouse($family, $person, $spouse, $i, $count) {

		$html = ' ';

		// we assume no one married more then 15 times.
		$wordcount = array(
		    WT_I18N::translate('first'),
		    WT_I18N::translate('second'),
		    WT_I18N::translate('third'),
		    WT_I18N::translate('fourth'),
		    WT_I18N::translate('fifth'),
		    WT_I18N::translate('sixth'),
		    WT_I18N::translate('seventh'),
		    WT_I18N::translate('eighth'),
		    WT_I18N::translate('ninth'),
		    WT_I18N::translate('tenth'),
		    WT_I18N::translate('11th'),
		    WT_I18N::translate('12th'),
		    WT_I18N::translate('13th'),
		    WT_I18N::translate('14th'),
		    WT_I18N::translate('15th')
		);

		$wordcount2 = array(
		    WT_I18N::translate('once'),
		    WT_I18N::translate('twice'),
		    WT_I18N::translate('three times'),
		    WT_I18N::translate('four times'),
		    WT_I18N::translate('five times'),
		    WT_I18N::translate('six times'),
		    WT_I18N::translate('seven times'),
		    WT_I18N::translate('eight times'),
		    WT_I18N::translate('nine times'),
		    WT_I18N::translate('ten times'),
		    WT_I18N::translate('11 times'),
		    WT_I18N::translate('12 times'),
		    WT_I18N::translate('13 times'),
		    WT_I18N::translate('14 times'),
		    WT_I18N::translate('15 times')
		);

		if($count > 1) {
			if($i == 0) {
				$person->getSex() == 'M' ? $html .= '<br>' . /* I18N: %s is a number  */ WT_I18N::translate('He married %s', $wordcount2[$count-1]) : $html .= '<br>' . WT_I18N::translate('She married %s', $wordcount2[$count-1]);
				$html .= '. ';
			}
			$person->getSex() == 'M' ? $html .= '<br>' . /* I18N: %s is an ordinal */ WT_I18N::translate('The %s time he married', $wordcount[$i]) : $html .= '<br>' . WT_I18N::translate('The %s time she married', $wordcount[$i]);
		}
		else {
			$person->getSex() == 'M' ? $html .= '<br>' . WT_I18N::translate('He married') : $html .= '<br>' . WT_I18N::translate('She married');
		}

		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';

		// Add relationship note
		if($this->options('check_relationship')) {
			$relationship = $this->check_relationship($person, $spouse, $family);
			if ($relationship) {
				$html .= ' (' . $relationship . ')';
			}
		}

		$html .= $this->printParents($spouse);

		if(!$family->getMarriage()) { // use the default privatized function to determine if marriage details can be shown.
			$html .= '.';
		}
		else {
			// use the facts below only on none private records.
			if ($this->printParents($spouse)) {
				$html .= ',';
			}
			$marrdate = $family->getMarriageDate();
			$marrplace = $family->getMarriagePlace();
			if ($marrdate && $marrdate->isOK()) {
				$html .= $this->print_date($marrdate);
			}
			if (!is_null($family->getMarriagePlace())) {
				$html .= $this->printPlace($family->getMarriagePlace());
			}
			$html .= $this->printLifespan($spouse, true);

			if($family->isDivorced()) {
				$html .= $person->getFullName() . ' ' . WT_I18N::translate('and') . ' ' . $spouse->getFullName() .  ' ' . WT_I18N::translate('divorced') . $this->print_divorce_date($family) . '.';
			}
		}
		return $html;
	}

	private function printPartner($family, $person, $spouse) {
		$html = ' ';
		switch ($person->getSex()) {
			case 'M':
				$html .= '<br>' . WT_I18N::translate('He had a relationship with');
				break;
			case 'F':
				$html .= '<br>' . WT_I18N::translate('She had a relationship with');
				break;
			default:
				$html .= '<br>' . WT_I18N::translate('This individual had a relationship with');
				break;
		}
		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
		$html .= $this->printRelationship($person, $spouse, $family);
		$html .= $this->printParents($spouse);
		if ($family->getFacts('_NMR') && $this->printLifespan($spouse, true)) {
			$html .= $this->printLifespan($spouse, true);
		}

		return $html;
	}

	private function printChildren($family, $person, $spouse) {
		$html = '';

		$match = null;
		if (preg_match('/\n1 NCHI (\d+)/', $family->getGedcomRecord(), $match) && $match[1]==0) {
			$html .= '<div class="children"><p>' . $person->getFullName() . ' ';
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ').$spouse->getFullName() . ' ';
						$html .= WT_I18N::translate_c('Two parents/one child', 'had');
					}
					else {
						$html .= WT_I18N::translate_c('One parent/one child', 'had');
					}
					$html .= ' ' . /* I18N: 'no' is the number zero  */ WT_I18N::translate('no') . ' ' . WT_I18N::translate('children') . '.</p></div>';
		}
		else {
			$children = $family->getChildren();
			if($children) {
				if ($this->check_privacy($children)) {
					$html .= '<div class="children"><p>' . $person->getFullName() . ' ';
					// needs multiple translations for the word 'had' to serve different languages.
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ').$spouse->getFullName() . ' ';
						if (count($children) > 1) {
							$html .= WT_I18N::translate_c('Two parents/multiple children', 'had');
						} else {
							$html .= WT_I18N::translate_c('Two parents/one child', 'had');
						}
					}
					else {
						if (count($children) > 1) {
							$html .= WT_I18N::translate_c('One parent/multiple children', 'had');
						} else {
							$html .= WT_I18N::translate_c('One parent/one child', 'had');
						}
					}
					$html .= ' './* I18N: %s is a number */ WT_I18N::plural('%s child', '%s children', count($children), count($children)) . '.</p></div>';
				}
				else {
					$html .= '<div class="children"><p>'. /* I18N: Note the space at the end of the string */ WT_I18N::translate('Children of ').$person->getFullName();
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
						if (!$family->getMarriage()) {
							// check relationship first (If a relationship is found the information of this parent is printed elsewhere on the page.)
							if ($this->options('check_relationship')) {
								$relationship = $this->check_relationship($person, $spouse, $family);
							}
							if(isset($relationship) && $relationship) {
								$html .= $spouse->getFullName() . ' (' . $relationship.')';
							}
							else {
								// the non-married spouse is not mentioned in the parents div text or elsewhere on the page. So put a link behind the name.
								$html .= '<a class="tooltip" title="" href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
								// Print info of the non-married spouse in a tooltip
								$html .= '<span class="tooltip-text">' . $this->print_tooltip($spouse) . '</span>';
							}
						}
						else {
							$html .= $spouse->getFullName();
						}
					}
					$html .= ':<ol>';

					foreach ($children as $child) {
						if ($child->canDisplayDetails()) {
							$html .= '<li class="child"><a href="' . $child->getHtmlUrl() . '">' . $child->getFullName() . '</a>';
							$pedi = $child->getChildFamilyPedigree($family->getXref());

							if($pedi === 'foster') {
								if ($child->getSex() == 'F') {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('FEMALE', 'foster child') . '</span>';
								} else {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('MALE', 'foster child') . '</span>';
								}
							}
							if($pedi === 'adopted') {
								if ($child->getSex() == 'F') {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('FEMALE', 'adopted') . '</span>';
								} else {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('MALE', 'adopted') . '</span>';
								}
							}
							if ($child->getBirthDate()->isOK() || $child->getDeathdate()->isOK()) {
								$html .= '<span class="lifespan"> (' . $child->getLifeSpan() . ')</span>';
							}

							$child_family = $this->get_family($child);
							if ($child->canDisplayDetails() && $child_family) {
									$html .= ' - <a class="scroll" href="#' . $child_family->getXref() . '"></a>';
							}
							else { // just go to the person details in the next generation (added prefix 'S'for Single Individual, to prevent double ID's.)
								if ($this->options('show_singles') == true) {
									$html .= ' - <a class="scroll" href="#S' . $child->getXref() . '"></a>';
								}
							}
							$html .= '</li>';
						}
					}
					$html .= '</ol></div>';
				}
			}
		}
		return $html;
	}

	private function printParents($person) {
		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$pedi = $person->getChildFamilyPedigree($parents->getXref());

			$html = '';
			switch($person->getSex()) {
				case 'M':
					if ($pedi === 'foster') {
						$html .= ', ' . WT_I18N::translate('foster son of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ', ' . WT_I18N::translate('adopted son of') . ' ';
					} else {
						$html .= ', ' . WT_I18N::translate('son of') . ' ';
					}
					break;
				case 'F':
					if ($pedi === 'foster') {
						$html .= ', ' . WT_I18N::translate('foster daughter of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ', ' . WT_I18N::translate('adopted daughter of') . ' ';
					} else {
						$html .= ', ' . WT_I18N::translate('daughter of') . ' ';
					}
					break;
				default:
					if ($pedi === 'foster') {
						$html .= ', ' . WT_I18N::translate_c('MALE', 'foster child of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ', ' . WT_I18N::translate('adopted child of') . ' ';
					} else {
						$html .= ', ' . WT_I18N::translate('child of') . ' ';
					}
			}

			$father = $parents->getHusband();
			$mother = $parents->getWife();

			if ($father) {
				$html .= $father->getFullName();
			}
			if ($father && $mother) {
				$html .= ' ' . /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
			}
			if ($mother) {
				$html .= $mother->getFullName();
			}

			return $html;
		}
	}

	private function printLifespan($person, $is_spouse = false){
		$html = '';
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$ageOfdeath = get_age_at_event(WT_Date::GetAgeGedcom($birthdate, $deathdate), false);

		$birthdata = false;
		if($birthdate->isOK() || $person->getBirthPlace() != ''){
			$birthdata = true;
			if ($is_spouse == true) {
				$html .= '. ';
				if($person->isDead()) {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PAST', 'She was born') : $html .= WT_I18N::translate_c('PAST', 'He was born');
				}
				else {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PRESENT', 'She was born') : $html .= WT_I18N::translate_c('PRESENT', 'He was born');
				}
			} else {
				$this->printParents($person) || $this->printFact($person, 'OCCU') ? $html .= ', ' : $html .= ' ';
				if ($person->isDead()) {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PAST (FEMALE)', 'was born') : $html .= WT_I18N::translate_c('PAST (MALE)', 'was born');
				}
				else {
				 	$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PRESENT (FEMALE)', 'was born') : $html .= WT_I18N::translate_c('PRESENT (MALE)', 'was born');
				}
			}
			if ($birthdate->isOK()) {
				$html .= $this->print_date($birthdate);
			}
			if ($person->getBirthPlace() != '') {
				$html .= $this->printPlace($person->getBirthPlace());
			}
		}

		$deathdata = false;
		if($deathdate->isOK() || $person->getDeathPlace() != ''){
			$deathdata = true;

			if($birthdata) {
				$html .= ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
				$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('FEMALE', 'died') : $html .= WT_I18N::translate_c('MALE', 'died');
			}
			else {
				$person->getSex() == 'F' ? $html .= '. ' . WT_I18N::translate('She died') : $html .= '. ' . WT_I18N::translate('He died');
			}

			if ($deathdate->isOK()) {
				$html .= $this->print_date($deathdate);
			}
			if ($person->getDeathPlace() != '') {
				$html .= $this->printPlace($person->getDeathPlace());
			}

			if ($birthdate->isOK() && $deathdate->isOK()) {
				if (WT_Date::getAge($birthdate, $deathdate, 0) < 2) {
					$html .= ' './* I18N: %s is the age of death in days/months; %s is a string, e.g. at the age of 2 months */  WT_I18N::translate_c('age in days/months', 'at the age of %s', $ageOfdeath);
				}
				else {
					$html .= ' './* I18N: %s is the age of death in years; %s is a number, e.g. at the age of 40 */  WT_I18N::translate_c('age in years', 'at the age of %s', $ageOfdeath);
				}
			}
		}

		if ($birthdata || $deathdata) {
			$html .= '. ';
		}

		return $html;
	}

	// some couples are known as not married but have children together. Print the info of the "spouse" parent in a tooltip.
	private function print_tooltip($person) {
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$html = '';
		if ($birthdate->isOK()) {
			$html .= '<strong>' . WT_I18N::translate('Birth') . ':</strong> ' . strip_tags($birthdate->Display());
		}
		if ($deathdate->isOK()) {
			$html .= '<br><strong>' . WT_I18N::translate('Death') . ':</strong> ' . strip_tags($deathdate->Display());
		}

		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$father = $parents->getHusband();
			$mother = $parents->getWife();
			if ($father) {
				$html .= '<br><strong>' . WT_I18N::translate('Father') . ':</strong> ' . strip_tags($father->getFullName());
			}
			if ($mother) {
				$html .= '<br><strong>' . WT_I18N::translate('Mother') . ':</strong> ' . strip_tags($mother->getFullName());
			}
		}
		return $html;
	}

	private function printRelationship($person, $spouse, $family) {
		$html = '';
		if ($this->options('check_relationship')) {
			$relationship = $this->check_relationship($person, $spouse, $family);
			if ($relationship) {
				$html .= ' (' . $relationship . ')';
			}
		}
		return $html;
	}

	private function print_thumbnail($person, $thumbsize, $resize_format, $square, $resize) {
		$mediaobject=$person->findHighlightedMedia();
		if ($mediaobject) {
			$html = '';
			if($resize == true) {
				$mediasrc = $resize_format == 1 ? $mediaobject->getServerFilename('thumb') : $mediaobject->getServerFilename('main');
				$thumbwidth = $thumbsize; $thumbheight = $thumbsize;
				$mediatitle = strip_tags($person->getFullName());

				$type = $mediaobject->mimeType();
				if($type == 'image/jpeg' || $type == 'image/png') {

					if (!list($width_orig, $height_orig) = @getimagesize($mediasrc)) {
						return null;
					}

					switch ($type) {
						case 'image/jpeg':
							$image = @imagecreatefromjpeg($mediasrc);
							break;
						case 'image/png':
							$image = @imagecreatefrompng($mediasrc);
							break;
					}

					// fallback if image is in the database but not on the server
					if(isset($width_orig) && isset($height_orig)) {
						$ratio_orig = $width_orig/$height_orig;
					}
					else {
						$ratio_orig = 1;
					}

					if($resize_format == 1) {
						$thumbwidth = $thumbwidth/100 * $width_orig;
						$thumbheight = $thumbheight/100 * $height_orig;
					}

					if($square == true) {
						$thumbheight = $thumbwidth;
						if ($ratio_orig < 1) {
						   $new_height = $thumbwidth/$ratio_orig;
						   $new_width = $thumbwidth;
						} else {
						   $new_width = $thumbheight*$ratio_orig;
						   $new_height = $thumbheight;
						}
					}
					else {
						if($resize_format == 1) {
							$new_width = $thumbwidth;
							$new_height = $thumbheight;
						} elseif ($width_orig > $height_orig) {
							$new_height = $thumbheight/$ratio_orig;
							$new_width 	= $thumbwidth;
						} elseif ($height_orig > $width_orig) {
						   $new_width 	= $thumbheight*$ratio_orig;
						   $new_height 	= $thumbheight;
						} else {
							$new_width 	= $thumbwidth;
							$new_height = $thumbheight;
						}
					}
					$process = @imagecreatetruecolor(round($new_width), round($new_height));
					if($type == 'image/png') { // keep transparancy for png files.
						imagealphablending($process, false);
						imagesavealpha($process, true);
					}
					@imagecopyresampled($process, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);

					$thumb = $square == true ? imagecreatetruecolor($thumbwidth, $thumbheight) : imagecreatetruecolor($new_width, $new_height);
					if($type == 'image/png') {
						imagealphablending($thumb, false);
						imagesavealpha($thumb, true);
					}
					@imagecopyresampled($thumb, $process, 0, 0, 0, 0, $thumbwidth, $thumbheight, $thumbwidth, $thumbheight);

					@imagedestroy($process);
					@imagedestroy($image);

					$width = $square == true ? round($thumbwidth) : round($new_width);
					$height = $square == true ? round($thumbheight) : round($new_height);
					ob_start();$type = 'image/png' ? imagepng($thumb,null,9) : imagejpeg($thumb,null,100);$newThumb = ob_get_clean();
					$html = '<a' .
							' class="'          	. 'gallery'                         			 	. '"' .
							' href="'           	. $mediaobject->getHtmlUrlDirect('main')    		. '"' .
							' type="'           	. $mediaobject->mimeType()                  		. '"' .
							' data-obje-url="'  	. $mediaobject->getHtmlUrl()                		. '"' .
							' data-obje-note="' 	. htmlspecialchars($mediaobject->getNote())			. '"' .
							' data-obje-xref="'		. $mediaobject->getXref()							. '"' .
							' data-title="'     	. WT_Filter::escapeHtml($mediaobject->getFullName()). '"' .
							'><img class="ftv-thumb" src="data:' . $mediaobject->mimeType() . ';base64,'.base64_encode($newThumb) . '" dir="auto" title="' . $mediatitle . '" alt="' . $mediatitle . '" width="' . $width . '" height="' . $height . '"/></a>';
				}
			}
			else {
				$html = $mediaobject->displayImage();
			}
			return $html;
		}
	}

	private function print_date($date) {
		if($date->qual1 || $date->qual2) {
			return ' ' . $date->Display();
		}
		if($date->MinDate()->d > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat dd-mm-yyyy', 'on ').$date->Display();
		}
		if($date->MinDate()->m > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat mmm yyyy', 'in ').$date->Display();
		}
		if($date->MinDate()->y > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat yyyy', 'in ').$date->Display();
		}
	}

	private function print_divorce_date($family) {
		foreach ($family->getAllFactsByType(explode('|', WT_EVENTS_DIV)) as $event) {
			// Only display if it has a date
			if ($event->getDate()->isOK() && $event->canShow()) {
				return $this->print_date($event->getDate());
			}
		}
	}

	private function printFact($person, $tag) {
		$facts = $person->getFacts();
		foreach ($facts as $fact) {
			if ($fact->getTag()== $tag) {
				$str = $fact->getDetail();
				$str = rtrim($str, ".");
				$html = ', ' . $str;
				return $html;
			}
		}
	}

	private function printPlace($place) {
		if($this->options('show_places') == true) {
			$place = new WT_Place($place, WT_GED_ID);
			$html = ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before placesnames', 'in ');
			if	($this->options('use_gedcom_places') == true) {
				$html .= $place->getShortName();
			} else {
				$country = $this->options('country');
				$new_place = array_reverse(explode(", ", $place->getGedcomName()));
				if (!empty($country) && $new_place[0] == $country) {
					unset($new_place[0]);
					$html .= '<span dir="auto">' . WT_Filter::escapeHtml(implode(', ', array_reverse($new_place))) . '</span>';
				} else {
					$html .= $place->getFullName();
				}
			}
			return $html;
		}
	}

	// Other functions
	private function get_person($pid) {
		$person=WT_Person::getInstance($pid);
		return $person;
	}

	private function get_family($person) {
		foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $family) {
			return $family;
		}
	}

	private function get_next_gen($pid) {
		$person = $this->get_person($pid);
		foreach($person->getSpouseFamilies() as $family) {
			$children = $family->getChildren();
			if($children) {
				foreach ($children as $key => $child) {
					$key = $family->getXref() . '-' . $key; // be sure the key is unique.
					$ng[$key]['pid'] = $child->getXref();
					$child->getSpouseFamilies(WT_PRIV_HIDE) ? $ng[$key]['desc'] = 1 : $ng[$key]['desc'] = 0;
				}
			}
		}
		if (isset($ng)) {
			return $ng;
		}
	}

	// check if a person has parents in the same generation
	private function has_parents_in_same_generation($person, $generation) {
		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$father = $parents->getHusband();
			$mother = $parents->getWife();
			if ($father) {
				$father = $father->getXref();
			}
			if ($mother) {
				$mother = $mother->getXref();
			}
			if(in_array($father, $generation) || in_array($mother, $generation)) {
				return true;
			}
		}
	}

	// check (blood) relationship between partners
	private function check_relationship($person, $spouse, $family) {
		$count = count($family->getChildren());
		for($i = 0; $i <= $count; $i++) { // the number of paths is equal to the number of children, because every relationship is checked through each child.
										  // and we need the relationship from the next path.
			$nodes = get_relationship($person, $spouse, false, 0, $i);

			if (!is_array($nodes)) {
				return '';
			}

			$path=array_slice($nodes['relations'], 1);

			$combined_path='';
			$display = false;
			foreach ($path as $key => $rel) {
				$rel_to_exclude = array('son', 'daughter', 'child'); // don't return the relationship path through the children
				if($key == 0 && in_array($rel, $rel_to_exclude)) {
					$display = false;
					break;
				}
				$rel_to_find = array('sister', 'brother', 'sibling'); // one of these relationships must be in the path
				if(in_array($rel, $rel_to_find)) {
					$display = true;
					break;
				}
			}

			if($display == true) {
				foreach ($path as $rel) {
					$combined_path.=substr($rel, 0, 3);
				}
				return get_relationship_name_from_path($combined_path, $person, $spouse);
			}
		}
	}

	private function check_privacy($record, $xrefs = false) {
		$count = 0;
		foreach ($record as $person) {
			if ($xrefs) {
				$person = $this->get_person($person);
			}
			if($person->canDisplayDetails()) {
				$count++;
			}
		}
		if ($count < 1) {
			return true;
		}
	}

	private function getImageData() {
		Zend_Session::writeClose();
		header('Content-type: text/html; charset=UTF-8');
		$xref = WT_Filter::get('mid');
		$mediaobject = WT_Media::getInstance($xref);
		if ($mediaobject) {
			echo $mediaobject->getServerFilename();
		}
	}

	// ************************************************* START OF MENU ********************************* //

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 120;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;

		$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));

		if(!empty($FTV_SETTINGS)) {
			if ($SEARCH_SPIDER) {
				return null;
			}

			foreach ($FTV_SETTINGS as $FTV_ITEM) {
				if($FTV_ITEM['TREE'] == WT_GED_ID && $FTV_ITEM['ACCESS_LEVEL'] >= WT_USER_ACCESS_LEVEL) {
					$FTV_GED_SETTINGS[] = $FTV_ITEM;
				}
			}
			if (!empty($FTV_GED_SETTINGS)) {
				$menu = new WT_Menu(WT_I18N::translate('Descendants'), '#', 'menu-fancy_treeview');

				foreach($FTV_GED_SETTINGS as $FTV_ITEM) {
					if(WT_Person::getInstance($FTV_ITEM['PID'])) {
						if($this->options('use_fullname') == true) {
							$submenu = new WT_Menu(WT_I18N::translate('Descendants of %s', WT_Person::getInstance($FTV_ITEM['PID'])->getFullName()), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $FTV_ITEM['PID'], 'menu-fancy_treeview-' . $FTV_ITEM['PID']);
						}
						else {
							$submenu = new WT_Menu(WT_I18N::translate('%s family', $FTV_ITEM['DISPLAY_NAME']), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $FTV_ITEM['PID'], 'menu-fancy_treeview-' . $FTV_ITEM['PID']);
						}
						$menu->addSubmenu($submenu);
					}
				}

				return $menu;
			}
		}
	}

}
