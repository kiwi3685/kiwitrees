<?php
// Header for Simpl_grey theme
//
// webtrees: Web based Family History software
// Copyright (C) 2010 webtrees development team.
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// Most of the icons used are from "Silk" icons by Mark James, at http://www.famfamfam.com/lab/icons/silk/
// licensed under the Creative Commons Attribution 2.5 License
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
			slideshow		:true,
			slideshowAuto	:false,
			slideshowSpeed	:5000,
			slideshowStart	:"'.WT_I18N::translate('Play').'",
			slideshowStop	:"'.WT_I18N::translate('Stop').'",
			speed			:2000,
			current			:"{current} '.WT_I18N::translate('of').' {total}",
			title			:function(){
								var img_title = jQuery(this).data("title");
								return img_title;
							},
			scalePhotos		:function(){
								if($(this).data("obje-type") === "photo") return true;
								else return false;							
							}
		});
		jQuery("body").on("click", "a.gallery", function(event) {		
			// Add colorbox to pdf-files
			jQuery("a[type^=application].gallery").colorbox({
				rel			:"gallery",
				innerWidth	:"75%",
				innerHeight	:"90%",
				iframe		:true,
				scalephoto	:false,
				title		:function(){
								var url = jQuery(this).attr("href");
								var img_title = jQuery(this).data("title");
								return "<a href=\"" + url + "\" target=\"_blank\">" + img_title + " - '.
								WT_I18N::translate('Open in full browser window').'</a>";
							}
			});
		});
		jQuery("textarea").autosize();
	');
