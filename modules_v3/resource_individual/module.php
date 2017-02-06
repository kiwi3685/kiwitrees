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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class resource_individual_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Individual');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A report of an individual’s details.');
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
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $GEDCOM;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('autocomplete();');

		session_write_close();

		//-- args
		$go 			= WT_Filter::post('go');
		$rootid 		= WT_Filter::get('rootid');
		$rootid			= WT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$photos			= WT_Filter::post('photos') ? WT_Filter::post('photos') : 'highlighted';
		$ged			= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
		$showsources	= WT_Filter::post('showsources') ? WT_Filter::post('showsources') : 0;
		$shownotes		= WT_Filter::post('shownotes') ? WT_Filter::post('shownotes') : 0;
		$exclude_tags	= array('FAMC', 'FAMS', '_WT_OBJE_SORT', 'HUSB', 'WIFE', 'CHIL');

		?>
		<div id="resource-page" class="individual_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "root_id"><?php echo WT_I18N::translate('Individual'); ?></label>
						<input data-autocomplete-type="INDI" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>">
					</div>
					<div class="chart_options">
						<label for = "showsources"><?php echo WT_I18N::translate('Show sources'); ?></label>
						<input type="checkbox" id="showsources" name="showsources" value="1"
							<?php if ($showsources) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "shownotes"><?php echo WT_I18N::translate('Show notes'); ?></label>
						<input type="checkbox" id="shownotes" name="shownotes" value="1"
							<?php if ($shownotes) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "photos"><?php echo WT_I18N::translate('Show media'); ?></label>
						<?php echo select_edit_control('photos', array(
							'none'=>WT_I18N::translate('None'),
							'all'=>WT_I18N::translate('All'),
							'highlighted'=>WT_I18N::translate('Highlighted image')),
							null,
							$photos);
						?>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr style="clear:both;">
			<!-- end of form -->
				<?php if ($go == 1) { ?>
					<?php
					$person = WT_Person::getInstance($rootid);
					$person->add_family_facts(false);
					$indifacts = $person->getIndiFacts();
					sort_facts($indifacts);
					if ($person && $person->canDisplayDetails()) { ; ?>
						<h2><?php echo $person->getFullName(); ?></h2>
						<?php // Image displays
						switch ($photos) {
							case 'highlighted':
								$image = $person->displayImage(true);
								if ($image) {
									echo '<div class="indi_mainimage">', $person->displayImage(true), '</div>';
								}
								break;
							case 'all':
								//show all level 1 images ?>
								<div class="images">
									<?php preg_match_all("/\d OBJE @(.+)@/", $person->getGedcomRecord(), $match);
									$allMedia =  $match[1];
									foreach ($allMedia as $media) {
										$image = WT_Media::getInstance($media);
										if ($image && $image->canDisplayDetails()) { ?>
											<span><?php echo $image->displayImage(); ?></span>
										<?php }
									} ?>
								</div>
								<?php
							break;
							case 'none':
							default:
							// show nothing
							break;
						} ?>
					<div class="facts_events">
						<h3><?php echo WT_I18N::translate('Facts and events'); ?></h3>
						<?php
						$source_num = 1;
						$source_list = array();
						foreach ($indifacts as $fact) {
							if (
								(!array_key_exists('extra_info', WT_Module::getActiveSidebars()) || !extra_info_WT_Module::showFact($fact))
								&& !in_array($fact->getTag(), $exclude_tags)
							) { ?>
								<p class="report_fact">
									<!-- fact label -->
									<span class="label">
										<?php echo print_fact_label($fact, $person);
										if ($showsources) {
											// -- count source(s) for this fact/event as footnote reference
											$ct = preg_match_all("/\d SOUR @(.*)@/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
											if ($ct > 0) {
												$sup = '<sup>';
													$sources = resource_sources($fact, 2, $source_num);
													for ($i = 0; $i < $ct; $i++) {
														$sup .= $source_num . ',&nbsp;';
														$source_num = $source_num + 1;
													}
													$sup = rtrim($sup,',&nbsp;');
													$source_list = array_merge($source_list, $sources);
												echo $sup . '</sup>';
											}
										} ?>
									</span>
									<!-- DETAILS -->
									<span class="details">
										<!-- fact date -->
										<?php echo $fact->getDate()->JD() != 0 ?  format_fact_date($fact, $person, false, false, false) : ""; ?>
										<!-- fact place -->
										<?php  if ($fact->getPlace()) { ?>
											<span class="place"><?php echo format_fact_place($fact, true); ?>
												<?php $address = print_address_structure($fact->getGedcomRecord(), 2, 'inline');
												echo $address != "" ?  '<span class="addr">' . $address . '</span>' : ""; ?>
											</span>
										<?php  } ?>
										<!-- fact details -->
										<?php
										if (!in_array($fact->getTag(), array('BURI'))) {
											$detail	= print_resourcefactDetails($fact, $person);
											echo $detail !== "&nbsp;" ?  '<span class="field">' . $detail . '</span>' : "";
										} ?>
									</span>
								</p>
							<?php }
						} ?>
					</div>
					<?php if ($shownotes) {
						$otherfacts = $person->getOtherFacts();
						foreach ($otherfacts as $fact) {
							if ($fact->getTag() == 'NOTE') { ?>
								<div class="notes">
									<h3><?php echo WT_I18N::translate('Notes'); ?></h3>
									<ol>
										<li>
											<?php echo print_resourcenotes($fact, 1, true, true); ?>
										</li>
									</ol>
								</div>
							<?php }
						}
					} ?>
					<div id="families">
						<h3><?php echo WT_I18N::translate('Families'); ?></h3>
						<?php
						$families = $person->getChildFamilies();
						// parents
						foreach ($families as $family) { ?>
							<h4><?php echo $person->getChildFamilyLabel($family); ?></h4>
							<div id="parents">
								<?php
								$husband = $family->getHusband();
								$wife = $family->getWife();
								if (!empty($husband)) { ?>
									<p>
										<span class="label">
											<?php echo WT_I18N::translate('Father'); ?>
										</span>
										<?php echo $husband->getFullName(); ?>&nbsp;
										<span class="details">
											<?php echo $this->personDetails($husband); ?>
										</span>
									</p>
								<?php }
								if (!empty($wife)) { ?>
									<p>
										<span class="label">
											<?php echo WT_I18N::translate('Mother'); ?>
										</span>
										<?php echo $wife->getFullName(); ?>&nbsp;
										<span class="details">
											<?php echo $this->personDetails($wife); ?>
										</span>
									</p>
								<?php }
								$marriage_details = marriageDetails($family);
								if (!empty($marriage_details)) {
									echo $marriage_details . '&nbsp;';
								} ?>
							</div>
							<div id="siblings">
								<?php
								$children = $family->getChildren();
								foreach ($children as $child) {
									if (!empty($child) && $child != $person) {  ?>
										<p>
											<span class="label">
												<?php echo get_relationship_name(get_relationship($person, $child)); ?>
											</span>
											<?php echo $child->getFullName(); ?>&nbsp;
											<span class="details">
												<?php echo $this->personDetails($child); ?>
											</span>
										</p>
									<?php }
								} ?>
							</div>
						<?php }
						// spouses
						$families = $person->getSpouseFamilies();
						foreach ($families as $family) {
							$spouse = $family->getSpouse($person);
							$marriage = $family->getMarriage(); ?>
							<h4><?php echo ($marriage ? WT_I18N::translate('Family with spouse') : WT_I18N::translate('Family with partner')); ?></h4>
							<div id="spouses">
								<p>
									<span class="label">
										<?php echo ($marriage ? WT_I18N::translate('Spouse') : WT_I18N::translate('Partner')); ?>
									</span>
									<?php echo $spouse->getFullName(); ?>&nbsp;
									<span class="details">
										<?php echo $this->personDetails($spouse); ?>
									</span>
								</p>
								<?php
								$marriage_details = marriageDetails($family);
								if (!empty($marriage_details)) {
									echo $marriage_details . '&nbsp;';
								} ?>
							</div>
							<div id="spouse_children">
								<?php
								$children = $family->getChildren();
								foreach ($children as $child) {
									if (!empty($child)) {  ?>
										<p>
											<span class="label">
												<?php echo get_relationship_name(get_relationship($person, $child)); ?>
											</span>
											<?php echo $child->getFullName(); ?> &nbsp;
											<span class="details">
												<?php echo $this->personDetails($child); ?>
											</span>
										</p>
									<?php }
								} ?>
							</div>
						<?php } ?>
					</div>
					<?php if ($showsources) {?>
						<div id="facts_sources">
							<h3><?php echo WT_I18N::translate('Sources'); ?></h3>
							<?php
								foreach ($source_list as $key => $value) {
									echo '
										<p>
											<span>' . ($key + 1) . '</span>
											<span>' . $value . '</span>
										</p>
									';
								}
							?>
						</div>
					<?php }
					} elseif ($person && $person->canDisplayName()) { ?>
						<h2><?php echo $this->getTitle() . '&nbsp;-&nbsp;' . $person->getFullName(); ?></h2>
						<p class="ui-state-highlight"><?php echo WT_I18N::translate('The details of this individual are private.'); ?></p>
						<?php exit;
					} else { ?>
						<h2><?php echo $this->getTitle(); ?></h2>
						<p class="ui-state-error"><?php echo WT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
						<?php exit;
					}
				} ?>
			</div>
	<?php }

	private function personDetails($person) {
		$birth_date = $person->getBirthDate();
		$birth_plac = $person->getBirthPlace();
		$death_date = $person->getDeathDate();
		$death_plac = $person->getDeathPlace();
		$birth = '';
		$death = '';

		if ($birth_date->isOK() || $birth_plac != '') {
			$birth = WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
				$birth_date->Display() . '&nbsp;' .
				$birth_plac;
		}

		if ($death_date->isOK() || $death_plac != '') {
			$death = ($birth == '' ? '' : '&nbsp;-&nbsp;') .
				WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
				$death_date->Display() . '&nbsp;' .
				$death_plac;
		}

		return $birth . $death;
	}

}
