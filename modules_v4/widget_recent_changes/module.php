<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

class widget_recent_changes_KT_Module extends KT_Module implements KT_Module_Widget {
	const DEFAULT_DAYS = 7;
	const MAX_DAYS = 90;

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Recent changes');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Recent changes” module */ KT_I18N::translate('A list of records that have been updated recently.');
	}

	// Implement class KT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {

		require_once KT_ROOT.'includes/functions/functions_print_lists.php';

		$days		= get_block_setting($widget_id, 'days', self::DEFAULT_DAYS);
		$infoStyle	= get_block_setting($widget_id, 'infoStyle', 'table');
		$sortStyle	= get_block_setting($widget_id, 'sortStyle', 'date_desc');
		$hide_empty	= get_block_setting($widget_id, 'hide_empty', false);
		if ($cfg) {
			foreach (array('days', 'infoStyle', 'show_parents', 'sortStyle', 'hide_empty') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$found_facts = get_recent_changes(KT_CLIENT_JD - $days);

		if (!$found_facts && $hide_empty) {
			return '';
		}

		$id = $this->getName();
		$class = $this->getName();

		if (KT_USER_GEDCOM_ADMIN) {
			$title = '<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$widget_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title = '';
		}
		$title .= /* I18N: title for list of recent changes */ KT_I18N::plural('Changes in the last day', 'Changes in the last %s days', $days, KT_I18N::number($days));

		$content = '';
		// Print block content
		if (count($found_facts) == 0) {
      		$content .= KT_I18N::translate('There have been no changes within the last %s days.', KT_I18N::number($days));
		} else {
			ob_start();
			switch ($infoStyle) {
				case 'list':
					$content .= print_changes_list($found_facts, $sortStyle);
					break;
				case 'table':
					// sortable table
					$content .= print_changes_table($found_facts, $sortStyle);
					break;
			}
			$content .= ob_get_clean();
		}

		if ($template) {
			require KT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}

	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 70;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Block
	public function configureBlock($widget_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'days',       KT_Filter::postInteger('days', 1, self::MAX_DAYS, self::DEFAULT_DAYS));
			set_block_setting($widget_id, 'infoStyle',  KT_Filter::post('infoStyle', 'list|table', 'table'));
			set_block_setting($widget_id, 'sortStyle',  KT_Filter::post('sortStyle', 'name|date_asc|date_desc', 'date_desc'));
			set_block_setting($widget_id, 'hide_empty', KT_Filter::postBool('hide_empty'));
			exit;
		}

		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$days = get_block_setting($widget_id, 'days', self::DEFAULT_DAYS);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Number of days to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="days" size="2" value="', $days, '">';
		echo ' <em>', KT_I18N::plural('maximum %d day', 'maximum %d days', self::MAX_DAYS, self::MAX_DAYS), '</em>';
		echo '</td></tr>';

		$infoStyle = get_block_setting($widget_id, 'infoStyle', 'table');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Presentation style');
		echo '</td><td class="optionbox">';
		echo select_edit_control('infoStyle', array('list' => KT_I18N::translate('list'), 'table' => KT_I18N::translate('table')), null, $infoStyle, '');
		echo '</td></tr>';

		$sortStyle = get_block_setting($widget_id, 'sortStyle', 'date');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Sort order');
		echo '</td><td class="optionbox">';
		echo select_edit_control('sortStyle', array(
			'name'      => /* I18N: An option in a list-box */ KT_I18N::translate('sort by name'),
			'date_asc'  => /* I18N: An option in a list-box */ KT_I18N::translate('sort by date, oldest first'),
			'date_desc' => /* I18N: An option in a list-box */ KT_I18N::translate('sort by date, newest first')
		), null, $sortStyle, '');
		echo '</td></tr>';

		$hide_empty = get_block_setting($widget_id, 'hide_empty', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Should this block be hidden when it is empty?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('hide_empty', $hide_empty);
		echo '</td></tr>';
		echo '<tr><td colspan="2" class="optionbox wrap">';
		echo '<span class="error">', KT_I18N::translate('If you hide an empty block, you will not be able to change its configuration until it becomes visible by no longer being empty.'), '</span>';
		echo '</td></tr>';
	}

}
