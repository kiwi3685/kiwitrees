<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

require KT_ROOT.KT_MODULES_DIR.'googlemap/defaultconfig.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$action=safe_REQUEST($_REQUEST, 'action');
if (isset($_REQUEST['parent'])) $parent=safe_REQUEST($_REQUEST, 'parent');
if (isset($_REQUEST['status'])) $status=safe_REQUEST($_REQUEST, 'status');
if (isset($_REQUEST['mode'])) $mode=safe_REQUEST($_REQUEST, 'mode');
if (isset($_REQUEST['deleteRecord'])) $deleteRecord=safe_REQUEST($_REQUEST, 'deleteRecord');

if (!isset($parent)) $parent=0;
if (!isset($status)) $status='active';

// Take a place id and find its place in the hierarchy
// Input: place ID
// Output: ordered array of id=>name values, starting with the Top Level
// e.g. array(0=>'Top Level', 16=>'England', 19=>'London', 217=>'Westminster');
// NB This function exists in both places.php and admin_places_edit.php
function place_id_to_hierarchy($id) {
	$statement=
		KT_DB::prepare("SELECT pl_parent_id, pl_place FROM `##placelocation` WHERE pl_id=?");
	$arr=array();
	while ($id!=0) {
		$row=$statement->execute(array($id))->fetchOneRow();
		$arr=array($id=>$row->pl_place)+$arr;
		$id=$row->pl_parent_id;
	}
	return $arr;
}

// NB This function exists in both admin_places.php and admin_places_edit.php
function getHighestIndex() {
	return (int)KT_DB::prepare("SELECT MAX(pl_id) FROM `##placelocation`")->fetchOne();
}

function getHighestLevel() {
	return (int)KT_DB::prepare("SELECT MAX(pl_level) FROM `##placelocation`")->fetchOne();
}

/**
 * Find all of the places in the hierarchy
 */
function get_place_list_loc($parent_id, $status='active') {
	switch ($status) {
		case 'all':
			$rows=
				KT_DB::prepare("SELECT pl_id, pl_place, pl_lati, pl_long, pl_zoom, pl_icon".
				" FROM `##placelocation`".
				" WHERE pl_parent_id=?".
				" ORDER BY pl_place COLLATE ".KT_I18N::$collation
				)
				->execute(array($parent_id))
				->fetchAll();
			break;
		case 'inactive':
			$rows=
				KT_DB::prepare(
					"SELECT pl_id, pl_place, pl_lati, pl_long, pl_zoom, pl_icon".
					" FROM `##placelocation`".
					" LEFT JOIN `##places` ON `##placelocation`.pl_place=`##places`.p_place".
					" WHERE `##places`.p_place IS NULL AND pl_parent_id=?".
					" ORDER BY pl_place COLLATE ".KT_I18N::$collation
				)
				->execute(array($parent_id))
				->fetchAll();
			break;
		case 'active':
		default:
			$rows=
				KT_DB::prepare(
					"SELECT DISTINCT pl_id, pl_place, pl_lati, pl_long, pl_zoom, pl_icon".
					" FROM `##placelocation`".
					" INNER JOIN `##places` ON `##placelocation`.pl_place=`##places`.p_place".
					" WHERE pl_parent_id=?".
					" ORDER BY pl_place COLLATE ".KT_I18N::$collation
				)
				->execute(array($parent_id))
				->fetchAll();
			break;
	}

	$placelist = array();
	foreach ($rows as $row) {
		$placelist[]=array('place_id'=>$row->pl_id, 'place'=>$row->pl_place, 'lati'=>$row->pl_lati, 'long'=>$row->pl_long, 'zoom'=>$row->pl_zoom, 'icon'=>$row->pl_icon);
	}
	return $placelist;
}

