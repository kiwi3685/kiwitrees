<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class extra_menus_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ KT_I18N::translate('Extra menus');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Extra menus” module */ KT_I18N::translate('Provides links to custom defined pages.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 60;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
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
		default:
				header('HTTP/1.0 404 Not Found');
			}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}

	public function getMenuTitle() {
		$default_title = KT_I18N::translate('Menu');
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'MENU_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	// Action from the configuration page
	private function edit() {
		if (KT_USER_IS_ADMIN) {
			require_once KT_ROOT.'includes/functions/functions_edit.php';
			if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
				$block_id = KT_Filter::postInteger('block_id');
				if ($block_id) {
					KT_DB::prepare(
						"UPDATE `##block` SET gedcom_id=NULLIF(?, ''), block_order=? WHERE block_id=?"
					)->execute(array(
						safe_POST('gedcom_id'),
						(int)safe_POST('block_order'),
						$block_id
					));
				} else {
					KT_DB::prepare(
						"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (NULLIF(?, ''), ?, ?)"
					)->execute(array(
						safe_POST('gedcom_id'),
						$this->getName(),
						(int)safe_POST('block_order')
					));
					$block_id = KT_DB::getInstance()->lastInsertId();
				}
				set_block_setting($block_id, 'menu_title',		safe_POST('menu_title',			KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'menu_address',	safe_POST('menu_address',		KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'menu_access',		safe_POST('menu_access',		KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'new_tab',			safe_POST('new_tab',			KT_REGEX_UNSAFE));
				$languages = array();
				foreach (KT_I18N::used_languages() as $code=>$name) {
					if (KT_Filter::postBool('lang_'.$code)) {
						$languages[] = $code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id	= safe_GET('block_id');
				$controller	= new KT_Controller_Page();
				if ($block_id) {
					$controller->setPageTitle(KT_I18N::translate('Edit menu'));
					$menu_title		= get_block_setting($block_id, 'menu_title');
					$menu_address	= get_block_setting($block_id, 'menu_address');
					$menu_access	= get_block_setting($block_id, 'menu_access');
					$new_tab		= get_block_setting($block_id, 'new_tab');
					$block_order = KT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id = ?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id = KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id = ?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(KT_I18N::translate('Add menu'));
					$menu_access	= 1;
					$menu_title		= '';
					$menu_address	= '';
					$new_tab		= 0;
					$block_order	= KT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name = ?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id = KT_GED_ID;
				}
				$controller->pageHeader();
				?>
				<div id="<?php echo $this->getName();?>">
					<form name="menu" method="post" action="#">
						<?php echo KT_Filter::getCsrf();?>
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="<?php echo $block_id;?>">
						<div id="module_admin">
							<label for "menu_title"><?php echo KT_I18N::translate('Title');?></label>
								<input type="text" id="menu_title" name="menu_title" size="51" tabindex="1" value="<?php echo $menu_title;?>" placeholder="<?php echo KT_I18N::translate('Add your menu title here');?>" autofocus>
							<label for "menu_title"><?php echo KT_I18N::translate('Menu address');?></label>
								<input type="text" id="menu_address" name="menu_address" size="51" tabindex="2" value="<?php echo $menu_address;?>" placeholder="<?php echo KT_I18N::translate('Add your menu address here');?>">
							<label for "menu_access"><?php echo KT_I18N::translate('Access level'); ?></label>
								<?php echo edit_field_access_level('menu_access', $menu_access, 'tabindex="3"'); ?>
							<label for "block_order"><?php echo KT_I18N::translate('Menu position');?></label>
								<input type="text" id="block_order" name="block_order" size="3" tabindex="4" value="<?php echo $block_order; ?>">
							<label for "gedcom_id"><?php echo KT_I18N::translate('Menu visibility');?></label>
								<?php echo select_edit_control('gedcom_id', KT_Tree::getIdList(), '', $gedcom_id, 'tabindex="5"');?>
							<label for "new_tab"><?php echo KT_I18N::translate('Open menu in new tab or window'); ?></label>
								<?php echo checkbox('new_tab', $new_tab, 'tabindex="6"');?>
						</div>
						<div id="module_lang">
							<label for = "languages"><?php echo KT_I18N::translate('Show this menu for which languages?');?></label>
								<?php $languages = get_block_setting($block_id, 'languages');
								echo edit_language_checkboxes('lang_', $languages);?>
						</div>
						<p class="save">
							<button class="btn btn-primary save" type="submit"  tabindex="7">
								<i class="fa fa-floppy-o"></i>
								<?php echo KT_I18N::translate('save'); ?>
							</button>
							<button class="btn btn-primary cancel" type="button" onclick="window.location='<?php echo $this->getConfigLink(); ?>';" tabindex="8">
								<i class="fa fa-times"></i>
								<?php echo KT_I18N::translate('cancel'); ?>
							</button>
						</p>
					</form>
				</div>
			<?php exit;
			}
		} else {
			header('Location: ' . KT_SERVER_NAME.KT_SCRIPT_PATH);
		}
	}

	private function delete() {
		$block_id = safe_GET('block_id');

		KT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		KT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	private function moveup() {
		$block_id = safe_GET('block_id');

		$block_order = KT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block = KT_DB::prepare(
			"SELECT block_order, block_id".
			" FROM `##block`".
			" WHERE block_order=(".
			"  SELECT MAX(block_order) FROM `##block` WHERE block_order < ? AND module_name=?".
			" ) AND module_name=?".
			" LIMIT 1"
		)->execute(array($block_order, $this->getName(), $this->getName()))->fetchOneRow();
		if ($swap_block) {
			KT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($swap_block->block_order, $block_id));
			KT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($block_order, $swap_block->block_id));
		}
	}

	private function movedown() {
		$block_id = safe_GET('block_id');

		$block_order = KT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block = KT_DB::prepare(
			"SELECT block_order, block_id".
			" FROM `##block`".
			" WHERE block_order=(".
			"  SELECT MIN(block_order) FROM `##block` WHERE block_order>? AND module_name=?".
			" ) AND module_name=?".
			" LIMIT 1"
		)->execute(array($block_order, $this->getName(), $this->getName()))->fetchOneRow();
		if ($swap_block) {
			KT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($swap_block->block_order, $block_id));
			KT_DB::prepare(
				"UPDATE `##block` SET block_order=? WHERE block_id=?"
			)->execute(array($block_order, $swap_block->block_id));
		}
	}

	private function config() {
		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#menus_tabs").tabs();');

		$action = safe_POST('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'MENU_TITLE', safe_POST('NEW_MENU_TITLE'));
			AddToLog($this->getName() . ' config updated', 'config');
		}

		$items = KT_DB::prepare (
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS menu_title, bs2.setting_value AS menu_address".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='menu_title'".
			" AND bs2.setting_name='menu_address'".
			" AND IFNULL(gedcom_id, ?)=?".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID, KT_GED_ID))->fetchAll();

		$min_block_order = KT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		$max_block_order = KT_DB::prepare(
			"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();
		?>

		<div id="<?php echo $this->getName();?>">
<!--		<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/pages/" target="_blank" rel="noopener noreferrer" title="'. KT_I18N::translate('View FAQ for this page.'). '">'. KT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a> -->
			<h2><?php echo $controller->getPageTitle();?></h2>
			<div id="menus_tabs">
				<ul>
					<li><a href="#menus_summary"><span><?php echo KT_I18N::translate('Main menu title'); ?></span></a></li>
					<li><a href="#menus_pages"><span><?php echo KT_I18N::translate('Menus'); ?></span></a></li>
				</ul>
				<div id="menus_summary">
					<form method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
						<input type="hidden" name="action" value="update">
						<div class="label"><?php echo KT_I18N::translate('Main menu title'); ?></div>
						<div class="value"><input type="text" name="NEW_MENU_TITLE" value="<?php echo $this->getMenuTitle(); ?>"></div>
						<div class="save">
							<button class="btn btn-primary save" type="submit">
								<i class="fa fa-floppy-o"></i>
								<?php echo KT_I18N::translate('save'); ?>
							</button>
						</div>
					</form>
				</div>
				<div id="menus_pages">
					<form method="get" action="<?php echo KT_SCRIPT_NAME;?>">
						<label><?php echo KT_I18N::translate('Family tree');?></label>
						<input type="hidden" name="mod" value="<?php echo $this->getName();?>">
						<input type="hidden" name="mod_action" value="admin_config">
						<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM);?>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo KT_I18N::translate('show'); ?>
						</button>
					</form>
					<div>
						<button class="btn btn-primary add" onclick="location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit'">
							<i class="fa fa-plus"></i>
							<?php echo KT_I18N::translate('Add menu item'); ?>
						</button>
					</div>
					<table id="menus_edit">
						<?php
						if ($items) {
							$trees = KT_Tree::getAll();
							foreach ($items as $menu) { ?>
								<tr class="menus_edit_pos">
									<td>
										<?php
										echo '<p>' . KT_I18N::translate('Menu position') . '<span>' . ($menu->block_order) . '</span></p>';
										echo '<p>' . KT_I18N::translate('Family tree');
											if ($menu->gedcom_id == null) {
												echo '<span>' . KT_I18N::translate('All') . '</span>';
									} else {
												echo '<span>' . $trees[$menu->gedcom_id]->tree_title_html . '</span>';
											}
										echo '</p>';
										?>
									</td>
									<td>
										<?php
										if ($menu->block_order == $min_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_moveup&amp;block_id=' . $menu->block_id . '" class="icon-uarrow"></a>';
										}
										?>
									</td>
									<td>
										<?php
										if ($menu->block_order == $max_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_movedown&amp;block_id=' . $menu->block_id . '" class="icon-darrow"></a>';
										}
										?>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $menu->block_id; ?>">
											<?php echo KT_I18N::translate('Edit');?>
										</a>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $menu->block_id; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to delete this menu item?'); ?>');">
											<?php echo KT_I18N::translate('Delete');?>
										</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="menus_edit_title"><?php echo KT_I18N::translate($menu->menu_title);?></div>
											<div class="menus_edit_content"><?php echo substr(KT_I18N::translate($menu->menu_address), 0, 1)=='<' ? KT_I18N::translate($menu->menu_address) : nl2br(KT_I18N::translate($menu->menu_address)); ?></div>
										</div>
									</td>
								</tr>
							<?php }
						} else { ?>
							<tr>
								<td class="error center" colspan="5">
									<?php echo KT_I18N::translate('The menu list is empty.');?>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	// Return the list of menus
	private function getMenuList() {
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS menu_title, bs2.setting_value AS menu_access, bs3.setting_value AS menu_address".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" JOIN `##block_setting` bs3 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='menu_title'".
			" AND bs2.setting_name='menu_access'".
			" AND bs3.setting_name='menu_address'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$menu_titles = $this->getMenuList();
		$lang = '';

		$min_block = KT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		foreach ($menu_titles as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if (in_array(KT_LOCALE, explode(',', $languages))) {
				$lang = KT_LOCALE;
			} else {
				$lang = '';
			}
		}

		$default_block = KT_DB::prepare(
			"SELECT ##block.block_id FROM `##block`, `##block_setting` WHERE block_order=? AND module_name=? AND ##block.block_id = ##block_setting.block_id AND ##block_setting.setting_value LIKE ?"
		)->execute(array($min_block, $this->getName(), '%'.$lang.'%'))->fetchOne();

		if (count($menu_titles) == 0) {
			$main_menu_address = '#';
		} else {
			$main_menu_address = KT_DB::prepare(
				"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
			)->execute(array($default_block, 'menu_address'))->fetchOne();
		}

		if (count($menu_titles) == 0 || count($menu_titles) > 1) {
			$main_menu_title = $this->getMenuTitle();
		} else {
			$main_menu_title = KT_DB::prepare(
				"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
			)->execute(array($default_block, 'menu_title'))->fetchOne();
		}

		$main_menu_target = KT_DB::prepare(
			"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
		)->execute(array($default_block, 'new_tab'))->fetchOne();

		//-- main menu item
		$menu = new KT_Menu($main_menu_title, $main_menu_address, $this->getName(), 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');

		if ($main_menu_target == 1) {
			$menu->addTarget('_blank');
		}

		foreach ($menu_titles as $items) {
			if (count($menu_titles)>1) {
				$languages=get_block_setting($items->block_id, 'languages');
				if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->menu_access>=KT_USER_ACCESS_LEVEL) {
					$submenu = new KT_Menu(KT_I18N::translate($items->menu_title), $items->menu_address, $this->getName().'-'.str_replace(' ', '', $items->menu_title));
					$target = get_block_setting($items->block_id, 'new_tab', 0);
					if ($target == 1) {
						$submenu->addTarget('_blank');
					}
					$menu->addSubmenu($submenu);
				}
			}
		}
		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit menus'), $this->getConfigLink(), $this->getName().'-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

}
