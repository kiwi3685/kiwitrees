<?php
// Header for colors theme
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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

global $subColor;

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(WT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(WT_JQUERY_AUTOSIZE)
	->addInlineJavascript('
		widget_bar();
		activate_colorbox();
		jQuery.extend(jQuery.colorbox.settings, {
			maxWidth		:"95%",
			maxHeight		:"95%",
			fixed			:false,
			slideshow		:true,
			slideshowAuto	:false,
			slideshowSpeed	:5000,
			slideshowStart	:"'.WT_I18N::translate('Play').'",
			slideshowStop	:"'.WT_I18N::translate('Stop').'",
			speed			:2000,
			title			:function(){
								var img_title = jQuery(this).data("title");
								return img_title;
							}
		});
		jQuery("body").on("click", "a.gallery", function(event) {
			// Add colorbox to pdf-files
			jQuery("a[type^=application].gallery").colorbox({
				rel			:"gallery",
				innerWidth	:"60%",
				innerHeight	:"90%",
				iframe		:true,
				photo		:false
			});
		});
		jQuery("textarea").autosize();
	');

global $ALL_CAPS;
if ($ALL_CAPS) $this->addInlineJavascript('all_caps();');
$ctype = safe_REQUEST($_REQUEST, 'ctype', array('gedcom', 'user'), WT_USER_ID ? 'user' : 'gedcom');

$show_widgetbar = false;
if (WT_USER_ID && WT_SCRIPT_NAME != 'index.php' && $view != 'simple') {
	$show_widgetbar = true;
}

echo '
	<!DOCTYPE html>
	<html ', WT_I18N::html_markup(), '>', '
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">',
		header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL), '
		<title>', htmlspecialchars($title), '</title>
		<link rel="icon" href="', WT_THEME_URL, 'images/favicon.png" type="image/png">
		<link rel="stylesheet" href="', WT_THEME_URL, 'jquery-ui-custom/jquery-ui.structure.min.css" type="text/css">
		<link rel="stylesheet" href="', WT_THEME_URL, 'jquery-ui-custom/jquery-ui.theme.min.css" type="text/css">
		<link rel="stylesheet" href="', WT_THEME_URL, 'css/colors.css" type="text/css">
		<link rel="stylesheet" href="', WT_THEME_URL,  'css/',  $subColor,  '.css" type="text/css">';

		if (stristr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.8, maximum-scale=2.0" />';
			echo '<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, 'ipad.css">';
		} elseif (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') || stristr($_SERVER['HTTP_USER_AGENT'], 'Trident')) {
			// This is needed for all versions of IE, so we cannot use conditional comments.
			echo '<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, 'msie.css">';
		}
		if (file_exists(WT_THEME_URL . 'mystyle.css')) {
			echo '<link rel="stylesheet" href="', WT_THEME_URL, 'mystyle.css" type="text/css">';
		}
echo
	'</head>',
	'<body id="body">';

if  ($view!='simple') { // Use "simple" headers for popup windows
	global $WT_IMAGES;
	echo
	// Top row left
	'<div id="header">',
		'<span class="title" dir="auto">', WT_TREE_TITLE, '</span>';

		// Top row right
		echo
		'<ul class="makeMenu">';
			if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
				echo '<li>
					<a href="#" onclick="window.open(\'edit_changes.php\',\'_blank\', chan_window_specs); return false;" style="color:red;">',
						WT_I18N::translate('Pending changes'), '
					</a>
				</li>';
			}
			foreach (WT_MenuBar::getOtherMenus() as $menu) {
				echo $menu->getMenuAsList();
			}
		echo
			'<li>',
				'<form style="display:inline;" action="search.php" method="post">',
				'<input type="hidden" name="action" value="general">',
				'<input type="hidden" name="topsearch" value="yes">',
				'<input type="search" name="query" size="15" placeholder="', WT_I18N::translate('Search'), '" dir="auto">',
				'<input class="search-icon" type="image" src="', $WT_IMAGES['search'], '" alt="', WT_I18N::translate('Search'), '" title="', WT_I18N::translate('Search'), '">',
				'</form>',
			'</li>',
		'</ul>',
	'</div>';

	// Print the main menu bar
	echo '<div id="topMenu">
		<ul id="main-menu">';
				if ($show_widgetbar) {
					echo '<li id="widget-button" class="fa-bars"><a href="#" ><span style="line-height: inherit;" class="fa fa-fw fa-2x fa-bars">&nbsp;</span></a></li>';
				}
				foreach (WT_MenuBar::getMainMenus() as $menu) {
					echo getMenuAsCustomList($menu);
				}
		echo '</ul>',
		// select menu for responsive layouts only
		'<select id="nav-select" onChange="window.location.href=this.value">
			<option selected="selected" value="">', WT_I18N::translate('Choose a page'), '</option>';
			foreach (WT_MenuBar::getMainMenus() as $menu) {
				echo $menu->getMenuAsSelect();
			}
	echo	'</select>
	</div>';

}
// Remove list from home when only 1 gedcom
$this->addInlineJavaScript(
	'if (jQuery("#menu-tree ul li").length == 2) jQuery("#menu-tree ul li:last-child").remove();'
);

echo
	$javascript,
	WT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions
	'<div id="content">';

// add widget bar inside content div for all pages except Home, and only for logged in users with role 'member' or above
if ($show_widgetbar) {
	include_once 'widget-bar.php';
}
