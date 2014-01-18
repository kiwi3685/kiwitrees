<?php
// Header for Simpl_designer theme
//
// Copyright (C) 2013 Nigel Osborne and kiwtrees.net. All rights reserved.
//
// kiwi-webtrees: Web based Family History software
// Copyright (C) 2014 kiwitrees.net
//
// Derived from PhpGedView and webtrees
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
// Copyright (C) 2010 to 2013  webtrees Development Team.  All rights reserved.
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
// @author Nigel Osborne http://kiwitrees.net

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(WT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(WT_JQUERY_AUTOSIZE)
	->addInlineJavascript('
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
echo
	'<!DOCTYPE html>',
	'<html ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<meta http-equiv="X-UA-Compatible" content="IE=edge">',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<title>', htmlspecialchars($title), '</title>',
	'<link rel="icon" href="', WT_THEME_URL, 'favicon.png" type="image/png">',
	'<link rel="stylesheet" type="text/css" href="', WT_THEME_URL, 'jquery-ui-1.10.3/jquery-ui-1.10.3.custom.css">',
	'<link rel="stylesheet" href="', WT_THEME_URL, 'style.css" type="text/css">',
	'<!--[if IE]>',
		'<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, 'msie.css">',
	'<![endif]-->';

// Additional css files required (Only if Lightbox installed)
if (WT_USE_LIGHTBOX) {
	echo '<link rel="stylesheet" type="text/css" href="', WT_STATIC_URL, WT_MODULES_DIR, 'lightbox/css/album_page.css" media="screen">';
}

echo '</head>';
if ($view!='simple') {echo '<body id="body">';
} else {echo '<body id="body_simple">';}

// begin header section
if ($view!='simple') {
	echo
		'<div id="fb-root"></div>',
		WT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions
		'<div id="topbar">
		<ul id="extra-menu" class="makeMenu">';
			$menu=WT_MenuBar::getFavoritesMenu();
			if ($menu) {
				echo $menu->getMenuAsList();
			}
			$menu=WT_MenuBar::getThemeMenu();
			if ($menu) {
				echo $menu->getMenuAsList();
			}
			$menu=WT_MenuBar::getLanguageMenu();
			if ($menu) {
				echo $menu->getMenuAsList();
			}
			if (WT_USER_ID) {
				echo '<li><a href="edituser.php">', WT_I18N::translate('Logged in as '), ' ', getUserFullName(WT_USER_ID), '</a></li> <li>', logout_link(), '</li>';
			} else {
				$class_name='login_block_WT_Module';
				$module=new $class_name;
				echo '<li><a href="#">', WT_I18N::translate('Login or Register'), '</a><ul id="login_popup"><li>', $module->getBlock('login_block'), '</li></ul></li>';
			}
	echo '</ul>
		</div>
		<div id="main_content">
		<div id="header">
		<div class="title" dir="auto">', WT_TREE_TITLE, '</div>
		</div>';
		
	// Print the menu bar
	$menu_items=array(
		WT_MenuBar::getGedcomMenu(),
		WT_MenuBar::getMyPageMenu(),
		WT_MenuBar::getChartsMenu(),
		WT_MenuBar::getListsMenu(),
		WT_MenuBar::getCalendarMenu(),
		WT_MenuBar::getReportsMenu(),
		WT_MenuBar::getSearchMenu(),
	);
	foreach (WT_MenuBar::getModuleMenus() as $menu) {
		$menu_items[]=$menu;
	}
	echo
		'<div id="topMenu" class="ui-state-active">
		<ul id="main-menu">';
	foreach ($menu_items as $menu) {
		if ($menu) {
			echo $menu->getMenuAsList();
		}
	}
	unset($menu_items, $menu);
	echo
		'</ul>',  // <ul id="main-menu">
		'<div class="header_search">',
		'<form action="search.php" method="post">',
		'<input type="hidden" name="action" value="general">',
		'<input type="hidden" name="topsearch" value="yes">',
		'<input type="search" name="query" size="25" placeholder="', WT_I18N::translate('Search'), '" dir="auto">',
		'</form>',
		'</div>',
		'</div>'; // <div id="topMenu">
}
// begin content section
echo $javascript, '<div id="content">';// closed in footer, as is div "main_content"
