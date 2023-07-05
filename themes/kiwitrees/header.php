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

define('KT_JQUERY_BIGTEXT', KT_THEME_URL.'js/jquery-bigtext.js');

// This theme uses the jQuery �colorbox� plugin to display images
$this
	->addExternalJavascript (KT_JQUERY_COLORBOX_URL)
	->addExternalJavascript (KT_JQUERY_WHEELZOOM_URL)
	->addExternalJavascript (KT_JQUERY_AUTOSIZE)
	->addExternalJavascript (KT_JQUERY_BIGTEXT)
	->addInlineJavascript ('
		widget_bar();
		display_help();
		activate_colorbox();
		jQuery.extend(jQuery.colorbox.settings, {
			slideshowStart	:"'.KT_I18N::translate('Play').'",
			slideshowStop	:"'.KT_I18N::translate('Stop').'",
		});
		// Add colorbox to pdf-files
		jQuery("body").on("click", "a.gallery", function(event) {
			jQuery("a[type^=application].gallery").colorbox({
				title: function(){
							var url = jQuery(this).attr("href");
							var img_title = jQuery(this).data("title");
							return "<a href=\"" + url + "\" target=\"_blank\">" + img_title + " - '.
							KT_I18N::translate('Open in full browser window').'</a>";
						}
			});
		});

		jQuery("textarea").autosize();

		jQuery("#bigtext span.treetitle").bigText({
			fontSizeFactor: 1,
		    maximumFontSize: 40,
		    limitingDimension: "both",
		    verticalAlign: "middle"
		});

	');

global $ALL_CAPS;
if ($ALL_CAPS) $this->addInlineJavascript('all_caps();');
$ctype = safe_REQUEST($_REQUEST, 'ctype', array('gedcom', 'user'), KT_USER_ID ? 'user' : 'gedcom');

$show_widgetbar = false;
if (KT_USER_ID && KT_SCRIPT_NAME != 'index.php' && $view != 'simple' && KT_Module::getActiveWidgets()) {
	$show_widgetbar = true;
}
?>
<!DOCTYPE html>
	<html <?php echo KT_I18N::html_markup(); ?>>
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<?php echo header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL); ?>
		<title><?php echo htmlspecialchars((string) $title); ?></title>
		<link rel="icon" href="<?php echo KT_THEME_URL; ?>images/favicon.png" type="image/png">
		<link rel="stylesheet" href="<?php echo KT_THEMES_DIR; ?>_administration/jquery-ui-custom/jquery-ui.structure.min.css" type="text/css">
		<link rel="stylesheet" href="<?php echo KT_THEMES_DIR; ?>_administration/jquery-ui-custom/jquery-ui.theme.min.css" type="text/css">
		<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>style.css" type="text/css">
		<!--[if IE]>
			<link type="text/css" rel="stylesheet" href="<?php echo KT_THEME_URL; ?>msie.css">
		<![endif]-->

		<?php if (file_exists(KT_THEME_URL . 'mystyle.css')) { ?>
			<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>mystyle.css<?php time(); ?>" type="text/css">
		<?php } ?>
	</head>

<?php // begin header section

if ($view!='simple') {echo '<body id="body">';
} else {echo '<body id="body_simple">';}

// begin header section
if ($view!='simple') { ?>
	<div id="navbar">
		<div id="header">
			<div id="bigtext" class="title" dir="auto">
				<?php echo KT_TREE_TITLE . KT_TREE_SUBTITLE; ?>
			</div>
			<div class="header_search">
				<form action="search.php" method="post">
					<input type="hidden" name="action" value="general">
					<input type="hidden" name="topsearch" value="yes">
					<input type="search" name="query" size="25" placeholder="<?php echo KT_I18N::translate('Search trees'); ?>" dir="auto">
				</form>
			</div>
			<ul id="extra-menu" class="makeMenu">
				<?php if (KT_USER_CAN_ACCEPT && exists_pending_change()) { ?>
					<li>
						<a href="edit_changes.php" target="_blank" rel="noopener noreferrer" style="color:red;">
							<?php echo KT_I18N::translate('Pending changes'); ?>
						</a>
					</li>
				<?php }
				foreach (KT_MenuBar::getOtherMenus() as $menu) {
					if (strpos($menu, KT_I18N::translate('Login')) && !KT_USER_ID && KT_Module::getModuleByName('login_block')) {
						$class_name	= 'login_block_KT_Module';
						$module		= new $class_name; ?>
						<li>
							<a href="#">
								<?php echo (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login')); ?>
							</a>
							<ul id="login_popup">
								<li><?php echo $module->getBlock('login_block'); ?></li>
							</ul>
						</li>
					<?php } else {
						echo $menu->getMenuAsList();
					}
				} ?>
			</ul>
		</div>
		<div id="topMenu" class="ui-state-active">
			<ul id="main-menu">
				<?php if ($show_widgetbar) { ?>
					<li id="widget-button" class="fa fa-fw fa-2x fa-bars">
						<a href="#" >
							<span style="line-height: inherit;">&nbsp;</span>
						</a>
					</li>
				<?php }
				foreach (KT_MenuBar::getMainMenus() as $menu) {
					echo $menu->getMenuAsList();
				} ?>
			</ul>
			<!-- select menu for responsive layouts only -->
			<div id="nav-select" onChange="window.location.href=this.value">
				<a href="#"><?php echo /* I18M: Menu label for responsive meny drop down */ KT_I18N::translate('Main menu'); ?></a>
				<?php foreach (KT_MenuBar::getMainMenus() as $menu) {
					echo $menu->getResponsiveMenu();
				} ?>
			</div>
		</div>
		<?php echo KT_FlashMessages::getHtmlMessages(); ?>
	</div>
<?php }
// add widget bar for all pages except Home, and only for logged in users with role 'visitor' or above
if ($show_widgetbar) {
	include_once 'widget-bar.php';
}
// add floating contact link if it is configured
if (array_key_exists('float_contacts', KT_Module::getActiveModules())) {
	float_contacts_KT_Module::show();
}

// begin content section
echo $javascript; ?>
<div id="content">
<?php