function outputLevel($parent_id) {
	$tmp = place_id_to_hierarchy($parent_id);
	$maxLevel = getHighestLevel();
	if ($maxLevel>8) $maxLevel = 8;
	$prefix = implode(';', $tmp);
	if ($prefix!='')
		$prefix.=';';
	$suffix=str_repeat(';', $maxLevel-count($tmp));
	$level=count($tmp);

	$rows=
		KT_DB::prepare("SELECT pl_id, pl_place, pl_long, pl_lati, pl_zoom, pl_icon FROM `##placelocation` WHERE pl_parent_id=? ORDER BY pl_place")
		->execute(array($parent_id))
		->fetchAll();

	foreach ($rows as $row) {
		echo $level,';',$prefix,$row->pl_place,$suffix,';',$row->pl_long,';',$row->pl_lati,';',$row->pl_zoom,';',$row->pl_icon,"\r\n";
		if ($level < $maxLevel) {
			outputLevel($row->pl_id);
		}
	}
}

/**
 * recursively find all of the csv files on the server
 *
 * @param string $path
 */
function findFiles($path) {
	global $placefiles;
	if (file_exists($path)) {
		$dir = dir($path);
		while (false !== ($entry = $dir->read())) {
			if ($entry!='.' && $entry!='..' && $entry!='.svn') {
				if (is_dir($path.'/'.$entry)) {
					findFiles($path.'/'.$entry);
				} elseif (strstr($entry, '.csv')!==false) {
					$placefiles[] = preg_replace('~'.KT_MODULES_DIR.'googlemap/extra~', '', $path).'/'.$entry;
				}
			}
		}
		$dir->close();
	}
}

$controller = new KT_Controller_Page();
$controller->restrictAccess(KT_USER_IS_ADMIN);

if ($action=='ExportFile' && KT_USER_IS_ADMIN) {
	Zend_Session::writeClose();
	$tmp = place_id_to_hierarchy($parent);
	$maxLevel = getHighestLevel();
	if ($maxLevel>8) $maxLevel=8;
	$tmp[0] = 'places';
	$outputFileName=preg_replace('/[:;\/\\\(\)\{\}\[\] $]/', '_', implode('-', $tmp)).'.csv';
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$outputFileName.'"');
	echo '"', KT_I18N::translate('Level'), '";"', KT_I18N::translate('Country'), '";';
	if ($maxLevel>0) echo '"', KT_I18N::translate('State'), '";';
	if ($maxLevel>1) echo '"', KT_I18N::translate('County'), '";';
	if ($maxLevel>2) echo '"', KT_I18N::translate('City'), '";';
	if ($maxLevel>3) echo '"', KT_I18N::translate('Place'), '";';
	if ($maxLevel>4) echo '"', KT_I18N::translate('Place'), '";';
	if ($maxLevel>5) echo '"', KT_I18N::translate('Place'), '";';
	if ($maxLevel>6) echo '"', KT_I18N::translate('Place'), '";';
	if ($maxLevel>7) echo '"', KT_I18N::translate('Place'), '";';
	echo '"', KT_I18N::translate('Longitude'), '";"', KT_I18N::translate('Latitude'), '";';
	echo '"', KT_I18N::translate('Zoom factor'), '";"', KT_I18N::translate('Icon'), '";', KT_EOL;
	outputLevel($parent);
	exit;
}

$controller
	->setPageTitle(KT_I18N::translate('Google Maps™'))
	->pageHeader();
echo '<link type="text/css" href ="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/css/googlemap.css" rel="stylesheet">';

?>
<table id="gm_config">
	<tr>
		<th>
			<a href="module.php?mod=googlemap&amp;mod_action=admin_config">
				<?php echo KT_I18N::translate('Google Maps™ preferences'); ?>
			</a>
		</th>
		<th>
			<a class="current" href="module.php?mod=googlemap&amp;mod_action=admin_places">
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
<?php

