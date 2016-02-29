<?php
// Header for webtrees administration theme
//
// kiwitrees: Web based Family History software
// Copyright (C) 2014 kiwitrees.net
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
// $Id$

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
			title:	function(){
				var img_title = jQuery(this).data("title");
				return img_title;
			}
		});
		jQuery("textarea").autosize();
		jQuery("#adminAccordion").accordion({
			active:0,
			event: "click hoverintent",
			heightStyle: "content",
			collapsible: true,
			icons: false,
			create: function(event, ui) {
				//get index in cookie on accordion create event
				if(sessionStorage.getItem("saved_index") != null){
					act =  parseInt(sessionStorage.getItem("saved_index"));
				}
			},
			activate: function(event, ui) {
				//set cookie for current index on change event
				var active = jQuery("#adminAccordion").accordion("option", "active");
				sessionStorage.setItem("saved_index", null);
				sessionStorage.setItem("saved_index", active);
			},
			active:parseInt(sessionStorage.getItem("saved_index"))
		});
		jQuery("#adminAccordion").css("visibility", "visible");
	');

echo
	'<!DOCTYPE html>
	<html ', WT_I18N::html_markup(), '>
	<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="noindex,nofollow">',	'<title>', htmlspecialchars($title), '</title>
	<link rel="icon" href="', WT_THEME_URL, 'images/kt.png" type="image/png">
	<link rel="stylesheet" href="', WT_THEME_URL, 'jquery-ui-custom/jquery-ui.structure.min.css" type="text/css">
	<link rel="stylesheet" href="', WT_THEME_URL, 'jquery-ui-custom/jquery-ui.theme.min.css" type="text/css">
	<link rel="stylesheet" href="', WT_THEME_URL, 'style.css" type="text/css">
	<!--[if IE]>',
			'<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, 'msie.css">
	<![endif]-->';

echo
	$javascript,
	'</head>';
	if ($view!='simple') {echo '<body id="body">';
	} else {echo '<body id="body_simple">';}

// Header
	if ($view!='simple') {
echo
	'<div id="admin_head" class="ui-widget-content">
		<i class="icon-kiwitrees"></i>
		<div id="title"><a href="admin.php">', WT_I18N::translate('Administration'), '</a></div>
		<div id="links">',
			WT_MenuBar::getGedcomMenu(), '</a> | ';
			if (WT_USER_GEDCOM_ID) {
				echo '<a href="individual.php?pid=', WT_USER_GEDCOM_ID, '&amp;ged=', WT_GEDURL, '">', WT_I18N::translate('My individual record'), '</a> | ';
			}
			echo logout_link(),
			'<span>';
			$language_menu = WT_MenuBar::getLanguageMenu();
				if ($language_menu) {
					echo ' | ', $language_menu->getMenuAsList();
				}
			echo '</span>';
			if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
			echo ' | <li><a href="edit_changes.php" target="_blank" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></li>';
			}
			echo ' | <div class="header_search">
				<form action="search.php" method="post">
					<input type="hidden" name="action" value="general">
					<input type="hidden" name="topsearch" value="yes">
					<input type="search" name="query" size="25" placeholder="', WT_I18N::translate('Search trees'), '" dir="auto">
				</form>
			</div>
		</div>
		<div id="info">',
			WT_WEBTREES, ' ', WT_VERSION_TEXT,
			'<br>',
			/* I18N: The local time on the server */
			WT_I18N::translate('Server time'), ' —  ', format_timestamp(WT_SERVER_TIMESTAMP),
			'<br>',
			/* I18N: The local time on the client/browser */
			WT_I18N::translate('Client time'), ' — ', format_timestamp(WT_CLIENT_TIMESTAMP),
			'<br>',
			/* I18N: Timezone - http://en.wikipedia.org/wiki/UTC */
			WT_I18N::translate('UTC'), ' — ', format_timestamp(WT_TIMESTAMP),
		'</div>',
	'</div>'; // close admin_head

// Side menu
echo '
	<div id="admin_menu" class="ui-widget-content">
		<div id="adminAccordion" style="visibility:hidden">
			<h3 id="administration"><i class="fa fa-dashboard fa-fw"></i><span class="menu-name"><span class="menu-name">', WT_I18N::translate('Dashboard'), '</span></span></h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin.php" ? 'class="current" ' : ''), 'href="admin.php">', WT_I18N::translate('Home'), '</a></p>
			</div>';

