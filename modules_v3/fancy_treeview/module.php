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
		return /* I18N: Description of the module */ WT_I18N::translate('A narrative report of the descendants of one family or individual');
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

	// Extend WT_Module_Config
	public function modAction($mod_action) {

		$ftv = new WT_Controller_FancyTreeView();

		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$controller->ftv_reset();
			$this->config();
			break;
		case 'admin_delete':
			$controller->delete();
			$this->config();
			break;
		case 'show':
			$this->show();
			break;
		case 'image_data':
			$controller->getImageData();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Actions from the configuration page
	private function config() {

		require WT_ROOT.'includes/functions/functions_edit.php';

		$ftv = new WT_Controller_FancyTreeView();

		$controller = new WT_Controller_Page;
		$controller
			->restrictAccess(WT_USER_IS_ADMIN)
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

					$indis = $controller->indis_array($surname, $soundex_std, $soundex_dm);
					usort($indis, array('WT_Person', 'CompareBirtDate'));

					if (isset($indis) && count($indis) > 0) {
						$pid = $indis[0]->getXref();
					}
					else {
						$controller->addMessage($controller, 'error', WT_I18N::translate('Error: The surname you entered doesn’t exist in this tree.'));
					}
				}

				if($root_id) {
					if ($controller->getSurname($root_id)) {
						// check if this person has a spouse and/or children
						$person = $controller->get_person($root_id);
						if(!$person->getSpouseFamilies()) {
							$controller->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add has no partner and/or children. It is not possible to set this individual as root person.'));
						}
						else {
							$pid = $root_id;
						}
					}
					else {
						$controller->addMessage($controller, 'error', WT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $root_id));
					}
				}

				if(isset($pid)) {
					$FTV_SETTINGS = unserialize(get_module_setting($controller->getName(), 'FTV_SETTINGS'));

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
							$controller->addMessage($controller, 'error', WT_I18N::translate('Error: The root person belonging to this surname already exists'));
						}
						if($root_id) {
							$controller->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add already exists'));
						}
					}
					else {
						$NEW_FTV_SETTINGS = $FTV_SETTINGS;
						$NEW_FTV_SETTINGS[] = array(
							'TREE' 			=> WT_Filter::postInteger('NEW_FTV_TREE'),
							'SURNAME' 		=> $ftv->getSurname($pid),
							'DISPLAY_NAME'	=> $ftv->getSurname($pid),
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
							if (!$ftv->searchArray($FTV_SETTINGS, 'PID', $new_pid)) {
								if($ftv->getSurname($new_pid)) {
									// check if this person has a spouse and/or children
									$person = $ftv->get_person($new_pid);
									if(!$person->getSpouseFamilies()) {
										$ftv->addMessage($controller, 'error', WT_I18N::translate('Error: The root person you are trying to add has no partner and/or children. It is not possible to set this individual as root person.'));
									}
									else {
										$FTV_SETTINGS[$key]['SURNAME'] = $ftv->getSurname($new_pid);
										$FTV_SETTINGS[$key]['DISPLAY_NAME'] = $ftv->getSurname($new_pid);
										$FTV_SETTINGS[$key]['PID'] = $new_pid;
									}
								}
								else {
									$ftv->addMessage($controller, 'error', WT_I18N::translate('Error: An individual with ID %s doesn’t exist in this tree.', $new_pid));
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

				$NEW_FTV_SETTINGS = $ftv->sortArray($FTV_SETTINGS, 'SORT');
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
			->addExternalJavascript(WT_STATIC_URL . 'js/fancytreeview.js')
			->addInlineJavascript('autocomplete();')
			->addInlineJavascript('
				var OptionsNumBlocks = ' . $ftv->options('numblocks') . ';
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
				if (!empty($FTV_SETTINGS) && $ftv->searchArray($FTV_SETTINGS, 'TREE', WT_GED_ID)):
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
										<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . $WT_TREE->tree_name.'&amp;rootid='.($FTV_ITEM['PID']) . '" target="_blank" rel="noopener noreferrer">';
										if($ftv->options('use_fullname') == true) {
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
					edit_field_yes_no('NEW_FTV_OPTIONS[USE_FULLNAME]', $ftv->options('use_fullname')) . '
				</div>';
				endif;
		$html .='<hr/>
				<h3>' . WT_I18N::translate('General Options') . '</h3>
				<div id="bottom">
					<div class="field">
						<label class="label">' . WT_I18N::translate('Number of generation blocks to show') . '</label>'.
						select_edit_control('NEW_FTV_OPTIONS[NUMBLOCKS]', array(WT_I18N::translate('All'), '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'), null, $ftv->options('numblocks')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Number of generation blocks to show” configuration setting */ WT_I18N::translate('This option is useful for large trees. Set the number of generation blocks to a low level to avoid slow page loading. Below the last generation block a button will appear to add the next set of generation blocks. The new blocks will be added to the blocks already loaded. Click on a “follow” link in the last visible generation block to load the next set of generation blocks.') . '
							</span>
						</div>
					</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Check relationship between partners') . '</label>'.
						edit_field_yes_no('NEW_FTV_OPTIONS[CHECK_RELATIONSHIP]', $ftv->options('check_relationship')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Check relationship between partners” configuration setting */ WT_I18N::translate('With this option turned on, the script checks if a married couple has the same ancestors. If a relationship between the partners is found a text will appear between brackets after the spouses’ name to indicate the blood relationship. <br><b>Note:</b> this option can be time and/or memory consuming on large trees. It can cause very slow page loading or an ’execution time out error’ on your server. If you notice such a behavior reduce the number of generation blocks to load at once or don’t use it in combination with the option to show singles (see the previous options). If you still experience any problems, don’t use this option at all.') . '
							</span>
						</div>
					</div>
					<div class="field">
						<label class="label">' . WT_I18N::translate('Show single persons') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_SINGLES]', $ftv->options('show_singles')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Show single persons” configuration setting */ WT_I18N::translate('Turn this option on to show people who have no partner or children. With this option turned on every child of a family will be shown in a detailed way in the next generation block.') . '
							</span>
						</div>
						</div>';
					if ($ftv->getCountryList()) {
	$html .= '			<div id="ftv_places" class="field">
							<label class="label">' . WT_I18N::translate('Show places') . '</label>' .
							edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_PLACES]', $ftv->options('show_places')) . '
						</div>
						<div id="gedcom_places" class="field">
							<label class="label">' . WT_I18N::translate('Use default family tree settings to abbreviate place names') . '</label>' . edit_field_yes_no('NEW_FTV_OPTIONS[USE_GEDCOM_PLACES]', $ftv->options('use_gedcom_places')) . '
							<div class="help_text">
								<span class="help_content">' .
									/* I18N: Help text for the “Use default Gedcom settings to abbreviate place names” configuration setting */ WT_I18N::translate('If you have ticked the “Show places” option you can choose to use the default family tree settings to abbreviate placenames. If you don’t set this option full place names will be shown.') . '
								</span>
							</div>
						</div>
						<div id="country_list" class="field">
							<label class="label">' . WT_I18N::translate('Select your country') . '</label>' .
							select_edit_control('NEW_FTV_OPTIONS[COUNTRY]', $ftv->getCountryList(), '', $ftv->options('country')) . '
							<div class="help_text">
								<span class="help_content">' .
									/* I18N: Help text for the “Select your country” configuration setting */ WT_I18N::translate('If you have ticked the “Show places” option but NOT the option to abbreviate placenames, you can set your own country here. Full places will be listed on the Descendancy pages, but when a place includes the name of your own country, this name will be left out. If you don’t select a country then all countries will be shown, including your own.') . '
								</span>
							</div>
						</div>';
					}
	$html .= '		<div class="field">
						<label class="label">' . WT_I18N::translate('Show occupations') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[SHOW_OCCU]', $ftv->options('show_occu')) . '
					</div>
					<div id="resize_thumbs" class="field">
						<label class="label">' . WT_I18N::translate('Resize thumbnails') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[RESIZE_THUMBS]', $ftv->options('resize_thumbs')) . '
						<div class="help_text">
							<span class="help_content">' .
								/* I18N: Help text for the “Resize thumbnails */ WT_I18N::translate('Choose “yes” to resize the default thumbnails for the Descendency pages. You can set a custom size in percentage or in pixels.<dl><dt>Size in percentage</dt><dd>The original thumbnails will be proportionally resized. This may result in a different width and height for each thumbnail.</dd><dt>Size in pixels</dt><dd>The longest side of the image will be resized to match the size in pixels. The other side will be resized proportionally.</dd><dt>Square thumbs</dt><dd>When you use a square thumbnail, all thumbnails will have the same width and height and the thumbnails will be cropped.</dd></dl>If you choose “no” the default thumbnails will be used with the formats you have set on the family tree configuration page.') . '
							</span>
						</div>
					</div>
					<div id="thumb_size" class="field">
						<label class="label">' . WT_I18N::translate('Thumbnail size') . '</label>
						<input type="text" size="3" id="NEW_FTV_OPTIONS[THUMB_SIZE]" name="NEW_FTV_OPTIONS[THUMB_SIZE]" value="' . $ftv->options('thumb_size') . '" />
						&nbsp;' .
						select_edit_control('NEW_FTV_OPTIONS[THUMB_RESIZE_FORMAT]', array('1' => WT_I18N::translate('percent'), '2' => WT_I18N::translate('pixels')), null, $ftv->options('thumb_resize_format')) . '
					</div>
					<div id="square_thumbs" class="field">
						<label class="label">' . WT_I18N::translate('Use square thumbnails') . '</label>' .
						edit_field_yes_no('NEW_FTV_OPTIONS[USE_SQUARE_THUMBS]', $ftv->options('use_square_thumbs')) . '
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

		$ftv = new WT_Controller_FancyTreeView();

		global $controller;
		$root			= WT_Filter::get('rootid', WT_REGEX_XREF); // the first pid
		$root_person	= $ftv->get_person($root);
		$controller		= new WT_Controller_Page;

		if($root_person && $root_person->canDisplayName()) {
			$controller
				->setPageTitle(/* I18N: %s is the surname of the root individual */ WT_I18N::translate('Descendants of %s', $root_person->getFullName()))
				->pageHeader()
				->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
				->addExternalJavascript(WT_STATIC_URL . 'js/fancytreeview.js')
				->addInlineJavascript('
					var RootID				= "' . $root . '";
					var ModuleName			= "' . $this->getName() . '";
					var OptionsNumBlocks	= ' . $ftv->options('numblocks') . ';
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
			<div id="resource-page">
				<h2><?php echo $this->getTitle(); ?></h2>
				<div class="chart_options noprint">
					<div class="help_text">
						<div class="help_content">
							<h5><?php echo $this->getDescription(); ?></h5>
							<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
							<div class="hidden">
								<?php echo /* I18N: help for resource facts and events module */ WT_I18N::translate('The list of available facts and events are those set by the site administrator as "All individual facts" and "Unique individual facts" at Administration > Family trees > <i>your family tree</i> > "Edit options" tab and therefore only GEDCOM first-level records.<br>Date filters must be 4-digit year only. Place, type and detail filters can be any string of characters you expect to find in those data fields. The "Type" field is only avaiable for Custom facts and Custom events.'); ?>
							</div>
						</div>
					<form id="change_root">
						<div class="chart_options">
							<label for = "new_rootid" class="label"><?php echo WT_I18N::translate('Change root person'); ?></label>
							<input type="text" data-autocomplete-type="INDI" name="new_rootid" id="new_rootid" value="<?php echo $root; ?>">
						</div>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
				</div>
				<hr class="noprint">
				<div id="fancy_treeview-page">
					<div id="error"></div>
					<div id="page-header">
						<h2>
							<?php echo $controller->getPageTitle() ?>
							<?php if (WT_USER_IS_ADMIN) { ?>
								<a href="module.php?mod=fancy_treeview&amp;mod_action=admin_config" target="_blank" rel="noopener noreferrer" class="noprint">
									<i class="fa fa-cog"></i>
								</a>
							<?php } ?>
						</h2>
					</div>
					<div id="page-body">
						<ol id="fancy_treeview"><?php echo $ftv->printPage('descendants'); ?></ol>
						<div id="btn_next">
							<button class="btn btn-next" type="button" name="next" value="<?php echo WT_I18N::translate('next'); ?>" title="<?php echo WT_I18N::translate('Show more generations'); ?>">
								<i class="fa fa-arrow-down"></i>
								<?php echo WT_I18N::translate('next'); ?>
							</button>
						</div>
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

		$ftv = new WT_Controller_FancyTreeView();

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
						if($ftv->options('use_fullname') == true) {
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