if ($action=='ImportGedcom') {
	$placelist=array();
	$j=0;
	$statement=
		KT_DB::prepare("SELECT i_gedcom FROM `##individuals` WHERE i_file=? UNION ALL SELECT f_gedcom FROM `##families` WHERE f_file=?")
		->execute(array(KT_GED_ID, KT_GED_ID));
	while ($gedrec=$statement->fetchColumn()) {
		$i = 1;
		$placerec = get_sub_record(2, '2 PLAC', $gedrec, $i);
		while (!empty($placerec)) {
			if (preg_match("/2 PLAC (.+)/", $placerec, $match)) {
				$placelist[$j] = array();
				$placelist[$j]['place'] = trim($match[1]);
				if (preg_match("/4 LATI (.*)/", $placerec, $match)) {
					$placelist[$j]['lati'] = trim($match[1]);
					if (($placelist[$j]['lati'][0] != 'N') && ($placelist[$j]['lati'][0] != 'S')) {
						if ($placelist[$j]['lati'] < 0) {
							$placelist[$j]['lati'][0] = 'S';
						} else {
							$placelist[$j]['lati'] = 'N'.$placelist[$j]['lati'];
						}
					}
				}
				else $placelist[$j]['lati'] = NULL;
				if (preg_match("/4 LONG (.*)/", $placerec, $match)) {
					$placelist[$j]['long'] = trim($match[1]);
					if (($placelist[$j]['long'][0] != 'E') && ($placelist[$j]['long'][0] != 'W')) {
						if ($placelist[$j]['long'] < 0) {
							$placelist[$j]['long'][0] = 'W';
						} else {
							$placelist[$j]['long'] = 'E'.$placelist[$j]['long'];
						}
					}
				}
				else $placelist[$j]['long'] = NULL;
				$j = $j + 1;
			}
			$i = $i + 1;
			$placerec = get_sub_record(2, '2 PLAC', $gedrec, $i);
		}
	}
	asort($placelist);

	$prevPlace = '';
	$prevLati = '';
	$prevLong = '';
	$placelistUniq = array();
	$j = 0;
	foreach ($placelist as $k=>$place) {
		if ($place['place'] != $prevPlace) {
			$placelistUniq[$j] = array();
			$placelistUniq[$j]['place'] = $place['place'];
			$placelistUniq[$j]['lati'] = $place['lati'];
			$placelistUniq[$j]['long'] = $place['long'];
			$j = $j + 1;
		} else if (($place['place'] == $prevPlace) && (($place['lati'] != $prevLati) || ($place['long'] != $prevLong))) {
			if (($placelistUniq[$j-1]['lati'] == 0) || ($placelistUniq[$j-1]['long'] == 0)) {
				$placelistUniq[$j-1]['lati'] = $place['lati'];
				$placelistUniq[$j-1]['long'] = $place['long'];
			} else if (($place['lati'] != '0') || ($place['long'] != '0')) {
				echo 'Difference: previous value = ', $prevPlace, ', ', $prevLati, ', ', $prevLong, ' current = ', $place['place'], ', ', $place['lati'], ', ', $place['long'], '<br>';
			}
		}
		$prevPlace = $place['place'];
		$prevLati = $place['lati'];
		$prevLong = $place['long'];
	}

	$highestIndex = getHighestIndex();

	$default_zoom_level=array(4, 7, 10, 12);
	foreach ($placelistUniq as $k=>$place) {
        $parent=preg_split('/ *, */', $place['place']);
		$parent=array_reverse($parent);
		$parent_id=0;
		for ($i=0; $i<count($parent); $i++) {
			if (!isset($default_zoom_level[$i]))
				$default_zoom_level[$i]=$default_zoom_level[$i-1];
			$escparent=$parent[$i];
			if ($escparent == '') {
				$escparent = 'Unknown';
			}
			$row=
				KT_DB::prepare("SELECT pl_id, pl_long, pl_lati, pl_zoom FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ?")
				->execute(array($i, $parent_id, $escparent))
				->fetchOneRow();
			if ($i < count($parent)-1) {
				// Create higher-level places, if necessary
				if (empty($row)) {
					$highestIndex++;
					KT_DB::prepare("INSERT INTO `##placelocation` (pl_id, pl_parent_id, pl_level, pl_place, pl_zoom) VALUES (?, ?, ?, ?, ?)")
						->execute(array($highestIndex, $parent_id, $i, $escparent, $default_zoom_level[$i]));
					echo htmlspecialchars((string) $escparent), '<br>';
					$parent_id=$highestIndex;
				} else {
					$parent_id=$row->pl_id;
				}
			} else {
				// Create lowest-level place, if necessary
				if (empty($row->pl_id)) {
					$highestIndex++;
					KT_DB::prepare("INSERT INTO `##placelocation` (pl_id, pl_parent_id, pl_level, pl_place, pl_long, pl_lati, pl_zoom) VALUES (?, ?, ?, ?, ?, ?, ?)")
						->execute(array($highestIndex, $parent_id, $i, $escparent, $place['long'], $place['lati'], $default_zoom_level[$i]));
					echo htmlspecialchars((string) $escparent), '<br>';
				} else {
					if (empty($row->pl_long) && empty($row->pl_lati) && $place['lati']!='0' && $place['long']!='0') {
						KT_DB::prepare("UPDATE `##placelocation` SET pl_lati=?, pl_long=? WHERE pl_id=?")
							->execute(array($place['lati'], $place['long'], $row->pl_id));
						echo htmlspecialchars((string) $escparent), '<br>';
					}
				}
			}
		}
	}
	$parent=0;
}

