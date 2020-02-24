<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class charts_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/block */ KT_I18N::translate('Charts');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Charts” module */ KT_I18N::translate('An alternative way to display charts.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $PEDIGREE_FULL_DETAILS, $show_full, $bwidth, $bheight;

		$PEDIGREE_ROOT_ID=get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');

		$details=get_block_setting($block_id, 'details', false);
		$type   =get_block_setting($block_id, 'type', 'pedigree');
		$pid    =get_block_setting($block_id, 'pid', KT_USER_ID ? (KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);
		$block  =get_block_setting($block_id, 'block');
		if ($cfg) {
			foreach (array('details', 'type', 'pid', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		// Override the request
		$_GET['rootid']=$pid;

		// Override GEDCOM configuration temporarily
		if (isset($show_full)) $saveShowFull = $show_full;
		$savePedigreeFullDetails = $PEDIGREE_FULL_DETAILS;
		if (!$details) {
			$show_full = 0;
			// Here we could adjust the block width & height to accommodate larger displays
		} else {
			$show_full = 1;
			// Here we could adjust the block width & height to accommodate larger displays
		}
		$PEDIGREE_FULL_DETAILS = $show_full;

		$person = KT_Person::getInstance($pid);
		if (!$person) {
			$pid = $PEDIGREE_ROOT_ID;
			set_block_setting($block_id, 'pid', $pid);
			$person = KT_Person::getInstance($pid);
		}

		if ($type!='treenav' && $person) {
			$controller = new KT_Controller_Hourglass($person->getXref(),0,3);
			$controller->setupJavascript();
		}

		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		if (KT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$block_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}

		if ($person) {
			switch($type) {
				case 'pedigree':
					$title .= KT_I18N::translate('Pedigree of %s', $person->getFullName());
					break;
				case 'descendants':
					$title .= KT_I18N::translate('Descendants of %s', $person->getFullName());
					break;
				case 'hourglass':
					$title .= KT_I18N::translate('Hourglass chart of %s', $person->getFullName());
					break;
				case 'treenav':
					$title .= KT_I18N::translate('Interactive tree of %s', $person->getFullName());
					break;
			}
			$title .= help_link('index_charts', $this->getName());
			$content = '<table cellspacing="0" cellpadding="0" border="0"><tr>';
			if ($type=='descendants' || $type=='hourglass') {
				$content .= '<td valign="middle">';
				ob_start();
				$controller->print_descendency($person, 1, false);
				$content .= ob_get_clean();
				$content .= '</td>';
			}
			if ($type=='pedigree' || $type=='hourglass') {
				//-- print out the root person
				if ($type != 'hourglass') {
					$content .= '<td valign="middle">';
					ob_start();
					print_pedigree_person($person);
					$content .= ob_get_clean();
					$content .= '</td>';
				}
				$content .= '<td valign="middle">';
				ob_start();
				$controller->print_person_pedigree($person, 1);
				$content .= ob_get_clean();
				$content .= '<td>';
			}
			if ($type == 'treenav') {
				require_once KT_MODULES_DIR . 'tree/module.php';
				require_once KT_MODULES_DIR . 'tree/class_treeview.php';
				$mod		= new tree_KT_Module;
				$tv			= new TreeView;
				$content	.= '<td>';

				$content .= '<script>jQuery("head").append(\'<link rel="stylesheet" href="'.$mod->css().'" type="text/css" />\');</script>';
				$content .= '<script src="' . $mod->js() . '"></script>';
		    	list($html, $js) = $tv->drawViewport($person, 2);
				$content .= $html.'<script>'.$js.'</script>';
				$content .= '</td>';
			}
			$content .= '</tr></table>';
		} else {
			$content = KT_I18N::translate('You must select an individual and chart type in the block configuration settings.');
		}

		if ($template) {
			if ($block) {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}

		// Restore GEDCOM configuration
		unset($show_full);
		if (isset($saveShowFull)) $show_full = $saveShowFull;
		$PEDIGREE_FULL_DETAILS = $savePedigreeFullDetails;
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return true;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		global $ctype, $controller;

		$PEDIGREE_ROOT_ID = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');

		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'details', KT_Filter::postBool('details'));
			set_block_setting($block_id, 'type',    KT_Filter::post('type', 'pedigree|descendants|hourglass|treenav', 'pedigree'));
			set_block_setting($block_id, 'pid',     KT_Filter::post('pid', KT_REGEX_XREF));
			exit;
		}

		$details	= get_block_setting($block_id, 'details', false);
		$type		= get_block_setting($block_id, 'type',    'pedigree');
		$pid		= get_block_setting($block_id, 'pid', KT_USER_ID ? (KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);

		$controller
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();');
		?>
		<tr>
			<td class="descriptionbox wrap width33">
				<?php echo KT_I18N::translate('Chart type'); ?>
			</td>
			<td class="optionbox">
				<select name="type">
					<option value="pedigree"<?php if ($type=="pedigree") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Pedigree'); ?></option>
					<option value="descendants"<?php if ($type=="descendants") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Descendants'); ?></option>
					<option value="hourglass"<?php if ($type=="hourglass") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Hourglass chart'); ?></option>
					<option value="treenav"<?php if ($type=="treenav") echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('Interactive tree'); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="descriptionbox wrap width33"><?php echo KT_I18N::translate('Show Details'); ?></td>
		<td class="optionbox">
			<select name="details">
					<option value="no" <?php if (!$details) echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('no'); ?></option>
					<option value="yes" <?php if ($details) echo " selected=\"selected\""; ?>><?php echo KT_I18N::translate('yes'); ?></option>
			</select>
			</td>
		</tr>
		<tr>
			<td class="descriptionbox wrap width33"><?php echo KT_I18N::translate('Individual'); ?></td>
			<td class="optionbox">
			<input data-autocomplete-type="INDI" type="text" name="pid" id="pid" value="<?php echo $pid; ?>" size="5">
				<?php
				echo print_findindi_link('pid');
				$root = KT_Person::getInstance($pid);
				if ($root) {
					echo '<span class="list_item">', $root->getFullName(), $root->format_first_major_fact(KT_EVENTS_BIRT, 1), '</span>';
				}
				?>
			</td>
		</tr>
		<?php
		require_once KT_ROOT.'includes/functions/functions_edit.php';
		$block = get_block_setting($block_id, 'block', false);
		?>
		<tr>
			<td class="descriptionbox wrap width33">
				<?php echo /* I18N: label for a yes/no option */ KT_I18N::translate('Add a scrollbar when block contents grow'); ?>
			</td>
			<td class="optionbox">
				<?php echo edit_field_yes_no('block', $block); ?>
			</td>
		</tr>

		<?php
	}
}
