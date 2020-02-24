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

class widget_html_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('HTML');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “HTML” module */ KT_I18N::translate('Add your own text and graphics.');
	}

	// Implement class KT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $ctype, $GEDCOM;

		// Only show this block for certain languages
		$languages=get_block_setting($widget_id, 'languages');
		if ($languages && !in_array(KT_LOCALE, explode(',', $languages))) {
			return;
		}

		/*
		* Select GEDCOM
		*/
		$gedcom=get_block_setting($widget_id, 'gedcom');
		switch($gedcom) {
		case '__current__':
			break;
		case '':
			break;
		case '__default__':
			$GEDCOM = KT_Site::preference('DEFAULT_GEDCOM');
			if (!$GEDCOM) {
				foreach (KT_Tree::getAll() as $tree) {
					$GEDCOM=$tree->tree_name;
					break;
				}
			}
			break;
		default:
			$GEDCOM = $gedcom;
			break;
		}

		/*
		* Retrieve text, process embedded variables
		*/
		$title_tmp = get_block_setting($widget_id, 'title');
		if (!$title_tmp) {
			$title_tmp = KT_I18N::translate('Default HTML block title');
		}

		$html = get_block_setting($widget_id, 'html');

		if ( (strpos($title_tmp, '#') !== false) || (strpos($html, '#') !== false) ) {
			$stats		= new KT_Stats($GEDCOM);
			$title_tmp	= $stats->embedTags($title_tmp);
			$html		= $stats->embedTags($html);
		}

		/*
		* Restore Current GEDCOM
		*/
		$GEDCOM = KT_GEDCOM;

		/*
		* Start Of Output
		*/
		$id		= $this->getName().$widget_id;
		$class	= $this->getName();
		if (KT_USER_GEDCOM_ADMIN) {
			$title = '<i class="icon-admin" title="' . KT_I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $widget_id . '\', \'' . $this->getTitle() . '\');"></i>';
		} else {
			$title = '';
		}
		$title .= $title_tmp;

		$content = $html;

		if (get_block_setting($widget_id, 'show_timestamp', false)) {
			$content .= '<br>' . format_timestamp(get_block_setting($widget_id, 'timestamp', KT_TIMESTAMP));
		}

		if ($template) {
			require KT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 50;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Widget
	public function configureBlock($widget_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'gedcom',         KT_Filter::post('gedcom'));
			set_block_setting($widget_id, 'title',          KT_Filter::post('title'));
			set_block_setting($widget_id, 'html',           KT_Filter::post('html'));
			set_block_setting($widget_id, 'show_timestamp', KT_Filter::postBool('show_timestamp'));
			set_block_setting($widget_id, 'timestamp',      KT_Filter::post('timestamp'));
			$languages = array();
			foreach (KT_I18N::used_languages() as $code=>$name) {
				if (safe_POST_bool('lang_'.$code)) {
					$languages[] = $code;
				}
			}
			set_block_setting($widget_id, 'languages', implode(',', $languages));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$title	= get_block_setting($widget_id, 'title');
		$html	= get_block_setting($widget_id, 'html');
		// title
		echo '
			<tr>
				<td class="descriptionbox wrap">' . KT_Gedcom_Tag::getLabel('TITL') . '</td>
				<td class="optionbox"><input type="text" name="title" size="30" value="' . htmlspecialchars($title) . '"></td>
			</tr>
		';

		// gedcom
		$gedcom = get_block_setting($widget_id, 'gedcom');
		if (count(KT_Tree::getAll()) > 1) {
			if ($gedcom == '__current__') {$sel_current = ' selected="selected"';} else {$sel_current = '';}
			if ($gedcom == '__default__') {$sel_default = ' selected="selected"';} else {$sel_default = '';}
			echo '
				<tr>
					<td class="descriptionbox wrap">'.  KT_I18N::translate('Family tree') . '</td>
					<td class="optionbox">
						<select name="gedcom">
							<option value="__current__"' . $sel_current . '>' . KT_I18N::translate('Current') . '</option>
							<option value="__default__"' . $sel_default . '>' . KT_I18N::translate('Default') . '</option>';
							foreach (KT_Tree::getAll() as $tree) {
								if ($tree->tree_name == $gedcom) {$sel = ' selected="selected"';} else {$sel = '';}
								echo '<option value="' . $tree->tree_name . '"' . $sel . ' dir="auto">' . $tree->tree_title_html . '</option>';
							}
						echo '</select>
					</td>
				</tr>
			';
		}

		// html
		$show_timestamp	= get_block_setting($widget_id, 'show_timestamp', false);
		$languages		= get_block_setting($widget_id, 'languages');
		echo '
			<tr>
				<td colspan="2" class="descriptionbox">' . KT_I18N::translate('Content') . help_link('block_html_content' . $this->getName()) . '</td>
			</tr>
			<tr>
				<td colspan="2" class="optionbox">
					<textarea name="html" class="html-edit" rows="10" style="width:98%;">' . htmlspecialchars($html) . '</textarea>
				</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap">' . KT_I18N::translate('Show the date and time of update') . '</td>
				<td class="optionbox">' .
					edit_field_yes_no('show_timestamp', $show_timestamp) . '
					<input type="hidden" name="timestamp" value="' . KT_TIMESTAMP . '">
				</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap">' . KT_I18N::translate('Show this block for which languages?') . '</td>
				<td class="optionbox">' .
					edit_language_checkboxes('lang_', $languages) . '
				</td>
			</tr>
		';
	}
}
