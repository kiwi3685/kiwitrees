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

// print a table of individuals
function format_indi_table($datalist, $option='') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $MAX_ALIVE_AGE, $controller;

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'indiTable';
	}

	$SHOW_EST_LIST_DATES = get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');
	if ($option == 'MARR_PLAC') return;
	$html = '';
	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
			jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
			jQuery("#' . $table_id . '").dataTable( {
				dom: \'<"H"<"filtersH_' . $table_id . '">T<"dt-clear">pBf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id.'">>\',
				' . WT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csv", exportOptions: {columns: [0,6,9,12,15,17] }}],
				jQueryUI: true,
				autoWidth: false,
				processing: true,
				retrieve: true,
				columns: [
					/*  0 givn      */ { dataSort: 2 },
					/*  1 surn      */ { dataSort: 3 },
					/*  2 GIVN,SURN */ { type: "unicode", visible: false },
					/*  3 SURN,GIVN */ { type: "unicode", visible: false },
					/*  4 sosa      */ { dataSort: 5, class: "center", visible: ' . ($option === 'sosa' ? 'true' : 'false') . ' },
					/*  5 SOSA      */ { type: "num", visible: false },
					/*  6 birt date */ { dataSort: 7 },
					/*  7 BIRT:DATE */ { visible: false },
					/*  8 anniv     */ { dataSort: 7, class: "center" },
					/*  9 birt plac */ { type: "unicode" },
					/* 10 children  */ { dataSort: 11, class: "center" },
					/* 11 children  */ { type: "num", visible: false },
					/* 12 deat date */ { dataSort: 13 },
					/* 13 DEAT:DATE */ { visible: false },
					/* 14 anniv     */ { dataSort: 13, class: "center" },
					/* 15 age       */ { dataSort: 16, class: "center" },
					/* 16 AGE       */ { type: "num", visible: false },
					/* 17 deat plac */ { type: "unicode" },
					/* 18 CHAN      */ { dataSort: 19, visible: ' . ($SHOW_LAST_CHANGE ? 'true' : 'false') . ' },
					/* 19 CHAN_sort */ { visible: false },
					/* 20 SEX       */ { visible: false },
					/* 21 BIRT      */ { visible: false },
					/* 22 DEAT      */ { visible: false },
					/* 23 TREE      */ { visible: false }
				],
				sorting: [[' . ($option === 'sosa' ? '4, "asc"' : '1, "asc"') . ']],
				displayLength: 20,
				pagingType: "full_numbers",
				stateSave: true,
				stateDuration: -1
			});

			jQuery("#' . $table_id . '")
			/* Hide/show parents */
			.on("click", ".btn-toggle-parents", function() {
				jQuery(this).toggleClass("ui-state-active");
				jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
			})

			/* Hide/show statistics */
			.on("click", ".btn-toggle-statistics", function() {
				jQuery(this).toggleClass("ui-state-active");
				jQuery("#indi_list_table-charts_' . $table_id . '").slideToggle();
			})

			/* Filter buttons in table header */
			.on("click", "button[data-filter-column]", function() {
				var btn = jQuery(this);
				// De-activate the other buttons in this button group
				btn.siblings().removeClass("ui-state-active");
				// Apply (or clear) this filter
				var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
				if (btn.hasClass("ui-state-active")) {
					btn.removeClass("ui-state-active");
					col.search("").draw();
				} else {
					btn.addClass("ui-state-active");
					col.search(btn.data("filter-value")).draw();
				}
  			});

			jQuery(".indi-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	$stats = new WT_Stats($GEDCOM);

	// Bad data can cause "longest life" to be huge, blowing memory limits
	$max_age = min($MAX_ALIVE_AGE, $stats->LongestLifeAge())+1;

	// Inititialise chart data
	for ($age=0; $age<=$max_age; $age++) {
		$deat_by_age[$age] = '';
	}
	for ($year=1550; $year<2030; $year+=10) {
		$birt_by_decade[$year] = '';
		$deat_by_decade[$year] = '';
	}

	$html = '
		<div class="loading-image">&nbsp;</div>
		<div class="indi-list">
			<table id="'. $table_id. '">
				<thead>
					<tr>
						<th colspan="24">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button
										class="ui-state-default"
										data-filter-column="20"
										data-filter-value="M"
										title="' . WT_I18N::translate('Show only males.') . '"
										type="button"
									>
									 	' . WT_Person::sexImage('M', 'large') . '
									</button>
									<button
										class="ui-state-default"
										data-filter-column="20"
										data-filter-value="F"
										title="' . WT_I18N::translate('Show only females.') . '"
										type="button"
									>
										' . WT_Person::sexImage('F', 'large') . '
									</button>
									<button
										class="ui-state-default"
										data-filter-column="20"
										data-filter-value="U"
										title="' . WT_I18N::translate('Show only individuals for whom the gender is not known.') . '"
										type="button"
									>
										' . WT_Person::sexImage('U', 'large') . '
									</button>
								</div>
								<div class="btn-group">
									<button
										class="ui-state-default"
										data-filter-column="22"
										data-filter-value="N"
										title="' . WT_I18N::translate('Show individuals who are alive or couples where both partners are alive.').'"
										type="button"
									>
										' . WT_I18N::translate('Alive') . '
									</button>
									<button
										class="ui-state-default"
										data-filter-column="22"
										data-filter-value="Y"
										title="' . WT_I18N::translate('Show individuals who are dead or couples where both partners are deceased.').'"
										type="button"
									>
										' . WT_I18N::translate('Dead') . '
									</button>
									<button
										class="ui-state-default"
										data-filter-column="22"
										data-filter-value="YES"
										title="' . WT_I18N::translate('Show individuals who died more than 100 years ago.') . '"
										type="button"
									>
										' . WT_Gedcom_Tag::getLabel('DEAT') . '&gt;100
									</button>
									<button
										class="ui-state-default"
										data-filter-column="22"
										data-filter-value="Y100"
										title="' . WT_I18N::translate('Show individuals who died within the last 100 years.') . '"
										type="button"
									>
										' . WT_Gedcom_Tag::getLabel('DEAT') . '&lt;=100
									</button>
								</div>
								<div class="btn-group">
									<button
										class="ui-state-default"
										data-filter-column="21"
										data-filter-value="YES"
										title="' . WT_I18N::translate('Show individuals born more than 100 years ago.') . '"
										type="button"
									>
										' . WT_Gedcom_Tag::getLabel('BIRT') . '&gt;100
									</button>
									<button
										class="ui-state-default"
										data-filter-column="21"
										data-filter-value="Y100"
										title="' . WT_I18N::translate('Show individuals born within the last 100 years.') . '"
										type="button"
									>
										'.WT_Gedcom_Tag::getLabel('BIRT') . '&lt;=100
									</button>
								</div>
								<div class="btn-group">
									<button
										class="ui-state-default"
										data-filter-column="23"
										data-filter-value="R"
										title="' . WT_I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.') . '"
										type="button"
									>
										' . WT_I18N::translate('Roots') . '
									</button>
									<button
										class="ui-state-default"
										data-filter-column="23"
										data-filter-value="L"
										title="' . WT_I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.') . '"
										type="button"
									>
										' . WT_I18N::translate('Leaves') . '
									</button>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th>' . WT_Gedcom_Tag::getLabel('GIVN') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('SURN') . '</th>
						<th>GIVN</th>
						<th>SURN</th>
						<th>' . /* I18N: Abbreviation for “Sosa-Stradonitz number”.  This is an individual’s surname, so may need transliterating into non-latin alphabets. */ WT_I18N::translate('Sosa') . '</th>
						<th>SOSA</th>
						<th>' . WT_Gedcom_Tag::getLabel('BIRT') . '</th>
						<th>SORT_BIRT</th>
						<th><i class="icon-reminder" title="' . WT_I18N::translate('Anniversary') . '"></i></th>
						<th>' . WT_Gedcom_Tag::getLabel('PLAC') . '</th>
						<th><i class="icon-children" title="' . WT_I18N::translate('Children') . '"></i></th>
						<th>NCHI</th>
						<th>' . WT_Gedcom_Tag::getLabel('DEAT') . '</th>
						<th>SORT_DEAT</th>
						<th><i class="icon-reminder" title="' . WT_I18N::translate('Anniversary') . '"></i></th>
						<th>' . WT_Gedcom_Tag::getLabel('AGE') . '</th>
						<th>AGE</th>
						<th>' . WT_Gedcom_Tag::getLabel('PLAC') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
						<th>CHAN</th>
						<th>SEX</th>
						<th>BIRT</th>
						<th>DEAT</th>
						<th>TREE</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="24">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button type="button" class="ui-state-default btn-toggle-parents">
										' . WT_I18N::translate('Show parents') . '
									</button>
									<button type="button" class="ui-state-default btn-toggle-statistics">
										' . WT_I18N::translate('Show statistics charts') . '
									</button>
								</div>
							</div>
						</th>
					</tr>
				</tfoot>
				<tbody>';

	$d100y			= new WT_Date(date('Y')-100);  // 100 years ago
	$unique_indis	= array(); // Don't double-count indis with multiple names.
	foreach ($datalist as $key=>$value) {
		if (is_object($value)) { // Array of objects
			$person=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$person = WT_Person::getInstance($value);
		} else { // Array of search results
			$gid = $key;
			if (isset($value['gid'])) $gid = $value['gid']; // from indilist
			if (isset($value[4])) $gid = $value[4]; // from indilist ALL
			$person = WT_Person::getInstance($gid);
		}
		if (is_null($person)) continue;
		if ($person->getType() !== 'INDI') continue;
		if (!$person->canDisplayName()) {
			continue;
		}
		//-- place filtering
		if ($option == 'BIRT_PLAC' && strstr($person->getBirthPlace(), $filter) === false) continue;
		if ($option == 'DEAT_PLAC' && strstr($person->getDeathPlace(), $filter) === false) continue;
		$html .= '<tr>';
		//-- Indi name(s)
		$html .= '<td colspan="2">';
		foreach ($person->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $person)).'"';
			}
			if ($num == $person->getPrimaryName()) {
				$class =' class="name2"';
				$sex_image = $person->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class = '';
				$sex_image = '';
			}
			$html .= '<a '. $title. ' href="'. $person->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image. '<br>';
		}
		// Indi parents
		$html .= $person->getPrimaryParentsNames('parents details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td hidden></td>';
		//-- GIVN/SURN
		// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		//-- SOSA
		if ($option === 'sosa') {
			$html .= '<td><a href="relationship.php?pid1='. $datalist[1]. '&amp;pid2='. $person->getXref(). '" title="'. WT_I18N::translate('Relationships'). '">'. WT_I18N::number($key). '</a></td><td>'. $key. '</td>';
		} else {
			$html .= '<td>&nbsp;</td><td>0</td>';
		}
		//-- Birth date
		$html .= '<td>';
		if ($birth_dates=$person->getAllBirthDates()) {
			foreach ($birth_dates as $num=>$birth_date) {
				if ($num) {
					$html .= '<br>';
				}
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			}
			if ($birth_dates[0]->gregorianYear()>=1550 && $birth_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$birt_by_decade[(int)($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$birth_date = $person->getEstimatedBirthDate();
			if ($SHOW_EST_LIST_DATES) {
				$html .= $birth_date->Display(!$SEARCH_SPIDER);
			} else {
				$html .= '&nbsp;';
			}
			$birth_dates[0] = new WT_Date('');
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>'. $birth_date->JD(). '</td>';
		//-- Birth anniversary
		$html .= '<td>'.WT_Date::getAge($birth_dates[0], null, 2).'</td>';
		//-- Birth place
		$html .= '<td>';
		foreach ($person->getAllBirthPlaces() as $n=>$birth_place) {
			$tmp = new WT_Place($birth_place, WT_GED_ID);
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= $tmp->getShortName();
			} else {
				$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
				$html .= highlight_search_hits($tmp->getShortName()). '</a>';
			}
		}
		$html .= '</td>';
		//-- Number of children
		$nchi = $person->getNumberOfChildren();
		$html .= '<td>'. WT_I18N::number($nchi). '</td><td>'. $nchi. '</td>';
		//-- Death date
		$html .= '<td>';
		if ($death_dates = $person->getAllDeathDates()) {
			foreach ($death_dates as $num=>$death_date) {
				if ($num) {
					$html .= '<br>';
				}
				$html .= $death_date->Display(!$SEARCH_SPIDER);
			}
			if ($death_dates[0]->gregorianYear()>=1550 && $death_dates[0]->gregorianYear()<2030 && !isset($unique_indis[$person->getXref()])) {
				$deat_by_decade[(int)($death_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
			}
		} else {
			$death_date = $person->getEstimatedDeathDate();
			// Estimated death dates are a fixed number of years after the birth date.
			// Don't show estimates in the future.
			if ($SHOW_EST_LIST_DATES && $death_date->MinJD() < WT_CLIENT_JD) {
				$html .= $death_date->Display(!$SEARCH_SPIDER);
			} else if ($person->isDead()) {
				$html .= WT_I18N::translate('yes');
			} else {
				$html .= '&nbsp;';
			}
			$death_dates[0] = new WT_Date('');
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>'. $death_date->JD(). '</td>';
		//-- Death anniversary
		$html .= '<td>'.WT_Date::getAge($death_dates[0], null, 2).'</td>';
		//-- Age at death
		$age = WT_Date::getAge($birth_dates[0], $death_dates[0], 0);
		if (!isset($unique_indis[$person->getXref()]) && $age>=0 && $age<=$max_age) {
			$deat_by_age[$age] .= $person->getSex();
		}
		// Need both display and sortable age
		$html .= '<td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 2) . '</td><td>' . WT_Date::getAge($birth_dates[0], $death_dates[0], 1) . '</td>';
		//-- Death place
		$html .= '<td>';
		foreach ($person->getAllDeathPlaces() as $n=>$death_place) {
			$tmp = new WT_Place($death_place, WT_GED_ID);
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= $tmp->getShortName();
			} else {
				$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
				$html .= highlight_search_hits($tmp->getShortName()). '</a>';
			}
		}
		$html .= '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $person->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Last change hidden sort column
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $person->LastChangeTimestamp(true). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Sorting by gender
		$html .= '<td>';
		$html .= $person->getSex();
		$html .= '</td>';
		//-- Filtering by birth date
		$html .= '<td>';
		if (!$person->canDisplayDetails() || WT_Date::Compare($birth_date, $d100y)>0) {
			$html .= 'Y100';
		} else {
			$html .= 'YES';
		}
		$html .= '</td>';
		//-- Filtering by death date
		$html .= '<td>';
		// Died in last 100 years?  Died?  Not dead?
		if (WT_Date::Compare($death_dates[0], $d100y)>0) {
			$html .= 'Y100';
		} elseif ($death_dates[0]->minJD() || $person->isDead()) {
			$html .= 'YES';
		} else {
			$html .= 'N';
		}
		$html .= '</td>';
		//-- Roots or Leaves ?
		$html .= '<td>';
		if (!$person->getChildFamilies()) { $html .= 'R'; }  // roots
		elseif (!$person->isDead() && $person->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
		else { $html .= '&nbsp;'; }
		$html .= '</td>';
		$html .= '</tr>';
		$unique_indis[$person->getXref()] = true;
	}
	$html .= '
				</tbody>
			</table>
			<div id="indi_list_table-charts_'. $table_id. '" style="display:none">
				<table class="list-charts">
					<tr>
						<td>
							' . print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')) . '
						</td>
						<td>
							' . print_chart_by_decade($deat_by_decade, WT_I18N::translate('Decade of death')) . '
						</td>
					</tr>
					<tr>
						<td colspan="2">
							' . print_chart_by_age($deat_by_age, WT_I18N::translate('Age related to death year')) . '
						</td>
					</tr>
				</table>
		</div>
		</div>';

	return $html;
}

// print a table of families
function format_fam_table($datalist, $option = '') {
	global $GEDCOM, $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $controller;

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'famTable';
	}

	if ($option=='BIRT_PLAC' || $option=='DEAT_PLAC') return;
	$html = '';
	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable( {
				dom: \'<"H"<"filtersH_' . $table_id . '"><"dt-clear">pBf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id . '">>\',
				' . WT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csv", exportOptions: {columns: [0,4,6,10,12,15] }}],
				jQueryUI: true,
				autoWidth: false,
				processing: true,
				retrieve: true,
				columns: [
					/*  0 husb givn */ {dataSort: 2},
					/*  1 husb surn */ {dataSort: 3},
					/*  2 GIVN,SURN */ {type: "unicode", visible: false},
					/*  3 SURN,GIVN */ {type: "unicode", visible: false},
					/*  4 age       */ {dataSort: 5, class: "center"},
					/*  5 AGE       */ {type: "num", visible: false},
					/*  6 wife givn */ {dataSort: 8},
					/*  7 wife surn */ {dataSort: 9},
					/*  8 GIVN,SURN */ {type: "unicode", visible: false},
					/*  9 SURN,GIVN */ {type: "unicode", visible: false},
					/* 10 age       */ {dataSort: 11, class: "center"},
					/* 11 AGE       */ {type: "num", visible: false},
					/* 12 marr date */ {dataSort: 13},
					/* 13 MARR:DATE */ {visible: false},
					/* 14 anniv     */ {dataSort: 13, class: "center"},
					/* 15 marr plac */ {type: "unicode"},
					/* 16 children  */ {dataSort: 17, class: "center"},
					/* 17 NCHI      */ {type: "num", visible: false},
					/* 18 CHAN      */ {dataSort: 19, visible: ' . ($SHOW_LAST_CHANGE ? 'true' : 'false') . '},
					/* 19 CHAN_sort */ {visible: false},
					/* 20 MARR      */ {visible: false},
					/* 21 DEAT      */ {visible: false},
					/* 22 TREE      */ {visible: false}
				],
				sorting: [[1, "asc"]],
				displayLength: 20,
				pagingType: "full_numbers",
				stateSave: true,
				stateDuration: -1
		   });

			jQuery("#' . $table_id . '")
			/* Hide/show parents */
			.on("click", ".btn-toggle-parents", function() {
				jQuery(this).toggleClass("ui-state-active");
				jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
			})

			/* Hide/show statistics */
			.on("click", ".btn-toggle-statistics", function() {
				jQuery(this).toggleClass("ui-state-active");
				jQuery("#fam_list_table-charts_' . $table_id . '").slideToggle();
			})

			/* Filter buttons in table header */
			.on("click", "button[data-filter-column]", function() {
				var btn = $(this);
				// De-activate the other buttons in this button group
				btn.siblings().removeClass("ui-state-active");
				// Apply (or clear) this filter
				var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
				if (btn.hasClass("ui-state-active")) {
					btn.removeClass("ui-state-active");
					col.search("").draw();
				} else {
					btn.addClass("ui-state-active");
					col.search(btn.data("filter-value")).draw();
				}
  			});

			jQuery(".fam-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
	');

	$stats = new WT_Stats($GEDCOM);
	$max_age = max($stats->oldestMarriageMaleAge(), $stats->oldestMarriageFemaleAge())+1;

	//-- init chart data
	for ($age=0; $age<=$max_age; $age++) {
		$marr_by_age[$age] = '';
	}
	for ($year = 1550; $year<2030; $year+=10) {
		$birt_by_decade[$year] = '';
		$marr_by_decade[$year] = '';
	}

	$html = '
		<div class="loading-image">&nbsp;</div>
		<div class="fam-list">
			<table id="' . $table_id . '">
				<thead>
					<tr>
						<th colspan="23">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="N"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show individuals who are alive or couples where both partners are alive.').'"
									>
										' . WT_I18N::translate('Both alive').'
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="W"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples where only the female partner is deceased.').'"
									>
										' . WT_I18N::translate('Widower') . '
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="H"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples where only the male partner is deceased.').'"
									>
										' . WT_I18N::translate('Widow') . '
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="Y"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show individuals who are dead or couples where both partners are deceased.').'"
									>
										' . WT_I18N::translate('Both dead') . '
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="R"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.') . '"
									>
										' . WT_I18N::translate('Roots') . '
									</button>
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="L"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.').'"
									>
										' . WT_I18N::translate('Leaves') . '
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="20"
										data-filter-value="U"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples with an unknown marriage date.').'"
									>
										' . WT_Gedcom_Tag::getLabel('MARR').'
									</button>
									<button
										type="button"
										data-filter-column="20"
										data-filter-value="YES"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples who married more than 100 years ago.').'"
									>
										'.WT_Gedcom_Tag::getLabel('MARR') . '&gt;100
									</button>
									<button
										type="button"
										data-filter-column="20"
										data-filter-value="Y100"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples who married within the last 100 years.').'"
									>
										' . WT_Gedcom_Tag::getLabel('MARR') . '&lt;=100
									</button>
									<button
										type="button"
										data-filter-column="20"
										data-filter-value="D"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show divorced couples.').'"
									>
										' . WT_Gedcom_Tag::getLabel('DIV') . '
									</button>
									<button
										type="button"
										data-filter-column="20"
										data-filter-value="M"
										class="ui-state-default"
										title="' . WT_I18N::translate('Show couples where either partner married more than once.').'"
									>
										' . WT_I18N::translate('Multiple marriages') . '
									</button>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th>' . WT_Gedcom_Tag::getLabel('GIVN') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('SURN') . '</th>
						<th>HUSB:GIVN_SURN</th>
						<th>HUSB:SURN_GIVN</th>
						<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>
						<th>AGE</th>
						<th>'. WT_Gedcom_Tag::getLabel('GIVN'). '</th>
						<th>'. WT_Gedcom_Tag::getLabel('SURN'). '</th>
						<th>WIFE:GIVN_SURN</th>
						<th>WIFE:SURN_GIVN</th>
						<th>'. WT_Gedcom_Tag::getLabel('AGE'). '</th>
						<th>AGE</th>
						<th>'. WT_Gedcom_Tag::getLabel('MARR'). '</th>
						<th>MARR:DATE</th>
						<th><i class="icon-reminder" title="'. WT_I18N::translate('Anniversary'). '"></i></th>
						<th>'. WT_Gedcom_Tag::getLabel('PLAC'). '</th>
						<th><i class="icon-children" title="'. WT_I18N::translate('Children'). '"></i></th>
					<th>NCHI</th>
					<th' .($SHOW_LAST_CHANGE?'':''). '>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>
					<th' .($SHOW_LAST_CHANGE?'':''). '>CHAN</th>
					<th>MARR</th>
					<th>DEAT</th>
					<th>TREE</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="23">
						<div class="btn-toolbar">
							<div class="btn-group">
								<button type="button" class="ui-state-default btn-toggle-parents">
									' . WT_I18N::translate('Show parents') . '
								</button>
								<button type="button" class="ui-state-default btn-toggle-statistics">
									' . WT_I18N::translate('Show statistics charts') . '
								</button>
							</div>
						</div>
					</th>
				</tr>
			</tfoot>
			<tbody>';

	$d100y=new WT_Date(date('Y')-100);  // 100 years ago
	foreach ($datalist as $family) {
		//-- Retrieve husband and wife
		$husb = $family->getHusband();
		if (is_null($husb)) $husb = new WT_Person('');
		$wife = $family->getWife();
		if (is_null($wife)) $wife = new WT_Person('');
		if (!$family->canDisplayDetails()) {
			continue;
		}
		//-- place filtering
		if ($option=='MARR_PLAC' && strstr($family->getMarriagePlace(), $filter)===false) continue;
		$html .= '<tr>';
		//-- Husband name(s)
		$html .= '<td colspan="2">';
		foreach ($husb->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $husb)).'"';
			}
			if ($num==$husb->getPrimaryName()) {
				$class=' class="name2"';
				$sex_image=$husb->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='';
				$sex_image='';
			}
			// Only show married names if they are the name we are filtering by.
			if ($name['type']!='_MARNM' || $num==$husb->getPrimaryName()) {
				$html .= '<a '. $title. ' href="'. $family->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image. '<br>';
			}
		}
		// Husband parents
		$html .= $husb->getPrimaryParentsNames('parents details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- Husb GIVN
		// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		$mdate=$family->getMarriageDate();
		//-- Husband age
		$hdate=$husb->getBirthDate();
		if ($hdate->isOK() && $mdate->isOK()) {
			if ($hdate->gregorianYear()>=1550 && $hdate->gregorianYear()<2030) {
				$birt_by_decade[(int)($hdate->gregorianYear()/10)*10] .= $husb->getSex();
			}
			$hage=WT_Date::getAge($hdate, $mdate, 0);
			if ($hage>=0 && $hage<=$max_age) {
				$marr_by_age[$hage].=$husb->getSex();
			}
		}
		$html .= '<td>'.WT_Date::getAge($hdate, $mdate, 2).'</td><td>'.WT_Date::getAge($hdate, $mdate, 1).'</td>';
		//-- Wife name(s)
		$html .= '<td colspan="2">';
		foreach ($wife->getAllNames() as $num=>$name) {
			if ($name['type']=='NAME') {
				$title='';
			} else {
				$title='title="'.strip_tags(WT_Gedcom_Tag::getLabel($name['type'], $wife)).'"';
			}
			if ($num == $wife->getPrimaryName()) {
				$class = ' class="name2"';
				$sex_image = $wife->getSexImage();
				list($surn, $givn)=explode(',', $name['sort']);
			} else {
				$class='';
				$sex_image='';
			}
			// Only show married names if they are the name we are filtering by.
			if ($name['type']!='_MARNM' || $num==$wife->getPrimaryName()) {
				$html .= '<a '. $title. ' href="'. $family->getHtmlUrl(). '"'. $class. '>'. highlight_search_hits($name['full']). '</a>'. $sex_image. '<br>';
			}
		}
		// Wife parents
		$html .= $wife->getPrimaryParentsNames('parents details1', 'none');
		$html .= '</td>';
		// Dummy column to match colspan in header
		$html .= '<td style="display:none;"></td>';
		//-- Wife GIVN
		//-- Husb GIVN
		// Use "AAAA" as a separator (instead of ",") as Javascript.localeCompare() ignores
		// punctuation and "ANN,ROACH" would sort after "ANNE,ROACH", instead of before it.
		// Similarly, @N.N. would sort as NN.
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). '</td>';
		$html .= '<td>'. WT_Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)). 'AAAA'. WT_Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)). '</td>';
		$mdate=$family->getMarriageDate();
		//-- Wife age
		$wdate = $wife->getBirthDate();
		if ($wdate->isOK() && $mdate->isOK()) {
			if ($wdate->gregorianYear()>=1550 && $wdate->gregorianYear()<2030) {
				$birt_by_decade[(int)($wdate->gregorianYear()/10)*10] .= $wife->getSex();
			}
			$wage = WT_Date::getAge($wdate, $mdate, 0);
			if ($wage>=0 && $wage<=$max_age) {
				$marr_by_age[$wage].=$wife->getSex();
			}
		}
		$html .= '<td>'.WT_Date::getAge($wdate, $mdate, 2).'</td><td>'.WT_Date::getAge($wdate, $mdate, 1).'</td>';
		//-- Marriage date
		$html .= '<td>';
		if ($marriage_dates = $family->getAllMarriageDates()) {
			foreach ($marriage_dates as $n=>$marriage_date) {
				if ($n) {
					$html .= '<br>';
				}
				$html .= '<div>'. $marriage_date->Display(!$SEARCH_SPIDER). '</div>';
			}
			if ($marriage_dates[0]->gregorianYear()>=1550 && $marriage_dates[0]->gregorianYear()<2030) {
				$marr_by_decade[(int)($marriage_dates[0]->gregorianYear()/10)*10] .= $husb->getSex() . $wife->getSex();
			}
		} else if (get_sub_record(1, '1 _NMR', $family->getGedcomRecord())) {
			$hus = $family->getHusband();
			$wif = $family->getWife();
			if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $hus);
			else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMR', $wif);
			else $html .= WT_Gedcom_Tag::getLabel('_NMR');
		} else if (get_sub_record(1, '1 _NMAR', $family->getGedcomRecord())) {
			$hus = $family->getHusband();
			$wif = $family->getWife();
			if (empty($wif) && !empty($hus)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $hus);
			else if (empty($hus) && !empty($wif)) $html .= WT_Gedcom_Tag::getLabel('_NMAR', $wif);
			else $html .= WT_Gedcom_Tag::getLabel('_NMAR');
		} else {
			$factdetail = explode(' ', trim($family->getMarriageRecord()));
			if (isset($factdetail)) {
				if (count($factdetail) >= 3) {
					if (strtoupper($factdetail[2]) != "N") {
						$html .= WT_I18N::translate('yes');
					} else {
						$html .= WT_I18N::translate('no');
					}
				} else {
					$html .= '&nbsp;';
				}
			}
		}
		$html .= '</td>';
		//-- Event date (sortable)hidden by datatables code
		$html .= '<td>';
		if ($marriage_dates) {
			$html .= $marriage_date->JD();
		} else {
			$html .= 0;
		}
		$html .= '</td>';
		//-- Marriage anniversary
		$html .= '<td>' . WT_Date::getAge($mdate, null, 2) . '</td>';
		//-- Marriage place
		$html .= '<td>';
		foreach ($family->getAllMarriagePlaces() as $n=>$marriage_place) {
			$tmp = new WT_Place($marriage_place, WT_GED_ID);
			if ($n) {
				$html .= '<br>';
			}
			if ($SEARCH_SPIDER) {
				$html .= $tmp->getShortName();
			} else {
				$html .= '<a href="'. $tmp->getURL() . '" title="'. strip_tags($tmp->getFullName()) . '">';
				$html .= highlight_search_hits($tmp->getShortName()). '</a>';
			}
		}
		$html .= '</td>';
		//-- Number of children
		$nchi=$family->getNumberOfChildren();
		$html .= '<td>'. WT_I18N::number($nchi). '</td><td>'. $nchi. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $family->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Last change hidden sort column
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $family->LastChangeTimestamp(true). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Sorting by marriage date
		$html .= '<td>';
		if (!$family->canDisplayDetails() || !$mdate->isOK()) {
			$html .= 'U';
		} else {
			if (WT_Date::Compare($mdate, $d100y)>0) {
				$html .= 'Y100';
			} else {
				$html .= 'YES';
			}
		}
		if ($family->isDivorced()) {
			$html .= 'D';
		}
		if (count($husb->getSpouseFamilies())>1 || count($wife->getSpouseFamilies())>1) {
			$html .= 'M';
		}
		$html .= '</td>';
		//-- Sorting alive/dead
		$html .= '<td>';
			if ($husb->isDead() && $wife->isDead()) $html .= 'Y';
			if ($husb->isDead() && !$wife->isDead()) {
				if ($wife->getSex()=='F') $html .= 'H';
				if ($wife->getSex()=='M') $html .= 'W'; // male partners
			}
			if (!$husb->isDead() && $wife->isDead()) {
				if ($husb->getSex()=='M') $html .= 'W';
				if ($husb->getSex()=='F') $html .= 'H'; // female partners
			}
			if (!$husb->isDead() && !$wife->isDead()) $html .= 'N';
		$html .= '</td>';
		//-- Roots or Leaves
		$html .= '<td>';
			if (!$husb->getChildFamilies() && !$wife->getChildFamilies()) { $html .= 'R'; } // roots
			elseif (!$husb->isDead() && !$wife->isDead() && $family->getNumberOfChildren()<1) { $html .= 'L'; } // leaves
			else { $html .= '&nbsp;'; }
		$html .= '</td>
		</tr>';
	}
	$html .= '
				</tbody>
			</table>
			<div id="fam_list_table-charts_'. $table_id. '" style="display:none">
				<table class="list-charts">
					<tr>
						<td>
							' . print_chart_by_decade($birt_by_decade, WT_I18N::translate('Decade of birth')) . '
						</td>
						<td>
							' . print_chart_by_decade($marr_by_decade, WT_I18N::translate('Decade of marriage')) . '
						</td>
					</tr>
					<tr>
						<td colspan="2">
							' . print_chart_by_age($marr_by_age, WT_I18N::translate('Age in year of marriage')) . '
						</td>
					</tr>
				</table>
		</div>
		</div>';

	return $html;
}

