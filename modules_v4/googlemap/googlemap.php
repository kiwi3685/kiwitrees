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

// functions copied from print_fact_place
function print_fact_place_map($factrec) {
	$ct = preg_match("/2 PLAC (.*)/", $factrec, $match);
	if ($ct>0) {
		$retStr = ' ';
		$levels = explode(',', $match[1]);
		$place = trim($match[1]);
		// reverse the array so that we get the top level first
		$levels = array_reverse($levels);
		$retStr .= '<a href="placelist.php?action=show';
		foreach ($levels as $pindex=>$ppart) {
			// routine for replacing ampersands
			$ppart = preg_replace("/amp\%3B/", "", trim($ppart));
			$retStr .= "&amp;parent[$pindex]=".$ppart;
		}
		$retStr .= '"> '.htmlspecialchars((string) $place).'</a>';
		return $retStr;
	}
	return '';
}

function rem_prefix_from_placename($prefix_list, $place, $placelist) {
	if ($prefix_list) {
		foreach (explode(';', $prefix_list) as $prefix) {
			if ($prefix && substr($place, 0, strlen($prefix)+1)==$prefix.' ') {
				$placelist[] = substr($place, strlen($prefix)+1);
			}
		}
	}
	return $placelist;
}

function rem_postfix_from_placename($postfix_list, $place, $placelist) {
	if ($postfix_list) {
		foreach (explode (';', $postfix_list) as $postfix) {
			if ($postfix && substr($place, -strlen($postfix)-1)==' '.$postfix) {
				$placelist[] = substr($place, 0, strlen($place)-strlen($postfix)-1);
			}
		}
	}
	return $placelist;
}

function rem_prefix_postfix_from_placename($prefix_list, $postfix_list, $place, $placelist) {
	if ($prefix_list && $postfix_list) {
		foreach (explode (";", $prefix_list) as $prefix) {
			foreach (explode (";", $postfix_list) as $postfix) {
				if ($prefix && $postfix && substr($place, 0, strlen($prefix)+1)==$prefix.' ' && substr($place, -strlen($postfix)-1)==' '.$postfix) {
					$placelist[] = substr($place, strlen($prefix)+1, strlen($place)-strlen($prefix)-strlen($postfix)-2);
				}
			}
		}
	}
	return $placelist;
}

function create_possible_place_names ($placename, $level) {
	global $GM_PREFIX, $GM_POSTFIX;

	$retlist = array();
	if ($level<=9) {
		$retlist = rem_prefix_postfix_from_placename($GM_PREFIX[$level], $GM_POSTFIX[$level], $placename, $retlist); // Remove both
		$retlist = rem_prefix_from_placename($GM_PREFIX[$level], $placename, $retlist); // Remove prefix
		$retlist = rem_postfix_from_placename($GM_POSTFIX[$level], $placename, $retlist); // Remove suffix
	}
	$retlist[]=$placename; // Exact

	return $retlist;
}

function abbreviate($text) {
	if (utf8_strlen($text)>13) {
		if (trim(utf8_substr($text, 10, 1))!='') {
			$desc = utf8_substr($text, 0, 11).'.';
		} else {
			$desc = trim(utf8_substr($text, 0, 11));
		}
	}
	else $desc = $text;
	return $desc;
}

