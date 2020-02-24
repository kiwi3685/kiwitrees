<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

global $GM_API_KEY;
$GM_API_KEY = get_module_setting('googlemap', 'GM_API_KEY', ''); // Optional Google Map API key
if ($GM_API_KEY) {$key = '&key=' . $GM_API_KEY; } else {$key = '';}
define('KT_GM_SCRIPT', 'https://maps.google.com/maps/api/js?v=3&amp;language=' . KT_LOCALE . $key);

// http://www.google.com/permissions/guidelines.html
//
// "... an unregistered Google Brand Feature should be followed by
// the superscripted letters TM or SM ..."
//
// Hence, use "Google Maps™"
//
// "... Use the trademark only as an adjective"
//
// "... Use a generic term following the trademark, for example:
// GOOGLE search engine, Google search"
//
// Hence, use "Google Maps™ mapping service" where appropriate.

class googlemap_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_Tab, KT_Module_Chart {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: The name of a module.  Google Maps™ is a trademark.  Do not translate it? http://en.wikipedia.org/wiki/Google_maps */ KT_I18N::translate('Google Maps™');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Google Maps™” module */ KT_I18N::translate('Show the location of places and events using the Google Maps™ mapping service.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$this->config();
			break;
		case 'admin_flags':
			$this->flags();
			break;
		case 'pedigree_map':
			$this->pedigree_map();
			break;
		case 'admin_placecheck':
			$this->admin_placecheck();
			break;
		case 'admin_places':
		case 'admin_places_edit':
			// TODO: these files should be methods in this class
			require KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
			require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
			require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/' . $mod_action . '.php';
			break;
		case 'street_view':
			$this->street_view();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref = $controller->getSignificantIndividual()->getXref();
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=pedigree_map&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-chart-pedigree_map'
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 60;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		ob_start();
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/googlemap.php';
		require_once KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		setup_map();
		return ob_get_clean();
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $controller;

