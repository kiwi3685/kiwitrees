<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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
		return 40;
	}

	// Implement WT_Module_Sidebar
	public function hasSidebarContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER;
	}

	// Implement WT_Module_Sidebar
	public function getSidebarContent() {
		global $controller, $spouselinks, $parentlinks;
		$controller->addInlineJavascript('
			jQuery("#sb_family_nav_content")
				.on("click", ".flyout a", function() {
					return false;
				})
				.on("click", ".flyout3", function() {
					window.location.href = jQuery(this).data("href");
					return false;
				});
		');
		$person = WT_Person::getInstance($controller->record->getXref());

		ob_start();
		?>

		<div id="sb_family_nav_content">
			<table class="nav_content">
				<?php
				//-- parent families -------------------------------------------------------------
				foreach ($controller->record->getChildFamilies() as $family) {
					$this->drawFamily($family, $person->getChildFamilyLabel($family));
				}
				//-- step parents ----------------------------------------------------------------
				foreach ($controller->record->getChildStepFamilies() as $family) {
					$this->drawFamily($family, $person->getStepFamilyLabel($family));
				}
				//-- spouse and children --------------------------------------------------
				foreach ($controller->record->getSpouseFamilies() as $family) {
					$this->drawFamily($family, $person->getSpouseFamilyLabel($family, $controller->record));
				}
				//-- step children ----------------------------------------------------------------
				foreach ($controller->record->getSpouseStepFamilies() as $family) {
					$this->drawFamily($family, $family->getFullName());
				}
				?>
			</table>
		</div>
		<?php
		return ob_get_clean();
	}

	// Implement WT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	/**
	 * Format a family.
	 *
	 * @param Family $family
	 * @param string $title
	 */
	private function drawFamily(WT_Family $family, $title) {
		global $controller;
		?>
		<tr>
			<td class="center" colspan="2">
				<a class="famnav_title" href="<?php echo $family->getHtmlUrl(); ?>">
					<?php echo $title; ?>
				</a>
			</td>
		</tr>
		<?php
		foreach ($family->getSpouses() as $spouse) {
			$menu = new WT_Menu(getCloseRelationshipName($controller->record, $spouse));
			$menu->addClass('', 'submenu flyout');
			$menu->addSubmenu(new WT_Menu($this->getParents($spouse)));
			?>
			<tr>
				<td class="facts_label">
					<?php echo $menu->getMenu(); ?>
				</td>
				<td class="center <?php echo $controller->getPersonStyle($spouse); ?> nam">
					<?php if ($spouse->canDisplayName()): ?>
						<a class="famnav_link" href="<?php echo $spouse->getHtmlUrl(); ?>">
							<?php echo $spouse->getFullName(); ?>
						</a>
						<div class="font9">
							<?php echo $spouse->getLifeSpan(); ?>
						</div>
					<?php else: ?>
						<?php echo $spouse->getFullName(); ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
		foreach ($family->getChildren() as $child) {
			$menu = new WT_Menu(getCloseRelationshipName($controller->record, $child));
			$menu->addClass('', 'submenu flyout');
			$menu->addSubmenu(new WT_Menu($this->getFamily($child)));
			?>
			<tr>
				<td class="facts_label">
					<?php echo $menu->getMenu(); ?>
				</td>
				<td class="center <?php echo $controller->getPersonStyle($child); ?> nam">
					<?php if ($child->canDisplayName()): ?>
					<a class="famnav_link" href="<?php echo $child->getHtmlUrl(); ?>">
						<?php echo $child->getFullName(); ?>
					</a>
					<div class="font9">
						<?php echo $child->getLifeSpan(); ?>
					</div>
					<?php else: ?>
						<?php echo $child->getFullName(); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php
		}
	}

	/**
	 * Forat the parents of an individual.
	 *
	 * @param WT_Person $person
	 *
	 * @return string
	 */
	private function getParents(WT_Person $person) {
		$father = null;
		$mother = null;
		$html   = '<div class="flyout2">' . WT_I18N::translate('Parents') . '</div>';
		$family = $person->getPrimaryChildFamily();
		if ($person->canDisplayName() && $family !== null) {
			$father = $family->getHusband();
			$mother = $family->getWife();
			$html .= $this->getHTML($father) .
					 $this->getHTML($mother);

			// Can only have a step parent if one & only one parent found at this point
			if ($father instanceof WT_Person xor $mother instanceof WT_Person) {
				$stepParents = '';
				foreach ($person->getChildStepFamilies() as $family) {
					if (!$father instanceof WT_Person) {
						$stepParents .= $this->getHTML($family->getHusband());
					} else {
						$stepParents .= $this->getHTML($family->getWife());
					}
				}
				if ($stepParents) {
					$relationship = $father instanceof WT_Person ?
						WT_I18N::translate_c("father’s wife", "step-mother") : WT_I18N::translate_c("mother’s husband", "step-father");
					$html .= '<div class="flyout2">' . $relationship . '</div>' . $stepParents;
				}
			}
		}
		if (!($father instanceof WT_Person || $mother instanceof WT_Person)) {
			$html .= '<div class="flyout4">(' . WT_I18N::translate_c('unknown family', 'unknown') . ')</div>';
		}
		return $html;
	}

	/**
	 * Format a family.
	 *
	 * @param Individual $person
	 *
	 * @return string
	 */
	private function getFamily(WT_Person $person) {
		$html = '';
		if ($person->canDisplayName()) {
			foreach ($person->getSpouseFamilies() as $family) {
				$spouse = $family->getSpouse($person);
				$html .= $this->getHTML($spouse, true);
				$children = $family->getChildren();
				if (count($children) > 0) {
					$html .= "<ul class='clist'>";
					foreach ($children as $child) {
						$html .= '<li>' . $this->getHTML($child) . '</li>';
					}
					$html .= '</ul>';
				}
			}
		}
		if (!$html) {
			$html = '<div class="flyout4">(' . WT_I18N::translate('none') . ')</div>';;
		}

		return '<div class="flyout2">' . WT_I18N::translate('Family') . '</div>' . $html;

	}

	/**
	 * Format an individual.
	 *
	 * @param      $person
	 * @param bool $showUnknown
	 *
	 * @return string
	 */
	private function getHTML($person, $showUnknown = false) {
		if ($person instanceof WT_Person) {
			return '<div class="flyout3" data-href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</div>';
		} elseif ($showUnknown) {
			return '<div class="flyout4">(' . WT_I18N::translate('unknown') . ')</div>';
		} else {
			return '';
		}
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