function get_lati_long_placelocation ($place) {
	$parent = explode (',', $place);
	$parent = array_reverse($parent);
	$place_id = 0;
	for ($i=0; $i<count($parent); $i++) {
		$parent[$i] = trim($parent[$i]);
		if (empty($parent[$i])) $parent[$i]='unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,
		$placelist = create_possible_place_names($parent[$i], $i+1);
		foreach ($placelist as $key => $placename) {
			$pl_id=
				KT_DB::prepare("SELECT pl_id FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place")
				->execute(array($i, $place_id, $placename))
				->fetchOne();
			if (!empty($pl_id)) break;
		}
		if (empty($pl_id)) break;
		$place_id = $pl_id;
	}

	$row=
		// KT_DB::prepare("SELECT pl_lati, pl_long, pl_zoom, pl_icon, pl_level FROM `##placelocation` WHERE pl_id=? ORDER BY pl_place")
		KT_DB::prepare("SELECT pl_media, sv_lati, sv_long, sv_bearing, sv_elevation, sv_zoom, pl_lati, pl_long, pl_zoom, pl_icon, pl_level FROM `##placelocation` WHERE pl_id=? ORDER BY pl_place")
		->execute(array($place_id))
		->fetchOneRow();
	if ($row) {
		return array('media'=>$row->pl_media, 'sv_lati'=>$row->sv_lati, 'sv_long'=>$row->sv_long, 'sv_bearing'=>$row->sv_bearing, 'sv_elevation'=>$row->sv_elevation, 'sv_zoom'=>$row->sv_zoom, 'lati'=>$row->pl_lati, 'long'=>$row->pl_long, 'zoom'=>$row->pl_zoom, 'icon'=>$row->pl_icon, 'level'=>$row->pl_level);
	} else {
		return array();
	}
}

function setup_map() {
	global $GOOGLEMAP_MIN_ZOOM, $GOOGLEMAP_MAX_ZOOM;

	?>
	<script src="<?php echo KT_GM_SCRIPT; ?>"></script>
	<script>
		var minZoomLevel = <?php echo $GOOGLEMAP_MIN_ZOOM;?>;
		var maxZoomLevel = <?php echo $GOOGLEMAP_MAX_ZOOM;?>;
		var startZoomLevel = <?php echo $GOOGLEMAP_MAX_ZOOM;?>;
	</script>
	<?php
}

