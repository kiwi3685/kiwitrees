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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class top10_pageviews_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Most viewed pages');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Most visited pages” module */ KT_I18N::translate('A list of the pages that have been viewed the most number of times.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $SHOW_COUNTER;

		$count_placement	= get_block_setting($block_id, 'count_placement', 'before');
		$num				= (int)get_block_setting($block_id, 'num', 10);
		$block				= get_block_setting($block_id, 'block', false);
		if ($cfg) {
			foreach (array('count_placement', 'num', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$id		= $this->getName() . $block_id;
		$class	= $this->getName() . '_block';
		if (KT_USER_GEDCOM_ADMIN) {
			$title = '
				<i class="icon-admin" title="' . KT_I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>';
		} else {
			$title = '';
		}
		$title .= $this->getTitle();

		$content = '';

		// load the lines from the file
		$top10 = KT_DB::prepare("
			SELECT page_parameter, page_count
			 FROM `##hit_counter`
			 WHERE gedcom_id=? AND page_name IN ('individual.php','family.php','source.php','repo.php','note.php','mediaviewer.php')
			 ORDER BY page_count DESC LIMIT " . $num
		)->execute(array(KT_GED_ID))->FetchAssoc();


		if ($block) {
			$content .= '<table width="90%">';
		} else {
			$content .= '<table>';
		}
		foreach ($top10 as $id => $count) {
			$record = KT_GedcomRecord::getInstance($id);
			if ($record && $record->canDisplayDetails()) {
				$content .= '<tr valign="top">';
				if ($count_placement == 'before') {
					$content .= '<td dir="ltr" align="right">[' . $count . ']</td>';
				}
				$content .= '<td class="name2" ><a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a></td>';
				if ($count_placement == 'after') {
					$content .= '<td dir="ltr" align="right">[' . $count . ']</td>';
				}
				$content .= '</tr>';
			}
		}
		$content .= '</table>';

		if ($template) {
			if ($block) {
				require KT_THEME_DIR . 'templates/block_small_temp.php';
			} else {
				require KT_THEME_DIR . 'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}
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
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'num',             KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($block_id, 'count_placement', KT_Filter::post('count_placement', 'before|after', 'before'));
			set_block_setting($block_id, 'block',           KT_Filter::postBool('block'));
			exit;
		}
		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$num				= get_block_setting($block_id, 'num', 10);
		$count_placement	= get_block_setting($block_id, 'count_placement', 'before');
		$block				= get_block_setting($block_id, 'block', false);
		?>

		<tr>
			<td class="descriptionbox wrap width33">
				<?php echo KT_I18N::translate('Number of items to show'); ?>
			</td>
			<td class="optionbox">
				<input type="text" name="num" size="2" value="'<?php echo $num; ?>">
			</td>
		</tr>

		<tr>
			<td class="descriptionbox wrap width33">
				<?php echo KT_I18N::translate('Place counts before or after name?'); ?>
			</td>
			<td class="optionbox">
				<?php echo select_edit_control('count_placement', array('before' => KT_I18N::translate('before'), 'after' => KT_I18N::translate('after')), null, $count_placement, ''); ?>
			</td>
		</tr>

		<tr>
			<td class="descriptionbox wrap width33">
				<?php echo /* I18N: label for a yes/no option */ KT_I18N::translate('Add a scrollbar when block contents grow'); ?>
			</td>
			<td class="optionbox">
				<?php echo edit_field_yes_no('block', $block); ?>
			</td>
		</tr>

	<?php }
}
