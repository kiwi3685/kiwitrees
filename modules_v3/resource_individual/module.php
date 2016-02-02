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
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Individual report');
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
	public function defaultMenuOrder() {
		return 26;
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
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('autocomplete();');

		session_write_close();

		//-- args
		$go 			= WT_Filter::post('go');
		$rootid 		= WT_Filter::get('rootid');
		$root_id		= WT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$photos			= WT_Filter::post('photos') ? WT_Filter::post('photos') : 'highlighted';
		$ged			= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
		$showsources	= WT_Filter::post('showsources') ? WT_Filter::post('showsources') : 'checked';
		$shownotes		= WT_Filter::post('shownotes') ? WT_Filter::post('shownotes') : 'checked';

		?>
		<div id="resource-page" class="individual_report">
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<div class="noprint">
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "rootid"><?php echo WT_I18N::translate('Individual'); ?></label>
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
							<?php if ($showsources) echo ' checked="checked"'; ?>
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
			<?php if ($go == 1) {
				$person = WT_Person::getInstance($rootid);
				$indifacts = $person->getIndiFacts();
				sort_facts($indifacts);
				if ($person && $person->canDisplayDetails()) { ; ?>
					<h2><?php echo $person->getFullName(); ?></h2>
					<?php // Image displays
					switch ($photos) {
						case 'highlighted':
							$image = $person->displayImage(true);
							if ($image) {
								echo '<div id="indi_mainimage">', $person->displayImage(), '</div>';
							}
							break;
						case 'all':
						//show all level 1 images
						break;
						case 'none':
						default:
						// show nothing
						break;

					} ?>
					<div id="facts_events">
						<h3><?php echo WT_I18N::translate('Facts and events'); ?></h3>
						<?php
						$source_num = 1;
						$source_list = array();
						foreach ($indifacts as $fact) {
							if (
								(!array_key_exists('extra_info', WT_Module::getActiveSidebars()) || !extra_info_WT_Module::showFact($fact))
								&& !in_array($fact->getTag(), array('FAMC', 'FAMS'))
							) { ?>
									<div class="individual_report_fact">
										<label>
											<?php echo print_fact_label($fact, $person);
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
											} ?>
										</label>
										<?php echo print_resourcefact($fact, $person); ?>
									</div>
							<?php }
						} ?>
					</div>
					<div id="notes">
						<h3><?php echo WT_I18N::translate('Notes'); ?></h3>
						<ol>
							<?php
							$otherfacts = $person->getOtherFacts();
							foreach ($otherfacts as $fact) {
								if ($fact->getTag() == 'NOTE') { ?>
									<li> <?php echo print_resourcenotes($fact, 1, true, true); ?></li>
								<?php }
							} ?>
						</ol>
					</div>
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
								if (!empty($husband)) {  ?>
									<p>
										<?php echo
										'<span class="label">' . WT_I18N::translate('Father') . '</span>' .
										$husband->getFullName() . '&nbsp;' .
										'(<span class="details">' .
											WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
											$husband->getBirthDate()->Display() . '&nbsp;' .
											$husband->getBirthPlace() . '&nbsp;-&nbsp;' .
											WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
											$husband->getDeathDate()->Display() . '&nbsp;' .
											$husband->getDeathPlace() .
										'</span>)';
										?>
									</p>
								<?php }
								if (!empty($wife)) {  ?>
									<p>
										<?php echo
										'<span class="label">' . WT_I18N::translate('Mother') . '</span>' .
										$wife->getFullName() . '&nbsp;' .
										'(<span class="details">' .
											WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
											$wife->getBirthDate()->Display() . '&nbsp;' .
											$wife->getBirthPlace() . '&nbsp;-&nbsp;' .
											WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
											$wife->getDeathDate()->Display() . '&nbsp;' .
											$wife->getDeathPlace() .
										'</span>)';
										?>
									</p>
								<?php } ?>
							</div>
							<div id="siblings">
								<?php
								$children = $family->getChildren();
								foreach ($children as $child) {
									if (!empty($child) && $child != $person) {  ?>
										<p>
											<?php echo
											'<span class="label">' . get_relationship_name(get_relationship($person, $child)) . '</span>' .
											$child->getFullName() . '&nbsp;' .
											'(<span class="details">' .
												WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
												$child->getBirthDate()->Display() . '&nbsp;' .
												$child->getBirthPlace() . '&nbsp;-&nbsp;';
												WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
												$child->getDeathDate()->Display() . '&nbsp;' .
												$child->getDeathPlace() .
											'</span>)';
											?>
										</p>
									<?php }
								} ?>
							</div>
						<?php }

						// spouses
						$families = $person->getSpouseFamilies();
						foreach ($families as $family) {?>
							<h4><?php echo WT_I18N::translate('Family with wife'); ?></h4>
							<div id="spouses">
								<?php
								$wife = $family->getWife();
								if (!empty($wife)) {  ?>
									<p>
										<?php echo
										'<span class="label">' . WT_I18N::translate('Wife') . '</span>' .
										$wife->getFullName() . '&nbsp;' .
										'(<span class="details">' .
											WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
											$wife->getBirthDate()->Display() . '&nbsp;' .
											$wife->getBirthPlace() . ')' .
											WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
											$wife->getDeathDate()->Display() . '&nbsp;' .
											$wife->getDeathPlace() .
										'</span>)';
										?>
									</p>
								<?php } ?>
							</div>
							<div id="spouse_children">
								<?php
								$children = $family->getChildren();
								foreach ($children as $child) {
									if (!empty($child)) {  ?>
										<p>
											<?php echo
											'<span class="label">' . get_relationship_name(get_relationship($person, $child)) . '</span>' .
											$child->getFullName() . '&nbsp;' .
											'(<span class="details">' .
												WT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
												$child->getBirthDate()->Display() . '&nbsp;' .
												$child->getBirthPlace() . ')' .
												WT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
												$child->getDeathDate()->Display() . '&nbsp;' .
												$child->getDeathPlace() .
											'</span>)';
											?>
										</p>
									<?php }
								} ?>
							</div>
						<?php } ?>

					</div>
					<div class="page-break"></div>
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
				<?php } elseif ($person && $person->canDisplayName()) { ?>
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

}
