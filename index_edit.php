<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'index_edit.php');
require './includes/session.php';

$controller = new KT_Controller_Ajax();

// Only one of $user_id and $gedcom_id should be set
$user_id = safe_REQUEST($_REQUEST, 'user_id');
if ($user_id) {
	$gedcom_id = null;
} else {
	$gedcom_id = safe_REQUEST($_REQUEST, 'gedcom_id');
}

// Only an admin can edit the "default" page
// Only managers can edit the "home page"
if (
	$gedcom_id < 0 && !KT_USER_IS_ADMIN ||
	$gedcom_id > 0 && !userGedcomAdmin(KT_USER_ID, $gedcom_id) ||
	$user_id && KT_USER_ID != $user_id && !KT_USER_IS_ADMIN
) {
	$controller->pageHeader();
	$controller->addInlineJavascript('window.location.reload();');
	exit;
}

$action = KT_Filter::get('action');

if (isset($_REQUEST['main'])) {
	$main = $_REQUEST['main'];
} else {
	$main = array();
}
if (isset($_REQUEST['right'])) {
	$right = $_REQUEST['right'];
} else {
	$right = array();
}

// Define all the icons we're going to use
$IconUarrow			= 'icon-uarrow';
$IconDarrow			= 'icon-darrow';
if($TEXT_DIRECTION == 'ltr') {
	$IconRarrow		= 'icon-rarrow';
	$IconLarrow		= 'icon-larrow';
	$IconRDarrow	= 'icon-rdarrow';
	$IconLDarrow	= 'icon-ldarrow';
} else {
	$IconRarrow		= 'icon-larrow';
	$IconLarrow		= 'icon-rarrow';
	$IconRDarrow	= 'icon-ldarrow';
	$IconLDarrow	= 'icon-rdarrow';
}

$all_blocks = array();
foreach (KT_Module::getActiveBlocks() as $name => $block) {
	if ($user_id || $gedcom_id && $block->isGedcomBlock()) {
		$all_blocks[$name] = $block;
	}
}

$blocks = get_gedcom_blocks($gedcom_id);

if ($action == 'update') {
	Zend_Session::writeClose();
	foreach (array('main', 'side') as $location) {
		if ($location == 'main') {
			$new_blocks = $main;
		} else {
			$new_blocks = $right;
		}
		foreach ($new_blocks as $order=>$block_name) {
			if (is_numeric($block_name)) {
				// existing block
				KT_DB::prepare("UPDATE `##block` SET block_order=? WHERE block_id=?")->execute(array($order, $block_name));
				// existing block moved location
				KT_DB::prepare("UPDATE `##block` SET location=? WHERE block_id=?")->execute(array($location, $block_name));
			} else {
				// new block
				if ($user_id) {
					KT_DB::prepare("INSERT INTO `##block` (user_id, location, block_order, module_name) VALUES (?, ?, ?, ?)")->execute(array($user_id, $location, $order, $block_name));
				} else {
					KT_DB::prepare("INSERT INTO `##block` (gedcom_id, location, block_order, module_name) VALUES (?, ?, ?, ?)")->execute(array($gedcom_id, $location, $order, $block_name));
				}
			}
		}
		// deleted blocks
		foreach ($blocks[$location] as $block_id=>$block_name) {
			if (!in_array($block_id, $main) && !in_array($block_id, $right)) {
				KT_DB::prepare("DELETE FROM `##block_setting` WHERE block_id=?")->execute(array($block_id));
				KT_DB::prepare("DELETE FROM `##block`         WHERE block_id=?")->execute(array($block_id));
			}
		}
	}
	exit;
}

