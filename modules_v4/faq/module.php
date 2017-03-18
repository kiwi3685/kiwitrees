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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class faq_WT_Module extends WT_Module implements WT_Module_Menu, WT_Module_Block, WT_Module_Config {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Abbreviation for “Frequently Asked Questions” */ WT_I18N::translate('FAQs');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “FAQ” module */ WT_I18N::translate('A list of frequently asked questions and answers.');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 130;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_NONE;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
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

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
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
		$default_title = WT_I18N::translate('FAQs');
		$HEADER_TITLE = WT_I18N::translate(get_module_setting($this->getName(), 'FAQ_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getSummaryDescription() {
		$default_description = '';
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'FAQ_DESCRIPTION', $default_description);
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
				set_block_setting($block_id, 'header',  safe_POST('header',  WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'faqbody', safe_POST('faqbody', WT_REGEX_UNSAFE)); // allow html
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
					$controller->setPageTitle(WT_I18N::translate('Edit FAQ item'));
					$header		 = get_block_setting($block_id, 'header');
					$faqbody	 = get_block_setting($block_id, 'faqbody');
					$block_order = WT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id	= WT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(WT_I18N::translate('Add FAQ item'));
					$header		 = '';
					$faqbody	 = '';
					$block_order = WT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id = WT_GED_ID;
				}
				$controller->pageHeader();
				if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
					ckeditor_WT_Module::enableEditor($controller);
				}
				?>
				<div id="<?php echo $this->getName(); ?>">
					<form name="faq" method="post" action="#">
						<?php echo WT_Filter::getCsrf(); ?>
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
						<table id="faq_module">
							<tr>
								<th><?php echo WT_I18N::translate('Question'); ?></th>
							</tr>
							<tr>
								<td><input type="text" name="header" size="90" tabindex="1" value="<?php echo htmlspecialchars($header); ?>"></td>
							</tr>
							<tr>
								<th><?php echo WT_I18N::translate('Answer'); ?></th>
							</tr>
							<tr>
									<td>
										<textarea name="faqbody" class="html-edit" rows="10" cols="90" tabindex="2"><?php echo htmlspecialchars($faqbody); ?></textarea>
									</td>
							</tr>
						</table>
						<table id="faq_module2">
							<tr>
								<th><?php echo WT_I18N::translate('Show this block for which languages?'); ?></th>
								<th><?php echo WT_I18N::translate('FAQ position'); ?></th>
								<th><?php echo WT_I18N::translate('FAQ visibility'); ?></th>
							</tr>
							<tr>
								<td>
									<?php
									$languages = get_block_setting($block_id, 'languages');
									echo edit_language_checkboxes('lang_', $languages);
									?>
								</td>
								<td>
									<input type="text" name="block_order" size="3" tabindex="3" value="<?php echo $block_order; ?>">
								</td>
								<td>
									<?php echo select_edit_control('gedcom_id', WT_Tree::getIdList(), WT_I18N::translate('All'), $gedcom_id, 'tabindex="4"'); ?>
								</td>
							</tr>
						</table>
						<p>
							<button class="btn btn-primary save" type="submit"  tabindex="5">
								<i class="fa fa-floppy-o"></i>
								<?php echo WT_I18N::translate('save'); ?>
							</button>
							<button class="btn btn-primary cancel" type="button" onclick="window.location='<?php echo $this->getConfigLink(); ?>';" tabindex="8">
								<i class="fa fa-times"></i>
								<?php echo WT_I18N::translate('cancel'); ?>
							</button>
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
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery("#faq_accordion").accordion({heightStyle: "content", collapsible: true, active: false});
				jQuery("#faq_accordion").css("visibility", "visible");
				jQuery(".faq_subaccordion").accordion({heightStyle: "content", collapsible: true, active: false});
				jQuery(".faq_subaccordion").css("visibility", "visible");
			');
			/* Use a structure like <div id="faq_subaccordion"><h2>Your sub-level title<h2><p>Your sub-level content</p></div> inside any FAQ page to create sub-levels within that FAQ */

		if (safe_POST('query_faq')) {
			$search = safe_POST('query_faq');
		} else {
			$search = '%';
		};

		$faqs = WT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS header, bs2.setting_value AS body".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='header'".
			" AND bs2.setting_name='faqbody'".
			" AND IFNULL(gedcom_id, ?)=?".
			" AND (bs2.setting_value LIKE '%" . $search . "%' OR bs1.setting_value LIKE '%" . $search . "%')".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID, WT_GED_ID))->fetchAll();

		$faq_description = get_module_setting($this->getName(), 'FAQ_DESCRIPTION');
		?>

		<div id="faq-page">
			<h2> <?php echo WT_I18N::translate('Frequently asked questions'); ?></h2>
			<div id="faq_description">
				<?php echo $faq_description; ?>
			</div>
			<div id="faq_search">
				<form method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show" >
					<input
						type="search"
						name="query_faq"
						value="<?php echo ($search == '%' ? '' : $search); ?>"
						placeholder="<?php echo WT_I18N::translate('Search FAQs'); ?>"
						dir="auto"
					>
				</form>
			</div>
			<div id="faq_accordion" style="visibility:hidden">
				<?php foreach ($faqs as $id => $faq) {
					$header		= get_block_setting($faq->block_id, 'header');
					$faqbody	= get_block_setting($faq->block_id, 'faqbody');
					$languages	= get_block_setting($faq->block_id, 'languages');
					if (!$languages || in_array(WT_LOCALE, explode(',', $languages))) { ?>
						<h2><?php echo $this->faq_search_hits($faq->header, $search); ?></h2>
						<div class="faq_body"> <?php echo $this->faq_search_hits(substr($faqbody, 0, 1)=='<' ? $faqbody : nl2br($faqbody), $search); ?> </div>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private function config() {
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Page();
		$controller
			->restrictAccess(WT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#faq_tabs").tabs();');

		if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
			ckeditor_WT_Module::enableEditor($controller);
		}

		$action = safe_POST('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'FAQ_TITLE',		safe_POST('NEW_FAQ_TITLE'));
			set_module_setting($this->getName(), 'FAQ_DESCRIPTION',	safe_POST('NEW_FAQ_DESCRIPTION', WT_REGEX_UNSAFE)); // allow html
			AddToLog($this->getName() . ' config updated', 'config');
		}

		$faqs = WT_DB::prepare(
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS header, bs2.setting_value AS faqbody".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='header'".
			" AND bs2.setting_name='faqbody'".
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

		<div id="<?php echo $this->getName(); ?>">
<!--		<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/pages/" target="_blank" rel="noopener noreferrer" title="'. WT_I18N::translate('View FAQ for this page.'). '">'. WT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a> -->
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<div id="faq_tabs">
				<ul>
					<li><a href="#faq_summary"><span><?php echo WT_I18N::translate('Summary'); ?></span></a></li>
					<li><a href="#faq_items"><span><?php echo WT_I18N::translate('FAQs'); ?></span></a></li>
				</ul>
				<div id="faq_summary">
					<form method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
						<input type="hidden" name="action" value="update">
						<div class="label"><?php echo WT_I18N::translate('Main menu and page title'); ?></div>
						<div class="value"><input type="text" name="NEW_FAQ_TITLE" value="<?php echo $this->getMenuTitle(); ?>"></div>
						<div class="label"><?php echo WT_I18N::translate('Page description'); ?></div>
						<div class="value2">
							<textarea name="NEW_FAQ_DESCRIPTION" class="html-edit" rows="5" cols="120"><?php echo $this->getSummaryDescription(); ?></textarea>
						</div>
						<div class="save">
							<button class="btn btn-primary save" type="submit">
								<i class="fa fa-floppy-o"></i>
								<?php echo WT_I18N::translate('save'); ?>
							</button>
						</div>
					</form>
				</div>
				<div id="faq_items">
					<form method="get" action="<?php echo WT_SCRIPT_NAME; ?>">
						<label><?php echo WT_I18N::translate('Family tree'); ?></label>
						<input type="hidden" name="mod" value="<?php echo $this->getName(); ?>">
						<input type="hidden" name="mod_action" value="admin_config">
						<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
						<button class="btn btn-primary show" type="submit" tabindex="5">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
					<div>
						<button class="btn btn-primary add" onclick="location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit'">
							<i class="fa fa-plus"></i>
							<?php echo WT_I18N::translate('Add FAQ item'); ?>
						</button>
					</div>
					<table id="faq_edit">
						<?php
						if ($faqs) {
							$trees = WT_Tree::getAll();
							foreach ($faqs as $faq) { ?>
								<tr class="faq_edit_pos">
									<td>
										<?php
										echo '<p>' . WT_I18N::translate('FAQ position') . '<span>' . ($faq->block_order) . '</span></p>';
										echo '<p>' . WT_I18N::translate('Family tree');
											if ($faq->gedcom_id == null) {
												echo '<span>' . WT_I18N::translate('All') . '</span>';
											} else {
												echo '<span>' . $trees[$faq->gedcom_id]->tree_title_html . '</span>';
											}
										echo '</p>';
										?>
									</td>
									<td>
										<?php
										if ($faq->block_order == $min_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_moveup&amp;block_id=' . $faq->block_id . '" class="icon-uarrow"></a>';
										}
										?>
									</td>
									<td>
										<?php
										if ($faq->block_order == $max_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_movedown&amp;block_id=' . $faq->block_id . '" class="icon-darrow"></a>';
										}
										?>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $faq->block_id; ?>">
											<?php echo WT_I18N::translate('Edit'); ?>
										</a>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $faq->block_id; ?>" onclick="return confirm('<?php echo WT_I18N::translate('Are you sure you want to delete this FAQ entry?'); ?>');">
											<?php echo WT_I18N::translate('Delete'); ?>
										</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="faq_edit_title"><?php echo $faq->header; ?></div>
											<div class="faq_edit_content"><?php echo substr($faq->faqbody, 0, 1)=='<' ? $faq->faqbody : nl2br($faq->faqbody); ?></div>
										</div>
									</td>
								</tr>
							<?php }
						} else { ?>
							<tr>
								<td class="error center" colspan="5">
									<?php echo WT_I18N::translate('The FAQ list is empty.'); ?>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$faqs = WT_DB::prepare(
			"SELECT block_id FROM `##block` b WHERE module_name=? AND IFNULL(gedcom_id, ?)=?"
		)->execute(array($this->getName(), WT_GED_ID, WT_GED_ID))->fetchAll();

		if (!$faqs) {
			return null;
		}

		$menu = new WT_Menu($this->getMenuTitle(), 'module.php?mod=faq&amp;mod_action=show', 'menu-help');
		if (WT_USER_IS_ADMIN) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit FAQ items'), $this->getConfigLink(), 'menu-faq-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	function faq_search_hits($string, $search) {
		if ($search != '%') {
			return preg_replace('/' . $search . '/i', '<span class="search_hit">$0</span>', $string);
		} else {
			return $string;
		}
	}

}
