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

class html_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('HTML');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “HTML” module */ KT_I18N::translate('Add your own text and graphics.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $ctype, $GEDCOM;

		// Only show this block for certain languages
		$languages = get_block_setting($block_id, 'languages');
		if ($languages && !in_array(KT_LOCALE, explode(',', $languages))) {
			return;
		}

		/*
		* Select GEDCOM
		*/
		$gedcom = get_block_setting($block_id, 'gedcom');
		switch ($gedcom) {
		case '__current__':
			break;
		case '':
			break;
		case '__default__':
			$GEDCOM=KT_Site::preference('DEFAULT_GEDCOM');
			if (!$GEDCOM) {
				foreach (KT_Tree::getAll() as $tree) {
					$GEDCOM = $tree->tree_name;
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
		$title_tmp	= get_block_setting($block_id, 'title');
		$html		= get_block_setting($block_id, 'html');

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
		$id		= $this->getName() . $block_id;
		$class	= $this->getName() . '_block';
		if (KT_USER_GEDCOM_ADMIN) {
			$title = '<i class="icon-admin" title="' . KT_I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>';
		} else {
			$title = '';
		}
		$title .= $title_tmp;

		$content = $html;

		if (get_block_setting($block_id, 'show_timestamp', false)) {
			$content .= '<p class="timestamp">' . format_timestamp(get_block_setting($block_id, 'timestamp', KT_TIMESTAMP)) . '</p>';
		}

		if ($template) {
			if (get_block_setting($block_id, 'block', false)) {
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
		return false;
	}

	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'gedcom',         KT_Filter::post('gedcom'));
			set_block_setting($block_id, 'title',          KT_Filter::post('title'));
			set_block_setting($block_id, 'html',           KT_Filter::post('html'));
			set_block_setting($block_id, 'show_timestamp', KT_Filter::postBool('show_timestamp'));
			set_block_setting($block_id, 'timestamp',      KT_Filter::post('timestamp'));
			$languages = array();
			foreach (KT_I18N::used_languages('name') as $code=>$name) {
				if (safe_POST_bool('lang_'.$code)) {
					$languages[]=$code;
				}
			}
			set_block_setting($block_id, 'languages', implode(',', $languages));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$templates=array(
			KT_I18N::translate('Keyword examples')=>
			'#getAllTagsTable#',

			KT_I18N::translate('Narrative description')=>
			/* I18N: do not translate the #keywords# */ KT_I18N::translate('This GEDCOM (family tree) was last updated on #gedcomUpdated#. There are #totalSurnames# surnames in this family tree. The earliest recorded event is the #firstEventType# of #firstEventName# in #firstEventYear#. The most recent event is the #lastEventType# of #lastEventName# in #lastEventYear#.<br /><br />If you have any comments or feedback please contact #contactWebmaster#.'),

			KT_I18N::translate('Statistics')=>
			'<div class="gedcom_stats">
				<span style="font-weight: bold"><a href="index.php?command=gedcom">#gedcomTitle#</a></span><br>
				' . KT_I18N::translate('This family tree was last updated on %s.', '#gedcomUpdated#') . '
				<table id="keywords">
					<tr>
						<td valign="top" class="width20">
							<table cellspacing="1" cellpadding="0">
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Individuals').'</td>
									<td class="facts_value" align="right"><a href="indilist.php?surname_sublist=no">#totalIndividuals#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Males').'</td>
									<td class="facts_value" align="right">#totalSexMales#<br>#totalSexMalesPercentage#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Females').'</td>
									<td class="facts_value" align="right">#totalSexFemales#<br>#totalSexFemalesPercentage#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Total surnames').'</td>
									<td class="facts_value" align="right"><a href="indilist.php?show_all=yes&amp;surname_sublist=yes&amp;ged='.KT_GEDURL.'">#totalSurnames#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'. KT_I18N::translate('Families').'</td>
									<td class="facts_value" align="right"><a href="famlist.php?ged='.KT_GEDURL.'">#totalFamilies#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Sources').'</td>
									<td class="facts_value" align="right"><a href="sourcelist.php?ged='.KT_GEDURL.'">#totalSources#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Media objects').'</td>
									<td class="facts_value" align="right"><a href="medialist.php?ged='.KT_GEDURL.'">#totalMedia#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Repositories').'</td>
									<td class="facts_value" align="right"><a href="repolist.php?ged='.KT_GEDURL.'">#totalRepositories#</a></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Total events').'</td>
									<td class="facts_value" align="right">#totalEvents#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Total users').'</td>
									<td class="facts_value" align="right">#totalUsers#</td>
								</tr>
							</table>
						</td>
						<td><br></td>
						<td valign="top">
							<table cellspacing="1" cellpadding="0" border="0">
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Earliest birth year').'</td>
									<td class="facts_value" align="right">#firstBirthYear#</td>
									<td class="facts_value">#firstBirth#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Latest birth year').'</td>
									<td class="facts_value" align="right">#lastBirthYear#</td>
									<td class="facts_value">#lastBirth#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Earliest death year').'</td>
									<td class="facts_value" align="right">#firstDeathYear#</td>
									<td class="facts_value">#firstDeath#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Latest death year').'</td>
									<td class="facts_value" align="right">#lastDeathYear#</td>
									<td class="facts_value">#lastDeath#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Person who lived the longest').'</td>
									<td class="facts_value" align="right">#longestLifeAge#</td>
									<td class="facts_value">#longestLife#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Average age at death').'</td>
									<td class="facts_value" align="right">#averageLifespan#</td>
									<td class="facts_value"></td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Family with the most children').'</td>
									<td class="facts_value" align="right">#largestFamilySize#</td>
									<td class="facts_value">#largestFamily#</td>
								</tr>
								<tr>
									<td class="facts_label">'.KT_I18N::translate('Average number of children per family').'</td>
									<td class="facts_value" align="right">#averageChildren#</td>
									<td class="facts_value"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br>
				<span style="font-weight: bold">'.KT_I18N::translate('Most Common Surnames').'</span><br>
				#commonSurnames#
			</div>'
		);

		$title = get_block_setting($block_id, 'title');
		$html = get_block_setting($block_id, 'html');
		// title
		echo '<tr><td class="descriptionbox wrap">',
			KT_Gedcom_Tag::getLabel('TITL'),
			'</td><td class="optionbox"><input type="text" name="title" size="30" value="', htmlspecialchars($title), '"></td></tr>';

		// templates
		echo '<tr><td class="descriptionbox wrap">',
			KT_I18N::translate('Templates'),
			help_link('block_html_template', $this->getName()),
			'</td><td class="optionbox">';
		// The CK editor needs lots of help to load/save data :-(
		if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
			$ckeditor_onchange='CKEDITOR.instances.html.setData(document.block.html.value);';
		} else {
			$ckeditor_onchange='';
		}
		echo '<select name="template" onchange="document.block.html.value=document.block.template.options[document.block.template.selectedIndex].value;', $ckeditor_onchange, '">';
		echo '<option value="', htmlspecialchars($html), '">', KT_I18N::translate('Custom'), '</option>';
		foreach ($templates as $title=>$template) {
			echo '<option value="', htmlspecialchars($template), '">', $title, '</option>';
		}
		echo '</select></td></tr>';

		// gedcom
		$gedcom=get_block_setting($block_id, 'gedcom');
		if (count(KT_Tree::getAll()) > 1) {
			if ($gedcom == '__current__') {$sel_current = ' selected="selected"';} else {$sel_current = '';}
			if ($gedcom == '__default__') {$sel_default = ' selected="selected"';} else {$sel_default = '';}
			echo '<tr><td class="descriptionbox wrap">',
				KT_I18N::translate('Family tree'),
				'</td><td class="optionbox">',
				'<select name="gedcom">',
				'<option value="__current__"', $sel_current, '>', KT_I18N::translate('Current'), '</option>',
				'<option value="__default__"', $sel_default, '>', KT_I18N::translate('Default'), '</option>';
			foreach (KT_Tree::getAll() as $tree) {
				if ($tree->tree_name == $gedcom) {$sel = ' selected="selected"';} else {$sel = '';}
				echo '<option value="', $tree->tree_name, '"', $sel, ' dir="auto">', $tree->tree_title_html, '</option>';
			}
			echo '</select></td></tr>';
		}

		// html
		echo '<tr><td colspan="2" class="descriptionbox">',
			KT_I18N::translate('Content'),
			help_link('block_html_content', $this->getName()),
			'</td></tr><tr>',
			'<td colspan="2" class="optionbox">';
		echo '<textarea name="html" class="html-edit" rows="10" style="width:98%;">', htmlspecialchars($html), '</textarea>';
		echo '</td></tr>';

		$show_timestamp = get_block_setting($block_id, 'show_timestamp', false);
		echo '<tr><td class="descriptionbox wrap">';
		echo KT_I18N::translate('Show the date and time of update');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_timestamp', $show_timestamp);
		echo '<input type="hidden" name="timestamp" value="', KT_TIMESTAMP, '">';
		echo '</td></tr>';

		$languages = get_block_setting($block_id, 'languages');
		echo '<tr><td class="descriptionbox wrap">';
		echo KT_I18N::translate('Show this block for which languages?');
		echo '</td><td class="optionbox">';
		echo edit_language_checkboxes('lang_', $languages);
		echo '</td></tr>';
	}
}
