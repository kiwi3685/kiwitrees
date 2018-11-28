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

		$list_f				= array();
		$list_f2			= array();
		$list_f3			= array();
		$list_m				= array();
		$list_m2			= array();
		$list_m3			= array();
		$count_cousins_f	= 0;
		$count_cousins_m	= 0;
		$count_duplicates	= 0;
		$family				= '';
		$person				= $controller->getSignificantIndividual();
		$fullname			= $controller->record->getFullName();
		$xref				= $controller->record->getXref();
		$cousins = KT_Filter::post('cousins');

		if ($person->getPrimaryChildFamily()) {
			$parentFamily = $person->getPrimaryChildFamily();
		} else {
			return '<h3>'.KT_I18N::translate('No family available').'</h3>';
			exit;
		}
		if ($parentFamily->getHusband()) {
			$grandparentFamilyHusb = $parentFamily->getHusband()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyHusb = '';
		}
		if ($parentFamily->getWife()) {
			$grandparentFamilyWife = $parentFamily->getWife()->getPrimaryChildFamily();
		} else {
			$grandparentFamilyWife = '';
		}

//		ob_start();
		?>
		<div id="cousins_tab_content">
			<div class="descriptionbox rela">
				<form name="cousinsForm" id="cousinsForm" method="post" action="">
					<input type="hidden" name="cousins" value="<?php echo KT_Filter::post('cousins') == 'second' ? 'first' : 'second'; ?>">
					<button class="btn btn-primary" type="submit">
						<i class="fa fa-eye"></i>
						<?php echo KT_Filter::post('cousins') == 'second' ? KT_I18N::translate('Show first cousins') : KT_I18N::translate('Show second cousins'); ?>
					</button>
				</form>
			</div>
			<?php if (KT_Filter::post('cousins') <> 'second') { ?>
				<div class="first_cousins">
					<?php
					//Lookup father's siblings
					$rows = KT_DB::prepare("SELECT l_to as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND l_type LIKE 'CHIL' AND l_from LIKE '".substr($grandparentFamilyHusb, 0, strpos($grandparentFamilyHusb, '@'))."'")->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $row) {
						if ($row['xref'] != substr($parentFamily->getHusband(), 0, strpos($parentFamily->getHusband(), '@')))
							$list_f[]=$row['xref'];
					}
					//Lookup Aunt & Uncle's families (father's family)
					foreach ($list_f as $ids) {
						$rows = KT_DB::prepare("SELECT l_from as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND (l_type LIKE 'HUSB' OR l_type LIKE 'WIFE') AND l_to LIKE '".$ids."'")->fetchAll(PDO::FETCH_ASSOC);
						foreach ($rows as $row) {
							$list_f2[]=$row['xref'];
						}
					}
					//Lookup cousins (father's family)
					foreach ($list_f2 as $id2) {
						$rows = KT_DB::prepare("SELECT l_to as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND l_type LIKE 'CHIL' AND l_from LIKE '".$id2."'")->fetchAll(PDO::FETCH_ASSOC);
						foreach ($rows as $row) {
							$list_f3[]=$row['xref'];
							$count_cousins_f ++;
						}
					}

					//Lookup mother's siblings
					$rows = KT_DB::prepare("SELECT l_to as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND l_type LIKE 'CHIL' AND l_from LIKE '".substr($grandparentFamilyWife, 0, strpos($grandparentFamilyWife, '@'))."'")->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $row) {
						if ($row['xref'] != substr($parentFamily->getWife(), 0, strpos($parentFamily->getWife(), '@')))
							$list_m[]=$row['xref'];
					}
					//Lookup Aunt & Uncle's families (mother's family)
					foreach ($list_m as $ids) {
						$rows = KT_DB::prepare("SELECT l_from as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND (l_type LIKE 'HUSB' OR l_type LIKE 'WIFE') AND l_to LIKE '".$ids."'")->fetchAll(PDO::FETCH_ASSOC);
						foreach ($rows as $row) {
							$list_m2[]=$row['xref'];
						}
					}
					//Lookup cousins (mother's family)
					foreach ($list_m2 as $id2) {
						$rows = KT_DB::prepare("SELECT l_to as xref FROM `##link` WHERE l_file = ".KT_GED_ID." AND l_type LIKE 'CHIL' AND l_from LIKE '".$id2."'")->fetchAll(PDO::FETCH_ASSOC);
						foreach ($rows as $row) {
							$list_m3[] = $row['xref'];
							$count_cousins_m ++;
							if (in_array($row['xref'], $list_f3)) {$count_duplicates++;} // this adjusts the count for cousins of siblings married to siblings
							$famc[] = $id2;
						}
					}
					$count_cousins = $count_cousins_f + $count_cousins_m - $count_duplicates;
					$myParentFamily = $parentFamily->getXref();
					?>

					<div class="cousins_row">
						<h3><?php echo KT_I18N::plural('%2$s has %1$d first cousin recorded', '%2$s has %1$d first cousins recorded', $count_cousins, $count_cousins, $fullname); ?></h3>
						<?php if ($count_duplicates > 0) { ?>
							<p><?php echo /* I18N: a reference to cousins of siblings married to siblings */ KT_I18N::plural('%1$d is on both sides of the family', '%1$d are on both sides of the family', $count_duplicates, $count_duplicates); ?></p>
						<?php } ?>
					</div>
					<div class="cousins_row">
						<div class="cousins_f">
							<h4><?php echo KT_I18N::translate('Father\'s family (%s)', $count_cousins_f); ?></h4>
							<?php
							$i = 0;
							$prev_fam_id = -1;
							foreach ($list_f3 as $id3) {
								$i++;
								$record = KT_Person::getInstance($id3);
								$cousinParentFamily = substr($record->getPrimaryChildFamily(), 0, strpos($record->getPrimaryChildFamily(), '@'));
					 			if ( $cousinParentFamily == $myParentFamily )
									continue; // cannot be cousin to self
								$family	= KT_Family::getInstance($cousinParentFamily);
								$tmp	= array('M'=>'', 'F'=>'F', 'U'=>'NN');
								$isF	= $tmp[$record->getSex()];
								$label = '';
								$famcrec = get_sub_record(1, '1 FAMC @' . $cousinParentFamily . '@', $record->getGedcomRecord());
								$pedi = get_gedcom_value('PEDI', 2, $famcrec, '', false);
								if ($pedi) {
									$label = '<span class="cousins_pedi">' . KT_Gedcom_Code_Pedi::getValue($pedi, $record) . '</span>';
								}
								if ($cousinParentFamily != $prev_fam_id) {
					 				$prev_fam_id = $cousinParentFamily;
									?>
									<h5><?php echo KT_I18N::translate('Parents'); ?>
										<a target="_blank" rel="noopener noreferrer" href="<?php echo $family->getHtmlUrl(); ?>">
											&nbsp;<?php echo $family->getFullName(); ?>
										</a>
									</h5>
									<?php
									$i = 1;
								} ?>
								<div class="person_box<?php echo $isF; ?>">
									<span class="cousins_counter"><?php echo $i; ?></span>
									<span class="cousins_name">
										<a target="_blank" rel="noopener noreferrer" href="<?php echo $record->getHtmlUrl(); ?>">
											<?php echo $record->getFullName(); ?>
										</a>
									</span>
									<span class="cousins_lifespan"><?php echo $record->getLifeSpan(); ?></span>
									<span class="cousins_pedi"><?php echo $label; ?></span>
								</div>
							<?php } ?>
						</div>

						<div class="cousins_m">
							<h4><?php echo KT_I18N::translate('Mother\'s family (%s)', $count_cousins_m); ?></h4>
							<?php
							$i = 0;
							$prev_fam_id = -1;
							foreach ($list_m3 as $id3) {
								$i++;
								$record = KT_Person::getInstance($id3);
								$cousinParentFamily = substr($record->getPrimaryChildFamily(), 0, strpos($record->getPrimaryChildFamily(), '@'));
					 			if ( $cousinParentFamily == $myParentFamily )
					 				continue; // cannot be cousin to self
								$record = KT_Person::getInstance($id3);
								$cousinParentFamily = substr($record->getPrimaryChildFamily(), 0, strpos($record->getPrimaryChildFamily(), '@'));
								$family = KT_Family::getInstance($cousinParentFamily);
								$tmp = array('M'=>'', 'F'=>'F', 'U'=>'NN');
								$isF = $tmp[$record->getSex()];
								$label = '';
								$famcrec = get_sub_record(1, '1 FAMC @'.$cousinParentFamily.'@', $record->getGedcomRecord());
								$pedi = get_gedcom_value('PEDI', 2, $famcrec, '', false);
								if ($pedi) {
									$label = KT_Gedcom_Code_Pedi::getValue($pedi, $record);
								}
					 			if ($cousinParentFamily != $prev_fam_id) {
					 				$prev_fam_id = $cousinParentFamily;
									?>
									<h5><?php echo KT_I18N::translate('Parents'); ?>
										<a target="_blank" rel="noopener noreferrer" href="<?php echo $family->getHtmlUrl(); ?>">
											&nbsp;<?php echo $family->getFullName(); ?>
										</a>
									</h5>
									<?php
									$i = 1;
								} ?>
								<div class="person_box<?php echo $isF; ?>">
									<span class="cousins_counter"><?php echo $i; ?></span>
									<span class="cousins_name">
										<a target="_blank" rel="noopener noreferrer" href="<?php echo $record->getHtmlUrl(); ?>">
											<?php echo $record->getFullName(); ?>
										</a>
									</span>
									<span class="cousins_lifespan"><?php echo $record->getLifeSpan(); ?></span>
									<span class="cousins_pedi"><?php echo $label; ?></span>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if (KT_Filter::post('cousins') == 'second') { ?>
				<div class="secondCousins">
					<div class="second_cousins">
						<?php
						$secondCousinsF = $this->getSecondCousins($grandparentFamilyHusb);
						$secondCousinsM = $this->getSecondCousins($grandparentFamilyWife);
						$secondCousins	= $secondCousinsF[1] + $secondCousinsM[1];
						?>
						<div class="cousins_row">
							<h3><?php echo KT_I18N::plural('%2$s has %1$d second cousin recorded', '%2$s has %1$d second cousins recorded', $secondCousins, $secondCousins, $fullname); ?></h3>
						</div>
						<div class="cousins_row">
							<div class="cousins_f">
								<h4><?php echo KT_I18N::translate('Second cousins on father\'s side (%s)', $secondCousinsF[1]); ?></h4>
								<?php echo $secondCousinsF[0]; ?>
							</div>
							<div class="cousins_m">
								<h4><?php echo KT_I18N::translate('Second cousins on mother\'s side (%s)', $secondCousinsM[1]); ?></h4>
								<?php echo $secondCousinsM[0]; ?>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>

		<?php
//		return ob_get_clean();

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

	function getSecondCousins($grandparentFamily) {
		$html			= '';
		$count_2cousins	= 0;
		$prev_fam_id	= -1;
		$myGrandFather	= $grandparentFamily->getHusband()->getXref();
		foreach ($grandparentFamily->getHusband()->getPrimaryChildFamily()->getChildren() as $key => $child) {
			if ($child->getSpouseFamilies() && $child->getXref() <> $myGrandFather) {
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
											$family				= KT_Family::getInstance($cousinParentFamily);
											if ($cousinParentFamily != $prev_fam_id) {
								 				$prev_fam_id = $cousinParentFamily;
												$html .= '<h5>' . KT_I18N::translate('Parents').'<a target="_blank" rel="noopener noreferrer" href="' . $family->getHtmlUrl() . '">&nbsp;' . $family->getFullName() . '</a></h5>';
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

		$myGrandMother = $grandparentFamily->getWife()->getXref();
		foreach ($grandparentFamily->getWife()->getPrimaryChildFamily()->getChildren() as $key => $child) {
			if ($child->getSpouseFamilies() && $child->getXref() <> $myGrandMother) {
				foreach ($child->getSpouseFamilies() as $family) {
					if (!is_null($family)) {
						$children = $family->getChildren();
						foreach ($children as $key => $child2) {
							foreach ($child2->getSpouseFamilies() as $family2) {
								if (!is_null($family2)) {
									$i = 0;
									$children2 = $family2->getChildren();
									foreach ($children2 as $key => $child3) {
										if ($child->canDisplayName()) {
											$i ++;
											$tmp = array('M'=>'', 'F'=>'F', 'U'=>'NN');
											$isF = $tmp[$child3->getSex()];
											$cousinParentFamily = substr($child3->getPrimaryChildFamily(), 0, strpos($child3->getPrimaryChildFamily(), '@'));
											$family = KT_Family::getInstance($cousinParentFamily);
											if ($cousinParentFamily != $prev_fam_id) {
								 				$prev_fam_id = $cousinParentFamily;
												$html .= '<h5>' . KT_I18N::translate('Parents').'<a target="_blank" rel="noopener noreferrer" href="' . $family->getHtmlUrl() . '">&nbsp;' . $family->getFullName() . '</a></h5>';
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

		return array($html, $count_2cousins);
	}

}
