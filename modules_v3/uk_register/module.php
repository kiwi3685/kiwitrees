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

class uk_register_WT_Module extends WT_Module implements WT_Module_Resources {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('UK 1939 Register');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “UK 1939 Register” module */ WT_I18N::translate('A list of missing data for the UK 1939 Register records');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {
		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&mod_action=show',
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $GEDCOM;
		$controller = new WT_Controller_Page();

		$controller
			->setPageTitle(WT_I18N::translate(WT_I18N::translate('Missing Register Data')))
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('autocomplete();');

		session_write_close();

		function life_sort($a, $b) {
			if ($a->getDate()->minJD() < $b->getDate()->minJD()) return -1;
			if ($a->getDate()->minJD() > $b->getDate()->minJD()) return 1;
			return 0;
		}

		function add_parents(&$array, $indi) {
			if ($indi) {
				$array[] = $indi;
				foreach ($indi->getChildFamilies() as $parents) {
					add_parents($array, $parents->getHusband());
					add_parents($array, $parents->getWife());
				}
			}
		}

		//-- args
		$surn	= safe_POST('surn', '[^<>&%{};]*');
		$plac	= safe_POST('plac', '[^<>&%{};]*');
		$dat	= '29 SEP 1939';
		$ged	= safe_POST('ged');
		if (empty($ged)) {
			$ged = $GEDCOM;
		}

		//List of Places
		$places = array('England', 'Wales');

		//List of Dates
		$uk_dates =  array(
			'29 SEP 1939'
		);

		// Generate a list of combined censuses or other facts
		foreach (array('29 SEP 1939' => GregorianToJD(9,29,1939)) as $date => $jd) {
			foreach ($places as $place) {
				$data_sources[] = array('event' => 'RESI', 'date' => $date, 'place' => $place, 'jd' => $jd);
			}
		}

		// Start Page -----------------------------------------------------------------------
		?>
			<div id="resource-page" class="ukregister">
				<h2><?php echo WT_I18N::translate('Individuals with missing register data'); ?></h2>
				<div class="noprint">
					<h4><?php echo WT_I18N::translate('Enter a surname, then select England or Wales from the country list'); ?></h3>
					<form name="surnlist" id="surnlist" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show">
						<div class="chart_options">
							<label for "SURN"><?php echo WT_Gedcom_Tag::getLabel('SURN'); ?></label>
							<input data-autocomplete-type="SURN" type="text" name="surn" id="SURN" value="<?php echo $surn; ?>">
							<input type="hidden" name="ged" id="ged" value="<?php echo $ged; ?>" >
							<div class="help_content">
								<p>
									<?php echo WT_I18N::translate('Select <b>All</b> for everyone, or leave blank for your own ancestors'); ?>
								</p>
							</div>
						</div>
						<div class="chart_options nocensus">
							<label for "cens_plac"><?php echo WT_I18N::translate('Country'); ?></label>
							<select name="plac" id="cens_plac">
								<?php
								echo '<option value="' . WT_I18N::translate('all') . '"';
									if ($plac == WT_I18N::translate('all')) {
										echo ' selected = "selected"';
									}
									echo '>' . WT_I18N::translate('all') . '
								</option>';
								foreach ($places as $place_list) {
									echo '<option value="' . $place_list. '"';
										if ($place_list == $plac) {
											echo ' selected = "selected"';
										}
										echo '>' . $place_list. '
									</option>';
								}
								?>
							</select>
						</div>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
					</form>
				</div>
				<hr style="clear:both;">
				<!-- end of form -->
		<?php

