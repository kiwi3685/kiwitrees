<?php
/*
 * webtrees - simpl_research sidebar module
 * 
 * Copyright (C) 2013 Nigel Osborne and kiwtrees.net. All rights reserved.
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2013 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class simpl_research_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Sidebar {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ WT_I18N::translate('Simpl_research links');
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
		case 'admin_reset':
			$this->research_reset();
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Reset all settings to default
	private function research_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'RESEARCH%'")->execute();
		AddToLog($this->getTitle().' reset to default values', 'config');
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
			->setPageTitle(getSidebarTitle())
			->pageHeader();

		if (WT_Filter::postBool('save')) {
			set_module_setting($this->getName(), 'RESEARCH_PLUGINS',  serialize(WT_Filter::post('NEW_RESEARCH_PLUGINS')));				
			AddToLog($this->getTitle().' config updated', 'config');
		}			

		$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));			
		$html = '	
			<h2>'.$controller->getPageTitle().'</h2>
			<form method="post" name="configform" action="'.$this->getConfigLink().'">						
				<input type="hidden" name="save" value="1">
				<h3>'.WT_I18N::translate('Check the plugins you want to use in the sidebar').'</h3>';
				foreach ($this->getPluginList() as $plugin) {
					if (is_array($RESEARCH_PLUGINS) && (array_key_exists($plugin->getName(), $RESEARCH_PLUGINS))) $value = $RESEARCH_PLUGINS[$plugin->getName()];
					if(!isset($value)) $value = '1';
					$html .= '<div class="field">'.two_state_checkbox('NEW_RESEARCH_PLUGINS['.$plugin->getName().']', $value).'<label>'.$plugin->getName().'</label></div>';
				}
				$html .= '
					<div class="buttons">
						<input type="submit" value="'.WT_I18N::translate('Save').'" />
						<input type="reset" value="'.WT_I18N::translate('Reset').'" onclick="if (confirm(\''.WT_I18N::translate('The settings will be reset to default. Are you sure you want to do this?').'\')) window.location.href=\'module.php?mod='.$this->getName().'&amp;mod_action=admin_reset\';"/>
					</div>
			</form>';
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
			$globalfacts=$controller->getGlobalFacts();
			$html = '<ul id="research_status">';
			$RESEARCH_PLUGINS = unserialize(get_module_setting($this->getName(), 'RESEARCH_PLUGINS'));
			foreach ($this->getPluginList() as $plugin) {
				if(!isset($value)) $value = '1';
				if($value == true) {
					foreach ($globalfacts as $key=>$value) {
						$fact = $value->getTag();
						if ($fact=="NAME") {
							$primary = $this->getPrimaryName($value);
							if($primary) {

								// create plugin vars						
								$givn 		= $primary['givn'];
								$given		= explode(" ", $givn);
								$first		= $given[0];
								$middle		= count($given) > 1 ? $given[1] : "";
								$surn 		= $primary['surn'];
								$surname	= $primary['surname'];
								$fullname 	= $givn.' '.$surname;
								$prefix		= $surn != $surname ? substr($surname, 0, strpos($surname, $surn) - 1) : "";

								$link = $plugin->create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname);
								$sublinks = $plugin->create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname);
							}
						}
					}
					if($sublinks) {
						$html.='<li><span class="ui-icon ui-icon-triangle-1-e left"></span><a class="mainlink" href="'.$link.'">'.$plugin->getName().'</a>';
						$html .= '<ul class="sublinks">';
						foreach ($sublinks as $sublink) {
							$html.='<li><span class="ui-icon ui-icon-triangle-1-e left"></span><a class="research_link" href="'.$sublink['link'].'" target="_blank">'.$sublink['title'].'</a></li>';
						}
						$html .= '</ul></li>';
					}
					else { // default
						$html.='<li><span class="ui-icon ui-icon-triangle-1-e left"></span><a class="research_link" href="'.$link.'" target="_blank">'.$plugin->getName().'</a></li>';
					}
				}
			}
			$html.= '</ul>';
			return $html;
		}
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

