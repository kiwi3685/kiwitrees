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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

function printSlcasWrtDefaultIndividual($mode, $recursion, $showCa, $type) {
	global $controller;

	$indi_xref			= $controller->getSignificantIndividual()->getXref();
	$PEDIGREE_ROOT_ID 	= get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');

	if ($indi_xref) {
		// Pages focused on a specific person - from the person, to me
		$pid1 = KT_USER_GEDCOM_ID ? KT_USER_GEDCOM_ID : KT_USER_ROOT_ID;
		$person1 = KT_Person::getInstance($pid1);
		if (!$pid1 && $PEDIGREE_ROOT_ID) {
			$person1 = KT_Person::getInstance($PEDIGREE_ROOT_ID);
		};
		$pid2 = $indi_xref;
		$person2 = KT_Person::getInstance($pid2);
		if ($pid1 == $pid2) {
			$person2 = $PEDIGREE_ROOT_ID ? KT_Person::getInstance($PEDIGREE_ROOT_ID) : null;
		}
	} else {
		// Regular pages - from me, to somebody
		$person1 = KT_USER_GEDCOM_ID ? KT_Person::getInstance(KT_USER_GEDCOM_ID) : KT_Person::getInstance(KT_USER_ROOT_ID);
		$person2 = $PEDIGREE_ROOT_ID ? KT_Person::getInstance($PEDIGREE_ROOT_ID) : null;
	}

	if ($person1 === null) {
		return;
	}

	if ($person2 === null) {
		return;
	}
	printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type);
}

function printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type) {
	global $KT_TREE, $GEDCOM_ID_PREFIX;
	$slcaController = new KT_Controller_Relationship;
	$caAndPaths = $slcaController->calculateCaAndPaths_123456($person1, $person2, $mode, $recursion);

	foreach ($caAndPaths as $caAndPath) {
		$slcaKey = $caAndPath->getCommonAncestor();
		$path = $caAndPath->getPath();

		// Extract the relationship names between pairs of individuals
		$relationships = $slcaController->oldStyleRelationshipPath($path);

		if (empty($relationships)) {
			// Cannot see one of the families/individuals, due to privacy;
			continue;
		}
		echo '<a href="relationship.php?pid1=' . $person1->getXref() . '&pid2=' . $person2->getXref() . '&ged=' . KT_GEDURL . '&find=4" target="_blank" rel="noopener noreferrer">' . KT_I18N::translate('Relationship:&nbsp;') . '</a>';
		echo KT_I18N::translate('%1$s is %2$s of %3$s',
			$person2->getFullName(),
			get_relationship_name_from_path(implode('', $relationships), $person1, $person2),
			$type == 'INDI' ? $person1->getLifespanName() : $person1->getFullName()
		);
		echo '<br/>';

		if (($slcaKey !== null) && ($showCa)) {
			//add actual common ancestor(s), unless already mentioned)
			if (substr($slcaKey, 0, 1) === $GEDCOM_ID_PREFIX) {
				$indi = KT_Person::getInstance($slcaKey, $KT_TREE);

				if (($person1 !== $indi) && ($person2 !== $indi)) {
					$html = '';
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';
					echo '(' . KT_I18N::translate('Common ancestor: ') . $html . ')';
					echo '<br>';
				}
			} else {
				$fam = KT_Family::getInstance($slcaKey, $KT_TREE);

				$names = array();
				foreach ($fam->getSpouses() as $indi) {
					$html = '';
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';

					$names[] = $indi->getFullName();
				}
				$famName = implode(' & ', $names);

				$html = '';
				$html .= '<a href="' . $fam->getHtmlUrl() . '" title="' . strip_tags($famName) . '">';
				$html .= highlight_search_hits($famName) . '</a>';
				echo '(' . KT_I18N::translate('Common ancestor: ') . $html . ')';
				echo '<br>';
			}
		}
	}
}

function printIndiRelationship() {
	$mode = intval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI'));
	$recursion = intval(get_gedcom_setting(KT_GED_ID, 'RELATIONSHIP_RECURSION'));
	$showCa = boolval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA'));

	if ($mode === 0) {
		return;
	}
	printSlcasWrtDefaultIndividual($mode, $recursion, $showCa, 'INDI');
}

function printFamilyRelationship($type, $people) {
	$person1 = isset($people["husb"]) ? KT_Person::getInstance($people["husb"]->getXref()) : null;
	$person2 = isset($people["wife"]) ? KT_Person::getInstance($people["wife"]->getXref()) : null;
	if ($type === 'FAMC') {
		$mode = intval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_OF_PARENTS'));
		$recursion = intval(get_gedcom_setting(KT_GED_ID, 'RELATIONSHIP_RECURSION'));
		$showCa = boolval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_OF_PARENTS_SHOW_CA'));
	} else {
		$mode = intval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_SPOUSE'));
		$recursion = intval(get_gedcom_setting(KT_GED_ID, 'RELATIONSHIP_RECURSION'));
		$showCa = boolval(get_gedcom_setting(KT_GED_ID, 'TAB_REL_TO_SPOUSE_SHOW_CA'));
	}

	if ($mode === 0 || !$person1 || !$person2 ) {
		return;
	}

	printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type);
}
