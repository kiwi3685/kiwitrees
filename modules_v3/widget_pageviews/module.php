<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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

class widget_pageviews_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Most viewed pages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Most visited pages” module */ WT_I18N::translate('A list of the pages that have been viewed the most number of times.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $SHOW_COUNTER;

		$num = (int)get_block_setting($widget_id, 'num', 10);
		$block = get_block_setting($widget_id, 'block', false);
		if ($cfg) {
			foreach (array('count_placement', 'num', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}

		$id = $this->getName();
		$class = $this->getName();
		if (WT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.WT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$widget_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title = '';
		}
		$title .= $this->getTitle();

		$content = "";
		// load the lines from the file
		$top10 = WT_DB::prepare(
			"SELECT page_parameter, page_count".
			" FROM `##hit_counter`".
			" WHERE gedcom_id=? AND page_name IN ('individual.php','family.php','source.php','repo.php','note.php','mediaviewer.php')".
			" ORDER BY page_count DESC LIMIT ".$num
		)->execute(array(WT_GED_ID))->FetchAssoc();

		$content .= '<ul>';

		foreach ($top10 as $id=>$count) {
			$record=WT_GedcomRecord::getInstance($id);
			if ($record && $record->canDisplayDetails()) {
				$content.='
					<li>
						<span class="inset"><a href="' . $record->getHtmlUrl().'">' . $record->getFullName() . '</a></span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">' . $count . '</span>
					</li>
				';
			}
		}
		$content .= '</ul>';

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement WT_Module_Widget
	public function defaultWidgetOrder() {
		return 120;
	}

	// Implement class WT_Module_Block
	public function configureBlock($widget_id) {
		if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'num',             WT_Filter::postInteger('num', 1, 10000, 10));
			set_block_setting($widget_id, 'block',           WT_Filter::postBool('block'));
			exit;
		}
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$num=get_block_setting($widget_id, 'num', 10);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Number of items to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="num" size="2" value="', $num, '">';
		echo '</td></tr>';

		$block=get_block_setting($widget_id, 'block', false);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ WT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}
