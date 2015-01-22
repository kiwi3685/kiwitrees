<?php
// Popup window for Editing news items
//
// TODO: this needs to be part of the news module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2005  PGV Development Team
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

define('WT_SCRIPT_NAME', 'editnews.php');
require './includes/session.php';

$controller=new WT_Controller_Simple();
$controller
	->setPageTitle(WT_I18N::translate('Add/edit journal/news entry'))
	->requireMemberLogin()
	->pageHeader();

$action   =safe_GET('action', array('compose', 'save', 'delete'), 'compose');
$news_id  =safe_GET('news_id');
$user_id  =safe_REQUEST($_REQUEST, 'user_id');
$gedcom_id=safe_REQUEST($_REQUEST, 'gedcom_id');
$date     =safe_POST('date', WT_REGEX_INTEGER, WT_TIMESTAMP);
$title    =safe_POST('title', WT_REGEX_UNSAFE);
$text     =safe_POST('text', WT_REGEX_UNSAFE);

switch ($action) {
case 'compose':
	if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
		ckeditor_WT_Module::enableEditor($controller);
	}
	echo '<h3>'.WT_I18N::translate('Add/edit journal/news entry').'</h3>';
	echo '<form style="overflow: hidden;" name="messageform" method="post" action="editnews.php?action=save&news_id='.$news_id.'">';
	if ($news_id) {
		$news = getNewsItem($news_id);
	} else {
		$news = array();
		$news['user_id'] = $user_id;
		$news['gedcom_id'] = $gedcom_id;
		$news['date'] = WT_TIMESTAMP;
		$news['title'] = '';
		$news['text'] = '';
	}
	echo '<input type="hidden" name="user_id" value="'.$news['user_id'].'">';
	echo '<input type="hidden" name="gedcom_id" value="'.$news['gedcom_id'].'">';
	echo '<input type="hidden" name="date" value="'.$news['date'].'">';
	echo '<table>';
	echo '<tr><th style="text-align:left;font-weight:900;" dir="auto;">'.WT_I18N::translate('Title:').'</th><tr>';
	echo '<tr><td><input type="text" name="title" size="50" dir="auto" autofocus value="'.$news['title'].'"></td></tr>';
	echo '<tr><th valign="top" style="text-align:left;font-weight:900;" dir="auto;">'.WT_I18N::translate('Entry Text:').'</th></tr>';
	echo '<tr><td>';
	echo '<textarea name="text" class="html-edit" cols="80" rows="10" dir="auto">'.WT_Filter::escapeHtml($news['text']).'</textarea>';
	echo '</td></tr>';
	echo '<tr><td><input type="submit" value="'.WT_I18N::translate('save').'"></td></tr>';
	echo '</table>';
	echo '</form>';
	break;
case 'save':
	$message=array();
	if ($news_id) {
		$message['id']=$news_id;
	}
	$message['user_id'] = $user_id;
	$message['gedcom_id'] = $gedcom_id;
	$message['date'] = $date;
	$message['title'] = $title;
	$message['text']  = $text;
	addNews($message);
	$controller->addInlineJavascript('window.opener.location.reload();window.close();');
	break;
case 'delete':
	deleteNews($news_id);
	$controller->addInlineJavascript('window.opener.location.reload();window.close();');
	break;
}
