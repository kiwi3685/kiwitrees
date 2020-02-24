<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?>>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="noindex,nofollow"><title><?php echo htmlspecialchars($title); ?></title>
	<link rel="icon" href="<?php echo KT_THEME_URL; ?>images/kt.png" type="image/png">
	<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>jquery-ui-custom/jquery-ui.structure.min.css" type="text/css">
	<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>jquery-ui-custom/jquery-ui.theme.min.css" type="text/css">
	<link rel="stylesheet" href="<?php echo KT_THEME_URL; ?>style.css" type="text/css">
	<!--[if IE]>
			<link type="text/css" rel="stylesheet" href="<?php echo KT_THEME_URL; ?>msie.css">
	<![endif]-->
	<?php echo $javascript; ?>
</head>
<?php if ($view != 'simple') { ?>
	<body id="body">
<?php } else { ?>
	<body id="body_simple">
<?php }

if ($view != 'simple') {
	// Header ?>
	<div id="admin_head" class="ui-widget-content">
		<h1 id="title"><?php echo KT_I18N::translate('Administration'); ?></h1>
		<div id="links">
			<?php echo KT_MenuBar::getGedcomMenu(); ?></a>
			 |
			<?php if (KT_USER_GEDCOM_ID) { ?>
				<a href="individual.php?pid=<?php echo KT_USER_GEDCOM_ID; ?>&amp;ged=<?php echo KT_GEDURL; ?>"><?php echo KT_I18N::translate('My individual record'); ?></a>
				 |
			<?php }
			echo logout_link(); ?>
			<span>
				<?php $language_menu = KT_MenuBar::getLanguageMenu();
				if ($language_menu) { ?>
				 	 |
					<?php echo $language_menu->getMenuAsList();
				} ?>
			</span>
			<?php if (KT_USER_CAN_ACCEPT && exists_pending_change()) { ?>
				 |
				 <span><a href="edit_changes.php" target="_blank" rel="noopener noreferrer" style="color:red;"><?php echo KT_I18N::translate('Pending changes'); ?></a></span>
			<?php } ?>
			 |
			<div class="header_search">
				<form action="search.php" method="post">
					<input type="hidden" name="action" value="general">
					<input type="hidden" name="topsearch" value="yes">
					<input type="search" name="query" size="25" placeholder="<?php echo KT_I18N::translate('Search trees'); ?>" dir="auto">
				</form>
			</div>
		</div>
		<div id="info">
			<i class="icon-kiwitrees"></i>
		</div>
	</div> <!--  close admin_head -->

	<!--  Side menu -->
	<div id="admin_menu" class="ui-widget-content">
		<div id="adminAccordion" style="visibility:hidden">
			<h3 id="administration"><i class="fa fa-dashboard fa-fw"></i><span class="menu-name"><span class="menu-name"><?php echo KT_I18N::translate('Dashboard'); ?></span></span></h3>
			<div>
				<p><a <?php echo (KT_SCRIPT_NAME == "admin.php" ? 'class="current" ' : ''); ?>href="admin.php"><?php echo KT_I18N::translate('Home'); ?></a></p>
			</div>
			<?php if (KT_USER_IS_ADMIN) { ?>
				<h3 id="administration"><i class="fa fa-cog fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Site administration'); ?></h3>
				<div>
					<?php $site_tools = array(
						"admin_site_config.php"	=> KT_I18N::translate('Site configuration'),
						"admin_site_logs.php"	=> KT_I18N::translate('Logs'),
						"admin_site_info.php"	=> KT_I18N::translate('Server information'),
						"admin_site_access.php"	=> KT_I18N::translate('Site access rules'),
						"admin_site_clean.php"	=> KT_I18N::translate('Clean up data folder'),
						"admin_site_lang.php"	=> KT_I18N::translate('Custom translation'),
						"admin_site_use.php"	=> KT_I18N::translate('Server usage'),
					);
					arsort($site_tools);
					foreach ($site_tools as $file=>$title) { ?>
						<p><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></p>
					<?php } ?>
				</div>
			<?php } ?>
			<h3 id="trees"><i class="fa fa-tree fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Family trees'); ?></span></h3>
			<div>
				<?php if (KT_USER_IS_ADMIN) { ?>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_trees_manage.php" ? 'class="current" ' : ''); ?>href="admin_trees_manage.php"><?php echo KT_I18N::translate('Manage family trees'); ?></a></p>
				<?php }
				//-- gedcom list
				foreach (KT_Tree::getAll() as $tree) {
					if (userGedcomAdmin(KT_USER_ID, $tree->tree_id)) {
						// Add a title="" element, since long tree titles are cropped ?>
						<p>
							<span>
								<a <?php echo (KT_SCRIPT_NAME == "admin_trees_config.php" && KT_GED_ID == $tree->tree_id ? 'class="current" ' : ''); ?>href="admin_trees_config.php?ged=<?php echo $tree->tree_name_url; ?>" title="<?php echo htmlspecialchars($tree->tree_title); ?>" dir="auto">
									<?php echo $tree->tree_title_html; ?>
								</a>
							</span>
						</p>
					<?php }
				} ?>
			</div>
			<h3 id="tree-tools"><i class="fa fa-wrench fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Family tree tools'); ?></span></h3>
			<div>
				<?php //-- gedcom list
				$ft_tools = array(
					"admin_trees_check.php"				=> KT_I18N::translate('Check for GEDCOM errors'),
					"admin_trees_change.php"			=> KT_I18N::translate('Changes log'),
					"admin_trees_addunlinked.php"		=> KT_I18N::translate('Add unlinked records'),
					"admin_trees_places.php"			=> KT_I18N::translate('Update place names'),
					"admin_trees_merge.php"				=> KT_I18N::translate('Merge records'),
					"admin_trees_renumber.php"			=> KT_I18N::translate('Renumber family tree'),
					"admin_trees_append.php"			=> KT_I18N::translate('Append family tree'),
					"admin_trees_duplicates.php"		=> KT_I18N::translate('Find duplicate individuals'),
					"admin_trees_findunlinked.php"		=> KT_I18N::translate('Find unlinked records'),
					"admin_trees_sanity.php"			=> KT_I18N::translate('Sanity check'),
					"admin_trees_source.php"			=> KT_I18N::translate('Sources - review'),
					"admin_trees_sourcecite.php"		=> KT_I18N::translate('Sources - review citations'),
					"admin_trees_missing.php"			=> KT_I18N::translate('Missing data'),
					"admin_trees_missing-source.php"	=> KT_I18N::translate('Missing fact or event sources'),
				);
				asort($ft_tools);
				foreach ($ft_tools as $file=>$title) { ?>
					<p><a <?php echo (KT_SCRIPT_NAME==$file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></p>
				<?php } ?>
				<p><a href="index_edit.php?gedcom_id=-1" onclick="return modalDialog('index_edit.php?gedcom_id=-1', '<?php echo  KT_I18N::translate('Set the default blocks for new family trees'); ?>');"><?php echo KT_I18N::translate('Set the default blocks'); ?></a></p>
			</div>
			<?php if (KT_USER_IS_ADMIN) { ?>
				<h3 id="user-admin"><i class="fa fa-users fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Users'); ?></span></h3>
				<div>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action')!="cleanup" && safe_GET('action')!="edit" ? 'class="current" ' : ''); ?>href="admin_users.php"><?php echo KT_I18N::translate('Manage users'); ?></a></p>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action')=="edit" && safe_GET('user_id')==0  ? 'class="current" ' : ''); ?>href="admin_users.php?action=edit"><?php echo KT_I18N::translate('Add a new user'); ?></a></p>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_users_bulk.php" ? 'class="current" ' : ''); ?>href="admin_users_bulk.php"><?php echo KT_I18N::translate('Send broadcast messages'); ?></a>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_users.php" && safe_GET('action')=="cleanup" ? 'class="current" ' : ''); ?>href="admin_users.php?action=cleanup"><?php echo KT_I18N::translate('Delete inactive users'); ?></a></p>
				</div>
				<h3 id="media"><i class="fa fa-image fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Media'); ?></span></h3>
				<div>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_media.php" ? 'class="current" ' : ''); ?>href="admin_media.php"><?php echo KT_I18N::translate('Manage media'); ?></a></p>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_media_upload.php" ? 'class="current" ' : ''); ?>href="admin_media_upload.php"><?php echo KT_I18N::translate('Upload media files'); ?></a></p>
				</div>
				<h3 id="modules"><i class="fa fa-puzzle-piece fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Modules'); ?></span></h3>
				<div>
					<p><a <?php echo (KT_SCRIPT_NAME == "admin_modules.php" ? 'class="current" ' : ''); ?>href="admin_modules.php"><?php echo KT_I18N::translate('Manage modules'); ?></a></p>
					<?php //-- module categories
					$module_cats = array(
						"admin_module_menus.php"		=> KT_I18N::translate('Menus'),
						"admin_module_tabs.php"			=> KT_I18N::translate('Tabs'),
						"admin_module_blocks.php"		=> KT_I18N::translate('Blocks'),
						"admin_module_widgets.php"		=> KT_I18N::translate('Widgets'),
						"admin_module_sidebar.php"		=> KT_I18N::translate('Sidebar'),
						"admin_module_reports.php"		=> KT_I18N::translate('Reports'),
						"admin_module_charts.php"		=> KT_I18N::translate('Charts'),
						"admin_module_lists.php"		=> KT_I18N::translate('Lists'),
					);
					asort($module_cats);
					foreach ($module_cats as $file=>$title) { ?>
						<p><a <?php echo (KT_SCRIPT_NAME == $file ? 'class="current" ' : ''); ?>href="<?php echo $file; ?>"><?php echo $title; ?></a></p>
					<?php } ?>
				</div>
				<h3 id="extras"><i class="fa fa-cogs fa-fw"></i><span class="menu-name"><?php echo KT_I18N::translate('Tools'); ?></span></h3>
				<div>
					<?php foreach (KT_Module::getActiveModules(true) as $module) {
						if ($module instanceof KT_Module_Config) { ?>
							<p><span><a <?php echo (KT_SCRIPT_NAME == "module.php" && safe_GET('mod') == $module->getName() ? 'class="current" ' : ''); ?>href="<?php echo $module->getConfigLink(); ?>"><?php echo $module->getTitle(); ?></a></span></p>
						<?php }
					} ?>
				</div>
			<?php } ?>
		</div>
	</div>
<?php }
// Content  ?>
<div id="admin_content" class="ui-widget-content">
	<?php echo KT_FlashMessages::getHtmlMessages(); // Feedback from asynchronous actions
// div closed in footer.php