// print a table of sources
function format_sour_table($datalist) {
	global $SHOW_LAST_CHANGE, $controller;

	// Count the number of linked records.  These numbers include private records.
	// It is not good to bypass privacy, but many servers do not have the resources
	// to process privacy for every record in the tree, code courtesy Greg Roach, webtrees.net
	$count_individuals = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##individuals` JOIN `##link` ON l_from = i_id AND l_file = i_file AND l_type = 'SOUR' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_families = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##families` JOIN `##link` ON l_from = f_id AND l_file = f_file AND l_type = 'SOUR' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_media = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##media` JOIN `##link` ON l_from = m_id AND l_file = m_file AND l_type = 'SOUR' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_notes = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##other` JOIN `##link` ON l_from = o_id AND l_file = o_file AND o_type = 'NOTE' AND l_type = 'SOUR' GROUP BY l_to, l_file"
	)->fetchAssoc();

	$html = '';

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'sourTable';
	}

	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
				' . WT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csv", exportOptions: {columns: [0,2,3,5,7,9] }}],
				jQueryUI: true,
				autoWidth: false,
				processing: true,
				retrieve: true,
				columns: [
					/*  0 title     */ { dataSort: 1 },
					/*  1 TITL      */ { visible: false, type: "unicode" },
					/*  2 author    */ { type: "unicode" },
					/*  3 #indi     */ { dataSort: 4, class: "center" },
					/*  4 #INDI     */ { type: "num", visible: false },
					/*  5 #fam      */ { dataSort: 6, class: "center" },
					/*  6 #FAM      */ { type: "num", visible: false },
					/*  7 #obje     */ { dataSort: 8, class: "center" },
					/*  8 #OBJE     */ { type: "num", visible: false },
					/*  9 #note     */ { dataSort: 10, class: "center" },
					/* 10 #NOTE     */ { type: "num", visible: false },
					/* 11 CHAN      */ { dataSort: 12, visible: ' . ($SHOW_LAST_CHANGE?'true':'false') . ' },
					/* 12 CHAN_sort */ { visible: false },
					/* 13 DELETE    */ { visible: ' . (WT_USER_GEDCOM_ADMIN ? 'true' : 'false') . ', sortable: false }
				],
				displayLength: 20,
				pagingType: "full_numbers",
				stateSave: true,
				stateDuration: -1
		   });
			jQuery(".source-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="source-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL'). '</th>';
	$html .= '<th>TITL</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('AUTH'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals'). '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families'). '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Media objects'). '</th>';
	$html .= '<th>#OBJE</th>';
	$html .= '<th>'. WT_I18N::translate('Shared notes'). '</th>';
	$html .= '<th>#NOTE</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>CHAN</th>';
	$html .='<th><div class="delete_src">
				<input type="button" value="'. WT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(WT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'sources\');} else {return false;}">
				<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
			</div></th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n=0;
	foreach ($datalist as $key=>$value) {
		if (is_object($value)) { // Array of objects
			$source=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$source=WT_Source::getInstance($key); // from placelist
			if (is_null($source)) {
				$source=WT_Source::getInstance($value);
			}
			unset($value);
		} else { // Array of search results
			$gid='';
			if (isset($value['gid'])) {
				$gid=$value['gid'];
			}
			if (isset($value['gedcom'])) {
				$source=new WT_Source($value['gedcom']);
			} else {
				$source=WT_Source::getInstance($gid);
			}
		}
		if (!$source || !$source->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Source name(s)
		$html .= '<td>';
		foreach ($source->getAllNames() as $n=>$name) {
			if ($n) {
				$html .= '<br>';
			}
			if ($n==$source->getPrimaryName()) {
				$html .= '<a class="name2" href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			} else {
				$html .= '<a href="'. $source->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			}
		}
		$html .= '</td>';
		// Sortable name
		$html .= '<td>'. strip_tags($source->getFullName()). '</td>';
		$key = $source->getXref() . '@' . WT_GED_ID;
		//-- Author
		$html .= '<td>'. highlight_search_hits(htmlspecialchars($source->getAuth())). '</td>';
		//-- Linked INDIs
		$num = array_key_exists($key, $count_individuals) ? $count_individuals[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked FAMs
		$num = array_key_exists($key, $count_families) ? $count_families[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked OBJEcts
		$num = array_key_exists($key, $count_media) ? $count_media[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked NOTEs
		$num = array_key_exists($key, $count_notes) ? $count_notes[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $source->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Last change hidden sort column
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $source->LastChangeTimestamp(true). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Select & delete
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div class="delete_src">
				<input type="checkbox" name="del_places[]" class="check" value="'.$source->getXref().'" title="'. WT_I18N::translate('Delete'). '">'.
				'</div></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';

	return $html;
}

// print a table of shared notes
function format_note_table($datalist) {
	global $SHOW_LAST_CHANGE, $controller;

	// Count the number of linked records.  These numbers include private records.
	// It is not good to bypass privacy, but many servers do not have the resources
	// to process privacy for every record in the tree, code courtesy Greg Roach, webtrees.net
	$count_individuals = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##individuals` JOIN `##link` ON l_from = i_id AND l_file = i_file AND l_type = 'NOTE' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_families = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##families` JOIN `##link` ON l_from = f_id AND l_file = f_file AND l_type = 'NOTE' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_media = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##media` JOIN `##link` ON l_from = m_id AND l_file = m_file AND l_type = 'NOTE' GROUP BY l_to, l_file"
	)->fetchAssoc();
	$count_sources = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##sources` JOIN `##link` ON l_from = s_id AND l_file = s_file AND l_type = 'NOTE' GROUP BY l_to, l_file"
	)->fetchAssoc();

	$html = '';

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'noteTable';
	}

	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable({
			"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
			' . WT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: [0,1,3,5,7] }}],
			jQueryUI: true,
			autoWidth: false,
			processing: true,
			retrieve: true,
			columns: [
				/*  0 title     */ { type: "unicode" },
				/*  1 #indi     */ { dataSort: 2, class: "center" },
				/*  2 #INDI     */ { type: "num", visible: false },
				/*  3 #fam      */ { dataSort: 4, class: "center" },
				/*  4 #FAM      */ { type: "num", visible: false },
				/*  5 #obje     */ { dataSort: 6, class: "center" },
				/*  6 #OBJE     */ { type: "num", visible: false },
				/*  7 #sour     */ { dataSort: 8, class: "center" },
				/*  8 #SOUR     */ { type: "num", visible: false },
				/*  9 CHAN      */ { dataSort: 10, visible: ' . ($SHOW_LAST_CHANGE?'true':'false') . ' },
				/* 10 CHAN_sort */ { visible: false },
				/* 11 DELETE    */ { visible: ' . (WT_USER_GEDCOM_ADMIN?'true':'false') . ', sortable: false }
			],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1
	   });
			jQuery(".note-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="note-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals'). '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families'). '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Media objects'). '</th>';
	$html .= '<th>#OBJE</th>';
	$html .= '<th>'. WT_I18N::translate('Sources'). '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>CHAN</th>';
	$html .='<th><div class="delete_src">
				<input type="button" value="'. WT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(WT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'notes\');} else {return false;}">
				<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
			</div></th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n=0;
	foreach ($datalist as $key=>$value) {
		if (is_object($value)) { // Array of objects
			$note=$value;
		} elseif (!is_array($value)) { // Array of IDs
			$note=WT_Note::getInstance($key); // from placelist
			if (is_null($note)) {
				$note=WT_Note::getInstance($value);
			}
			unset($value);
		} else { // Array of search results
			$gid='';
			if (isset($value['gid'])) {
				$gid=$value['gid'];
			}
			if (isset($value['gedcom'])) {
				$note=new WT_Note($value['gedcom']);
			} else {
				$note=WT_Note::getInstance($gid);
			}
		}
		if (!$note || !$note->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Shared Note name
		$html .= '<td><a class="name2" href="'. $note->getHtmlUrl(). '">'. highlight_search_hits($note->getFullName()). '</a></td>';
		$key = $note->getXref() . '@' . WT_GED_ID;
		//-- Linked INDIs
		$num = array_key_exists($key, $count_individuals) ? $count_individuals[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked FAMs
		$num = array_key_exists($key, $count_families) ? $count_families[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked OBJEcts
		$num = array_key_exists($key, $count_media) ? $count_media[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Linked SOURs
		$num = array_key_exists($key, $count_sources) ? $count_sources[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $note->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td></td>';
		}
		//-- Last change hidden sort column
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $note->LastChangeTimestamp(true). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Select & delete
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div class="delete_src">
				<input type="checkbox" name="del_places[]" class="check" value="'.$note->getXref().'" title="'. WT_I18N::translate('Delete'). '">'.
				'</div></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';

	return $html;
}

// print a table of stories
function format_story_table($datalist) {
	global $controller;
	$html = '';

		if (WT_SCRIPT_NAME == 'search.php') {
			$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
		} else {
			$table_id = 'storyTable';
		}

		$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
		if (WT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(WT_JQUERY_DT_HTML5)
				->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
		}
		$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable({
			"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
			' . WT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv"}],
			jQueryUI: true,
			autoWidth: false,
			processing: true,
			retrieve: true,
			columns: [
                /* 0-name */ null,
                /* 1-NAME */ null
			],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1
	   });
			jQuery(".story-list").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="story-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '" class="width100"><thead><tr>';
	$html .= '<th>'. WT_I18N::translate('Story title'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Individual'). '</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
		foreach ($datalist as $story) {
			$story_title	= get_block_setting($story, 'title');
			$xref			= explode(",", get_block_setting($story, 'xref'));
			$count_xref		= count($xref);
			// if one indi is private, the whole story is private.
			$private = 0;
			for ($x = 0; $x < $count_xref; $x++) {
				$indi[$x] = WT_Person::getInstance($xref[$x]);
				if ($indi[$x] && !$indi[$x]->canDisplayDetails()) {
					$private = $x+1;
				}
			}
			if ($private == 0) {
				$languages=get_block_setting($story, 'languages');
				if (!$languages || in_array(WT_LOCALE, explode(',', $languages))) {
					$html .= '<tr>
						<td>'. highlight_search_hits($story_title) . '</td>
						<td>';
							for ($x = 0; $x < $count_xref; $x++) {
								$indi[$x] = WT_Person::getInstance($xref[$x]);
								if (!$indi[$x]){
									$html .= '<p style="margin:0;" class="error">'. $xref[$x]. '</p>';
								} else {
									$html .= '<p style="margin:0;"><a href="' . $indi[$x]->getHtmlUrl() . '#stories" class="current">' . highlight_search_hits($indi[$x]->getFullName()) . '</a></p>';
								}
							}
						$html .= '</td>
					</tr>';
				}
			}
		}
	$html .= '</tbody></table></div>';

	return $html;
}

// print a table of repositories
function format_repo_table($repos) {
	global $SHOW_LAST_CHANGE, $SEARCH_SPIDER, $controller;

	// Count the number of linked records.  These numbers include private records.
	// It is not good to bypass privacy, but many servers do not have the resources
	// to process privacy for every record in the tree code courtesy Greg Roach, webtrees.net
	$count_sources = WT_DB::prepare(
		"SELECT CONCAT(l_to, '@', l_file), COUNT(*) FROM `##sources` JOIN `##link` ON l_from = s_id AND l_file = s_file AND l_type = 'REPO' GROUP BY l_to, l_file"
	)->fetchAssoc();

	$html = '';

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'repoTable';
	}

	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable({
			"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
			' . WT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: [0,1] }}],
			jQueryUI: true,
			autoWidth: false,
			processing: true,
			retrieve: true,
			columns: [
				/* 0 name      */ { type: "unicode" },
				/* 1 #sour     */ { dataSort: 2, class: "center" },
				/* 2 #SOUR     */ { type: "num", visible: false },
				/* 3 CHAN      */ { dataSort: 4, visible: ' . ($SHOW_LAST_CHANGE?'true':'false') . ' },
				/* 4 CHAN_sort */ { visible: false },
				/* 5 DELETE    */ { visible: ' . (WT_USER_GEDCOM_ADMIN?'true':'false') . ', sortable: false }
			],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1
	   });
		jQuery(".repo-list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="repo-list">';
	//-- table header
	$html .= '<table id="'. $table_id. '"><thead><tr>';
	$html .= '<th>'. WT_I18N::translate('Repository name'). '</th>';
	$html .= '<th>'. WT_I18N::translate('Sources'). '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>'. WT_Gedcom_Tag::getLabel('CHAN'). '</th>';
	$html .= '<th' .($SHOW_LAST_CHANGE?'':''). '>CHAN</th>';
	$html .='<th><div class="delete_src">
				<input type="button" value="'. WT_I18N::translate('Delete'). '" onclick="if (confirm(\''. htmlspecialchars(WT_I18N::translate('Permanently delete these records?')). '\')) {return checkbox_delete(\'repos\');} else {return false;}">
				<input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
			</div></th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n=0;
	foreach ($repos as $repo) {
		if (!$repo->canDisplayDetails()) {
			continue;
		}
		$html .= '<tr>';
		//-- Repository name(s)
		$html .= '<td>';
		foreach ($repo->getAllNames() as $n=>$name) {
			if ($n) {
				$html .= '<br>';
			}
			if ($n==$repo->getPrimaryName()) {
				$html .= '<a class="name2" href="'. $repo->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			} else {
				$html .= '<a href="'. $repo->getHtmlUrl(). '">'. highlight_search_hits($name['full']). '</a>';
			}
		}
		$html .= '</td>';
		$key = $repo->getXref() . '@' . WT_GED_ID;
		//-- Linked SOURces
		$num = array_key_exists($key, $count_sources) ? $count_sources[$key] : 0;
		$html .= '<td>'. WT_I18N::number($num). '</td><td>'. $num. '</td>';
		//-- Last change
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $repo->LastChangeTimestamp(). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Last change hidden sort column
		if ($SHOW_LAST_CHANGE) {
			$html .= '<td>'. $repo->LastChangeTimestamp(true). '</td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		//-- Select & delete
		if (WT_USER_GEDCOM_ADMIN) {
			$html .= '<td><div class="delete_src">
				<input type="checkbox" name="del_places[]" class="check" value="'.$repo->getXref().'" title="'. WT_I18N::translate('Delete'). '">'.
				'</div></td>';
		} else {
			$html .= '<td>&nbsp;</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';

	return $html;
}

// print a table of media objects
function format_media_table($datalist) {
	global $SHOW_LAST_CHANGE, $controller;
	$html = '';

	if (WT_SCRIPT_NAME == 'search.php') {
		$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	} else {
		$table_id = 'mediaTable';
	}

	$controller->addExternalJavascript(WT_JQUERY_DATATABLES_URL);
	if (WT_USER_CAN_EDIT) {
		$controller
			->addExternalJavascript(WT_JQUERY_DT_HTML5)
			->addExternalJavascript(WT_JQUERY_DT_BUTTONS);
	}
	$controller->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable({
			"sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
			' . WT_I18N::datatablesI18N() . ',
			buttons: [{extend: "csv", exportOptions: {columns: [1,2,3,5,7] }}],
			jQueryUI: true,
			autoWidth: false,
			processing: true,
			retrieve: true,
			columns: [
				/* 0 media		*/ { sortable: false },
				/* 1 title		*/ { type: "unicode"},
				/* 2 file		*/ { visible: ' . (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT ? 'true' : 'false') . '},
				/* 3 #indi		*/ { dataSort: 4, class: "center" },
				/* 4 #INDI		*/ { type: "num", visible: false },
				/* 5 #fam		*/ { dataSort: 6, class: "center" },
				/* 6 #FAM		*/ { type: "num", visible: false },
				/* 7 #sour		*/ { dataSort: 8, class: "center" },
				/* 8 #SOUR		*/ { type: "num", visible: false },
				/* 9 CHAN		*/ { dataSort: 10, visible: ' . ($SHOW_LAST_CHANGE ? 'true' : 'false') . '},
				/* 10 CHAN_sort	*/ { visible: false },
			],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1
	   });
		jQuery(".media-list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
		');

	//--table wrapper
	$html .= '<div class="loading-image">&nbsp;</div>';
	$html .= '<div class="media-list">';
	//-- table header
	$html .= '<table id="'. $table_id . '"><thead><tr>';
	$html .= '<th>'. WT_I18N::translate('Media') . '</th>';
	$html .= '<th>'. WT_Gedcom_Tag::getLabel('TITL') . '</th>';
	$html .= '<th>'. WT_I18N::translate('File name') . '</th>';
	$html .= '<th>'. WT_I18N::translate('Individuals') . '</th>';
	$html .= '<th>#INDI</th>';
	$html .= '<th>'. WT_I18N::translate('Families') . '</th>';
	$html .= '<th>#FAM</th>';
	$html .= '<th>'. WT_I18N::translate('Sources') . '</th>';
	$html .= '<th>#SOUR</th>';
	$html .= '<th' . ($SHOW_LAST_CHANGE?'':'') . '>'. WT_Gedcom_Tag::getLabel('CHAN') . '</th>';
	$html .= '<th' . ($SHOW_LAST_CHANGE?'':'') . '>CHAN</th>';
	$html .= '</tr></thead>';
	//-- table body
	$html .= '<tbody>';
	$n = 0;
	foreach ($datalist as $key => $value) {
		if (is_object($value)) { // Array of objects
			$media=$value;
		} else {
			$media = new WT_Media($value["GEDCOM"]);
			if (is_null($media)) $media = WT_Media::getInstance($key);
			if (is_null($media)) continue;
		}
		if ($media->canDisplayDetails()) {
			$name = $media->getFullName();
			$html .= "<tr>";
			//-- Object thumbnail
			$html .= '<td>'. $media->displayImage(). '</td>';
			//-- Object name(s)
			$html .= '<td>';
			$html .= '<a href="'. $media->getHtmlUrl(). '" class="list_item name2">';
			$html .= highlight_search_hits($name). '</a>';
			$html .= '</td>';
			//-- File name
			$html .= '<td>';
			if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT)
				$html .= '<br><a href="'. $media->getHtmlUrl(). '">'. basename($media->getFilename()). '</a>';
			$html .= '</td>';

			//-- Linked INDIs
			$num=count($media->fetchLinkedIndividuals());
			$html .= '<td>' . WT_I18N::number($num). '</td><td>' . $num. '</td>';
			//-- Linked FAMs
			$num=count($media->fetchLinkedfamilies());
			$html .= '<td>' . WT_I18N::number($num). '</td><td>' . $num. '</td>';
			//-- Linked SOURces
			$num=count($media->fetchLinkedSources());
			$html .= '<td>' . WT_I18N::number($num). '</td><td>' . $num. '</td>';
			//-- Last change
			if ($SHOW_LAST_CHANGE) {
				$html .= '<td>' . $media->LastChangeTimestamp() . '</td>';
			} else {
				$html .= '<td>&nbsp;</td>';
			}
			//-- Last change hidden sort column
			if ($SHOW_LAST_CHANGE) {
				$html .= '<td>' . $media->LastChangeTimestamp(true) . '</td>';
			} else {
				$html .= '<td>&nbsp;</td>';
			}
			$html .= '</tr>';
		}
	}
	$html .= '</tbody></table></div>';

	return $html;
}

/**
 * Print a table of surnames, for the top surnames block, the indi/fam lists, etc.
 *
 * @param string[][] $surnames array (of SURN, of array of SPFX_SURN, of array of PID)
 * @param string $script "indilist.php" (counts of individuals) or "famlist.php" (counts of spouses)
 * @param string $sort "1" for ascending order, "2" for descending order
 *
 * @return string
 */
function format_surname_table($surnames, $script = '', $sort = '2') {
	global $controller;
	$html = '';
	$controller
		->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
		->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["num-asc" ]=function(a,b) {a=parseFloat(a); b=parseFloat(b); return (a<b) ? -1 : (a>b ? 1 : 0);};
			jQuery.fn.dataTableExt.oSort["num-desc"]=function(a,b) {a=parseFloat(a); b=parseFloat(b); return (a>b) ? -1 : (a<b ? 1 : 0);};
			jQuery(".surname-list").dataTable( {
			dom: \'t\',
			destroy: true,
			jQueryUI: true,
			autoWidth:false,
			paging: false,
			sorting: [[' . ($sort == '1' ? '2, "asc"' : '2, "desc"') . ']],
			columns: [
				/*  0 name  */ { dataSort:1 },
				/*  1 NAME  */ { visible:false },
				/*  2 count */ { dataSort:3, class: "center" },
				/*  3 COUNT */ { visible:false}
			]
			});
		');

	if ($script == 'famlist.php') {
		$col_heading = WT_I18N::translate('Spouses');
	} else {
		$col_heading = WT_I18N::translate('Individuals');
	}

	$html .= '
		<table class="surname-list">
			<thead>
				<tr>
					<th>' . WT_Gedcom_Tag::getLabel('SURN') . '</th>
					<th>&nbsp;</th>
					<th>' . $col_heading . '</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>';
				foreach ($surnames as $surn => $surns) {
					// Each surname links back to the indi/fam surname list
					if ($surn) {
						$url = $script . '?surname=' . rawurlencode($surn) . '&amp;ged=' . WT_GEDURL . '&show_all_firstnames=yes';
					} else {
						$url = $script . '?alpha=,&amp;ged=' . WT_GEDURL;
					}
					$html .= '<tr>
						<td>';
							// Multiple surname variants, e.g. von Groot, van Groot, van der Groot, etc.
							foreach ($surns as $spfxsurn => $indis) {
								if ($spfxsurn) {
									$html .= '<a href="' . $url . '" dir="auto">' . htmlspecialchars($spfxsurn) . '</a><br>';
								} else {
									// No surname, but a value from "2 SURN"?  A common workaround for toponyms, etc.
									$html .= '<a href="' . $url . '" dir="auto">' . htmlspecialchars($surn) . '</a><br>';
								}
							}
						$html .= '</td>';
						// Sort column for name
						$html .= '<td>' . $surn . '</td>';
						// Surname count
						$html .= '<td>';
							$subtotal = 0;
							foreach ($surns as $spfxsurn => $indis) {
								$subtotal += count($indis);
								$html .= WT_I18N::number(count($indis)) . '<br>';
							}
							// More than one surname variant? Show a subtotal
							if (count($surns) > 1) {
								$html .= WT_I18N::number($subtotal);
							}
						$html .= '</td>';
						// add hidden numeric sort column
						$html .= '<td>' . $subtotal . '</td>
					</tr>';
				}
			$html .= '</tbody>
		</table>
	';

	return $html;
}

// Print a tagcloud of surnames.
// @param $surnames array (of SURN, of array of SPFX_SURN, of array of PID)
// @param $type string, indilist or famlist
// @param $totals, boolean, show totals after each name
function format_surname_tagcloud($surnames, $script, $totals) {
	$cloud=new Zend_Tag_Cloud(
		array(
			'tagDecorator'=>array(
				'decorator'=>'HtmlTag',
				'options'=>array(
					'htmlTags'=>array(),
					'fontSizeUnit'=>'%',
					'minFontSize'=>80,
					'maxFontSize'=>250
				)
			),
			'cloudDecorator'=>array(
				'decorator'=>'HtmlCloud',
				'options'=>array(
					'htmlTags'=>array(
						'div'=>array(
							'class'=>'tag_cloud'
						)
					)
				)
			)
		)
	);
	foreach ($surnames as $surn=>$surns) {
		foreach ($surns as $spfxsurn=>$indis) {
			$cloud->appendTag(array(
				'title'=>$totals ? WT_I18N::translate('%1$s (%2$d)', '<span dir="auto">'.$spfxsurn.'</span>', count($indis)) : $spfxsurn,
				'weight'=>count($indis),
				'params'=>array(
					'url'=>$surn ?
						$script.'?surname='.urlencode($surn).'&amp;ged='.WT_GEDURL :
						$script.'?alpha=,&amp;ged='.WT_GEDURL
				)
			));
		}
	}
	return (string)$cloud;
}

// Print a list of surnames.
// @param $surnames array (of SURN, of array of SPFX_SURN, of array of PID)
// @param $style, 1=bullet list, 2=semicolon-separated list, 3=tabulated list with up to 4 columns
// @param $totals, boolean, show totals after each name
// @param $type string, indilist or famlist
function format_surname_list($surnames, $style, $totals, $script) {
	global $GEDCOM;

	$html=array();
	foreach ($surnames as $surn=>$surns) {
		// Each surname links back to the indilist
		if ($surn) {
			$url=$script.'?surname='.urlencode($surn).'&amp;ged='.rawurlencode($GEDCOM);
		} else {
			$url=$script.'?alpha=,&amp;ged='.rawurlencode($GEDCOM);
		}
		// If all the surnames are just case variants, then merge them into one
		// Comment out this block if you want SMITH listed separately from Smith
		$first_spfxsurn=null;
		foreach ($surns as $spfxsurn=>$indis) {
			if ($first_spfxsurn) {
				if (utf8_strtoupper($spfxsurn)==utf8_strtoupper($first_spfxsurn)) {
					$surns[$first_spfxsurn]=array_merge($surns[$first_spfxsurn], $surns[$spfxsurn]);
					unset ($surns[$spfxsurn]);
				}
			} else {
				$first_spfxsurn=$spfxsurn;
			}
		}
		$subhtml='<a href="'.$url.'" dir="auto">'.htmlspecialchars(implode(WT_I18N::$list_separator, array_keys($surns))).'</a>';

		if ($totals) {
			$subtotal=0;
			foreach ($surns as $spfxsurn=>$indis) {
				$subtotal+=count($indis);
			}
			$subhtml.='&nbsp;('.WT_I18N::number($subtotal).')';
		}
		$html[]=$subhtml;

	}
	switch ($style) {
	case 1:
		return '<ul><li>'.implode('</li><li>', $html).'</li></ul>';
	case 2:
		return implode(WT_I18N::$list_separator, $html);
	case 3:
		$i = 0;
		$count = count($html);
		$count_indi = 0;
		$col = 1;
		if ($count>36) $col=4;
		else if ($count>18) $col=3;
		else if ($count>6) $col=2;
		$newcol=ceil($count/$col);
		$html2 ='<table class="list_table"><tr>';
		$html2.='<td class="list_value" style="padding: 14px;">';

		foreach ($html as $surn=>$surns) {
			$html2.= $surns.'<br>';
			$i++;
			if ($i==$newcol && $i<$count) {
				$html2.='</td><td class="list_value" style="padding: 14px;">';
				$newcol=$i+ceil($count/$col);
			}
		}
		$html2.='</td></tr></table>';

		return $html2;
	}
}


// print a list of recent changes
function print_changes_list($change_ids, $sort) {
	$n = 0;
	$arr=array();
	foreach ($change_ids as $change_id) {
		$record = WT_GedcomRecord::getInstance($change_id);
		if (!$record || !$record->canDisplayDetails()) {
			continue;
		}
		// setup sorting parameters
		$arr[$n]['record'] = $record;
		$arr[$n]['jd'] = ($sort == 'name') ? 1 : $n;
		$arr[$n]['anniv'] = $record->LastChangeTimestamp(true);
		$arr[$n++]['fact'] = $record->getSortName(); // in case two changes have same timestamp
	}

	switch ($sort) {
	case 'name':
		uasort($arr, 'event_sort_name');
		break;
	case 'date_asc':
		uasort($arr, 'event_sort');
		$arr = array_reverse($arr);
		break;
	case 'date_desc':
		uasort($arr, 'event_sort');
	}
	$html = '';
	foreach ($arr as $value) {
		$html .= '<a href="' . $value['record']->getHtmlUrl() . '" class="list_item name2">' . $value['record']->getFullName() . '</a>';
		$html .= '<div class="indent" style="margin-bottom:5px">';
		if ($value['record']->getType() == 'INDI') {
			if ($value['record']->getAddName()) {
				$html .= '<a href="' . $value['record']->getHtmlUrl() . '" class="list_item">' . $value['record']->getAddName() . '</a>';
			}
		}
		$html .= /* I18N: [a record was] Changed on <date/time> by <user> */ WT_I18N::translate('Changed on %1$s by %2$s', $value['record']->LastChangeTimestamp(), $value['record']->LastChangeUser());
		$html .= '</div>';
	}
	return $html;
}

// print a table of recent changes
function print_changes_table($change_ids, $sort) {
	global $controller;

	$return = '';
	$n = 0;

	$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page

	switch ($sort) {
	case 'name':        //name
		$aaSorting = "[5,'asc'], [4,'desc']";
		break;
	case 'date_asc':    //date ascending
		$aaSorting = "[4,'asc'], [5,'asc']";
		break;
	case 'date_desc':   //date descending
		$aaSorting = "[4,'desc'], [5,'asc']";
		break;
	}
	$html = '';
	$controller
		->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
		->addInlineJavascript('
			jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
			jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
			jQuery("#' . $table_id . '").dataTable({
				dom: \'t\',
				' . WT_I18N::datatablesI18N() . ',
				autoWidth: false,
				jQueryUI: true,
				sorting: ['.$aaSorting.'],
				columns: [
					/* 0-Type */    { sortable: false, class: "center" },
					/* 1-Record */  { dataSort: 5 },
					/* 2-Change */  ' . (WT_USER_ID ? '{ dataSort: 4 }' : '{ visible: false }') . ',
					/* 3-By */      ' . (WT_USER_ID ? 'null' : '{ visible: false }') . ',
					/* 4-DATE */    { visible: false },
					/* 5-SORTNAME */{ type: "unicode", visible: false}
				],
				displayLength: 20,
				pagingType: "full_numbers",
				stateSave: true,
				stateDuration: -1
			});
		');

		//-- table header
		$html .= '
			<table id="' . $table_id . '" class="width100">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>' . WT_I18N::translate('Record') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('_WT_USER') . '</th>
						<th>DATE</th>
						<th>SORTNAME</th>
					</tr>
				</thead>
				<tbody>
		';

	//-- table body
	foreach ($change_ids as $change_id) {
		$record = WT_GedcomRecord::getInstance($change_id);
		if (!$record || !$record->canDisplayDetails()) {
			continue;
		}
		$indi = false;
		switch ($record->getType()) {
			case "INDI":
				$icon = $record->getSexImage('small', '', '', false);
				$indi = true;
				break;
			case "FAM":
				$icon = '<i class="icon-button_family"></i>';
				break;
			case "OBJE":
				$icon = '<i class="icon-button_media"></i>';
				break;
			case "NOTE":
				$icon = '<i class="icon-button_note"></i>';
				break;
			case "SOUR":
				$icon = '<i class="icon-button_source"></i>';
				break;
			case "REPO":
				$icon = '<i class="icon-button_repository"></i>';
				break;
			default:
				$icon = '&nbsp;';
				break;
		}
		$name = $record->getFullName();
		++$n;
		$html .= '
				<tr>
					<td><a href="'. $record->getHtmlUrl() .'">'. $icon . '</a></td>
					<td class="wrap">
						<a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
						if ($indi) {
							$addname = $record->getAddName();
							if ($addname) {
								$html .= '<div class="indent"><a href="'. $record->getHtmlUrl() .'">'. $addname . '</a></div>';
							}
						}
					$html .= '</td>
					<td class="wrap">' . $record->LastChangeTimestamp() . '</td>
					<td class="wrap">' . $record->LastChangeUser() . '</td>
					<td>' . $record->LastChangeTimestamp(true) . '</td>
					<td>' . $record->getSortName() . '</td>
				</tr>';
	}

	$html .= '</tbody></table>';
	return $html;
}

// print a table of events
function print_events_table($startjd, $endjd, $events='BIRT MARR DEAT', $only_living=false, $sort_by='anniv') {
	global $controller;
	$html = '';
	$table_id = 'ID'.(int)(microtime()*1000000); // lists requires a unique ID in case there are multiple lists per page
	$controller
		->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
		->addInlineJavascript('
			jQuery("#' . $table_id . '").dataTable({
				"sDom": \'t\',
				' . WT_I18N::datatablesI18N() . ',
				jQueryUI: true,
				autoWidth: false,
				filter: false,
				lengthChange: false,
				info: true,
				paging: false,
				sorting: [[ '.($sort_by=='alpha' ? 1 : 3).', "asc" ]],
				columns: [
					/* 0-Record */ { dataSort: 1 },
					/* 1-NAME */   { visible: false },
					/* 2-Date */   { dataSort: 3 },
					/* 3-DATE */   { visible: false },
					/* 4-Anniv. */ { dataSort: 5, class: "center" },
					/* 5-ANNIV  */ { type: "numeric", visible: false },
					/* 6-Event */  { class: "center" }
				]
			});
		');

	// Did we have any output?  Did we skip anything?
	$output = 0;
	$filter = 0;
	$filtered_events = array();

	foreach (get_events_list($startjd, $endjd, $events) as $value) {
		$record=$value['record'];
		//-- only living people ?
		if ($only_living) {
			if ($record->getType()=="INDI" && $record->isDead()) {
				$filter ++;
				continue;
			}
			if ($record->getType()=="FAM") {
				$husb = $record->getHusband();
				if (is_null($husb) || $husb->isDead()) {
					$filter ++;
					continue;
				}
				$wife = $record->getWife();
				if (is_null($wife) || $wife->isDead()) {
					$filter ++;
					continue;
				}
			}
		}

		// Privacy
		if (!$record->canDisplayDetails() || !canDisplayFact($record->getXref(), $record->getGedId(), $value['factrec'])) {
			continue;
		}
		//-- Counter
		$output ++;

		if ($output==1) {
			//-- table body
			$html .= '<table id="'.$table_id.'" class="width100">';
			$html .= '<thead><tr>';
			$html .= '<th>'.WT_I18N::translate('Record').'</th>';
			$html .= '<th>NAME</th>'; //hidden by datatables code
			$html .= '<th>'.WT_Gedcom_Tag::getLabel('DATE').'</th>';
			$html .= '<th>DATE</th>'; //hidden by datatables code
			$html .= '<th><i class="icon-reminder" title="'.WT_I18N::translate('Anniversary').'"></i></th>';
			$html .= '<th>ANNIV</th>';
			$html .= '<th>'.WT_Gedcom_Tag::getLabel('EVEN').'</th>';
			$html .= '</tr></thead><tbody>'."\n";
		}

		$value['name'] = $record->getFullName();
		$value['url'] = $record->getHtmlUrl();
		if ($record->getType()=="INDI") {
			$value['sex'] = $record->getSexImage();
		} else {
			$value['sex'] = '';
		}
		$filtered_events[] = $value;
	}

	foreach ($filtered_events as $n=>$value) {
		$html .= "<tr>";
		//-- Record name(s)
		$name = $value['name'];
		$html .= '<td class="wrap">';
		$html .= '<a href="'.$value['url'].'">'.$name.'</a>';
		if ($value['record']->getType()=="INDI") {
			$html .= $value['sex'];
		}
		$html .= '</td>';
		//-- NAME
		$html .= '<td>'; //hidden by datatables code
		$html .= $value['record']->getSortName();
		$html .= '</td>';
		//-- Event date
		$html .= '<td class="wrap">';
		$html .= $value['date']->Display(empty($SEARCH_SPIDER));
		$html .= '</td>';
		//-- Event date (sortable)
		$html .= '<td>'; //hidden by datatables code
		$html .= $n;
		$html .= '</td>';
		//-- Anniversary
		$anniv = $value['anniv'];
		$html .= '<td>'.($anniv ? WT_I18N::number($anniv) : '&nbsp;').'</td><td>'.$anniv.'</td>';
		//-- Event name
		$html .= '<td class="wrap">';
		$html .= '<a href="'.$value['url'].'">'.WT_Gedcom_Tag::getLabel($value['fact']).'</a>';
		$html .= '&nbsp;</td>';

		$html .= '</tr>'."\n";
	}

	if ($output!=0) {
		$html .= '</tbody></table>';
	}

	// Print a final summary message about restricted/filtered facts
	$summary = "";
	if ($endjd==WT_CLIENT_JD) {
		// We're dealing with the Today's Events block
		if ($output==0) {
			if ($filter==0) {
				$summary = WT_I18N::translate('No events exist for today.');
			} else {
				$summary = WT_I18N::translate('No events for living people exist for today.');
			}
		}
	} else {
		// We're dealing with the Upcoming Events block
		if ($output==0) {
			if ($filter==0) {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as “tomorrow”
					$summary = WT_I18N::plural('No events exist for the next %s day.', 'No events exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			} else {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events for living people exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as “tomorrow”
					$summary = WT_I18N::plural('No events for living people exist for the next %s day.', 'No events for living people exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			}
		}
	}
	if ($summary!="") {
		$html .= '<strong>'. $summary. '</strong>';
	}

	return $html;
}

/**
 * print a list of events
 *
 * This performs the same function as print_events_table(), but formats the output differently.
 */
function print_events_list($startjd, $endjd, $events='BIRT MARR DEAT', $only_living=false, $sort_by='anniv') {
	// Did we have any output?  Did we skip anything?
	$output = 0;
	$filter = 0;
	$filtered_events = array();
	$html = '';
	foreach (get_events_list($startjd, $endjd, $events) as $value) {
		$record = WT_GedcomRecord::getInstance($value['id']);
		//-- only living people ?
		if ($only_living) {
			if ($record->getType()=="INDI" && $record->isDead()) {
				$filter ++;
				continue;
			}
			if ($record->getType()=="FAM") {
				$husb = $record->getHusband();
				if (is_null($husb) || $husb->isDead()) {
					$filter ++;
					continue;
				}
				$wife = $record->getWife();
				if (is_null($wife) || $wife->isDead()) {
					$filter ++;
					continue;
				}
			}
		}

		// Privacy
		if (!$record->canDisplayDetails() || !canDisplayFact($record->getXref(), $record->getGedId(), $value['factrec'])) {
			continue;
		}
		$output ++;

		$value['name'] = $record->getFullName();
		$value['url'] = $record->getHtmlUrl();
		if ($record->getType()=="INDI") {
			$value['sex'] = $record->getSexImage();
		} else {
			$value['sex'] = '';
		}
		$filtered_events[] = $value;
	}

	// Now we've filtered the list, we can sort by event, if required
	switch ($sort_by) {
	case 'anniv':
		uasort($filtered_events, 'event_sort');
		break;
	case 'alpha':
		uasort($filtered_events, 'event_sort_name');
		break;
	}

	foreach ($filtered_events as $value) {
		$html .= "<a href=\"".$value['url']."\" class=\"list_item name2\">".$value['name']."</a>".$value['sex'];
		$html .= "<br><div class=\"indent\">";
		$html .= WT_Gedcom_Tag::getLabel($value['fact']).' - '.$value['date']->Display(true);
		if ($value['anniv']!=0) $html .= " (" . WT_I18N::translate('%s year anniversary', $value['anniv']).")";
		if (!empty($value['plac'])) {
			$tmp=new WT_Place($value['plac'], WT_GED_ID);
			$html .= " - <a href=\"".$tmp->getURL()."\">".$tmp->getFullName()."</a>";
		}
		$html .= "</div>";
	}

	// Print a final summary message about restricted/filtered facts
	$summary = "";
	if ($endjd == WT_CLIENT_JD) {
		// We're dealing with the Today's Events block
		if ($output == 0) {
			if ($filter == 0) {
				$summary = WT_I18N::translate('No events exist for today.');
			} else {
				$summary = WT_I18N::translate('No events for living people exist for today.');
			}
		}
	} else {
		// We're dealing with the Upcoming Events block
		if ($output == 0) {
			if ($filter == 0) {
				if ($endjd == $startjd) {
					$summary = WT_I18N::translate('No events exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as “tomorrow”
					$summary = WT_I18N::plural('No events exist for the next %s day.', 'No events exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			} else {
				if ($endjd==$startjd) {
					$summary = WT_I18N::translate('No events for living people exist for tomorrow.');
				} else {
					// I18N: tanslation for %s==1 is unused; it is translated separately as “tomorrow”
					$summary = WT_I18N::plural('No events for living people exist for the next %s day.', 'No events for living people exist for the next %s days.', $endjd-$startjd+1, WT_I18N::number($endjd-$startjd+1));
				}
			}
		}
	}
	if ($summary) {
		$html .= "<b>". $summary. "</b>";
	}

	return $html;
}

// print a chart by age using Google chart API
function print_chart_by_age($data, $title) {
	$count = 0;
	$agemax = 0;
	$vmax = 0;
	$avg = 0;
	foreach ($data as $age=>$v) {
		$n = strlen($v);
		$vmax = max($vmax, $n);
		$agemax = max($agemax, $age);
		$count += $n;
		$avg += $age*$n;
	}
	if ($count<1) return;
	$avg = round($avg/$count);
	$chart_url = "https://chart.googleapis.com/chart?cht=bvs"; // chart type
	$chart_url .= "&amp;chs=725x150"; // size
	$chart_url .= "&amp;chbh=3,2,2"; // bvg : 4,1,2
	$chart_url .= "&amp;chf=bg,s,FFFFFF99"; //background color
	$chart_url .= "&amp;chco=0000FF,FFA0CB,FF0000"; // bar color
	$chart_url .= "&amp;chdl=".rawurlencode(WT_I18N::translate('Males'))."|".rawurlencode(WT_I18N::translate('Females'))."|".rawurlencode(WT_I18N::translate('Average age').": ".$avg); // legend & average age
	$chart_url .= "&amp;chtt=".rawurlencode($title); // title
	$chart_url .= "&amp;chxt=x,y,r"; // axis labels specification
	$chart_url .= "&amp;chm=V,FF0000,0,".($avg-0.3).",1"; // average age line marker
	$chart_url .= "&amp;chxl=0:|"; // label
	for ($age=0; $age<=$agemax; $age+=5) {
		$chart_url .= $age."|||||"; // x axis
	}
	$chart_url .= "|1:||".rawurlencode(WT_I18N::percentage($vmax/$count)); // y axis
	$chart_url .= "|2:||";
	$step = $vmax;
	for ($d=$vmax; $d>0; $d--) {
		if ($vmax<($d*10+1) && ($vmax % $d)==0) $step = $d;
	}
	if ($step==$vmax) {
		for ($d=$vmax-1; $d>0; $d--) {
			if (($vmax-1)<($d*10+1) && (($vmax-1) % $d)==0) $step = $d;
		}
	}
	for ($n=$step; $n<$vmax; $n+=$step) {
		$chart_url .= $n."|";
	}
	$chart_url .= rawurlencode($vmax." / ".$count); // r axis
	$chart_url .= "&amp;chg=100,".round(100*$step/$vmax, 1).",1,5"; // grid
	$chart_url .= "&amp;chd=s:"; // data : simple encoding from A=0 to 9=61
	$CHART_ENCODING61 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	for ($age=0; $age<=$agemax; $age++) {
		$chart_url .= $CHART_ENCODING61[(int)(substr_count($data[$age], "M")*61/$vmax)];
	}
	$chart_url .= ",";
	for ($age=0; $age<=$agemax; $age++) {
		$chart_url .= $CHART_ENCODING61[(int)(substr_count($data[$age], "F")*61/$vmax)];
	}
	$html = '<img src="'. $chart_url. '" alt="'. $title. '" title="'. $title. '" class="gchart">';
	return $html;
}

// print a chart by decade using Google chart API
function print_chart_by_decade($data, $title) {
	$count = 0;
	$vmax = 0;
	foreach ($data as $age=>$v) {
		$n = strlen($v);
		$vmax = max($vmax, $n);
		$count += $n;
	}
	if ($count<1) return;
	$chart_url = "https://chart.googleapis.com/chart?cht=bvs"; // chart type
	$chart_url .= "&amp;chs=360x150"; // size
	$chart_url .= "&amp;chbh=3,3"; // bvg : 4,1,2
	$chart_url .= "&amp;chf=bg,s,FFFFFF99"; //background color
	$chart_url .= "&amp;chco=0000FF,FFA0CB"; // bar color
	$chart_url .= "&amp;chtt=".rawurlencode($title); // title
	$chart_url .= "&amp;chxt=x,y,r"; // axis labels specification
	$chart_url .= "&amp;chxl=0:|&lt;|||"; // <1570
	for ($y=1600; $y<2030; $y+=50) {
		$chart_url .= $y."|||||"; // x axis
	}
	$chart_url .= "|1:||".rawurlencode(WT_I18N::percentage($vmax/$count)); // y axis
	$chart_url .= "|2:||";
	$step = $vmax;
	for ($d=$vmax; $d>0; $d--) {
		if ($vmax<($d*10+1) && ($vmax % $d)==0) $step = $d;
	}
	if ($step==$vmax) {
		for ($d=$vmax-1; $d>0; $d--) {
			if (($vmax-1)<($d*10+1) && (($vmax-1) % $d)==0) $step = $d;
		}
	}
	for ($n=$step; $n<$vmax; $n+=$step) {
		$chart_url .= $n."|";
	}
	$chart_url .= rawurlencode($vmax." / ".$count); // r axis
	$chart_url .= "&amp;chg=100,".round(100*$step/$vmax, 1).",1,5"; // grid
	$chart_url .= "&amp;chd=s:"; // data : simple encoding from A=0 to 9=61
	$CHART_ENCODING61 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	for ($y=1570; $y<2030; $y+=10) {
		$chart_url .= $CHART_ENCODING61[(int)(substr_count($data[$y], "M")*61/$vmax)];
	}
	$chart_url .= ",";
	for ($y=1570; $y<2030; $y+=10) {
		$chart_url .= $CHART_ENCODING61[(int)(substr_count($data[$y], "F")*61/$vmax)];
	}
	$html = '<img src="'. $chart_url. '" alt="'. $title. '" title="'. $title. '" class="gchart">';
	return $html;
}
