<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

class relatives_KT_Module extends KT_Module implements KT_Module_Tab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Families');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Families” module */ KT_I18N::translate('A tab showing the close relatives of an individual.');
	}

	// Extend class KT_Module_Tab
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 20;
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	function printFamilyHeader(KT_Family $family, $type, $label, $people) { ?>
		<table class="fam_relationship">
			<tr>
				<td>
					<i class="icon-cfamily"></i>
				</td>
				<td>
					<span class="subheaders"><?php echo $label; ?></span>
					<a href="<?php echo $family->getHtmlUrl(); ?>"> - <?php echo KT_USER_CAN_EDIT ? KT_I18N::translate('Edit family') : KT_I18N::translate('View family'); ?></a>
				</td>
				<td>
					<?php if (
						array_key_exists('chart_relationship', KT_Module::getActiveModules()) &&
							KT_USER_ID && (
							($type == 'FAMC' && get_gedcom_setting(KT_GED_ID, 'TAB_REL_OF_PARENTS') > 0) ||
							($type == 'FAMS' && get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_SPOUSE') > 0)
						)
					) { ?>
						<div class="fam_rela"><?php echo printFamilyRelationship($type, $people); ?></div>
					<?php } ?>
				</td>
			</tr>
		</table>
	<?php }

	/**
	* print parents informations
	* @param Family family
	* @param Array people
	* @param String family type
	* @return html table rows
	*/
	function printParentsRows($family, $people, $type) {
		global $personcount, $SHOW_PEDIGREE_PLACES, $controller, $SEARCH_SPIDER;

		$elderdate = "";
		//-- new father/husband
		$styleadd = "";
		if (isset($people["newhusb"])) {
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_labelblue"><?php echo $people["newhusb"]->getLabel(); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["newhusb"]); ?>">
					<?php print_pedigree_person($people["newhusb"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
			$elderdate = $people["newhusb"]->getBirthDate();
		}
		//-- father/husband
		if (isset($people["husb"])) {
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>">
					<?php echo $people["husb"]->getLabel();
					if ($controller->record->equals($people["husb"])) {
						echo '<i class="icon-selected"></i>' . reflexivePronoun($controller->record);
					} else {
						echo get_relationship_name(get_relationship($controller->record, $people["husb"], true, 3));
					} ?>
				</td>
				<td class="<?php echo $controller->getPersonStyle($people["husb"]); ?>">
					<?php print_pedigree_person($people["husb"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
			$elderdate = $people["husb"]->getBirthDate();
		}
		//-- missing father
		if ($type=="parents" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo KT_I18N::translate('Add a father'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'HUSB', '<?php echo $family->getXref(); ?>');"><?php echo KT_I18N::translate('Add a father'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- missing husband
		if ($type=="spouse" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo KT_I18N::translate('Add husband'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewspouse('<?php echo $controller->record->getXref(); ?>', '<?php echo $family->getXref(); ?>', 'HUSB');"><?php echo KT_I18N::translate('Add a husband to this family'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- new mother/wife
		$styleadd = "";
		if (isset($people["newwife"])) {
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_labelblue"><?php echo $people["newwife"]->getLabel($elderdate); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["newwife"]); ?>">
					<?php print_pedigree_person($people["newwife"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
		}
		//-- mother/wife
		if (isset($people["wife"])) {
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>">
					<?php echo $people["wife"]->getLabel($elderdate);
					if ($controller->record->equals($people["wife"])) {
						echo '<i class="icon-selected"></i>' . reflexivePronoun($controller->record);
					} else {
						echo get_relationship_name(get_relationship($controller->record, $people["wife"], true, 3));
					} ?>
				</td>
				<td class="<?php echo $controller->getPersonStyle($people["wife"]); ?>">
					<?php print_pedigree_person($people["wife"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
		}
		//-- missing mother
		if ($type=="parents" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo KT_I18N::translate('Add a mother'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'WIFE', '<?php echo $family->getXref(); ?>');"><?php echo KT_I18N::translate('Add a mother'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- missing wife
		if ($type=="spouse" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo KT_I18N::translate('Add wife'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewspouse('<?php echo $controller->record->getXref(); ?>','<?php echo $family->getXref(); ?>', 'WIFE');"><?php echo KT_I18N::translate('Add a wife to this family'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- marriage row
		if ($family->getMarriageRecord()!="" || KT_USER_CAN_EDIT) {
			?>
			<tr>
				<td class="facts_label">
					&nbsp;
				</td>
				<td class="facts_value">
					<?php $marr_type = strtoupper($family->getMarriageType());
					if ($marr_type=='CIVIL' || $marr_type=='PARTNERS' || $marr_type=='RELIGIOUS' || $marr_type=='COML' || $marr_type=='UNKNOWN') {
						$marr_fact = 'MARR_' . $marr_type;
					} else {
						$marr_fact = 'MARR';
					}
					$famid = $family->getXref();
					$place = $family->getMarriagePlace();
					$date = $family->getMarriageDate();
					if ($date && $date->isOK() || $place) {
						if ($date) {
							$details=$date->Display(false);
						}
						if ($place) {
							if ($details) {
								$details .= ' — ';
							}
							$tmp=new KT_Place($place, KT_GED_ID);
							$details .= $tmp->getShortName();
						}
						echo KT_Gedcom_Tag::getLabelValue($marr_fact, $details);
					} else if (get_sub_record(1, "1 _NMR", find_family_record($famid, KT_GED_ID))) {
						$husb = $family->getHusband();
						$wife = $family->getWife();
						if (empty($wife) && !empty($husb)) {
							echo KT_Gedcom_Tag::getLabel('_NMR', $husb);
						} elseif (empty($husb) && !empty($wife)) {
							echo KT_Gedcom_Tag::getLabel('_NMR', $wife);
						} else {
							echo KT_Gedcom_Tag::getLabel('_NMR');
						}
					} else if ($family->getMarriageRecord()=="" && $controller->record->canEdit()) {
						echo "<a href=\"#\" onclick=\"return add_new_record('".$famid."', 'MARR');\">".KT_I18N::translate('Add marriage details')."</a>";
					} else {
						echo KT_Gedcom_Tag::getLabelValue($marr_fact, KT_I18N::translate('yes'));
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	* print children informations
	* @param Family family
	* @param Array people
	* @param String family type
	* @return html table rows
	*/
	function printChildrenRows($family, $people, $type) {
		global $personcount, $controller;

		$elderdate = $family->getMarriageDate();
		$key=0;
		foreach ($people["children"] as $child) {
			$label = $child->getLabel();
			$styleadd = "";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>">
					<?php if ($styleadd=="red") {
						echo $child->getLabel();
					} else {
						echo $child->getLabel($elderdate, $key+1);
					}
					if ($controller->record->equals($child)) {
						echo '<i class="icon-selected"></i>' . reflexivePronoun($controller->record);
					} else {
						echo get_relationship_name(get_relationship($controller->record, $child, true, 3));
					} ?>
				</td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		foreach ($people["newchildren"] as $child) {
			$label = $child->getLabel();
			$styleadd = "blue";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php if ($styleadd=="red") echo $child->getLabel(); else echo $child->getLabel($elderdate, $key+1); ?></td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		foreach ($people["delchildren"] as $child) {
			$label = $child->getLabel();
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php if ($styleadd=="red") echo $child->getLabel(); else echo $child->getLabel($elderdate, $key+1); ?></td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		if (isset($family) && $controller->record->canEdit()) {
			if ($type == "spouse") {
				$child_u = KT_I18N::translate('Add a son or daughter');
				$child_m = KT_I18N::translate('son');
				$child_f = KT_I18N::translate('daughter');
			} else {
				$child_u = KT_I18N::translate('Add a brother or sister');
				$child_m = KT_I18N::translate('brother');
				$child_f = KT_I18N::translate('sister');
			}
		?>
			<tr>
				<td class="facts_label">
					<?php if (KT_USER_CAN_EDIT && isset($people["children"][1])) { ?>
					<a href="#" onclick="reorder_children('<?php echo $family->getXref(); ?>');"><i class="icon-media-shuffle"></i> <?php echo KT_I18N::translate('Re-order children'); ?></a>
					<?php } ?>
				</td>
				<td class="facts_value">
					<a href="#" onclick="return addnewchild('<?php echo $family->getXref(); ?>');"><?php echo $child_u; ?></a>
					<span style='white-space:nowrap;'>
						<a href="#" class="icon-sex_m_15x15" onclick="return addnewchild('<?php echo $family->getXref(); ?>','M');"></a>
						<a href="#" class="icon-sex_f_15x15" onclick="return addnewchild('<?php echo $family->getXref(); ?>','F');"></a>
					</span>
				</td>
			</tr>
			<?php
		}
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $GEDCOM, $ABBREVIATE_CHART_LABELS, $show_full, $personcount, $controller;

		if (isset($show_full)) $saved_show_full = $show_full; // We always want to see full details here
		$show_full = 1;

		$saved_ABBREVIATE_CHART_LABELS = $ABBREVIATE_CHART_LABELS;
		$ABBREVIATE_CHART_LABELS = false; // Override GEDCOM configuration

		ob_start();
		?>
		<table class="facts_table">
			<tr>
				<td class="descriptionbox rela">
					<input id="checkbox_elder" type="checkbox" checked>
					<label for="checkbox_elder"><?php echo KT_I18N::translate('Show date differences'); ?></label>
					<input id="checkbox_rela" type="checkbox" checked>
					<label for="checkbox_rela"><?php echo KT_I18N::translate('Show relationships'); ?></label>
				</td>
			</tr>
		</table>
		<?php
		$personcount=0;
		$families = $controller->record->getChildFamilies();
		if (count($families)==0) {
			if ($controller->record->canEdit()) {
				?>
				<table class="facts_table">
					<tr>
						<td class="facts_value"><a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'HUSB');"><?php echo KT_I18N::translate('Add a father'); ?></td>
					</tr>
					<tr>
						<td class="facts_value"><a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'WIFE');"><?php echo KT_I18N::translate('Add a mother'); ?></a></td>
					</tr>
				</table>
				<?php
			}
		}

		// parents
		foreach ($families as $family) {
			$people = $controller->buildFamilyList($family, "parents");
			$this->printFamilyHeader($family, 'FAMC', $controller->record->getChildFamilyLabel($family), $people);
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "parents");
			$this->printChildrenRows($family, $people, "parents");
			echo '</table>';
		}

		// step-parents
		foreach ($controller->record->getChildStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, "step-parents");
			$this->printFamilyHeader($family, 'FAMC', $controller->record->getStepFamilyLabel($family), $people);
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "parents");
			$this->printChildrenRows($family, $people, "parents");
			echo '</table>';
		}

		// spouses
		$families = $controller->record->getSpouseFamilies();
		foreach ($families as $family) {
			$people = $controller->buildFamilyList($family, "spouse");
			$this->printFamilyHeader($family, 'FAMS', $controller->record->getSpouseFamilyLabel($family), $people);
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "spouse");
			$this->printChildrenRows($family, $people, "spouse");
			echo '</table>';
		}

		// step-children
		foreach ($controller->record->getSpouseStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, "step-children");
			$this->printFamilyHeader($family, 'FAMS', $family->getFullName(), $people);
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "spouse");
			$this->printChildrenRows($family, $people, "spouse");
			echo '</table>';
		}
		?>
		<script>
			persistent_toggle("checkbox_elder", ".elderdate");
			persistent_toggle("checkbox_rela", ".fam_rela");
		</script>
		<?php

		$ABBREVIATE_CHART_LABELS = $saved_ABBREVIATE_CHART_LABELS; // Restore GEDCOM configuration
		unset($show_full);
		if (isset($saved_show_full)) $show_full = $saved_show_full;

		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}
}