$controller
	->pageHeader()
	->addInlineJavascript('
	/**
	* Move Up Block Javascript function
	*
	* This function moves the selected option up in the given select list
	* @param String section_name the name of the select to move the options
	*/
	function move_up_block(section_name) {
		section_select = document.getElementById(section_name);
		if (section_select) {
			if (section_select.selectedIndex <= 0) return false;
			index = section_select.selectedIndex;
			temp = new Option(section_select.options[index-1].text, section_select.options[index-1].value);
			section_select.options[index-1] = new Option(section_select.options[index].text, section_select.options[index].value);
			section_select.options[index] = temp;
			section_select.selectedIndex = index-1;
		}
	}

	/**
	* Move Down Block Javascript function
	*
	* This function moves the selected option down in the given select list
	* @param String section_name the name of the select to move the options
	*/
	function move_down_block(section_name) {
		section_select = document.getElementById(section_name);
		if (section_select) {
			if (section_select.selectedIndex < 0) return false;
			if (section_select.selectedIndex >= section_select.length-1) return false;
			index = section_select.selectedIndex;
			temp = new Option(section_select.options[index+1].text, section_select.options[index+1].value);
			section_select.options[index+1] = new Option(section_select.options[index].text, section_select.options[index].value);
			section_select.options[index] = temp;
			section_select.selectedIndex = index+1;
		}
	}

	/**
	* Move Block from one column to the other Javascript function
	*
	* This function moves the selected option down in the given select list
	* @author KosherJava
	* @param String from_column the name of the select to move the option from
	* @param String to_column the name of the select to remove the option to
	*/
	function move_left_right_block(from_column, to_column) {
		to_select = document.getElementById(to_column);
		from_select = document.getElementById(from_column);
		instruct = document.getElementById("instructions");
		if ((to_select) && (from_select)) {
			add_option = from_select.options[from_select.selectedIndex];
			if (to_column != "available_select") {
				to_select.options[to_select.length] = new Option(add_option.text, add_option.value);
			}
			if (from_column != "available_select") {
				from_select.options[from_select.selectedIndex] = null; //remove from list
			}
		}
	}
	/**
	* Select Options Javascript function
	*
	* This function selects all the options in the multiple select lists
	*/
	function select_options() {
		section_select = document.getElementById("main_select");
		if (section_select) {
			for (i=0; i<section_select.length; i++) {
				section_select.options[i].selected=true;
			}
		}
		section_select = document.getElementById("right_select");
		if (section_select) {
			for (i=0; i<section_select.length; i++) {
				section_select.options[i].selected=true;
			}
		}
		return true;
	}
	/**
	* Show Block Description Javascript function
	*
	* This function shows a description for the selected option
	* @param String list_name the name of the select to get the option from
	*/
	function show_description(list_name) {
		list_select = document.getElementById(list_name);
		instruct = document.getElementById("instructions");
		if (block_descr[list_select.options[list_select.selectedIndex].value] && instruct) {
			instruct.innerHTML = block_descr[list_select.options[list_select.selectedIndex].value];
		} else {
			instruct.innerHTML = block_descr["advice1"];
		}
		list1 = document.getElementById("main_select");
		list2 = document.getElementById("available_select");
		list3 = document.getElementById("right_select");
		if (list_name=="main_select") {
			list2.selectedIndex = -1;
			list3.selectedIndex = -1;
		}
		if (list_name=="available_select") {
			list1.selectedIndex = -1;
			list3.selectedIndex = -1;
		}
		if (list_name=="right_select") {
			list1.selectedIndex = -1;
			list2.selectedIndex = -1;
		}
	}
	var block_descr = new Array();
	');


	// Load Block Description array for use by javascript
	foreach ($all_blocks as $block_name => $block) {
		$controller->addInlineJavascript(
			'block_descr["' . $block_name . '"] = "' . addslashes($block->getDescription()) . '";'
		);
	}
	$controller->addInlineJavascript(
		'block_descr["advice1"] = "' . KT_I18N::translate('Highlight a block name and then click on one of the arrow icons to move that highlighted block in the indicated direction.') . '";'
	);

?>
<form name="config_setup" method="post" action="index_edit.php?action=update" onsubmit="select_options(); return modalDialogSubmitAjax(this);" >
	<input type="hidden" name="user_id"   value="<?php echo $user_id; ?>">
	<input type="hidden" name="gedcom_id" value="<?php echo $gedcom_id; ?>">
	<table id="change_blocks">
		<!-- NOTE: Row 1: Column legends -->
		<tr>
			<td class="descriptionbox center vmiddle" colspan="2">
				<b><?php echo KT_I18N::translate('Main Section Blocks'); ?></b>
			</td>
			<td class="descriptionbox center vmiddle" colspan="3">
				<b><?php echo KT_I18N::translate('Available Blocks'); ?></b>
			</td>
			<td class="descriptionbox center vmiddle" colspan="2">
				<b><?php echo KT_I18N::translate('Right Section Blocks'); ?></b>
			</td>
		</tr>
		<tr>
			<!-- NOTE: Row 2 column 1: Up/Down buttons for left (main) block list -->
			<td class="optionbox center vmiddle">
				<a onclick="move_up_block('main_select');" title="<?php echo KT_I18N::translate('Move up'); ?>"class="<?php echo $IconUarrow; ?>"></a>
				<br>
				<a onclick="move_down_block('main_select');" title="<?php echo KT_I18N::translate('Move down'); ?>"class="<?php echo $IconDarrow; ?>"></a>
				<br><br>
				<?php echo help_link('block_move_up'); ?>
			</td>
			<!-- NOTE: Row 2 column 2: Left (Main) block list -->
			<td class="optionbox center">
				<select multiple="multiple" id="main_select" name="main[]" size="10" onchange="show_description(\'main_select\');">
					<?php foreach ($blocks['main'] as $block_id => $block_name) { ?>
						<option value="<?php echo $block_id; ?>"><?php echo $all_blocks[$block_name]->getTitle() . ' (id ' . $block_id . ')'; ?></option>
					<?php } ?>
				</select>
			</td>
			<!-- NOTE: Row 2 column 3: Left/Right buttons for left (main) block list -->
			<td class="optionbox center vmiddle">
				<a onclick="move_left_right_block('main_select', 'right_select');" title="<?php echo KT_I18N::translate('Move Right'); ?>"class="<?php echo $IconRDarrow; ?>"></a>
				<br>
				<a onclick="move_left_right_block('main_select', 'available_select');" title="<?php echo KT_I18N::translate('Remove'); ?>"class="<?php echo $IconRarrow; ?>"></a>
				<br>
				<a onclick="move_left_right_block('available_select', 'main_select');" title="<?php echo KT_I18N::translate('Add'); ?>"class="<?php echo $IconLarrow; ?>"></a>
				<br><br>
				<?php echo help_link('block_move_right'); ?>
			</td>
			<!-- NOTE: Row 2 column 4: Middle (Available) block list -->
			<td class="optionbox center">
				<select id="available_select" name="available[]" size="10" onchange="show_description(\'available_select\');">
					<?php foreach ($all_blocks as $block_name=>$block) { ?>
						<option value="<?php echo $block_name; ?>"><?php echo $block->getTitle(); ?></option>
					<?php } ?>
				</select>
			</td>
			<!-- NOTE: Row 2 column 5: Left/Right buttons for right block list -->
			<td class="optionbox center vmiddle">
				<a onclick="move_left_right_block('right_select', 'main_select');" title="<?php echo KT_I18N::translate('Move Left'); ?>"class="<?php echo $IconLDarrow; ?>"></a>
				<br>
				<a onclick="move_left_right_block('right_select', 'available_select');" title="<?php echo KT_I18N::translate('Remove'); ?>"class="<?php echo $IconLarrow; ?>"></a>
				<br>
				<a onclick="move_left_right_block('available_select', 'right_select');" title="<?php echo KT_I18N::translate('Add'); ?>"class="<?php echo $IconRarrow; ?>"></a>
				<br><br>
				<?php echo help_link('block_move_right'); ?>
			</td>
			<!-- NOTE: Row 2 column 6: Right block list -->
			<td class="optionbox center">
				<select multiple="multiple" id="right_select" name="right[]" size="10" onchange="show_description('right_select');">
					<?php foreach ($blocks['side'] as $block_id=>$block_name) { ?>
						<option value="<?php echo $block_id; ?>"><?php echo $all_blocks[$block_name]->getTitle() . ' (id ' . $block_id . ')'; ?></option>
					<?php } ?>
				</select>
			</td>
			<!-- NOTE: Row 2 column 7: Up/Down buttons for right block list -->
			<td class="optionbox center vmiddle">
				<a onclick="move_up_block('right_select');" title="<?php echo KT_I18N::translate('Move up'); ?>"class="<?php echo $IconUarrow; ?>"></a>
				<br>
				<a onclick="move_down_block('right_select');" title="<?php echo KT_I18N::translate('Move down'); ?>"class="<?php echo $IconDarrow; ?>"></a>
				<br><br>
				<?php echo help_link('block_move_up'); ?>
			</td>
		</tr>
		<!-- NOTE: Row 3 columns 1-7: Summary description of currently selected block -->
		<tr>
			<td class="descriptionbox wrap" colspan="7">
				<div id="instructions">
					<?php echo KT_I18N::translate('Highlight a block name and then click on one of the arrow icons to move that highlighted block in the indicated direction.'); ?>
				</div>
			</td>
		</tr>
		<tr>
			<td class="topbottombar" colspan="7">
				<button class="btn btn-primary show" type="submit">
					<i class="fa fa-floppy-o"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</td>
		</tr>
	</table>
</form>