if ($action=='ImportFile') {
	$placefiles = array();
	findFiles(KT_MODULES_DIR.'googlemap/extra');
	sort($placefiles);
?>
<form method="post" enctype="multipart/form-data" id="importfile" name="importfile" action="module.php?mod=googlemap&amp;mod_action=admin_places">
	<input type="hidden" name="action" value="ImportFile2">
	<table class="gm_plac_edit">
		<tr>
			<th><?php echo KT_I18N::translate('File containing places (CSV)'); ?></th>
			<td>
				<div class="input">
					<input type="file" name="placesfile" size="50">
				</div>
			</td>
		</tr>
		<?php if (count($placefiles)>0) { ?>
		<tr>
			<th><?php echo KT_I18N::translate('Server file containing places (CSV)'); ?></th>
			<td>
				<select name="localfile">
					<option></option>
					<?php foreach ($placefiles as $p=>$placefile) { ?>
					<option value="<?php echo htmlspecialchars((string) $placefile); ?>"><?php
						if (substr($placefile, 0, 1)=="/") echo substr($placefile, 1);
						else echo $placefile; ?></option>
					<?php } ?>
				</select>
				<span class="help_content">
					<?php echo KT_I18N::translate('Select a file from the list of files already on the server which contains the place locations in CSV format.'); ?>
				</span>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th><?php echo KT_I18N::translate('Delete all existing geographic data before importing the file.'); ?></th>
			<td><input type="checkbox" name="cleardatabase"></td>
		</tr>
		<tr>
			<th><?php echo KT_I18N::translate('Do not create new locations, just import coordinates for existing locations.'); ?></th>
			<td><input type="checkbox" name="updateonly"></td>
		</tr>
		<tr>
			<th><?php echo KT_I18N::translate('Overwrite existing coordinates.'); ?></th>
			<td><input type="checkbox" name="overwritedata"></td>
		</tr>
	</table>
	<button id="savebutton" class="btn btn-primary" type="submit">
		<i class="fa fa-save"></i>
		<?php echo KT_I18N::translate('Continue Adding'); ?>
	</button>
</form>
<?php
	exit;
}

if ($action=='ImportFile2') {
	$country_names=array();
	foreach (KT_Stats::iso3166() as $key=>$value) {
		$country_names[$key]=KT_I18N::translate($key);
	}
	if (isset($_POST['cleardatabase'])) {
		KT_DB::exec("DELETE FROM `##placelocation` WHERE 1=1");
	}
	if (!empty($_FILES['placesfile']['tmp_name'])) {
		$lines = file($_FILES['placesfile']['tmp_name']);
	} elseif (!empty($_REQUEST['localfile'])) {
		$lines = file(KT_MODULES_DIR.'googlemap/extra'.$_REQUEST['localfile']);
	}
	// Strip BYTE-ORDER-MARK, if present
	if (!empty($lines[0]) && substr($lines[0], 0, 3)==KT_UTF8_BOM) $lines[0]=substr($lines[0], 3);
	asort($lines);
	$highestIndex = getHighestIndex();
	$placelist = array();
	$j = 0;
	$maxLevel = 0;
	foreach ($lines as $p => $placerec) {
		$fieldrec = explode(';', $placerec);
		if ($fieldrec[0] > $maxLevel) $maxLevel = $fieldrec[0];
	}
	$fields = count($fieldrec);
	$set_icon = true;
	if (!is_dir(KT_MODULES_DIR.'googlemap/places/flags/')) {
		$set_icon = false;
	}
	foreach ($lines as $p => $placerec) {
		$fieldrec = explode(';', $placerec);
		if (is_numeric($fieldrec[0]) && $fieldrec[0]<=$maxLevel) {
			$placelist[$j] = array();
			$placelist[$j]['place'] = '';
			for ($ii=$fields-4; $ii>1; $ii--) {
				if ($fieldrec[0] > $ii-2) $placelist[$j]['place'] .= $fieldrec[$ii].',';
			}
			foreach ($country_names as $countrycode => $countryname) {
				if ($countrycode == strtoupper($fieldrec[1])) {
					$fieldrec[1] = $countryname;
					break;
				}
			}
			$placelist[$j]['place'] .= $fieldrec[1];
			$placelist[$j]['long'] = $fieldrec[$fields-4];
			$placelist[$j]['lati'] = $fieldrec[$fields-3];
			$placelist[$j]['zoom'] = $fieldrec[$fields-2];
			if($set_icon) {
				$placelist[$j]['icon'] = trim($fieldrec[$fields-1]);
			} else {
				$placelist[$j]['icon'] = '';
			}
			$j = $j + 1;
		}
	}

	$prevPlace = '';
	$prevLati = '';
	$prevLong = '';
	$placelistUniq = array();
	$j = 0;
	foreach ($placelist as $k=>$place) {
		if ($place['place'] != $prevPlace) {
			$placelistUniq[$j] = array();
			$placelistUniq[$j]['place'] = $place['place'];
			$placelistUniq[$j]['lati'] = $place['lati'];
			$placelistUniq[$j]['long'] = $place['long'];
			$placelistUniq[$j]['zoom'] = $place['zoom'];
			$placelistUniq[$j]['icon'] = $place['icon'];
			$j = $j + 1;
		} else if (($place['place'] == $prevPlace) && (($place['lati'] != $prevLati) || ($place['long'] != $prevLong))) {
			if (($placelistUniq[$j-1]['lati'] == 0) || ($placelistUniq[$j-1]['long'] == 0)) {
				$placelistUniq[$j-1]['lati'] = $place['lati'];
				$placelistUniq[$j-1]['long'] = $place['long'];
				$placelistUniq[$j-1]['zoom'] = $place['zoom'];
				$placelistUniq[$j-1]['icon'] = $place['icon'];
			} else if (($place['lati'] != '0') || ($place['long'] != '0')) {
				echo 'Difference: previous value = ', $prevPlace, ', ', $prevLati, ', ', $prevLong, ' current = ', $place['place'], ', ', $place['lati'], ', ', $place['long'], '<br>';
			}
		}
		$prevPlace = $place['place'];
		$prevLati = $place['lati'];
		$prevLong = $place['long'];
	}

	$default_zoom_level = array();
	$default_zoom_level[0] = 4;
	$default_zoom_level[1] = 7;
	$default_zoom_level[2] = 10;
	$default_zoom_level[3] = 12;
	foreach ($placelistUniq as $k=>$place) {
		$parent = explode(',', $place['place']);
		$parent = array_reverse($parent);
		$parent_id=0;
		for ($i=0; $i<count($parent); $i++) {
			$escparent=$parent[$i];
			if ($escparent == '') {
				$escparent = 'Unknown';
			}
			$row=
				KT_DB::prepare("SELECT pl_id, pl_long, pl_lati, pl_zoom, pl_icon FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
				->execute(array($i, $parent_id, $escparent))
				->fetchOneRow();
			if (empty($row)) {       // this name does not yet exist: create entry
				if (!isset($_POST['updateonly'])) {
					$highestIndex = $highestIndex + 1;
					if (($i+1) == count($parent)) {
						$zoomlevel = $place['zoom'];
					} elseif (isset($default_zoom_level[$i])) {
						$zoomlevel = $default_zoom_level[$i];
					} else {
						$zoomlevel = $GOOGLEMAP_MAX_ZOOM;
					}
					if (($place['lati'] == '0') || ($place['long'] == '0') || (($i+1) < count($parent))) {
						KT_DB::prepare("INSERT INTO `##placelocation` (pl_id, pl_parent_id, pl_level, pl_place, pl_zoom, pl_icon) VALUES (?, ?, ?, ?, ?, ?)")
							->execute(array($highestIndex, $parent_id, $i, $escparent, $zoomlevel, $place['icon']));
					} else {
						//delete leading zero
						$pl_lati = str_replace(array('N', 'S', ','), array('', '-', '.') , $place['lati']);
						$pl_long = str_replace(array('E', 'W', ','), array('', '-', '.') , $place['long']);
						if ($pl_lati >= 0) {
							$place['lati'] = 'N'.abs($pl_lati);
						} elseif ($pl_lati < 0) {
							$place['lati'] = 'S'.abs($pl_lati);
						}
						if ($pl_long >= 0) {
							$place['long'] = 'E'.abs($pl_long);
						} elseif ($pl_long < 0) {
							$place['long'] = 'W'.abs($pl_long);
						}
						KT_DB::prepare("INSERT INTO `##placelocation` (pl_id, pl_parent_id, pl_level, pl_place, pl_long, pl_lati, pl_zoom, pl_icon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
							->execute(array($highestIndex, $parent_id, $i, $escparent, $place['long'], $place['lati'], $zoomlevel, $place['icon']));
					}
					$parent_id = $highestIndex;
				}
			} else {
				$parent_id = $row->pl_id;
				if ((isset($_POST['overwritedata'])) && ($i+1 == count($parent))) {
					KT_DB::prepare("UPDATE `##placelocation` SET pl_lati=?, pl_long=?, pl_zoom=?, pl_icon=? WHERE pl_id=?")
						->execute(array($place['lati'], $place['long'], $place['zoom'], $place['icon'], $parent_id));
				} else {
					if ((($row->pl_long == '0') || ($row->pl_long == null)) && (($row->pl_lati == '0') || ($row->pl_lati == null))) {
						KT_DB::prepare("UPDATE `##placelocation` SET pl_lati=?, pl_long=? WHERE pl_id=?")
							->execute(array($place['lati'], $place['long'], $parent_id));
					}
					if (empty($row->pl_icon) && !empty($place['icon'])) {
						KT_DB::prepare("UPDATE `##placelocation` SET pl_icon=? WHERE pl_id=?")
							->execute(array($place['icon'], $parent_id));
					}
				}
			}
		}
	}
	$parent = 0;
}

if ($action == 'DeleteRecord') {
	$sql = "DELETE FROM `##placelocation` WHERE pl_id IN (".$deleteRecord.")";
	KT_DB::prepare($sql)->execute();
}

?>
<script>
function updateList(status) {
	window.location.href='<?php if (strstrb($_SERVER['REQUEST_URI'], '&status')) { $uri=strstrb($_SERVER['REQUEST_URI'], '&status');} else { $uri=$_SERVER['REQUEST_URI']; } echo $uri, '&status='; ?>'+ status;
}

function edit_place_location(placeid) {
	window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=update&placeid=' + placeid, '_blank');
	return false;
}

function add_place_location(placeid) {
	window.open('module.php?mod=googlemap&mod_action=admin_places_edit&action=add&placeid='+placeid, '_blank');
	return false;
}

</script>
<?php
$controller->addInlineJavascript('jQuery("#gm_tabs").tabs();');

echo '<div id="gm_tabs">
	<ul>
		<li><a href="#gm_list"><span>', KT_I18N::translate('List'), '</span></a></li>
		<li><a href="#gm_options"><span>', KT_I18N::translate('Options'), '</span></a></li>
	</ul>
	<div id="gm_list">
		<div class="help_text">
			<span class="help_content">' .
				KT_I18N::translate('By default the list shows only those places which can be matched between the Google Maps place list and your family trees.  You may have details for other places, such as those imported in bulk from an external file.  This option also allows you to list all the places that exist the Google Maps list, or just those that exist in the Google Maps table but not on your family trees.') . '
			</span>
		</div>
		<div id="gm_breadcrumb">';
			$where_am_i=place_id_to_hierarchy($parent);
			foreach (array_reverse($where_am_i, true) as $id=>$place) {
				if ($id==$parent) {
					if ($place != 'Unknown') {
						echo htmlspecialchars((string) $place);
					} else {
						echo KT_I18N::translate('unknown');
					}
				} else {
					echo '<a href="module.php?mod=googlemap&mod_action=admin_places&parent=', $id, '&status=', $status, '">';
					if ($place != 'Unknown') {
						echo htmlspecialchars((string) $place), '</a>';
					} else {
						echo KT_I18N::translate('unknown'), '</a>';
					}
				}
				echo ' - ';
			}
			echo '<a href="module.php?mod=googlemap&mod_action=admin_places&parent=0&status=', $status, '">', KT_I18N::translate('Top Level'), '</a>
		</div>
		<div id="gm_active">
			<form name="active" method="post" action="module.php?mod=googlemap&mod_action=admin_places&parent=', $parent, '&status=', $status, '" style="display:inline;">
				<label for="status">', KT_I18N::translate('List places'), '</label>
				<select id="status" name="status" onchange="updateList(this.value)">
					<option value="all"';
						if ($status=='all') echo ' selected="selected"';
						echo '>', KT_I18N::translate('All'), '
					</option>
					<option value="active"';
						if ($status=='active') echo ' selected="selected"';
						echo '>', KT_I18N::translate('Active'), '
					</option>
					<option value="inactive"';
						if ($status=='inactive') echo ' selected="selected"';
						echo '>', KT_I18N::translate('Inactive'), '
					</option>
				</select>
			</form>';
			$placelist=get_place_list_loc($parent, $status);
			echo '<span>(', KT_I18N::translate('%s places', count($placelist)), ')</span>
		</div>
		<div class="gm_plac_edit scrollableContainer">
			<div class="scrollingArea">
				<table class="gm_plac_edit">
					<thead>
						<tr>
							<th><div class="col1">', KT_Gedcom_Tag::getLabel('PLAC'), '</div></th>
							<th><div class="col2">', KT_Gedcom_Tag::getLabel('LATI'), '</div></th>
							<th><div class="col3">', KT_Gedcom_Tag::getLabel('LONG'), '</div></th>
							<th><div class="col4">', KT_I18N::translate('Zoom factor'), '</div></th>
							<th><div class="col5">', KT_I18N::translate('Icon'), '</div></th>
							<th><div class="col6">', KT_I18N::translate('Edit'), '</div></th>
							<th><div class="col7">
								<input type="button" value="'. KT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(KT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'places\');} else {return false;}">
								<input type="checkbox" onClick="toggle_select(this)" style="vertical-align:middle;">
							</div></th>
						</tr>
					</thead>
					<tbody>';
					if (count($placelist) == 0)
						echo '<tr><td colspan="7" class="accepted">', KT_I18N::translate('No places found'), '</td></tr>';
					foreach ($placelist as $place) {
						echo '<tr>
							<td><div class="col1">
								<a href="module.php?mod=googlemap&mod_action=admin_places&parent=', $place['place_id'], '&status=', $status, '">';
								if ($place['place'] != 'Unknown') {
										echo htmlspecialchars((string) $place['place']), '</a></div></td>';
								} else {
										echo KT_I18N::translate('unknown'), '</a></div></td>';
								}
							echo '<td><div class="col2">', $place['lati'], '</div></td>
							<td><div class="col3">', $place['long'], '</div></td>
							<td><div class="col4">', $place['zoom'], '</div></td>
							<td><div class="col5">';
								if (($place['icon'] == NULL) || ($place['icon'] == '')) {
									if (($place['lati'] == NULL) || ($place['long'] == NULL) || (($place['lati'] == '0') && ($place['long'] == '0'))) {
										echo '<img src="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/images/mm_20_yellow.png">';
									}
									else {
										echo '<img src="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/images/mm_20_red.png">';
									}
								} else {
									echo '<img src="', KT_STATIC_URL, KT_MODULES_DIR, 'googlemap/', $place['icon'], '" width="25" height="15">';
								}
							echo '</div></td>
							<td><div class="col6">
                                <a href="#" onclick="edit_place_location(', $place['place_id'], ');return false;" class="icon-edit" title="', KT_I18N::translate('Edit'), '"></a>
							</div></td>';
							$noRows=
								KT_DB::prepare("SELECT COUNT(pl_id) FROM `##placelocation` WHERE pl_parent_id=?")
								->execute(array($place['place_id']))
								->fetchOne();
							if ($noRows==0) {
								echo '<td><div class="col7">
									<input type="checkbox" name="del_places[]" class="check" value="'.$place["place_id"].'" title="', KT_I18N::translate('Remove'), '">',
								'</div></td>';
							} else {
								echo '<td><div class="col7"><i class="fa-times" title="' . $noRows . '"></i></div></td>';
							}
						echo '</tr>';
					}
				echo '</tbody>
					<tfoot>
						<tr>
							<th><div class="col1">', KT_Gedcom_Tag::getLabel('PLAC'), '</div></th>
							<th><div class="col2">', KT_Gedcom_Tag::getLabel('LATI'), '</div></th>
							<th><div class="col3">', KT_Gedcom_Tag::getLabel('LONG'), '</div></th>
							<th><div class="col4">', KT_I18N::translate('Zoom factor'), '</div></th>
							<th><div class="col5">', KT_I18N::translate('Icon'), '</div></th>
							<th><div class="col6">', KT_I18N::translate('Edit'), '</div></th>
							<th><div class="col7">
								<input type="button" value="'. KT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(KT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'places\');} else {return false;}">
								<input type="checkbox" onClick="toggle_select(this)" style="vertical-align:middle;">
							</div></th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div id="gm_options">
		<table id="gm_manage">
			<tr>
				<td>',
					KT_I18N::translate('Add  a new geographic location'),
				'</td>
				<td>
					<form action="#" onsubmit="add_place_location(this.parent_id.options[this.parent_id.selectedIndex].value); return false;">',
						select_edit_control("parent_id", $where_am_i, KT_I18N::translate('Top Level'), $parent), '
						<button class="btn btn-primary" type="submit" value="', KT_I18N::translate('Add'), '">
							<i class="fa fa-plus"></i>' .
							KT_I18N::translate('Add') . '
						</button>
					</form>
				</td>
			</tr>
			<tr>
				<td>',
					KT_I18N::translate('Import all places from a family tree'),
				'</td>
				<td>
					<form action="module.php" method="get">
						<input type="hidden" name="mod" value="googlemap">
						<input type="hidden" name="mod_action" value="admin_places">
						<input type="hidden" name="action" value="ImportGedcom">',
						select_edit_control("ged", KT_Tree::getNameList(), null, KT_GEDCOM), '
						<button class="btn btn-primary" type="submit" value="', KT_I18N::translate('Import'), '">
							<i class="fa fa-arrow-right"></i>' .
							KT_I18N::translate('Import') . '
						</button>
					</form>
				</td>
			</tr>
			<tr>
				<td>',
					KT_I18N::translate('Upload geographic data'),
				'</td>
				<td>
					<form action="module.php" method="get">
						<input type="hidden" name="mod" value="googlemap">
						<input type="hidden" name="mod_action" value="admin_places">
						<input type="hidden" name="action" value="ImportFile">
						<button class="btn btn-primary" type="submit" value="', KT_I18N::translate('Upload'), '">
							<i class="fa fa-upload"></i>' .
							KT_I18N::translate('Upload') . '
						</button>
					</form>
				</td>
			</tr>
			<tr>
				<td>',
					KT_I18N::translate('Download geographic data'),
				'</td>
				<td>
					<form action="module.php" method="get">
						<input type="hidden" name="mod" value="googlemap">
						<input type="hidden" name="mod_action" value="admin_places">
						<input type="hidden" name="action" value="ExportFile">',
						select_edit_control("parent", $where_am_i, KT_I18N::translate('All'), KT_GED_ID), '
						<button class="btn btn-primary" type="submit" value="', KT_I18N::translate('Download'), '">
							<i class="fa fa-download"></i>' .
							KT_I18N::translate('Download') . '
						</button>
					</form>
				</td>
			</tr>
		</table>
	</div>
</div>'; // close #tabs