		if ($this->checkMapData()) {
			ob_start();
			require_once KT_ROOT.KT_MODULES_DIR.'googlemap/googlemap.php';
			require_once KT_ROOT.KT_MODULES_DIR.'googlemap/defaultconfig.php';
			echo '<link type="text/css" href ="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/css/googlemap.css" rel="stylesheet">';
			if (KT_USER_IS_ADMIN) {
				echo '
					<p style="margin:-10px 0 0 0; padding-bottom:2px;">
						<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_config"><i style="margin: 0 3px 0 10px;" class="icon-config_maps">&nbsp;</i>', KT_I18N::translate('Google Maps™ preferences'), '</a></span>
						<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_places"><i style="margin: 0 3px 0 10px;" class="icon-edit_maps">&nbsp;</i>', KT_I18N::translate('Geographic data'), '</a></span>
						<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_placecheck"><i style="margin: 0 3px 0 10px;" class="icon-check_maps">&nbsp;</i>', KT_I18N::translate('Place Check'), '</a></span>
					</p>
				';
			}
			echo '<div id="map_pane"></div>';
			$famids = array();
			$families = $controller->record->getSpouseFamilies();
			foreach ($families as $family) {
				$famids[] = $family->getXref();
			}
			$controller->record->add_family_facts(false);
			build_indiv_map($controller->record->getIndiFacts(), $famids);
			echo '<script>loadMap();</script>';
			return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
		} else {
			$html = '<table class="facts_table">';
			$html .= '<tr><td colspan="2" class="facts_value">'.KT_I18N::translate('No map data for this person');
			$html .= '</td></tr>';
			if (KT_USER_IS_ADMIN) {
				$html .= '<tr><td class="center" colspan="2">';
				$html .= '<a href="module.php?mod=googlemap&amp;mod_action=admin_config">'.KT_I18N::translate('Google Maps™ preferences'). '</a>';
				$html .= '</td></tr>';
			}
			return $html;
		}
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER && (array_key_exists('googlemap', KT_Module::getActiveModules()) || KT_USER_IS_ADMIN);
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	private function config() {
		require KT_ROOT . KT_MODULES_DIR.'googlemap/defaultconfig.php';
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$action = safe_REQUEST($_REQUEST, 'action');

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle(KT_I18N::translate('Google Maps™'))
			->pageHeader()
			->addInlineJavascript('jQuery("#tabs").tabs();');


		if ($action == 'update') {
			set_module_setting('googlemap', 'GM_MAP_TYPE',          $_POST['NEW_GM_MAP_TYPE']);
			set_module_setting('googlemap', 'GM_USE_STREETVIEW',    $_POST['NEW_GM_USE_STREETVIEW']);
			set_module_setting('googlemap', 'GM_MIN_ZOOM',          $_POST['NEW_GM_MIN_ZOOM']);
			set_module_setting('googlemap', 'GM_MAX_ZOOM',          $_POST['NEW_GM_MAX_ZOOM']);
			set_module_setting('googlemap', 'GM_PRECISION_0',       $_POST['NEW_GM_PRECISION_0']);
			set_module_setting('googlemap', 'GM_PRECISION_1',       $_POST['NEW_GM_PRECISION_1']);
			set_module_setting('googlemap', 'GM_PRECISION_2',       $_POST['NEW_GM_PRECISION_2']);
			set_module_setting('googlemap', 'GM_PRECISION_3',       $_POST['NEW_GM_PRECISION_3']);
			set_module_setting('googlemap', 'GM_PRECISION_4',       $_POST['NEW_GM_PRECISION_4']);
			set_module_setting('googlemap', 'GM_PRECISION_5',       $_POST['NEW_GM_PRECISION_5']);
			set_module_setting('googlemap', 'GM_DEFAULT_TOP_VALUE', $_POST['NEW_GM_DEFAULT_TOP_LEVEL']);
			set_module_setting('googlemap', 'GM_COORD',             $_POST['NEW_GM_COORD']);
			set_module_setting('googlemap', 'GM_PLACE_HIERARCHY',   $_POST['NEW_GM_PLACE_HIERARCHY']);
			set_module_setting('googlemap', 'GM_PH_MARKER',         $_POST['NEW_GM_PH_MARKER']);
			set_module_setting('googlemap', 'GM_DISP_SHORT_PLACE',  $_POST['NEW_GM_DISP_SHORT_PLACE']);
			set_module_setting('googlemap', 'GM_API_KEY',  			$_POST['NEW_GM_API_KEY']);

			for ($i = 1; $i <= 9; $i ++) {
				set_module_setting('googlemap', 'GM_PREFIX_'.$i,  $_POST['NEW_GM_PREFIX_'.$i]);
				set_module_setting('googlemap', 'GM_POSTFIX_'.$i, $_POST['NEW_GM_POSTFIX_'.$i]);
			}

			AddToLog('Googlemap config updated', 'config');
			// read the config file again, to set the vars
			require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		}
		?>
		<table id = "gm_config">
			<tr>
				<th>
					<a class="current" href="module.php?mod=googlemap&amp;mod_action=admin_config">
						<?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
					</a>
				</th>
				<th>
					<a href="module.php?mod=googlemap&amp;mod_action=admin_places">
						<?php echo KT_I18N::translate('Geographic data'); ?>
					</a>
				</th>
				<th>
					<a href="module.php?mod=googlemap&amp;mod_action=admin_placecheck">
						<?php echo KT_I18N::translate('Place Check'); ?>
					</a>
				</th>
			</tr>
		</table>

		<form method="post" name="configform" action="module.php?mod=googlemap&mod_action=admin_config">
			<input type="hidden" name="action" value="update">
			<div id="tabs">
				<ul>
				<li><a href="#gm_basic"><span><?php echo KT_I18N::translate('Basic'); ?></span></a></li>
					<li><a href="#gm_advanced"><span><?php echo KT_I18N::translate('Advanced'); ?></span></a></li>
					<li><a href="#gm_ph"><span><?php echo KT_I18N::translate('Place hierarchy'); ?></span></a></li>
				</ul>
				<div id="gm_basic">
					<table class="gm_edit_config">
						<tr>
							<th><?php echo KT_I18N::translate('Default map type'); ?></th>
							<td>
								<select name="NEW_GM_MAP_TYPE">
									<option value="ROADMAP" <?php if ($GOOGLEMAP_MAP_TYPE == "ROADMAP") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Map'); ?></option>
									<option value="SATELLITE" <?php if ($GOOGLEMAP_MAP_TYPE == "SATELLITE") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Satellite'); ?></option>
									<option value="HYBRID" <?php if ($GOOGLEMAP_MAP_TYPE == "HYBRID") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Hybrid'); ?></option>
									<option value="TERRAIN" <?php if ($GOOGLEMAP_MAP_TYPE == "TERRAIN") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Terrain'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php echo /* I18N: http://en.wikipedia.org/wiki/Google_street_view */ KT_I18N::translate('Google Street View™'); ?></th>
							<td><?php echo radio_buttons('NEW_GM_USE_STREETVIEW', array(false=>KT_I18N::translate('hide'),true=>KT_I18N::translate('show')), get_module_setting('googlemap', 'GM_USE_STREETVIEW', '0')); ?></td>
						</tr>
						<tr>
							<th>
								<?php echo KT_I18N::translate('Zoom factor of map'); ?>
							</th>
							<td>
								<?php echo KT_I18N::translate('minimum'); ?>: <select name="NEW_GM_MIN_ZOOM">
								<?php for ($j=1; $j<15; $j++) { ?>
								<option value="<?php echo $j, "\""; if ($GOOGLEMAP_MIN_ZOOM == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
								<?php } ?>
								</select>
								<?php echo KT_I18N::translate('maximum'); ?>: <select name="NEW_GM_MAX_ZOOM">
								<?php for ($j=1; $j<21; $j++) { ?>
								<option value="<?php echo $j, "\""; if ($GOOGLEMAP_MAX_ZOOM == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
								<?php } ?>
								</select>
								<p class="help_content">
									<?php echo KT_I18N::translate('Minimum and maximum zoom factor for the Google map. 1 is the full map, 15 is single house. Note that 15 is only available in certain areas.'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><?php echo /* I18N: Optional Google Map API key */ KT_I18N::translate('Google Maps™ API key'); ?></th>
							<td>
								<input type="text" name="NEW_GM_API_KEY" value="<?php echo $GM_API_KEY; ?>" size="50">
								<p class="help_content">
									<?php echo KT_I18N::translate('<b>Optional</b>. Google prefers that users of Google Maps™ obtain an API key from them. This is linked to their usage restrictions described at https://developers.google.com/maps/documentation/geocoding/usage-limits. The same page has a link to get a key. You can continue to use the maps feature without the API key if you do not exceed the restrictions but a warning message will exist in the source code of your web page.'); ?>
								</p>
							</td>
						</tr>
					</table>
				</div>

				<div id="gm_advanced">
					<table class="gm_edit_config">
						<tr>
							<th colspan="2"><?php echo KT_I18N::translate('Precision of the latitude and longitude'); ?></th>
							<td>
								<table>
									<tr>
										<td><?php echo KT_I18N::translate('Country'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_0">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_0 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo KT_I18N::translate('State'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_1">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_1 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo KT_I18N::translate('City'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_2">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_2 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
									<tr><td><?php echo KT_I18N::translate('Neighborhood'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_3">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_3 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
									<tr><td><?php echo KT_I18N::translate('House'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_4">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_4 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
									<tr>
										<td><?php echo KT_I18N::translate('Max'); ?>&nbsp;&nbsp;</td>
										<td><select name="NEW_GM_PRECISION_5">
											<?php for ($j=0; $j<10; $j++) { ?>
											<option value="<?php echo $j; ?>"<?php if ($GOOGLEMAP_PRECISION_5 == $j) echo " selected=\"selected\""; echo ">", $j; ?></option>
											<?php } ?>
											</select>&nbsp;&nbsp;<?php echo KT_I18N::translate('digits'); ?>
										</td>
									</tr>
								</table>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<span class="help_content">
									<?php echo KT_I18N::translate('This specifies the precision of the different levels when entering new geographic locations. For example a country will be specified with precision 0 (=0 digits after the decimal point), while a town needs 3 or 4 digits.'); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th colspan="2"><?php echo KT_I18N::translate('Default value for top-level'); ?></th>
							<td><input type="text" name="NEW_GM_DEFAULT_TOP_LEVEL" value="<?php echo $GM_DEFAULT_TOP_VALUE; ?>" size="20"></td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="4">
								<span class="help_content">
									<?php echo KT_I18N::translate('Here the default level for the highest level in the place-hierarchy can be defined. If a place cannot be found this name is added as the highest level (country) and the database is searched again.'); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th class="gm_prefix" colspan="3"><?php echo KT_I18N::translate('Optional prefixes and suffixes');?></th>
						</tr>
						<tr>
							<td colspan="4">
								<span class="help_content">
									<?php echo KT_I18N::translate('Some place names may be written with optional prefixes and suffixes.  For example “Orange” versus “Orange County”.  If the family tree contains the full place names, but the geographic database contains the short place names, then you should specify a list of the prefixes and suffixes to be disregarded.  Multiple options should be separated with semicolons.  For example “County;County of” or “Township;Twp;Twp.”.'); ?>
								</span>
							</td>
						</tr>
						<tr id="gm_level_titles">
							<th>&nbsp;</th>
							<th><?php echo KT_I18N::translate('Prefixes'); ?></th>
							<th><?php echo KT_I18N::translate('Suffixes'); ?></th>
						<?php for ($level=1; $level < 10; $level++) { ?>
						<tr  class="gm_levels">
							<th>
								<?php
								if ($level == 1) {
									echo KT_I18N::translate('Country');
								} else {
									echo KT_I18N::translate('Level'), " ", $level;
								}
								?>
							</th>
							<td><input type="text" size="30" name="NEW_GM_PREFIX_<?php echo $level; ?>" value="<?php echo $GM_PREFIX[$level]; ?>"></td>
							<td><input type="text" size="30" name="NEW_GM_POSTFIX_<?php echo $level; ?>" value="<?php echo $GM_POSTFIX[$level]; ?>"></td>
						</tr>
						<?php } ?>
					</table>
				</div>

				<div id="gm_ph">
					<table class="gm_edit_config">
						<tr>
							<th><?php echo KT_I18N::translate('Use Google Maps™ for the place hierarchy'); ?></th>
							<td><?php echo edit_field_yes_no('NEW_GM_PLACE_HIERARCHY', get_module_setting('googlemap', 'GM_PLACE_HIERARCHY', '0')); ?></td>
							<td></td>
						</tr>
						<tr>
							<th><?php echo KT_I18N::translate('Type of place markers in Place Hierarchy'); ?></th>
							<td>
								<select name="NEW_GM_PH_MARKER">
									<option value="G_DEFAULT_ICON" <?php if ($GOOGLEMAP_PH_MARKER == "G_DEFAULT_ICON") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Standard'); ?></option>
									<option value="G_FLAG" <?php if ($GOOGLEMAP_PH_MARKER == "G_FLAG") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Flag'); ?></option>
								</select>
							</td>
							<td></td>
						</tr>
						<tr>
							<th><?php echo KT_I18N::translate('Display short placenames'); ?></th>
							<td><?php echo edit_field_yes_no('NEW_GM_DISP_SHORT_PLACE', $GM_DISP_SHORT_PLACE); ?></td>
							<td>
								<span class="help_content">
									<?php echo KT_I18N::translate('Here you can choose between two types of displaying places names in hierarchy. If set Yes the place has short name or actual level name, if No - full name.<br /><b>Examples:<br />Full name: </b>Chicago, Illinois, USA<br /><b>Short name: </b>Chicago<br /><b>Full name: </b>Illinois, USA<br /><b>Short name: </b>Illinois'); ?>
								</span>
							</td>
						</tr>
						<tr>
							<th><?php echo KT_I18N::translate('Display Map Coordinates'); ?></th>
							<td><?php echo edit_field_yes_no('NEW_GM_COORD', $GOOGLEMAP_COORD); ?></td>
							<td>
								<span class="help_content">
									<?php echo KT_I18N::translate('This options sets whether Latitude and Longitude are displayed on the pop-up window attached to map markers.'); ?>
								</span>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<p>
				<button class="btn btn-primary" type="submit">
				<i class="fa fa-floppy-o"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
			</p>
		</form>
		<?php
	}

	private function flags() {
		require KT_ROOT . KT_MODULES_DIR . 'googlemap/defaultconfig.php';
		require KT_ROOT . 'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Page();
		$controller
			->setPageTitle(KT_I18N::translate('Select flag'))
			->pageHeader();

		$stats				= new KT_Stats(KT_GEDCOM);
		$countries			= $stats->get_all_countries();
		$action				= safe_REQUEST($_REQUEST, 'action');
 		$countrySelected	= KT_Filter::get('countrySelected', null, 'Countries');
 		$stateSelected		= KT_Filter::get('stateSelected',   null, 'States');

		$country = array();
		if (is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/flags')) {
			$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/flags');
			while ($file = readdir($rep)) {
				if (stristr($file, '.png')) {
					$country[] = substr($file, 0, strlen($file) - 4);
				}
 			}
			closedir($rep);
			sort($country);
		}

		if ($countrySelected == 'Countries') {
			$flags = $country;
		} else {
			$flags = array();
			if (is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags')) {
				$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags');
				while ($file = readdir($rep)) {
					if (stristr($file, '.png')) {
						$flags[] = substr($file, 0, strlen($file) - 4);
					}
				}
				closedir($rep);
				sort($flags);
			}
		}
		// Sort flags into alpha list after transaltion
		$flag_list = array();
		foreach ($flags as $flag) {
			if (array_key_exists($flag, $countries)) {
				$flag_list[$flag] = $countries[$flag];
			} else {
				$flag_list[$flag] = $flag;
			}
		}
		uasort($flag_list, "utf8_strcasecmp");

		$flags_s = array();
		if ($stateSelected != 'States' && is_dir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected)) {
			$rep = opendir(KT_ROOT . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected);
			while ($file = readdir($rep)) {
				if (stristr($file, '.png')) {
					$flags_s[] = substr($file, 0, strlen($file)-4);
				}
			}
			closedir($rep);
			sort($flags_s);
		}

		if ($action == 'ChangeFlag' && KT_Filter::post('FLAGS')) {
		?>
			<script>
		<?php if (KT_Filter::post('selcountry') == 'Countries') { ?>
					window.opener.document.editplaces.icon.value = 'places/flags/<?php echo KT_Filter::post('FLAGS'); ?>.png';
					window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL, KT_MODULES_DIR; ?>googlemap/places/flags/<?php echo KT_Filter::post('FLAGS'); ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
		<?php } elseif (KT_Filter::post('selstate') != "States"){ ?>
					window.opener.document.editplaces.icon.value = 'places/<?php echo $countrySelected, '/flags/', $_POST['selstate'], '/', $flags_s[$_POST['FLAGS']]; ?>.png';
					window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL, KT_MODULES_DIR; ?>googlemap/places/<?php echo $countrySelected, "/flags/", $_POST['selstate'], "/", $flags_s[$_POST['FLAGS']]; ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
		<?php } else { ?>
					window.opener.document.editplaces.icon.value = "places/<?php echo $countrySelected, "/flags/", KT_Filter::post('FLAGS'); ?>.png";
					window.opener.document.getElementById('flagsDiv').innerHTML = "<img src=\"<?php echo KT_STATIC_URL, KT_MODULES_DIR; ?>googlemap/places/<?php echo $countrySelected, "/flags/", KT_Filter::post('FLAGS'); ?>.png\">&nbsp;&nbsp;<a href=\"#\" onclick=\"change_icon();return false;\"><?php echo KT_I18N::translate('Change flag'); ?></a>&nbsp;&nbsp;<a href=\"#\" onclick=\"remove_icon();return false;\"><?php echo KT_I18N::translate('Remove flag'); ?></a>";
		<?php } ?>
					window.opener.updateMap();
					window.close();
			</script>
		<?php
			exit;
		} else {
		?>
		<script>
			function selectCountry() {
				if (document.flags.COUNTRYSELECT.value == 'Countries') {
					window.location="module.php?mod=googlemap&mod_action=admin_flags";
				} else if (document.flags.STATESELECT.value != 'States') {
					window.location="module.php?mod=googlemap&mod_action=admin_flags&countrySelected=" + document.flags.COUNTRYSELECT.value + "&stateSelected=" + document.flags.STATESELECT.value;
				} else {
					window.location="module.php?mod=googlemap&mod_action=admin_flags&countrySelected=" + document.flags.COUNTRYSELECT.value;
				}
			}
		</script>
		<?php
		}
		$countryList = array();
		$placesDir = scandir(KT_MODULES_DIR.'googlemap/places/');
		for ($i = 0; $i < count($country); $i++) {
			if (count(preg_grep('/' . $country[$i] . '/', $placesDir)) != 0) {
				$rep = opendir(KT_MODULES_DIR.'googlemap/places/'.$country[$i].'/');
				while ($file = readdir($rep)) {
					if (stristr($file, 'flags')) {
						if (isset($countries[$country[$i]])) {
							$countryList[$country[$i]] = $countries[$country[$i]];
						} else {
							$countryList[$country[$i]] = $country[$i];
						}
					}
				}
				closedir($rep);
			}
		}
		asort($countryList);

		$stateList = array();
		if ($countrySelected != 'Countries') {
			$placesDir = scandir(KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/');
			for ($i = 0; $i < count($flags); $i++) {
				if (in_array($flags[$i], $placesDir)) {
					$rep = opendir(KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $flags[$i] . '/');
					while ($file = readdir($rep)) {
						$stateList[$flags[$i]] = $flags[$i];
					}
					closedir($rep);
				}
			}
			asort($stateList);
		}
		?>
		<div id="changeflags-page">
			<h3><?php echo KT_I18N::translate('Change flag'); ?></h3>
			<form method="post" id="flags" name="flags" action="module.php?mod=googlemap&amp;mod_action=admin_flags&amp;countrySelected=<?php echo $countrySelected; ?>&amp;stateSelected=<?php echo $stateSelected; ?>">
				<input type="hidden" name="action" value="ChangeFlag">
				<input type="hidden" name="selcountry" value="<?php echo $countrySelected; ?>">
				<input type="hidden" name="selstate" value="<?php echo $stateSelected; ?>">
				<select name="COUNTRYSELECT" dir="ltr" onchange="selectCountry()">
					<option value="Countries"><?php echo KT_I18N::translate('Countries'); ?></option>
					<?php foreach ($countryList as $country_key=>$country_name) {
						echo '<option value="', $country_key, '"';
						if ($countrySelected == $country_key) echo ' selected="selected" ';
						echo '>', $country_name, '</option>';
					} ?>
				</select>
				<p class="help_text">
					<span class="help_content">
						<?php echo KT_I18N::translate('Using the pull down menu it is possible to select a country, of which a flag can be selected. If no flags are shown, then there are no flags defined for this country.'); ?>
					</span>
				</p>
				<hr>
				<div class="flags_wrapper">
					<?php if ($countrySelected == 'Countries' || count($stateList) == 0) {
						if (count($flag_list) > 50) { // Add second set of save/close buttons ?>
							<p id="save-cancel">
								<button class="btn btn-primary" type="submit">
									<i class="fa fa-save"></i>
									<?php echo KT_I18N::translate('Save'); ?>
								</button>
								<button class="btn btn-primary" type="button" onclick="window.close();">
									<i class="fa fa-times"></i>
									<?php echo KT_I18N::translate('close'); ?>
								</button>
							</p>
						<?php }
					} ?>
					<div class="clearfloat"></div>
					<?php
					foreach ($flag_list as $iso => $name) {
						if ($countrySelected == 'Countries') {
							echo '<div class="flags_item">
								<span>
									<input type="radio" dir="ltr" name="FLAGS" value="' . $iso . '">
									<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/flags/' . $iso . '.png" alt="' . $name . '"  title="' . $iso . '">
								</span>
								<label>' . $name . '</label>
							</div>';
						} else {
							echo '<div class="flags_item">
								<span>
									<input type="radio" dir="ltr" name="FLAGS" value="' . $iso . '">
									<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $iso . '.png">
								</span>
								<label>' . $iso . '</label>
							</div>';
						}
					} ?>
				</div>
				<div <?php echo ($countrySelected == 'Countries' || count($stateList) == 0)  ? 'style=" visibility: hidden"' : ''; ?>>
					<select name="STATESELECT" dir="ltr" onchange="selectCountry()">
						<option value="States"><?php echo /* I18N: Part of a country, state/region/county */ KT_I18N::translate('Subdivision'); ?></option>
						<?php foreach ($stateList as $state_key=>$state_name) {
							echo '<option value="', $state_key, '"';
							if ($stateSelected == $state_key) echo ' selected="selected"';
							echo '>', $state_name, '</option>';
						} ?>
					</select>
					<p class="help_text">
						<span class="help_content">
							<?php echo KT_I18N::translate('Using the pull down menu it is possible to select a state for this country, for which a flag can be selected. If no flags are shown, then there are no flags defined for this state.'); ?>
						</span>
					</p>
					<hr>
				</div>
				<div class="flags_wrapper">
					<?php
					$j=1;
					for ($i=0; $i<count($flags_s); $i++) {
						if ($stateSelected != 'States') {
							echo '
								<div class="flags_item">
									<span>
										<input type="radio" dir="ltr" name="FLAGS" value="', $i, '">
										<img src="' . KT_STATIC_URL . KT_MODULES_DIR . 'googlemap/places/' . $countrySelected . '/flags/' . $stateSelected . '/' . $flags_s[$i], '.png">
									</span>
									<label>', $flags_s[$i], '</label>
								</div>
							';
						}
						$j++;
					} ?>
				</div>
				<p id="save-cancel">
					<button class="btn btn-primary" type="submit">
						<i class="fa fa-save"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
					<button class="btn btn-primary" type="button" onclick="window.close();">
						<i class="fa fa-times"></i>
						<?php echo KT_I18N::translate('close'); ?>
					</button>
				</p>
			</form>
		</div>
	<?php }

	private function pedigree_map() {
		global $controller, $PEDIGREE_GENERATIONS, $MAX_PEDIGREE_GENERATIONS;

		require KT_ROOT.KT_MODULES_DIR.'googlemap/defaultconfig.php';
		require_once KT_ROOT.KT_MODULES_DIR.'googlemap/googlemap.php';

		$controller = new KT_Controller_Pedigree();

		// Start of internal configuration variables
		// Limit this to match available number of icons.
		// 8 generations equals 255 individuals
		$MAX_PEDIGREE_GENERATIONS = min($MAX_PEDIGREE_GENERATIONS, 8);

		// End of internal configuration variables
		$controller
			->setPageTitle(/* I18N: %s is an individual’s name */ KT_I18N::translate('Pedigree map of %s', $controller->getPersonName()))
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();');

		echo '<link type="text/css" href ="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/css/googlemap.css" rel="stylesheet">';
		echo '<div id="pedigreemap-page">
				<h2>', $controller->getPageTitle(), '</h2>';

		// -- print the form to change the number of displayed generations
		?>
		<form name="people" method="get" action="module.php?ged=<?php echo KT_GEDURL; ?>&amp;mod=googlemap&amp;mod_action=pedigree_map">
			<input type="hidden" name="mod" value="googlemap">
			<input type="hidden" name="mod_action" value="pedigree_map">
			<div class="chart_options">
				<label for = "rootid" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Individual'); ?></label>
					<input class="pedigree_form" data-autocomplete-type="INDI" type="text" id="rootid" name="rootid" value="<?php echo $controller->root->getXref(); ?>">
					<?php echo print_findindi_link('rootid'); ?>
			</div>
			<div class="chart_options">
				<label for = "pedigree_generations" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Generations'); ?></label>
				<select name="PEDIGREE_GENERATIONS" id="pedigree_generations">
				<?php
					for ($p=3; $p<=$MAX_PEDIGREE_GENERATIONS; $p++) {
						echo '<option value="', $p, '" ';
						if ($p == $controller->PEDIGREE_GENERATIONS) {
							echo 'selected="selected"';
						}
						echo '>', $p, '</option>';
					}
				?>
				</select>
			</div>
			<button class="btn btn-primary show" type="submit">
	 			<i class="fa fa-eye"></i>
	 			<?php echo KT_I18N::translate('View'); ?>
	 		</button>
		</form>
		<hr style="clear:both;">
		<!-- end of form -->

		<!-- count records by type -->
		<?php
		$curgen=1;
		$priv=0;
		$count=0;
		$miscount=0;
		$missing = '';

		for ($i=0; $i<($controller->treesize); $i++) {
			// -- check to see if we have moved to the next generation
			if ($i+1 >= pow(2, $curgen)) {$curgen++;}
			$person = KT_Person::getInstance($controller->treeid[$i]);
			if (!empty($person)) {
				$name = $person->getFullName();
				if ($name == KT_I18N::translate('Private')) $priv++;
				$place = $person->getBirthPlace();
				if (empty($place)) {
					$latlongval[$i] = NULL;
				} else {
					$latlongval[$i] = get_lati_long_placelocation($person->getBirthPlace());
					if ($latlongval[$i] != NULL && $latlongval[$i]['lati'] == '0' && $latlongval[$i]['long'] == '0') {
						$latlongval[$i] = NULL;
					}
				}
				if ($latlongval[$i] != NULL) {
					$lat[$i] = str_replace(array('N', 'S', ','), array('', '-', '.'), $latlongval[$i]['lati']);
					$lon[$i] = str_replace(array('E', 'W', ','), array('', '-', '.'), $latlongval[$i]['long']);
					if (($lat[$i] != NULL) && ($lon[$i] != NULL)) {
						$count++;
					} else { // The place is in the table but has empty values
						if ($name) {
							if ($missing) {
								$missing .= ', ';
							}
							$missing .= '<a href="' . $person->getHtmlUrl() . '">' . $name . '</a>';
							$miscount++;
						}
					}
				} else { // There was no place, or not listed in the map table
					if ($name) {
						if ($missing) {
							$missing .= ', ';
						}
						$missing .= '<a href="' . $person->getHtmlUrl() . '">' . $name . '</a>';
						$miscount++;
					}
				}
			}
		}
		//<!-- end of count records by type -->
		//<!-- start of map display -->
		echo '<div id="pedigreemap_chart">';
		echo '<table class="tabs_table" cellspacing="0" cellpadding="0" border="0" width="100%">';
		echo '<tr>';
		echo '<td valign="top">';
		echo '<div id="pm_map"><i class="icon-loading-large"></i></div>';
		if (KT_USER_IS_ADMIN) {
			echo '
				<p style="margin:10px 0;">
					<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_config"><i style="margin: 0 3px 0 10px;" class="icon-config_maps">&nbsp;</i>', KT_I18N::translate('Google Maps™ preferences'), '</a></span>
					<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_places"><i style="margin: 0 3px 0 10px;" class="icon-edit_maps">&nbsp;</i>', KT_I18N::translate('Geographic data'), '</a></span>
					<span><a href="module.php?mod=' .$this->getName(). '&amp;mod_action=admin_placecheck"><i style="margin: 0 3px 0 10px;" class="icon-check_maps">&nbsp;</i>', KT_I18N::translate('Place Check'), '</a></span>
				</p>
			';
		}
		echo '</td><td width="15px">&nbsp;</td>';
		echo '<td width="310px" valign="top">';
		echo '<div id="side_bar" style="width:300px; font-size:0.9em; overflow:auto; overflow-x:hidden; overflow-y:auto;"></div></td>';
		echo '</tr>';
		echo '</table>';
		// display info under map
		echo '<hr>';
		echo '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
		echo '<tr>';
		echo '<td valign="top">';
		// print summary statistics
		if (isset($curgen)) {
			$total=pow(2,$curgen)-1;
			$miss=$total-$count-$priv;
			echo KT_I18N::plural(
				'%1$d individual displayed, out of the normal total of %2$d, from %3$d generations.',
				'%1$d individuals displayed, out of the normal total of %2$d, from %3$d generations.',
				$count,
				$count, $total, $curgen
			), '<br>';
			echo '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<td valign="top">';
			if ($priv) {
				echo KT_I18N::plural('%s individual is private.', '%s individuals are private.', $priv, $priv), '<br>';
			}
			if ($count+$priv != $total) {
				if ($miscount == 0) {
					echo KT_I18N::translate('No ancestors in the database.'), "<br>";
				} else {
					echo /* I18N: %1$d is a count of individuals, %2$s is a list of their names */ KT_I18N::plural(
						'%1$d individual is missing birthplace map coordinates: %2$s.',
						'%1$d individuals are missing birthplace map coordinates: %2$s.',
						$miscount, $miscount, $missing),
						'<br>';
				}
			}
		}
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</div>';// close #pedigreemap_chart
		echo '</div>';// close #pedigreemap-page
		?>
		<!-- end of map display -->
		<!-- Start of map scripts -->
		<?php
		echo '<script src="', KT_GM_SCRIPT, '"></script>';
		$controller->addInlineJavascript($this->pedigree_map_js());
	}

	private function pedigree_map_js() {
		global $controller, $SHOW_HIGHLIGHT_IMAGES, $PEDIGREE_GENERATIONS;
		// The HomeControl returns the map to the original position and style
		$js='function HomeControl(controlDiv, pm_map) {'.
			// Set CSS styles for the DIV containing the control
			// Setting padding to 5 px will offset the control from the edge of the map
			'controlDiv.style.paddingTop = "5px";
			controlDiv.style.paddingRight = "0px";'.
			// Set CSS for the control border
			'var controlUI = document.createElement("DIV");
			controlUI.style.backgroundColor = "white";
			controlUI.style.color = "black";
			controlUI.style.borderColor = "black";
			controlUI.style.borderColor = "black";
			controlUI.style.borderStyle = "solid";
			controlUI.style.borderWidth = "2px";
			controlUI.style.cursor = "pointer";
			controlUI.style.textAlign = "center";
			controlUI.title = "";
			controlDiv.appendChild(controlUI);'.
			// Set CSS for the control interior
			'var controlText = document.createElement("DIV");
			controlText.style.fontFamily = "Arial,sans-serif";
			controlText.style.fontSize = "12px";
			controlText.style.paddingLeft = "15px";
			controlText.style.paddingRight = "15px";
			controlText.innerHTML = "<b>'.KT_I18N::translate('Redraw map').'<\/b>";
			controlUI.appendChild(controlText);'.
			// Setup the click event listeners: simply set the map to original LatLng
			'google.maps.event.addDomListener(controlUI, "click", function() {
				pm_map.setMapTypeId(google.maps.MapTypeId.TERRAIN),
				pm_map.fitBounds(bounds),
				pm_map.setCenter(bounds.getCenter()),
				infowindow.close()
				if (document.getElementById(lastlinkid) != null) {
					document.getElementById(lastlinkid).className = "person_box:target";
				}
			});
		}'.
		// This function picks up the click and opens the corresponding info window
		'function myclick(i) {
			if (document.getElementById(lastlinkid) != null) {
				document.getElementById(lastlinkid).className = "person_box:target";
			}
			google.maps.event.trigger(gmarkers[i], "click");
		}'.
		// this variable will collect the html which will eventually be placed in the side_bar
		'var side_bar_html = "";'.
		// arrays to hold copies of the markers and html used by the side_bar
		// because the function closure trick doesnt work there
		'var gmarkers = [];
		var i = 0;
		var lastlinkid;
		var infowindow = new google.maps.InfoWindow({});'.
		// === Create an associative array of GIcons()
		'var gicons = [];
		gicons["1"]        = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon1.png")
		gicons["1"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["2"]         = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2.png")
		gicons["2"].shadow  = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["2L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["2L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["2R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["2R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["2Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["2Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon2Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["3"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3.png")
		gicons["3"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["3L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["3L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["3R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["3R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["3Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["3Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon3Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["4"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4.png")
		gicons["4"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["4L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["4L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["4R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["4R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["4Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["4Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon4Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["5"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5.png")
		gicons["5"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["5L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["5L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["5R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["5R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["5Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["5Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon5Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["6"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6.png")
		gicons["6"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["6L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["6L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["6R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["6R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["6Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["6Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon6Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["7"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7.png")
		gicons["7"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["7L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["7L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["7R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["7R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["7Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["7Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon7Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);
		gicons["8"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8.png")
		gicons["8"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow50.png",
									new google.maps.Size(37, 34), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(10, 34) // Shadow anchor is base of image
								);
		gicons["8L"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8L.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(28, 28) // Image anchor
								);
		gicons["8L"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-left-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(32, 27) // Shadow anchor is base of image
								);
		gicons["8R"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8R.png",
									new google.maps.Size(32, 32), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(4, 28)  // Image anchor
								);
		gicons["8R"].shadow = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/shadow-right-large.png",
									new google.maps.Size(49, 32), // Shadow size
									new google.maps.Point(0, 0),  // Shadow origin
									new google.maps.Point(15, 27) // Shadow anchor is base of image
								);
		gicons["8Ls"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8Ls.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(22, 22) // Image anchor
								);
		gicons["8Rs"] = new google.maps.MarkerImage(KT_STATIC_URL+KT_MODULES_DIR+"googlemap/images/icon8Rs.png",
									new google.maps.Size(24, 24), // Image size
									new google.maps.Point(0, 0),  // Image origin
									new google.maps.Point(2, 22)  // Image anchor
								);'.
		// / A function to create the marker and set up the event window
		'function createMarker(point, name, html, mhtml, icontype) {
			// alert(i+". "+name+", "+icontype);
			var contentString = "<div id=\'iwcontent_edit\'>"+mhtml+"<\/div>";'.
			//create a marker with the requested icon
			'var marker = new google.maps.Marker({
				icon:     gicons[icontype],
				shadow:   gicons[icontype].shadow,
				map:      pm_map,
				position: point,
				zIndex:   0
			});
			var linkid = "link"+i;
			google.maps.event.addListener(marker, "click", function() {
				infowindow.close();
				infowindow.setContent(contentString);
				infowindow.open(pm_map, marker);
				document.getElementById(linkid).className = "person_box";
				if (document.getElementById(lastlinkid) != null) {
					document.getElementById(lastlinkid).className = "person_box:target";
				}
				lastlinkid=linkid;
			});'.
			// save the info we need to use later for the side_bar
			'gmarkers[i] = marker;'.
			// add a line to the side_bar html
			'side_bar_html += "<br><div id=\'"+linkid+"\' onclick=\'myclick(" + i + ")\'>" + html +"<br></div>";
			i++;
			return marker;
		};'.
		// create the map
		'var myOptions = {
			zoom: 6,
			center: new google.maps.LatLng(0, 0),
			mapTypeId: google.maps.MapTypeId.TERRAIN,  // ROADMAP, SATELLITE, HYBRID, TERRAIN
			mapTypeControlOptions: {
				style: google.maps.MapTypeControlStyle.DROPDOWN_MENU  // DEFAULT, DROPDOWN_MENU, HORIZONTAL_BAR
			},
			navigationControlOptions: {
				position: google.maps.ControlPosition.TOP_RIGHT,  // BOTTOM, BOTTOM_LEFT, LEFT, TOP, etc
				style: google.maps.NavigationControlStyle.SMALL   // ANDROID, DEFAULT, SMALL, ZOOM_PAN
			},
			streetViewControl: false,  // Show Pegman or not
			scrollwheel: true
		};
		var pm_map = new google.maps.Map(document.getElementById("pm_map"), myOptions);
		google.maps.event.addListener(pm_map, "maptypechanged", function() {
			map_type.refresh();
		});
		google.maps.event.addListener(pm_map, "click", function() {
			if (document.getElementById(lastlinkid) != null) {
				document.getElementById(lastlinkid).className = "person_box:target";
			}
		infowindow.close();
		});'.
		// Create the DIV to hold the control and call HomeControl() passing in this DIV. --
		'var homeControlDiv = document.createElement("DIV");
		var homeControl = new HomeControl(homeControlDiv, pm_map);
		homeControlDiv.index = 1;
		pm_map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);'.
		// create the map bounds
		'var bounds = new google.maps.LatLngBounds();';
		// add the points
		$curgen=1;
		$priv=0;
		$count=0;
		$event = '<img src="'.KT_STATIC_URL.KT_MODULES_DIR.'googlemap/images/sq1.png" width="10" height="10">'.
			'<strong>&nbsp;'.KT_I18N::translate('Root').':&nbsp;</strong>';
		$colored_line = array('1'=>'#FF0000','2'=>'#0000FF','3'=>'#00FF00',
						'4'=>'#FFFF00','5'=>'#00FFFF','6'=>'#FF00FF',
						'7'=>'#C0C0FF','8'=>'#808000');

		for ($i=0; $i<($controller->treesize); $i++) {
			// moved up to grab the sex of the individuals
			$person = KT_Person::getInstance($controller->treeid[$i]);
			if ($person) {
				$name = $person->getFullName();

				// -- check to see if we have moved to the next generation
				if ($i+1 >= pow(2, $curgen)) {
					$curgen++;
				}
				$relationship=get_relationship_name(get_relationship($controller->root, $person, false, 0));
				if (empty($relationship)) $relationship = KT_I18N::translate('self');
				$event = '<img src=\"' . KT_STATIC_URL.KT_MODULES_DIR . 'googlemap/images/sq' . $curgen . '.png\" width=\"10\" height=\"10\">'.
					'<span class=\"relationship\">' . $relationship . '</span>';
				// add thumbnail image
				if ($SHOW_HIGHLIGHT_IMAGES) {
					$image = $person->displayImage();
				} else {
					$image = '';
				}
				// end of add image

				$dataleft  = addslashes($image) . $event . addslashes($name);
				$datamid   = "<a href='".$person->getHtmlUrl()."' id='alturl' title='" . KT_I18N::translate('Individual information') . "'>";
				$datamid .= '<br>' . KT_I18N::translate('View Person') . '<br>';
				$datamid  .= '</a>';
				$dataright = '<span class=\"event\">' . KT_I18N::translate('Birth') . ' </span>' .
						addslashes($person->getBirthDate()->Display(false)) . '<br>' . $person->getBirthPlace();

				$latlongval[$i] = get_lati_long_placelocation($person->getBirthPlace());
				if ($latlongval[$i] != NULL) {
					$lat[$i] = (double)str_replace(array('N', 'S', ','), array('', '-', '.'), $latlongval[$i]['lati']);
					$lon[$i] = (double)str_replace(array('E', 'W', ','), array('', '-', '.'), $latlongval[$i]['long']);
					if ($lat[$i] || $lon[$i]) {
						if (($latlongval[$i]['icon'] != NULL)) {
							$flags[$i] = $latlongval[$i]['icon'];
							$ffile = strrchr($latlongval[$i]['icon'], '/');
							$ffile = substr($ffile,1, strpos($ffile, '.')-1);
							if (empty($flags[$ffile])) {
								$flags[$ffile] = $i; // Only generate the flag once
								$js .= 'var point = new google.maps.LatLng(' . $lat[$i] . ',' . $lon[$i]. ');';
								$js .= 'var Marker1_0_flag = new google.maps.MarkerImage();';
								$js .= 'Marker1_0_flag.image = "'.KT_STATIC_URL.KT_MODULES_DIR.'googlemap/'.$flags[$i].'";';
								$js .= 'Marker1_0_flag.shadow = "'.KT_STATIC_URL.KT_MODULES_DIR.'googlemap/images/flag_shadow.png";';
								$js .= 'Marker1_0_flag.iconSize = new google.maps.Size(25, 15);';
								$js .= 'Marker1_0_flag.shadowSize = new google.maps.Size(35, 45);';
								$js .= 'Marker1_0_flag.iconAnchor = new google.maps.Point(12, 15);';
								$js .= 'var Marker1_0 = new google.maps.LatLng(point, {icon:Marker1_0_flag});';
							}
						}
						$marker_number = $curgen;
						$dups=0;
						for ($k=0; $k<$i; $k++) {
							if ($latlongval[$i] == $latlongval[$k]) {
								$dups++;
								switch($dups) {
									case 1: $marker_number = $curgen . 'L'; break;
									case 2: $marker_number = $curgen . 'R'; break;
									case 3: $marker_number = $curgen . 'Ls'; break;
									case 4: $marker_number = $curgen . 'Rs'; break;
									case 5: //adjust position where markers have same coodinates
									default: $marker_number = $curgen;
										$lon[$i] = $lon[$i]+0.0025;
										$lat[$i] = $lat[$i]+0.0025;
										break;
								}
							}
						}
						$js .= 'var point = new google.maps.LatLng('.$lat[$i].','.$lon[$i].');';
						$js .= "var marker = createMarker(point, \"".addslashes($name)."\",\n\t\"<div>".$dataleft.$datamid.$dataright."</div>\", \"";
						$js .= "<div class='iwstyle'>";
						$js .= "<a href='module.php?ged=".KT_GEDURL."&amp;mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=" . $person->getXref() . "&amp;PEDIGREE_GENERATIONS={$PEDIGREE_GENERATIONS}";
						$js .= "' title='".KT_I18N::translate('Pedigree map')."'>".$dataleft."</a>".$datamid.$dataright."</div>\", \"".$marker_number."\");";
						// Construct the polygon lines
						$to_child = (intval(($i-1)/2)); // Draw a line from parent to child
						if (array_key_exists($to_child, $lat) && $lat[$to_child]!=0 && $lon[$to_child]!=0) {
							$js .='
							var linecolor;
							var plines;
							var lines = [new google.maps.LatLng('.$lat[$i].','.$lon[$i].'),
								new google.maps.LatLng('.$lat[$to_child].','.$lon[$to_child].')];
							linecolor = "'.$colored_line[$curgen].'";
							plines = new google.maps.Polygon({
								paths: lines,
								strokeColor: linecolor,
								strokeOpacity: 0.8,
								strokeWeight: 3,
								fillColor: "#FF0000",
								fillOpacity: 0.1
							});
							plines.setMap(pm_map);';
						}
					// Extend and fit marker bounds
					$js .='bounds.extend(point);';
					$js .='pm_map.fitBounds(bounds);';
					$count++;
					}
				}
			} else {
				$latlongval[$i] = NULL;
			}
		}
		$js .='pm_map.setCenter(bounds.getCenter());'.
		// Close the sidebar highlight when the infowindow is closed
		'google.maps.event.addListener(infowindow, "closeclick", function() {
			document.getElementById(lastlinkid).className = "person_box:target";
		});'.
		// put the assembled side_bar_html contents into the side_bar div
		'document.getElementById("side_bar").innerHTML = side_bar_html;'.
		// create the context menu div
		'var contextmenu = document.createElement("div");
			contextmenu.style.visibility="hidden";
			contextmenu.innerHTML = "<a href=\'#\' onclick=\'zoomIn()\'><div class=\'optionbox\'>&nbsp;&nbsp;'.KT_I18N::translate('Zoom in').'&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'zoomOut()\'><div class=\'optionbox\'>&nbsp;&nbsp;'.KT_I18N::translate('Zoom out').'&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'zoomInHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;'.KT_I18N::translate('Zoom in here').'</div></a>"
								+ "<a href=\'#\' onclick=\'zoomOutHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;'.KT_I18N::translate('Zoom out here').'&nbsp;&nbsp;</div></a>"
								+ "<a href=\'#\' onclick=\'centreMapHere()\'><div class=\'optionbox\'>&nbsp;&nbsp;'.KT_I18N::translate('Center map here').'&nbsp;&nbsp;</div></a>";'.
		// listen for singlerightclick
		'google.maps.event.addListener(pm_map,"singlerightclick", function(pixel,tile) {'.
			// store the "pixel" info in case we need it later
			// adjust the context menu location if near an egde
			// create a GControlPosition
			// apply it to the context menu, and make the context menu visible
			'clickedPixel = pixel;
			var x=pixel.x;
			var y=pixel.y;
			if (x > pm_map.getSize().width - 120) { x = pm_map.getSize().width - 120 }
			if (y > pm_map.getSize().height - 100) { y = pm_map.getSize().height - 100 }
			var pos = new GControlPosition(G_ANCHOR_TOP_LEFT, new GSize(x,y));
			pos.apply(contextmenu);
			contextmenu.style.visibility = "visible";
		});
		'.
		// functions that perform the context menu options
		'function zoomIn() {'.
			// perform the requested operation
			'pm_map.zoomIn();'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomOut() {'.
			// perform the requested operation
			'pm_map.zoomOut();'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomInHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.zoomIn(point,true);'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function zoomOutHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.setCenter(point,pm_map.getZoom()-1);'.
			// There is no pm_map.zoomOut() equivalent
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}
		function centreMapHere() {'.
			// perform the requested operation
			'var point = pm_map.fromContainerPixelToLatLng(clickedPixel)
			pm_map.setCenter(point);'.
			// hide the context menu now that it has been used
			'contextmenu.style.visibility="hidden";
		}'.
		// If the user clicks on the map, close the context menu
		'google.maps.event.addListener(pm_map, "click", function() {
			contextmenu.style.visibility="hidden";
		});';
		return $js;
	}

	private function admin_placecheck() {
		require KT_ROOT.KT_MODULES_DIR.'googlemap/defaultconfig.php';
		require_once KT_ROOT.KT_MODULES_DIR.'googlemap/googlemap.php';
		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$action		= safe_GET('action', '','go');
		$gedcom_id	= safe_GET('gedcom_id', array_keys(KT_Tree::getAll()), KT_GED_ID);
		$country	= safe_GET('country', KT_REGEX_UNSAFE, 'XYZ');
		$state		= safe_GET('state', KT_REGEX_UNSAFE, 'XYZ');
		$matching	= safe_GET_bool('matching');

		$par_id		= array();

		if (!empty($KT_SESSION['placecheck_gedcom_id'])) {
			$gedcom_id = $KT_SESSION['placecheck_gedcom_id'];
		} else {
			$KT_SESSION['placecheck_gedcom_id'] = $gedcom_id;
		}
		if (!empty($KT_SESSION['placecheck_country'])) {
			$country = $KT_SESSION['placecheck_country'];
		} else {
			$KT_SESSION['placecheck_country'] = $country;
		}
		if (!empty($KT_SESSION['placecheck_state'])) {
			$state = $KT_SESSION['placecheck_state'];
		} else {
			$KT_SESSION['placecheck_state'] = $state;
		}

		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle(KT_I18N::translate('Google Maps™'))
			->pageHeader();

		echo '
			<table id="gm_config">
				<tr>
					<th>
						<a href="module.php?mod=googlemap&amp;mod_action=admin_config">', KT_I18N::translate('Google Maps™ preferences'),'</a>
					</th>
					<th>
						<a href="module.php?mod=googlemap&amp;mod_action=admin_places">
							', KT_I18N::translate('Geographic data'),'
						</a>
					</th>
					<th>
						<a class="current" href="module.php?mod=googlemap&amp;mod_action=admin_placecheck">
							', KT_I18N::translate('Place Check'),'
						</a>
					</th>
				</tr>
			</table>';

		//Start of User Defined options
		echo '
			<form method="get" name="placecheck" action="module.php">
				<input type="hidden" name="mod" value="', $this->getName(), '">
				<input type="hidden" name="mod_action" value="admin_placecheck">
				<div class="gm_check">
					<label>', KT_I18N::translate('Family tree'), '</label>';
					echo select_edit_control('gedcom_id', KT_Tree::getIdList(), null, $gedcom_id, ' onchange="this.form.submit();"');
					echo '<label>', KT_I18N::translate('Country'), '</label>
					<select name="country" onchange="this.form.submit();">
						<option value="XYZ" selected="selected">', /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'), '</option>
						<option value="XYZ">', KT_I18N::translate('All'), '</option>';
							$rows=KT_DB::prepare("SELECT pl_id, pl_place FROM `##placelocation` WHERE pl_level=0 ORDER BY pl_place")
								->fetchAssoc();
							foreach ($rows as $id=>$place) {
								echo '<option value="', htmlspecialchars($place), '"';
								if ($place == $country) {
									echo ' selected="selected"';
									$par_id=$id;
								}
								echo '>', htmlspecialchars($place), '</option>';
							}
					echo '</select>';
					if ($country!='XYZ') {
						echo '<label>', /* I18N: Part of a country, state/region/county */ KT_I18N::translate('Subdivision'), '</label>
							<select name="state" onchange="this.form.submit();">
								<option value="XYZ" selected="selected">', KT_I18N::translate('Select'), '</option>
								<option value="XYZ">', KT_I18N::translate('All'), '</option>';
								$places=KT_DB::prepare("SELECT pl_place FROM `##placelocation` WHERE pl_parent_id=? ORDER BY pl_place")
									->execute(array($par_id))
									->fetchOneColumn();
								foreach ($places as $place) {
									echo '<option value="', htmlspecialchars($place), '"', $place == $state?' selected="selected"':'', '>', htmlspecialchars($place), '</option>';
								}
								echo '</select>';
							}
					echo '<label>', KT_I18N::translate('Include fully matched places: '), '</label>';
					echo '<input type="checkbox" name="matching" value="1" onchange="this.form.submit();"';
					if ($matching) {
						echo ' checked="checked"';
					}
					echo '>';
				echo '</div>';// close div gm_check
				echo '<input type="hidden" name="action" value="go">';
			echo '</form>';//close form placecheck
			echo '<hr>';

		switch ($action) {
		case 'go':
			$table_id = 'gm_check_details';
			$controller
				->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
				->addExternalJavascript(KT_JQUERY_DT_HTML5)
				->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
				->addInlineJavascript('
					jQuery("#' . $table_id . '").dataTable({
						dom: \'<"H"<"filtersH_' . $table_id . '">T<"dt-clear">pBf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id.'">>\',
						' . KT_I18N::datatablesI18N() . ',
						buttons: [{extend: "csv"}],
						jQueryUI: true,
						autoWidth: false,
						pageLength: 20,
						pagingType: "full_numbers",
						stateSave: true,
						stateDuration: 300
					});
					jQuery("#gm_check_details").css("visibility", "visible");
					jQuery(".loading-image").css("display", "none");
				');
			//Identify gedcom file
			$trees		= KT_Tree::getAll();
			echo '<div id="gm_check_title">', $trees[$gedcom_id]->tree_title_html, '</div>';
			//Select all '2 PLAC ' tags in the file and create array
			$place_list	= array();
			$ged_data	= KT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_gedcom LIKE ? AND i_file=?")
				->execute(array("%\n2 PLAC %", $gedcom_id))
				->fetchOneColumn();
			foreach ($ged_data as $ged_datum) {
				preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
				foreach ($matches[1] as $match) {
					$place_list[$match]=true;
				}
			}
			$ged_data = KT_DB::prepare("SELECT f_gedcom FROM `##families` WHERE f_gedcom LIKE ? AND f_file=?")
				->execute(array("%\n2 PLAC %", $gedcom_id))
				->fetchOneColumn();
			foreach ($ged_data as $ged_datum) {
				preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
				foreach ($matches[1] as $match) {
					$place_list[$match]=true;
				}
			}
			// Unique list of places
			$place_list	= array_keys($place_list);

			// Apply_filter
			if ($country == 'XYZ') {
				$filter = '.*$';
			} else {
				$filter = preg_quote($country).'$';
				if ($state != 'XYZ') {
					$filter = preg_quote($state).', '.$filter;
				}
			}
			$place_list = preg_grep('/'.$filter.'/', $place_list);

			//sort the array, limit to unique values, and count them
			$place_parts = array();
			usort($place_list, "utf8_strcasecmp");
			$i = count($place_list);

			//calculate maximum no. of levels to display
			$x		= 0;
			$max	= 0;
			while ($x < $i) {
				$levels	= explode(",", $place_list[$x]);
				$parts	= count($levels);
				if ($parts > $max) {
					$max = $parts;
				}
				$x++;
			}
			$x = 0;

			//scripts for edit, add and refresh
			?>
			<script>
			function edit_place_location(placeid) {
				window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=update&placeid=' + placeid, '_blank');
				return false;
			}

			function add_place_location(placeid) {
				window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=add&placeid=' + placeid, '_blank');
				return false;
			}
			</script>
			<?php

			//start to produce the display table
			$cols	= 0;
			$span	= $max * 3 + 3;
			echo '<div class="loading-image">&nbsp;</div>
			<div class="gm_check_details">
				<table id="gm_check_details" style="width: 100%; visibility: hidden;">
					<thead>
						<tr>
							<th rowspan="3">', KT_I18N::translate('Family tree place'), '</th>
							<th colspan="', $span, '">', KT_I18N::translate('Google Maps location data'), '</th>
						</tr>
						<tr>';
							while ($cols<$max) {
								if ($cols == 0) {
									echo '<th colspan="3">', KT_I18N::translate('Country'), '</th>';
								} else {
									echo '<th colspan="3">', KT_I18N::translate('Level'), '&nbsp;', $cols+1, '</th>';
								}
								$cols++;
							}
						echo '</tr>
						<tr>';
							$cols = 0;
							while ($cols < $max) {
								echo '
									<th>' . KT_Gedcom_Tag::getLabel('PLAC') . '</th>
									<th>' . KT_I18N::translate('Latitude') . '</th>
									<th>' . KT_I18N::translate('Longitude') . '</th>';
								$cols ++;
							}
						echo '</tr>
					</thead>
					<tbody>';
						$countrows = 0;
						while ($x < $i) {
							$placestr 	= '';
							$levels 	= explode(', ', $place_list[$x]);
							$parts		= count($levels);
							$levels		= array_reverse($levels);
							$placestr	.= '<a href="placelist.php?action=show';
							foreach ($levels as $pindex=>$ppart) {
								$placestr .= '&amp;parent[' . $pindex . ']=' . urlencode($ppart);
							}
							$placestr		.= '">' . $place_list[$x] . "</a>";
							$gedplace		= '<tr><td>' . $placestr . '</td>';
							$prev_lati		= 1;
							$z				= 0;
							$y				= 0;
							$id				= 0;
							$level			= 0;
							$matched[$x]	= 0;// used to exclude places where the gedcom place is matched at all levels
							$mapstr_edit	= '<a href="#" dir="auto" onclick="edit_place_location(\'';
							$mapstr_add		= '<a href="#" dir="auto" onclick="add_place_location(\'';
							$mapstr3		= '';
							$mapstr4   		= '';
							$mapstr5		= '\')" title=\'';
							$mapstr6		= '\' >';
							$mapstr7		= '\')">';
							$mapstr8		= '</a>';
			 				while ($z < $parts) {
								if ($levels[$z] == ' ' || $levels[$z] == '')
									$levels[$z] = 'unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,

								$levels[$z] = rtrim(ltrim($levels[$z]));
								$placelist	= create_possible_place_names($levels[$z], $z + 1); // add the necessary prefix/postfix values to the place name
								foreach ($placelist as $key=>$placename) {
									$row =
										KT_DB::prepare("SELECT pl_id, pl_place, pl_long, pl_lati, pl_zoom FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
										->execute(array($z, $id, $placename))
										->fetchOneRow(PDO::FETCH_ASSOC);
									if (!empty($row['pl_id'])) {
										$row['pl_placerequested'] = $levels[$z]; // keep the actual place name that was requested so we can display that instead of what is in the db
										break;
									}
								}

								if ($row['pl_id'] != '') {
									$id = $row['pl_id'];
								}

								if ($row['pl_place'] != '') {
									$placestr2 = $mapstr_edit . $id . '&amp;level=' . $level . $mapstr3 . $mapstr5 . KT_I18N::translate('Zoom=') . $row['pl_zoom'] . $mapstr6 . $row['pl_placerequested'] . $mapstr8;
									if ($row['pl_place'] == 'unknown')
										$matched[$x]++;
								} else {
									if ($levels[$z] == 'unknown') {
										$placestr2 = $mapstr_add . $id . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<strong>' . rtrim(ltrim(KT_I18N::translate('unknown'))) . "</strong>" . $mapstr8;
										$matched[$x]++;
									} else {
										$placestr2 = $mapstr_add . $id . '&amp;place_name=' . urlencode($levels[$z]) . '&amp;level=' . $level . $mapstr3 . $mapstr7 . '<span class="error">' . rtrim(ltrim($levels[$z])) . '</span>' . $mapstr8;
										$matched[$x]++;
									}
								}

								if ($prev_lati == 0) { // no link to edit if parent has no coordinates
									$plac[$z] = '<td class="CellWithComment">' .
										$levels[$z] . '
										<span class="CellComment">' . KT_I18N::translate('Coordinates can not be added here until the parent place has coordinates.') . '</span>
									</td>';
								} else {
									$plac[$z] = '<td>' . $placestr2 . '</td>';
								}

								if ($row['pl_lati'] == '0') {
									$lati[$z] = '<td class="error"><strong>' . $row['pl_lati'] . '</strong></td>';
								} elseif ($row['pl_lati'] != '') {
									$lati[$z] = '<td>' . $row['pl_lati'] . '</td>';
								} else {
									$lati[$z] = '<td class="error center"><strong>X</strong></td>';
									$prev_lati = 0;
									$matched[$x]++;
								}
								if ($row['pl_long'] == '0') {
									$long[$z] = '<td class="error"><strong>' . $row['pl_long'] . '</strong></td>';
								} elseif ($row['pl_long']!='') {
									$long[$z] = '<td>' . $row['pl_long'] . '</td>';
								} else {
									$long[$z] = '<td class="error center"><strong>X</strong></td>';
									$matched[$x]++;
								}
								$level++;
								$mapstr3 = $mapstr3 . '&amp;parent[' . $z . ']=' . addslashes($row['pl_placerequested']);
								$mapstr4 = $mapstr4 . '&amp;parent[' . $z . ']=' . addslashes(rtrim(ltrim($levels[$z])));
								$z++;
							}
							if ($matching) {
								$matched[$x] = 1;
							}
							if ($matched[$x] != 0) {
								echo $gedplace;
								$z = 0;
								while ($z < $max) {
									if ($z < $parts) {
										echo $plac[$z];
										echo $lati[$z];
										echo $long[$z];
									} else {
										echo '<td>&nbsp;</td>
										<td>&nbsp;</td>
										<td>&nbsp;</td>';
									}
									$z++;
								}
								echo '</tr>';
								$countrows++;
							}
							$x++;
						}
					echo '</tbody>
				</table>
			</div>';
			break;
		}
	}

	private function checkMapData() {
		global $controller;
		$xrefs="'".$controller->record->getXref()."'";
		$families = $controller->record->getSpouseFamilies();
		foreach ($families as $family) {
			$xrefs.=", '".$family->getXref()."'";
		}
		return KT_DB::prepare("SELECT COUNT(*) AS tot FROM `##placelinks` WHERE pl_gid IN (".$xrefs.") AND pl_file=?")
			->execute(array(KT_GED_ID))
			->fetchOne();
	}
	private function street_view() {
	header('Content-type: text/html; charset=UTF-8');

		?>
		<html>
			<head>
				<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
				<script src="https://maps.google.com/maps/api/js?v=3"></script>
				<script>

		// Following function creates an array of the google map parameters passed ---------------------
		var qsParm = new Array();
		function qs() {
			var query = window.location.search.substring(1);
			var parms = query.split('&');
			for (var i=0; i<parms.length; i++) {
				var pos = parms[i].indexOf('=');
				if (pos > 0) {
					var key = parms[i].substring(0,pos);
					var val = parms[i].substring(pos+1);
					qsParm[key] = val;
				}
			}
		}
		qsParm['x'] = null;
		qsParm['y'] = null;
		qs();

		var geocoder = new google.maps.Geocoder();

		function geocodePosition(pos) {
			geocoder.geocode({
					latLng: pos
			}, function(responses) {
				if (responses && responses.length > 0) {
					updateMarkerAddress(responses[0].formatted_address);
				} else {
					updateMarkerAddress('Cannot determine address at this location.');
				}
			});
		}

		function updateMarkerStatus(str) {
			document.getElementById('markerStatus').innerHTML = str;
		}

		function updateMarkerPosition(latLng) {
			document.getElementById('info').innerHTML = [
				latLng.lat(),
				latLng.lng()
			].join(', ');
		}

		function updateMarkerAddress(str) {
			document.getElementById('address').innerHTML = str;
		}

		function roundNumber(num, dec) {
			var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
			return result;
		}

		function initialize() {
			var x = qsParm['x'];
			var y = qsParm['y'];
			var b = parseFloat(qsParm['b']);
			var p = parseFloat(qsParm['p']);
			var m = parseFloat(qsParm['m']);

			var latLng = new google.maps.LatLng(y, x);

			// Create the map and mapOptions
			var mapOptions = {
				zoom: 16,
				center: latLng,
				mapTypeId: google.maps.MapTypeId.ROADMAP,  // ROADMAP, SATELLITE, HYBRID, TERRAIN
				mapTypeControlOptions: {
					style: google.maps.MapTypeControlStyle.DROPDOWN_MENU  // DEFAULT, DROPDOWN_MENU, HORIZONTAL_BAR
				},
				navigationControl: true,
				navigationControlOptions: {
					position: google.maps.ControlPosition.TOP_RIGHT,  // BOTTOM, BOTTOM_LEFT, LEFT, TOP, etc
					style: google.maps.NavigationControlStyle.SMALL   // ANDROID, DEFAULT, SMALL, ZOOM_PAN
				},
				streetViewControl: false,  // Show Pegman or not
				scrollwheel: true
			};

			var map = new google.maps.Map(document.getElementById('mapCanvas'), mapOptions);

			var bearing = b;
			if (bearing < 0) {
				bearing=bearing+360;
			}
			var pitch = p;
			var svzoom = m;

			var imageNum = Math.round(bearing/22.5) % 16;

			var image = new google.maps.MarkerImage('<?php echo KT_SCRIPT_PATH . KT_MODULES_DIR; ?>/googlemap/images/panda-icons/panda-' + imageNum + '.png',
				// This marker is 50 pixels wide by 50 pixels tall.
				new google.maps.Size(50, 50),
				// The origin for this image is 0,0.
				new google.maps.Point(0, 0),
				// The anchor for this image is the base of the flagpole at 0,32.
				new google.maps.Point(26, 36)
			);

			var shape = {
				coord: [1, 1, 1, 20, 18, 20, 18 , 1],
				type: 'poly'
			};

			var marker = new google.maps.Marker({
				icon: image,
				// shape: shape,
				position: latLng,
				title: 'Drag me to a Blue Street',
				map: map,
				draggable: true
			});

			// Next, get the map’s default panorama and set up some defaults.

			// First check if Browser supports html5
			var browserName=navigator.appName;
			if (browserName == 'Microsoft Internet Explorer') {
				var render_type = '';
			} else {
				var render_type = 'html5';
			}

			// --- Create the panorama ---
			var panoramaOptions = {
				navigationControl: true,
				navigationControlOptions: {
					position: google.maps.ControlPosition.TOP_RIGHT,  // BOTTOM, BOTTOM_LEFT, LEFT, TOP, etc
					style: google.maps.NavigationControlStyle.SMALL   // ANDROID, DEFAULT, SMALL, ZOOM_PAN
				},
				linksControl: true,
				addressControl: true,
				addressControlOptions: {
					style: {
						// display: 'none'
						// backgroundColor: 'red'
					}
				},
				position: latLng,
				mode: render_type,
				pov: {
					heading: bearing,
					pitch: pitch,
					zoom: svzoom
				}
			};
			panorama = new google.maps.StreetViewPanorama(document.getElementById('mapCanvas'), panoramaOptions);
			panorama.setPosition(latLng);
			setTimeout(function() { panorama.setVisible(true); }, 1000);
			setTimeout(function() { panorama.setVisible(true); }, 2000);
			setTimeout(function() { panorama.setVisible(true); }, 3000);

			// Enable navigator contol and address control to be toggled with right mouse button -------
			var aLink = document.createElement('a');
			aLink.href = 'javascript:void(0)'; onmousedown=function(e) {
				if (parseInt(navigator.appVersion)>3) {
					var clickType=1;
					if (navigator.appName == 'Netscape') {
						clickType=e.which;
					} else {
						clickType=event.button;
					}
					if (clickType == 1) {
						self.status='Left button!';
					}
					if (clickType!=1) {
						if (panorama.get('addressControl') == false) {
							panorama.set('navigationControl', false);
							panorama.set('addressControl', true);
							panorama.set('linksControl', true);
						} else {
							panorama.set('navigationControl', false);
							panorama.set('addressControl', false);
							panorama.set('linksControl', false);
						}
					}
				}
				return true;
			};
			panorama.controls[google.maps.ControlPosition.TOP_RIGHT].push(aLink);

			// Update current position info.
			updateMarkerPosition(latLng);
			geocodePosition(latLng);

			// Add dragging event listeners.
			google.maps.event.addListener(marker, 'dragstart', function() {
				updateMarkerAddress('Dragging...');
			});

			google.maps.event.addListener(marker, 'drag', function() {
				updateMarkerStatus('Dragging...');
				updateMarkerPosition(marker.getPosition());
				panorama.setPosition(marker.getPosition());
			});

			google.maps.event.addListener(marker, 'dragend', function() {
				updateMarkerStatus('Drag ended');
				geocodePosition(marker.getPosition());
			});

			google.maps.event.addListener(panorama, 'pov_changed', function() {
				povLevel = panorama.getPov();
				parent.document.getElementById('sv_bearText').value = roundNumber(povLevel.heading, 2)+"\u00B0";
				parent.document.getElementById('sv_elevText').value = roundNumber(povLevel.pitch, 2)+"\u00B0";
				parent.document.getElementById('sv_zoomText').value = roundNumber(povLevel.zoom, 2);
			});

			google.maps.event.addListener(panorama, 'position_changed', function() {
				pos = panorama.getPosition();
				marker.setPosition(pos);
				parent.document.getElementById('sv_latiText').value = pos.lat()+"\u00B0";
				parent.document.getElementById('sv_longText').value = pos.lng()+"\u00B0";
			});

			// Now add the ImageMapType overlay to the map
			map.overlayMapTypes.push(null);

			// Now create the StreetView ImageMap
			var street = new google.maps.ImageMapType({
				getTileUrl: function(coord, zoom) {
					var X = coord.x % (1 << zoom);  // wrap
					return 'https://cbk0.google.com/cbk?output=overlay&zoom=' + zoom + '&x=' + X + '&y=' + coord.y + '&cb_client=api';
				},
				tileSize: new google.maps.Size(256, 256),
				isPng: true
			});

			//  Add the Street view Image Map
			map.overlayMapTypes.setAt(1, street);
		}

		function toggleStreetView() {
			var toggle = panorama.getVisible();
			if (toggle == false) {
				panorama.setVisible(true);
				document.myForm.butt1.value = "<?php echo KT_I18N::translate('Google Maps™'); ?>";
			} else {
				panorama.setVisible(false);
				document.myForm.butt1.value = "<?php echo KT_I18N::translate('Google Street View™'); ?>";
			}
		}

		// Onload handler to fire off the app.
		google.maps.event.addDomListener(window, 'load', initialize);

		</script>
		</head>
			<body>
				<style>
					#mapCanvas {
						width: 520px;
						height: 350px;
						margin: 0 auto;
						margin-top: -10px;
						border:1px solid black;
					}
					#infoPanel {
						display: none;
						margin: 0 auto;
						margin-top: 5px;
					}
					#infoPanel div {
						display: none;
						margin-bottom: 5px;
						background: #ffffff;
					}
					div {
						text-align: center;
					}
				</style>

				<div id="toggle">
					<form name="myForm" title="myForm">
						<?php
						echo '<input id="butt1" name ="butt1" type="button" value="', KT_I18N::translate('Google Maps™'), '" onclick="toggleStreetView();">';
						echo '<input id="butt2" name ="butt2" type="button" value="', KT_I18N::translate('Reset'), '" onclick="initialize();">';
						?>
					</form>
				</div>

				<div id="mapCanvas">
				</div>

				<div id="infoPanel">
					<div id="markerStatus"><em>Click and drag the marker.</em></div>
					<div id="info" ></div>
					<div id="address"></div>
				</div>
			</body>
		</html>
		<?php
	}

	// Implement KT_Module_Tab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

}
