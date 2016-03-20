<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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

// Create tables, if not already present
try {
	WT_DB::updateSchema(WT_MODULES_DIR, 'widget_blog/db_schema/', 'NB_SCHEMA_VERSION', 3);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class widget_blog_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Journal');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Journal” module */ WT_I18N::translate('A private area to record notes or keep a journal.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $ctype, $controller;

		$url = $_SERVER['REQUEST_URI'];

		switch (safe_GET('action')) {
		case 'deletenews':
			$news_id = safe_GET('news_id');
			if ($news_id) {
				deleteNews($news_id);
			}
			break;
		}

		$usernews = getUserNews(WT_USER_ID);

		$id=$this->getName();
		$class=$this->getName();
		$title='';
		$title.=$this->getTitle();
		$content = '';

		if (count($usernews)==0) {
			$content .= WT_I18N::translate('You have not created any Journal items.');
		}

		foreach ($usernews as $key=>$news) {
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
					<a href="#" onclick="window.open(\'editnews.php?news_id=\'+' . $key . ', \'_blank\', news_window_specs); return false;">' . WT_I18N::translate('Edit') . '</a> |
					<a href="' . $url . '?action=deletenews&amp;news_id=' . $key . '\'" onclick="return confirm(\'' . WT_I18N::translate('Are you sure you want to delete this Journal entry?') . '\');">' . WT_I18N::translate('Delete') . '</a>
				</div>';
		}
		if (WT_USER_ID) {
			$content .= '
				<p>
					<a href="#" onclick="window.open(\'editnews.php?user_id=\'+' . WT_USER_ID . ', \'_blank\', news_window_specs); return false;">' . WT_I18N::translate('Add a Journal entry') . '</a>
				</p>';
		}

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

	// Implement WT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 90;
	}

	// Implement WT_Module_Menu
	public function defaultAccessLevel() {
		return false;
	}

	// Implement class WT_Module_Block
	public function configureBlock($widget_id) {
	}
}
