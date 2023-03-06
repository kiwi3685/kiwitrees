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

class pages_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return KT_I18N::translate('Pages');
	}

	public function getMenuTitle() {
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', 'Resources'));
		return $HEADER_TITLE;
	}

	// Extend class KT_Module
	public function getDescription() {
		return KT_I18N::translate('Display resource pages.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 40;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
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
	public function isUserBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;
		$block_id = safe_GET('block_id');
		$blockId_list = array();
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
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
		$menu = new KT_Menu($this->getMenuTitle(), 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;pages_id='.$default_block, 'menu-my_pages', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');
		foreach ($this->getMenupagesList() as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;pages_id='.$items->block_id;
				$submenu = new KT_Menu(KT_I18N::translate($items->pages_title), $path, 'menu-my_pages-'.$items->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit pages'), $this->getConfigLink(), 'menu-my_pages-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
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
		}
	}

	// Action from the configuration page
	private function edit() {
		if (KT_USER_IS_ADMIN) {
			require_once KT_ROOT . 'includes/functions/functions_edit.php';

			if (safe_POST_bool('save')) {
				$block_id=safe_POST('block_id');
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
					$block_id=KT_DB::getInstance()->lastInsertId();
				}
				set_block_setting($block_id, 'pages_title', safe_POST('pages_title', KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'pages_content', safe_POST('pages_content', KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'pages_access', safe_POST('pages_access', KT_REGEX_UNSAFE));
				$languages=array();
				foreach (KT_I18N::used_languages() as $code=>$name) {
					if (safe_POST_bool('lang_'.$code)) {
						$languages[]=$code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id=safe_GET('block_id');
				$controller=new KT_Controller_Page();
				if ($block_id) {
					$controller->setPageTitle(KT_I18N::translate('Edit pages'));
					$items_title=get_block_setting($block_id, 'pages_title');
					$items_content=get_block_setting($block_id, 'pages_content');
					$items_access=get_block_setting($block_id, 'pages_access');
					$block_order=KT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id=KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(KT_I18N::translate('Add pages'));
					$items_title='';
					$items_content='';
					$items_access=1;
					$block_order=KT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id=KT_GED_ID;
				}
				$controller->pageHeader();
				if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
					ckeditor_KT_Module::enableEditor($controller);
				}

				echo '<div id="' . $this->getName() . '">
					<form name="pages" method="post" action="#">
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="', $block_id, '">
						<table id="faq_module">
							<tr>
								<th>', KT_I18N::translate('Title'),'</th>
							</tr>
							<tr>
								<td><input type="text" name="pages_title" size="90" tabindex="1" value="'.htmlspecialchars((string) $items_title).'"></td>
							</tr>
							<tr>
								<th>', KT_I18N::translate('Content'),'</th>
							</tr>
							<tr>
								<td><textarea name="pages_content" class="html-edit" rows="10" cols="90" tabindex="2">', htmlspecialchars((string) $items_content), '</textarea><td>
							</tr>
							<tr>
								<th>', KT_I18N::translate('Access level'),'</th>
							</tr>
							<tr>
								<td>', edit_field_access_level('pages_access', $items_access, 'tabindex="4"'), '</td>
							</tr>
						</table>
						<table id="pages_module">
							<tr>
								<th>', KT_I18N::translate('Show this pages for which languages?'), help_link('pages_language', $this->getName()), '</th>
								<th>', KT_I18N::translate('Pages position'), help_link('pages_position', $this->getName()), '</th>
								<th>', KT_I18N::translate('Pages visibility'), help_link('pages_visibility', $this->getName()), '</th>
							</tr>
							<tr>
								<td>',	$languages=get_block_setting($block_id, 'languages'); echo edit_language_checkboxes('lang_', $languages), '</td>
								<td><input type="text" name="block_order" size="3" tabindex="5" value="', $block_order, '"></td>
								<td>', select_edit_control('gedcom_id', KT_Tree::getIdList(), '', $gedcom_id, 'tabindex="4"'),'</td>
							</tr>
						</table>
						<p>
							<input type="submit" value="', KT_I18N::translate('Save'), '" tabindex="7">
							&nbsp;
							<input type="button" value="', KT_I18N::translate('Cancel'), '" onclick="window.location=\''.$this->getConfigLink().'\';" tabindex="8">
						</p>
					</form>
				</div>';
				exit;
			}
		} else {
			header('Location: ' . KT_SERVER_NAME.KT_SCRIPT_PATH);
		}
	}

	private function delete() {
		$block_id=safe_GET('block_id');

		KT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		KT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	private function moveup() {
		$block_id=safe_GET('block_id');

		$block_order=KT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block=KT_DB::prepare(
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
		$block_id=safe_GET('block_id');

		$block_order=KT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block=KT_DB::prepare(
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

	private function show() {
		global $controller;

		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', 'Resources'));
		$HEADER_DESCRIPTION = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_DESCRIPTION', 'These are resources'));
		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($HEADER_TITLE)
			->pageHeader()
			// following is for custom js operations on pages
			->addInlineJavascript('
				// add custom sub-tabs
				jQuery(".mytabs").tabs();
				jQuery("a.button").click(function() {
					var hash = jQuery(this).prop("hash");
					if (hash) {
						jQuery(".mytabs").tabs("option", "active", hash);
						window.location.reload(true);
						window.scrollBy({top: 500, left: 0, behavior: "smooth"});
					}
			    });

			    function returnTop() {
			    	jQuery("html, body").stop().animate({scrollTop: jQuery("html, body").offset().top});
			    }

				// add active_link class for breadcrumbs
				jQuery( "a.reveal_link" ).click(function() {
					jQuery("a.reveal_link").removeClass("active_link");
					jQuery(this).addClass("active_link");
					returnTop();
				});
				// add optional return to top button with class "reveal_button"
				jQuery( ".reveal_button" ).click(function() {
					returnTop();
				});
			');

		$items_id = safe_GET('pages_id');
		$items_list = $this->getPagesList();
		$count_items = 0;
		foreach ($items_list as $items) {
			$languages = get_block_setting($items->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
				$count_items = $count_items +1;
			}
		}
		?>
		<div id="pages-container">
            <div id="pages-summary">
    			<h2><?php echo $HEADER_TITLE; ?></h2>
    			<?php echo $HEADER_DESCRIPTION; ?>
            </div>
			<div class="clearfloat"></div>
			<div id="pages_tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
				<?php if ($count_items > 1) { ?>
					<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
						<?php
						foreach ($items_list as $items) {
							$languages = get_block_setting($items->block_id, 'languages');
							if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items->pages_access >= KT_USER_ACCESS_LEVEL) { ?>
								<li class="ui-state-default ui-corner-top<?php echo ($items_id==$items->block_id ? ' ui-tabs-selected ui-state-active' : ''); ?>">
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;pages_id=<?php echo $items->block_id; ?>" class="ui-tabs-anchor">
										<span title="<?php echo KT_I18N::translate($items->pages_title); ?>"><?php echo KT_I18N::translate($items->pages_title); ?></span>
									</a>
								</li>
							<?php }
						} ?>
					</ul>
				<?php } ?>
				<div id="outer_pages_container">
					<?php
					foreach ($items_list as $items) {
						$languages = get_block_setting($items->block_id, 'languages');
						if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $items_id==$items->block_id && $items->pages_access >= KT_USER_ACCESS_LEVEL) {
							$items_content = $items->pages_content;
						}
					}
					if (empty($items_content)) {
						$items_content = '<h4>' . KT_I18N::translate('No content') . '</h4>';
					} else {
					 echo $items_content;
				 	} ?>
				</div><!-- close outer_pages_container -->
			</div><!-- cclose pages_tabs -->
		</div><!-- cclose pages-container -->
	<?php }

	private function config() {
		require_once KT_ROOT . 'includes/functions/functions_edit.php';

		$controller=new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#pages_tabs").tabs();');

		if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
			ckeditor_KT_Module::enableEditor($controller);
		}

		$action = safe_POST('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'HEADER_TITLE',		safe_POST('NEW_HEADER_TITLE'));
			set_module_setting($this->getName(), 'HEADER_DESCRIPTION',	safe_POST('NEW_HEADER_DESCRIPTION', KT_REGEX_UNSAFE)); // allow html

			AddToLog($this->getName() . ' config updated', 'config');
		}

		$HEADER_TITLE			= get_module_setting($this->getName(), 'HEADER_TITLE', KT_I18N::translate('Resources'));
		$HEADER_DESCRIPTION		= get_module_setting($this->getName(), 'HEADER_DESCRIPTION', KT_I18N::translate('These are resources'));

		$items=KT_DB::prepare(
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_content".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_content'".
			" AND IFNULL(gedcom_id, ?)=?".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID, KT_GED_ID))->fetchAll();

		$min_block_order=KT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		$max_block_order=KT_DB::prepare(
			"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		echo'<div id="' . $this->getName() . '">';
//			<a class="current faq_link" href="' . KT_KIWITREES_URL . '/faqs/modules-faqs/pages/" target="_blank" rel="noopener noreferrer" title="'. KT_I18N::translate('View FAQ for this page.'). '">'. KT_I18N::translate('View FAQ for this page.'). '<i class="' . $iconStyle . ' fa-comments-o"></i></a>
			echo'<h2>' . $controller->getPageTitle() . '</h2>
			<div id="pages_tabs">
				<ul>
					<li><a href="#pages_summary"><span>', KT_I18N::translate('Summary'), '</span></a></li>
					<li><a href="#pages_pages"><span>', KT_I18N::translate('Pages'), '</span></a></li>
				</ul>
				<div id="pages_summary">
					<form method="post" name="configform" action="module.php?mod=' . $this->getName() . '&mod_action=admin_config">
					<input type="hidden" name="action" value="update">
					<div class="label">', KT_I18N::translate('Main menu and summary page title'), help_link('pages_title',$this->getName()),'</div>
					<div class="value"><input type="text" name="NEW_HEADER_TITLE" value="', $HEADER_TITLE, '"></div>
					<div class="label">', KT_I18N::translate('Summary page description'), help_link('pages_description',$this->getName()),'</div>
					<div class="value2">
						<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" rows="5" cols="120">', $HEADER_DESCRIPTION, '</textarea>
					</div>
					<div class="save"><input type="submit" value="', KT_I18N::translate('Save'), '"></div>
					</form>
				</div>
				<div id="pages_pages">
					<form method="get" action="', KT_SCRIPT_NAME ,'#pages_pages">',	KT_I18N::translate('Family tree'), '
						<input type="hidden" name="mod", value="', $this->getName(), '">
						<input type="hidden" name="mod_action", value="admin_config">',
						select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM), '
						<input type="submit" value="', KT_I18N::translate('show'), '">
					</form>
					<div class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only"><a class="ui-button-text" href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_edit">', KT_I18N::translate('Add page'), '</a></div>
					<table id="pages_module">';
						if ($items) {
							$trees=KT_Tree::getAll();
							foreach ($items as $item) {
								echo'<tr class="faq_edit_pos">
									<td>',
										KT_I18N::translate('Position item'), ': ', $item->block_order, ', ';
										if ($item->gedcom_id==null) {
											echo KT_I18N::translate('All');
										} else {
											echo $trees[$item->gedcom_id]->tree_title_html;
										}
									echo '</td>
									<td>';
										if ($item->block_order==$min_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_moveup&amp;block_id=', $item->block_id, ' "class="icon-uarrow"></a>';
										}
									echo '</td>
									<td>';
										if ($item->block_order==$max_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_movedown&amp;block_id=', $item->block_id, ' "class="icon-darrow"></a>';
										}
									echo '</td>
									<td>
										<a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_edit&amp;block_id=', $item->block_id, '">', KT_I18N::translate('Edit'), '</a>
									</td>
									<td>
										<a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_delete&amp;block_id=', $item->block_id, '" onclick="return confirm(\'', KT_I18N::translate('Are you sure you want to delete this page?'), '\');">', KT_I18N::translate('Delete'), '</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="faq_edit_title">', KT_I18N::translate($item->pages_title), '</div>
											<div class="faq_edit_content">', substr($item->pages_content, 0, 1)=='<' ? $item->pages_content : nl2br($item->pages_content), '</div>
										</div>
									</td>
								</tr>';
							}
						} else {
							echo '<tr><td class="error center" colspan="5">', KT_I18N::translate('No pages have been created'), '</td></tr></table>';
						}
					echo '</table>';
				echo '</div>
			</div>
		</div>';
	}

	// Return the list of pages
	private function getPagesList() {
		return KT_DB::prepare(
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
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

	// Return the list of pages for menu
	private function getMenupagesList() {
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS pages_title, bs2.setting_value AS pages_access".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='pages_title'".
			" AND bs2.setting_name='pages_access'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

}
