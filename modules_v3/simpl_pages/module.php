<?php
// A module to allow additional administrator defined pages
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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

class simpl_pages_WT_Module extends WT_Module implements WT_Module_Menu, WT_Module_Block, WT_Module_Config {

	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Pages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Simpl_pages” module */ WT_I18N::translate('Display resource pages.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_delete':
			$this->delete();
			$this->config();
			break;
		case 'admin_edit':
			$this->edit();
			break;
		case 'admin_movedown':
			$this->movedown();
			$this->config();
			break;
		case 'admin_moveup':
			$this->moveup();
			$this->config();
			break;
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_NONE;
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
	}

	public function getMenuTitle() {
		$default_title = WT_I18N::translate('Resources');
		$HEADER_TITLE = WT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getSummaryDescription() {
		$default_description = WT_I18N::translate('These are resources');
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
		return $HEADER_DESCRIPTION;
	}

	// Action from the configuration page
	private function edit() {
		if (WT_USER_IS_ADMIN) {
			require_once WT_ROOT.'includes/functions/functions_edit.php';
			if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
				$block_id = WT_Filter::postInteger('block_id');
				if ($block_id) {
					WT_DB::prepare(
						"UPDATE `##block` SET gedcom_id=NULLIF(?, ''), block_order=? WHERE block_id=?"
					)->execute(array(
						safe_POST('gedcom_id'),
						(int)safe_POST('block_order'),
						$block_id
					));
				} else {
					WT_DB::prepare(
						"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (NULLIF(?, ''), ?, ?)"
					)->execute(array(
						safe_POST('gedcom_id'),
						$this->getName(),
						(int)safe_POST('block_order')
					));
					$block_id = WT_DB::getInstance()->lastInsertId();
				}
				set_block_setting($block_id, 'pages_title', safe_POST('pages_title', WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'pages_content', safe_POST('pages_content', WT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'pages_access', safe_POST('pages_access', WT_REGEX_UNSAFE));
				$languages = array();
				foreach (WT_I18N::used_languages() as $code=>$name) {
					if (WT_Filter::postBool('lang_'.$code)) {
						$languages[] = $code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id	= safe_GET('block_id');
				$controller	= new WT_Controller_Page();
				if ($block_id) {
					$controller->setPageTitle(WT_I18N::translate('Edit pages'));
					$items_title	= get_block_setting($block_id, 'pages_title');
					$items_content	= get_block_setting($block_id, 'pages_content');
					$items_access	= get_block_setting($block_id, 'pages_access');
					$block_order	= WT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id	=WT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(WT_I18N::translate('Add pages'));
					$items_title	= '';
					$items_content	= '';
					$items_access	= 1;
					$block_order 	= WT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id = WT_GED_ID;
				}
				$controller->pageHeader();
				if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
					ckeditor_WT_Module::enableEditor($controller);
				}
				?>
				<div id="<?php echo $this->getName();?>">
					<form name="pages" method="post" action="#">
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="', $block_id, '">
						<table id="faq_module">
							<tr>
								<th>', WT_I18N::translate('Title'),'</th>
							</tr>
							<tr>
								<td><input type="text" name="pages_title" size="90" tabindex="1" value="<?php echo htmlspecialchars($items_title);?>"></td>
							</tr>
							<tr>
								<th><?php echo WT_I18N::translate('Content');?></th>
							</tr>
							<tr>
								<td>
									<textarea name="pages_content" class="html-edit" rows="10" cols="90" tabindex="2"><?php echo htmlspecialchars($items_content);?></textarea>
								<td>
							</tr>
							<tr>
								<th><?php echo WT_I18N::translate('Access level');?></th>
							</tr>
							<tr>
								<td><?php edit_field_access_level('pages_access', $items_access, 'tabindex="4"');?></td>
							</tr>
						</table>
						<table id="pages_module">
							<tr>
								<th><?php echo WT_I18N::translate('Show this pages for which languages?'), help_link('pages_language', $this->getName());?></th>
								<th><?php echo WT_I18N::translate('Pages position'), help_link('pages_position', $this->getName());?></th>
								<th><?php echo WT_I18N::translate('Pages visibility'), help_link('pages_visibility', $this->getName());?></th>
							</tr>
							<tr>
								<td>
									<?php
									$languages = get_block_setting($block_id, 'languages');
									echo edit_language_checkboxes('lang_', $languages);
									?>
								</td>
								<td><input type="text" name="block_order" size="3" tabindex="5" value="<?php $block_order;?>"></td>
								<td><?php echo select_edit_control('gedcom_id', WT_Tree::getIdList(), WT_I18N::translate('All'), $gedcom_id, 'tabindex="4"');?></td>
							</tr>
						</table>
						<p>
							<input type="submit" value="<?php echo WT_I18N::translate('save');?>" tabindex="7">
							&nbsp;
							<input type="button" value="<?php echo WT_I18N::translate('cancel');?>" onclick="window.location=\''.$this->getConfigLink().'\';" tabindex="8">
						</p>
					</form>
				</div>
				<?php exit;
			}
		} else {
			header('Location: ' . WT_SERVER_NAME.WT_SCRIPT_PATH);
		}
	}

	private function delete() {
		$block_id = safe_GET('block_id');

		WT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		WT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	private function moveup() {
		$block_id = safe_GET('block_id');

		$block_order = WT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block = WT_DB::prepare(
			"SELECT block_order, block_id".
			" FROM `##block`".
			" WHERE block_order=(".
			"  SELECT MAX(block_order) FROM `##block` WHERE block_order < ? AND module_name=?".
			" ) AND module_name=?".
			" LIMIT 1"
		)->execute(array($block_order, $this->getName(), $this->getName()))->fetchOneRow();
		if ($swap_block) {
			WT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($swap_block->block_order, $block_id));
			WT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($block_order, $swap_block->block_id));
		}
	}

	private function movedown() {
		$block_id = safe_GET('block_id');

		$block_order = WT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block = WT_DB::prepare(
			"SELECT block_order, block_id".
			" FROM `##block`".
			" WHERE block_order=(".
			"  SELECT MIN(block_order) FROM `##block` WHERE block_order>? AND module_name=?".
			" ) AND module_name=?".
			" LIMIT 1"
		)->execute(array($block_order, $this->getName(), $this->getName()))->fetchOneRow();
		if ($swap_block) {
			WT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($swap_block->block_order, $block_id));
			WT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($block_order, $swap_block->block_id));
		}
	}

