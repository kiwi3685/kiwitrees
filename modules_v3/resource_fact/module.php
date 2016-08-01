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

class resource_fact_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Facts and events');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of Fact report module */ WT_I18N::translate('A report of individuals who have a selected fact or event in their record.');
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
		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;
		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $level2_tags;
		require WT_ROOT.'includes/functions/functions_resource.php';

		$table_id = 'ID'.(int)(microtime()*1000000); // create a unique ID

		//-- set list of all configured individual tags (level 1)
		$indifacts				= preg_split("/[, ;:]+/", get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts			= preg_split("/[, ;:]+/", get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$indifacts				=array_merge($indifacts, $uniquefacts);
		$translated_indifacts	= array();
		foreach ($indifacts as $addfact) {
			$translated_indifacts[$addfact] = WT_Gedcom_Tag::getLabel($addfact);
		}
		uasort($translated_indifacts, 'factsort');

		// set list of facts that have level 2 TYPE subtag
		$typefacts = array();
		foreach ($level2_tags as $key=>$value) {
			$key == 'TYPE' ? $typefacts[] = $value : '';
		}
		$typefacts = array_values(array_intersect(call_user_func_array('array_merge', $typefacts), $indifacts));

		//-- variables
		$fact		= WT_Filter::post('fact');
		$year_from	= WT_Filter::post('year_from');
		$year_to	= WT_Filter::post('year_to');
		$place		= WT_Filter::post('place');
		$type		= WT_Filter::post('type');
		$detail		= WT_Filter::post('detail');
		$go			= WT_Filter::post('go');
		$reset		= WT_Filter::post('reset');

		// reset all variables
		if ($reset == 'reset') {
			$fact		= '';
			$year_from	= '';
			$year_to	= '';
			$place		= '';
			$type		= '';
			$detail		= '';
			$go			= 0;
		}

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
			->addInlineJavascript('
				autocomplete();
				jQuery("#' .$table_id. '").dataTable( {
					dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
					' . WT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
					autoWidth: true,
					paging: true,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					jQueryUI: true,
					sorting: [[0,"asc"]],
					displayLength: 20,
					"aoColumns": [
						/* 0-BIRT_DATE */  	{"bVisible": false},
						/* 1-OTHER_DATE */ 	{"bVisible": false},
						/* 2-Name */		{"sClass": "nowrap"},
						/* 3-DoB */			{"iDataSort": 0, "sClass": "nowrap"},
						/* 4-Date */ 		{"iDataSort": 1, "sClass": "nowrap"},
						/* 5-Place */ 		{},
						/* 6-Type */ 		{},
						/* 7-Details */ 	{}
					]
				});
			jQuery("#output").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");

			var form = document.getElementById("resource"),
			fact = form.elements.fact;
			var typefacts = ' . json_encode($typefacts) . ';
			fact.onchange = function () {
			    var form = this.form;
				if (jQuery.inArray(this.value, typefacts) !== -1) {
			        form.elements.type.disabled = false;
					jQuery("#disable_type").css("opacity", "initial");
			    } else {
			        form.elements.type.disabled = true;
					jQuery("#disable_type").css("opacity", "0.4");
			    }
			};
			fact.onchange();
		');
		?>

		<div id="resource-page" class="fact_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="help_text">
				<div class="help_content">
					<h5><?php echo $this->getDescription(); ?></h5>
					<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
					<div class="hidden">
						<?php echo /* I18N help for resource facts and events module */ WT_I18N::translate('The list of available facts and events are those set by the site administrator as "All individual facts" and "Unique individual facts" at Administration > Family trees > <i>your family tree</i> > "Edit options" tab and therefore only GEDCOM first-level records.<br>Date filters must be 4-digit year only. Place, type and detail filters can be any string of characters you expect to find in those data fields. The "Type" field is only avaiable for Custom facts and Custom events.'); ?>
					</div>
				</div>
			</div>
			<!-- options form -->
			<div class="noprint">
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo WT_GEDURL; ?>">
					<div class="chart_options">
						<label for = "fact"><?php echo WT_I18N::translate('Fact or event'); ?></label>
						<select name="fact" id="fact">
							<option value="fact" disabled selected ><?php echo /* I18N: first/default option in a drop-down listbox */ WT_I18N::translate('Select'); ?></option>
							<?php foreach ($translated_indifacts as $key=>$fact_name) {
								if ($key !== 'EVEN' && $key !== 'FACT') {
									echo '<option value="' . $key . '"' . ($key == $fact ? ' selected ' : '') . '>' . $fact_name . '</option>';
								}
							}
							echo '<option value="EVEN"' . ($fact == 'EVEN'? ' selected ' : '') . '>' . WT_I18N::translate('Custom Event') . '</option>';
							echo '<option value="FACT"' . ($fact == 'FACT'? ' selected ' : '') . '>' . WT_I18N::translate('Custom Fact') . '</option>';
							?>
						</select>
					</div>
						<input type="hidden" name="go" value="1">
						<div class="chart_options">
							<label for="year_from"><?php echo WT_I18N::translate('Date'); ?></label>
							<input type="text" id="year_from" name="year_from" placeholder="<?php echo WT_I18N::translate('Year from - 4 digits only'); ?>" value="<?php echo $year_from; ?>" pattern="\d{4}">
							<input type="text" id="year_to" name="year_to" placeholder="<?php echo WT_I18N::translate('Year to - 4 digits only'); ?>" value="<?php echo $year_to; ?>" pattern="\d{4}">
						</div>
						<div class="chart_options">
							<label for="place"><?php echo WT_I18N::translate('Place'); ?></label>
							<input type="text" data-autocomplete-type="PLAC" id="place" name="place" value="<?php echo $place; ?>">
						</div>
						<div class="chart_options" id="disable_type">
							<label for="type"><?php echo WT_I18N::translate('Type'); ?></label>
							<input type="text" data-autocomplete-type="EVEN_TYPE" id="type" name="type" value="<?php echo $type; ?>" <?php ($fact !== 'EVEN' || $fact !== 'FACT' ? '' : 'disabled'); ?>>
						</div>
						<div class="chart_options">
							<label for="detail"><?php echo WT_I18N::translate('Details'); ?></label>
							<input type="text" id="detail" name="detail" value="<?php echo $detail; ?>">
						</div>
		 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
							<i class="fa fa-eye"></i>
							<?php echo WT_I18N::translate('show'); ?>
						</button>
						<button class="btn btn-primary" type="submit" name="reset" value="reset">
							<i class="fa fa-refresh"></i>
							<?php echo WT_I18N::translate('Reset'); ?>
						</button>
				</form>
				<hr style="clear:both;">
			</div>
			<!-- end of form -->
			<?php if ($go == 1) { ?>
				<div class="loading-image">&nbsp;</div>
				<div id="output" style="visibility:hidden;">
					<table id="report_header">
						<tr>
							<th colspan="2"><?php echo WT_I18N::translate('Listing individuals based on these filters'); ?></th>
						</tr>
						<tr>
							<th><?php echo WT_I18N::translate('Fact'); ?></th>
							<td><?php echo WT_Gedcom_Tag::getLabel($fact); ?></td>
						</tr>
						<?php if ($year_from || $year_to) { ?>
							<tr>
								<th><?php echo WT_I18N::translate('Dates'); ?></th>
								<td><?php echo ($year_from ? WT_I18N::translate('from %s', $year_from) : '') . ' ' . ($year_to ? WT_I18N::translate('to %s', $year_to) : ''); ?></td>
							</tr>
						<?php }
						if ($place) { ?>
							<tr>
								<th><?php echo WT_I18N::translate('Place'); ?></th>
								<td><?php echo $place; ?></td>
							</tr>
						<?php }
						if ($type) { ?>
							<tr>
								<th><?php echo WT_I18N::translate('Type'); ?></th>
								<td><?php echo $type; ?></td>
							</tr>
						<?php }
						if ($detail) { ?>
							<tr>
								<th><?php echo WT_I18N::translate('Details'); ?></th>
								<td><?php echo $detail; ?></td>
							</tr>
							<?php } ?>
					</table>
					<table id="<?php echo $table_id; ?>"style="width:100%;">
						<thead>
							<tr>
								<th>BIRT_DATE</th><!-- hidden cell -->
								<th>OTHER_DATE</th><!-- hidden cell -->
								<th><?php echo WT_I18N::translate('Name'); ?></th>
								<th><?php echo WT_Gedcom_Tag::getLabel('BIRT:DATE'); ?></th>
								<th><?php echo WT_I18N::translate('Date'); ?></th>
								<th><?php echo WT_I18N::translate('Place'); ?></th>
								<th><?php echo WT_I18N::translate('Type'); ?></th>
								<th><?php echo WT_I18N::translate('Details'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$rows = resource_findfact($fact);
							foreach ($rows as $row) {
								$person = WT_Person::getInstance($row->xref);
								if ($person->canDisplayDetails()) { ?>
									<?php $indifacts = $person->getIndiFacts();
									foreach ($indifacts as $item) {
										if ($item->getTag() == $fact) {
											$filtered_facts = filter_facts ($item, $person, $year_from, $year_to, $place, $detail, $type);
											if ($filtered_facts) {
												$factrec = $item->getGedcomRecord();
												if (preg_match('/2 DATE (.+)/', $factrec, $match)) {
													$date = new WT_Date($match[1]);
												} else {
													$date = new WT_Date('');
												} ?>
												<tr>
													<td><!-- hidden cell 1 - birth date -->
														<?php echo $person->getBirthDate()->JD(); ?>
													</td>
													<td><!-- hidden cell 2 - other date -->
														<?php echo $date->JD(); ?>
													</td>
													<td>
														<a href="<?php echo $person->getHtmlUrl(); ?>" target="_blank"><?php echo $person->getFullName(); ?></a>
													</td>
													<td>
														<?php echo $person->getBirthDate()->Display(); ?>
													</td>
													<td>
														<?php echo format_fact_date($item, $person, false, true, false); ?>
													</td>
													<td>
														<?php echo format_fact_place($item, true); ?>
													</td>
													<td class="field">
														<?php
															$ct = preg_match("/2 TYPE (.*)/", $item->getGedcomRecord(), $ematch);
															if ($ct>0) {
																$factname = trim($ematch[1]);
																echo $factname;
															} else {
																echo '';
															}
														?>
													</td>
													<td class="field">
														<?php echo print_resourcefactDetails($item, $person); ?>
													</td>
												</tr>
											<?php }
										}
									}
								}
							} ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
	<?php }
}
