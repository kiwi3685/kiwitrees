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

define('KT_SCRIPT_NAME', 'index.php');
require './includes/session.php';

// The only option for action is "ajax"
$action = safe_REQUEST($_REQUEST, 'action', 'ajax');


//-- get the blocks list
$blocks = get_gedcom_blocks(KT_GED_ID);

$all_blocks = KT_Module::getActiveBlocks();

// We generate individual blocks using AJAX
if ($action == 'ajax') {
	$controller = new KT_Controller_Ajax();
	$controller->pageHeader();

	// Check we're displaying an allowable block.
	$block_id = KT_Filter::get('block_id');
	if (array_key_exists($block_id, $blocks['main'])) {
		$module_name = $blocks['main'][$block_id];
	} elseif (array_key_exists($block_id, $blocks['side'])) {
		$module_name = $blocks['side'][$block_id];
	} else {
		exit;
	}
	if (array_key_exists($module_name, $all_blocks)) {
		$class_name = $module_name.'_KT_Module';
		$module = new $class_name;
		$module->getBlock($block_id);
	}
	if (KT_DEBUG_SQL) {
		echo KT_DB::getQueryLog();
	}
	exit;
}

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_TREE_TITLE)
	->setMetaRobots('index,follow')
	->setCanonicalUrl(KT_SCRIPT_NAME . '?ged=' . KT_GEDCOM)
	->pageHeader()
	// By default jQuery modifies AJAX URLs to disable caching, causing JS libraries to be loaded many times.
	->addInlineJavascript('jQuery.ajaxSetup({cache:true});');

echo '<div id="home-page">';
	if ($blocks['main']) {
		if ($blocks['side']) {
			echo '<div id="index_main_blocks">';
		} else {
			echo '<div id="index_full_blocks">';
		}
		foreach ($blocks['main'] as $block_id => $module_name) {
			$class_name = $module_name.'_KT_Module';
			$module = new $class_name;
			if ($SEARCH_SPIDER || !$module->loadAjax()) {
				// Load the block directly
				$module->getBlock($block_id);
			} else {
				// Load the block asynchronously
				echo '
					<div id="block_', $block_id, '">
						<div class="loading-image">&nbsp;</div>
					</div>
				';
				$controller->addInlineJavascript(
					'jQuery("#block_'.$block_id.'").load("index.php?ctype=' . $ctype . '&action=ajax&block_id=' . $block_id . '");'
				);
			}
		}
		echo '</div>';
	}
	if ($blocks['side']) {
		if ($blocks['main']) {
			echo '<div id="index_small_blocks">';
		} else {
			echo '<div id="index_full_blocks">';
		}
		foreach ($blocks['side'] as $block_id => $module_name) {
			$class_name = $module_name.'_KT_Module';
			$module = new $class_name;
			if ($SEARCH_SPIDER || !$module->loadAjax()) {
				// Load the block directly
				$module->getBlock($block_id);
			} else {
				// Load the block asynchronously
				echo '
					<div id="block_', $block_id, '">
						<div class="loading-image">&nbsp;</div>
					</div>
				';
				$controller->addInlineJavascript(
					'jQuery("#block_'.$block_id.'").load("index.php?ctype=' . $ctype . '&action=ajax&block_id=' . $block_id . '");'
				);
			}
		}
		echo '</div>';
	}

	// link for changing blocks
	if (KT_USER_ID || $SHOW_COUNTER) {
		echo '<div id="link_change_blocks">';
			if (KT_USER_GEDCOM_ADMIN) echo '<a href="index_edit.php?gedcom_id=' . KT_GED_ID . '" onclick="return modalDialog(\'index_edit.php?gedcom_id=' . KT_GED_ID . '\', \'' . KT_I18N::translate('Change the blocks on this page') . '\');">', KT_I18N::translate('Change the blocks on this page'), '</a>';
			if ($SHOW_COUNTER) {echo '<span>'.KT_I18N::translate('Hit Count:').' '.$hitCount.'</span>';}
		echo '</div>'; // <div id="link_change_blocks">
	} else {
		echo '<div class="clearfloat"></div>';
	}

echo '</div>'; // <div id="home-page">
