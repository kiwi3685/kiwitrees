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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class report_related_fam_WT_Module extends WT_Module implements WT_Module_Report {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ WT_I18N::translate('Related families');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Related individuals” module */ WT_I18N::translate('A report of families closely related to a selected individual.');
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

	// Implement WT_Module_Report
	public function getReportMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Report
	public function show() {
		global $controller, $GEDCOM, $MAX_DESCENDANCY_GENERATIONS, $DEFAULT_PEDIGREE_GENERATIONS;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();

				jQuery("#accordion")
					.accordion({event: "click", collapsible: true, heightStyle: "content"})
					.find("h3 a").click(function(ev){
						ev.stopPropagation();
					});
				jQuery("#container").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');

		//-- args
		$rootid 			= WT_Filter::get('rootid');
		$root_id			= WT_Filter::post('root_id');
		$rootid				= empty($root_id) ? $rootid : $root_id;
		$choose_relatives	= WT_Filter::post('choose_relatives') ? WT_Filter::post('choose_relatives') : 'child-family';
		$showsources		= WT_Filter::post('showsources') ? WT_Filter::post('showsources') : 0;
		$shownotes			= WT_Filter::post('shownotes') ? WT_Filter::post('shownotes') : 0;
		$photos				= WT_Filter::post('photos') ? WT_Filter::post('photos') : 'highlighted';
		$ged				= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
		$maxgen				= WT_Filter::post('generations', WT_REGEX_INTEGER, $DEFAULT_PEDIGREE_GENERATIONS);
		$exclude_tags		= array('FAMC', 'FAMS', '_WT_OBJE_SORT', 'HUSB', 'WIFE', 'CHIL');

		$select = array(
			'child-family'		=> WT_I18N::translate('Parents and siblings'),
			'spouse-family'		=> WT_I18N::translate('Spouses and children'),
			'direct-ancestors'	=> WT_I18N::translate('Direct line ancestors'),
			'ancestors'			=> WT_I18N::translate('Direct line ancestors and their families'),
			'descendants'		=> WT_I18N::translate('Descendants'),
			'all'				=> WT_I18N::translate('All relatives')
		);

		$generations = array(
			1	=> WT_I18N::number(1),
			2	=> WT_I18N::number(2),
			3	=> WT_I18N::number(3),
			4	=> WT_I18N::number(4),
			5	=> WT_I18N::number(5),
			6	=> WT_I18N::number(6),
			7	=> WT_I18N::number(7),
			8	=> WT_I18N::number(8),
			9	=> WT_I18N::number(9),
			10	=> WT_I18N::number(10),
			-1	=> WT_I18N::translate('All')
		);

		?>
		<div id="page" class="families_report">
			<div class="noprint">
				<h2><?php echo $this->getTitle(); ?></h2>
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo WT_GEDURL; ?>">
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
					<div class="chart_options">
						<label for = "choose_relatives"><?php echo WT_I18N::translate('Choose relatives'); ?></label>
						<?php echo select_edit_control('choose_relatives', $select,	null, $choose_relatives); ?>
					</div>
					<div class="chart_options">
						<label for = "generations"><?php echo WT_I18N::translate('Generations'); ?></label>
						<?php echo select_edit_control('generations', $generations, null, $maxgen); ?>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php
			$person = WT_Person::getInstance($rootid);
			if ($person && $person->canDisplayDetails()) { ?>
				<h2><?php echo /* I18N: heading for report on related individuals */ WT_I18N::translate('%1s related to %2s ', $select[$choose_relatives], $person->getLifespanName()); ?></h2>
				<?php
				// collect list of relatives
				$list = array();
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
						add_ancestors($list, $rootid, false, $MAX_DESCENDANCY_GENERATIONS);
						break;
					case "ancestors":
						add_ancestors($list, $rootid, true, $MAX_DESCENDANCY_GENERATIONS);
						break;
					case "descendants":
						$list[$rootid]->generation = 1;
						add_descendancy($list, $rootid, false, $MAX_DESCENDANCY_GENERATIONS);
						break;
					case "all":
						add_ancestors($list, $rootid, true, $MAX_DESCENDANCY_GENERATIONS);
						add_descendancy($list, $rootid, true, $MAX_DESCENDANCY_GENERATIONS);
						break;
				}
			}
			// output display
			?>
			<div class="loading-image">&nbsp;</div>
			<div id="container" style="visibility:hidden;">
				<div id="accordion">
					<?php
					$number			= 0;
					$sup			= '';
					$source_list	= array();
					$data			= array();
					foreach ($list as $relative) {
						$person = WT_Person::getInstance($relative->getXref());
						$person->add_family_facts(false);
						$indifacts = $person->getIndiFacts();
						sort_facts($indifacts);
						if ($person && $person->canDisplayDetails()) { ?>
							<h3>
								<a href="<?php echo $person->getHtmlUrl(); ?>"><?php echo $person->getLifespanName(); ?></a>
							</h3>
							<div class="accordion-container">
								<?php // Image displays
								switch ($photos) {
									case 'highlighted':
										$image = $person->displayImage(true);
										if ($image) { ?>
											<div class="indi_mainimage"><?php echo $person->displayImage(true); ?></div>
										<?php }
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
									foreach ($indifacts as $fact) {
										if (
											(!array_key_exists('extra_info', WT_Module::getActiveSidebars()) || !extra_info_WT_Module::showFact($fact))
											&& !in_array($fact->getTag(), $exclude_tags)
										) {
											$data		 = getResourcefact($fact, $family, $sup, $source_list, $number);
											$sup		 = $data[0];
											$source_list = $data[1];
											$number		 = $data[2]; ?>
											<p class="report_fact">
												<span class="label"><?php echo print_fact_label($fact, $family) . $showsources ? $sup : ''; ?></span>
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
														<?php echo personDetails($husband); ?>
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
										<h4><?php echo ($marriage ? WT_I18N::translate('Family with spouse') : WT_I18N::translate('Family with partner')); ?></h4>
										<div id="spouses">
											<?php if (!empty($spouse)) { ?>
												<p>
													<span class="label">
														<?php echo ($marriage ? WT_I18N::translate('Spouse') : WT_I18N::translate('Partner')); ?>
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
							<p class="ui-state-highlight"><?php echo WT_I18N::translate('The details of this individual are private.'); ?></p>
							<?php exit;
						} else { ?>
							<h2><?php echo $this->getTitle(); ?></h2>
							<p class="ui-state-error"><?php echo WT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
							<?php exit;
						}
					}
					if ($showsources) {?>
						<h3><?php echo WT_I18N::translate('Sources'); ?></h3>
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
		</div> <!-- #pages -->
		<?php
	}

}