function build_indiv_map($indifacts, $famids) {
	global $controller, $GOOGLEMAP_MAX_ZOOM, $GM_DEFAULT_TOP_VALUE;

	// Create the markers list array
	$markers = array();
	// Add the events to the markers list array
	//-- sort the facts into date order
	sort_facts($indifacts);
	$i = 0;
	foreach ($indifacts as $key => $value) {
		$fact = $value->getTag();
		$fact_data=$value->getDetail();
		$factrec = $value->getGedComRecord();
		$placerec = null;

		if ($value->getPlace()!=null) {
			$placerec = get_sub_record(2, '2 PLAC', $factrec);
			$addrFound = false;
		} else {
			if (preg_match("/\d ADDR (.*)/", $factrec, $match)) {
				$placerec = get_sub_record(1, "\d ADDR", $factrec);
				$addrFound = true;
			}
		}
		if (!empty($placerec)) {
			$ctla = preg_match("/\d LATI (.*)/", $placerec, $match1);
			$ctlo = preg_match("/\d LONG (.*)/", $placerec, $match2);
			$spouse = $value->getSpouse();
			if ($spouse) {
				$useThisItem = $spouse->canDisplayDetails();
			} else {
				$useThisItem = true;
			}
			if (($ctla > 0) && ($ctlo > 0) && ($useThisItem == true)) {
				$i++;
				$markers[$i]=array(
					'class'      => 'optionbox',
					'index'      => '',
					'tabindex'   => '',
					'placed'     => 'no',
					'fact'       => $fact,
					'fact_label' => KT_Gedcom_Tag::getLabel($fact),
					'info'       => $fact_data=='Y' ? '' : $fact_data,
					'placerec'   => $placerec,
					'lati'       => str_replace(array('N', 'S', ','), array('', '-', '.') , $match1[1]),
					'lng'        => str_replace(array('E', 'W', ','), array('', '-', '.') , $match2[1]),
				);
				$ctd = preg_match("/2 DATE (.+)/", $factrec, $match);
				if ($ctd > 0) {
					$markers[$i]['date'] = $match[1];
				}
				if ($spouse) {
					$markers[$i]['name']		= $spouse->getXref();
				}
				if ($spouse && ($fact == 'HUSB' || $fact == 'WIFE')) {
					$markers[$i]['fact_label']	= get_relationship_name(get_relationship($controller->record, $spouse, true, 3));
				}
			} else {
				if ($useThisItem == true && $addrFound == false) {
					$ctpl = preg_match("/\d PLAC (.*)/", $placerec, $match1);
					$latlongval = get_lati_long_placelocation($match1[1]);
					if ((count($latlongval) == 0) && (!empty($GM_DEFAULT_TOP_VALUE))) {
						$latlongval = get_lati_long_placelocation($match1[1].', '.$GM_DEFAULT_TOP_VALUE);
						if ((count($latlongval) != 0) && ($latlongval['level'] == 0)) {
							$latlongval['lati'] = NULL;
							$latlongval['long'] = NULL;
						}
					}
					if ((count($latlongval) != 0) && ($latlongval['lati'] != NULL) && ($latlongval['long'] != NULL)) {
						$i++;
						$markers[$i]=array(
							'class'      => 'optionbox',
							'index'      => '',
							'tabindex'   => '',
							'placed'     => 'no',
							'fact'       => $fact,
							'fact_label' => KT_Gedcom_Tag::getLabel($fact),
							'info'       => $fact_data=='Y' ? '' : $fact_data,
							'placerec'   => $placerec,
						);
						$markers[$i]['icon'] = $latlongval['icon'];
						if ($GOOGLEMAP_MAX_ZOOM > $latlongval['zoom']) {
							$GOOGLEMAP_MAX_ZOOM = $latlongval['zoom'];
						}
						$markers[$i]['lati'] = str_replace(array('N', 'S', ','), array('', '-', '.') , $latlongval['lati']);
						$markers[$i]['lng'] = str_replace(array('E', 'W', ','), array('', '-', '.') , $latlongval['long']);
						$markers[$i]['media'] = $latlongval['media'];
						$markers[$i]['sv_lati'] = $latlongval['sv_lati'];
						$markers[$i]['sv_long'] = $latlongval['sv_long'];
						$markers[$i]['sv_bearing'] = $latlongval['sv_bearing'];
						$markers[$i]['sv_elevation'] = $latlongval['sv_elevation'];
						$markers[$i]['sv_zoom'] = $latlongval['sv_zoom'];
						$ctd = preg_match("/2 DATE (.+)/", $factrec, $match);
						if ($ctd>0) {
							$markers[$i]['date'] = $match[1];
						}
						if ($spouse) {
							$markers[$i]['name']		= $spouse->getXref();
						}
						if ($spouse && ($fact == 'HUSB' || $fact == 'WIFE')) {
							$markers[$i]['fact_label']	= get_relationship_name(get_relationship($controller->record, $spouse, true, 3));
						}
					}
				}
			}
		}
	}

	// Add children to the markers list array
	if (count($famids)>0) {
		$hparents=false;
		for ($f=0; $f<count($famids); $f++) {
			if (!empty($famids[$f])) {
				$famrec = find_gedcom_record($famids[$f], KT_GED_ID, true);
				if ($famrec) {
					$num = preg_match_all("/1\s*CHIL\s*@(.*)@/", $famrec, $smatch, PREG_SET_ORDER);
					for ($j=0; $j<$num; $j++) {
						$person = KT_Person::getInstance($smatch[$j][1]);
						if ($person->canDisplayDetails()) {
							$srec = find_person_record($smatch[$j][1], KT_GED_ID);
							$birthrec = '';
							$placerec = '';
							foreach ($person->getAllFactsByType(explode('|', KT_EVENTS_BIRT)) as $sEvent) {
								$birthrec = $sEvent->getGedcomRecord();
								$placerec = get_sub_record(2, '2 PLAC', $birthrec);
								if (!empty($placerec)) {
									$ctd = preg_match("/\d DATE (.*)/", $birthrec, $matchd);
									$ctla = preg_match("/\d LATI (.*)/", $placerec, $match1);
									$ctlo = preg_match("/\d LONG (.*)/", $placerec, $match2);
									if (($ctla>0) && ($ctlo>0)) {
										$i++;
										$markers[$i]=array('index'=>'', 'tabindex'=>'', 'placed'=>'no');
										if (strpos($srec, "\n1 SEX F")!==false) {
											$markers[$i]['fact']       = 'BIRT';
											$markers[$i]['fact_label'] = KT_I18N::translate('daughter');
											$markers[$i]['class']      = 'person_boxF';
										} else {
											if (strpos($srec, "\n1 SEX M")!==false) {
												$markers[$i]['fact']       = 'BIRT';
												$markers[$i]['fact_label'] = KT_I18N::translate('son');
												$markers[$i]['class']      = 'person_box';
											} else {
												$markers[$i]['fact']       = 'BIRT';
												$markers[$i]['fact_label'] = KT_I18N::translate('child');
												$markers[$i]['class']      = 'person_boxNN';
											}
										}
										$markers[$i]['placerec'] = $placerec;
										$match1[1] = trim($match1[1]);
										$match2[1] = trim($match2[1]);
										$markers[$i]['lati'] = str_replace(array('N', 'S', ','), array('', '-', '.'), $match1[1]);
										$markers[$i]['lng'] = str_replace(array('E', 'W', ','), array('', '-', '.'), $match2[1]);
										if ($ctd > 0) {
											$markers[$i]['date'] = $matchd[1];
										}
										$markers[$i]['name'] = $smatch[$j][1];
									} else {
										$ctpl = preg_match("/\d PLAC (.*)/", $placerec, $match1);
										$latlongval = get_lati_long_placelocation($match1[1]);
										if ((count($latlongval) == 0) && (!empty($GM_DEFAULT_TOP_VALUE))) {
											$latlongval = get_lati_long_placelocation($match1[1].', '.$GM_DEFAULT_TOP_VALUE);
											if ((count($latlongval) != 0) && ($latlongval['level'] == 0)) {
												$latlongval['lati'] = NULL;
												$latlongval['long'] = NULL;
											}
										}
										if ((count($latlongval) != 0) && ($latlongval['lati'] != NULL) && ($latlongval['long'] != NULL)) {
											$i++;
											$markers[$i]=array('index'=>'', 'tabindex'=>'', 'placed'=>'no');
											$markers[$i]['fact']		= 'BIRT';
											$markers[$i]['fact_label']	= KT_I18N::translate('child');
											$markers[$i]['class']		= 'option_boxNN';
											if (strpos($srec, "\n1 SEX F")!==false) {
												$markers[$i]['fact']		= 'BIRT';
												$markers[$i]['fact_label']	= KT_I18N::translate('daughter');
												$markers[$i]['class']		= 'person_boxF';
											}
											if (strpos($srec, "\n1 SEX M")!==false) {
												$markers[$i]['fact']		= 'BIRT';
												$markers[$i]['fact_label']	= KT_I18N::translate('son');
												$markers[$i]['class']		= 'person_box';
											}
											$markers[$i]['icon'] = $latlongval['icon'];
											$markers[$i]['placerec'] = $placerec;
											if ($GOOGLEMAP_MAX_ZOOM > $latlongval['zoom']) {
												$GOOGLEMAP_MAX_ZOOM = $latlongval['zoom'];
											}
											$markers[$i]['lati'] = str_replace(array('N', 'S', ','), array('', '-', '.'), $latlongval['lati']);
											$markers[$i]['lng']  = str_replace(array('E', 'W', ','), array('', '-', '.'), $latlongval['long']);
											if ($ctd > 0) {
												$markers[$i]['date'] = $matchd[1];
											}
											$markers[$i]['name'] = $smatch[$j][1];
											$markers[$i]['media'] = $latlongval['media'];
											$markers[$i]['sv_lati'] = $latlongval['sv_lati'];
											$markers[$i]['sv_long'] = $latlongval['sv_long'];
											$markers[$i]['sv_bearing'] = $latlongval['sv_bearing'];
											$markers[$i]['sv_elevation'] = $latlongval['sv_elevation'];
											$markers[$i]['sv_zoom'] = $latlongval['sv_zoom'];
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	// Prepare the $markers array for use by the following "required" file/files
	if ($i != 0) {
		$indexcounter = 0;
		for ($j=1; $j<=$i; $j++) {
			if ($markers[$j]['placed'] == 'no') {
				$multimarker = -1;
				// Count nr of locations where the long/lati is identical
				for ($k=$j; $k<=$i; $k++) {
					if (($markers[$j]['lati'] == $markers[$k]['lati']) && ($markers[$j]['lng'] == $markers[$k]['lng'])) {
						$multimarker = $multimarker + 1;
					}
				}
				// If only one location with this long/lati combination
				if ($multimarker == 0) {
					// --- NOTE for V3 api, following line is changed from "yes" to "no"
					// --- This aids in identifying multi-event locations
					$markers[$j]['placed'] = 'no';
					$markers[$j]['index'] = $indexcounter;
					$markers[$j]['tabindex'] = 0;
					$indexcounter = $indexcounter + 1;
				} else {
					$tabcounter = 0;
					$markersindex = 0;
					$markers[$j]['placed'] = 'yes';
					$markers[$j]['index'] = $indexcounter;
					$markers[$j]['tabindex'] = $tabcounter;
					$tabcounter = $tabcounter + 1;
					for ($k=$j+1; $k<=$i; $k++) {
						if (($markers[$j]['lati'] == $markers[$k]['lati']) && ($markers[$j]['lng'] == $markers[$k]['lng'])) {
							$markers[$k]['placed'] = 'yes';
							$markers[$k]['index'] = $indexcounter;
							// if ($tabcounter == 4) {
							// V3 ==============================
							if ($tabcounter == 30) {
							// V3 ==============================
								$indexcounter = $indexcounter + 1;
								$tabcounter = 0;
								$markersindex = $markersindex + 1;
							}
							$markers[$k]['index'] = $indexcounter;
							$markers[$k]['tabindex'] = $tabcounter;
							$tabcounter = $tabcounter + 1;
						}
					}
					$indexcounter = $indexcounter + 1;
				}
			}
		}
		// add $gmarks array to the required googlemap.js.php
		$gmarks = $markers;
		$pid	= $controller->record->getXref();
		require_once KT_ROOT.KT_MODULES_DIR.'googlemap/googlemap.js.php';
		// Create the normal googlemap sidebar of events and children
		echo '
			<div  id="map_content" style="border:1px solid; overflow-x: hidden; overflow-y: auto; margin-top: 10px;">
				<table class="facts_table">';
					$z=0;
					foreach($markers as $marker) {
						echo '<tr>
							<td class="facts_label">
								<a href="#" onclick="myclick(', $z, ', ', $marker['index'], ', ', $marker['tabindex'], ')">', $marker['fact_label'], '</a>
							</td>';
							$z++;
							echo '<td class="', $marker['class'], '" style="white-space:normal;">';
								if (!empty($marker['name'])) {
									$person = KT_Person::getInstance($marker['name']);
									if ($person) {
										echo '<span style="margin:0 10px; display:inline-block;"><a href="', $person->getHtmlUrl(), '">', $person->getFullName(), '</a></span>';
									}
								}
								echo '<span  style="margin:0 10px; display:inline-block;">', print_fact_place_map($marker['placerec']), '</span>';
								if (!empty($marker['date'])) {
									$date = new KT_Date($marker['date']);
									echo '<span style="margin:0 10px; display:inline-block;">', $date->Display(true), '</span>';
								}

							echo '</td>';
						echo '</tr>';
					}
				echo '</table>
			</div>
		<br>';
	} // end prepare markers array

	return $i;
} // end build_indiv_map function
