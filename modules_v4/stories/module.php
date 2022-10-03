<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

class stories_KT_Module extends KT_Module implements KT_Module_Block, KT_Module_Tab, KT_Module_Config, KT_Module_Menu {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Stories');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Stories” module */ KT_I18N::translate('Add narrative stories to individuals in the family tree.');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 160;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_HIDE;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_edit':
			$this->edit();
			break;
		case 'admin_delete':
			$this->delete();
			$this->config();
			break;
		case 'admin_config':
			$this->config();
			break;
		case 'story_link':
			$this->story_link();
			break;
		case 'show_list':
			$this->show_list();
			break;
		case 'remove_indi':
			$indi  = safe_GET('indi_ref');
			$block_id = safe_GET('block_id');
			if ($indi && $block_id) {
				self::removeIndi($indi, $block_id);
			}
			unset($_GET['action']);
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
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
		return false;
	}

	// Implement class KT_Module_Tab
	public function defaultTabOrder() {
		return 50;
	}

	// Implement class KT_Module_Tab
	public function getTabContent() {
		global  $controller;

		$block_ids =
			KT_DB::prepare("
				SELECT ##block.block_id
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block.block_id = ##block_setting.block_id
				 AND (##block_setting.setting_name = 'xref' AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]'))
				 AND ##block.gedcom_id=?
				 ORDER BY ##block.block_order
			")->execute(array(
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID
			))->fetchOneColumn();

		$html			= '';
		$class			= '';
		$ids			= array();
		$count_stories	= 0;
		foreach ($block_ids as $block_id) {
			$block_order = get_block_order($block_id);
			// check how many stories can be shown in a language
			$languages = get_block_setting($block_id, 'languages');
			if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) {
				$count_stories ++;
				$ids[] = $block_order;
			}
		}
		// establish first story id from lowest block_order
		$ids ? $first_story = min($ids) : $first_story = '';
		foreach ($block_ids as $block_id) {
			$block_order = get_block_order($block_id);
			if ($block_order == $first_story) {
				$first_story = $block_id;
			}
		}
		ob_start();

		if (KT_USER_GEDCOM_ADMIN) { // change this to KT_USER_CAN_EDIT to allow editors to create first story. ?>
			<div style="border-bottom:thin solid #aaa; margin:-10px; padding-bottom:2px;">
				<span>
					<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;xref=<?php echo $controller->record->getXref(); ?>">
						<i style="margin: 0 3px 0 10px;" class="icon-button_addnote">&nbsp;</i>
						<?php echo KT_I18N::translate('Add story'); ?>
					</a>
				</span>
				<span>
					<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config&amp;xref=<?php echo $controller->record->getXref(); ?>">
						<i style="margin: 0 3px 0 10px;" class="icon-button_linknote">&nbsp;</i>
						<?php echo KT_I18N::translate('Link this individual to an existing story '); ?>
					</a>
				</span>
			</div>
		<?php }

		if ($count_stories > 1) {
			$class = 'story';
			$controller->addInlineJavascript('
				// Start with all stories hidden except the first
				jQuery("#story_contents div.story").hide();
				jQuery("#story_contents #stories_" + ' . $first_story . ').show();

				// Calculate scroll value
				var posn = jQuery("#navbar").height() - jQuery("#indi_header").height() + jQuery(".ui-tabs-nav").height() + 25;
				if (jQuery("#navbar").css("position") == "fixed") {
					var posn = jQuery("#indi_header").height() + jQuery(".ui-tabs-nav").height() - 20;
				}

				// On clicking a title hide all stories except the chosen one
				jQuery("#contents_list a").click(function(e){
					e.preventDefault();
					var id = jQuery(this).attr("id").split("_");
					jQuery("#story_contents .story").hide();
					jQuery("#story_contents #stories_" + id[1]).show();
					jQuery("html, body").stop().animate({scrollTop: jQuery("#stories").offset().top - posn}, 2000);
				});
			'); ?>

			<h3 class="center"><?php echo KT_I18N::translate('List of stories'); ?></h3>
			<ol id="contents_list">
				<?php foreach ($block_ids as $block_id) {
					$languages = get_block_setting($block_id, 'languages');
					if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
						<li style="padding:2px 8px;">
							<a href="#" id="title_<?php echo $block_id; ?>"><?php echo get_block_setting($block_id, 'title'); ?></a>
						</li>
					<?php }
				} ?>
			</ol>
			<hr class="stories_divider">
		<?php } ?>
		<div id="story_contents">
			<?php foreach ($block_ids as $block_id) {
				$languages = get_block_setting($block_id, 'languages');
				if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) { ?>
					<div id="stories_<?php echo $block_id; ?>" class="<?php echo $class; ?>">
						<?php if (KT_USER_CAN_EDIT) { ?>
							<div style="margin-top:15px;">
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_edit&amp;block_id=<?php echo $block_id; ?>">
									<i style="margin: 0 3px 0 0;" class="icon-button_note">&nbsp;</i><?php echo KT_I18N::translate('Edit story'); ?>
								</a>
							</div>
						<?php } ?>
						<h1><?php echo get_block_setting($block_id, 'title'); ?></h1>
						<div style="white-space: normal;">
							<?php echo get_block_setting($block_id, 'story_body'); ?>
						</div>
						<?php if ($count_stories > 1) { ?>
							<hr class="stories_divider">
						<?php } ?>
					</div>
				<?php }
			} ?>
		</div>

		<?php return '<div class="stories_tab_content">' . ob_get_clean() . '</div>';

	}

	function getStoriesCount() {
		global $controller;

		$count_of_stories =
			KT_DB::prepare("
				SELECT COUNT(##block.block_id)
				 FROM ##block, ##block_setting
				 WHERE ##block.module_name=?
				 AND ##block_setting.setting_value REGEXP CONCAT('[[:<:]]', ?, '[[:>:]]')
				 AND gedcom_id=?
			")->execute(array(
				$this->getName(),
				$xref = $controller->record->getXref(),
				KT_GED_ID
			))->fetchOne();

		return $count_of_stories;
	}

	// Implement class KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->getStoriesCount() > 0;
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return $this->getStoriesCount() == 0;
	}

	// Implement class KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement class KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// Action from the configuration page
	private function edit() {
		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		if (KT_USER_CAN_EDIT) {
			if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
				$block_id = KT_Filter::postInteger('block_id');
				if ($block_id) {
					KT_DB::prepare(
						"UPDATE `##block` SET gedcom_id=? WHERE block_id=?"
					)->execute(array(safe_POST('gedcom_id'), $block_id));
				} else {
					KT_DB::prepare(
						"INSERT INTO `##block` (gedcom_id, module_name, block_order) VALUES (?, ?, ?)"
					)->execute(array(
						safe_POST('gedcom_id'),
						$this->getName(),
						0
					));
					$block_id = KT_DB::getInstance()->lastInsertId();
				}
				$xref = array();
				foreach (safe_Post('xref') as $indi_ref => $name) {
					$xref[] = $name;
				}
				set_block_setting($block_id, 'xref', implode(',', $xref));
				set_block_setting($block_id, 'title', safe_POST('title', KT_REGEX_UNSAFE)); // allow html
				set_block_setting($block_id, 'story_body',  safe_POST('story_body', KT_REGEX_UNSAFE)); // allow html
				$languages = array();
				foreach (KT_I18N::used_languages() as $code => $name) {
					if (safe_POST_bool('lang_' . $code)) {
						$languages[] = $code;
					}
				}
				set_block_setting($block_id, 'languages', implode(',', $languages));
				$this->config();
			} else {
				$block_id=safe_GET('block_id');
				$controller = new KT_Controller_Page();
				$controller->addInlineJavascript('
					jQuery("#newField").click(function(){
					    jQuery(".add_indi:last").clone().insertAfter(".indi_find:last");
					    jQuery(".add_indi:last>input").attr("value", "");
					    jQuery(".add_indi:last>input").removeAttr("id").autocomplete({
							source: "autocomplete.php?field=INDI",
							html: !0
						});
					})
				');

				if ($block_id) {
					$controller->setPageTitle(KT_I18N::translate('Edit story'));
					$title = get_block_setting($block_id, 'title');
					$story_body = get_block_setting($block_id, 'story_body');
					$xref = explode(",", get_block_setting($block_id, 'xref'));
					$count_xref = count($xref);
					$gedcom_id = KT_DB::prepare(
						"SELECT gedcom_id FROM `##block` WHERE block_id=?"
					)->execute(array($block_id))->fetchOne();
				} else {
					$controller->setPageTitle(KT_I18N::translate('Add story'));
					$title = '';
					$story_body = '';
					$gedcom_id = KT_GED_ID;
					$xref = safe_GET('xref', KT_REGEX_XREF);
					$count_xref = 1;
				}
				$controller
					->pageHeader()
					->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
					->addInlineJavascript('autocomplete();');

				if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
					ckeditor_KT_Module::enableEditor($controller);
				}

				echo '
					<form id="story" name="story" method="post">',
						KT_Filter::getCsrf(), '
						<input type="hidden" name="save" value="1">
						<input type="hidden" name="block_id" value="', $block_id, '">
						<input type="hidden" name="gedcom_id" value="', KT_GED_ID, '">
						<table id="faq_module">
							<tr>
								<th>', KT_I18N::translate('Story title'), '</th>
							</tr>
							<tr>
								<td><textarea name="title" rows="1" cols="90">', htmlspecialchars((string) $title), '</textarea></td>
							</tr>
							<tr>
								<th>' ,KT_I18N::translate('Story'), '</th>
							</tr>
							<tr>
								<td><textarea name="story_body" class="html-edit" rows="10" cols="90">', htmlspecialchars((string) $story_body), '</textarea></td>
							</tr>
						</table>
						<table id="faq_module2">
							<tr>
								<th>', KT_I18N::translate('Individual'), '</th>
								<th>', KT_I18N::translate('Show this block for which languages?'), '</th>
							</tr>
							<tr>
								<td class="optionbox">';
									if (!$block_id) {
										echo '<div class="indi_find">
											<p class="add_indi">
												<input data-autocomplete-type="INDI" type="text" name="xref[]" id="pid" value="' . $xref . '" placeholder="' . KT_I18N::translate('Enter name or part of name') . '">
											</p>';
											if ($xref) {
												$person = KT_Person::getInstance($xref);
												if ($person) {
													echo $person->format_list('span');
													echo '
														<p>
															<a href="module.php?mod=', $this->getName(), '&amp;mod_action=remove_indi&amp;indi_ref='. $xref. '&amp;block_id=' . $block_id. '" class="current" onclick="return confirm(\'' . KT_I18N::translate('Are you sure you want to remove this?') . '\');">' . KT_I18N::translate('Remove') . '</a>
														</p>
														<hr style="margin-top: 0;"">
													';
												}
											}
										echo '</div>';
									} else {
										for ($x = 0; $x < $count_xref; $x++) {
											echo '<div class="indi_find">
												<p class="add_indi">
													<input data-autocomplete-type="INDI" type="text" name="xref[]" id="pid', $x, '" value="' . $xref[$x] . '" placeholder="' . KT_I18N::translate('Enter name or part of name') . '">
												</p>';
												if ($xref) {
													$person = KT_Person::getInstance($xref[$x]);
													if ($person) {
														echo $person->format_list('span');
														echo '
															<p>
																<a href="module.php?mod=', $this->getName(), '&amp;mod_action=remove_indi&amp;indi_ref='. $xref[$x]. '&amp;block_id='. $block_id. '" class="current" onclick="return confirm(\'' . KT_I18N::translate('Are you sure you want to remove this?') . '\');">' . KT_I18N::translate('Remove') . '</a>
															</p>
															<hr style="margin-top: 0;"">
														';
													}
												}
											}
										echo '</div>';
									}
									echo '<p><a href="#" id="newField" class="current">' . KT_I18N::translate('Add another individual') . '</a></p>
								</td>';
								$languages = get_block_setting($block_id, 'languages');
								echo '<td class="optionbox">',
									edit_language_checkboxes('lang_', $languages), '
								</td>
							</tr>
						</table>
						<button class="btn btn-primary save" type="submit">
							<i class="fa fa-floppy-o"></i>' .
							KT_I18N::translate('Save'). '
						</button>
						<button class="btn btn-primary cancel" type="button" onclick="window.location=\'' . $this->getConfigLink() . '\';" tabindex="6">
							<i class="fa fa-times"></i>' .
							KT_I18N::translate('Cancel') .'
						</button>
					</form>';
				exit;
			}
		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	private function config() {
		require_once KT_ROOT . 'includes/functions/functions_edit.php';
		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
			    jQuery("#story_table").sortable({items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});
			    //-- update the order numbers after drag-n-drop sorting is complete
			    jQuery("#story_table").bind("sortupdate", function(event, ui) {
					jQuery("#"+jQuery(this).attr("id")+" input").each(
						function (index, value) {
							value.value = index+1;
						}
					);
				});
			');

		$stories = KT_DB::prepare(
			"SELECT block_id, xref, block_order" .
			" FROM ##block" .
			" WHERE module_name=?" .
			" AND gedcom_id=?"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();

		$new_xref = safe_GET('xref', KT_REGEX_XREF);

		//transfer old xref in ##block to new xref in ##block_setting
		foreach ($stories as $story) {
			if ($story->xref != NULL) {
				set_block_setting($story->block_id, 'xref', $story->xref);
				KT_DB::prepare(
					"UPDATE `##block` SET xref = NULL WHERE block_id=?"
				)->execute(array($story->block_id));
			}
		}

		foreach ($stories as $this->getName=>$story) {
			$order = safe_POST('taborder-'. $story->block_id);
			if ($order) {
				KT_DB::prepare(
					"UPDATE `##block` SET block_order=? WHERE block_id=?"
				)->execute(array($order, $story->block_id));
				$story->block_order = $order; // Make the new order take effect immediately
			}
		}
		uasort($stories, function ($x, $y) {
			return $x->block_order > $y->block_order;
		});

		?>
		<div id="<?php echo $this->getName(); ?>">
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<form method="get" action="<?php echo KT_SCRIPT_NAME; ?>">
				<label><?php echo KT_I18N::translate('Family tree'); ?></label>
				<input type="hidden" name="mod", value="<?php echo $this->getName(); ?>">
				<input type="hidden" name="mod_action", value="admin_config">
				<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
				<button class="btn btn-primary show" type="submit" style="float:none;">
					<i class="fa fa-eye"></i>
					<?php echo KT_I18N::translate('Show'); ?>
				</button>
			</form>
			<?php
			echo
				'<button class="btn btn-primary add" onclick="window.location.href=\'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_edit\'">
					<i class="fa fa-plus"></i>' .
					KT_I18N::translate('Add story') .'
				</button>
				<form name="story_list" method="post" action="module.php?mod=', $this->getName(), '&amp;mod_action=admin_config">';
					if (count($stories)>0) {
					echo '<table id="story_table">
						<thead>
							<tr>
								<th>', KT_I18N::translate('Order'), '</th>
								<th>', KT_I18N::translate('Story title'), '</th>
								<th>', KT_I18N::translate('Individual'), '</th>
								<th class="maxwidth">', KT_I18N::translate('Edit'), '</th>
								<th class="maxwidth">', KT_I18N::translate('Delete'), '</th>';
								if ($new_xref) echo '<th class="maxwidth">', KT_I18N::translate('Link'), '</th>';
							echo '</tr>
						</thead>
						<tbody>';
							$order = 1;
							foreach ($stories as $story) {
								$story_title = get_block_setting($story->block_id, 'title');
								$xref = explode(",", get_block_setting($story->block_id, 'xref'));
								$count_xref = count($xref);
								echo '
									<tr class="sortme">
										<td>
											<input type="text" value="', $order, '" name="taborder-', $story->block_id, '">
										</td>
										<td>', $story_title, '</td>
										<td>';
											for ($x = 0; $x < $count_xref; $x++) {
												$indi[$x] = KT_Person::getInstance($xref[$x]);
												if ($indi[$x]) {
														  echo '<p><a href="', $indi[$x]->getHtmlUrl() . '#stories" class="current">' . $indi[$x]->getFullName(), '</a></p>';
												} else {
													echo '<p class="error">', $xref[$x], '</p>';
												}
											}
										echo '</td>
										<td class="center"><a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_edit&amp;block_id=', $story->block_id, '"><div class="icon-edit">&nbsp;</div></a></td>
										<td class="center"><a href="module.php?mod=', $this->getName(), '&amp;mod_action=admin_delete&amp;block_id=', $story->block_id, '" onclick="return confirm(\'', KT_I18N::translate('Are you sure you want to delete this story?'), '\');"><div class="icon-delete">&nbsp;</div></a></td>';
										if ($new_xref) echo '<td class="center"><a href="module.php?mod=', $this->getName(), '&amp;mod_action=story_link&amp;block_id=', $story->block_id, '&amp;xref=', $new_xref, '" onclick="return confirm(\'', KT_I18N::translate('Are you sure you want to link to this story?'), '\');"><div class="icon-link">&nbsp;</div></a></td>';
									echo '</tr>';
								$order++;
							}
						echo '</tbody>
					</table>';
				}
				echo '<button class="btn btn-primary save" type="submit">
					<i class="fa fa-floppy-o"></i>' .
					KT_I18N::translate('Save'). '
				</button>
			</form>
		</div>';
	}

	private function show_list() {
		global $controller;
		$controller = new KT_Controller_Page();
		$controller->addExternalJavascript(KT_JQUERY_DATATABLES_URL);
		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_JQUERY_DT_HTML5)
				->addExternalJavascript(KT_JQUERY_DT_BUTTONS);
		}
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery("#story_table").dataTable({
					dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csv"}],
					jQueryUI: true,
					autoWidth: false,
					paging: true,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					sorting: [[0,"asc"]],
					displayLength: 20,
					columns: [
						/* 0-name */ null,
						/* 1-NAME */ null
					]
				});
			');

		$stories = KT_DB::prepare(
			"SELECT block_id" .
			" FROM `##block`" .
			" WHERE module_name=?" .
			" AND gedcom_id=?"
		)->execute(array($this->getName(), KT_GED_ID))->fetchAll();

		echo '<h2 class=" story center">', KT_I18N::translate('Stories'), '</h2>';
		if (count($stories)>0) {
			echo '<table id="story_table" class="width100">
				<thead>
					<tr>
						<th>', KT_I18N::translate('Story title'), '</th>
						<th>', KT_I18N::translate('Individual'), '</th>
					</tr>
				</thead>
				<tbody>';
				foreach ($stories as $story) {
					$story_title = get_block_setting($story->block_id, 'title');
					$xref = explode(",", get_block_setting($story->block_id, 'xref'));
					$count_xref = count($xref);
					// if one indi is private, the whole story is private.
						$private = 0;
						for ($x = 0; $x < $count_xref; $x++) {
							$indi[$x] = KT_Person::getInstance($xref[$x]);
							if ($indi[$x] && !$indi[$x]->canDisplayDetails()) {
								$private = $x+1;
							}
						}
					if ($private == 0) {
						$languages=get_block_setting($story->block_id, 'languages');
						if (!$languages || in_array(KT_LOCALE, explode(',', $languages))) {
							echo '<tr>
								<td>', $story_title, '</td>
								<td>';
									for ($x = 0; $x < $count_xref; $x++) {
										$indi[$x] = KT_Person::getInstance($xref[$x]);
										if (!$indi[$x]){
											echo '<p class="error">', $xref[$x], '</p>';
										} else {
											echo '<p><a href="', $indi[$x]->getHtmlUrl() . '#stories" class="current">' . $indi[$x]->getFullName(), '</a></p>';
										}
									}
								echo '</td>
							</tr>';
						}
					}
				}
			echo '</tbody></table>';
		}
	}

	// Delete a story from the database
	private function delete() {
		if (KT_USER_CAN_EDIT) {
			$block_id = safe_GET('block_id');

			$block_order=KT_DB::prepare(
				"SELECT block_order FROM `##block` WHERE block_id=?"
			)->execute(array($block_id))->fetchOne();

			KT_DB::prepare(
				"DELETE FROM `##block_setting` WHERE block_id=?"
			)->execute(array($block_id));

			KT_DB::prepare(
				"DELETE FROM `##block` WHERE block_id=?"
			)->execute(array($block_id));

		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	// Link an individual to an existing story directly
	private function story_link() {
		if (KT_USER_GEDCOM_ADMIN) {
			$block_id = safe_GET('block_id');
			$new_xref = safe_GET('xref', KT_REGEX_XREF);
			$xref = explode(",", get_block_setting($block_id, 'xref'));
			$xref[] = $new_xref;
			set_block_setting($block_id, 'xref', implode(',', $xref));
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH. 'individual.php?pid='. $new_xref);
		} else {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
			exit;
		}
	}

	// Delete an individual linked to a story, from the database
	private function removeIndi($indi, $block_id) {
		$xref = explode(",", get_block_setting($block_id, 'xref'));
		$xref = array_diff($xref, array($indi));
		set_block_setting($block_id, 'xref', implode(',', $xref));
		header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH. 'module.php?mod='. $this->getName(). '&mod_action=admin_edit&block_id='. $block_id);
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		global $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return null;
		}
		//-- Stories menu item
		$menu = new KT_Menu($this->getTitle(), 'module.php?mod=' . $this->getName() . '&amp;mod_action=show_list', 'menu-story');
		return $menu;
	}

}
