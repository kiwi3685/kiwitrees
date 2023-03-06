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

// Create tables, if not already present
try {
	KT_DB::updateSchema(KT_ROOT . KT_MODULES_DIR . 'widget_blog/db_schema/', 'NB_SCHEMA_VERSION', 3);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class widget_blog_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Journal');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Journal” module */ KT_I18N::translate('A private area to record notes or keep a journal.');
	}

	private function deleteNews($news_id) {
		return KT_DB::prepare("DELETE FROM `##news` WHERE news_id=?")->execute(array($news_id));
	}

	// Implement class KT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $ctype, $controller;

		$url = $_SERVER['REQUEST_URI'];
		$usernews = getUserNews(KT_USER_ID);
		$id=$this->getName();
		$class=$this->getName();
		$title='';
		$title.=$this->getTitle();
		$content = '';

		if (count($usernews)==0) {
			$content .= KT_I18N::translate('You have not created any Journal items.');
		}

		foreach ($usernews as $news) {
			$day	= date('j', $news['date']);
			$mon	= date('M', $news['date']);
			$year	= date('Y', $news['date']);

			$content .= '
				<div class="journal_box">
					<div class="news_title">' . $news['title'] . '</div>
					<div class="news_date">' . format_timestamp($news['date']) . '</div>';
					if ($news["text"]==strip_tags($news["text"])) {
						// No HTML?
						$news["text"]=nl2br($news["text"], false);
					}
			$content .= $news["text"] . '<br><br>
					<a href="#" onclick="window.open(\'editnews.php?news_id=\'+' . $news['id'] . ', \'_blank\', news_window_specs); return false;">' . KT_I18N::translate('Edit') . '</a>
					<a href="#" onclick="if (confirm(\'' . KT_I18N::translate('Are you sure you want to delete this Journal entry?') . '\')) {
						jQuery.post(\'action.php\',{action:\'deleteNews\',newsId:\''. $news['id'] .'\'},function(){location.reload();})
					}">' . KT_I18N::translate('Delete') . '</a>
				</div>';
		}
		if (KT_USER_ID) {
			$content .= '
				<p>
					<a href="#" onclick="window.open(\'editnews.php?user_id=\'+' . KT_USER_ID . ', \'_blank\', news_window_specs); return false;">' . KT_I18N::translate('Add a Journal entry') . '</a>
				</p>';
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

	// Implement KT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 90;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Block
	public function configureBlock($widget_id) {
	}
}
