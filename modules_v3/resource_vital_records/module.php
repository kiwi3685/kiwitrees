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

class resource_vital_records_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ WT_I18N::translate('Vital records');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Vital records” module */ WT_I18N::translate('A report of individuals\' births, marriages and deaths for a selected name, place or date range.');
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
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('
				autocomplete();
				// check that at least one filter has been used
				function checkform() {
				    if (
						document.resource.name.value == "" &&
						document.resource.place.value == "" &&
						document.resource.b_from.value == "" &&
						document.resource.b_to.value == "" &&
						document.resource.d_from.value == "" &&
						document.resource.d_to.value == ""
					) {
						if (confirm("' . WT_I18N::translate('You have not set any filters. Kiwitrees will try to list records for every individual in your tree. Is this what you want to do?') . '")){
						    document.resource.submit(); // OK
						} else {
						    return false; // Cancel
						}
				    }
				}
			');

		//Configuration settings ===== //
	    $action	= WT_Filter::post('action');
		$reset	= WT_Filter::post('reset');
		$name	= WT_Filter::post('name', '');
	    $b_from	= WT_Filter::post('b_from', '');
	    $b_to	= WT_Filter::post('b_to', '');
		$d_from	= WT_Filter::post('d_from', '');
	    $d_to	= WT_Filter::post('d_to', '');
		$place	= WT_Filter::post('place', '');

		// dates for calculations
		$b_fromJD = (new WT_Date($b_from))->minJD();
		$b_toJD = (new WT_Date($b_to))->minJD();
		$d_fromJD = (new WT_Date($d_from))->minJD();
		$d_toJD = (new WT_Date($d_to))->minJD();

		// reset all variables
	    if ($reset == 'reset') {
			$action	= '';
			$name	= '';
		    $b_from	= '';
		    $b_to	= '';
			$d_from	= '';
		    $d_to	= '';
			$place	= '';
	    }

		?>
		<div id="resource-page" class="vital_records">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="action" value="go">
					<div class="chart_options">
						<label for = "NAME"><?php echo WT_Gedcom_Tag::getLabel('NAME'); ?></label>
						<input type="text" name="name" id="NAME" value="<?php echo WT_Filter::escapeHtml($name); ?>" dir="auto">
					</div>
					<div class="chart_options">
						<label for = "PLAC"><?php echo WT_Gedcom_Tag::getLabel('PLAC'); ?></label>
						<input data-autocomplete-type="PLAC" type="text" name="place" id="PLAC" value="<?php echo WT_Filter::escapeHtml($place); ?>" dir="auto">
					</div>
					<div class="chart_options">
		              <label for = "DATE1"><?php echo WT_I18N::translate('Birth date - from'); ?></label>
		              <input type="text" name="b_from" id="DATE1" value="<?php echo $b_from; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE1"); ?>
		            </div>
		            <div class="chart_options">
		              <label for = "DATE2"><?php echo WT_I18N::translate('Birth date - to'); ?></label>
		              <input type="text" name="b_to" id="DATE2" value="<?php echo $b_to; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE2"); ?>
		            </div>
					<div class="chart_options">
		              <label for = "DATE3"><?php echo WT_I18N::translate('Death date - from'); ?></label>
		              <input type="text" name="d_from" id="DATE3" value="<?php echo $d_from; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE3"); ?>
		            </div>
		            <div class="chart_options">
		              <label for = "DATE4"><?php echo WT_I18N::translate('Death date - to'); ?></label>
		              <input type="text" name="d_to" id="DATE4" value="<?php echo $d_to; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE4"); ?>
		            </div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>" onclick="return checkform()">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
					<button class="btn btn-primary" type="submit" name="reset" value="reset">
		                <i class="fa fa-refresh"></i>
						<?php echo WT_I18N::translate('reset'); ?>
		            </button>
				</form>
			</div>
			<hr class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php if ($action == 'go') {
				$controller
					->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
					->addExternalJavascript(WT_JQUERY_DT_HTML5)
					->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
					->addInlineJavascript('
						jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
						jQuery("#vital_records").dataTable({
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
							"aoColumns": [
								/* 0-name */			null,
								/* 1-birth date */		{ dataSort: 2 },
								/* 2-BIRT:DATE */		{ visible: false },
								/* 3-marr details */	null,
								/* 4-death date */		{ dataSort: 5 },
								/* 5-DEAT:DATE */		{ visible: false },
							]
						});
						jQuery("#vital_records").css("visibility", "visible");
						jQuery(".loading-image").css("display", "none");
					');

				($name) ? $filter1 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Names containing <span>%1s</span>', $name) . '</p>' : $filter1 = '';
				($place) ? $filter2 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Place names containing <span>%1s</span>', $place) . '</p>' : $filter2 = '';
				($b_from && !$b_to) ? $filter3 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Births from <span>%1s</span>', $b_from) . '</p>' : $filter3 = '';
				(!$b_from && $b_to) ? $filter4 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Births to <span>%1s</span>', $b_to) . '</p>' : $filter4 = '';
				($b_from && $b_to) ? $filter5 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Births between <span>%1s</span> and <span>%2s</span> ', $b_from, $b_to) . '</p>' : $filter5 = '';
				($d_from && !$d_to) ? $filter6 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Deaths from <span>%1s</span>', $d_from) . '</p>' : $filter6 = '';
				(!$d_from && $d_to) ? $filter7 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Deaths to <span>%1s</span>', $d_to) . '</p>' : $filter7 = '';
				($d_from && $d_to) ? $filter8 = '<p>' . /* I18N: A filter on the Vital records report page */ WT_I18N::translate('Deaths between <span>%1s</span> and <span>%2s</span> ', $d_from, $d_to) . '</p>' : $filter8 = '';

				$filter_list = $filter1 . $filter2 . $filter3 . $filter4 . $filter5 . $filter6 . $filter7 . $filter8;

				$list = resource_vital_records($name, $place, $b_fromJD, $b_toJD, $d_fromJD, $d_toJD, WT_GED_ID);

				// output display
				?>
				<div id="report_header">
					<h4><?php echo WT_I18N::translate('Listing individuals based on these filters'); ?></h4>
					<p><?php echo $filter_list; ?></p>
				</div>
				<div class="loading-image">&nbsp;</div>
				<table id="vital_records" class="width100" style="visibility:hidden;">
					<thead>
						<tr>
							<th><?php echo WT_I18N::translate('Name'); ?></th>
							<th><?php echo WT_I18N::translate('Birth'); ?></th>
							<th><?php //SORT_BIRT ?></th>
							<th><?php echo WT_I18N::translate('Marriage'); ?></th>
							<th><?php echo WT_I18N::translate('Death'); ?></th>
							<th><?php //SORT_DEAT ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($list as $person) {
							if ($person && $person->canDisplayDetails()) {
								$person->add_family_facts();
								$indifacts = $person->getIndiFacts();
								?>
								<tr>
									<td>
										<div>
											<p class="first">
												<a href="<?php echo $person->getHtmlUrl(); ?>"><?php echo $person->getFullName(); ?></a>
											</p>
											<?php if ($person->getPrimaryChildFamily() && $person->getPrimaryChildFamily()->getHusband()) { ?>
												<p>
													<?php echo WT_I18N::translate('Father') . ': ' . $person->getPrimaryChildFamily()->getHusband()->getLifespanName(); ?>
												</p>
											<?php }
											if ($person->getPrimaryChildFamily() && $person->getPrimaryChildFamily()->getWife()) { ?>
												<p>
													<?php echo WT_I18N::translate('Mother') . ': ' . $person->getPrimaryChildFamily()->getWife()->getLifespanName(); ?>
												</p>
											<?php } ?>
										</div>
									</td>
									<td>
										<div>
											<?php foreach ($indifacts as $fact) {
												if ($fact->getTag() == 'BIRT') { ?>
													<p class="first">
														<?php echo ($person->getBirthDate() ? WT_I18N::translate('Date') . ': ' . format_fact_date($fact, $person, true, true, false) . '<br>' : '') .
														($person->getBirthPlace() ? WT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
													</p>
													<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
													for ($j=0; $j<$ct; $j++) {
														$sid = trim($match[$j][2], '@');
														$source = WT_Source::getInstance($sid);
														if ($source->canDisplayDetails()) {
															echo '<p>' . WT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
														}
													}
												}
											}
											?>
										</div>
									</td>
									<td><?php echo $person->getBirthDate()->JD(); ?></td><!-- used for sorting only -->
									<td>
										<div>
											<?php foreach ($indifacts as $fact) {
												if ($fact->getParentObject() instanceof WT_Family && ($fact->getTag() == 'MARR' || $fact->getTag() == '_NMR')) {
													foreach ($fact->getParentObject() as $family_fact) {
														$sex = $person->getSex();
														switch ($sex) {
															case 'M':
																$spouse = $fact->getParentObject()->getWife();
																break;
															case 'F':
																$spouse = $fact->getParentObject()->getHusband();
																break;
															default:
																$spouse = '';
																break;
														} ?>
														<?php
														if ($spouse) { ?>
															<div>
																<p class="first">
																	<?php echo WT_I18N::translate('Spouse'); ?>: <a href="<?php echo $spouse->getHtmlUrl(); ?>"><?php echo $spouse->getFullName(); ?></a>
																</p>
																<p class="first">
																	<?php echo ($fact->getParentObject()->getMarriageDate() ? WT_I18N::translate('Date') . ': ' . format_fact_date($fact, $spouse, true, true, false) . '<br>' : '') .
																	($fact->getParentObject()->getMarriagePlace() ? WT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
																</p>
																<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
																for ($j=0; $j<$ct; $j++) {
																	$sid = trim($match[$j][2], '@');
																	$source = WT_Source::getInstance($sid);
																	if ($source->canDisplayDetails()) {
																		echo '<p>' . WT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
																	}
																} ?>
															</div>
														<?php }
													}
												}
											} ?>
										</div>
									</td>
									<td>
										<div>
											<?php foreach ($indifacts as $fact) {
												if ($fact->getTag() == 'DEAT') { ?>
													<p class="first">
														<?php echo ($person->getDeathDate() ? WT_I18N::translate('Date') . ': ' . format_fact_date($fact, $person, true, true, false) . '<br>' : '') .
														($person->getDeathPlace() ? WT_I18N::translate('Place') . ': ' . format_fact_place($fact, true, true, true) : ''); ?>
													</p>
													<?php $ct = preg_match_all("/(2 SOUR (.+))/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
													for ($j=0; $j<$ct; $j++) {
														$sid = trim($match[$j][2], '@');
														$source = WT_Source::getInstance($sid);
														if ($source->canDisplayDetails()) {
															echo '<p>' . WT_I18N::translate('Source') . ': ' . $source->getFullName() . '</p>';
														}
													}
												}
											}
											?>
										</div>
									</td>
									<td><?php echo $person->getDeathDate()->JD(); ?></td><!-- used for sorting only -->
								</tr>
							<?php }
						}
						?>
			 		</tbody>
				</table>
			<?php }
		}
}
