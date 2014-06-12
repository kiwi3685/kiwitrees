<?php
// Module help text.
//
// This file is included from the application help_text.php script.
// It simply needs to set $title and $text for the help topic $help_topic
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
//	Copyright (C) 2012 Nigel Osborne and kiwtrees.net. All rights reserved.

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'gallery_position':
	$title=WT_I18N::translate('Gallery position');
	$text=WT_I18N::translate('This field controls the order in which the galleries are displayed.').'<br><br>'.WT_I18N::translate('You do not have to enter the numbers sequentially. If you leave holes in the numbering scheme, you can insert other albums later. For example, if you use the numbers 1, 6, 11, 16, you can later insert albums with the missing sequence numbers. Negative numbers and zero are allowed, and can be used to insert albums in front of the first one.').'<br><br>'.WT_I18N::translate('When more than one gallery has the same position number, only one of these albums will be visible.');
	break;

case 'gallery_visibility':
	$title=WT_I18N::translate('Gallery visibility');
	$text=WT_I18N::translate('You can determine whether this album will be visible regardless of family tree, or whether it will be visible only to the current family tree.').
	'<br><ul><li><b>'.WT_I18N::translate('All').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The album will appear in all galleries, regardless of family tree.').'</li><li><b>'.get_gedcom_setting(WT_GED_ID, 'title').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The album will appear only in the currently active family trees\'s gallery.').'</li></ul>';
	break;

case 'gallery_language':
	$title=WT_I18N::translate('Gallery language');
	$text=WT_I18N::translate('Either leave all languages un-ticked to display the gallery texts in every language, or tick the specific languages you want to display it for.<br><br>To create translated texts for different languages create multiple copies setting the appropriate language only for each version.');
	break;

case 'gallery_title':
	$title=WT_I18N::translate('Summary page and Menu title');
	$text=WT_I18N::translate('This is a brief title. It is displayed in two places.<ol><li> It is used as the main menu item name if your theme uses names, and you have more than one gallery. If you only have one gallery, then the title of that gallery is used. It should be kept short or it might break the menu display.</li><li>It is used as the main title on the display page, above the tabbed list of galleries.</li></ol>');
	break;

case 'gallery_description':
	$title=WT_I18N::translate('Gallery description');
	$text=WT_I18N::translate('This is a sub-heading that will display below the Summary page title, above the tabbed list of galleries. It can contain HTML elements including an image if you wish. Simply ensure there is no content if you do not want to display it.');
	break;

case 'gallery_source':
	$title=WT_I18N::translate('Gallery source');
	$text=WT_I18N::translate('Here you can either select the kiwitrees media folder to display in this gallery page, or you can set a link to a Flickr or Picasa location for your group of images.'.
	'<br>'.
	'<em>[Such external sources must be public or they will not be viewable in kiwitrees.]</em>'.
	'<br><br>'.
	'For Flickr (www.flickr.com), enter the <strong>Set</strong> number of your images, usually a long number like <strong>72157633272831222</strong>. Nothing else is required in this field.'.
	'<br><br>'.
	'For Picassa (picasaweb.google.com) enter your user name and user album, in the format <strong>username/album</strong> like <strong>kiwi3685/NZImages</strong>'.
	'<br><br>'.
	'The module will add these references to the correct URLs to link to your Flickr or Picasa sites.');
	break;
}
