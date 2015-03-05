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

class no_census_WT_Module extends WT_Module implements WT_Module_Menu {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('UK Census check');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “UK Census check” module */ WT_I18N::translate('A list of missing census data, for UK census records only');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 21;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		return null;
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		}
	}

	private function show() {
		global $controller, $GEDCOM;
		$controller=new WT_Controller_Page();

		$controller
			->setPageTitle(WT_I18N::translate(WT_I18N::translate('Missing Census Data')))
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('autocomplete();');

		session_write_close();

		//-- args
		$surn = safe_POST('surn', '[^<>&%{};]*');
		$plac = safe_POST('plac', '[^<>&%{};]*');
		$dat = safe_POST('dat', '[^<>&%{};]*');

		$ged = safe_POST('ged');
		if (empty($ged)) {
			$ged = $GEDCOM;
		}

		//List of Places
		$places = array('England', 'Wales', 'Scotland', 'Isle of Man', 'Channel Islands');

		//List of Dates
		$uk_dates =  array(
			'06 JUN 1841',
			'30 MAR 1851',
			'07 APR 1861',
			'02 APR 1871',
			'03 APR 1881',
			'05 APR 1891',
			'31 MAR 1901',
			'02 APR 1911'
		);

		// Generate a list of combined censuses or other facts
		foreach (array(
			'06 JUN 1841'=>GregorianToJD(6,6,1841),
			'30 MAR 1851'=>GregorianToJD(3,30,1851),
			'07 APR 1861'=>GregorianToJD(4,7,1861),
			'02 APR 1871'=>GregorianToJD(4,2,1871),
			'03 APR 1881'=>GregorianToJD(4,3,1881),
			'05 APR 1891'=>GregorianToJD(4,5,1891),
			'31 MAR 1901'=>GregorianToJD(3,31,1901),
			'02 APR 1911'=>GregorianToJD(4,02,1911)
		) as $date=>$jd)
			foreach ($places as $place)
				$data_sources[]=array('event'=>'CENS', 'date'=>$date, 'place'=>$place, 'jd'=>$jd);
				
		// Start Page -----------------------------------------------------------------------
		echo '

			<div class="nocensus_page">
				<h2 class="center">' . WT_I18N::translate('Individuals with missing census data') . '</h2>
				<h4 class="center">' . WT_I18N::translate('Enter a surname, then select any combination of the two options Census place and Census date') . '</h3>
				<form method="post" id="surnlist" name="surnlist" action="module.php?mod=no_census&amp;mod_action=show&theme='.basename(WT_THEME_DIR). '">
					<div class="nocensus">
						<label for "SURN" style="width: 100px;display: inline-block;">' . WT_Gedcom_Tag::getLabel('SURN') . '</label>
						<input data-autocomplete-type="SURN" type="text" name="surn" id="SURN" value="' . $surn . '">
						<input type="hidden" name="ged" id="ged" value="' . $ged . '" >
						' . WT_I18N::translate('Select <b>All</b> for everyone, or leave blank for your own ancestors') . '
					</div>
					<div class="nocensus">
						<label for "cens_plac">' . WT_I18N::translate('Census Place') . '</label>
						<select name="plac" id="cens_plac">
							<option value="' . WT_I18N::translate('all') . '"';
								if ($plac == WT_I18N::translate('all')) echo ' selected="selected"';
								echo '>' . WT_I18N::translate('all') . '
							</option>';
							foreach ($places as $place_list) {
								echo '<option value="' . $place_list. '"';
									if ($place_list == $plac) echo ' selected="selected"';
									echo ">". $place_list. "
								</option>";
							}
						echo '</select>
					</div>
					<div class="nocensus">
						<label for "cens_dat">' . WT_I18N::translate('Census date') . '</label>
						<select name="dat"  id="cens_dat">
							<option value="' . WT_I18N::translate('all') . '"';
								if ($dat == WT_I18N::translate('all')) echo ' selected="selected"';
								echo '>' . WT_I18N::translate('all') . '
							</option>';
							foreach ($uk_dates as $date_list) {
								echo '<option value="' . $date_list. '"';
									if ($date_list == $dat) echo ' selected="selected"';
									echo ">". substr($date_list,7,4). "
								</option>";
							}
						echo '</select>
					</div>
					<button style="width: 50px;margin: 20px auto;display: block;padding: 5px;" type="submit">', WT_I18N::translate('view'), '</button>
				</form>
		';
		?>
		<?php

		function life_sort($a, $b) {
			if ($a->getDate()->minJD() < $b->getDate()->minJD()) return -1;
			if ($a->getDate()->minJD() > $b->getDate()->minJD()) return 1;
			return 0;
		}

		function add_parents(&$array, $indi) {
			if ($indi) {
				$array[]=$indi;
				foreach ($indi->getChildFamilies() as $parents) {
					add_parents($array, $parents->getHusband());
					add_parents($array, $parents->getWife());
				}
			}
		}

		if ($surn == WT_I18N::translate('All') || $surn == WT_I18N::translate('all')) {
			$indis=WT_Query_Name::individuals('', '', '', false, false, WT_GED_ID);
		} elseif ($surn) {
			$indis = WT_Query_Name::individuals($surn, '', '', false, false, WT_GED_ID);
		} else {
			$id = WT_Tree::get(WT_GED_ID)->userPreference(WT_USER_ID, 'gedcomid');
			if (!$id) {
				$id = WT_Tree::get(WT_GED_ID)->userPreference(WT_USER_ID, 'rootid');
			}
			if (!$id) {
				$id = 'I1';
			}
			$indis = array();
			add_parents($indis, WT_Person::getInstance($id));
		}

		// Show sources to user
		echo '<ul id="nocensus_result">';
			// Check each INDI against each SOUR
			$n = 0;
			foreach ($indis as $id=>$indi) {
				// Build up a list of significant life events for this individual
				$life = array();
				// Get a birth/death date for this indi
				// Make sure we have a BIRTH, whether it has a place or not
				$birt_jd	= $indi->getEstimatedBirthDate()->JD();
				$birt_plac	= $indi->getBirthPlace();
				$deat_jd	= $indi->getEstimatedDeathDate()->JD();
				$deat_plac	= $indi->getDeathPlace();

				// Create an array of events with dates
				foreach ($indi->getFacts() as $event) {
					if ($event->getTag() != 'CHAN' && $event->getDate()->isOK()) {
						$life[] = $event;
					}
				}
				uasort($life, 'life_sort');
				// Now check for missing sources
				$missing_text = '';
				foreach ($data_sources as $data_source) {
				$check1 = "{$data_source['place']}";
				$check2 = "{$data_source['date']}";
					if($check1 == $plac || $plac == WT_I18N::translate('all')) {
						if($check2 == $dat || $dat == WT_I18N::translate('all')) {
							// Person not alive - skip
							if ($data_source['jd']<$birt_jd || $data_source['jd']>$deat_jd)
								continue;
							// Find where the person was immediately before/after
							$bef_plac=$birt_plac;
							$aft_plac=$deat_plac;
							$bef_fact='BIRT';
							$bef_jd=$birt_jd;
							$aft_jd=$deat_jd;
							$aft_fact='DEAT';
							foreach ($life as $event) {
								if ($event->getDate()->MinJD()<=$data_source['jd'] && $event->getDate()->MinJD()>$bef_jd) {
									$bef_jd  =$event->getDate()->MinJD();
									$bef_plac=$event->getPlace();
									$bef_fact=$event->getTag();
								}
								if ($event->getDate()->MinJD()>=$data_source['jd'] && $event->getDate()->MinJD()<$aft_jd) {
									$aft_jd  =$event->getDate()->MinJD();
									$aft_plac=$event->getPlace();
									$aft_fact=$event->getTag();
								}
							}
							// If we already have this event - skip
							if ($bef_jd == $data_source['jd'] && $bef_fact == $data_source['event'])
								continue;
							// If we were in the right place before/after the missing event, show it
							if (stripos($bef_plac, $data_source['place']) !== false || stripos($aft_plac, $data_source['place']) !== false) {
								$age_at_census = substr($data_source['date'],7,4) - $indi->getBirthDate()->gregorianYear();
								$desc_event=WT_Gedcom_Tag::getLabel($data_source['event']);
								$missing_text.="<li>{$data_source['place']} {$desc_event} for {$data_source['date']} <i><font size='-2'>({$age_at_census})</font></i></li>";
							}
						}
					}
				}
			if ($missing_text) {
				$birth_year=$indi->getBirthDate()->gregorianYear();
				if ($birth_year == 0) {
					$birth_year='????';
				}
				$death_year=$indi->getDeathDate()->gregorianYear();
				if ($death_year == 0) {
					$death_year='????';
				}
				echo '
					<li>
						<a target="_blank" href="', $indi->getHtmlUrl(), '">', $indi->getFullName(), '
							<span> (', $birth_year, '-', $death_year, ') </span>
						</a>
						<ul>', $missing_text, '</ul>
					</li>
				';
				++$n;
				} 	
			}
			if ($n == 0) echo '<div class="center error">' . WT_I18N::translate('No missing records found') . '</div>';
		echo '
			</ul>
	</div>';
	}

	private function life_sort($a, $b) {
		if ($a->getDate()->minJD() < $b->getDate()->minJD()) return -1;
		if ($a->getDate()->minJD() > $b->getDate()->minJD()) return 1;
		return 0;
	}

}