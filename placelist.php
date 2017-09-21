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

define('KT_SCRIPT_NAME', 'placelist.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$action		= safe_GET('action',  array('find', 'show'), 'find');
$display	= safe_GET('display', array('hierarchy', 'list'), 'hierarchy');
$parent		= safe_GET('parent', KT_REGEX_UNSAFE); // Place names may include HTML chars.  "Sunny View Cemetery", Smallville, <unknown>, Texas, USA"
if (!is_array($parent)) {
	$parent = array();
}
$level = count($parent);

$controller = new KT_Controller_Page();
if ($display == 'hierarchy') {
	if ($level) {
		$controller->setPageTitle(KT_I18N::translate('Place hierarchy') . ' - <span dir="auto">' . htmlspecialchars(end($parent)) . '</span>');
	} else {
		$controller->setPageTitle(KT_I18N::translate('Place hierarchy'));
	}
} else {
	$controller->setPageTitle(KT_I18N::translate('Place List'));
}
$controller
	->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_places', KT_USER_ACCESS_LEVEL))
	->pageHeader();

echo '<div id="place-hierarchy">';
	switch ($display) {
		case 'list':
			$list_places	= KT_Place::allPlaces(KT_GED_ID);
			$num_places		= count($list_places);

			echo '
				<h2>', $controller->getPageTitle(), '</h2>
				<h4><a href="placelist.php?display=hierarchy">', KT_I18N::translate('Switch to Place hierarchy'), '</a></h4>';

			if ($num_places == 0) {
				echo '<p>', KT_I18N::translate('No results found.'), '<p>';
			} else {
				echo '<ul>';
					foreach ($list_places as $n=>$list_place) {
						echo '
							<li>
								<a href="', $list_place->getURL(), '">', $list_place->getReverseName(), '</a>
							</li>';
					}
				echo' </ul>';
			}

			break;
		case 'hierarchy':
			$use_googlemap = array_key_exists('googlemap', KT_Module::getActiveModules()) && get_module_setting('googlemap', 'GM_PLACE_HIERARCHY');

			if ($use_googlemap) {
				require KT_ROOT.KT_MODULES_DIR.'googlemap/placehierarchy.php';
			}

			// Find this place and its ID
			$place			= new KT_Place(implode(', ', array_reverse($parent)), KT_GED_ID);
			$place_id		= $place->getPlaceId();
			$child_places	= $place->getChildPlaces();
			$numfound		= count($child_places);

			//-- if the number of places found is 0 then automatically redirect to search page
			if ($numfound == 0) {
				$action='show';
			}

			// Breadcrumbs
			echo '<h2>', $controller->getPageTitle();
				if ($place_id) {
					$parent_place=$place->getParentPlace();
					while ($parent_place->getPlaceId()) {
						echo ', <a href="', $parent_place->getURL(), '" dir="auto">', $parent_place->getPlaceName(), '</a>';
						$parent_place=$parent_place->getParentPlace();
					}
					echo ', <a href="', KT_SCRIPT_NAME, '">', KT_I18N::translate('Top Level'), '</a>';
				}
			echo '</h2>
			<h4>
				<a href="placelist.php?display=list">', KT_I18N::translate('Switch to list view'), '</a>
			</h4>';

			if ($use_googlemap) {
				$linklevels='';
				$placelevels='';
				$place_names=array();
				for ($j=0; $j<$level; $j++) {
					$linklevels .= '&amp;parent['.$j.']='.rawurlencode($parent[$j]);
					if ($parent[$j]=='') {
						$placelevels = ', ' . KT_I18N::translate('unknown') . $placelevels;
					} else {
						$placelevels = ', ' . $parent[$j] . $placelevels;
					}
				}
				create_map($placelevels);
			}

			echo '<div id="place-list">
				<p>';
					if ($place_id) {
						echo /* I18N: %s is a country or region */ KT_I18N::translate('Places in %s', $place->getPlaceName());
					}
				echo '</p>
				<ul>';
					foreach ($child_places as $n => $child_place) {
						echo '<li>
							<a href="', $child_place->getURL(), '" class="list_item">', $child_place->getPlaceName(), '</a>
						</li>';
						if ($use_googlemap) {
							$place_names[$n] = $child_place->getPlaceName();
						}
					}
				echo'</ul>';
				if ($child_places) {
					if ($action=='find' && $place_id) {
						$this_place = '<a href="' . $place->getURL() . '&amp;action=show" class="formField">' . $place->getPlaceName() . '</a>';
						echo '<p>',
							KT_I18N::translate('View all records found in %s', $this_place), help_link('ppp_view_records'), '
						</p>';
					}
				}
			echo '</div>';

			if ($place_id && $action=='show') {
				// -- array of names
				$myindilist = array();
				$myfamlist = array();

				$positions=
					KT_DB::prepare("SELECT DISTINCT pl_gid FROM `##placelinks` WHERE pl_p_id=? AND pl_file=?")
					->execute(array($place_id, KT_GED_ID))
					->fetchOneColumn();

				foreach ($positions as $position) {
					$record=KT_GedcomRecord::getInstance($position);
					if ($record && $record->canDisplayDetails()) {
						switch ($record->getType()) {
						case 'INDI':
							$myindilist[]=$record;
							break;
						case 'FAM':
							$myfamlist[]=$record;
							break;
						}
					}
				}

				//-- display results
				$controller
					->addInlineJavascript('jQuery("#places-tabs").tabs();')
					->addInlineJavascript('jQuery("#places-tabs").css("visibility", "visible");')
					->addInlineJavascript('jQuery(".loading-image").css("display", "none");');

				echo '<div class="loading-image">&nbsp;</div>';
				echo '<div id="places-tabs"><ul>';
				if ($myindilist) {
					echo '<li><a href="#places-indi"><span id="indisource">', KT_I18N::translate('Individuals'), '</span></a></li>';
				}
				if ($myfamlist) {
					echo '<li><a href="#places-fam"><span id="famsource">', KT_I18N::translate('Families'), '</span></a></li>';
				}
				echo '</ul>';
				if ($myindilist) {
					echo '<div id="places-indi">', format_indi_table($myindilist), '</div>';
				}
				if ($myfamlist) {
					echo '<div id="places-fam">', format_fam_table($myfamlist), '</div>';
				}
				if (!$myindilist && !$myfamlist) {
					echo '<div id="places-indi">', format_indi_table(array()), '</div>';
				}
				echo '</div>'; // <div id="places-tabs">
			}

			if ($use_googlemap) {
				echo '<link type="text/css" href="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/css/googlemap.css" rel="stylesheet">';
				map_scripts($numfound, $level, $parent, $linklevels, $placelevels, $place_names);
			}
		break;
	}
echo '</div>'; // <div id="place-hierarchy">
