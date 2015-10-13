<?php
// Classes and libraries for module system
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
		return 9;
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
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Configuration page
	private function config() {
		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page;
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getSidebarTitle())
			->pageHeader();

		if (WT_Filter::postBool('save')) {
			set_module_setting($this->getName(), 'RESEARCH_PLUGINS', serialize(WT_Filter::post('NEW_RESEARCH_PLUGINS')));
			AddToLog($this->getTitle().' config updated', 'config');
		}

		$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
		$html = '
			<div id="' . $this->getName() . '">
				<h2>'.$controller->getPageTitle().'</h2>
				<h3>' . WT_I18N::translate('Check the plugins you want to use in the sidebar') . '</h3>
				<form method="post" name="configform" action="'.$this->getConfigLink().'">
					<input type="hidden" name="save" value="1">
					<h4>' . WT_I18N::translate('Select all') .'
						<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
					</h4>
					<div id="simpl_research_links">';
						foreach ($this->getPluginList() as $plugin) {
							if (is_array($RESEARCH_PLUGINS) && array_key_exists(get_class($plugin), $RESEARCH_PLUGINS)) {
								$value = $RESEARCH_PLUGINS[get_class($plugin)];
							} else {
								$value = '0';
							}
							$html .= '
								<div class="field">' .
									checkbox('NEW_RESEARCH_PLUGINS['  .get_class($plugin) . ']', $value, ' class="check"').'
									<label>'.
										$plugin->getName().'
									</label>
								</div>
							';
						}
					$html .= '
						</div>
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
				jQuery("#research_status a.mainlink").click(function(e){
					e.preventDefault();
					jQuery(this).parent().find(".sublinks").toggle();
				});
			');
			$globalfacts = $controller->getGlobalFacts();
			$html = '<ul id="research_status">';
				$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
				$link = '';
				$sublinks = '';
				foreach ($this->getPluginList() as $plugin) {
					if(is_array($RESEARCH_PLUGINS) && array_key_exists(get_class($plugin), $RESEARCH_PLUGINS)) {
						$value = $RESEARCH_PLUGINS[get_class($plugin)];
					} else {
						 $value = '0';
					}
					if($value == 1) {
						$name = false; // only use the first fact with a NAME tag.
						foreach ($globalfacts as $key=>$value) {
							$fact = $value->getTag();
							if ($fact == "NAME" && !$name) {
								$primary = $this->getPrimaryName($value);
								if($primary) {
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
									$link 		= $plugin->create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname);
									$sublinks 	= $plugin->create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname);
								}
							}
						}
						if($sublinks) {
							$html .= '
								<li>
									<a class="mainlink" href="'.htmlspecialchars($link).'">
										<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
										$plugin->getName().'
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
								</li>
							';
						}
						else { // default
							$html .= '
								<li>
									<a class="research_link" href="'.htmlspecialchars($link).'" target="_blank">
										<span class="ui-icon ui-icon-triangle-1-e left"></span>'.
										$plugin->getName().'
									</a>
								</li>
							';
						}
					}
				}
			$html .=  '</ul>';
			return $html;
		}
	}

	private function encode($var, $plus) {
		$var = rawurlencode($var);
		return $plus ? str_replace("%20", "+", $var) : $var;
	}

 	// Scan the plugin folder for a list of plugins
	private function getPluginList() {
		$array=array();
		$dir=dirname(__FILE__).'/plugins/';
		$dir_handle=opendir($dir);
		while ($file=readdir($dir_handle)) {
			if (substr($file, -4)=='.php') {
				require dirname(__FILE__).'/plugins/'.$file;
				$class=basename($file, '.php').'_plugin';
				$array[$class]=new $class;
			}
		}
		closedir($dir_handle);
		ksort($array);
		return $array;
	}

	// Based on function print_name_record() in /library/WT/Controller/Individual.php
	private function getPrimaryName(WT_Event $event) {
		if (!$event->canShow()) {
			return false;
		}
		$factrec = $event->getGedComRecord();
		// Create a dummy record, so we can extract the formatted NAME value from the event.
		$dummy=new WT_Person('0 @'.$event->getParentObject()->getXref()."@ INDI\n1 DEAT Y\n".$factrec);
		$all_names=$dummy->getAllNames();
		return $all_names[0];
	}

}

// Each plugin should extend the base_plugin class, and implement any functions included here
class research_base_plugin {
}
