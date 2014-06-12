<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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

class family_nav_WT_Module extends WT_Module implements WT_Module_Sidebar {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ WT_I18N::translate('Family navigator');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Family navigator” module */ WT_I18N::translate('A sidebar showing an individual’s close families and relatives.');
	}

	// Implement WT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 20;
	}

	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarContent() {
		global $controller, $spouselinks, $parentlinks;

		ob_start();

		echo '
			<div id="sb_family_nav_content">
				<table class="nav_content">';

		//-- parent families -------------------------------------------------------------
		foreach ($controller->record->getChildFamilies() as $family) {
			$people = $controller->buildFamilyList($family, 'parents');
			echo '
				<tr>
					<td class="center" colspan="2">
						<a class="famnav_link" href="' . $family->getHtmlUrl() . '">'.
							$controller->record->getChildFamilyLabel($family) . '
						</a>
					</td>
				</tr>';
			if (isset($people['husb'])) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['husb'], true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['husb']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['husb']), ' nam">
							<a class="famnav_link" href="' . $people["husb"]->getHtmlUrl() . '">'.
								$people['husb']->getFullName().'
							</a>
							<div class="font9">' . $people['husb']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			if (isset($people['wife'])) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['wife'], true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['wife']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['wife']), ' nam">
							<a class="famnav_link" href="' . $people['wife']->getHtmlUrl() . '">'. 
								$people['wife']->getFullName(). '
							</a>
							<div class="font9">' . $people['wife']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			foreach ($people['children'] as $child) {
				if ($controller->record->equals($child)) {
					$menu = new WT_Menu('<i class="icon-selected"></i>');
				} else {
					$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $child, true, 3)));
				}
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($child) . $spouselinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">'.
							$menu->getMenu(). '
						</td>
						<td class="center ',
							$controller->getPersonStyle($child), ' nam">
							<a class="famnav_link" href="' . $child->getHtmlUrl() . '">',
								$child->getFullName(), '
							</a>
							<div class="font9">' . $child->getLifeSpan() . '</div>
						</td>
					</tr>';
			}
		}

		//-- step parents ----------------------------------------------------------------
		foreach ($controller->record->getChildStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, 'step-parents');
			echo '
				<tr>
					<td class="center" colspan="2">
						<a class="famnav_link" href="' . $family->getHtmlUrl() . '">' .
							$controller->record->getStepFamilyLabel($family) . '
						</a>
					</td>
				</tr>';

			if (isset($people['husb']) ) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['husb'])));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['husb']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people["husb"]), ' nam">
							<a class="famnav_link" href="' . $people['husb']->getHtmlUrl() . '">' .
								$people['husb']->getFullName(). '
							</a>
							<div class="font9">' . $people['husb']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			if (isset($people['wife']) ) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['wife'], true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['wife']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['wife']), ' nam">
							<a class="famnav_link" href="' . $people['wife']->getHtmlUrl() . '">' .
								$people['wife']->getFullName(). '
							</a>
							<div class="font9">' . $people['wife']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}
			foreach ($people['children'] as $child) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $child, true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($child) . $spouselinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($child), ' nam">
							<a class="famnav_link" href="' . $child->getHtmlUrl() . '">' .
								$child->getFullName(). '
							</a>
							<div class="font9">' . $child->getLifeSpan() . '</div>
						</td>
					</tr>';
			}
		}

		//-- spouse and children --------------------------------------------------
		foreach ($controller->record->getSpouseFamilies() as $family) {
			echo '
				<tr>
					<td class="center" colspan="2">
						<a class="famnav_link" href="' . $family->getHtmlUrl() . '">' .
							WT_I18N::translate('Immediate Family') . '
						</a>
					</td>
				</tr>';
			$people = $controller->buildFamilyList($family, 'spouse');
			if (isset($people['husb'])) {
				if ($controller->record->equals($people['husb'])) {
					$menu = new WT_Menu('<i class="icon-selected"></i>');
				} else {
					$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['husb'], true, 3)));
				}
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['husb']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['husb']), ' nam">
							<a class="famnav_link" href="' . $people['husb']->getHtmlUrl() . '">'. 
								$people['husb']->getFullName(). '
							</a>
							<div class="font9">' . $people['husb']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			if (isset($people['wife'])) {
				if ($controller->record->equals($people['wife'])) {
					$menu = new WT_Menu('<i class="icon-selected"></i>');
				} else {
					$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['wife'], true, 3)));
				}
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['wife']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['wife']), ' nam">
							<a class="famnav_link" href="' . $people['wife']->getHtmlUrl() . '">' .
								$people['wife']->getFullName(). '
							</a>
							<div class="font9">' . $people['wife']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			foreach ($people['children'] as $child) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $child, true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($child) . $spouselinks);
				$menu->addSubmenu($submenu);
				echo '<tr><td class="facts_label" style="width:75px;">', $menu->getMenu(), '</td><td class="center ', $controller->getPersonStyle($child), ' nam">';
				echo '<a class="famnav_link" href="' . $child->getHtmlUrl() . '">';
				echo $child->getFullName();
				echo '</a>';
				echo '<div class="font9">' . $child->getLifeSpan() . '</div>';
				echo '</td></tr>';
			}
		}
		//-- step children ----------------------------------------------------------------
		foreach ($controller->record->getSpouseStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, 'step-children');
			echo '
				<tr>
					<td class="center" colspan="2">
						<a class="famnav_link" href="' . $family->getHtmlUrl() . '">' .
							$family->getFullName() . '
						</a>
					</td>
				</tr>';

			if (isset($people['husb']) ) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['husb'])));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['husb']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['husb']), ' nam">
							<a class="famnav_link" href="' . $people['husb']->getHtmlUrl() . '">'.
								$people['husb']->getFullName(). '
							</a>
							<div class="font9">' . $people['husb']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}

			if (isset($people['wife']) ) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $people['wife'], true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($people['wife']) . $parentlinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($people['wife']), ' nam">
							<a class="famnav_link" href="' . $people['wife']->getHtmlUrl() . '">'. 
								$people['wife']->getFullName(). '
							</a>
							<div class="font9">' . $people['wife']->getLifeSpan() . '</div>
						</td>
					</tr>';
			}
			foreach ($people['children'] as $child) {
				$menu = new WT_Menu(get_relationship_name(get_relationship($controller->record, $child, true, 3)));
				$menu->addClass('', 'submenu flyout2');
				$submenu = new WT_Menu($this->print_pedigree_person_nav($child) . $spouselinks);
				$menu->addSubMenu($submenu);
				echo '
					<tr>
						<td class="facts_label">',
							$menu->getMenu(), '
						</td>
						<td class="center ',
							$controller->getPersonStyle($child), ' nam">
							<a class="famnav_link" href="' . $child->getHtmlUrl() . '">' .
								$child->getFullName(). '
							</a>
							<div class="font9">' . $child->getLifeSpan() . '</div>
						</td>
					</tr>';
			}
		}

		echo '
			</table>
		</div>';

		return ob_get_clean();
	}

	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	function print_pedigree_person_nav($person) {
		global $SEARCH_SPIDER, $spouselinks, $parentlinks, $step_parentlinks;

		$persons 		  = false;
		$person_step 	  = false;
		$person_parent 	  = false;
		$natdad 		  = false;
		$natmom 		  = false;
		$spouselinks      = '';
		$parentlinks      = '';
		$step_parentlinks = '';

		if ($person->canDisplayName() && !$SEARCH_SPIDER) {
			//-- draw a box for the family flyout
			$parentlinks      .= '<div class="flyout4">' . WT_I18N::translate('Parents') . '</div>';
			$step_parentlinks .= '<div class="flyout4">' . WT_I18N::translate('Parents') . '</div>';
			$spouselinks      .= '<div class="flyout4">' . WT_I18N::translate('Family' ) . '</div>';

			//-- parent families --------------------------------------
			$fams = $person->getChildFamilies();
			foreach ($fams as $family) {

				if (!is_null($family)) {
					$husb = $family->getHusband($person);
					$wife = $family->getWife($person);
					$children = $family->getChildren();

					// Husband ------------------------------
					if ($husb || $children) {
						if ($husb) {
							$person_parent = true;
							$parentlinks .= '
								<a class="flyout3" href="' . $husb->getHtmlUrl() . '">'.
									$husb->getFullName(). '
								</a>';
							$natdad = true;
						}
					}

					// Wife ------------------------------
					if ($wife || $children) {
						if ($wife) {
							$person_parent = true;
							$parentlinks .= '
								<a class="flyout3" href="' . $wife->getHtmlUrl() . '">'.
									$wife->getFullName().
								'</a>';
							$natmom = true;
						}
					}
				}
			}

			//-- step families -----------------------------------------
			$fams = $person->getChildStepFamilies();
			foreach ($fams as $family) {
				if (!is_null($family)) {
					$husb = $family->getHusband($person);
					$wife = $family->getWife($person);
					$children = $family->getChildren();

					if (!$natdad) {
						// Husband -----------------------
						if ($husb || $children) {
							if ($husb) {
								$person_step = true;
								$parentlinks .= '
									<a class="flyout3" href="' . $husb->getHtmlUrl() . '">'.
										$husb->getFullName().
									'</a>';
							}
						}
					}

					if (!$natmom) {
						// Wife ----------------------------
						if ($wife || $children) {
							if ($wife) {
								$person_step = true;
								$parentlinks .= '
									<a class="flyout3" href="' . $wife->getHtmlUrl() . '">'.
										$wife->getFullName().'
									</a>';
							}
						}
					}
				}
			}

			// Spouse Families -------------------------------------- @var $family Family
			foreach ($person->getSpouseFamilies() as $family) {
				$spouse = $family->getSpouse($person);
				$children = $family->getChildren();

				// Spouse ------------------------------
				if ($spouse || $children) {
					if ($spouse) {
						$spouselinks .= '
							<a class="flyout3" href="' . $spouse->getHtmlUrl() . '">'.
								$spouse->getFullName().'
							</a>';
						$persons = true;
					}
				}

				// Children ------------------------------   @var $child Person
				foreach ($children as $child) {
					$persons = true;
					$spouselinks .= '
						<ul class="clist">
							<li class="flyout3">
								<a href="' . $child->getHtmlUrl() . '">'.
									$child->getFullName(). '
								</a>
							</li>
						</ul>';
				}
			}
			if (!$persons) {
				$spouselinks .= '(' . WT_I18N::translate('none') . ')';
			}
			if (!$person_parent) {
				$parentlinks .= '(' . WT_I18N::translate_c('unknown family', 'unknown') . ')';
			}
			if (!$person_step) {
				$step_parentlinks .= '(' . WT_I18N::translate_c('unknown family', 'unknown') . ')';
			}
		}
	}
}
