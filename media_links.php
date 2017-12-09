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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$controller = new KT_Controller_Individual();
global $spouselinks, $parentlinks, $DeathYr, $BirthYr, $censyear, $censdate;

$pid = KT_Filter::get('pid');

if (KT_Family::getInstance($pid)) {
	$record	= KT_Family::getInstance($pid);
	if ($record->getHusband()->getXref()) {
		$pid = $record->getHusband()->getXref();
	} elseif ($record->getWife()->getXref()) {
		$pid = $record->getWife()->getXref();
	}
}

$person		= KT_Person::getInstance($pid);
$currpid	= $pid;
$person->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $person->getDeathYear();
$person->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $person->getBirthYear();

?>
<div id="media-links">
	<table>
		<tr>
			<th colspan="2">
				<?php echo KT_I18N::translate('Family navigator'); ?>
			</th>
		<tr>
			<td colspan="2" class="descriptionbox wrap center">
				<?php echo KT_I18N::translate('Click name to add person to list of links.'); ?>
			</td>
		</tr>
		<?php
		//-- Build Parent Family -------------
		$personcount	= 0;
		$families		= $person->getChildFamilies();
		foreach ($families as $family) {
			$label		= $person->getChildFamilyLabel($family);
			$people		= $controller->buildFamilyList($family, "parents");
			$marrdate	= $family->getMarriageDate();
			// Parents - husband
			if (isset($people["husb"])) {
				$fulln		= strip_tags($people['husb']->getFullName());
				$menu		= new KT_Menu(getCloseRelationshipName($person, $people["husb"]));
				$slabel		= print_pedigree_person_nav2($people["husb"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu); ?>
				<tr>
					<td>
						<?php echo $menu->getMenu(); ?>
					</td>
					<td align="left">
						<?php if (($people["husb"]->canDisplayDetails())) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people["husb"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people["husb"]->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				</tr>
			<?php }
			// Parents - wife
			if (isset($people["wife"])) {
				$fulln		= strip_tags($people['wife']->getFullName());
				$menu		= new KT_Menu(getCloseRelationshipName($person, $people["wife"]));
				$slabel		= print_pedigree_person_nav2($people["wife"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu); ?>
				<tr>
					<td>
						<?php echo $menu->getMenu(); ?>
					</td>
					<td align="left">
						<?php if (($people["wife"]->canDisplayDetails())) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people["wife"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people["wife"]->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				</tr>
			<?php }
			// Parents - siblings
			if (isset($people["children"])) {
				$elderdate = $family->getMarriageDate();
				foreach ($people["children"] as $key=>$child) {
					$fulln		= strip_tags($child->getFullName());
					$menu		= new KT_Menu(getCloseRelationshipName($person, $child));
					$slabel		= print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $currpid, $censyear);
					$slabel		.= $spouselinks;
					$submenu	= new KT_Menu($slabel);
					$menu->addSubMenu($submenu);
					// Only print current person in immediate family group
					if ($child->getXref() != $pid) { ?>
						<tr>
							<td>
								<?php if ($child->getXref() == $pid) {
									echo $child->getLabel();
								} else {
									echo $menu->getMenu();
								} ?>
							</td>
							<td align="left">
								<?php if ($child->canDisplayDetails()) { ?>
									<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
										<?php echo $child->getFullName(); ?>
									</a>
									<?php
								} else {
									echo KT_I18N::translate('Private');
								} ?>
							</td>
						</tr>
					<?php }
				}
				$elderdate = $child->getBirthDate(false);
			}
		}
		//-- Build step families -------
		foreach ($person->getChildStepFamilies() as $family) {
			$label = $person->getStepFamilyLabel($family);
			$people = $controller->buildFamilyList($family, "step-parents");
			if ($people) { ?>
				<!-- blank row 1-->
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			<?php }
			$marrdate = $family->getMarriageDate();
			// Husband ----------
			$elderdate = "";
			if (isset($people["husb"]) ) {
				$fulln 		= strip_tags($people['husb']->getFullName());
				$menu		= new KT_Menu();
				$menu->addLabel(getCloseRelationshipName($person, $people["husb"]));
				$slabel		= print_pedigree_person_nav2($people["husb"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				$people["husb"]->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $people["husb"]->getDeathYear();
				$people["husb"]->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $people["husb"]->getBirthYear(); ?>
				<tr>
					<td>
						<?php echo $menu->getMenu(); ?>
					</td>
					<td align="left">
						<?php if ($people["husb"]->canDisplayDetails()) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people["husb"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people["husb"]->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				</tr>
				<?php $elderdate = $people["husb"]->getBirthDate(false);
			}
			// Wife
			if (isset($people["wife"]) ) {
				$fulln		= strip_tags($people['wife']->getFullName());
				$menu		= new KT_Menu();
				$menu->addLabel(getCloseRelationshipName($person, $people["wife"]));
				$slabel		= print_pedigree_person_nav2($people["wife"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				$people["wife"]->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $people["wife"]->getDeathYear();
				$people["wife"]->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $people["wife"]->getBirthYear(); ?>
				<tr>
					<td>
						<?php echo $menu->getMenu(); ?>
					</td>
					<td align="left">
						<?php if ($people["wife"]->canDisplayDetails()) { ?>
						<a href="#" onclick="insertRowToTable('<?php echo $people["wife"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
							<?php echo $people["wife"]->getFullName(); ?>
						</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				</tr>
			<?php }
			// Children --
			if (isset($people["children"])) {
				$elderdate = $family->getMarriageDate();
				foreach ($people["children"] as $key=>$child) {
					$fulln		= strip_tags($child->getFullName());
					$menu		= new KT_Menu(getCloseRelationshipName($person, $child));
					$slabel		= print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $currpid, $censyear);
					$slabel		.= $spouselinks;
					$submenu	= new KT_Menu($slabel);
					$menu->addSubMenu($submenu);
					$child->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $child->getDeathYear();
					$child->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $child->getBirthYear(); ?>
					<tr>
						<td>
							<?php echo $menu->getMenu(); ?>
						</td>
						<td align="left">
							<?php if ($child->canDisplayDetails()) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $child->getFullName(); ?>
							</a>
							<?php } else {
								echo KT_I18N::translate('Private');
							} ?>
						</td>
					</tr>
				<?php }
			}
		} ?>
		<?php //-- Build Spouse Family -------------
		$families = $person->getSpouseFamilies();
		foreach ($families as $family) {
			$people = $controller->buildFamilyList($family, "spouse");
			if ($people) { ?>
				<!-- blank row 2-->
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
			<?php }
			$marrdate = $family->getMarriageDate();
			// Husband
			if (isset($people["husb"])) {
				$fulln		= strip_tags($people['husb']->getFullName());
				$menu		= new KT_Menu(getCloseRelationshipName($person, $people["husb"]));
				$slabel		= print_pedigree_person_nav2($people["husb"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				$people["husb"]->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $people["husb"]->getDeathYear();
				$people["husb"]->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $people["husb"]->getBirthYear(); ?>
				<tr class="fact_value">
					<td>
						<?php echo $menu->getMenu();?>
					</td>
					<td align="left" >
						<?php if ($people["husb"]->canDisplayDetails()) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people["husb"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people["husb"]->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				<tr>
			<?php }
			// Wife
			if (isset($people["wife"])) {
				$fulln		= strip_tags($people['wife']->getFullName());
				$menu		= new KT_Menu(getCloseRelationshipName($person, $people["wife"]));
				$slabel		= print_pedigree_person_nav2($people["wife"]->getXref(), 2, 0, $personcount++, $currpid, $censyear);
				$slabel		.= $parentlinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubMenu($submenu);
				$people["wife"]->getDeathYear() == 0 ? $DeathYr = "" : $DeathYr = $people["wife"]->getDeathYear();
				$people["wife"]->getBirthYear() == 0 ? $BirthYr = "" : $BirthYr = $people["wife"]->getBirthYear(); ?>
				<tr>
					<td>
						<?php echo $menu->getMenu();?>
					</td>
					<td align="left">
						<?php if ($people["wife"]->canDisplayDetails()) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $people["wife"]->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $people["wife"]->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				<tr>
			<?php }
			// Children
			foreach ($people["children"] as $key=>$child) {
				$fulln		= strip_tags($child->getFullName());
				$menu		= new KT_Menu(getCloseRelationshipName($person, $child));
				$slabel		= print_pedigree_person_nav2($child->getXref(), 2, 0, $personcount++, $child->getLabel(), $censyear);
				$slabel		.= $spouselinks;
				$submenu	= new KT_Menu($slabel);
				$menu->addSubmenu($submenu); ?>
				<tr>
					<td >
						<?php echo $menu->getMenu(); ?>
					</td>
					<td align="left">
						<?php if (($child->canDisplayDetails())) { ?>
							<a href="#" onclick="insertRowToTable('<?php echo $child->getXref(); ?>','<?php echo KT_Filter::escapeHtml($fulln); ?>');">
								<?php echo $child->getFullName(); ?>
							</a>
						<?php } else {
							echo KT_I18N::translate('Private');
						} ?>
					</td>
				</tr>
			<?php }
		} ?>
		<tr>
			<td colspan="2">
				<a class="error" href="#" onclick="fam_nav_close();"><?php echo KT_I18N::translate('Close'); ?></a>
			</td>
		<tr>
	</table>
</div> <!-- close "media-links" -->
<?php
/**
 * print the information for an individual chart box
 *
 * find and print a given individuals information for a pedigree chart
 * @param string $pid the Gedcom Xref ID of the   to print
 * @param int $style the style to print the box in, 1 for smaller boxes, 2 for larger boxes
 * @param int $count on some charts it is important to keep a count of how many boxes were printed
 */
function print_pedigree_person_nav2($pid, $style=1, $count=0, $personcount="1", $currpid, $censyear) {
	global $HIDE_LIVE_PEOPLE, $SHOW_LIVING_NAMES;
	global $SHOW_HIGHLIGHT_IMAGES, $bwidth, $bheight, $PEDIGREE_FULL_DETAILS, $SHOW_PEDIGREE_PLACES;
	global $TEXT_DIRECTION, $DEFAULT_PEDIGREE_GENERATIONS, $OLD_PGENS, $talloffset, $PEDIGREE_LAYOUT, $MEDIA_DIRECTORY;
	global $ABBREVIATE_CHART_LABELS;
	global $chart_style, $box_width, $generations, $show_spouse, $show_full;
	global $CHART_BOX_TAGS, $SHOW_LDS_AT_GLANCE, $PEDIGREE_SHOW_GENDER;
	global $SEARCH_SPIDER;

	global $spouselinks, $parentlinks, $step_parentlinks, $persons, $person_step, $person_parent, $tabno;
	global $natdad, $natmom, $censyear, $censdate;

	if ($style != 2) $style=1;
	if (empty($show_full)) $show_full = 0;
	if (empty($PEDIGREE_FULL_DETAILS)) $PEDIGREE_FULL_DETAILS = 0;

	if (!isset($OLD_PGENS)) $OLD_PGENS = $DEFAULT_PEDIGREE_GENERATIONS;
	if (!isset($talloffset)) $talloffset = $PEDIGREE_LAYOUT;

	$person=KT_Person::getInstance($pid);
	if ($pid==false || empty($person)) {
		$spouselinks  = false;
		$parentlinks  = false;
		$step_parentlinks = false;
	}

	$tmp=array('M'=>'','F'=>'F', 'U'=>'NN');
	$isF=$tmp[$person->getSex()];
	$spouselinks = "";
	$parentlinks = "";
	$step_parentlinks   = "";
	$disp=$person->canDisplayDetails();

	if ($person->canDisplayName() && !$SEARCH_SPIDER) {
		//-- draw a box for the family popup
		if ($TEXT_DIRECTION=="rtl") {
			$spouselinks .= "<table id=\"flyoutFamRTL\" class=\"person_box$isF\"><tr><td class=\"name2 font9 rtl\">";
			$spouselinks .= "<b>" . KT_I18N::translate('Family') . "</b> (" .$person->getFullName(). ")<br>";
			$parentlinks .= "<table id=\"flyoutParRTL\" class=\"person_box$isF\"><tr><td class=\"name2 font9 rtl\">";
			$parentlinks .= "<b>" . KT_I18N::translate('Parents') . "</b> (" .$person->getFullName(). ")<br>";
			$step_parentlinks .= "<table id=\"flyoutStepRTL\" class=\"person_box$isF\"><tr><td class=\"name2 font9 rtl\">";
			$step_parentlinks .= "<b>" . KT_I18N::translate('Parents') . "</b> (" .$person->getFullName(). ")<br>";
		} else {
			$spouselinks .= "<table id=\"flyoutFam\" class=\"person_box$isF\"><tr><td class=\"name2 font9 ltr\">";
			$spouselinks .= "<b>" . KT_I18N::translate('Family') . "</b> (" .$person->getFullName(). ")<br>";
			$parentlinks .= "<table id=\"flyoutPar\" class=\"person_box$isF\"><tr><td class=\"name2 font9 ltr\">";
			$parentlinks .= "<b>" . KT_I18N::translate('Parents') . "</b> (" .$person->getFullName(). ")<br>";
			$step_parentlinks .= "<table id=\"flyoutStep\" class=\"person_box$isF\"><tr><td class=\"name2 font9 ltr\">";
			$step_parentlinks .= "<b>" . KT_I18N::translate('Parents') . "</b> (" .$person->getFullName(). ")<br>";
		}
		$persons       = '';
		$person_parent = '';
		$person_step   = '';

		//-- parent families
		foreach ($person->getChildFamilies() as $family) {

			if (!is_null($family)) {
				$husb = $family->getHusband($person);
				$wife = $family->getWife($person);
				// $spouse = $family->getSpouse($person);
				$children = $family->getChildren();
				$num = count($children);
				$marrdate = $family->getMarriageDate();

				// Husband -----------
				if ($husb || $num > 0) {
					if ($husb) {
						$person_parent = "Yes";
						$tmp=$husb->getXref();
						if ($husb->canDisplayName()) {
							$fulln =strip_tags($husb->getFullName());
							$parentlinks .= "<a href=\"#\" onclick=\"opener.insertRowToTable(";
							$parentlinks .= "'".$husb->getXref()."', "; // pid = PID
							$parentlinks .= "'".htmlentities($fulln)."', "; // nam = Name
							if ($currpid=="Wife" || $currpid=="Husband") {
								$parentlinks .= "'Father in Law', "; // label = 1st Gen Male Relationship
							} else {
								$parentlinks .= "'Grand-Father', "; // label = 2st Gen Male Relationship
							}
							$parentlinks .= "'".$husb->getSex()."', "; // sex = Gender
							$parentlinks .= "''".", "; // cond = Condition (Married etc)
							$parentlinks .= "'".$husb->getbirthyear()."', "; // yob = Year of Birth
							if ($husb->getbirthyear()>=1) {
								$parentlinks .= "'".($censyear-$husb->getbirthyear())."', "; // age =  Census Year - Year of Birth
							} else {
								$parentlinks .= "'', "; // age =  Undefined
							}
							$parentlinks .= "'Y', "; // Y/M/D = Age in Years/Months/Days
							$parentlinks .= "'', "; // occu  = Occupation
							$parentlinks .= "'".$husb->getcensbirthplace()."'"; // birthpl = Birthplace
							$parentlinks .= ");\">";
							$parentlinks .= $husb->getFullName();
							$parentlinks .= "</a>";

						} else {
							$parentlinks .= KT_I18N::translate('Private');
						}
						$natdad = "yes";
					}
				}

				// Wife -----------
				if ($wife || $num>0) {
					if ($wife) {
						$person_parent="Yes";
						$tmp=$wife->getXref();
						if ($wife->canDisplayName()) {
							$fulln =strip_tags($wife->getFullName());
							$parentlinks .= "<a href=\"#\" onclick=\"opener.insertRowToTable(";
							$parentlinks .= "'".$wife->getXref()."',"; // pid = PID
							$parentlinks .= "'".htmlentities($fulln)."',"; // nam = Full Name
							if ($currpid=="Wife" || $currpid=="Husband") {
								$parentlinks .= "'Mother in Law',"; // label = 1st Gen Female Relationship
							} else {
								$parentlinks .= "'Grand-Mother',"; // label = 2st Gen Female Relationship
							}
							$parentlinks .= "'".$wife->getSex()."',"; // sex = Gender
							$parentlinks .= "''".","; // cond = Condition (Married etc)
							$parentlinks .= "'".$wife->getbirthyear()."',"; // yob = Year of Birth
							if ($wife->getbirthyear()>=1) {
								$parentlinks .= "'".($censyear-$wife->getbirthyear())."',"; // age =  Census Year - Year of Birth
							} else {
								$parentlinks .= "''".","; // age =  Undefined
							}
							$parentlinks .= "'Y'".","; // Y/M/D = Age in Years/Months/Days
							$parentlinks .= "''".","; // occu  = Occupation
							$parentlinks .= "'".$wife->getcensbirthplace()."'"; // birthpl = Birthplace
							$parentlinks .= ");\">";
							$parentlinks .= $wife->getFullName(); // Full Name
							$parentlinks .= "</a>";
						} else {
							$parentlinks .= KT_I18N::translate('Private');
						}
						$parentlinks .= "<br>";
						$natmom = "yes";
					}
				}
			}
		}

		//-- step families ---
		$fams = $person->getChildStepFamilies();
		foreach ($fams as $family) {
			if (!is_null($family)) {
				$husb = $family->getHusband($person);
				$wife = $family->getWife($person);
				// $spouse = $family->getSpouse($person);
				$children = $family->getChildren();
				$num = count($children);
				$marrdate = $family->getMarriageDate();

				if ($natdad == "yes") {
				} else {
					// Husband ----
					if (($husb || $num>0) && $husb->getLabel() != ".") {
						if ($husb) {
							$person_step="Yes";
							$tmp=$husb->getXref();
							if ($husb->canDisplayName()) {
								$fulln =strip_tags($husb->getFullName());
								$parentlinks .= "<a href=\"individual.php?pid={$tmp}&amp;tab={$tabno}&amp;gedcom=".KT_GEDURL."\">";
								$parentlinks .= $husb->getFullName();
								$parentlinks .= "</a>";
							} else {
								$parentlinks .= KT_I18N::translate('Private');
							}
							$parentlinks .= "<br>";
						}
					}
				}

				if ($natmom == "yes") {
				} else {
					// Wife ---------
					if ($wife || $num>0) {
						if ($wife) {
							$person_step="Yes";
							$tmp=$wife->getXref();
							if ($wife->canDisplayName()) {
								$fulln =addslashes($wife->getFullName());
								$parentlinks .= "<a href=\"individual.php?pid={$tmp}&amp;tab={$tabno}&amp;gedcom=".KT_GEDURL."\">";
								$parentlinks .= $wife->getFullName();
								$parentlinks .= "</a>";
							} else {
								$parentlinks .= KT_I18N::translate('Private');
							}
							$parentlinks .= "<br>";
						}
					}
				}
			}
		}

		// Spouse Families  @var $family Family
		foreach ($person->getSpouseFamilies() as $family) {
			if (!is_null($family)) {
				$spouse = $family->getSpouse($person);
				$children = $family->getChildren();
				$num = count($children);
				$marrdate = $family->getMarriageDate();

				// Spouse -----------
				if ($spouse || $num>0) {
					if ($spouse) {
						$tmp=$spouse->getXref();
						if ($spouse->canDisplayName()) {
							$fulln =strip_tags($spouse->getFullName());
							$spouselinks .= "<a href=\"#\" onclick=\"opener.insertRowToTable(";
							$spouselinks .= "'".$spouse->getXref()."',"; // pid = PID
							$spouselinks .= "'".strip_tags($spouse->getFullName())."',"; // Full Name
							if ($currpid=="Son" || $currpid=="Daughter") {
								if ($spouse->getSex()=="M") {
									$spouselinks .= "'Son in Law',"; // label = Male Relationship
								} else {
									$spouselinks .= "'Daughter in Law',"; // label = Female Relationship
								}
							} else {
								if ($spouse->getSex()=="M") {
									$spouselinks .= "'Brother in Law',"; // label = Male Relationship
								} else {
									$spouselinks .= "'Sister in Law',"; // label = Female Relationship
								}
							}
								$spouselinks .= "'".$spouse->getSex()."',"; // sex = Gender
								$spouselinks .= "''".","; // cond = Condition (Married etc)
								$spouselinks .= "'".$spouse->getbirthyear()."',"; // yob = Year of Birth
								if ($spouse->getbirthyear()>=1) {
									$spouselinks .= "'".($censyear-$spouse->getbirthyear())."',"; // age =  Census Year - Year of Birth
								} else {
									$spouselinks .= "''".","; // age =  Undefined
								}
								$spouselinks .= "'Y'".","; // Y/M/D = Age in Years/Months/Days
								$spouselinks .= "''".","; // occu  = Occupation
								$spouselinks .= "'".$spouse->getcensbirthplace()."'"; // birthpl = Birthplace
								$spouselinks .= ");\">";
								$spouselinks .= $spouse->getFullName(); // Full Name
								$spouselinks .= "</a>";
						} else {
							$spouselinks .= KT_I18N::translate('Private');
						}
						$spouselinks .= "</a>";
						if ($spouse->getFullName() != "") {
							$persons = "Yes";
						}
					}
				}

				// Children -----------   @var $child Person
				$spouselinks .= "<div id='spouseFam'>";
				$spouselinks .= "<ul class=\"clist\">";
				foreach ($children as $c=>$child) {
					if ($child) {
						$persons="Yes";
						if ($child->canDisplayName()) {
							$fulln =strip_tags($child->getFullName());
							$spouselinks .= "<li>";
							$spouselinks .= "<a href=\"#\" onclick=\"opener.insertRowToTable(";
							$spouselinks .= "'".$child->getXref()."',"; // pid = PID
							$spouselinks .= "'".htmlentities($fulln)."',"; // nam = Name
							if ($currpid=="Son" || $currpid=="Daughter") {
								if ($child->getSex()=="M") {
									$spouselinks .= "'Grand-Son',"; // label = Male Relationship
								} else {
									$spouselinks .= "'Grand-Daughter',"; // label = Female Relationship
								}
							} else {
								if ($child->getSex()=="M") {
									$spouselinks .= "'Nephew',"; // label = Male Relationship
								} else {
									$spouselinks .= "'Niece',"; // label  = Female Relationship
								}
							}
							$spouselinks .= "'".$child->getSex()."',"; // sex = Gender
							$spouselinks .= "''".","; // cond = Condition (Married etc)
							$spouselinks .= "'".$child->getbirthyear()."',"; // yob = Year of Birth
							if ($child->getbirthyear()>=1) {
								$spouselinks .= "'".($censyear-$child->getbirthyear())."',"; // age =  Census Year - Year of Birth
							} else {
								$spouselinks .= "''".","; // age =  Undefined
							}
							$spouselinks .= "'Y'".","; // Y/M/D = Age in Years/Months/Days
							$spouselinks .= "''".","; // occu  = Occupation
							$spouselinks .= "'".$child->getcensbirthplace()."'"; // birthpl = Birthplace
							$spouselinks .= ");\">";
							$spouselinks .= $child->getFullName(); // Full Name
							$spouselinks .= "</a>";
							} else {
								$spouselinks .= KT_I18N::translate('Private');
							}
							$spouselinks .= "</li>";
					}
				}
				$spouselinks .= "</ul>";
				$spouselinks .= "</div>";
			}
		}

		if ($persons != "Yes") {
			$spouselinks  .= "(" . KT_I18N::translate('none') . ")</td></tr></table>";
		} else {
			$spouselinks  .= "</td></tr></table>";
		}

		if ($person_parent != "Yes") {
			$parentlinks .= "(" . KT_I18N::translate_c('unknown family', 'unknown') . ")</td></tr></table>";
		} else {
			$parentlinks .= "</td></tr></table>";
		}

		if ($person_step != "Yes") {
			$step_parentlinks .= "(" . KT_I18N::translate_c('unknown family', 'unknown') . ")</td></tr></table>";
		} else {
			$step_parentlinks .= "</td></tr></table>";
		}
	}
}