if (WT_USER_IS_ADMIN) {
echo '		<h3 id="administration"><i class="fa fa-cog fa-fw"></i><span class="menu-name">', WT_I18N::translate('Site Administration'), '</h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_config.php"  ? 'class="current" ' : ''), 'href="admin_site_config.php">',  WT_I18N::translate('Site configuration'    ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_logs.php"    ? 'class="current" ' : ''), 'href="admin_site_logs.php">',    WT_I18N::translate('Logs'                  ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_readme.php"  ? 'class="current" ' : ''), 'href="admin_site_readme.php">',  WT_I18N::translate('README documentation'  ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_info.php"    ? 'class="current" ' : ''), 'href="admin_site_info.php">',    WT_I18N::translate('Server information'       ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_access.php"  ? 'class="current" ' : ''), 'href="admin_site_access.php">',  WT_I18N::translate('Site access rules'     ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_clean.php"   ? 'class="current" ' : ''), 'href="admin_site_clean.php">',   WT_I18N::translate('Clean up data folder'  ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_lang.php"    ? 'class="current" ' : ''), 'href="admin_site_lang.php">',    WT_I18N::translate('Custom translation'    ), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_use.php"     ? 'class="current" ' : ''), 'href="admin_site_use.php">',     WT_I18N::translate('Server usage'		    ), '</a></p>
			</div>';
}

echo '		<h3 id="trees"><i class="fa fa-tree fa-fw"></i><span class="menu-name">', WT_I18N::translate('Family trees'), '</span></h3>
			<div>';
if (WT_USER_IS_ADMIN) {
echo '			<p><a ', (WT_SCRIPT_NAME=="admin_trees_manage.php" ? 'class="current" ' : ''), 'href="admin_trees_manage.php">', WT_I18N::translate('Manage family trees'), '</a></p>';
}
				//-- gedcom list
				foreach (WT_Tree::getAll() as $tree) {
					if (userGedcomAdmin(WT_USER_ID, $tree->tree_id)) {
						// Add a title="" element, since long tree titles are cropped
echo '					<p><span><a ', (WT_SCRIPT_NAME=="admin_trees_config.php" && WT_GED_ID==$tree->tree_id ? 'class="current" ' : ''), 'href="admin_trees_config.php?ged='.$tree->tree_name_url.'" title="', htmlspecialchars($tree->tree_title), '" dir="auto">', $tree->tree_title_html, '</a></span></p>';
					}
				}
echo '		</div>

			<h3 id="tree-tools"><i class="fa fa-wrench fa-fw"></i><span class="menu-name">', WT_I18N::translate('Family tree tools'), '</span></h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_check.php"		? 'class="current" ' : ''), 'href="admin_trees_check.php">',		WT_I18N::translate('Check for GEDCOM errors'	), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_change.php"    	? 'class="current" ' : ''), 'href="admin_site_change.php">',		WT_I18N::translate('Changes log'				), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_other.php"    	? 'class="current" ' : ''), 'href="admin_site_other.php">',			WT_I18N::translate('Add unlinked records'		), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_places.php"   	? 'class="current" ' : ''), 'href="admin_trees_places.php">',		WT_I18N::translate('Update place names'			), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_site_merge.php"  		? 'class="current" ' : ''), 'href="admin_site_merge.php">',			WT_I18N::translate('Merge records'				), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_renumber.php" 	? 'class="current" ' : ''), 'href="admin_trees_renumber.php">',		WT_I18N::translate('Renumber family tree'		), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_append.php"    	? 'class="current" ' : ''), 'href="admin_trees_append.php">',		WT_I18N::translate('Append family tree'			), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_duplicates.php"  ? 'class="current" ' : ''), 'href="admin_trees_duplicates.php">',	WT_I18N::translate('Find duplicate individuals'	), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_unlinked.php"	? 'class="current" ' : ''), 'href="admin_trees_unlinked.php">',		WT_I18N::translate('Find unlinked individuals'	), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_trees_sanity.php"		? 'class="current" ' : ''), 'href="admin_trees_sanity.php">',		WT_I18N::translate('Sanity check'				), '</a></p>
				<p><a href="index_edit.php?gedcom_id=-1" onclick="return modalDialog(\'index_edit.php?gedcom_id=-1'.'\', \'',  WT_I18N::translate('Set the default blocks for new family trees'), '\');">', WT_I18N::translate('Set the default blocks'), '</a></p>
			</div>';

