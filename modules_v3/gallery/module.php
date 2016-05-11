<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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

class gallery_WT_Module extends WT_Module implements WT_Module_Menu, WT_Module_Block, WT_Module_Config {

	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Gallery');
	}

	public function getMenuTitle() {
		$default_title = WT_I18N::translate('Gallery');
		$HEADER_TITLE = WT_I18N::translate(get_module_setting($this->getName(), 'HEADER_TITLE', $default_title));
		return $HEADER_TITLE;
	}

	public function getSummaryDescription() {
		$default_description = WT_I18N::translate('These are galleries');
		$HEADER_DESCRIPTION = get_module_setting($this->getName(), 'HEADER_DESCRIPTION', $default_description);
		return $HEADER_DESCRIPTION;
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Display image galleries.');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 100;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_NONE;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
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

	// Extend WT_Module
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

	// Implement WT_Module_Menu
	public function getMenu() {
		global $controller, $SEARCH_SPIDER;

		$block_id=safe_GET('block_id');
		$default_block=WT_DB::prepare(
			"SELECT block_id FROM `##block` WHERE block_order=? AND module_name=?"
		)->execute(array(0, $this->getName()))->fetchOne();

		if ($SEARCH_SPIDER) {
			return null;
		}

		//-- main GALLERIES menu item
		$menu = new WT_Menu($this->getMenuTitle(), 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;gallery_id='.$default_block, 'menu-my_gallery', 'down');
		$menu->addClass('menuitem', 'menuitem_hover', '');
		foreach ($this->getMenuAlbumList() as $item) {
			$languages=get_block_setting($item->block_id, 'languages');
			if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $item->gallery_access>=WT_USER_ACCESS_LEVEL) {
				$path = 'module.php?mod='.$this->getName().'&amp;mod_action=show&amp;gallery_id='.$item->block_id;
				$submenu = new WT_Menu(WT_I18N::translate($item->gallery_title), $path, 'menu-my_gallery-'.$item->block_id);
				$menu->addSubmenu($submenu);
			}
		}
		if (WT_USER_IS_ADMIN) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit gallerys'), $this->getConfigLink(), 'menu-my_gallery-edit');
			$menu->addSubmenu($submenu);
		}
		return $menu;
	}

	// Action from the configuration page
	private function edit() {
		if (WT_USER_IS_ADMIN) {
			global $MEDIA_DIRECTORY;
			require_once WT_ROOT.'includes/functions/functions_edit.php';

			if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
				$block_id=safe_POST('block_id');
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
					$block_id=WT_DB::getInstance()->lastInsertId();
				}
				set_block_setting($block_id, 'gallery_title',		safe_POST('gallery_title',		WT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'gallery_description', safe_POST('gallery_description',WT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'gallery_folder_w',	safe_POST('gallery_folder_w',	WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'gallery_folder_f',	safe_POST('gallery_folder_f',	WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'gallery_folder_p',	safe_POST('gallery_folder_p',	WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'gallery_access',	 	safe_POST('gallery_access',		WT_REGEX_UNSAFE));
				set_block_setting($block_id, 'plugin',			 	safe_POST('plugin',				WT_REGEX_UNSAFE));
				$languages=array();
				foreach (WT_I18N::used_languages() as $code=>$name) {
					if (safe_POST_bool('lang_'.$code)) {
						$languages[]=$code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id	= safe_GET('block_id');
				$controller	= new WT_Controller_Page();
				if ($block_id) {
					$item_title=get_block_setting($block_id, 'gallery_title');
					$item_description=get_block_setting($block_id, 'gallery_description');
					$item_folder_w=get_block_setting($block_id, 'gallery_folder_w');
					$item_folder_f=get_block_setting($block_id, 'gallery_folder_f');
					$item_folder_p=get_block_setting($block_id, 'gallery_folder_p');
					$item_access=get_block_setting($block_id, 'gallery_access');
					$plugin=get_block_setting($block_id, 'plugin');
					$block_order=WT_DB::prepare(
						"SELECT block_order FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
					$gedcom_id=WT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$item_title='';
					$item_description='';
					$item_folder_w=$MEDIA_DIRECTORY;
					$item_folder_f='';
					$item_folder_p='';
					$item_access=1;
					$plugin='kiwitrees';
					$block_order=WT_DB::prepare(
						"SELECT IFNULL(MAX(block_order)+1, 0) FROM `##block` WHERE module_name=?"
					)->execute(array($this->getName()))->fetchOne();
					$gedcom_id=WT_GED_ID;
				}
				$controller
					->pageHeader()
					->addInlineJavaScript('
						function hide_fields(){
							if (jQuery("#kiwitrees-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","visible");
								jQuery("#flickr-div .vis").css("visibility","hidden");
								jQuery("#picasa-div .vis").css("visibility","hidden");
							}
							else if (jQuery("#flickr-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","hidden");
								jQuery("#flickr-div .vis").css("visibility","visible");
								jQuery("#picasa-div .vis").css("visibility","hidden");
							}
							else if (jQuery("#picasa-radio").is(":checked")){
								jQuery("#kiwitrees-div .vis").css("visibility","hidden");
								jQuery("#flickr-div .vis").css("visibility","hidden");
								jQuery("#picasa-div .vis").css("visibility","visible");
							}
						};
					');

				if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
					ckeditor_WT_Module::enableEditor($controller);
				}
				?>
				<div id="<?php echo $this->getName();?>">
					<form name="<?php echo $this->getName(); ?>" method="post" action="#">
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="<?php echo $block_id; ?>">
						<?php echo WT_Filter::getCsrf(); ?>
						<table id="faq_module">
							<tr><th><?php echo WT_I18N::translate('Title'); ?></th></tr>
							<tr><td><input type="text" name="gallery_title" size="90" tabindex="1" value="<?php echo htmlspecialchars($item_title); ?>"></td></tr>
							<tr><th><?php echo WT_I18N::translate('Description'); ?></th></tr>
							<tr><td>
								<textarea name="gallery_description" class="html-edit" rows="10" cols="90" tabindex="2"><?php echo htmlspecialchars($item_description); ?></textarea>
							</td></tr>
							<tr><th><?php echo WT_I18N::translate('Source'); ?></th></tr>
							<tr>
								<td>
									<div id="kiwitrees-div">
										<p>
											<?php
											echo '<input id="kiwitrees-radio" type="radio" name="plugin" value="kiwitrees"';
											if ($plugin == 'kiwitrees') {
												echo ' checked';
											}
											echo ' onclick="hide_fields();">', WT_I18N::translate('kiwitrees');
											?>
										</p>
										<?php
										echo '<label class="vis"';
											if ($plugin == 'kiwitrees') {
												echo ' style="visibility:visible;">';
											} else {
												echo ' style="visibility:hidden;">';
											}
											echo WT_I18N::translate('Folder name on server');
											echo select_edit_control("gallery_folder_w", WT_Query_Media::folderList(), null, htmlspecialchars($item_folder_w));
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
											echo ' onclick="hide_fields();">' . WT_I18N::translate('Flickr');
											?>
										</p>
										<?php
										echo '<label class="vis"';
											if ($plugin == 'flickr') {
												echo ' style="visibility:visible;">';
											} else {
												echo ' style="visibility:hidden;">';
											}
											echo WT_I18N::translate('Flickr set number');
											echo '<input id="flickr" type="text" name="gallery_folder_f" tabindex="1" value="' . htmlspecialchars($item_folder_f) . '">';
										echo '</label>';
										?>
									</div>
									<div id="picasa-div">
										<p>
											<?php
											echo '<input id="picasa-radio" type="radio" name="plugin" value="picasa"';
											if ($plugin == 'picasa') {
												echo ' checked ';
											}
											echo ' onclick="hide_fields();">' . WT_I18N::translate('Picasa');
											?>
										</p>
										<?php
										echo '<label class="vis"';
											if ($plugin == 'picasa') {
												echo ' style="visibility:visible;">';
											} else {
												echo ' style="visibility:hidden;">';
											}
											echo WT_I18N::translate('Picasa user/gallery');
											echo '<input id="picasa" type="text" name="gallery_folder_p" tabindex="1" value="' . htmlspecialchars($item_folder_p) . '">';
										echo '</label>';
										?>
									</div>
								</td>
							</tr>
						</table>
						<table id="gallery_module">
							<tr>
								<th><?php echo WT_I18N::translate('Show this gallery for which languages?'); ?></th>
								<th><?php echo WT_I18N::translate('Gallery position'); ?></th>
								<th><?php echo WT_I18N::translate('Gallery visibility'); ?></th>
								<th><?php echo WT_I18N::translate('Access level'); ?></th>
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
									<?php echo select_edit_control('gedcom_id', WT_Tree::getIdList(), '', $gedcom_id, 'tabindex="4"'); ?>
								</td>
								<td>
									<?php echo edit_field_access_level('gallery_access', $item_access, 'tabindex="4"'); ?>
								</td>
							</tr>
						</table>
						<p class="save">
							<button class="btn btn-primary save" type="submit"  tabindex="7">
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
		$block_id=safe_GET('block_id');

		WT_DB::prepare(
			"DELETE FROM `##block_setting` WHERE block_id=?"
		)->execute(array($block_id));

		WT_DB::prepare(
			"DELETE FROM `##block` WHERE block_id=?"
		)->execute(array($block_id));
	}

	private function moveup() {
		$block_id=safe_GET('block_id');

		$block_order=WT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block=WT_DB::prepare(
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
		$block_id=safe_GET('block_id');

		$block_order=WT_DB::prepare(
			"SELECT block_order FROM `##block` WHERE block_id=?"
		)->execute(array($block_id))->fetchOne();

		$swap_block=WT_DB::prepare(
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

	private function config() {
		require_once WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#gallery_tabs").tabs();');

		if (array_key_exists('ckeditor', WT_Module::getActiveModules())) {
			ckeditor_WT_Module::enableEditor($controller);
		}

		$action = safe_POST('action');

		if ($action == 'update') {
			set_module_setting($this->getName(), 'HEADER_TITLE',		safe_POST('NEW_HEADER_TITLE'));
			set_module_setting($this->getName(), 'HEADER_DESCRIPTION',	safe_POST('NEW_HEADER_DESCRIPTION', WT_REGEX_UNSAFE)); // allow html
			set_module_setting($this->getName(), 'THEME_DIR',			safe_POST('NEW_THEME_DIR'));
			AddToLog($this->getName() . 'config updated', 'config');
		}

		$current_themedir	= get_module_setting($this->getName(), 'THEME_DIR', WT_I18N::translate('azur'));
		$themename			= $this->galleria_theme_names();

		$items = WT_DB::prepare(
			"SELECT block_id, block_order, gedcom_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_description".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='gallery_title'".
			" AND bs2.setting_name='gallery_description'".
			" AND IFNULL(gedcom_id, ?)=?".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID, WT_GED_ID))->fetchAll();

		$min_block_order=WT_DB::prepare(
			"SELECT MIN(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();

		$max_block_order=WT_DB::prepare(
			"SELECT MAX(block_order) FROM `##block` WHERE module_name=?"
		)->execute(array($this->getName()))->fetchOne();
		?>
		<div id="<?php echo $this->getName();?>">
			<a class="current faq_link" href="http://kiwitrees.net/?p=2854" target="_blank" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<div id="gallery_tabs">
				<ul>
					<li><a href="#gallery_summary"><span><?php echo WT_I18N::translate('Summary'); ?></span></a></li>
					<li><a href="#gallery_pages"><span><?php echo WT_I18N::translate('Galleries'); ?></span></a></li>
				</ul>
				<div id="gallery_summary">
					<form method="post" name="configform" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config">
						<input type="hidden" name="action" value="update">
						<div class="label"><?php echo WT_I18N::translate('Main menu and summary page title'); ?></div>
						<div class="value"><input type="text" name="NEW_HEADER_TITLE" value="<?php echo $this->getMenuTitle(); ?>"></div>
						<div class="label"><?php echo WT_I18N::translate('Summary page description'); ?></div>
						<div class="value2">
							<textarea name="NEW_HEADER_DESCRIPTION" class="html-edit" rows="5" cols="120"><?php echo $this->getSummaryDescription(); ?></textarea>
						</div>
						<div id="gallery_theme">
							<div class="label"><?php echo WT_I18N::translate('Select gallery theme'); ?></div>
							<?php
							foreach ($themename as $themedir) {
								echo
									'<div ', ($current_themedir == $themedir ? 'class = "current_theme"' : 'class = "theme_box"'), '>
											<img src="', WT_MODULES_DIR , $this->getName(), '/images/' , $themedir, '.png" alt="', $themedir, ' title="' ,$themedir, '">
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
							<?php echo WT_I18N::translate('save'); ?>
						</button>
					</form>
				</div>
				<div id="gallery_pages">
					<form method="get" action="<?php echo WT_SCRIPT_NAME;?>">
						<label><?php echo WT_I18N::translate('Family tree');?></label>
						<input type="hidden" name="mod" value="<?php echo $this->getName();?>">
						<input type="hidden" name="mod_action" value="admin_config">
						<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM);?>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
					<div>
						<button class="btn btn-primary add" onclick="location.href='module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit'">
							<i class="fa fa-plus"></i>
							<?php echo WT_I18N::translate('Add gallery'); ?>
						</button>
					</div>
					<table id="gallery_module">
						<?php
						if ($items) {
							$trees = WT_Tree::getAll();
							foreach ($items as $gallery) { ?>
								<tr class="gallery_edit_pos">
									<td>
										<?php
										echo '<p>' . WT_I18N::translate('Gallery position') . '<span>' . ($gallery->block_order) . '</span></p>';
										echo '<p>' . WT_I18N::translate('Family tree');
											if ($gallery->gedcom_id == null) {
												echo '<span>' . WT_I18N::translate('All') . '</span>';
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
											<?php echo WT_I18N::translate('Edit');?>
										</a>
									</td>
									<td>
										<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_delete&amp;block_id=<?php echo $gallery->block_id; ?>" onclick="return confirm('<?php echo WT_I18N::translate('Are you sure you want to delete this menu item?'); ?>');">
											<?php echo WT_I18N::translate('Delete');?>
										</a>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										<div class="faq_edit_item">
											<div class="faq_edit_title"><?php echo WT_I18N::translate($gallery->gallery_title);?></div>
											<div><?php echo substr($gallery->gallery_description, 0, 1)=='<' ? $gallery->gallery_description : nl2br($gallery->gallery_description);?></div>
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

	private function getJavaScript($item_id) {
		$theme = "azur";// alternatives: "classic", "simpl_galleria"
		$plugin=get_block_setting($item_id, 'plugin');
		$js='Galleria.loadTheme("'.WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/galleria/themes/'.$theme.'/galleria.'.$theme.'.min.js");';
			switch ($plugin) {
			case 'flickr':
			$flickr_set = get_block_setting($item_id, 'gallery_folder_f');
			$js.='
				Galleria.run("#galleria", {
					flickr: "set:'.$flickr_set.'",
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
			case 'picasa':
			$picasa_set = get_block_setting($item_id, 'gallery_folder_p');
			$js.='
				Galleria.run("#galleria", {
					picasa: "useralbum:'.$picasa_set.'",
					picasaOptions: {
						sort: "date-posted-asc",
						imageSize: "original"
					},
					_showCaption: false,
					imageCrop: false
				});
			';
			break;
			default:
			$js.='
				Galleria.ready(function(options) {
					this.bind("image", function(e) {
						data = e.galleriaData;
						$("#links_bar").html(data.layer);
					});
				});
				Galleria.run("#galleria", {
					imageCrop: false,
					_showCaption: false,
					_locale: {
						show_captions:		"'.WT_I18N::translate('Show descriptions').'",
						hide_captions:		"'.WT_I18N::translate('Hide descriptions').'",
						play:				"'.WT_I18N::translate('Play slideshow').'",
						pause:				"'.WT_I18N::translate('Pause slideshow').'",
						enter_fullscreen:	"'.WT_I18N::translate('Enter fullscreen').'",
						exit_fullscreen:	"'.WT_I18N::translate('Exit fullscreen').'",
						next:				"'.WT_I18N::translate('Next image').'",
						prev:				"'.WT_I18N::translate('Previous image').'",
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
		return WT_DB::prepare(
			"SELECT block_id,
				bs1.setting_value AS gallery_title,
				bs2.setting_value AS gallery_access,
				bs3.setting_value AS gallery_description,
				bs4.setting_value AS gallery_folder_w,
				bs5.setting_value AS gallery_folder_f,
				bs6.setting_value AS gallery_folder_p".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" JOIN `##block_setting` bs3 USING (block_id)".
			" JOIN `##block_setting` bs4 USING (block_id)".
			" JOIN `##block_setting` bs5 USING (block_id)".
			" JOIN `##block_setting` bs6 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='gallery_title'".
			" AND bs2.setting_name='gallery_access'".
			" AND bs3.setting_name='gallery_description'".
			" AND bs4.setting_name='gallery_folder_w'".
			" AND bs5.setting_name='gallery_folder_f'".
			" AND bs6.setting_name='gallery_folder_p'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID))->fetchAll();
	}

	// Return the list of gallerys for menu
	private function getMenuAlbumList() {
		return WT_DB::prepare(
			"SELECT block_id, bs1.setting_value AS gallery_title, bs2.setting_value AS gallery_access".
			" FROM `##block` b".
			" JOIN `##block_setting` bs1 USING (block_id)".
			" JOIN `##block_setting` bs2 USING (block_id)".
			" WHERE module_name=?".
			" AND bs1.setting_name='gallery_title'".
			" AND bs2.setting_name='gallery_access'".
			" AND (gedcom_id IS NULL OR gedcom_id=?)".
			" ORDER BY block_order"
		)->execute(array($this->getName(), WT_GED_ID))->fetchAll();
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
		$item_id=safe_GET('gallery_id');
		$controller = new WT_Controller_Page();
		$controller
			->setPageTitle($this->getMenuTitle())
			->pageHeader()
			->addExternalJavaScript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/galleria/galleria-1.4.2.min.js')
			->addExternalJavaScript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/galleria/plugins/flickr/galleria.flickr.min.js')
			->addExternalJavaScript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/galleria/plugins/picasa/galleria.picasa.min.js')
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
						$item_list=$this->getAlbumList();
						foreach ($item_list as $item) {
							$languages=get_block_setting($item->block_id, 'languages');
							if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $item->gallery_access >= WT_USER_ACCESS_LEVEL) { ?>
								<li class="ui-state-default ui-corner-top<?php echo ($item_id == $item->block_id ? ' ui-tabs-selected ui-state-active' : ''); ?>">
									<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;gallery_id=<?php echo $item->block_id; ?>" class="ui-tabs-anchor">
										<span title="<?php echo WT_I18N::translate($item->gallery_title); ?>"><?php echo WT_I18N::translate($item->gallery_title); ?></span>
									</a>
								</li>
							<?php }
						} ?>
					</ul>
					<div id="outer_gallery_container">
						<?php
						foreach ($item_list as $item) {
							$languages=get_block_setting($item->block_id, 'languages');
							if ((!$languages || in_array(WT_LOCALE, explode(',', $languages))) && $item_id==$item->block_id && $item->gallery_access>=WT_USER_ACCESS_LEVEL) {
								$item_gallery = '
									<h4>' . WT_I18N::translate($item->gallery_description) . '</h4>' .
									$this->mediaDisplay($item->gallery_folder_w, $item_id);
							}
						}
						if (!isset($item_gallery)) {
							echo '<h4>' . WT_I18N::translate('Image collections related to our family') . '</h4>' .
								$this->mediaDisplay('//', $item_id);
						} else {
							echo $item_gallery;
						} ?>
					</div><!-- close #outer_gallery_container -->
				</div><!-- close #gallery_tabs -->
			</div><!-- close #gallery-container -->
		</div><!-- close #gallery-page -->
	<?php }

	// Print the gallery display
	private function mediaDisplay($sub_folder, $item_id) {
		global $MEDIA_DIRECTORY;
		$plugin = get_block_setting($item_id, 'plugin');
		$images = '';
		$media_links = '';
		// Get the related media items
		$sub_folder=str_replace($MEDIA_DIRECTORY, "",$sub_folder);
		$sql = "SELECT * FROM ##media WHERE m_filename LIKE '%" . $sub_folder . "%' ORDER BY m_filename";
		$rows = WT_DB::prepare($sql)->execute()->fetchAll(PDO::FETCH_ASSOC);
		if ($plugin == 'kiwitrees') {
			foreach ($rows as $rowm) {
				// Get info on how to handle this media file
				$media=WT_Media::getInstance($rowm['m_id']);
				if ($media->canDisplayDetails()) {
					$links = array_merge(
						$media->fetchLinkedIndividuals(),
						$media->fetchLinkedFamilies(),
						$media->fetchLinkedSources()
					);
					$rawTitle = $rowm['m_titl'];
					if (empty($rawTitle)) $rawTitle = get_gedcom_value('TITL', 2, $rowm['m_gedcom']);
					if (empty($rawTitle)) $rawTitle = basename($rowm['m_filename']);
					$mediaTitle = htmlspecialchars(strip_tags($rawTitle));
					$rawUrl = $media->getHtmlUrlDirect();
					$thumbUrl = $media->getHtmlUrlDirect('thumb');
					$media_notes = $this->FormatGalleryNotes($rowm['m_gedcom']);
					$mime_type = $media->mimeType();
					$gallery_links='';
					if (WT_USER_CAN_EDIT) {
						$gallery_links.='<div class="edit_links">';
							$gallery_links .='<div class="image_option"><a href="'. $media->getHtmlUrl(). '"><img src="'.WT_THEME_URL.'images/edit.png" title="'.WT_I18N::translate('Edit').'"></a></div>';
							if (WT_USER_GEDCOM_ADMIN) {
								if (array_key_exists('GEDFact_assistant', WT_Module::getActiveModules())) {
									$gallery_links.='<div class="image_option"><a href="inverselink.php?mediaid=' . $rowm['m_id'] . '&linkto=manage&ged=' . WT_GEDCOM . '" target="_blank"><img src="' . WT_THEME_URL . 'images/link.png" title="' . WT_I18N::translate('Manage links') . '"></a></div>';
								}
							}
						$gallery_links.='</div><hr>';// close .edit_links
					}
					if ($links) {
						$gallery_links .= '<h4>'.WT_I18N::translate('Linked to:').'</h4>';
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
			if (WT_USER_CAN_ACCESS || $media_links != '') {
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
				'<a id="copy" href="http://galleria.io/" target="_blank">Display by Galleria</a>'.// gallery.io attribution
				'</div>'.// close #page
				'<div style="clear: both;"></div>';
		} else {
			$html .= WT_I18N::translate('Gallery is empty. Please choose other gallery.').
				'</div>'. // close #galleria
				'</div>'. // close #page
				'<div style="clear: both;"></div>';
		}
		return $html;
	}

	// Get galleria themes list
	private function galleria_theme_names() {
		$themes = array();
		$d = dir(WT_MODULES_DIR.$this->getName(). '/galleria/themes/');
		while (false !== ($folder = $d->read())) {
			if ($folder[0] != '.' && $folder[0] != '_' && is_dir(WT_MODULES_DIR.$this->getName(). '/galleria/themes/'.$folder)) {
				$themes[] = $folder;
			}
		}
		$d->close();
		return $themes;
	}

}
