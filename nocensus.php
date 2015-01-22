<?php
// Look for missing data in a GEDCOM file
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from PhpGedView
// Copyright (C) 2007 Greg Roach fisharebest@users.sourceforge.net
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

define('WT_SCRIPT_NAME', 'nocensus.php');
require './includes/session.php';

// Must be an admin user to use this module
if (!WT_USER_CAN_EDIT) {
	header("Location: login.php?url=nocensus.php");
	exit;
}

$controller=new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate(WT_I18N::translate('Missing Census Data')))
	->pageHeader()
	->addExternalJavaScript('js/autocomplete.js');
	
session_write_close();

//-- args
$surn = safe_GET('surn', '[^<>&%{};]*');
$plac = safe_GET('plac', '[^<>&%{};]*');
$dat = safe_GET('dat', '[^<>&%{};]*');

$ged = safe_GET('ged');
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
echo '<div class="box">';
echo '<h2 class="center">Individuals with missing census data</h2><br />';
?>
<form name="surnlist" id="surnlist" action="?">
	<table class="center facts_table width80">
		<tr>
			<td class="optionbox width20 wrap <?php echo $TEXT_DIRECTION; ?>" rowspan="4">
				Enter a surname, then select any combination of the two options "Census Place" and "Census Date"
			</td>
			<td class="descriptionbox <?php echo $TEXT_DIRECTION; ?>">
				<?php echo help_link("surname_help", "qm", "surname"); echo WT_Gedcom_Tag::getLabel('SURN'); ?>
			</td>
			<td class="optionbox <?php echo $TEXT_DIRECTION; ?>">
				<input type="text" name="surn" id="SURN" value="<?php echo $surn?>" />
				<input type="hidden" name="ged" id="ged" value="<?php echo $ged?>" />
				Enter "All" for everyone, or leave blank for your own ancestors
			</td>
		</tr>
		<tr>
			<td class="descriptionbox <?php echo $TEXT_DIRECTION; ?>">
				<?php echo help_link("surname_help", "qm", "surname"); ?>Census Place
			</td>
			<td class="optionbox <?php echo $TEXT_DIRECTION; ?>">
				<select name="plac">
					<?php
						echo "<option value=\"All\"";
						if ($plac == 'All') echo " selected=\"selected\"";
						echo ">All</option>";
						foreach ($places as $place_list) {
							echo "<option value=\"". $place_list. "\"";
							if ($place_list == $plac) echo " selected=\"selected\"";
							echo ">". $place_list. "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="descriptionbox <?php echo $TEXT_DIRECTION; ?>">
				<?php echo help_link("surname_help", "qm", "surname"); ?>Census Date
			</td>
			<td class="optionbox <?php echo $TEXT_DIRECTION; ?>">
				<select name="dat">
					<?php
						echo "<option value=\"All\"";
						if ($dat == 'All') echo " selected=\"selected\"";
						echo ">All</option>";
						foreach ($uk_dates as $date_list) {
							echo "<option value=\"". $date_list. "\"";
							if ($date_list == $dat) echo " selected=\"selected\"";
							echo ">". substr($date_list,7,4) . "</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>		
			<td colspan="2" class="optionbox <?php echo $TEXT_DIRECTION; ?>" style="text-align: center;">
				<input type="submit" value="<?php echo WT_I18N::translate('view'); ?>" />
			</td>
		</tr>
	</table>
	<br />
</form>
<?php

function add_parents(&$array, $indi) {
	if ($indi) {
		$array[]=$indi;
		foreach ($indi->getChildFamilies() as $parents) {
			add_parents($array, $parents->getHusband());
			add_parents($array, $parents->getWife());
		}
	}
}

if ($surn=='All' || $surn=='all') {
	$indis=WT_Query_Name::individuals('', '', '', false, false, WT_GED_ID);
} elseif ($surn) {
	$indis=WT_Query_Name::individuals($surn, '', '', false, false, WT_GED_ID);
} else {
	$id=WT_Tree::get(WT_GED_ID)->userPreference(WT_USER_ID, 'gedcomid');
	if (!$id) {
		$id=WT_Tree::get(WT_GED_ID)->userPreference(WT_USER_ID, 'rootid');
	}
	if (!$id) {
		$id='I1';
	}
	$indis=array();
	add_parents($indis, WT_Person::getInstance($id));
}

// Show sources to user
// Check each INDI against each SOUR
$n = 0;
foreach ($indis as $id=>$indi) {
	// Build up a list of significant life events for this individual
	$life=array();
	// Get a birth/death date for this indi
	// Make sure we have a BIRTH, whether it has a place or not
	$birt_jd=$indi->getEstimatedBirthDate()->JD();
	$birt_plac=$indi->getBirthPlace();
	$deat_jd=$indi->getEstimatedDeathDate()->JD();
	$deat_plac=$indi->getDeathPlace();

	// Create an array of events with dates
	foreach ($indi->getFacts() as $event) {
		if ($event->getTag()!='CHAN' && $event->getDate()->isOK()) {
			$life[]=$event;
		}
	}
	uasort($life, 'life_sort');
	// Now check for missing sources
	$missing_text='';
	foreach ($data_sources as $data_source) {
	$check1 = "{$data_source['place']}";
	$check2 = "{$data_source['date']}";
		if($check1 == $plac || $plac == 'All') {
			if($check2 == $dat || $dat == 'All') {
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
				if ($bef_jd==$data_source['jd'] && $bef_fact==$data_source['event'])
					continue;
				// If we were in the right place before/after the missing event, show it
				if (stripos($bef_plac, $data_source['place'])!==false || stripos($aft_plac, $data_source['place'])!==false) {
					$age_at_census = substr($data_source['date'],7,4) - $indi->getBirthDate()->gregorianYear();
					$desc_event=WT_Gedcom_Tag::getLabel($data_source['event']);
					$missing_text.="<li>{$data_source['place']} {$desc_event} for {$data_source['date']} <i><font size='-2'>({$age_at_census})</font></i></li>";
				}
			}
		}
	}
if ($missing_text) {
	switch ($n%3) {
	case 0:
		echo '<hr style="color:#D2B48C;" />';
		echo '<div class="font14" style="float:left; margin-left:1em; width:35%;">';
		break;
	case 1:
		echo '<div class="font14" style="float:left; width:35%;">';
		break;
	case 2:
		echo '<div class="font14" style="float:left;">';
		break;
	}

	$birth_year=$indi->getBirthDate()->gregorianYear();
	if ($birth_year==0) {
		$birth_year='????';
	}
	$death_year=$indi->getDeathDate()->gregorianYear();
	if ($death_year==0) {
		$death_year='????';
	}
	echo '<a target="_blank" href="', $indi->getHtmlUrl(), '">', $indi->getFullName(), '<span class="font12"> (', $birth_year, '-', $death_year, ') </span></a><ul>', $missing_text, '</ul></div>';

	switch ($n%3) {
	case 0:
		break;
	case 1:
		break;
	case 2:
		echo '<br /><div style="clear:left;"></div>';
		break;
	}
	++$n;
	} 	
}
if ($n == 0) echo "<div align=\"center\">No missing records found</div>";

echo '<div style="clear:left;"></div></div>';

function life_sort($a, $b) {
	if ($a->getDate()->minJD() < $b->getDate()->minJD()) return -1;
	if ($a->getDate()->minJD() > $b->getDate()->minJD()) return 1;
	return 0;
}