if (WT_USER_IS_ADMIN) {
echo '
			<h3 id="user-admin"><i class="fa fa-users fa-fw"></i><span class="menu-name">', WT_I18N::translate('Users'), '</span></h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin_users.php" && safe_GET('action')!="cleanup"&& safe_GET('action')!="createform" ? 'class="current" ' : ''), 'href="admin_users.php">', WT_I18N::translate('Manage users'), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_users.php" && safe_GET('action')=="createform" ? 'class="current" ' : ''), 'href="admin_users.php?action=createform">', WT_I18N::translate('Add a new user'), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_users_bulk.php" ? 'class="current" ' : ''), 'href="admin_users_bulk.php">', WT_I18N::translate('Send broadcast messages'), '</a>
				<p><a ', (WT_SCRIPT_NAME=="admin_users.php" && safe_GET('action')=="cleanup" ? 'class="current" ' : ''), 'href="admin_users.php?action=cleanup">', WT_I18N::translate('Delete inactive users'), '</a></p>
			</div>

			<h3 id="media"><i class="fa fa-image fa-fw"></i><span class="menu-name">', WT_I18N::translate('Media'), '</span></h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin_media.php" ? 'class="current" ' : ''), 'href="admin_media.php">', WT_I18N::translate('Manage media'), '</a></p>
				<p><a ', (WT_SCRIPT_NAME=="admin_media_upload.php" ? 'class="current" ' : ''), 'href="admin_media_upload.php">', WT_I18N::translate('Upload media files'), '</a></p>
			</div>

			<h3 id="modules"><i class="fa fa-puzzle-piece fa-fw"></i><span class="menu-name">', WT_I18N::translate('Modules'), '</span></h3>
			<div>
				<p><a ', (WT_SCRIPT_NAME=="admin_modules.php" ? 'class="current" ' : ''), 'href="admin_modules.php">', WT_I18N::translate('Manage modules'), '</a></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_menus.php"     ? 'class="current" ' : ''), 'href="admin_module_menus.php">',     WT_I18N::translate('Menus' 	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_tabs.php"      ? 'class="current" ' : ''), 'href="admin_module_tabs.php">',      WT_I18N::translate('Tabs'	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_blocks.php"    ? 'class="current" ' : ''), 'href="admin_module_blocks.php">',    WT_I18N::translate('Blocks'	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_widgets.php"   ? 'class="current" ' : ''), 'href="admin_module_widgets.php">',   WT_I18N::translate('Widgets'	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_sidebar.php"   ? 'class="current" ' : ''), 'href="admin_module_sidebar.php">',   WT_I18N::translate('Sidebar'	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_reports.php"   ? 'class="current" ' : ''), 'href="admin_module_reports.php">',   WT_I18N::translate('Reports'	),  '</a></span></p>
				<p><span><a ', (WT_SCRIPT_NAME=="admin_module_resources.php" ? 'class="current" ' : ''), 'href="admin_module_resources.php">', WT_I18N::translate('Resources'), '</a></span></p>
			</div>

			<h3 id="extras"><i class="fa fa-cogs fa-fw"></i><span class="menu-name">', WT_I18N::translate('Tools'), '</span></h3>
			<div>';
				foreach (WT_Module::getActiveModules(true) as $module) {
					if ($module instanceof WT_Module_Config) {
						echo '<p><span><a ', (WT_SCRIPT_NAME=="module.php" && safe_GET('mod')==$module->getName() ? 'class="current" ' : ''), 'href="', $module->getConfigLink(), '">', $module->getTitle(), '</a></span></p>';
					}
				}
}
echo '
			</div>
		</div>
	</div>';
	}
echo '
	<div id="admin_content" class="ui-widget-content">' ,
		WT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions;