// This theme adds extra autocomplete fields
$this->addInlineJavaScript('
	jQuery("#SPFX, input[name*=SPFX]").autocomplete({source: "'.WT_THEME_URL.'files/simpl_autocomplete.php?field=SPFX"});
	jQuery("#NPFX, input[name*=NPFX]").autocomplete({source: "'.WT_THEME_URL.'files/simpl_autocomplete.php?field=NPFX"});
	jQuery("#NSFX, input[name*=NSFX]").autocomplete({source: "'.WT_THEME_URL.'files/simpl_autocomplete.php?field=NSFX"});
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
if ($view!='simple') {
	echo '<body id="body">';
} else {
	echo '<body id="body_simple">';
}

if ($view!='simple') {
	//Prepare menu arrays
	$home_menu=array(
		$menu = WT_MenuBar::getGedcomMenu(),
		$menu = WT_MenuBar::getMyPageMenu()
	);
	$view_menu=array(
		$menu = WT_MenuBar::getChartsMenu(),
		$menu = WT_MenuBar::getListsMenu(),
		$menu = WT_MenuBar::getReportsMenu(),
		$menu = WT_MenuBar::getCalendarMenu()
	);
	$tools_menu=array(
		$menu=WT_MenuBar::getLanguageMenu(),
		$menu = WT_MenuBar::getThemeMenu(),
		$menu = WT_MenuBar::getFavoritesMenu()
	);
	$module_menu = WT_MenuBar::getModuleMenus();
	$search_menu = WT_MenuBar::getSearchMenu();

	// begin header section	
	echo '<div id="header">
		<div id="title">';
			echo '<span class="viewing">', WT_I18N::translate('Viewing:&nbsp;'), '</span>';
				if (WT_USER_GEDCOM_ADMIN) {
					echo '<a href="admin.php">', WT_TREE_TITLE, '</a><br>';
				} else {
					echo WT_TREE_TITLE, '<br>';
				}
			if (WT_USER_ID) {
				echo '<span class="user">', WT_I18N::translate('Member:&nbsp;'), '<a href="edituser.php">', getUserFullName(WT_USER_ID), '</a></span>';
				if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
					echo ' <span><a href="#" onclick="window.open(\'edit_changes.php\',\'_blank\', chan_window_specs); return false;" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></span>';
				}
			}
		echo '</div>
		<div class="pro_linedrop">
			<ul class="select" dir="auto">
				<li>
					<a class="home" href="index.php?ctype=gedcom"><span><b><img alt="', WT_I18N::translate('Home'), '" src="', WT_THEME_DIR, 'images/simpl_favicon.png"></b></span></a>
				</li>';		
				// Home 
				echo '<li>';
					if (WT_USER_ID) {
							echo '<a href="index.php?ctype=user">';
						} else {
							echo '<a href="index.php?ctype=gedcom">';
						}
					echo '<span><b>', WT_I18N::translate('Home'), '</b></span></a>
					<ul class="sub">';
						foreach ($home_menu as $n=>$menu) {
							if ($menu) {
								$menu->title = '';							
								echo $menu;
							}
						}
					echo '</ul>
				</li>';
				// View menu (lists, charts, reports, calendar)
				echo '<li><a href="#"><span><b>', WT_I18N::translate('View'), '</b></span></a>
					<ul class="sub">';
						foreach ($view_menu as $n=>$menu) {
							if ($menu) {
								echo $menu;
							}
						}
					echo '</ul>
				</li>';
				// Tools
				echo '<li><a href="#"><span><b>', WT_I18N::translate('Tools'), '</b></span></a>
					<ul class="sub">';
						foreach ($tools_menu as $n=>$menu) {
							if ($menu) {
								echo $menu;
							}
						}
					echo '</ul>
				</li>';
				// Other menu items
				if(!empty($module_menu)) {
					echo '<li><a href="#"><span><b>', WT_I18N::translate('Other'), '</b></span></a>
						<ul class="sub">';
							foreach ($module_menu as $n=>$menu) {
								$other_menu = $menu;
								if (
									!strpos($other_menu, '>'.WT_I18N::translate('Edit').'<')
									&& !strpos($other_menu, '>'.WT_I18N::translate('FAQ').'<')
								) {echo $other_menu;}									
							}
						echo '</ul>
					</li>';
				}
				// Edit menu
				if(!empty($module_menu)) {
					foreach ($module_menu as $n=>$menu) {
						$edit_menu = $menu;
						if (strpos($edit_menu, '>'.WT_I18N::translate('Edit').'<')) {
							$edit_menu = str_replace("<li class=\"node\">", "",$edit_menu);
							$edit_menu = str_replace(">".WT_I18N::translate('Edit')."<", "><span><b>".WT_I18N::translate('Edit')."</b></span><",$edit_menu);
							$edit_menu = str_replace("<ul>", "<ul class=\"sub\">",$edit_menu);
							echo $edit_menu;
						}									
					}
				}
				// Search Box
				echo '<li class="lrt2">';
					echo
					'<form action="search.php" method="post">',
					'<input type="hidden" name="action" value="general">',
					'<input type="hidden" name="topsearch" value="yes">',
					'<div class="formbut"><i class="icon-magnifier"></i>',
					'<input type="text" name="query" size="16" placeholder="', WT_I18N::translate('Search'), '" dir="auto">',
					'</div></form>';
				echo '</li>';
				// Login/Out
				if (WT_USER_ID) {
					echo '<li class="lrt"><span>', logout_link(), '</span></li>';
				} else {
					echo '<li class="lrt"><span>', login_link(), '</span></li>';
				}
				// FAQ menu
				if(!empty($module_menu)) {
					foreach ($module_menu as $n=>$menu) {
						$faq_menu = $menu->getMenuAsList();
						if (strpos($faq_menu, '>'.WT_I18N::translate('FAQ').'<')) {
							echo '<li class="lrt"><a href="module.php?mod=faq&amp;mod_action=show"><span><b>FAQs</b></span></a></li>';
						}									
					}
				}
				// Search menu
				if(!empty($search_menu)) {
					if (strpos($search_menu, WT_I18N::translate('Search'))) {
						$search_menu = str_replace("<li id=\"menu-search\">", "<li class=\"lrt\">",$search_menu);
						$search_menu = str_replace(">".WT_I18N::translate('Search')."<", "><span><b>".WT_I18N::translate('Search')."</b></span><",$search_menu);
						$search_menu = str_replace("<ul>", "<ul class=\"sub rt\">",$search_menu);
						echo $search_menu;
					}
				}					
			echo '</ul>
		</div>', //close pro_linedrop
	'</div>'; //close header
}
// begin content section
echo $javascript,
	WT_FlashMessages::getHtmlMessages(), // Feedback from asynchronous actions
	'<div id="content">';