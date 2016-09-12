<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2006-2009 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License or,
// at your discretion, any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//
//$Id$

define('WT_SCRIPT_NAME', 'admin_trees_missing.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require WT_ROOT.'includes/functions/functions_print_facts.php';

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Missing data'))
	->pageHeader()
	->addExternalJavascript(WT_STATIC_URL.'js/autocomplete.js')
	->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
	->addExternalJavascript(WT_JQUERY_DT_HTML5)
	->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
	->addInlineJavascript('
		autocomplete();
		jQuery("#source_list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');

	$go 				= WT_Filter::post('go');
	$rootid 			= WT_Filter::get('rootid');
	$root_id			= WT_Filter::post('root_id');
	$rootid				= empty($root_id) ? $rootid : $root_id;
	$choose_relatives	= WT_Filter::post('choose_relatives') ? WT_Filter::post('choose_relatives') : 'child-family';
	$ged				= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
	$selected_facts		= WT_Filter::postArray('selected_facts');
	$maxgen				= WT_Filter::post('generations');

	$select = array(
		'child-family'		=> WT_I18N::translate('Parents and siblings'),
		'spouse-family'		=> WT_I18N::translate('Spouses and children'),
		'direct-ancestors'	=> WT_I18N::translate('Direct line ancestors'),
		'ancestors'			=> WT_I18N::translate('Direct line ancestors and their families'),
		'descendants'		=> WT_I18N::translate('Descendants'),
		'all'				=> WT_I18N::translate('All relatives')
	);

	$sourcefacts = array(
		'fsour' => WT_I18N::translate('Sources to the events'),
	);

	$indifacts = array(
		'fbirt' => WT_Gedcom_Tag::getLabel('BIRT'),
		'fbapm' => WT_Gedcom_Tag::getLabel('BAPM') . '/' . WT_Gedcom_Tag::getLabel('CHR'),
		'fdeat' => WT_Gedcom_Tag::getLabel('DEAT'),
		'fburi' => WT_Gedcom_Tag::getLabel('BURI'),
		'fconf' => WT_Gedcom_Tag::getLabel('CONF'),
		'ffcom' => WT_Gedcom_Tag::getLabel('FCOM'),
		'freli' => WT_Gedcom_Tag::getLabel('RELI'),
		'fbarm' => WT_Gedcom_Tag::getLabel('BARM'),
		'fbasm' => WT_Gedcom_Tag::getLabel('BASM'),
	);

	$famfacts = array(
		'fenga' => WT_Gedcom_Tag::getLabel('ENGA'),
		'fmarb' => WT_Gedcom_Tag::getLabel('MARB'),
		'fmarr' => WT_Gedcom_Tag::getLabel('MARR'),
	);

	$facts =  array_merge($sourcefacts, $indifacts, $famfacts);

	$facts_list = '';
	foreach ($selected_facts as $key) {
		$facts_list .= isset($facts[$key]) ? $facts[$key] . ', ' : null;
	}
	$facts_list = rtrim($facts_list, ', ');

	$defaults = array(
		'fbirt',
		'fdeat',
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
	<div id="missing_data-page">
		<div class="noprint">
			<h2><?php echo $controller->getPageTitle(); ?></h2>
			<div class="help_text">
				<p class="helpcontent">
					<?php echo /* I18N: Sub-title for missing data admin page */ WT_I18N::translate('A list of selected information missing from an individual and their relatives.'); ?>
					<br>
					<?php echo /* I18N: Help content for missing data admin page */ WT_I18N::translate('Whenever possible names are followed by the individual\'s lifespan dates for ease of identification. Note that these may include dates of baptism, christening, burial and cremation if birth and death dates are missing.<br>The list also ignores any estimates of dates or ages, so living people will be listed as missing death dates and places.<br>Some facts such as "Religion" do not commonly have sub-tags like date, place or source, so here only the fact itself is checked for.'); ?>
				</p>
			</div>
			<form name="resource" id="resource" method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
				<input type="hidden" name="go" value="1">
				<div id="admin_options">
					<div class="input">
						<label><?php echo WT_I18N::translate('Family tree'); ?></label>
						<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
					</div>
					<div class="input">
						<label for = "rootid"><?php echo WT_I18N::translate('Individual'); ?></label>
						<input data-autocomplete-type="INDI" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>" required>
					</div>
					<div class="input">
						<label for = "choose_relatives"><?php echo WT_I18N::translate('Choose relatives'); ?></label>
						<?php echo select_edit_control('choose_relatives', $select,	null, $choose_relatives); ?>
					</div>
					<div class="input">
						<label for = "generations"><?php echo WT_I18N::translate('Generations'); ?></label>
						<?php echo select_edit_control('generations', $generations, null, $maxgen); ?>
					</div>
					<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-check"></i>
						<?php echo $controller->getPageTitle(); ?>
					</button>

					<hr class="noprint" style="clear:both;">
					<h3>
						<?php echo WT_I18N::translate('Select all'); ?>
						<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
					</h3>
					<?php
						foreach ($sourcefacts as $code=>$name) {
							echo '
								<span style="display:inline-block;width: 200px;">
									<input class="check" type="checkbox" name="selected_facts[]" id="fact_' . $code . '"';
										if ($selected_facts && in_array($code, $selected_facts)) {
											echo 'checked="checked"';
										} elseif (!$selected_facts && in_array($code, $defaults)) {
											echo 'checked="checked"';
										}
									echo ' value="' . $code . '">
									<label for="fact_' . $code . '"> '.$name.'</label>
								</span>
							';
						}
					?>
					<h3><?php echo WT_I18N::translate('Individual'); ?></h3>
					<?php
						foreach ($indifacts as $code=>$name) {
							echo '
								<span style="display:inline-block;width: 200px;">
									<input class="check" type="checkbox" name="selected_facts[]" id="fact_' . $code . '"';
										if ($selected_facts && in_array($code, $selected_facts)) {
											echo 'checked="checked"';
										} elseif (!$selected_facts && in_array($code, $defaults)) {
											echo 'checked="checked"';
										}
									echo ' value="' . $code . '">
									<label for="fact_' . $code . '"> '.$name.'</label>
								</span>
							';
						}
					?>
					<h3><?php echo WT_I18N::translate('Family'); ?></h3>
					<?php
						foreach ($famfacts as $code=>$name) {
							echo '
								<span style="display:inline-block;width: 200px;">
									<input class="check" type="checkbox" name="selected_facts[]" id="fact_' . $code . '"';
										if ($selected_facts && in_array($code, $selected_facts)) {
											echo 'checked="checked"';
										} elseif (!$selected_facts && in_array($code, $defaults)) {
											echo 'checked="checked"';
										}
									echo ' value="' . $code . '">
									<label for="fact_' . $code . '"> '.$name.'</label>
								</span>
							';
						}
					?>

				</div>
			</form>
		</div>
		<hr class="noprint" style="clear:both;">
		<!-- end of form -->
		<?php if ($go == 1) {
			$controller
				->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
				->addExternalJavascript(WT_JQUERY_DT_HTML5)
				->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
				->addInlineJavascript('
					jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
					jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
					jQuery("#missing_data").dataTable({
						dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
						' . WT_I18N::datatablesI18N() . ',
						buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
						autoWidth: false,
						paging: true,
						pagingType: "full_numbers",
						lengthChange: true,
						filter: true,
						info: true,
						jQueryUI: true,
						sorting: [0,"asc"],
						displayLength: 20,
						columns: [
							/* 0-name  */	null,
							/* 1-event */	{"bSortable": false}
						],
						stateSave: true,
						stateDuration: -1
					});
					jQuery("#missing_data").css("visibility", "visible");
					jQuery(".loading-image").css("display", "none");
				');

			$person = WT_Person::getInstance($rootid);
			if ($person && $person->canDisplayDetails()) {
				// collect list of relatives
				$list = array();
				$list[$rootid] = $person;
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
						add_ancestors($list, $rootid, false, $maxgen);
						break;
					case "ancestors":
						add_ancestors($list, $rootid, true, $maxgen);
						break;
					case "descendants":
						$list[$rootid]->generation = 1;
						add_descendancy($list, $rootid, false, $maxgen);
						break;
					case "all":
						add_ancestors($list, $rootid, true, $maxgen);
						add_descendancy($list, $rootid, true, $maxgen);
						break;
				}
			}
			// prepare final list
			$total_individuals	= 0;
			$total_indi_events	= 0;
			$ind_missing_events	= 0;
			$total_families		= 0;
			$total_fam_events	= 0;
			$fam_missing_events	= 0;
			$total_matches		= 0;
			$result				= array();
			$check				= array();
			$source				= '';

			foreach ($list as $relative) {
				$indi_ct	= false;
				$fam_record	= array();
				// collect FAMS records for this person
				foreach($selected_facts as $key){
			        if(array_key_exists($key, $famfacts)) {
						$ct = preg_match_all('/\n1 FAMS @(.+)@/', $relative->getGedcomRecord(), $matches, PREG_SET_ORDER); // collect family info for FAM records ($matches)
						foreach ($matches as $match) {
							if (!in_array($match[1], $check)) {
								$total_matches = $total_matches + 1;
								$check[] = $match[1]; // avoid duplicate data from both spouses
								$fam_record[] = WT_Family::getInstance($match[1]);
							}
						}
					}
				}
				// BIRTH
				if (in_array('fbirt', $selected_facts)) {
					// Can't use getBirthDate(), as this also gives BAP/CHR events
					$total_indi_events	= $total_indi_events + 2;
					$event				= $relative->getFactByType('BIRT');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// BIRT:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BIRT:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BIRT:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('Birth source') : null;
					}
				}
				// DEATH
				if (in_array('fdeat', $selected_facts)) {
					// Can't use getDeathDate(), as this also gives BUR/CREM events
					$total_indi_events = $total_indi_events + 2;
					$event = $relative->getFactByType('DEAT');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// DEAT:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('DEAT:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('DEAT:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('Death source') : null;
					}
				}
				// BAPTISM / CHRISTENING
				if (in_array('fbapm', $selected_facts)) {
					$total_indi_events = $total_indi_events + 2;
					$chr	= $relative->getFactByType('CHR');
					$bapm	= $relative->getFactByType('BAPM');
					if (!$chr) {
						$event			= $relative->getFactByType('BAPM');
						$date_label		= WT_Gedcom_Tag::getLabel('BAPM:DATE');
						$place_label	= WT_Gedcom_Tag::getLabel('BAPM:PLAC');
						$source_label	= WT_I18N::translate('Baptism source');
					} else {
						$event			= $relative->getFactByType('CHR');
						$date_label		= WT_Gedcom_Tag::getLabel('CHR:DATE');
						$place_label	= WT_Gedcom_Tag::getLabel('CHR:PLAC');
						$source_label	= WT_I18N::translate('Christening source');
					}
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// BAPM/CHR:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = $date_label : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = $place_label : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = $source_label : null;
					}
				}
				// BARM - male only
				if (in_array('fbarm', $selected_facts) && $relative->getSex() == 'M') {
					$total_indi_events = $total_indi_events + 2;
					$event = $relative->getFactByType('BARM');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// BARM:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BARM:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BARM:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('Bar mitzvah source') : null;
					}
				}
				// BASM - female only
				if (in_array('fbasm', $selected_facts) && $relative->getSex() == 'F') {
					$total_indi_events = $total_indi_events + 2;
					$event = $relative->getFactByType('BASM');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// BASM:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BASM:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('BASM:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('Bat mitzvah source') : null;
					}
				}
				// CONFIRMATION
				if (in_array('fconf', $selected_facts)) {
					$total_indi_events = $total_indi_events + 2;
					$event = $relative->getFactByType('CONF');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// CONF:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('CONF:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('CONF:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('Confirmation source') : null;
					}
				}
				// FIRST COMMUNION
				if (in_array('ffcom', $selected_facts)) {
					$total_indi_events = $total_indi_events + 2;
					$event = $relative->getFactByType('FCOM');
					$check_event		= check_events($event, $ind_missing_events);
					$ind_missing_events	= $check_event['count'];
					// FCOM:SOUR
					$check_source		= check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source);
					$ind_missing_events	= $check_source['count'];
					$total_indi_events	= $check_source['total_indi'];
					//RESULT
					if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
						$indi_ct = true;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$check_event['date']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('FCOM:DATE') : null;
						$check_event['place']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('FCOM:PLAC') : null;
						$check_source['source']	== 1 ? $result[$relative->getXref()]['fact'][] = WT_I18N::translate('First communion source') : null;
					}
				}
				// RELIGION
				if (in_array('freli', $selected_facts)) {
					$total_indi_events = $total_indi_events + 1;
					$event = $relative->getFactByType('RELI');
					if (!$event) {
						$indi_ct = true;
						$ind_missing_events++;
						$result[$relative->getXref()]['count'] = 0;
						$result[$relative->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $relative->getHtmlUrl() . '" target="_blank;">' . $relative->getLifespanName() . '</a>';
						$result[$relative->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('RELI');
					}
				}
				// ENGAGEMENT (checking FAM records openssl_encrypt)
				if (in_array('fenga', $selected_facts)) {
					foreach ($fam_record as $family) {
						$total_fam_events = $total_fam_events + 2;
						$event = $family->getFactByType('ENGA');
						$check_event		= check_events($event, $fam_missing_events);
						$fam_missing_events	= $check_event['count'];
						// ENGA:SOUR
						$check_source		= check_source ($total_fam_events, $event, $fam_missing_events, $selected_facts);
						$fam_missing_events	= $check_source['count'];
						$total_fam_events	= $check_source['total_indi'];
						//RESULT
						if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
							$result[$family->getXref()]['count'] = 1;
							$result[$family->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
							$check_event['date']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('ENGA:DATE') : null;
							$check_event['place']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('ENGA:PLAC') : null;
							$check_source['source']	== 1 ? $result[$family->getXref()]['fact'][] = WT_I18N::translate('Engagement source') : null;
						}
					}
				}
				// Marriage (checking FAM records openssl_encrypt)
				if (in_array('fmarr', $selected_facts)) {
					foreach ($fam_record as $family) {
						$total_fam_events = $total_fam_events + 2;
						$event = $family->getFactByType('MARR');
						$check_event		= check_events($event, $fam_missing_events);
						$fam_missing_events	= $check_event['count'];
						// MARR:SOUR
						$check_source		= check_source ($total_fam_events, $event, $fam_missing_events, $selected_facts);
						$fam_missing_events	= $check_source['count'];
						$total_fam_events	= $check_source['total_indi'];
						//RESULT
						if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
							$result[$family->getXref()]['count'] = 1;
							$result[$family->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
							$check_event['date']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('MARR:DATE') : null;
							$check_event['place']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('MARR:PLAC') : null;
							$check_source['source']	== 1 ? $result[$family->getXref()]['fact'][] = WT_I18N::translate('Marriage source') : null;
						}
					}
				}
				// Marriage Banns (checking FAM records openssl_encrypt)
				if (in_array('fmarb', $selected_facts)) {
					foreach ($fam_record as $family) {
						$total_fam_events = $total_fam_events + 2;
						$event = $family->getFactByType('MARB');
						$check_event		= check_events($event, $fam_missing_events);
						$fam_missing_events	= $check_event['count'];
						// MARB:SOUR
						$check_source		= check_source ($total_fam_events, $event, $fam_missing_events, $selected_facts);
						$fam_missing_events	= $check_source['count'];
						$total_fam_events	= $check_source['total_indi'];
						//RESULT
						if ($check_event['date'] == 1 || $check_event['place'] == 1 || $check_source['source'] == 1) {
							$result[$family->getXref()]['count'] = 1;
							$result[$family->getXref()]['name'] = '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
							$check_event['date']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('MARB:DATE') : null;
							$check_event['place']	== 1 ? $result[$family->getXref()]['fact'][] = WT_Gedcom_Tag::getLabel('MARB:PLAC') : null;
							$check_source['source']	== 1 ? $result[$family->getXref()]['fact'][] = WT_I18N::translate('Marriage banns source') : null;
						}
					}
				}
				// count listed individual records
				if ($indi_ct) {
					$total_individuals ++;
				}
			} // end $list loop

			// count listed family records
			foreach ($result as $relative) {
				$total_families = $total_families + $relative['count'];
			}

			// output results as table
			?>
			<h2><?php echo /* I18N heading for report on missing data */ WT_I18N::translate('%1s related to %2s ', $select[$choose_relatives], $person->getLifespanName()); ?></h2>
			<h3><?php echo /* I18N sub-heading for report on missing data listing selected event types */ WT_I18N::translate('Mising data for <span style="font-weight:normal;">%s</span>', $facts_list); ?></h3>
			<div class="loading-image">&nbsp;</div>
			<table id="missing_data" style="width: 100%; visibility:hidden;">
				<thead>
					<tr>
						<th><?php echo WT_I18N::translate('Name'); ?></th>
						<th><?php echo WT_I18N::translate('Event'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($result as $relative) { ?>
						<tr>
							<td style="white-space:nowrap;"><?php  echo $relative['name']; ?></td>
							<td>
								<?php $fact_list = '';
								foreach ($relative['fact'] as $fact) {
									$fact_list .= $fact . ', ';
								}
//								$fact_list = str_replace(', , ', ', ', $fact_list);
								$fact_list = trim($fact_list, ', ');

//									trim(str_replace(', , ', ', ',$fact_list), ', ')


								echo $fact_list; ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<div id="summary_missing">
				<h3> <?php echo WT_I18N::translate('Summary'); ?></h3>
				<?php
				$print = 0;
				foreach($selected_facts as $key){
			        if(array_key_exists($key, $indifacts)) {
						$print++;
						?>
						<p><?php echo WT_I18N::translate('%1s individuals out of %2s relatives.', $total_individuals, count($list)); ?></p>
						<p><?php echo WT_I18N::translate('%1s events out of %2s events.', $ind_missing_events, $total_indi_events); ?></p>
						<?php
						break;
					}
			    }
				foreach($selected_facts as $key){
			        if(array_key_exists($key, $famfacts)) {
						$print++;
						?>
						<p><?php echo WT_I18N::translate('%1s families out of %2s related families.', $total_families, $total_matches); ?></p>
						<p><?php echo WT_I18N::translate('%1s family events out of %2s events.', $fam_missing_events, $total_fam_events); ?></p>
						<?php
						break;
					}
			    }
		        if($print == 2) { ?>
					<p><strong><?php echo WT_I18N::translate('Total'); ?></strong></p>
					<?php
					$a = $total_families + $total_individuals;
					$b = $total_matches + count($list);
					$c = $fam_missing_events + $ind_missing_events;
					$d = $total_fam_events + $total_indi_events;
					?>
					<p><strong><?php echo WT_I18N::translate('%1s individuals and families out of %2s.', $a, $b); ?></strong></p>
					<p><strong><?php echo WT_I18N::translate('%1s total events out of %2s.', $c, $d); ?></strong></p>
				<?php } ?>
			</div>
		<?php } ?>
	</div> <!-- close missing_data page div -->

<?php

function check_events ($event, $ind_missing_events) {
	if ($event && $event->getDate()->JD() != 0) {
		$date = '';
	} else {
		$date = 1;
		$ind_missing_events ++;
	}
	if ($event && $event->getPlace()) {
		$place = '';
	} else {
		$place = 1;
		$ind_missing_events ++;
	}
	return array('date' => $date, 'place' => $place, 'count' => $ind_missing_events);
}

function check_source ($total_indi_events, $event, $ind_missing_events, $selected_facts, $source) {
	if (in_array('fsour', $selected_facts)) {
		$source = '';
		$total_indi_events = $total_indi_events + 1;
		$event ? $ct = preg_match_all("/\d SOUR @(.*)@/", $event->getGedcomRecord(), $match) : $ct = 0;
		if ($ct == 0) {
			$source = 1;
			$ind_missing_events ++;
		}
	}
	return array('total_indi' => $total_indi_events, 'source' => $source, 'count' => $ind_missing_events);
}
