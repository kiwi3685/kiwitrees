<?php
// Fancy Tree Pedigree View Module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from JustCarmen
// Copyright (C) 2015 JustCarmen
//
// Derived from webtrees
// Copyright (C) 2014 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

class fancy_treeview_pedigree_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Menu, WT_Module_Resources {

	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of the module */ WT_I18N::translate('Ancestors');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('A narrative report of the ancestors of one individual');
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		$ftv = new WT_Controller_FancyTreeView();

		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_reset':
			$ftv->ftv_reset();
			$this->config();
			break;
		case 'admin_delete':
			$ftv->delete();
			$this->config();
			break;
		case 'show':
			$this->show();
			break;
		case 'image_data':
			$ftv->getImageData();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=fancy_treeview&amp;mod_action=admin_config';
	}

	// Actions from the configuration page
	private function config() {
		return false;
	}
	// ************************************************* START OF FRONT PAGE ********************************* //

	// Show
	private function show() {
		$ftv = new WT_Controller_FancyTreeView();

		global $controller;
		$root			= WT_Filter::get('rootid', WT_REGEX_XREF); // the first pid
		$root_person	= $ftv->get_person($root);
		$controller		= new WT_Controller_Page;

		if($root_person && $root_person->canDisplayName()) {
			$controller
				->setPageTitle(/* I18N: %s is the surname of the root individual */ WT_I18N::translate('Ancestors of %s', $root_person->getFullName()))
				->pageHeader()
				->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
				->addExternalJavascript(WT_MODULES_DIR . 'fancy_treeview/js/ftv.js')
				->addInlineJavascript('
					var RootID				= "' . $root . '";
					var ModuleName			= "' . $this->getName() . '";
					var OptionsNumBlocks	= ' . $ftv->options('numblocks') . ';
					var TextFollow			= "' . WT_I18N::translate('follow') . '";
					', WT_Controller_Base::JS_PRIORITY_HIGH
				)
				->addInlineJavascript('
					autocomplete();

					// submit form to change root id
				    jQuery( "form#change_root" ).submit(function(e) {
				        e.preventDefault();
				        var new_rootid = jQuery("form #new_rootid").val();
						var url = jQuery(location).attr("pathname") + "?mod=' . $this->getName() . '&mod_action=show&rootid=" + new_rootid;
				        jQuery.ajax({
				            url: url,
				            csrf: WT_CSRF_TOKEN,
				            success: function() {
				                window.location = url;
				            },
				            statusCode: {
				                404: function() {
				                    var msg = "' . WT_I18N::translate('This individual does not exist or you do not have permission to view it.') . '";
				                    jQuery("#error").text(msg).addClass("ui-state-error").show();
				                    setTimeout(function() {
				                        jQuery("#error").fadeOut("slow");
				                    }, 3000);
				                    jQuery("form #new_rootid")
				                        .val("")
				                        .focus();
				                }
				            }
				        });
				    });
				');

			// Start page content
			?>
			<div id="resource-page">
				<h2><?php echo $this->getTitle(); ?></h2>
				<div class="chart_options noprint">
					<div class="help_text">
						<div class="help_content">
							<h5><?php echo $this->getDescription(); ?></h5>
							<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
							<div class="hidden">
								<?php echo /* I18N: help for resource facts and events module */ WT_I18N::translate('The list of available facts and events are those set by the site administrator as "All individual facts" and "Unique individual facts" at Administration > Family trees > <i>your family tree</i> > "Edit options" tab and therefore only GEDCOM first-level records.<br>Date filters must be 4-digit year only. Place, type and detail filters can be any string of characters you expect to find in those data fields. The "Type" field is only avaiable for Custom facts and Custom events.'); ?>
							</div>
						</div>
					<form id="change_root">
						<div class="chart_options">
							<label for = "new_rootid" class="label"><?php echo WT_I18N::translate('Change root person'); ?></label>
							<input type="text" data-autocomplete-type="INDI" name="new_rootid" id="new_rootid" value="<?php echo $root; ?>">
						</div>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
				</div>
				<hr class="noprint">
				<div id="fancy_treeview-page">
					<div id="error"></div>
					<div id="page-header">
						<h2>
							<?php echo $controller->getPageTitle() ?>
							<?php if (WT_USER_IS_ADMIN) { ?>
								<a href="module.php?mod=fancy_treeview&amp;mod_action=admin_config" target="_blank" rel="noopener noreferrer" class="noprint">
									<i class="fa fa-cog"></i>
								</a>
							<?php } ?>
						</h2>
					</div>
					<div id="page-body">
						<ol id="fancy_treeview"><?php echo $ftv->printPage('ancestors'); ?></ol>
						<div id="btn_next">
							<button class="btn btn-next" type="button" name="next" value="<?php echo WT_I18N::translate('next'); ?>" title="<?php echo WT_I18N::translate('Show more generations'); ?>">
								<i class="fa fa-arrow-down"></i>
								<?php echo WT_I18N::translate('next'); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php } else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				$controller->pageHeader(); ?>
				<p class="ui-state-error"><?php echo WT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
			<?php exit;
		}
	}

	// ************************************************* START OF MENU ********************************* //

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 120;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		$ftv = new WT_Controller_FancyTreeView();

		global $SEARCH_SPIDER;

		$ftv_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));

		if(!empty($ftv_SETTINGS)) {
			if ($SEARCH_SPIDER) {
				return null;
			}

			foreach ($ftv_SETTINGS as $ftv_ITEM) {
				if($ftv_ITEM['TREE'] == WT_GED_ID && $ftv_ITEM['ACCESS_LEVEL'] >= WT_USER_ACCESS_LEVEL) {
					$ftv_GED_SETTINGS[] = $ftv_ITEM;
				}
			}
			if (!empty($ftv_GED_SETTINGS)) {
				$menu = new WT_Menu(WT_I18N::translate('Descendants'), '#', 'menu-fancy_treeview');

				foreach($ftv_GED_SETTINGS as $ftv_ITEM) {
					if(WT_Person::getInstance($ftv_ITEM['PID'])) {
						if($ftv->options('use_fullname') == true) {
							$submenu = new WT_Menu(WT_I18N::translate('Descendants of %s', WT_Person::getInstance($ftv_ITEM['PID'])->getFullName()), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $ftv_ITEM['PID'], 'menu-fancy_treeview-' . $ftv_ITEM['PID']);
						}
						else {
							$submenu = new WT_Menu(WT_I18N::translate('%s family', $ftv_ITEM['DISPLAY_NAME']), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $ftv_ITEM['PID'], 'menu-fancy_treeview-' . $ftv_ITEM['PID']);
						}
						$menu->addSubmenu($submenu);
					}
				}

				return $menu;
			}
		}
	}

}
