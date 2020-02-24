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

class widget_givnnames_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Top=Most common */ KT_I18N::translate('Top given names');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Top given names” module */ KT_I18N::translate('A list of the most popular given names.');
	}

	// Implement class KT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $TEXT_DIRECTION, $controller;

		$num = get_block_setting($widget_id, 'num', 10);
		$infoStyle = get_block_setting($widget_id, 'infoStyle', 'table');
		if ($cfg) {
			foreach (array('num', 'infoStyle') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$stats = new KT_Stats(KT_GEDCOM);

		$id=$this->getName();
		$class=$this->getName();
		if (KT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$widget_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}
		if ($num==1) {
			// I18N: i.e. most popular given name.
			$title.=KT_I18N::translate('Top given name');
		} else {
			// I18N: Title for a list of the most common given names, %s is a number.  Note that a separate translation exists when %s is 1
			$title.=KT_I18N::plural('Top %s given name', 'Top %s given names', $num, KT_I18N::number($num));
		}

		$content = '<div class="normal_inner_block">';
		//Select List or Table
		switch ($infoStyle) {
		case "list": // Output style 1:  Simple list style.
			if ($TEXT_DIRECTION=='ltr') $padding = 'padding-left: 15px';
			else $padding = 'padding-right: 15px';
			$params=array(1,$num,'rcount');
			//List Female names
			$totals=$stats->commonGivenFemaleTotals($params);
			if ($totals) {
				$content.='<b>'.KT_I18N::translate('Females').'</b><div class="wrap" style="'.$padding.'">'.$totals.'</div><br>';
			}
			//List Male names
			$totals=$stats->commonGivenMaleTotals($params);
			if ($totals) {
				$content.='<b>'.KT_I18N::translate('Males').'</b><div class="wrap" style="'.$padding.'">'.$totals.'</div><br>';
			}
			break;
		case "table": // Style 2: Tabular format.  Narrow, 2 or 3 column table.
			$params=array(1,$num,'rcount');
			$content.='<table style="margin:auto;">
						<tr valign="top">
						<td>'.$stats->commonGivenFemaleTable($params).'</td>
						<td>'.$stats->commonGivenMaleTable($params).'</td>';
			$content.='</tr></table>';
			break;
		}
		$content .=  "</div>";

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
		return 170;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Block
	public function configureBlock($widget_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'num',       KT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($widget_id, 'infoStyle', KT_Filter::post('infoStyle', 'list|table', 'table'));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$num=get_block_setting($widget_id, 'num', 10);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Number of items to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="num" size="2" value="', $num, '">';
		echo '</td></tr>';

		$infoStyle=get_block_setting($widget_id, 'infoStyle', 'table');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Presentation style');
		echo '</td><td class="optionbox">';
		echo select_edit_control('infoStyle', array('list'=>KT_I18N::translate('list'), 'table'=>KT_I18N::translate('table')), null, $infoStyle, '');
		echo '</td></tr>';
	}
}
