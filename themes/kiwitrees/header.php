<?php
// Header for Simpl_designer theme
//
// Copyright (C) 2013 Nigel Osborne and kiwtrees.net. All rights reserved.
//
// kiwitrees: Web based Family History software
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
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//
// @author Nigel Osborne http://kiwitrees.net

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

define('WT_JQUERY_BIGTEXT',  WT_THEME_URL.'js/jquery-bigtext.js');

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript (WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript (WT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript (WT_JQUERY_AUTOSIZE)
	->addExternalJavascript (WT_JQUERY_BIGTEXT)
	->addInlineJavascript ('
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

		jQuery("#bigtext span").bigText({
			fontSizeFactor: 1,
		    maximumFontSize: 48,
		    limitingDimension: "both",
		    verticalAlign: "top"
		});

	');

global $ALL_CAPS;
if ($ALL_CAPS) $this->addInlineJavascript('all_caps();');
$ctype = safe_REQUEST($_REQUEST, 'ctype', array('gedcom', 'user'), WT_USER_ID ? 'user' : 'gedcom');

echo
	'<!DOCTYPE html>',
	'<html ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<meta http-equiv="X-UA-Compatible" content="IE=edge">',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<title>', htmlspecialchars($title), '</title>',
	'<link rel="icon" href="', WT_THEME_URL, 'images/favicon.png" type="image/png">',
	'<link rel="stylesheet" type="text/css" href="', WT_THEMES_DIR, '_administration/jquery-ui-1.10.3/jquery-ui-1.10.3.custom.css">',
	'<link rel="stylesheet" href="', WT_THEME_URL, 'style.css" type="text/css">',
	'<!--[if IE]>',
		'<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, 'msie.css">',
	'<![endif]-->

	</head>';
	
if ($view!='simple') {echo '<body id="body">';
} else {echo '<body id="body_simple">';}

// begin header section
if ($view!='simple') {
	echo '
		<div id="main_content">
			<div id="navbar">
				<div id="header">
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
							$menu = WT_MenuBar::getMyAccountMenu();
							if ($menu) {
								echo $menu->getMenuAsList();
							}
							if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
								echo '<li>
									<a href="#" onclick="window.open(\'edit_changes.php\',\'_blank\', chan_window_specs); return false;" style="color:red;">',
										WT_I18N::translate('Pending changes'), '
									</a>
								</li>';
							}
						} else {
							$class_name = 'login_block_WT_Module';
							$module = new $class_name;
							echo '<li>
								<a href="#">'.
									(WT_Site::preference('USE_REGISTRATION_MODULE') ? WT_I18N::translate('Login or Register') : WT_I18N::translate('Login')) , '
								</a>
								<ul id="login_popup">
									<li>',
										$module->getBlock('login_block'), '
									</li>
								</ul>
							</li>';
						}
		echo 		'</ul>
					<div id="bigtext" class="title" dir="auto">',
						WT_TREE_TITLE, '
					</div>
					<div class="header_search">
						<form action="search.php" method="post">
							<input type="hidden" name="action" value="general">
							<input type="hidden" name="topsearch" value="yes">
							<input type="search" name="query" size="25" placeholder="', WT_I18N::translate('Search'), '" dir="auto">
						</form>
					</div>
				</div>', // <div id="header">
				'<div id="topMenu" class="ui-state-active">
					<ul id="main-menu">';
						if ($ctype != 'gedcom') {
							echo '<li id="widget-button" class="fa fa-fw fa-2x icon-widget"><a href="#" ><span style="line-height: inherit;">&nbsp;</span></a></li>';
						}
						foreach (WT_MenuBar::getModuleMenus() as $menu) {
							if ($menu) {
								echo $menu->getMenuAsList();
							}
						}
		echo
					'</ul>
				</div>', // <div id="topMenu">
				WT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions
			'</div>'; // <div id="navbar">
}
// begin content section
echo $javascript, '<div id="content">';// closed in footer, as is div "main_content"

// add widget bar inside content div for all pages except Home, and only for logged in users with role 'member' or above
if ($ctype != 'gedcom' && $view != 'simple') {
	include_once 'widget-bar.php';
}
