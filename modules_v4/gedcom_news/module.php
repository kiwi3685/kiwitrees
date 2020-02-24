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

// Create tables, if not already present
try {
	KT_DB::updateSchema(KT_ROOT . KT_MODULES_DIR . 'gedcom_news/db_schema/', 'NB_SCHEMA_VERSION', 3);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class gedcom_news_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('News');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “GEDCOM News” module */ KT_I18N::translate('Family news and site announcements.');
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template = true, $cfg = null) {
		global $ctype;

		switch (KT_Filter::get('action')) {
		case 'deletenews':
			$news_id = KT_Filter::get('news_id');
			if ($news_id) {
				KT_DB::prepare("DELETE FROM `##news` WHERE news_id=?")->execute(array($news_id));
			}
			break;
		}
		$block = get_block_setting($block_id, 'block', true);

		if (isset($_REQUEST['gedcom_news_archive'])) {
			$limit	= 'nolimit';
			$flag	= 0;
		} else {
			$flag	= get_block_setting($block_id, 'flag', 0);
			if ($flag == 0) {
				$limit	= 'nolimit';
			} else {
				$limit = get_block_setting($block_id, 'limit', 'nolimit');
			}
		}
		if ($cfg) {
			foreach (array('limit', 'flag') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name = $cfg[$name];
				}
			}
		}
		$usernews	= getGedcomNews(KT_GED_ID);
		$id			= $this->getName().$block_id;
		$class		= $this->getName().'_block';
		if (KT_USER_GEDCOM_ADMIN) {
			$title = '<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$block_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title = '';
		}
		$title	.= $this->getTitle();
		$content = '';
		if (count($usernews)==0) {
			$content .= KT_I18N::translate('No News articles have been submitted.').'<br>';
		}
		$c = 0;
		foreach ($usernews as $news) {
			if ($limit == 'count') {
				if ($c >= $flag) {
					break;
				}
				$c ++;
			}
			if ($limit == 'date') {
				if ((int)((KT_TIMESTAMP - $news['date']) / 86400) > $flag) {
					break;
				}
			}
			$content .= "<div class=\"news_box\" id=\"article{$news['id']}\">";
			$content .= "<div class=\"news_title\">".htmlspecialchars($news['title']).'</div>';
			$content .= "<div class=\"news_date\">".format_timestamp($news['date']).'</div>';
			if ($news["text"] == strip_tags($news["text"])) {
				// No HTML?
				//$news["text"]=nl2br($news["text"], false);
				$news["text"] = nl2br($news["text"]);
			}
			$content .= $news["text"];
			// Print Admin options for this News item
			if (KT_USER_GEDCOM_ADMIN) {
				$content .= '<hr>'
				. "<a href=\"#\" onclick=\"window.open('editnews.php?news_id='+".$news['id'].", '_blank', news_window_specs); return false;\">" . KT_I18N::translate('Edit')."</a> | "
				. "<a href=\"index.php?action=deletenews&amp;news_id=".$news['id']."&amp;ctype={$ctype}\" onclick=\"return confirm('" . KT_I18N::translate('Are you sure you want to delete this News entry?')."');\">" . KT_I18N::translate('Delete')."</a><br>";
			}
			$content .= "</div>";
		}
		$printedAddLink = false;
		if (KT_USER_GEDCOM_ADMIN) {
			$content .= "<a href=\"#\" onclick=\"window.open('editnews.php?gedcom_id='+KT_GED_ID, '_blank', news_window_specs); return false;\">".KT_I18N::translate('Add a News article')."</a>";
			$printedAddLink = true;
		}
		if ($limit =='date' || $limit == 'count') {
			if ($printedAddLink) $content .= "&nbsp;&nbsp;|&nbsp;&nbsp;";
			$content .= "<a href=\"index.php?gedcom_news_archive=yes&amp;ctype={$ctype}\">" . KT_I18N::translate('View archive')."</a>";
			$content .= help_link('gedcom_news_archive').'<br>';
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
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($block_id, 'limit', KT_Filter::post('limit'));
			set_block_setting($block_id, 'flag',  KT_Filter::post('flag'));
			set_block_setting($block_id, 'block', KT_Filter::postBool('block'));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		// Limit Type
		$limit	= get_block_setting($block_id, 'limit', 'nolimit');
		$flag	= get_block_setting($block_id, 'flag', 0);
		$block	= get_block_setting($block_id, 'block', false);
		echo '
			<tr>
				<td class="descriptionbox wrap width33">' . KT_I18N::translate('Limit display by:'), help_link('gedcom_news_limit') . '</td>
				<td class="optionbox">
					<select name="limit">
						<option value="nolimit"' . ($limit == 'nolimit' ? ' selected="selected"' : '') . '">' .  KT_I18N::translate('No limit') . '</option>
						<option value="date"' . ($limit == 'date' ? ' selected="selected"' : '') .'">' . KT_I18N::translate('Age of item') . '</option>
						<option value="count"' . ($limit == 'count' ? ' selected="selected"':'') . '">' . KT_I18N::translate('Number of items') . '</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap width33">' . KT_I18N::translate('Limit:'), help_link('gedcom_news_flag') . '</td>
				<td class="optionbox">
					<input type="text" name="flag" size="4" maxlength="4" value="' . $flag . '">
				</td>
			</tr>
			<tr>
				<td class="descriptionbox wrap width33">' .
					/* I18N: label for a yes/no option */ KT_I18N::translate('Add a scrollbar when block contents grow') . '
				</td>
				<td class="optionbox">' .
					edit_field_yes_no('block', $block) . '
				</td>
			</tr>
		';
	}
}
