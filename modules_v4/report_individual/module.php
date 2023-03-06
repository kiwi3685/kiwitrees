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

class report_individual_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ KT_I18N::translate('Individual');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A report of an individual’s details.');
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
		global $controller, $GEDCOM;
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
			');

		//-- args
		$go 			= KT_Filter::post('go');
		$rootid 		= KT_Filter::get('rootid');
		$root_id		= KT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$photos			= KT_Filter::post('photos') ? KT_Filter::post('photos') : 'highlighted';
		$ged			= KT_Filter::post('ged') ? KT_Filter::post('ged') : $GEDCOM;
		$showsources	= KT_Filter::post('showsources') ? KT_Filter::post('showsources') : 0;
		$shownotes		= KT_Filter::post('shownotes') ? KT_Filter::post('shownotes') : 0;
		$exclude_tags	= array('FAMC', 'FAMS', '_KT_OBJE_SORT', 'HUSB', 'WIFE', 'CHIL');

		?>
		<div id="page" class="individual_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo KT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "root_id"><?php echo KT_I18N::translate('Individual'); ?></label>
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
	 				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo KT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr style="clear:both;">
			<!-- end of form -->
				<?php
				$person = KT_Person::getInstance($rootid);
				$person->add_family_facts(false);
				$indifacts = $person->getIndiFacts();
				sort_facts($indifacts);
				if ($person && $person->canDisplayDetails()) { ; ?>
					<h2>
						<a href="<?php echo $person->getHtmlUrl(); ?>"><?php echo $person->getLifespanName(); ?></a>
					</h2>
					<?php // Image displays
					switch ($photos) {
						case 'highlighted':
							$image = $person->displayImage(true);
							if ($image) {
								echo '<div class="indi_mainimage">', $person->displayImage(true), '</div>';
							}
							break;
						case 'all':
							//show all individual images, sorted or unsorted ?>
							<div class="images">
								<?php
                                $sort_current_objes = array();
                                $media_objes        = array();
                                $obje_links         = array();
								$sort_ct            = preg_match_all('/\n1 _KT_OBJE_SORT @(.*)@/', $person->getGedcomRecord(), $sort_match, PREG_SET_ORDER);
                                $media_ct           = preg_match_all('/\n\d OBJE @(.*)@/', $person->getGedcomRecord(), $media_match, PREG_SET_ORDER);

                                if ($sort_ct) {
    								for ($i = 0; $i < $sort_ct; $i++) {
    									if (!isset($sort_current_objes[$sort_match[$i][1]])) {
    										$sort_current_objes[$sort_match[$i][1]] = 1;
    									} else {
    										$sort_current_objes[$sort_match[$i][1]]++;
    									}
    									$obje_links[] = $sort_match[$i][1];
    								}
                                }

                                if ($media_ct) {
                                    for ($i = 0; $i < $media_ct; $i++) {
    									if (!isset($media_objes[$media_match[$i][1]])) {
    										$media_objes[$media_match[$i][1]] = 1;
    									} else {
    										$media_objes[$media_match[$i][1]]++;
    									}
                                        if (!in_array($media_match[$i][1], $obje_links)) {
                                            $obje_links[] = $media_match[$i][1];
                                        }

    								}
                                }

                                foreach ($obje_links as $media) {
                                    $image = KT_Media::getInstance($media);
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
					<h3><?php echo KT_I18N::translate('Facts and events'); ?></h3>
					<?php
					$source_num = 1;
					$source_list = array();
					foreach ($indifacts as $fact) {
						if (
							(!array_key_exists('extra_info', KT_Module::getActiveSidebars()) || !extra_info_KT_Module::showFact($fact))
							&& !in_array($fact->getTag(), $exclude_tags)
						) { ?>
							<div class="report_fact">
								<!-- fact label -->
								<span class="label">
									<?php echo print_fact_label($fact, $person);
									if ($showsources) {
										// -- count source(s) for this fact/event as footnote reference
										$ct = preg_match_all("/\d SOUR @(.*)@/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
										if ($ct > 0) {
											$sup = '<sup class="source">';
												$sources = report_sources($fact, 2, $source_num);
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
									if (!in_array($fact->getTag(), array('BURI'))) { // avoid printing address details twice
										$detail	= print_resourcefactDetails($fact, $person);
										echo $detail !== "&nbsp;" ?  '<span class="field">' . $detail . '</span>' : "";
									} ?>
									<!-- fact or event notes (level 2) -->
									<?php if ($shownotes) {
										$fe_notes = print_resourcenotes($fact, 2, true, true);
										echo $fe_notes ? '
											<div style="font-size: 90%;">
												<span class="label indent">' .
													KT_I18N::translate('Note') . ': <br>
												</span>
												<br>
												<span style="white-space: pre-wrap;">' . $fe_notes . '</span>
											</div>' : "";
									} ?>
								</span>
							</div>
						<?php }
					} ?>
				</div>
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
							$husband	= $family->getHusband();
							$wife		= $family->getWife();
							$marriage	= marriageDetails($family);
							if (!empty($husband)) { ?>
								<p>
									<span class="label indent">
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
									<span class="label indent">
										<?php echo KT_I18N::translate('Mother'); ?>
									</span>
									<?php echo $wife->getFullName(); ?>&nbsp;
									<span class="details">
										<?php echo personDetails($wife); ?>
									</span>
								</p>
							<?php }
							// marriage details
							if (!empty($marriage)) { ?>
								<div class="indent" style="margin-bottom: 30px;">
									<?php echo $marriage; ?>
								</div>
							<?php } ?>
						</div>
						<div id="siblings">
							<?php
							$children = $family->getChildren();
							foreach ($children as $child) {
								if (!empty($child) && $child != $person  && $child->canDisplayDetails()) {  ?>
									<p>
										<span class="label indent">
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
						$spouse		= $family->getSpouse($person);
						$married	= $family->getMarriage();
						$marriage	= marriageDetails($family); ?>
						<h4><?php echo ($married ? KT_I18N::translate('Family with spouse') : KT_I18N::translate('Family with partner')); ?></h4>
						<div id="spouses">
							<?php if (!empty($spouse)) { ?>
								<p>
									<span class="label indent">
										<?php echo ($married ? KT_I18N::translate('Spouse') : KT_I18N::translate('Partner')); ?>
									</span>
									<?php echo $spouse->getFullName(); ?>&nbsp;
									<span class="details">
										<?php echo personDetails($spouse); ?>
									</span>
								</p>
							<?php }
							// marriage details
							if (!empty($marriage)) { ?>
								<div class="indent" style="margin-bottom: 30px;">
									<?php echo $marriage; ?>
								</div>
							<?php } ?>
						</div>
						<div id="spouse_children">
							<?php
							$children = $family->getChildren();
							foreach ($children as $child) {
								if (!empty($child) && $child->canDisplayDetails()) { ?>
									<p>
										<span class="label indent">
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
				<?php if ($showsources) {?>
					<div id="facts_sources">
						<h3><?php echo KT_I18N::translate('Sources'); ?></h3>
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
					<p class="ui-state-highlight"><?php echo KT_I18N::translate('The details of this individual are private.'); ?></p>
					<?php exit;
				} else { ?>
					<h2><?php echo $this->getTitle(); ?></h2>
					<p class="ui-state-error"><?php echo KT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
					<?php exit;
				} ?>
			</div>
	<?php }

}
