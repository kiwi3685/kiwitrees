<?php
// Function for printing relationships
//
// Various printing functions used to print fact records
//
// Kiwitrees: Web based WT_Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
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

function printSlcasWrtDefaultIndividual($mode, $recursion, $showCa, $type) {
	global $controller;

//	$extraController	= new WT_Controller_Page;
	$indi_xref			= $controller->getSignificantIndividual()->getXref();
	$PEDIGREE_ROOT_ID 	= get_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID');

	if ($indi_xref) {
		// Pages focused on a specific person - from the person, to me
		$pid1 = WT_USER_GEDCOM_ID ? WT_USER_GEDCOM_ID : WT_USER_ROOT_ID;
		$person1 = WT_Person::getInstance($pid1);
		if (!$pid1 && $PEDIGREE_ROOT_ID) {
			$person1 = WT_Person::getInstance($PEDIGREE_ROOT_ID);
		};
		$pid2 = $indi_xref;
		$person2 = WT_Person::getInstance($pid2);
		if ($pid1 == $pid2) {
			$person2 = $PEDIGREE_ROOT_ID ? WT_Person::getInstance($PEDIGREE_ROOT_ID) : null;
		}
	} else {
		// Regular pages - from me, to somebody
		$person1 = WT_USER_GEDCOM_ID ? WT_Person::getInstance(WT_USER_GEDCOM_ID) : WT_Person::getInstance(WT_USER_ROOT_ID);
		$person2 = $PEDIGREE_ROOT_ID ? WT_Person::getInstance($PEDIGREE_ROOT_ID) : null;
	}

	if ($person1 === null) {
		return;
	}

	if ($person2 === null) {
		return;
	}
	printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type);
}

function printSlcas(WT_Family $family, $mode, $recursion, $showCa) {
	$person1 = null;
	foreach ($family->getFacts('HUSB') as $fact) {
		$person = $fact->getTarget();
		if ($person instanceof WT_Person) {
			$person1 = $person;
		}
	}

	$person2 = null;
	foreach ($family->getFacts('WIFE') as $fact) {
		$person = $fact->getTarget();
		if ($person instanceof WT_Person) {
			$person2 = $person;
		}
	}

	if ($person1 === null) {
		return;
	}

	if ($person2 === null) {
		return;
	}

	//not that great, may still go via descendants of relatives
	//we'd have to restrict to short paths (may still not be 100% what we want)
	//or exclude edges with date after some given date (problem: imprecise/unknown dates)
	//this could even help wrt performance
	//printShortestPathBetween($person1, $person2);

	printSlcasBetween($person1, $person2, $mode, $recursion, $showCa);
}

function printShortestPathBetween($person1, $person2) {
	global $WT_TREE;
	$slcaController = new WT_Controller_Relationship;

	$paths = $slcaController->calculateRelationships_withWeights($person1, $person2, false, true);
	foreach ($paths as $path) {
		// Extract the relationship names between pairs of individuals
		$relationships = $slcaController->oldStyleRelationshipPath($path);
		if (empty($relationships)) {
			// Cannot see one of the families/individuals, due to privacy;
			continue;
		}
		echo WT_I18N::translate('%1$s is %2$s of %3$s.',
			$person2->getFullName(),
			get_relationship_name_from_path(implode('', $relationships), $person1, $person2),
			$person1->getFullName());
		echo "<br/>";
	}
}

function printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type) {
	global $WT_TREE, $GEDCOM_ID_PREFIX;
	$slcaController = new WT_Controller_Relationship;
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
		echo '<a href="relationship.php?pid1=' . $person1->getXref() . '&pid2=' . $person2->getXref() . '&ged=' . WT_GEDURL . '&find=4" target="_blank">' . WT_I18N::translate('Relationship:&nbsp;') . '</a>';
		echo WT_I18N::translate('<span>%1$s is %2$s of %3$s',
			$person2->getFullName(),
			get_relationship_name_from_path(implode('', $relationships), $person1, $person2),
			$type == 'INDI' ? $person1->getLifespanName() : $person1->getFullName()
		);
		echo '<br/>';

		if (($slcaKey !== null) && ($showCa)) {
			//add actual common ancestor(s), unless already mentioned)
			if (substr($slcaKey, 0, 1) === $GEDCOM_ID_PREFIX) {
				$indi = WT_Person::getInstance($slcaKey, $WT_TREE);

				if (($person1 !== $indi) && ($person2 !== $indi)) {
					$html = "";
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';
					echo "(" . WT_I18N::translate('Common ancestor: ') . $html.")";
					echo "<br />";
				}
			} else {
				$fam = WT_Family::getInstance($slcaKey, $WT_TREE);

				$names = array();
				foreach ($fam->getSpouses() as $indi) {
					$html = "";
					$html .= '<a href="' . $indi->getHtmlUrl() . '" title="' . strip_tags($indi->getFullName()) . '">';
					$html .= highlight_search_hits($indi->getFullName()) . '</a>';

					$names[] = $indi->getFullName();
				}
				$famName = implode(' & ', $names);

				$html = "";
				$html .= '<a href="' . $fam->getHtmlUrl() . '" title="' . strip_tags($famName) . '">';
				$html .= highlight_search_hits($famName) . '</a>';
				echo "(" . WT_I18N::translate('Common ancestors: ').$html.")";
				echo "<br />";
			}
		}
	}
}

function printIndiRelationship() {
	$mode = intval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI'));
	$recursion = intval(get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION'));
	$showCa = boolval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA'));

	if ($mode === 0) {
		return;
	}
	printSlcasWrtDefaultIndividual($mode, $recursion, $showCa, 'INDI');
}

function printFamilyRelationship($type, $people) {
	$person1 = WT_Person::getInstance($people["husb"]->getXref());
	$person2 = WT_Person::getInstance($people["wife"]->getXref());
	if ($type === 'FAMC') {
		$mode = intval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS'));
		$recursion = intval(get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION'));
		$showCa = boolval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS_SHOW_CA'));
	} else {
		$mode = intval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE'));
		$recursion = intval(get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION'));
		$showCa = boolval(get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE_SHOW_CA'));
	}

	if ($mode === 0) {
		return;
	}
//	printSlcasBetween($family, $mode, $recursion, $showCa, $person1, $person2);
	printSlcasBetween($person1, $person2, $mode, $recursion, $showCa, $type);
}
