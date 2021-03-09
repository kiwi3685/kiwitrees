<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class gallery_KT_Module extends KT_Module implements KT_Module_Menu, KT_Module_Block, KT_Module_Config {

	// Extend class KT_Module
	public function getTitle() {
		return KT_I18N::translate('Gallery');
	}

	public function getMenuTitle() {
		$default_title = KT_I18N::translate('Gallery');
		$HEADER_TITLE = KT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getSummaryDescription() {
		$default_description = KT_I18N::translate('These are galleries');
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
		return $HEADER_DESCRIPTION;
	}

	// Extend class KT_Module
	public function getDescription() {
		return KT_I18N::translate('Display image galleries.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 100;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_NONE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
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
	public function configureBlock($block_id) {
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return false;
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
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;

		$block_id=safe_GET('block_id');
		$default_block=KT_DB::prepare(
			"SELECT block_id FROM `##block` WHERE block_order=? AND module_name=?"
		)->execute(array(0, $this->getName()))->fetchOne();

		if ($SEARCH_SPIDER) {
			return null;
		}

		//-- main GALLERIES menu item
		$menu = new KT_Menu($this->getMenuTitle(), 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;gallery_id='.$default_block, 'menu-my_gallery', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');
		foreach ($this->getMenuAlbumList() as $item) {
			$languages=get_block_setting($item->block_id, 'languages');
			if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item->gallery_access>=KT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;gallery_id='.$item->block_id;
				$submenu = new KT_Menu(KT_I18N::translate($item->gallery_title), $path, 'menu-my_gallery-'.$item->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (KT_USER_IS_ADMIN) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit gallerys'), $this->getConfigLink(), 'menu-my_gallery-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	// Action from the configuration page
	private function edit() {
		if (KT_USER_IS_ADMIN) {
			global $MEDIA_DIRECTORY;
			require_once KT_ROOT.'includes/functions/functions_edit.php';

			if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
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
				set_block_setting($block_id, 'gallery_title',		safe_POST('gallery_title',		KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'gallery_description', safe_POST('gallery_description',KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'gallery_folder_w',	safe_POST('gallery_folder_w',	KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'gallery_folder_f',	safe_POST('gallery_folder_f',	KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'gallery_access',	 	safe_POST('gallery_access',		KT_REGEX_UNSAFE));
				set_block_setting($block_id, 'plugin',			 	safe_POST('plugin',				KT_REGEX_UNSAFE));
				$languages=array();
				foreach (KT_I18N::used_languages() as $code=>$name) {
					if (safe_POST_bool('lang_'.$code)) {
						$languages[]=$code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id	= safe_GET('block_id');
				$controller	= new KT_Controller_Page();
				if ($block_id) {
					$item_title=get_block_setting($block_id, 'gallery_title');
					$item_description=get_block_setting($block_id, 'gallery_description');
					$item_folder_w=get_block_setting($block_id, 'gallery_folder_w');
					$item_folder_f=get_block_setting($block_id, 'gallery_folder_f');
					$item_access=get_block_setting($block_id, 'gallery_access');
					$plugin=get_block_setting($block_id, 'plugin');
					$block_order=KT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id=KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$item_title='';
					$item_description='';
					$item_folder_w=$MEDIA_DIRECTORY;
					$item_folder_f='';
					$item_access=1;
					$plugin='kiwitrees';
					$block_order=KT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id=KT_GED_ID;
				}
				$controller
					->pageHeader()
					->addInlineJavaScript('
						function hide_fields(){
							if (jQuery("#kiwitrees-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","visible");
								jQuery("#flickr-div .vis").css("visibility","hidden");
								jQuery("#googlephotos-div .vis").css("visibility","hidden");
							}
							else if (jQuery("#flickr-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","hidden");
								jQuery("#flickr-div .vis").css("visibility","visible");
								jQuery("#googlephotos-div .vis").css("visibility","hidden");
							}
							else if (jQuery("#googlephotos-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","hidden");
								jQuery("#flickr-div .vis").css("visibility","hidden");
								jQuery("#googlephotos-div .vis").css("visibility","visible");
							}
						};
					');

				if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
					ckeditor_KT_Module::enableEditor($controller);
				}
				?>
				<div id="<?php echo $this->getName();?>">
					<form name="<?php echo $this->getName(); ?>" method="post" action="#">
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
						<?php echo KT_Filter::getCsrf(); ?>
						<table id="faq_module">
							<tr><th><?php echo KT_I18N::translate('Title'); ?></th></tr>
							<tr><td><input type="text" name="gallery_title" size="90" tabindex="1" value="<?php echo htmlspecialchars($item_title); ?>"></td></tr>
							<tr><th><?php echo KT_I18N::translate('Description'); ?></th></tr>
							<tr><td>
								<textarea name="gallery_description" class="html-edit" rows="10" cols="90" tabindex="2"><?php echo htmlspecialchars($item_description); ?></textarea>
							</td></tr>
							<tr><th><?php echo KT_I18N::translate('Source'); ?></th></tr>
							<tr>
								<td>
									<div id="kiwitrees-div">
										<p>
											<?php
											echo '<input id="kiwitrees-radio" type="radio" name="plugin" value="kiwitrees"';
											if ($plugin == 'kiwitrees') {
												echo ' checked';
											}
											echo ' onclick="hide_fields();">', KT_I18N::translate('kiwitrees');
											?>
										</p>
										<?php
										echo '<label class="vis"';
											if ($plugin == 'kiwitrees') {
												echo ' style="visibility:visible;">';
											} else {
												echo ' style="visibility:hidden;">';
											}
											echo KT_I18N::translate('Folder name on server');
											echo select_edit_control("gallery_folder_w", KT_Query_Media::folderList(), null, htmlspecialchars($item_folder_w));
										echo '</label>';
										?>
									</div>
									<div id="flickr-div">
										<p>
											<?php
											echo '<input id="flickr-radio" type="radio" name="plugin" value="flickr"';
											if ($plugin == 'flickr') {
												echo ' checked';
											}
											echo ' onclick="hide_fields();">' . KT_I18N::translate('Flickr');
											?>
										</p>
										<?php
										echo '<label class="vis"';
											if ($plugin == 'flickr') {
												echo ' style="visibility:visible;">';
											} else {
												echo ' style="visibility:hidden;">';
											}
											echo KT_I18N::translate('Flickr set number');
											echo '<input id="flickr" type="text" name="gallery_folder_f" tabindex="1" value="' . htmlspecialchars($item_folder_f) . '">';
										echo '</label>';
										?>
									</div>
								</td>
							</tr>
						</table>
						<table id="gallery_module">
							<tr>
								<th><?php echo KT_I18N::translate('Show this gallery for which languages?'); ?></th>
								<th><?php echo KT_I18N::translate('Gallery position'); ?></th>
								<th><?php echo KT_I18N::translate('Gallery visibility'); ?></th>
								<th><?php echo KT_I18N::translate('Access level'); ?></th>
							</tr>
							<tr>
								<td>
									<?php
									$languages = get_block_setting($block_id, 'languages');
									echo edit_language_checkboxes('lang_', $languages);
									?>
								</td>
								<td>
									<input type="text" name="block_order" size="3" tabindex="5" value="<?php echo $block_order; ?>"></td>
								</td>
								<td>
									<?php echo select_edit_control('gedcom_id', KT_Tree::getIdList(), '', $gedcom_id, 'tabindex="4"'); ?>
								</td>
								<td>
									<?php echo edit_field_access_level('gallery_access', $item_access, 'tabindex="4"'); ?>
								</td>
							</tr>
						</table>
						<p class="save">
							<button class="btn btn-primary save" type="submit"  tabindex="7">
								<i class="fa fa-floppy-o"></i>
								<?php echo KT_I18N::translate('Save'); ?>
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
			"SELECT block_order, block_id
			FROM `##block`
			WHERE block_order=(
			 SELECT MAX(block_order) FROM `##block` WHERE block_order < ? AND module_name=?
			) AND module_name=?
			LIMIT 1"
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
			"SELECT block_order, block_id
			FROM `##block`
			WHERE block_order=(
			 SELECT MIN(block_order) FROM `##block` WHERE block_order>? AND module_name=?
			) AND module_name=?
			LIMIT 1"
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
		$controller = new KT_Controller_Page;
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#gallery_tabs").tabs();');

		if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
			ckeditor_KT_Module::enableEditor($controller);
		}

		$action = KT_Filter::post('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'HEADER_TITLE',		safe_POST('NEW_HEADER_TITLE'));
			set_module_setting($this->getName(), 'HEADER_DESCRIPTION',	safe_POST('NEW_HEADER_DESCRIPTION', KT_REGEX_UNSAFE)); // allow html
			set_module_setting($this->getName(), 'THEME_DIR',			safe_POST('NEW_THEME_DIR'));
			AddToLog($this->getName() . 'config updated', 'config');
		}

		$current_themedir	= get_module_setting($this->getName(), 'THEME_DIR', 'classic');
		$themenames			= $this->galleria_theme_names();

		$items = KT_DB::prepare(
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_description
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			WHERE module_name=?
			AND bs1.setting_name='gallery_title'
			AND bs2.setting_name='gallery_description'
			AND IFNULL(gedcom_id, ?)=?
			ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID, KT_GED_ID))->fetchAll();

		$min_block_order=KT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		$max_block_order=KT_DB::prepare(
			"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();
		?>
		<div id="<?php echo $this->getName();?>">
			<a class="current faq_link" href="<?php echo KT_KIWITREES_URL; ?>/faqs/modules/gallery/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<div id="gallery_tabs">
				<ul>
					<li><a href="#gallery_summary"><span><?php echo KT_I18N::translate('Summary'); ?></span></a></li>
					<li><a href="#gallery_pages"><span><?php echo KT_I18N::translate('Galleries'); ?></span></a></li>
				</ul>
				<div id="gallery_summary">
					<form method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
						<input type="hidden" name="action" value="update">
						<div class="label"><?php echo KT_I18N::translate('Main menu and summary page title'); ?></div>
						<div class="value"><input type="text" name="NEW_HEADER_TITLE" value="<?php echo $this->getMenuTitle(); ?>"></div>
						<div class="label"><?php echo KT_I18N::translate('Summary page description'); ?></div>
						<div class="value2">
							<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" rows="5" cols="120"><?php echo $this->getSummaryDescription(); ?></textarea>
						</div>
						<div id="gallery_theme">
							<div class="label"><?php echo KT_I18N::translate('Select gallery theme'); ?></div>
							<?php
							foreach ($themenames as $themedir) {
								echo
									'<div ', ($current_themedir == $themedir ? 'class = "current_theme"' : 'class = "theme_box"'), '>
											<img src="', KT_MODULES_DIR , $this->getName(), '/images/' , $themedir, '.png" alt="', $themedir, ' title="' ,$themedir, '">
										<p>
											<input type="radio" id="radio_', $themedir, '" name="NEW_THEME_DIR" value="', $themedir, '" ',($current_themedir == $themedir ? ' checked="checked"' : ''), '/>
											<label for="radio_', $themedir, '">', $themedir, '</label>
										</p>
									</div>
								';
							}
							?>
						</div>
						<div style="clear:both;"></div>
						<button class="btn btn-primary save" type="submit">
							<i class="fa fa-floppy-o"></i>
							<?php echo KT_I18N::translate('Save'); ?>
						</button>
					</form>
				</div>
				<div id="gallery_pages">
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
							<?php echo KT_I18N::translate('Add gallery'); ?>
						</button>
					</div>
					<table id="gallery_module">
						<?php
						if ($items) {
							$trees = KT_Tree::getAll();
							foreach ($items as $gallery) { ?>
								<tr class="gallery_edit_pos">
									<td>
										<?php
										echo '<p>' . KT_I18N::translate('Gallery position') . '<span>' . ($gallery->block_order) . '</span></p>';
										echo '<p>' . KT_I18N::translate('Family tree');
											if ($gallery->gedcom_id == null) {
												echo '<span>' . KT_I18N::translate('All') . '</span>';
									} else {
												echo '<span>' . $trees[$gallery->gedcom_id]->tree_title_html . '</span>';
											}
										echo '</p>';
										?>
									</td>
									<td>
										<?php
										if ($gallery->block_order == $min_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_moveup&amp;block_id=' . $gallery->block_id . '" class="icon-uarrow"></a>';
										}
										?>
									</td>
									<td>
										<?php
										if ($gallery->block_order == $max_block_order) {
											echo '&nbsp;';
										} else {
											echo '<a href="module.php?mod=' . $this->getName() . '&amp;mod_action=admin_movedown&amp;block_id=' . $gallery->block_id . '" class="icon-darrow"></a>';
										}
										?>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $gallery->block_id; ?>">
											<?php echo KT_I18N::translate('Edit');?>
										</a>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $gallery->block_id; ?>" onclick="return confirm('<?php echo KT_I18N::translate('Are you sure you want to delete this menu item?'); ?>');">
											<?php echo KT_I18N::translate('Delete');?>
										</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="faq_edit_title"><?php echo KT_I18N::translate($gallery->gallery_title);?></div>
											<div><?php echo substr($gallery->gallery_description, 0, 1)=='<' ? $gallery->gallery_description : nl2br($gallery->gallery_description);?></div>
										</div>
									</td>
								</tr>
								<?php }
							} else { ?>
								<tr>
									<td class="error center" colspan="5">
										<?php echo KT_I18N::translate('No pages have been created');?>
									</td>
								</tr>
							<?php } ?>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	private function getJavaScript($item_id) {
		$theme	= get_module_setting($this->getName(), 'THEME_DIR', 'classic');
		$plugin	= get_block_setting($item_id, 'plugin');

		$js		= 'Galleria.loadTheme("' . KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/themes/' . $theme . '/galleria.' . $theme . '.min.js");';
		switch ($plugin) {
			case 'flickr':
			$flickr_set = get_block_setting($item_id, 'gallery_folder_f');
			$js	.= '
				Galleria.run("#galleria", {
					flickr: "set:' . $flickr_set . '",
					flickrOptions: {
						sort: "date-posted-asc",
						description: true,
						imageSize: "original"
					},
					_showCaption: false,
					imageCrop: false
				});
			';
			break;
			default:
			$js	.='
				Galleria.ready(function(options) {
					this.bind("image", function(e) {
						data = e.galleriaData;
						jQuery("#links_bar").html(data.layer);
					});
				});
				Galleria.run("#galleria", {
					imageCrop: false,
					_showCaption: false,
					_locale: {
						show_captions:		"' . KT_I18N::translate('Show descriptions') . '",
						hide_captions:		"' . KT_I18N::translate('Hide descriptions') . '",
						play:				"' . KT_I18N::translate('Play slideshow') . '",
						pause:				"' . KT_I18N::translate('Pause slideshow') . '",
						enter_fullscreen:	"' . KT_I18N::translate('Enter fullscreen') . '",
						exit_fullscreen:	"' . KT_I18N::translate('Exit fullscreen') . '",
						next:				"' . KT_I18N::translate('Next image') . '",
						prev:				"' . KT_I18N::translate('Previous image') . '",
						showing_image:		"" // counter not compatible with I18N of kiwitrees
					}
				});
			';
			break;
		}
		return $js;
	}

	// Return the list of gallerys
	private function getAlbumList() {
		return KT_DB::prepare(
			"SELECT block_id,
				bs1.setting_value AS gallery_title,
				bs2.setting_value AS gallery_access,
				bs3.setting_value AS gallery_description,
				bs4.setting_value AS gallery_folder_w,
				bs5.setting_value AS gallery_folder_f
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			JOIN `##block_setting` bs3 USING (block_id)
			JOIN `##block_setting` bs4 USING (block_id)
			JOIN `##block_setting` bs5 USING (block_id)
			WHERE module_name=?
			AND bs1.setting_name='gallery_title'
			AND bs2.setting_name='gallery_access'
			AND bs3.setting_name='gallery_description'
			AND bs4.setting_name='gallery_folder_w'
			AND bs5.setting_name='gallery_folder_f'
			AND (gedcom_id IS NULL OR gedcom_id=?)
			ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

	// Return the list of gallerys for menu
	private function getMenuAlbumList() {
		return KT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_access
			FROM `##block` b
			JOIN `##block_setting` bs1 USING (block_id)
			JOIN `##block_setting` bs2 USING (block_id)
			WHERE module_name=?
			AND bs1.setting_name='gallery_title'
			AND bs2.setting_name='gallery_access'
			AND (gedcom_id IS NULL OR gedcom_id=?)
			ORDER BY block_order"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();
	}

	// Print the Notes for each media item
	static function FormatGalleryNotes($haystack) {
		$needle   = '1 NOTE';
		$before   = substr($haystack, 0, strpos($haystack, $needle));
		$after    = substr(strstr($haystack, $needle), strlen($needle));
		$final    = $before.$needle.$after;
		$notes    = print_fact_notes($final, 1, true, true);
		if ($notes !='' && $notes != '<br>') {
			$html = htmlspecialchars($notes);
			return $html;
		}
		return false;
	}

	// Start to show the gallery display with the parts common to all galleries
	private function show() {
		global $MEDIA_DIRECTORY, $controller;
		$item_id	= KT_Filter::get('gallery_id');
		$version	= '1.6.1';
		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader()
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/galleria.min.js')
			->addExternalJavaScript(KT_STATIC_URL . KT_MODULES_DIR . $this->getName() . '/galleria/plugins/flickr/galleria.flickr.min.js')
			->addInlineJavaScript($this->getJavaScript($item_id));
		?>

		<div id="gallery-page">
			<div id="gallery-container">
				<h2><?php echo $controller->getPageTitle(); ?></h2>
				<p><?php echo $this->getSummaryDescription(); ?></p>
				<div class="clearfloat"></div>
				<div id="gallery_tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
					<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
						<?php
						$item_list = $this->getAlbumList();
						foreach ($item_list as $item) {
							$languages=get_block_setting($item->block_id, 'languages');
							if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item->gallery_access >= KT_USER_ACCESS_LEVEL) { ?>
								<li class="ui-state-default ui-corner-top<?php echo ($item_id == $item->block_id ? ' ui-tabs-selected ui-state-active' : ''); ?>">
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;gallery_id=<?php echo $item->block_id; ?>" class="ui-tabs-anchor">
										<span title="<?php echo KT_I18N::translate($item->gallery_title); ?>"><?php echo KT_I18N::translate($item->gallery_title); ?></span>
									</a>
								</li>
							<?php }
						} ?>
					</ul>
					<div id="outer_gallery_container">
						<?php
						foreach ($item_list as $item) {
							$languages = get_block_setting($item->block_id, 'languages');
							if ((!$languages || in_array(KT_LOCALE, explode(',', $languages))) && $item_id==$item->block_id && $item->gallery_access>=KT_USER_ACCESS_LEVEL) {
								$item_gallery = '
									<h4>' . KT_I18N::translate($item->gallery_description) . '</h4>' .
									$this->mediaDisplay($item->gallery_folder_w, $item_id, $version);
							}
						}
						if (!isset($item_gallery)) {
							echo '<h4>' . KT_I18N::translate('Image collections related to our family') . '</h4>' .
								$this->mediaDisplay('//', $item_id, $version);
						} else {
							echo $item_gallery;
						} ?>
					</div><!-- close #outer_gallery_container -->
				</div><!-- close #gallery_tabs -->
			</div><!-- close #gallery-container -->
		</div><!-- close #gallery-page -->
	<?php }

	// Print the gallery display
	private function mediaDisplay($sub_folder, $item_id, $version) {
		global $MEDIA_DIRECTORY;
		$plugin			= get_block_setting($item_id, 'plugin');
		$images			= '';
		$media_links 	= '';
		// Get the related media items
		$sub_folder	= str_replace($MEDIA_DIRECTORY, "",$sub_folder);
		$sql		= "SELECT * FROM ##media WHERE m_filename LIKE '%" . $sub_folder . "%' ORDER BY m_filename";
		$rows		= KT_DB::prepare($sql)->execute()->fetchAll(PDO::FETCH_ASSOC);
		if ($plugin == 'kiwitrees') {
			foreach ($rows as $rowm) {
				// Get info on how to handle this media file
				$media	= KT_Media::getInstance($rowm['m_id']);
				if ($media->canDisplayDetails()) {
					$links = array_merge(
						$media->fetchLinkedIndividuals(),
						$media->fetchLinkedFamilies(),
						$media->fetchLinkedSources()
					);
					$rawTitle = $rowm['m_titl'];
					if (empty($rawTitle)) $rawTitle = get_gedcom_value('TITL', 2, $rowm['m_gedcom']);
					if (empty($rawTitle)) $rawTitle = basename($rowm['m_filename']);
					$mediaTitle		= htmlspecialchars(strip_tags($rawTitle));
					$rawUrl			= $media->getHtmlUrlDirect();
					$thumbUrl		= $media->getHtmlUrlDirect('thumb');
					$media_notes	= $this->FormatGalleryNotes($rowm['m_gedcom']);
					$mime_type		= $media->mimeType();
					$gallery_links	= '';
					if (KT_USER_CAN_EDIT) {
						$gallery_links .= '<div class="edit_links">';
							$gallery_links .= '<div class="image_option"><a href="'. $media->getHtmlUrl(). '"><img src="'.KT_THEME_URL.'images/edit.png" title="' . KT_I18N::translate('Edit').'"></a></div>';
							if (KT_USER_GEDCOM_ADMIN) {
								if (array_key_exists('census_assistant', KT_Module::getActiveModules())) {
									$gallery_links.='<div class="image_option"><a href="inverselink.php?mediaid=' . $rowm['m_id'] . '&linkto=manage&ged=' . KT_GEDCOM . '" target="_blank" rel="noopener noreferrer"><img src="' . KT_THEME_URL . 'images/link.png" title="' . KT_I18N::translate('Manage links') . '"></a></div>';
								}
							}
						$gallery_links .= '</div><hr>';// close .edit_links
					}
					if ($links) {
						$gallery_links .= '<h4>'.KT_I18N::translate('Linked to:').'</h4>';
						$gallery_links .= '<div id="image_links">';
							foreach ($links as $record) {
									$gallery_links .= '<a href="' . $record->getHtmlUrl() . '">' . $record->getFullname().'</a><br>';
							}
						$gallery_links .= '</div>';
					}
					$media_links = htmlspecialchars($gallery_links);
					if ($mime_type == 'application/pdf'){
						$images .= '<a href="' . $rawUrl . '"><img class="iframe" src="' . $thumbUrl . '" data-title="' . $mediaTitle.'" data-layer="' . $media_links.'" data-description="' . $media_notes . '"></a>';
					} else {
						$images .= '<a href="' . $rawUrl . '"><img src="'.$thumbUrl.'" data-title="' .$mediaTitle . '" data-layer="' . $media_links . '" data-description="' . $media_notes . '"></a>';
					}
				}
			}
			if (KT_USER_CAN_ACCESS || $media_links != '') {
				$html =
					'<div id="links_bar"></div>'.
					'<div id="galleria" style="width:80%;">';
			} else {
				$html =
					'<div id="galleria" style="width:100%;">';
			}
		} else {
			$html = '<div id="galleria" style="width:100%;">';
			$images .= '&nbsp;';
		}
		if ($images) {
			$html .= $images.
				'</div>'.// close #galleria
				'<a id="copy" href="https://galleriajs.github.io/" target="_blank" rel="noopener noreferrer">' . /* I18N: Copyright statement in gallery module */ KT_I18N::translate('Display by Galleria (%1s)', $version) . '</a>'.// gallery.io attribution
				'</div>'.// close #page
				'<div style="clear: both;"></div>';
		} else {
			$html .= KT_I18N::translate('Gallery is empty. Please choose other gallery.').
				'</div>'. // close #galleria
				'</div>'. // close #page
				'<div style="clear: both;"></div>';
		}
		return $html;
	}

	// Get galleria themes list
	private function galleria_theme_names() {
		$themes = array();
		$d = dir(KT_MODULES_DIR.$this->getName(). '/galleria/themes/');
		while (false !== ($folder = $d->read())) {
			if ($folder[0] != '.' && $folder[0] != '_' && is_dir(KT_MODULES_DIR . $this->getName() . '/galleria/themes/' . $folder)) {
				$themes[] = $folder;
			}
		}
		$d->close();
		return $themes;
	}

}
