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

class report_related_fam_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ KT_I18N::translate('Related families');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Related individuals” module */ KT_I18N::translate('A report of families closely related to a selected individual.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class KT_Module_Report
	public function show() {
		global $controller, $GEDCOM, $MAX_DESCENDANCY_GENERATIONS, $DEFAULT_PEDIGREE_GENERATIONS;
		require KT_ROOT.'includes/functions/functions_resource.php';
		require KT_ROOT.'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();
				savestate(\'' . $this->getName() . '\');

				jQuery("#accordion")
					.accordion({event: "click", collapsible: true, heightStyle: "content"})
					.find("h3 a").click(function(ev){
						ev.stopPropagation();
					});
				jQuery("#container").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');

		//-- args
		$rootid 			= KT_Filter::get('rootid');
		$root_id			= KT_Filter::post('root_id');
		$rootid				= empty($root_id) ? $rootid : $root_id;
		$choose_relatives	= KT_Filter::post('choose_relatives') ? KT_Filter::post('choose_relatives') : 'child-family';
		$showsources		= KT_Filter::post('showsources') ? KT_Filter::post('showsources') : 0;
		$shownotes			= KT_Filter::post('shownotes') ? KT_Filter::post('shownotes') : 0;
		$photos				= KT_Filter::post('photos') ? KT_Filter::post('photos') : 'highlighted';
		$ged				= KT_Filter::post('ged') ? KT_Filter::post('ged') : $GEDCOM;
		$maxgen				= KT_Filter::post('generations', KT_REGEX_INTEGER, $DEFAULT_PEDIGREE_GENERATIONS);
		$exclude_tags		= array('FAMC', 'FAMS', '_KT_OBJE_SORT', 'HUSB', 'WIFE', 'CHIL');

		$select = array(
			'child-family'		=> KT_I18N::translate('Parents and siblings'),
			'spouse-family'		=> KT_I18N::translate('Spouses and children'),
			'direct-ancestors'	=> KT_I18N::translate('Direct line ancestors'),
			'ancestors'			=> KT_I18N::translate('Direct line ancestors and their families'),
			'descendants'		=> KT_I18N::translate('Descendants'),
			'all'				=> KT_I18N::translate('All relatives')
		);

		$generations = array(
			1	=> KT_I18N::number(1),
			2	=> KT_I18N::number(2),
			3	=> KT_I18N::number(3),
			4	=> KT_I18N::number(4),
			5	=> KT_I18N::number(5),
			6	=> KT_I18N::number(6),
			7	=> KT_I18N::number(7),
			8	=> KT_I18N::number(8),
			9	=> KT_I18N::number(9),
			10	=> KT_I18N::number(10),
			-1	=> KT_I18N::translate('All')
		);

		?>
		<div id="page" class="families_report">
			<div class="noprint">
				<h2><?php echo $this->getTitle(); ?></h2>
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo KT_GEDURL; ?>">
					<div class="chart_options">
						<label for = "rootid"><?php echo KT_I18N::translate('Individual'); ?></label>
						<input data-autocomplete-type="INDI" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>">
					</div>
					<div class="chart_options">
						<label for = "showsources"><?php echo KT_I18N::translate('Show sources'); ?></label>
						<input class="savestate" type="checkbox" id="showsources" name="showsources" value="1"
							<?php if ($showsources) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "shownotes"><?php echo KT_I18N::translate('Show notes'); ?></label>
						<input class="savestate" type="checkbox" id="shownotes" name="shownotes" value="1"
							<?php if ($shownotes) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "photos"><?php echo KT_I18N::translate('Show media'); ?></label>
						<?php echo select_edit_control(
							'photos',
							array(
								'none'=>KT_I18N::translate('None'),
								'all'=>KT_I18N::translate('All'),
								'highlighted'=>KT_I18N::translate('Highlighted image')
							),
							null,
							$photos,
							'class="savestate"'
						); ?>
					</div>
					<div class="chart_options">
						<label for = "choose_relatives"><?php echo KT_I18N::translate('Choose relatives'); ?></label>
						<?php echo select_edit_control(
							'choose_relatives',
							$select,
							null,
							$choose_relatives,
							'class="savestate"'
						); ?>
					</div>
					<div class="chart_options">
						<label for = "generations"><?php echo KT_I18N::translate('Generations'); ?></label>
						<?php echo select_edit_control(
							'generations',
							$generations,
							null,
							$maxgen,
							'class="savestate"'
						); ?>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo KT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php
			$person = KT_Person::getInstance($rootid);
			$list = array();
			if ($person && $person->canDisplayDetails()) { ?>
				<h2><?php echo /* I18N: heading for report on related individuals */ KT_I18N::translate('%1s related to %2s ', $select[$choose_relatives], $person->getLifespanName()); ?></h2>
				<?php
				// collect list of relatives
				switch ($choose_relatives) {
					case "child-family":
						foreach ($person->getChildFamilies() as $family) {
							$husband = $family->getHusband();
							$wife = $family->getWife();
							if (!empty($husband)) {
								$list[$husband->getXref()] = $husband;
							}
							if (!empty($wife)) {
								$list[$wife->getXref()] = $wife;
							}
							$children = $family->getChildren();
							foreach ($children as $child) {
								if (!empty($child)) $list[$child->getXref()] = $child;
							}
						}
						break;
					case "spouse-family":
						foreach ($person->getSpouseFamilies() as $family) {
						$husband = $family->getHusband();
							$wife = $family->getWife();
							if (!empty($husband)) {
								$list[$husband->getXref()] = $husband;
							}
							if (!empty($wife)) {
								$list[$wife->getXref()] = $wife;
							}
							$children = $family->getChildren();
							foreach ($children as $child) {
								if (!empty($child)) $list[$child->getXref()] = $child;
							}
						}
						break;
					case "direct-ancestors":
						$list[$rootid] = $person;
						add_ancestors($list, $rootid, false, $maxgen);
						break;
					case "ancestors":
						$list[$rootid] = $person;
						add_ancestors($list, $rootid, true, $maxgen);
						break;
					case "descendants":
						$list[$rootid] = $person;
						add_descendancy($list, $rootid, false, $maxgen);
						break;
					case "all":
						$list[$rootid] = $person;
						add_ancestors($list, $rootid, true, $maxgen);
						add_descendancy($list, $rootid, true, $maxgen);
						break;
				}
			}
			// output display
			if ($list) { ?>
				<div class="loading-image">&nbsp;</div>
				<div id="container" style="visibility:hidden;">
					<div id="accordion">
						<?php
						$number			= 0;
						$sup			= '';
						$source_list	= array();
						$data			= array();
						foreach ($list as $relative) {
							$person = KT_Person::getInstance($relative->getXref());
							$person->add_family_facts(false);
							$indifacts = $person->getIndiFacts();
							sort_facts($indifacts);
							if ($person && $person->canDisplayDetails()) { ?>
								<h3><?php echo $person->getLifespanName(); ?></a></h3>
								<div class="accordion-container">
										<?php // Image displays
										switch ($photos) {
											case 'highlighted': ?>
												<div style="vertical-align: top;">
													<?php $image = $person->displayImage(true);
													if ($image) { ?>
														<div class="indi_mainimage" style="display: inline-block;"><?php echo $person->displayImage(true); ?></div>
													<?php } ?>
													<h3 style="display: inline-block;vertical-align: top;margin: 10px;">
														<a href="<?php echo $person->getHtmlUrl(); ?>">
															<?php echo $person->getLifespanName(); ?>
														</a>
													</h3>
												</div>
												<?php break;
											case 'all':
												//show all level 1 images ?>
												<div class="images" style="display: inline-block;">
													<?php preg_match_all("/\d OBJE @(.+)@/", $person->getGedcomRecord(), $match);
													$allMedia =  $match[1];
													foreach ($allMedia as $media) {
														$image = KT_Media::getInstance($media);
														if ($image && $image->canDisplayDetails()) { ?>
															<span><?php echo $image->displayImage(); ?></span>
														<?php }
													} ?>
												</div>
												<h3 style="margin: 10px;">
													<a href="<?php echo $person->getHtmlUrl(); ?>">
														<?php echo $person->getLifespanName(); ?>
													</a>
												</h3>
												<?php
											break;
											case 'none':
											default:
											// show nothing
											break;
										} ?>
									<div class="facts_events">
										<h3><?php echo KT_I18N::translate('Facts and events'); ?></h3>
										<?php
										foreach ($indifacts as $fact) {
											if (
												(!array_key_exists('extra_info', KT_Module::getActiveSidebars()) || !extra_info_KT_Module::showFact($fact))
												&& !in_array($fact->getTag(), $exclude_tags)
											) {
												$data		 = getResourcefact($fact, $person, $sup, $source_list, $number);
												$sup		 = $data[0];
												$source_list = $data[1];
												$number		 = $data[2]; ?>
												<p class="report_fact">
													<span class="label"><?php echo print_fact_label($fact, $person) . $showsources ? $sup : ''; ?></span>
													<span class="details">
														<?php echo $data[3]['date']; ?>
														<?php echo $data[3]['place']; ?>
														<?php echo $data[3]['addr']; ?>
														<?php echo $data[3]['detail']; ?>
													</span>
												</p>
											<?php }
										} ?>
									</div> <!-- .facts_events -->
									<?php if ($shownotes) {
										$otherfacts = $person->getOtherFacts();
										foreach ($otherfacts as $fact) {
											if ($fact->getTag() == 'NOTE') { ?>
												<div class="notes">
													<h3><?php echo KT_I18N::translate('Notes'); ?></h3>
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
										<h3><?php echo KT_I18N::translate('Families'); ?></h3>
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
															<?php echo KT_I18N::translate('Father'); ?>
														</span>
														<?php echo $husband->getFullName(); ?>&nbsp;
														<span class="details">
															<?php echo personDetails($husband); ?>
														</span>
													</p>
												<?php }
												if (!empty($wife)) { ?>
													<p>
														<span class="label">
															<?php echo KT_I18N::translate('Mother'); ?>
														</span>
														<?php echo $wife->getFullName(); ?>&nbsp;
														<span class="details">
															<?php echo personDetails($wife); ?>
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
																<?php echo personDetails($child); ?>
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
											<h4><?php echo ($marriage ? KT_I18N::translate('Family with spouse') : KT_I18N::translate('Family with partner')); ?></h4>
											<div id="spouses">
												<?php if (!empty($spouse)) { ?>
													<p>
														<span class="label">
															<?php echo ($marriage ? KT_I18N::translate('Spouse') : KT_I18N::translate('Partner')); ?>
														</span>
														<?php echo $spouse->getFullName(); ?>&nbsp;
														<span class="details">
															<?php echo personDetails($spouse); ?>
														</span>
													</p>
												<?php }
												$marriage_details = marriageDetails($family);
												if (!empty($marriage_details)) {
													echo $marriage_details . '&nbsp;';
												} ?>
											</div>
											<div id="spouse_children">
												<?php
												$children = $family->getChildren();
												foreach ($children as $child) {
													if (!empty($child)) { ?>
														<p>
															<span class="label">
																<?php echo get_relationship_name(get_relationship($person, $child)); ?>
															</span>
															<?php echo $child->getFullName(); ?> &nbsp;
															<span class="details">
																<?php echo personDetails($child); ?>
															</span>
														</p>
													<?php }
												} ?>
											</div>
										<?php } ?>
										</div>
								</div> <!-- .accordion-container -->
							<?php } elseif ($person && $person->canDisplayName()) { ?>
								<h2><?php echo $this->getTitle() . '&nbsp;-&nbsp;' . $person->getFullName(); ?></h2>
								<p class="ui-state-highlight"><?php echo KT_I18N::translate('The details of this individual are private.'); ?></p>
								<?php exit;
							} else { ?>
								<h2><?php echo $this->getTitle(); ?></h2>
								<p class="ui-state-error"><?php echo KT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
								<?php exit;
							}
						}
						if ($showsources) {?>
							<h3><?php echo KT_I18N::translate('Sources'); ?></h3>
							<div id="facts_sources">
								<?php foreach ($source_list as $source) { ?>
									<p>
										<span><?php echo ($source['key']); ?></span>
										<span><?php echo $source['value']; ?></span>
									</p>
								<?php } ?>
							</div>
						<?php } ?>
					</div> <!-- #accordion -->
				</div> <!-- #container -->
			<?php } else { ?>
				<div id="noresult">
					<?php echo KT_I18N::translate('Nothing found'); ?>
				</div>
			<?php } ?>
		</div> <!-- #pages -->
		<?php
	}

}
