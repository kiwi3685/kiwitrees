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

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript(KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript(KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript(KT_JQUERY_AUTOSIZE)
	->addInlineJavascript('
		widget_bar();
		activate_colorbox();
		jQuery.extend(jQuery.colorbox.settings, {
			slideshowStart	:"' . KT_I18N::translate('Play') . '",
			slideshowStop	:"' . KT_I18N::translate('Stop') . '",
		});
		// Add colorbox to pdf-files
		jQuery("body").on("click", "a.gallery", function(event) {
			jQuery("a[type^=application].gallery").colorbox({
				title: function(){
							var url = jQuery(this).attr("href");
							var img_title = jQuery(this).data("title");
							return "<a href=\"" + url + "\" target=\"_blank\">" + img_title + " - '.
							KT_I18N::translate('Open in full browser window') . '</a>";
						}
			});
		});
		jQuery("textarea").autosize();
	');

global $ALL_CAPS;
if ($ALL_CAPS) $this->addInlineJavascript('all_caps();');
$ctype = safe_REQUEST($_REQUEST, 'ctype', array('gedcom', 'user'), KT_USER_ID ? 'user' : 'gedcom');


$show_widgetbar = false;
if (KT_USER_ID && KT_SCRIPT_NAME != 'index.php' && $view != 'simple' && KT_Module::getActiveWidgets()) {
	$show_widgetbar = true;
}

echo '
	<!DOCTYPE html>
	<html ' . KT_I18N::html_markup() . '>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
		header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL) . '
		<title>' . htmlspecialchars((string) $title) . '</title>
		<link rel="icon" href="' . KT_THEME_URL . 'images/favicon.png" type="image/png">
		<link rel="stylesheet" href="' . KT_THEME_URL . 'jquery-ui-custom/jquery-ui.structure.min.css" type="text/css">
		<link rel="stylesheet" href="' . KT_THEME_URL . 'jquery-ui-custom/jquery-ui.theme.min.css" type="text/css">
		<link rel="stylesheet" href="' . KT_THEME_URL . 'style.css" type="text/css">
		<!--[if IE]>
			<link type="text/css" rel="stylesheet" href="' . KT_THEME_URL . 'msie.css">
			<![endif]-->';
		if (file_exists(KT_THEME_URL . 'mystyle.css')) {
            echo '<link rel="stylesheet" href="' . KT_THEME_URL . 'mystyle.css' . time() . '" type="text/css">';
		}
	echo '</head>
	<body id="body">';

if ($view != 'simple') { // Use "simple" headers for popup windows
	echo '<div id="navbar">
	<div id="header">' .
		KT_TREE_TITLE . KT_TREE_SUBTITLE . '
		<div class="hsearch">
			<form action="search.php" method="post">
				<input type="hidden" name="action" value="general">
				<input type="hidden" name="topsearch" value="yes">
				<input type="search" name="query" size="25" placeholder="' . KT_I18N::translate('Search') . '" dir="auto">
				<input type="submit" name="search" value="&gt;">
			</form>
		</div>'.
	'</div>' . // <div id="header">
	'<div id="optionsmenu">'.
		'<ul class="makeMenu">';
			if (KT_USER_CAN_ACCEPT && exists_pending_change()) {
echo			'<li>
					<a href="edit_changes.php" target="_blank" rel="noopener noreferrer" style="color:red;">'.
						KT_I18N::translate('Pending changes') . '
					</a>
				</li>';
			}
			foreach (KT_MenuBar::getOtherMenus() as $menu) {
				echo $menu->getMenuAsList();
			}
echo	'</ul>'.
	'</div>';

	// Print the menu bar
	echo
		'<div id="topMenu">'.
			'<ul id="main-menu">';
				if ($show_widgetbar) {
					echo '<li id="widget-button" class="fa fa-fw fa-2x fa-bars"><a href="#" ><span style="line-height: inherit;">' . KT_I18N::translate('Widgets') . '</span></a></li>';
				}
				foreach (KT_MenuBar::getMainMenus() as $menu) {
					echo $menu->getMenuAsList();
				}
echo
		'</ul>' . // <ul id="main-menu">
		// select menu for responsive layouts only
		'<select id="nav-select" onChange="window.location.href=this.value">
			<option selected="selected" value="">' . KT_I18N::translate('Choose a page') . '</option>';
			foreach (KT_MenuBar::getMainMenus() as $menu) {
				echo $menu->getResponsiveMenu();
			}
echo	'</select>
		</div>' . // <div id="topMenu">
		KT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions
}
echo '</div>' . $javascript . '<div id="content">';

// add widget bar inside content div for all pages except Home, and only for logged in users with role 'member' or above
if ($show_widgetbar) {
	include_once 'widget-bar.php';
}
