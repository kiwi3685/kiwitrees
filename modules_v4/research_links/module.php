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

class research_links_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Sidebar, WT_Module_List {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ WT_I18N::translate('Research links');
	}

	public function getSidebarTitle() {
		return /* Title used in the sidebar */ WT_I18N::translate('Research links');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('A collection of links to popular research web sites.');
	}

	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 30;
	}

	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	// Extend class WT_Module_List
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_List
	public function getListMenus() {
		global $controller;
		$menus = array();
		$menu  = new WT_Menu(
			$this->getTitle(),
			'module.php?mod=simpl_research&amp;mod_action=show',
			'menu-research_links'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'show': // for list menu item
				$this->show();
				break;
		case 'admin_config':
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// Configuration page
	private function config() {
		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page;
		$controller
			->restrictAccess(WT_USER_IS_ADMIN)
			->setPageTitle($this->getSidebarTitle())
			->pageHeader()
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				var oTable = jQuery("#research_links").dataTable( {
					"sDom": \'<"H"firl>t\',
					'.WT_I18N::datatablesI18N().',
					"bJQueryUI" 		: true,
					"bAutoWidth" 		: true,
					"aaSorting" 		: [[ 2, "asc" ]],
					"bStateSave" 		: true,
					"bPaginate"			: false,
					"iCookieDuration" 	: 180,
					"aoColumns" : [
						{ dataSort: 1, sClass: "center" },
						{ type: "unicode", visible: false },
						null,
						null,
						{ sClass: "center" },
						{ sClass: "center" },
					]
				});
			');

		if (WT_Filter::postBool('save')) {
			set_module_setting($this->getName(), 'RESEARCH_PLUGINS', serialize(WT_Filter::post('NEW_RESEARCH_PLUGINS')));
			set_module_setting($this->getName(), 'RESEARCH_PLUGINS_DEFAULT_AREA', WT_Filter::post('NEW_RESEARCH_PLUGINS_DEFAULT_AREA'));
			AddToLog($this->getTitle().' config updated', 'config');
		}

		$all_plugins = $this->getPluginList(); // all plugins with area names
		$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS')); // enabled plugins
		$html = '
			<div id="' . $this->getName() . '">
				<h2>' . $controller->getPageTitle() . '</h2>
				<form method="post" name="configform" action="' . $this->getConfigLink() . '">
					<input type="hidden" name="save" value="1">
					<h3>' . WT_I18N::translate('Select the research area to set as default. This area will open first in the sidebar.') . '</h3>';
					foreach ($all_plugins as $area => $plugins) {
						// reset returns the first value in an array
						// we take the area code from the first plugin in this area
						$area_code = reset($plugins)->getSearchArea();
						$html .= '<input type="radio" name="NEW_RESEARCH_PLUGINS_DEFAULT_AREA" value="' . $area . '"';
							if (get_module_setting($this->getName(), 'RESEARCH_PLUGINS_DEFAULT_AREA') === $area) {
								$html .= ' checked="checked"';
							}
						$html .= '>
						<span>' . $area . '</span>';
					}
					$html .= '<h3>' . WT_I18N::translate('Select the links you want to use in the sidebar') . '</h3>
					<h4>' . WT_I18N::translate('Select all') .'
						<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
					</h4>
					<button class="btn btn-primary save" type="submit">
						<i class="fa fa-floppy-o"></i>'.
						WT_I18N::translate('save').'
					</button>
					<div class="clearfloat"></div>
					<table id="research_links" style="width: 100%;">
						<thead>
							<th> ' . WT_I18N::translate('Enabled') . '</th>
							<th></th>
							<th> ' . WT_I18N::translate('Name') . '</th>
							<th> ' . WT_I18N::translate('Area') . '</th>
							<th> ' . WT_I18N::translate('Pay to view') . '</th>
							<th> ' . WT_I18N::translate('Links only') . '</th>
						</thead>
						<tbody>';
							foreach ($all_plugins as $area => $plugins) {
								foreach ($plugins as $label => $plugin) {
									if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) {
										$enabled = $RESEARCH_PLUGINS[$label];
									} else {
										$enabled = '0';
									}
									$html .= '
										<tr>
											<td>' . checkbox('NEW_RESEARCH_PLUGINS['  .$label . ']', $enabled, ' class="check"') .' </td>
											<td>' . $enabled . '</td>
											<td>' . $plugin->getName() .' </td>
											<td>' . $area .' </td>
											<td>' . $this->getCurrency($plugin) .' </td>
											<td>';
											 	if ($plugin->createLinkOnly()) {
													$html .= ' (<i class="fa fa-link" style="font-size: 1em; margin:0;"></i>) ';
												}
											$html .= '</td>
										</tr>
									';
								}
							}
						$html .= '</tbody>
					</table>
					<button class="btn btn-primary save" type="submit">
						<i class="fa fa-floppy-o"></i>'.
						WT_I18N::translate('save').'
					</button>
				</form>
			</div>';
		// output
		ob_start();
		$html .= ob_get_clean();
		echo $html;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarContent() {
		// code based on similar in function_print_list.php
		global $controller, $WT_IMAGES, $SEARCH_SPIDER;
		if ($SEARCH_SPIDER) {
			return false;
		} else {
			$controller->addInlineJavascript(
				$this->getJavaScript('block') . '
				jQuery("#' . $this->getName() . ' a:first").text("' . $this->getSidebarTitle() . '");
			');

			$globalfacts = $controller->getGlobalFacts();
			$html = '<ul id="research_status">';
				$i = 0;
				$total_enabled_plugins = 0;
				$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
				foreach ($this->getPluginList() as $area => $plugins) {
					$enabled_plugins		 = $this->countEnabledPlugins($plugins, $RESEARCH_PLUGINS, true);
					$total_enabled_plugins	 = $total_enabled_plugins + $enabled_plugins;
					ksort($plugins);
					if ($enabled_plugins > 0) {
						$html .= '
						<li class="research-area" data-area="' . $area . '">
							<a href="#" class="research-area-title">
								<span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e"></span>
								' . $area . ' (' . $enabled_plugins . ')' . '
							</a>
							<ul class="research-list">';
								$i++;
								foreach ($plugins as $label => $plugin) {
									if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) {
										$value = $RESEARCH_PLUGINS[$label];
									} else {
										$value = '0';
									}
									if($value == 1) {
										$name = false; // only use the first fact with a NAME tag.
										foreach ($globalfacts as $key=>$value) {
											$fact = $value->getTag();
											if ($fact == "NAME" && !$name) {
												$primary = $this->getPrimaryName($value);
												if ($primary) {
													$name			= true;
													$data			= $this->setPluginVariables($plugin, $primary, true);
													$link 			= $plugin->create_link($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
													$sublinks 		= $plugin->create_sublink($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
													$link_only		= $plugin->createLinkOnly();
													$sublinks_only	= $plugin->createSubLinksOnly();
												}
											}
										}
										if ($sublinks || $sublinks_only) {
											$sublinks_only ? $sublinks = $sublinks_only : $sublinks = $sublinks;
											$html .= '<li>
												<a class="mainlink" href="'.htmlspecialchars($link).'">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . '
													<span title="' . WT_I18N::translate('Pay to view') . '">' . $this->getCurrency($plugin) . '</span>';
													if ($sublinks_only) {
														$html .= ' (<i class="fa fa-link" style="font-size: 1em; margin:0;" title="' . WT_I18N::translate('Links only') . '"></i>) ';
													}
												$html .= '</a>
												<ul class="sublinks">';
													foreach ($sublinks as $sublink) {
														if (stripos($sublink['link'], "postresearchform") === false){
															$alink = 'href="' . htmlspecialchars($sublink['link']) . '"';
														} else {
															$alink = 'href="javascript:void(0);" onclick="' . htmlspecialchars($sublink['link']) . '; return false;"';
														}
														$html .= '<li>
															<a class="research_link" ' . $alink . ' target="_blank" rel="noopener noreferrer">
																<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
																$sublink['title'].'
															</a>
														</li>';
													}
												$html .= '</ul>
											</li>';
										} else { // default
											$link_only ? $link = $link_only : $link = $link;
											if (stripos($link, "postresearchform") === false){
												$alink = 'href="' . htmlspecialchars($link) . '"';
											} else {
												$alink = 'href="javascript:void(0);" onclick="' . htmlspecialchars($link) . '; return false;"';
											}
											$html .= '<li>
												<a class="research_link" ' . $alink . ' target="_blank" rel="noopener noreferrer">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . '
													<span  title="' . WT_I18N::translate('Pay to view') . '">' . $this->getCurrency($plugin) . '</span>';
													if ($link_only) {
														$html .= ' (<i class="fa fa-link" style="font-size: 1em; margin:0;" title="' . WT_I18N::translate('Links only') . '"></i>) ';
													}
												$html .= '</a>
											</li>';
										}
									}
								}
							$html .= '</ul>
						</li>';
					}
				}
			$html .=  '</ul>';
		}
		return $html;
	}

	private function show() {

		$all_plugins 	= $this->getPluginList();
		$action 		= WT_Filter::post('action');
		$indi			= WT_Filter::post('indi', WT_REGEX_XREF, '');
		$surn			= WT_Filter::post('surn', null, '');
		$givn			= WT_Filter::post('givn', null, '');
		$sdate			= WT_Filter::post('sdate', null, '');
		$edate			= WT_Filter::post('edate', null, '');
		$sel_area		= WT_Filter::postArray('area');
		$links_array	= implode(",", WT_Filter::postArray('links_array'));
		$reset 			= WT_Filter::post('reset');
		if ($reset) {unset($_POST);}

		global $controller;
		$controller = new WT_Controller_Page();
		$controller
			->restrictAccess(WT_Module::isActiveList(WT_GED_ID, $this->getName(), WT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript(
				$this->getJavaScript('inline-block') . '
				autocomplete();
				jQuery(function() {
					jQuery(".research-area:odd").addClass("odd");
					jQuery(".research-area:even").addClass("even");
				});
			');
		?>

		<style>
			#research_links-page .help_content .hidden {display: none;	margin: 0 10px;}
			#research_links-page h4 {font-weight:700; margin: 10px 12px;}
			#research_links-page h5 {display: inline-block; margin: 0 10px 10px; font-size: 12px; font-style: normal;}
			#research_links-page ul#research_status li.research-area {border: 1px solid #aaa; display: inline-block; float: left;  margin: 0 5px; padding: 10px; width: 270px;}
			#research_links-page ul#research_status span.ui-icon {display: inline-block; vertical-align: middle;}
			#research_links-page button {margin: 12px; padding: 5px 10px;}
			#research_links-page form {display: inline;}
			#research_links {overflow: hidden; margin-bottom: 20px;}
			.chart_options.check-boxes {margin:0 20px 12px 20px;}
			.chart_options.check-boxes .select-all {margin: 0 40px 12px 40px;}
			.chart_options.check-boxes span {margin: 0 20px;}
			.chart_options.check-boxes input {width: auto;}
			.chart_options.check-boxes label {display: inline-block; vertical-align: top;}
		</style>
		<div id="research_links-page">
			<h2>
				<?php echo $controller->getPageTitle(); ?>
				<?php if (WT_USER_IS_ADMIN) { ?>
					<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config" target="_blank" rel="noopener noreferrer" class="noprint">
						<i class="fa fa-cog"></i>
					</a>
				<?php } ?>
			</h2>
			<div class="help_text">
				<div class="help_content">
					<h5><?php echo $this->getDescription(); ?></h5>
					<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
					<div class="hidden">
						<?php echo /* I18N: help for resource links page */ WT_I18N::translate('You can use this page to search external databases for either an existing person in the family tree, or the names of any person you have not yet recorded.'); ?>
					</div>
				</div>
			</div>
			<form name="research_options" id="research_options" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo WT_GEDURL; ?>">
				<input type="hidden" name="action" value="1">
				<div class="chart_options">
					<label for="indi"><?php echo WT_I18N::translate('Existing person'); ?></label>
					<input data-autocomplete-type="INDI" type="text" name="indi" id="indi" value="<?php echo WT_Filter::escapeHtml($indi); ?>" dir="auto">
				</div>

				<h4><?php echo WT_I18N::translate('A person not already added to this family tree'); ?></h4>
				<div class="chart_options">
					<label for="givn"><?php echo WT_I18N::translate('Given name or names'); ?></label>
					<input type="text" name="givn" id="givn" value="<?php echo WT_Filter::escapeHtml($givn); ?>" dir="auto">
				</div>
				<div class="chart_options">
					<label for="surn"><?php echo WT_I18N::translate('Surname'); ?></label>
					<input type="text" name="surn" id="surn" value="<?php echo WT_Filter::escapeHtml($surn); ?>" dir="auto">
				</div>
				<div class="chart_options">
					<label for="sdate"><?php echo WT_I18N::translate('Start date'); ?></label>
					<input type="text" name="sdate" id="sdate" value="<?php echo WT_Filter::escapeHtml($sdate); ?>" dir="auto">
				</div>
				<div class="chart_options">
					<label for="edate"><?php echo WT_I18N::translate('End date'); ?></label>
					<input type="text" name="edate" id="edate" value="<?php echo WT_Filter::escapeHtml($edate); ?>" dir="auto">
				</div>

				<h4><?php echo WT_I18N::translate('Select research areas to use'); ?></h4>
				<div class="chart_options check-boxes">
					<span>
						<input id="select-all" type="checkbox" onclick="toggle_select(this)">
						<label for="select-all" ><?php echo WT_I18N::translate('Select all'); ?></label>
					</span>
					<?php foreach ($all_plugins as $area => $plugins) {
						// reset returns the first value in an array
						// we take the area code from the first plugin in this area
						$area_code = reset($plugins)->getSearchArea(); ?>
						<span>
							<input class='check' type="checkbox" name="area[]" id="area_<?php echo $area_code; ?>"
								<?php if ($sel_area && in_array($area, $sel_area)) {
									echo ' checked="checked"';
								} ?>
							 	 value="<?php echo $area; ?>">
							<label for="area_<?php echo $area_code; ?>"><?php echo $area; ?></label>
						</span>
					<?php } ?>
				</div>
				<div class="clearfloat"></div>
				<button class="btn btn-primary show" type="submit">
					<i class="fa fa-link"></i>
					<?php echo WT_I18N::translate('Show links'); ?>
				</button>
			</form>
			<form method="post" name="rela_form" action="#">
				<input type="hidden" name="reset" value="1">
				<button class="btn btn-primary reset" type="submit">
					<i class="fa fa-refresh"></i>
					<?php echo WT_I18N::translate('Reset'); ?>
				</button>
			</form>
			<hr style="clear:both;">

			<?php if ($action) { ?>
				<div id="research_links">
					<h3><?php echo WT_I18N::translate('Links'); ?></h3>
					<ul id="research_status">
						<?php
						$i = 0;
						$total_enabled_plugins = 0;
						$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
						foreach ($all_plugins as $area => $plugins) {
							if (in_array($area, $sel_area)) {
								$enabled_plugins		 = $this->countEnabledPlugins($plugins, $RESEARCH_PLUGINS, $indi);
								$total_enabled_plugins	 = $total_enabled_plugins + $enabled_plugins;
								ksort($plugins);
								if ($enabled_plugins > 0) { ?>
									<li class="research-area" data-area="<?php echo $area; ?>">
										<!-- reset returns the first value in an array
										// we take the area code from the first plugin in this area -->
										<?php $area_code = reset($plugins)->getSearchArea(); ?>
										<a href="#" class="research-area-title">
											<span class="ui-icon ui-icon-triangle-1-e left"></span>
											<?php echo $area; ?> (<?php echo $enabled_plugins; ?>)
										</a>
										<ul class="research-list">
											<?php
											$i++;
											foreach ($plugins as $label => $plugin) {
												if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) { // 'links_only' excluded
													$value = $RESEARCH_PLUGINS[$label];
												} else {
													$value = '0';
												}
												if ($value == 1) {
													if ($indi) {
														$name			= false; // only use the first fact with a NAME tag.
														$record   		= WT_Person::getInstance($indi, WT_GED_ID);
														$globalfacts	= $record->getGlobalFacts();
														foreach ($globalfacts as $key=>$value) {
															$fact = $value->getTag();
															if ($fact == "NAME" && !$name) {
																$primary = $this->getPrimaryName($value);
																if ($primary) {
																	$name		= true;
																	$data		= $this->setPluginVariables($plugin, $primary, $indi);
																	$link 		= $plugin->create_link($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
																	$sublinks 	= $plugin->create_sublink($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
																}
															}
														}
													} else {
														$data		= $this->setPluginVariables($plugin, null, false, $surn, $givn, $sdate, $edate);
														$link 		= $plugin->create_link($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
														$sublinks 	= $plugin->create_sublink($data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]);
													}
													if ($sublinks) { // 'links_only' excluded ?>
														<li>
															<a class="mainlink" href="<?php echo htmlspecialchars($link); ?>">
																<span class="ui-icon ui-icon-triangle-1-e left"></span>
																<?php echo $plugin->getName(); ?>
																<span title="<?php echo WT_I18N::translate('Pay to view'); ?>"><?php echo $this->getCurrency($plugin); ?></span>
															</a>
															<ul class="sublinks">
																<?php foreach ($sublinks as $sublink) { ?>
																	<li>
																		<a class="research_link" href="<?php echo htmlspecialchars($sublink['link']); ?>" target="_blank" rel="noopener noreferrer">
																			<span class="ui-icon ui-icon-triangle-1-e left"></span>
																			<?php echo $sublink['title']; ?>
																		</a>
																	</li>
																<?php } ?>
															</ul> <!-- sublinks -->
														</li>
													<?php } elseif (!$plugin->createLinkOnly() && !$plugin->createSubLinksOnly()) { // default, excluding 'links_only'
														if (stripos($link, "postresearchform") === false) {
															$alink = 'href="' . htmlspecialchars($link) . '"';
														} else {
															$alink = 'href="javascript:void(0);" onclick="' . htmlspecialchars($link) . '; return false;"';
														} ?>
														<li>
															<a class="research_link" <?php echo $alink; ?> target="_blank" rel="noopener noreferrer">
																<span class="ui-icon ui-icon-triangle-1-e left"></span>
																<?php echo $plugin->getName(); ?>
																<span  title="<?php echo WT_I18N::translate('Pay to view'); ?>"><?php echo $this->getCurrency($plugin); ?></span>
															</a>
														</li>
													<?php }
												}
											} ?>
										</ul> <!-- research-list -->
									<?php } ?>
								</li>
							<?php }
						} ?>
					</ul> <!-- research_status -->
				</div>
			<?php } ?>
		</div>
		<?php
	}

	private function encode($var, $plus) {
		$var = rawurlencode($var);
		return $plus ? str_replace("%20", "+", $var) : $var;
	}

	protected function getPluginList() {
		$plugins 	 = array();
		$dir	 	 = dirname(__FILE__).'/plugins/';
		$dir_handle  = opendir($dir);
		while ($file = readdir($dir_handle)) {
			if (substr($file, -4)=='.php') {
				require dirname(__FILE__) . '/plugins/' . $file;
				$label	= basename($file, ".php");
				$class	= $label . '_plugin';
				$plugin	= new $class;
				$area = self::getSearchAreaName($plugin->getSearchArea());
				$plugins[$area][$label] = $plugin;
			}
		}
		closedir($dir_handle);
		$int		 = WT_I18N::translate("International");
		$pluginlist	 = array_merge(array($int => $plugins[$int]), $plugins);
		return $pluginlist;
	}


	// Based on function print_name_record() in /library/WT/Controller/Individual.php
	private function getPrimaryName(WT_Event $event) {
		if (!$event->canShow()) {
			return false;
		}
		$factrec	= $event->getGedComRecord();
		// Create a dummy record, so we can extract the formatted NAME value from the event.
		$dummy		= new WT_Person('0 @'.$event->getParentObject()->getXref()."@ INDI\n1 DEAT Y\n".$factrec);
		$all_names	= $dummy->getAllNames();
		return $all_names[0];
	}

	private function getSearchAreaName($area) {
		$stats		 = new WT_Stats(WT_GEDCOM);
		$countries	 = $stats->get_all_countries();
		if (array_key_exists($area, $countries)) {
			$area = $countries[$area];
		} else {
			$area = WT_I18N::translate("International");
		}
		return $area;
	}

	private function getCurrency($plugin) {
		if ($plugin->getPaySymbol()) {
			$symbol = ' (' . /* Currency symbol from http://character-code.com/currency-html-codes.php */ WT_I18N::translate('&#36;') . ') ';
		} else {
			$symbol = '';
		}
		return $symbol;
	}

	/* Count the enabled plugins */
	private function countEnabledPlugins($plugins, $RESEARCH_PLUGINS, $indi) {
		$count = 0;
		foreach (array_keys($plugins) as $label) {
			if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) { // 'links_only excluded on research links page'
				$count += intval($RESEARCH_PLUGINS[$label]);
			}
		}
		if (!$indi) {
			foreach($plugins as $area => $plugin) {
				$plugin->createLinkOnly() ? $count = $count - 1 : $count = $count;
				$plugin->createSubLinksOnly() ? $count = $count - 1 : $count = $count;
			}
		}
		return $count;
	}

	private function setPluginVariables($plugin, $primary, $indi, $surn=false, $givn=false, $sdate=false, $edate=false) {
		global $controller;

		if ($indi) {
			$record		= WT_Person::getInstance($indi, WT_GED_ID);
			$givn 		= $this->encode($primary['givn'], $plugin->encode_plus()); // all given names
			$given		= explode(" ", $primary['givn']);
			$first		= $given[0]; // first given name
			$middle		= count($given) > 1 ? $given[1] : ""; // middle name (second given name)
			$surn 		= $this->encode($primary['surn'], $plugin->encode_plus()); // surname without prefix
			$surname	= $this->encode($primary['surname'], $plugin->encode_plus()); // full surname (with prefix)
			$fullname 	= $plugin->encode_plus() ? $givn . '+' .$surname : $givn . '%20' . $surname; // full name
			$prefix		= $surn != $surname ? substr($surname, 0, strpos($surname, $surn) - 1) : ""; // prefix
			if (is_string($indi)) {
				$record		= WT_Person::getInstance($indi, WT_GED_ID);
				$record->getBirthYear() ? $birth_year = $record->getBirthYear() : $birth_year = '';
				$record->getDeathYear() ? $death_year = $record->getDeathYear() : $death_year = '';
				$record->getSex() 		? $gender	  = $record->getSex() : $gender = '';
			} else {
				$controller->record->getBirthYear() ? $birth_year = $controller->record->getBirthYear() : $birth_year = '';
				$controller->record->getDeathYear() ? $death_year = $controller->record->getDeathYear() : $death_year = '';
				$controller->record->getSex() 		? $gender	  = $controller->record->getSex() : $gender = '';
			}
		} else {
			$givn 		= $givn; // all given names
			$first		= $givn; // first given name
			$middle		= ''; // not used
			$surn 		= $surn; // surname without prefix
			$surname	= $surn; // full surname (with prefix)
			$fullname 	= $plugin->encode_plus() ? $givn . '+' .$surname : $givn . '%20' . $surname; // full name
			$prefix		= ''; // not used
			$birth_year = $sdate;
			$death_year = $edate;
			$gender		= ''; // not used
		}

		return array($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender);

	}

	private function getJavaScript($style) {

		// check at least one area selected and person details entered
		$js='
			jQuery("button.show").on("click", function() {
				var check = [];
				var check1 = 0;
				jQuery(".chart_options input[type=text]").each(function() {
					check1 = check1 + jQuery(this).val().length;
				});
				if (check1 === 0) {
					check.push("' . WT_I18N::translate('You must enter details of a person to search for.') . '\n\n");
				}
				var check2 = jQuery("div.check-boxes :checkbox:checked").length;
				if (!check2) {
					check.push("' . WT_I18N::translate('You must select at least one research area to use.') . '\n\n");
				}
				if (check.length > 0 ) {
					alert(check.join(""));
					return false;
				}
			});

			// expand the default search area
			jQuery(".research-list").first().css("display", "' . $style . '");
			jQuery(".research-area").each(function(){
				if (jQuery(this).data("area") === "' . get_module_setting($this->getName(), 'RESEARCH_PLUGINS_DEFAULT_AREA') . '") {
					jQuery(".research-list").css("display", "none");
					jQuery(this).find(".research-list").css("display", "' . $style . '");
				}
			});

			jQuery("#research_status").on("click", ".research-area-title", function(e){
				e.preventDefault();
				jQuery(this).next(".research-list").slideToggle()
				jQuery(this).parent().siblings().find(".research-list").slideUp();
			});

			jQuery("#research_status a.mainlink").click(function(e){
				e.preventDefault();
				jQuery(this).parent().find(".sublinks").toggle();
			});

			// function for use by research links which need a javascript form submit
			// source: see http://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit
			// thanks: JustCarmen (http://www.justcarmen.nl/fancy-modules/fancy-research-links/)
			// usage: see freebmd, onlinebegraafplaatsen, or metagenealogy plugin for examples
			function postresearchform(url, params) {
				var form = document.createElement("form");

				for (var key in params) {
					if(params.hasOwnProperty(key)) {
						var hiddenField = document.createElement("input");
						hiddenField.setAttribute("type", "hidden");
						hiddenField.setAttribute("name", key);
						hiddenField.setAttribute("value", params[key]);
						form.appendChild(hiddenField);
					 }
				}

				form.setAttribute("method", "post");
				form.setAttribute("action", url);
				form.setAttribute("target", "_blank");

				document.body.appendChild(form);
				form.submit();
			};
		';

		return $js;

	}
}

// Each plugin should extend the base_plugin class, and implement any functions included here
class research_base_plugin {
}
