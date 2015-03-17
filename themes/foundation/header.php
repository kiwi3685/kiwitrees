<?php
// Header for Kiwitrees theme
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
define('WT_FOUDATION', WT_STATIC_URL, '/library/framework/Foundation/js/foundation/foundation.js');
define('WT_FOUDATION_TOPBAR', WT_STATIC_URL, '/library/framework/Foundation/js/foundation/foundation.topbar.js');

// This theme uses the jQuery “colorbox” plugin to display images
$this
	->addExternalJavascript (WT_JQUERY_COLORBOX_URL)
	->addExternalJavascript (WT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript (WT_JQUERY_AUTOSIZE)
	->addExternalJavascript (WT_JQUERY_BIGTEXT)
	->addInlineJavascript ('
		$(document).foundation();
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

echo '
	<!DOCTYPE html>
	<html ', WT_I18N::html_markup(), '>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">',
		header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL), '
		<title>', htmlspecialchars($title), '</title>
		<link rel="icon" href="', WT_THEME_URL, 'images/favicon.png" type="image/png">
		<link rel="stylesheet" href="', WT_STATIC_URL, 'library/framework/Foundation/css/normalize.css">
		<link rel="stylesheet" href="', WT_STATIC_URL, 'library/framework/Foundation/css/foundation.css">
		<link rel="stylesheet" href="', WT_STATIC_URL, 'library/framework/FontAwesome/css/font-awesome.css">

		<link rel="stylesheet" type="text/css" href="', WT_THEMES_DIR, '_administration/jquery-ui-1.10.3/jquery-ui-1.10.3.custom.css">
		<link rel="stylesheet" href="', WT_THEME_URL, 'app.css">
		<link rel="stylesheet" href="', WT_THEME_URL, 'style.css" type="text/css">
	</head>';

if ($view!='simple') {echo '<body id="body">';
} else {echo '<body id="body_simple">';}

// begin header section
if ($view!='simple') {
	?>
		<nav class="top-bar" data-topbar role="navigation">
			<ul class="title-area">
				<li class="name">
					<h1><a href="#"><?php echo WT_TREE_TITLE; ?> </a></h1>
				</li>
				<!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
				<li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
			</ul>

			<section class="top-bar-section">
				<!-- Right Nav Section -->
				<ul class="right">
					<?php if (WT_USER_CAN_ACCEPT && exists_pending_change()) { ?>
						<li>
							<a href="#" onclick="window.open(\'edit_changes.php\',\'_blank\', chan_window_specs); return false;" style="color:red;">
								<?php echo WT_I18N::translate('Pending changes'); ?>
							</a>
						</li>
					<?php }
					foreach (WT_MenuBar::getOtherMenus() as $menu) {
						if (strpos($menu, WT_I18N::translate('Login')) && !WT_USER_ID && (array_key_exists('login_block', WT_Module::getInstalledModules('%')))) {
							$class_name	= 'login_block_WT_Module';
							$module		=  new $class_name; ?>
							<li class="has-dropdown">
								<a href="#">', (WT_Site::preference('USE_REGISTRATION_MODULE') ? WT_I18N::translate('Login or Register') : WT_I18N::translate('Login')), '</a>
								<ul class="dropdown" id="login_popup">
									<li>', $module->getBlock('login_block'), '</li>
								</ul>
							</li>
						<?php } else {
							echo $menu->getOtherMenuAsList();
						}
					} ?>
					<li class="has-form">
						<form class="row collapse" action="search.php" method="post">
							<div class="large-8 small-9 columns">
								<input type="text" name="query" placeholder=" <?php echo WT_I18N::translate('Search');?> " dir="auto">
								<input type="hidden" name="action" value="general">
								<input type="hidden" name="topsearch" value="yes">
							</div>
							<div class="large-4 small-3 columns">
								<a href="#" class="alert button expand">Search</a>
							</div>
						</form>
					</li>
				</ul>
			</section>

			<div class="icon-bar" role="navigation">
				<?php if (WT_USER_ID && WT_SCRIPT_NAME != 'index.php') { ?>
				<a class="" aria-labelledby="#itemlabel1">
					<i class="fa fa-bars"></i>
				</a>
				<?php }
				foreach (WT_MenuBar::getMainMenus() as $menu) {
					echo $menu->getMenuAsList();
				} ?>
			</div>


				<?php WT_FlashMessages::getHtmlMessages();  // Feedback from asynchronous actions ?>
		</nav>
<?php }
// begin content section
echo $javascript, '<div id="content">';// closed in footer, as is div "main_content"

// add widget bar inside content div for all pages except Home, and only for logged in users with role 'visitor' or above
if (WT_USER_ID && WT_SCRIPT_NAME != 'index.php' && $view != 'simple') {
	include_once 'widget-bar.php';
}