	private function show() {
		global $controller;
		$controller = new WT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader();
		$items_id	 = safe_GET('pages_id');
		$items_list  = $this->getPagesList();
		$count_items = 0;
		foreach ($items_list as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $items->pages_access >= WT_USER_ACCESS_LEVEL) {
				$count_items = $count_items +1;
			}
		}
		?>
		<div id="pages-container">
			<h2><?php echo $this->getMenuTitle();?></h2>
			<p><?php echo $this->getSummaryDescription();?></p>
			<div style="clear:both;"></div>
			<div id="pages_tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
				<?php
				if ($count_items > 1) { ?>
					<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
						<?php
						foreach ($items_list as $items) {
							$languages = get_block_setting($items->block_id, 'languages');
							if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $items->pages_access >= WT_USER_ACCESS_LEVEL) { ?>
								<li class="ui-state-default ui-corner-top<?php ($items_id==$items->block_id ? ' ui-tabs-selected ui-state-active' : '');?>">
									<a href="module.php?mod=<?php echo $this->getName();?>&amp;mod_action=show&amp;pages_id=<?php echo $items->block_id;?>">
										<span title="<?php echo WT_I18N::translate($items->pages_title);?>"><?php echo WT_I18N::translate($items->pages_title);?></span>
									</a>
								</li>
							<?php }
						} ?>
					</ul>
				<?php } ?>
				<div id="outer_pages_container" style="padding: 1em;">
					<?php
					foreach ($items_list as $items) {
						$languages = get_block_setting($items->block_id, 'languages');
						if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $items_id==$items->block_id && $items->pages_access >= WT_USER_ACCESS_LEVEL) {
							$items_content = $items->pages_content;
						}
					}
					if (empty($items_content)) {
						$items_content = '<h4>'.WT_I18N::translate('No content').'</h4>';
					}
					echo $items_content;?>
				</div> <!-- close outer_pages_container -->
			</div> <!-- close pages_tabs -->
		</div> <!-- close pages-container -->
		<?php
	}

	private function config() {
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#pages_tabs").tabs();');

		if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
			ckeditor_WT_Module::enableEditor($controller);
		}

		$action = safe_POST('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'HEADER_TITLE',		safe_POST('NEW_HEADER_TITLE'));
			set_module_setting($this->getName(), 'HEADER_DESCRIPTION',	safe_POST('NEW_HEADER_DESCRIPTION', WT_REGEX_UNSAFE)); // allow html
			AddToLog($this->getName() . ' config updated', 'config');
		}

		$items = WT_DB::prepare (
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_content".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_content'".
			" AND IFNULL(gedcom_id, ?)=?".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID, WT_GED_ID))->fetchAll();

		$min_block_order = WT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		$max_block_order = WT_DB::prepare(
			"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();
		?>

		<div id="<?php echo $this->getName();?>">
<!--		<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/pages/" target="_blank" title="'. WT_I18N::translate('View FAQ for this page.'). '">'. WT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a> -->
			<h2><?php echo $controller->getPageTitle();?></h2>
			<div id="pages_tabs">
				<ul>
					<li><a href="#pages_summary"><span><?php echo WT_I18N::translate('Summary');?></span></a></li>
					<li><a href="#pages_pages"><span><?php echo WT_I18N::translate('Pages');?></span></a></li>
				</ul>
				<div id="pages_summary">
					<form method="post" name="configform" action="module.php?mod=' . $this->getName() ;?>&mod_action=admin_config">
						<input type="hidden" name="action" value="update">
						<div class="label"><?php echo WT_I18N::translate('Main menu and summary page title'), help_link('pages_title',$this->getName());?></div>
						<div class="value"><input type="text" name="NEW_HEADER_TITLE" value="<?php echo $this->getMenuTitle();?>"></div>
						<div class="label"><?php echo WT_I18N::translate('Summary page description'), help_link('pages_description',$this->getName());?></div>
						<div class="value2">
							<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" rows="5" cols="120"><?php echo $this->getSummaryDescription();?></textarea>
						</div>
						<div class="save"><input type="submit" value="<?php echo WT_I18N::translate('save');?>"></div>
					</form>
				</div>
				<div id="pages_pages">
					<form method="get" action="<?php echo WT_SCRIPT_NAME;?>#pages_pages">
						<label><?php echo WT_I18N::translate('Family tree');?></label>
						<input type="hidden" name="mod", value="<?php echo $this->getName();?>">
						<input type="hidden" name="mod_action", value="admin_config">
						<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM);?>
						<input type="submit" value="<?php echo WT_I18N::translate('show');?>">
					</form>
					<div class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">
						<a class="ui-button-text" href="module.php?mod=<?php $this->getName();?>&amp;mod_action=admin_edit">
							<?php echo WT_I18N::translate('Add page');?>
						</a>
					</div>
					<table id="pages_module">
						<?php
						if ($items) {
							$trees = WT_Tree::getAll();
							foreach ($items as $item) { ?>
								<tr class="faq_edit_pos">
									<td>
										<?php echo WT_I18N::translate('Position item'), ': ', $item->block_order;
										if ($item->gedcom_id==null) {
											echo WT_I18N::translate('All');
										} else {
											echo $trees[$item->gedcom_id]->tree_title_html;
										} ?>
									</td>
									<td>
										<?php if ($item->block_order == $min_block_order) { ?>
											&nbsp;
										<?php } else { ?>
											<a href="module.php?mod=<?php echo $this->getName();?>&amp;mod_action=admin_moveup&amp;block_id=<?php echo $item->block_id;?> "class="icon-uarrow">
											</a>
										<?php } ?>
									</td>
									<td>
										<?php if ($item->block_order == $max_block_order) { ?>
											&nbsp;
										<?php } else { ?>
											<a href="module.php?mod=<?php echo $this->getName();?>&amp;mod_action=admin_movedown&amp;block_id=<?php echo $item->block_id;?> "class="icon-darrow">
											</a>
										<?php } ?>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName();?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $item->block_id;?>">
											<?php echo WT_I18N::translate('Edit');?>
										</a>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName();?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $item->block_id;?>" onclick="return confirm(\'<?php echo WT_I18N::translate('Are you sure you want to delete this page?');?>\');">
											<?php echo WT_I18N::translate('Delete');?>
										</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="faq_edit_title"><?php echo WT_I18N::translate($item->pages_title);?></div>
											<div><?php substr($item->pages_content, 0, 1)=='<' ? $item->pages_content : nl2br($item->pages_content);?></div>
										</div>
									</td>
								</tr>
							<?php }
						} else { ?>
							<tr>
								<td class="error center" colspan="5">
									<?php echo WT_I18N::translate('No pages have been created');?>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	// Return the list of pages
	private function getPagesList() {
		return WT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_access, bs3.setting_value AS pages_content".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" JOIN `##block_setting` bs3 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_access'".
			" AND bs3.setting_name='pages_content'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID))->fetchAll();
	}

	// Return the list of pages for menu
	private function getMenupagesList() {
		return WT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_access".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_access'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID))->fetchAll();
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 40;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;
		$block_id = safe_GET('block_id');
		$blockId_list = array();
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $items->pages_access >= WT_USER_ACCESS_LEVEL) {
				$blockId_list[] = $items->block_id;
			}
		}
		if( !empty( $blockId_list ) ) {
			$default_block = $blockId_list[0];
		} else {
			$default_block = "";
		}

		if ($SEARCH_SPIDER) {
			return null;
		}

		//-- main PAGES menu item
		$menu = '';
		$menu = new WT_Menu($this->getMenuTitle(), 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;pages_id='.$default_block, 'menu-my_pages', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $items->pages_access >= WT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;pages_id='.$items->block_id;
				$submenu = new WT_Menu(WT_I18N::translate($items->pages_title), $path, 'menu-my_pages-'.$items->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (WT_USER_IS_ADMIN) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit pages'), $this->getConfigLink(), 'menu-my_pages-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}
}
