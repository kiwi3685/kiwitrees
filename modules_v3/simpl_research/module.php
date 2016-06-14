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

class simpl_research_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Sidebar {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ WT_I18N::translate('Research links');
	}

	public function getSidebarTitle() {
		return /* Title used in the sidebar */ WT_I18N::translate('Research links');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('A sidebar tool to provide quick links to popular research web sites.');
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

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch($mod_action) {
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
			->requireAdminLogin()
			->setPageTitle($this->getSidebarTitle())
			->pageHeader()
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				var oTable = jQuery("#simpl_research_links").dataTable( {
					"sDom": \'<"H"firl>t\',
					'.WT_I18N::datatablesI18N().',
					"bJQueryUI" 		: true,
					"bAutoWidth" 		: true,
					"aaSorting" 		: [[ 1, "asc" ]],
					"bStateSave" 		: true,
					"bPaginate"			: false,
					"iCookieDuration" 	: 180,
					"aoColumns" : [
						{ bSortable: false, sClass: "center" },
						null,
						null,
						{ sClass: "center" },
					]
				});
			');

		if (WT_Filter::postBool('save')) {
			set_module_setting($this->getName(), 'RESEARCH_PLUGINS', serialize(WT_Filter::post('NEW_RESEARCH_PLUGINS')));
			AddToLog($this->getTitle().' config updated', 'config');
		}

		$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
		$html = '
			<div id="' . $this->getName() . '">
				<h2>'.$controller->getPageTitle().'</h2>
				<h3>' . WT_I18N::translate('Select the links you want to use in the sidebar') . '</h3>
				<form method="post" name="configform" action="' . $this->getConfigLink() . '">
					<input type="hidden" name="save" value="1">
					<h3>' . WT_I18N::translate('Select all') .'
						<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
					</h3>
					<table id="simpl_research_links" style="width: 100%;">
						<thead>
							<th> ' . WT_I18N::translate('Enabled') . '</th>
							<th> ' . WT_I18N::translate('Name') . '</th>
							<th> ' . WT_I18N::translate('Area') . '</th>
							<th> ' . WT_I18N::translate('Pay to view') . '</th>
						</thead>
						<tbody>';
							foreach ($this->getPluginList() as $area => $plugins) {
								foreach ($plugins as $label => $plugin) {
									if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) {
										$enabled = $RESEARCH_PLUGINS[$label];
									} else {
										$enabled = '0';
									}

									$html .= '
										<tr>
											<td>' . checkbox('NEW_RESEARCH_PLUGINS['  .$label . ']', $enabled, ' class="check"') .' </td>
											<td>' . $plugin->getName() .' </td>
											<td>' . $area .' </td>
											<td>' . $this->getCurrency($plugin) .' </td>
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
			$controller->addInlineJavascript('
				jQuery("#'.$this->getName().' a").text("'.$this->getSidebarTitle().'");
				jQuery("#research_status").on("click", ".research-area-title", function(e){
					e.preventDefault();
					jQuery(this).next(".research-list").slideToggle()
					jQuery(this).parent().siblings().find(".research-list").slideUp();
				});
				jQuery("#research_status a.mainlink").click(function(e){
					e.preventDefault();
					jQuery(this).parent().find(".sublinks").toggle();
				});
			');
			$globalfacts = $controller->getGlobalFacts();
			$html = '<ul id="research_status">';
				if (WT_USER_GEDCOM_ADMIN) {
					$html .= '<a class="config_link fa fa-cog icon-admin" href="' . $this->getConfigLink() . '" title="' . WT_I18N::translate('Configure') .'">&nbsp;</a>';
				}
				$i = 0;
				$total_enabled_plugins = 0;
				$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
				$link = '';
				$sublinks = '';
				foreach ($this->getPluginList() as $area => $plugins) {
					$enabled_plugins		 = $this->countEnabledPlugins($plugins, $RESEARCH_PLUGINS);
					$total_enabled_plugins	 = $total_enabled_plugins + $enabled_plugins;
					ksort($plugins);
					if ($enabled_plugins > 0) {
						$html .= '
						<li class="research-area">
							<a href="#" class="research-area-title">
								<span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e"></span>
								' . $area . ' (' . $enabled_plugins . ')' . '
							</a>
							<ul class="research-list'; $html .= ($i == 0 ? ' first' : ''); $html .= '">';
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
													$name = true;
													// create plugin vars
													$givn 		= $this->encode($primary['givn'], $plugin->encode_plus()); // all given names
													$given		= explode(" ", $primary['givn']);
													$first		= $given[0]; // first given name
													$middle		= count($given) > 1 ? $given[1] : ""; // middle name (second given name)
													$surn 		= $this->encode($primary['surn'], $plugin->encode_plus()); // surname without prefix
													$surname	= $this->encode($primary['surname'], $plugin->encode_plus()); // full surname (with prefix)
													$fullname 	= $plugin->encode_plus() ? $givn.'+'.$surname : $givn.'%20'.$surname; // full name
													$prefix		= $surn != $surname ? substr($surname, 0, strpos($surname, $surn) - 1) : ""; // prefix
													if ($controller->record->getBirthYear()) {
														$birth_year	= $controller->record->getBirthYear();
													} else {
														$birth_year = '';
													}
													$link 		= $plugin->create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year);
													$sublinks 	= $plugin->create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year);
												}
											}
										}
										if ($sublinks) {
											$html .= '<li>
												<a class="mainlink" href="'.htmlspecialchars($link).'">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . $this->getCurrency($plugin) . '
												</a>
												<ul class="sublinks">';
													foreach ($sublinks as $sublink) {
														$html .= '
															<li>
																<a class="research_link" href="'.htmlspecialchars($sublink['link']).'" target="_blank">
																	<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
																	$sublink['title'].'
																</a>
															</li>
														';
													}
												$html .= '</ul>
											</li>';
										} else { // default
											$html .= '<li>
												<a class="research_link" href="'.htmlspecialchars($link).'" target="_blank">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . $this->getCurrency($plugin) . '
												</a>
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
	private function countEnabledPlugins($plugins, $RESEARCH_PLUGINS) {
		$count = 0;
		foreach (array_keys($plugins) as $label) {
			if (is_array($RESEARCH_PLUGINS) && array_key_exists($label, $RESEARCH_PLUGINS)) {
				$count += intval($RESEARCH_PLUGINS[$label]);
			}
		}
		return $count;
	}
}

// Each plugin should extend the base_plugin class, and implement any functions included here
class research_base_plugin {
}
