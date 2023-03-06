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

class cousins_tab_KT_Module extends KT_Module implements KT_Module_Tab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Cousins');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab showing cousins of an individual.');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 80;
	}

	// Implement KT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
		global $controller;

		$person			= $controller->getSignificantIndividual();
		$fullname		= $controller->record->getFullName();
		$xref			= $controller->record->getXref();
		$parentFamily	= '';
		$cousins		= KT_Filter::post('cousins');

		if ($person->getPrimaryChildFamily()) {
			$parentFamily = $person->getPrimaryChildFamily();
		}
		if ($parentFamily && $parentFamily->getHusband()) {
			$grandparentFamilyHusb = $parentFamily->getHusband()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyHusb = '';
		}
		if ($parentFamily && $parentFamily->getWife()) {
			$grandparentFamilyWife = $parentFamily->getWife()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyWife = '';
		}

		ob_start();
		?>
		<div id="cousins_tab_content">
			<?php if ($parentFamily) { ?>
				<div class="descriptionbox rela">
					<form name="cousinsForm" id="cousinsForm" method="post" action="">
						<input type="hidden" name="cousins" value="<?php echo $cousins == 'second' ? 'first' : 'second'; ?>">
						<button class="btn btn-primary" type="submit">
							<i class="fa fa-eye"></i>
							<?php echo $cousins == 'second' ? KT_I18N::translate('Show first cousins') : KT_I18N::translate('Show second cousins'); ?>
						</button>
					</form>
				</div>
				<?php if ($cousins <> 'second') { ?>
					<div class="first_cousins">
						<?php
						$firstCousinsF	= $grandparentFamilyHusb ? $this->getFirstCousins($parentFamily, $grandparentFamilyHusb, 'husb') : array('',0,0,'');
						$list 			= $firstCousinsF[3]; // list of cousins used by next function to assess possible duplicates due to siblings marry siblings links.
						$firstCousinsM	= $grandparentFamilyWife ? $this->getFirstCousins($parentFamily, $grandparentFamilyWife, 'wife', $list) : array('',0,0);
						$countCousinsF	= $firstCousinsF[1];
						$countCousinsM	= $firstCousinsM[1];
						$totalCousins	= $countCousinsF + $countCousinsM;
						$duplicatesF	= $firstCousinsF[2];
						$duplicatesM	= $firstCousinsM[2];
						$duplicates		= $duplicatesF + $duplicatesM;
						?>
						<div class="cousins_row">
							<h3><?php echo KT_I18N::plural('%2$s has %1$d first cousin recorded', '%2$s has %1$d first cousins recorded', $totalCousins, $totalCousins, $fullname); ?></h3>
							<?php if ($duplicates > 0) { ?>
								<p style="margin: 0 15px 5px;"><?php echo /* I18N: a reference to cousins of siblings married to siblings */ KT_I18N::plural('%1$d is on both sides of the family', '%1$d are on both sides of the family', $duplicates, $duplicates); ?></p>
							<?php } ?>
						</div>
						<div class="cousins_row">
							<div class="cousins_f">
								<h4><?php echo KT_I18N::translate('Father\'s family (%s)', $countCousinsF); ?></h4>
								<?php echo $firstCousinsF[0]; ?>
							</div>
							<div class="cousins_m">
								<h4><?php echo KT_I18N::translate('Mother\'s family (%s)', $countCousinsM); ?></h4>
								<?php echo $firstCousinsM[0]; ?>
							</div>
						</div>
					</div>
				<?php }
				if ($cousins == 'second') { ?>
					<div class="secondCousins">
						<div class="second_cousins">
							<?php
							$secondCousinsF = $grandparentFamilyHusb ? $this->getSecondCousins($grandparentFamilyHusb) : array('',0);
							$secondCousinsM = $grandparentFamilyWife ? $this->getSecondCousins($grandparentFamilyWife) : array('',0);
							$countCousinsF	= $secondCousinsF[1];
							$countCousinsM	= $secondCousinsM[1];
							$totalCousins	= $countCousinsF + $countCousinsM;
							?>
							<div class="cousins_row">
								<h3><?php echo KT_I18N::plural('%2$s has %1$d second cousin recorded', '%2$s has %1$d second cousins recorded', $totalCousins, $totalCousins, $fullname); ?></h3>
							</div>
							<div class="cousins_row">
								<div class="cousins_f">
									<h4><?php echo KT_I18N::translate('Second cousins on father\'s side (%s)', $countCousinsF); ?></h4>
									<?php echo $secondCousinsF[0]; ?>
								</div>
								<div class="cousins_m">
									<h4><?php echo KT_I18N::translate('Second cousins on mother\'s side (%s)', $countCousinsM); ?></h4>
									<?php echo $secondCousinsM[0]; ?>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
			<?php } else { ?>
				<h3><?php echo KT_I18N::translate('No family available'); ?></h3>
			<?php } ?>
		</div>

		<?php
		return ob_get_clean();

	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return true;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	function getFirstCousins($parentFamily, $grandparentFamily, $type, $list = array()) {
		$html				= '';
		$count_1cousins		= 0;
		$prev_fam_id		= -1;
		$family				= '';
		$count_duplicates	= 0;
		$list ? $list : $list = array();

		if ($type == 'husb') {
			$myParent = $parentFamily->getHusband()->getXref();
		} elseif ($type == 'wife') {
			$myParent = $parentFamily->getWife()->getXref();
		}

		foreach ($grandparentFamily->getChildren() as $key => $child) {
			if ($child->getSpouseFamilies() && $child->getXref() <> $myParent) {
				foreach ($child->getSpouseFamilies() as $family) {
					if (!is_null($family)) {
						$i = 0;
						$children = $family->getChildren();
						foreach ($children as $key => $child2) {
							if ($child2->canDisplayName()) {
								$i ++;
								if (in_array($child2->getXref(), $list)) {$count_duplicates++;} // this adjusts the count for cousins of siblings married to siblings
								$list[] = $child2->getXref();
								$record = KT_Person::getInstance($child2->getXref());
								$cousinParentFamily = substr($record->getPrimaryChildFamily(), 0, strpos($record->getPrimaryChildFamily(), '@'));
					 			if ( $cousinParentFamily == $parentFamily->getXref() )
									continue; // cannot be cousin to self
								$tmp = array('M'=>'', 'F'=>'F', 'U'=>'NN');
								$isF = $tmp[$child2->getSex()];
								$label = '';
								$famcrec = get_sub_record(1, '1 FAMC @'.$cousinParentFamily.'@', $record->getGedcomRecord());
								$pedi = get_gedcom_value('PEDI', 2, $famcrec, '', false);
								if ($pedi) {
									$label = KT_Gedcom_Code_Pedi::getValue($pedi, $record);
								}
								$cousinParentFamily = substr($child2->getPrimaryChildFamily(), 0, strpos($child2->getPrimaryChildFamily(), '@'));
								$family2 = KT_Family::getInstance($cousinParentFamily);
								if ($cousinParentFamily != $prev_fam_id) {
									$prev_fam_id = $cousinParentFamily;
									$html .= '<h5>' . KT_I18N::translate('Parents');
										if (!is_null($family2)) {
											$html .= '<a target="_blank" rel="noopener noreferrer" href="' . $family2->getHtmlUrl() . '">&nbsp;' . $family2->getFullName() . '</a>';
										}
									$html .= '</h5>';
									$i = 1;
								}
								$html .= '
									<div class="person_box' . $isF . '">
										<span class="cousins_counter">' . $i . '</span>
										<span class="cousins_name">
											<a target="_blank" rel="noopener noreferrer" href="' . $child2->getHtmlUrl() . '">' . $child2->getFullName() . '</a>
										</span>
										<span class="cousins_lifespan">' . $child2->getLifeSpan() . '</span>
										<span class="cousins_pedi">' . $label . '</span>
									</div>
								';
								$count_1cousins ++;
							}
						}
					}
				}
			}
		}

		return array($html, $count_1cousins, $count_duplicates, $list);
	}

	function getSecondCousins($grandparentFamily) {
		$html			= '';
		$count_2cousins	= 0;
		$prev_fam_id	= -1;

		for ($x = 1; $x < 3; $x ++) {
			$x == 1 ? $myGrandParent = $grandparentFamily->getHusband() : $myGrandParent = $grandparentFamily->getWife();
			if ($myGrandParent->getPrimaryChildFamily()) {
				foreach ($myGrandParent->getPrimaryChildFamily()->getChildren() as $key => $child) {
					if ($child->getSpouseFamilies() && $child->getXref() <> $myGrandParent->getXref()) {
						foreach ($child->getSpouseFamilies() as $family) {
							if (!is_null($family)) {
								$i = 0;
								$children = $family->getChildren();
								foreach ($children as $key => $child2) {
									foreach ($child2->getSpouseFamilies() as $family2) {
										if (!is_null($family2)) {
											$children2 = $family2->getChildren();
											foreach ($children2 as $key => $child3) {
												if ($child->canDisplayName()) {
													$i ++;
													$tmp				= array('M'=>'', 'F'=>'F', 'U'=>'NN');
													$isF				= $tmp[$child3->getSex()];
													$cousinParentFamily = substr($child3->getPrimaryChildFamily(), 0, strpos($child3->getPrimaryChildFamily(), '@'));
													$family3			= KT_Family::getInstance($cousinParentFamily);
													if ($cousinParentFamily != $prev_fam_id) {
										 				$prev_fam_id = $cousinParentFamily;
														$html .= '<h5>' . KT_I18N::translate('Parents');
															if (!is_null($family3)) {
																$html .= '<a target="_blank" rel="noopener noreferrer" href="' . $family3->getHtmlUrl() . '">&nbsp;' . $family3->getFullName() . '</a>';
															}
														$html .= '</h5>';
														$i = 1;
													}
													$html .= '
														<div class="person_box' . $isF . '">
															<span class="cousins_counter">' . $i . '</span>
															<span class="cousins_name">
																<a target="_blank" rel="noopener noreferrer" href="' . $child3->getHtmlUrl() . '">' . $child3->getFullName() . '</a>
															</span>
															<span class="cousins_lifespan">' . $child3->getLifeSpan() . '</span>
														</div>
													';
													$count_2cousins ++;
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
		}

		return array($html, $count_2cousins);
	}

}
