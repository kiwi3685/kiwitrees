<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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
class report_family_KT_Module extends KT_Module implements KT_Module_Report {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ KT_I18N::translate('Family');
	}
	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A report of family members and their details.');
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
		$fam_xref = $controller->getSignificantFamily()->getXref();
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $fam_xref . '&amp;ged=' . KT_GEDURL,
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
		$controller = new KT_Controller_Family();
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
		$rootid 		= KT_Filter::get('rootid');
		$root_id		= KT_Filter::post('root_id');
		$rootid			= empty($root_id) ? $rootid : $root_id;
		$ged			= KT_Filter::post('ged') ? KT_Filter::post('ged') : $GEDCOM;
		$showsources	= KT_Filter::post('showsources') ? KT_Filter::post('showsources') : 0;
		$shownotes		= KT_Filter::post('shownotes') ? KT_Filter::post('shownotes') : 0;
		$missing		= KT_Filter::post('missing') ? KT_Filter::post('missing') : 0;
		$showmedia		= KT_Filter::post('showmedia') ? KT_Filter::post('showmedia') : 'main';
		$photos			= KT_Filter::post('photos') ? KT_Filter::post('photos') : 'highlighted';
		$exclude_tags	= array('CHAN','NAME','SEX','SOUR','NOTE','OBJE','RESN','FAMC','FAMS','TITL','CHIL','HUSB','WIFE','_UID','_KT_OBJE_SORT');
		$basic_tags		= array('BIRT','BAPM_CHR','DEAT','BURI_CREM');
		?>
		<div id="page" class="family_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo KT_GEDURL; ?>">
					<div class="chart_options">
						<label for = "rootid"><?php echo KT_I18N::translate('Family'); ?></label>
						<input data-autocomplete-type="FAM" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>">
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
						<label for = "missing"><?php echo KT_I18N::translate('Show basic events when blank'); ?></label>
						<input class="savestate" type="checkbox" id="missing" name="missing" value="1"
							<?php if ($missing) echo ' checked="checked"'; ?>
						>
					</div>
					<div class="chart_options">
						<label for = "showmedia"><?php echo KT_I18N::translate('Show family media'); ?></label>
						<?php echo select_edit_control(
							'showmedia',
							array(
								'none'=>KT_I18N::translate('None'),
								'main'=>KT_I18N::translate('Main'),
								'all'=>KT_I18N::translate('All')
							),
							null,
							$showmedia,
							'class="savestate"'
						); ?>
					</div>
					<div class="chart_options">
						<label for = "photos"><?php echo KT_I18N::translate('Show individual media'); ?></label>
						<?php echo select_edit_control('photos', array(
							'none'=>KT_I18N::translate('None'),
							'all'=>KT_I18N::translate('All'),
							'highlighted'=>KT_I18N::translate('Highlighted image')),
							null,
							$photos,
							'class="savestate"'
						);
						?>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo KT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr style="clear:both;">
			<!-- end of form -->
			<div class="loading-image">&nbsp;</div>
			<div id="container" style="visibility:hidden;">
				<?php
				$family		= KT_Family::getInstance($rootid);
				$sections	= array('husband', 'wife', 'children');
				if ($family && $family->canDisplayDetails()) { ; ?>
					<h2>
						<a href="<?php echo $family->getHtmlUrl(); ?>"><?php echo $family->getFullName(); ?></a>
					</h2>
					<div class="images">
					<?php // Image displays
						// Iterate over all of the media items for the person
						preg_match_all('/\n(\d) OBJE @(' . KT_REGEX_XREF . ')@/', $family->getGedcomRecord(), $matches, PREG_SET_ORDER);
						if ($matches) {
							foreach ($matches as $match) {
								$level = $match[1];
								$media = KT_Media::getInstance($match[2]);
								if (!$media || !$media->canDisplayDetails() || $media->isExternal()) {
									continue;
								}
								switch ($showmedia) {
									case 'all':
										//show all level images ?>
										<span><?php echo $media->displayImage(); ?></span>
										<?php
										break;
									case 'main':
										//show only level 1 images
										if ($level == 1) { ?>
											<span><?php echo $media->displayImage(); ?></span>
										<?php }
										break;
									case 'none':
									default:
									// show nothing
									break;
								}
							}
						} ?>
					</div>
					<div id="accordion">
						<?php
						$number			= 0;
						$sup			= '';
						$source_list	= array();
						$data			= array();
						$husb = $family->getHusband();
						$wife = $family->getWife();
						foreach($sections as $section) {
							if ($section == 'husband') {
								$children = array($husb);
							}
							if ($section == 'wife') {
								$children = array($wife);
							}
							if ($section == 'children') {
								$children = $family->getChildren();
							}
							foreach ($children as $child) {
								if ($child) {
									$person	= KT_Person::getInstance($child->getXref());
									$person->add_family_facts(false);
									$indifacts = $person->getIndiFacts();
									switch ($section) {
										case 'husband' :
											$header = KT_I18N::translate('Husband');
											break;
										case 'wife' :
											$header = KT_I18N::translate('Wife');
											break;
										case 'children' :
											$header = getCloseRelationshipName($husb ? $husb : $wife, $child);
											break;
									}
									if ($person && $person->canDisplayDetails()) {
										if ($missing) {
											// set missing tag variables
											$indifact_list = array();
											$missing_facts = array();
											foreach ($indifacts as $fact) {
												if ($fact->getTag() === 'BAPM' || $fact->getTag() === 'CHR') {
													$indifact_list[] = 'BAPM_CHR';
												} elseif ($fact->getTag() === 'BURI' || $fact->getTag() === 'CREM') {
													$indifact_list[] = 'BURI_CREM';
												} else {
													$indifact_list[] = $fact->getTag();
												}
											}
											foreach ($basic_tags as $var) {
												if (!in_array($var, $indifact_list)) {
													$missing_facts[] = new KT_Event('1 ' . $var, null, -1);
												}
											}
											$indifacts = array_merge($indifacts, $missing_facts);
										}?>
										<h3>
											<a href="<?php echo $child->getHtmlUrl(); ?>">
												<span class="relationship"><?php echo $header; ?></span>
												&nbsp;-&nbsp;
												<?php echo $child->getLifespanName(); ?>
											</a>
										</h3>
										<div class="<?php echo $section; ?>">
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
														<?php $sort_current_objes = array();
														$sort_ct = preg_match_all('/\n1 _KT_OBJE_SORT @(.*)@/', $person->getGedcomRecord(), $sort_match, PREG_SET_ORDER);
														for ($i = 0; $i < $sort_ct; $i++) {
															if (!isset($sort_current_objes[$sort_match[$i][1]])) {
																$sort_current_objes[$sort_match[$i][1]] = 1;
															} else {
																$sort_current_objes[$sort_match[$i][1]]++;
															}
															$sort_obje_links[] = $sort_match[$i][1];
														}
														foreach ($sort_obje_links as $media) {
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
											<!-- facts and events for this individual -->
											<div class="facts_events">
												<?php
												sort_facts($indifacts);
												foreach ($indifacts as $fact) {
													if (!in_array($fact->getTag(), $exclude_tags)) {
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
														<?php
														// Add spouse details to marriage events
														if (in_array($fact->getTag(), array('MARR', '_NMR')) && $fact->getSpouse()) { ?>
															<p class="report_fact">
																<span class="label indent"><?php echo $fact->getDate()->isOK() ? KT_I18N::translate('Spouse') : KT_I18N::translate('Partner'); ?></span>
																<span class="details"><span class="field"><?php echo $fact->getSpouse()->getLifespanName(); ?></span></span>
															</p>
														<?php }
													}
												} ?>
												<hr>
												<?php // parents
												if ($section !== 'children') {
													$parent_families = $person->getChildFamilies();
													foreach ($parent_families as $parent_family) {
														$p_husb	= $parent_family->getHusband();
														$p_wife	= $parent_family->getWife();
														if (!empty($p_husb)) { ?>
															<p class="report_fact">
																<span class="label indent"><?php echo KT_I18N::translate('Father'); ?></span>
																<span class="field"><?php echo $p_husb->getLifeSpanName(); ?></span>
															</p>
														<?php }
														if (!empty($p_wife)) { ?>
															<p class="report_fact">
																<span class="label indent"><?php echo KT_I18N::translate('Mother'); ?></span>
																<span class="field"><?php echo $p_wife->getLifeSpanName(); ?></span>
															</p>
														<?php }
													}
												}
												// Notes
												$otherfacts = $person->getOtherFacts();
												if ($otherfacts && $shownotes) { ?>
													<div id="notes">
														<h4><?php echo KT_I18N::translate('Notes'); ?></h4>
														<ol>
															<?php foreach ($otherfacts as $fact) {
																if ($fact->getTag() === 'NOTE') { ?>
																	<li><?php echo print_resourcenotes($fact, 1, true, true); ?></li>
																<?php }
															} ?>
														</ol>
													</div>
												<?php } ?>
											</div>
										</div>
									<?php }
								}
							}
						} ?>
					</div>
					<?php if ($showsources) {?>
						<div id="facts_sources">
							<h3><?php echo KT_I18N::translate('Sources'); ?></h3>
							<?php foreach ($source_list as $source) { ?>
								<p>
									<span><?php echo ($source['key']); ?></span>
									<span><?php echo $source['value']; ?></span>
								</p>
							<?php } ?>
						</div>
					<?php }
				} elseif ($family && $family->canDisplayName()) { ?>
					<h2><?php echo $this->getTitle() . '&nbsp;-&nbsp;' . $family->getFullName(); ?></h2>
					<p class="ui-state-highlight"><?php echo KT_I18N::translate('The details of this family are private.'); ?></p>
					<?php exit;
				} else { ?>
					<h2><?php echo $this->getTitle(); ?></h2>
					<p class="ui-state-error"><?php echo KT_I18N::translate('This family does not exist or you do not have permission to view it.'); ?></p>
					<?php exit;
				} ?>
			</div>
		</div>
	<?php }
}
