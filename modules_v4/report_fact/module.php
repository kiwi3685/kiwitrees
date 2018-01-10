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

class report_fact_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ KT_I18N::translate('Facts and events');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of Fact report module */ KT_I18N::translate('A report of individuals who have a selected fact or event in their record.');
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
		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;
		return $menus;
	}

	/**
	 * Generate a SURN,GIVN and GIVN,SURN sortable name for an individual.
	 * This allows table data to sort by surname or given names.
	 *
	 * Use AAAA as a separator (instead of ","), as Javascript localeCompare()
	 * ignores punctuation and "ANN,ROACH" would sort after "ANNE,ROACH",
	 * instead of before it.
	 *
	 * @param KT_Person $person
	 *
	 * @return string[]
	 */
	public function sortableNames(KT_Person $person) {
		$names   = $person->getAllNames();
		$primary = $person->getPrimaryName();

		list($surn, $givn) = explode(',', $names[$primary]['sort']);

		$givn = str_replace('@P.N.', 'AAAA', $givn);
		$surn = str_replace('@N.N.', 'AAAA', $surn);

		return [
			$surn . 'AAAA' . $givn,
			$givn . 'AAAA' . $surn,
		];
	}

	// Implement class KT_Module_Report
	public function show() {
		global $controller, $level2_tags;
		require KT_ROOT . 'includes/functions/functions_resource.php';

		$table_id = 'ID' . (int)(microtime(true)*1000000); // create a unique ID

		//-- set list of all configured individual tags (level 1)
		$indifacts				= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts			= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$indifacts				= array_merge($indifacts, $uniquefacts);
		$translated_indifacts	= array();
		foreach ($indifacts as $addfact) {
			$translated_indifacts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
		}
		uasort($translated_indifacts, 'factsort');

		// set list of facts that have level 2 TYPE subtag
		$typefacts = array();
		foreach ($level2_tags as $key=>$value) {
			$key == 'TYPE' ? $typefacts[] = $value : '';
		}
		$typefacts = array_values(array_intersect(call_user_func_array('array_merge', $typefacts), $indifacts));

		//-- variables
		$fact		= KT_Filter::post('fact');
		$year_from	= KT_Filter::post('year_from');
		$year_to	= KT_Filter::post('year_to');
		$place		= KT_Filter::post('place');
		$type		= KT_Filter::post('type');
		$detail		= KT_Filter::post('detail');
		$go			= KT_Filter::post('go');
		$reset		= KT_Filter::post('reset');

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

		$controller = new KT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();

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

		<div id="page" class="fact_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<div class="help_text">
					<div class="help_content">
						<h5><?php echo $this->getDescription(); ?></h5>
						<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
						<div class="hidden">
							<?php echo /* I18N: help for report facts and events module */ KT_I18N::translate('The list of available facts and events are those set by the site administrator as "All individual facts" and "Unique individual facts" at Administration > Family trees > <i>your family tree</i> > "Edit options" tab and therefore only GEDCOM first-level records.<br>Date filters must be 4-digit year only. Place, type and detail filters can be any string of characters you expect to find in those data fields. The "Type" field is only avaiable for Custom facts and Custom events.'); ?>
						</div>
					</div>
				</div>
				<!-- options form -->
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "fact"><?php echo KT_I18N::translate('Fact or event'); ?></label>
						<select name="fact" id="fact">
							<option value="fact" disabled selected ><?php echo /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'); ?></option>
							<?php foreach ($translated_indifacts as $key=>$fact_name) {
								if ($key !== 'EVEN' && $key !== 'FACT') {
									echo '<option value="' . $key . '"' . ($key == $fact ? ' selected ' : '') . '>' . $fact_name . '</option>';
								}
							}
							echo '<option value="EVEN"' . ($fact == 'EVEN'? ' selected ' : '') . '>' . KT_I18N::translate('Custom event') . '</option>';
							echo '<option value="FACT"' . ($fact == 'FACT'? ' selected ' : '') . '>' . KT_I18N::translate('Custom Fact') . '</option>';
							?>
						</select>
					</div>
						<div class="chart_options">
							<label for="year_from"><?php echo KT_I18N::translate('Date'); ?></label>
							<input type="text" id="year_from" name="year_from" placeholder="<?php echo KT_I18N::translate('Year from - 4 digits only'); ?>" value="<?php echo $year_from; ?>" pattern="\d{4}">
							<input type="text" id="year_to" name="year_to" placeholder="<?php echo KT_I18N::translate('Year to - 4 digits only'); ?>" value="<?php echo $year_to; ?>" pattern="\d{4}">
						</div>
						<div class="chart_options">
							<label for="place"><?php echo KT_I18N::translate('Place'); ?></label>
							<input type="text" data-autocomplete-type="PLAC" id="place" name="place" value="<?php echo $place; ?>">
						</div>
						<div class="chart_options" id="disable_type">
							<label for="type"><?php echo KT_I18N::translate('Type'); ?></label>
							<input type="text" data-autocomplete-type="EVEN_TYPE" id="type" name="type" value="<?php echo $type; ?>" <?php ($fact !== 'EVEN' || $fact !== 'FACT' ? '' : 'disabled'); ?>>
						</div>
						<div class="chart_options">
							<label for="detail"><?php echo KT_I18N::translate('Details'); ?></label>
							<input type="text" id="detail" name="detail" value="<?php echo $detail; ?>">
						</div>
		 				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>">
							<i class="fa fa-eye"></i>
							<?php echo KT_I18N::translate('show'); ?>
						</button>
						<button class="btn btn-primary" type="submit" name="reset" value="reset">
							<i class="fa fa-refresh"></i>
							<?php echo KT_I18N::translate('Reset'); ?>
						</button>
				</form>
				<hr style="clear:both;">
			</div>
			<!-- end of form -->
			<?php if ($go == 1) {
				// prepare data.
				$rows = report_findfact($fact);
				$data = array();
				$x = 0;
				$count_type = false;
				$count_details = false;
				foreach ($rows as $row) {
					$person = KT_Person::getInstance($row->xref);
					if ($person->canDisplayDetails()) { ?>
						<?php $indifacts = $person->getIndiFacts();
						foreach ($indifacts as $item) {
							if ($item->getTag() == $fact) {
								$filtered_facts = filter_facts($item, $person, $year_from, $year_to, $place, $detail, $type);
								if ($filtered_facts) {
									$factrec = $item->getGedcomRecord();
									if (preg_match('/2 DATE (.+)/', $factrec, $match)) {
										$date = new KT_Date($match[1]);
									} else {
										$date = new KT_Date('');
									}
									// Extract Given names and Surnames for sorting
									list($surn_givn, $givn_surn) = $this->sortableNames($person);
									$data[$x]['GIVN'] = KT_Filter::escapeHtml($givn_surn);
									$data[$x]['SURN'] = KT_Filter::escapeHtml($surn_givn);
									$data[$x]['NAME'] = '';
									foreach ($person->getAllNames() as $num => $name) {
										if ($name['type'] == 'NAME') {
											$title='';
										} else {
											$title='title="'.strip_tags(KT_Gedcom_Tag::getLabel($name['type'], $person)).'"';
										}
										if ($num == $person->getPrimaryName()) {
											$class =' class="name2"';
											$sex_image = $person->getSexImage();
											list($surn, $givn) = explode(',', $name['sort']);
										} else {
											$class = '';
											$sex_image = '';
										}
										$data[$x]['NAME'] .= '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. $name['full'] . '</a>'. $sex_image. '<br>';
									}
									$data[$x]['B_DATE'] = $person->getBirthDate()->JD();
									$data[$x]['O_DATE'] = $date->JD();
									$data[$x]['BIRTH'] = $person->getBirthDate()->Display();
									$data[$x]['FACT_DATE'] = format_fact_date($item, $person, false, true, false);
									$data[$x]['FACT_PLACE'] = format_fact_place($item, true);
									$ct = preg_match("/2 TYPE (.*)/", $item->getGedcomRecord(), $ematch);
									if ($ct > 0) {
										$factname = trim($ematch[1]);
										$data[$x]['TYPE'] = $factname;
										$count_type = true;
									} else {
										$data[$x]['TYPE'] = '';
									}
									if (print_resourcefactDetails($item, $person)){
										$data[$x]['DETAILS'] = print_resourcefactDetails($item, $person);
										$count_details = true;
									} else {
										$data[$x]['DETAILS'] = '';
									}
								}
							}
						}
					}
					$x++;
				}

				$controller
					->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
					->addExternalJavascript(KT_JQUERY_DT_HTML5)
					->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
					->addInlineJavascript('
						jQuery("#' .$table_id. '").dataTable( {
							dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
							' . KT_I18N::datatablesI18N() . ',
							buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
							autoWidth: true,
							paging: true,
							pagingType: "full_numbers",
							lengthChange: true,
							filter: true,
							info: true,
							jQueryUI: true,
							sorting: [[1,"asc"], [0,"asc"]],
							displayLength: 20,
							"aoColumns": [
								/*  0-GIVN */  		{ type: "unicode", visible: false },
								/*  1-SURN */ 		{ type: "unicode", visible: false },
								/*  2-BIRT_DATE */ 	{ visible: false },
								/*  3-OTHER_DATE */ { visible: false },
								/*  4-Given name */	{ dataSort: 0, class: "nowrap" },
								/*  5-Surname */	{ dataSort: 1, class: "nowrap" },
								/*  6-DoB */		{ dataSort: 2, class: "nowrap" },
								/*  7-Date */ 		{ dataSort: 3, class: "nowrap" },
								/*  8-Place */ 		null,
								/*  9-Type */ 		' . ($count_type ? 'null' : '{ visible: false }') . ',
								/* 10-Details */ 	' . ($count_details ? 'null' : '{ visible: false }') . ',
							]
						});
					jQuery("#output").css("visibility", "visible");
					jQuery(".loading-image").css("display", "none");
				');

				($fact) ? $filter1 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Fact or event: <span>%1s</span>', KT_Gedcom_Tag::getLabel($fact)) . '</p>' : $filter1 = '';
				($year_from && !$year_to) ? $filter2 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Date from: <span>%1s</span>', $year_from) . '</p>' : $filter2 = '';
				(!$year_from && $year_to) ? $filter3 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Date to <span>%1s</span>', $year_to) . '</p>' : $filter3 = '';
				($year_from && $year_to) ? $filter4 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Dates between <span>%1s</span> and <span>%2s</span> ', $year_from, $year_to) . '</p>' : $filter4 = '';
				($place) ? $filter5 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Place: <span>%1s</span>', $place) . '</p>' : $filter5 = '';
				($type) ? $filter6 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Type: <span>%1s</span>', $type) . '</p>' : $filter6 = '';
				($detail) ? $filter7 = '<p>' . /* I18N: A filter on the facts and events report page */ KT_I18N::translate('Details: <span>%1s</span>', $detail) . '</p>' : $filter7 = '';

				$filter_list = $filter1 . $filter2 . $filter3 . $filter4 . $filter5 . $filter6 . $filter7;

				 ?>
				 <div id="report_header">
 					<h4><?php echo KT_I18N::translate('Listing individuals based on these filters'); ?></h4>
 					<p><?php echo $filter_list; ?></p>
 				</div>
				<div class="loading-image">&nbsp;</div>
				<div id="output" style="visibility:hidden;">
					<table id="<?php echo $table_id; ?>"style="width:100%;">
						<thead>
							<tr>
								<th>BIRT_DATE</th><!-- hidden cell for sorting -->
								<th>OTHER_DATE</th><!-- hidden cell for sorting -->
								<th>GIVN</th><!-- hidden cell for sorting -->
								<th>SURN</th><!-- hidden cell for sorting -->
								<th><?php echo KT_Gedcom_Tag::getLabel('GIVN'); ?></th>
								<th><?php echo KT_Gedcom_Tag::getLabel('SURN'); ?></th>
								<th><?php echo KT_Gedcom_Tag::getLabel('BIRT:DATE'); ?></th>
								<th><?php echo KT_I18N::translate('Date'); ?></th>
								<th><?php echo KT_I18N::translate('Place'); ?></th>
								<th><?php echo KT_I18N::translate('Type'); ?></th>
								<th><?php echo KT_I18N::translate('Details'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($data as $row) { ?>
								<tr>
									<td hidden><?php echo $row['GIVN']; ?></td><!-- hidden cell - birth date -->
									<td hidden><?php echo $row['SURN']; ?></td><!-- hidden cell - other date -->
									<td hidden><?php echo $row['B_DATE']; ?></td><!-- hidden cell - birth date -->
									<td hidden><?php echo $row['O_DATE']; ?></td><!-- hidden cell- other date -->
									<td colspan="2"><?php echo $row['NAME']; ?></td>
									<td hidden></td>
									<td><?php echo $row['BIRTH']; ?></td>
									<td><?php echo $row['FACT_DATE']; ?></td>
									<td><?php echo $row['FACT_PLACE']; ?></td>
									<td class="field"><?php echo $row['TYPE']; ?></td>
									<td class="field"><?php echo $row['DETAILS']; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
	<?php }

}
