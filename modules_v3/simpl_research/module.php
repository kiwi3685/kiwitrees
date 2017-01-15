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
			->restrictAccess(WT_USER_IS_ADMIN)
			->setPageTitle($this->getSidebarTitle())
			->pageHeader()
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				var oTable = jQuery("#simpl_research_links").dataTable( {
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
					<table id="simpl_research_links" style="width: 100%;">
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
			$controller->addInlineJavascript('
				jQuery("#' . $this->getName() . ' a:first").text("' . $this->getSidebarTitle() . '");

				// expand the default search area
				jQuery(".research-area").each(function(){
					if (jQuery(this).data("area") === "' . get_module_setting($this->getName(), 'RESEARCH_PLUGINS_DEFAULT_AREA') . '") {
						jQuery(this).find(".research-list").css("display", "block");
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
			');

			$globalfacts = $controller->getGlobalFacts();
			$html = '<ul id="research_status">';
				$i = 0;
				$total_enabled_plugins = 0;
				$other_surname = WT_Filter::post('other_surname');
				$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
				foreach ($this->getPluginList() as $area => $plugins) {
					$enabled_plugins		 = $this->countEnabledPlugins($plugins, $RESEARCH_PLUGINS);
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
													$controller->record->getBirthYear() ? $birth_year	= $controller->record->getBirthYear() : $birth_year = '';
													$controller->record->getDeathYear() ? $death_year	= $controller->record->getDeathYear() : $death_year = '';
													$link 		= $plugin->create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year);
													$sublinks 	= $plugin->create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year);
													$links_only = $plugin->createLinkOnly();
												}
											}
										}
										if ($sublinks || $links_only) {
											$links_only ? $sublinks = $links_only : $sublinks = $sublinks;
											$html .= '<li>
												<a class="mainlink" href="'.htmlspecialchars($link).'">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . '
													<span title="' . WT_I18N::translate('Pay to view') . '">' . $this->getCurrency($plugin) . '</span>';
													if ($links_only) {
														$html .= ' (<i class="fa fa-link" style="font-size: 1em; margin:0;" title="' . WT_I18N::translate('Links only') . '"></i>) ';
													}
												$html .= '</a>
												<ul class="sublinks">';
													foreach ($sublinks as $sublink) {
														$html .= '<li>
																<a class="research_link" href="'.htmlspecialchars($sublink['link']).'" target="_blank" rel="noopener noreferrer">
																	<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
																	$sublink['title'].'
																</a>
														</li>';
													}
												$html .= '</ul>
											</li>';
										} else { // default
											if (stripos($link, "postresearchform") === false){
												$alink = 'href="' . htmlspecialchars($link) . '"';
											} else {
												$alink = 'href="javascript:void(0);" onclick="' . htmlspecialchars($link) . '; return false;"';
											}
											$html .= '<li>
												<a class="research_link" ' . $alink . ' target="_blank" rel="noopener noreferrer">
													<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
													$plugin->getName() . '
													<span  title="' . WT_I18N::translate('Pay to view') . '">' . $this->getCurrency($plugin) . '</span>
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