		if ($surn == WT_I18N::translate('All') || $surn == WT_I18N::translate('all')) {
			$indis = WT_Query_Name::individuals('', '', '', false, false, WT_GED_ID);
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

		// Notes about the register
		?>
		<h4><?php echo WT_I18N::translate('Notes'); ?></h4>
		<ol id="register_notes">
			<li><?php echo /* I18N: Note about UK 1939 Register check */ WT_I18N::translate('This list assumes entries relating to the 1939 Register are recorded using the <b>residence</b> GEDCOM tag (RESI))'); ?></li>
			<li><?php echo /* I18N: Note about UK 1939 Register check */ WT_I18N::translate('In general anyone who died after 1991, or is still alive, will be redacted (hidden) on the Register. They are listed here, but with a note indicating they are likely to be redacted. However, the Register is incomplete in this regard, so many people who died <u>before</u> 1991 are still redacted.'); ?></li>
			<li><?php echo /* I18N: Note about UK 1939 Register check */ WT_I18N::translate('Anyone serving in the military on 29 September 1939 is excluded from the Register. They are included in these lists but with a note that they may be in the military. This list assumes their military service is recorded with either the _MILI or _MILT GEDCOM tags'); ?></li>
		</ol>
		<?php
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
							if ($data_source['jd'] < $birt_jd || $data_source['jd'] > $deat_jd)
								continue;
							// Find where the person was immediately before/after
							$bef_plac	= $birt_plac;
							$aft_plac	= $deat_plac;
							$bef_fact	= 'BIRT';
							$bef_jd		= $birt_jd;
							$aft_jd		= $deat_jd;
							$aft_fact	= 'DEAT';

							foreach ($life as $event) {
								if ($event->getDate()->MinJD() <= $data_source['jd'] && $event->getDate()->MinJD() > $bef_jd) {
									$bef_jd   = $event->getDate()->MinJD();
									$bef_plac = $event->getPlace();
									$bef_fact = $event->getTag();
								}
								if ($event->getDate()->MinJD() >= $data_source['jd'] && $event->getDate()->MinJD() < $aft_jd) {
									$aft_jd   = $event->getDate()->MinJD();
									$aft_plac = $event->getPlace();
									$aft_fact = $event->getTag();
								}
							}
							// If we already have this event - skip
							if ($bef_jd == $data_source['jd'] && $bef_fact == $data_source['event'])
								continue;
							// If we were in the right place before/after the missing event, show it
							if (stripos($bef_plac, $data_source['place']) !== false || stripos($aft_plac, $data_source['place']) !== false) {
								$age_at_census = substr($data_source['date'],7,4) - $indi->getBirthDate()->gregorianYear();
								$desc_event = WT_Gedcom_Tag::getLabel($data_source['event']);
								$missing_text .= '<li><i style="font-size: 90%;">' . WT_I18N::translate('Age') . ' - ' . $age_at_census . '</i></li>';
								// Person died after 1991 - make note
								if ($indi->getEstimatedDeathDate()->gregorianYear() > '1991') {
									$missing_text .= '<li><i style="font-size: 90%;">' . WT_I18N::translate('Probably redacted - living or died after 1991') . '</i></li>';
								}
								// Check if person in military
								if ($bef_fact == '_MILI' || $bef_fact == '_MILT') {
									$missing_text .= '<li><i style="font-size: 90%;">' . WT_I18N::translate('Probably excluded - military service') . '</i></li>';
								}
							}
						}
					}
				}
			if ($missing_text) {
				$birth_year = $indi->getBirthDate()->gregorianYear();
				if ($birth_year == 0) {
					$birth_year='????';
				}
				$death_year = $indi->getDeathDate()->gregorianYear();
				if ($death_year == 0) {
					$death_year='????';
				}
				?>
					<li>
						<a target="_blank" href="<?php echo $indi->getHtmlUrl(); ?>"><?php echo $indi->getFullName(); ?>
							<span> (<?php echo $birth_year . '-' . $death_year; ?>) </span>
						</a>
						<ul><?php echo $missing_text; ?></ul>
					</li>
				<?php
				++$n;
				}
			}
			if ($n == 0 && $surn) {
				echo '<div class="center error">' . WT_I18N::translate('No missing records found') . '</div>';
			} else {
				echo '<div class="center">' . WT_I18N::plural('%s record found', '%s records found', $n, $n) . '</div>';
			}
		echo '
			</ul>
	</div>';
	}
}
