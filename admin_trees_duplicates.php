<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_trees_duplicates.php');

require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Find duplicate individuals'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();

		// prevent more than two boxes from being checked
		var checked = 0;
		function addCheck(box) {
			// allow checked box to be unchecked
			if(!box.checked) return true;
			// get ref to collection
			var boxes = document.getElementsByName(box.name);
			// count checked
			var cb, count=0, k=0;
			while(cb=boxes[k++])
				if(cb.checked && ++count>2){
					alert("Sorry, you can only merge 2 at a time");
					return false;
				}
			return true;
		}

		// loop through all checkboxes with class "check" and create input string for form
		function checkbox_test() {
			var counter = 0, i = 0, myvar = new Array();
			form = document.createElement("form");
			form.setAttribute("method", "POST");
			form.setAttribute("action", "admin_trees_merge.php");
			form.setAttribute("target", "_blank");
			// get a collection of objects with the specified class "check"
			input_obj = document.getElementsByClassName("check"); // this might fail on some old browsers (see http://caniuse.com/getelementsbyclassname)
			// loop through all collected objects
			for (i = 0; i < input_obj.length; i++) {
				// if input object is checked then ...
				if (input_obj[i].checked === true) {
					// ... increase counter and concatenate checkbox value to the input string
					myvar[i] = document.createElement("input");
					myvar[i].setAttribute("name", "gid" + (counter + 1));
					myvar[i].setAttribute("type", "hidden");
					myvar[i].setAttribute("value", input_obj[i].value);
					form.appendChild(myvar[i]);
					counter++;
				}
			}
			// display send form or display message if there is only 1 or no checked checkboxes
			if (counter > 0) {
				if (counter == 1) {
					alert("Select TWO items to merge");
					return false;
				}
				// send checkbox values
				document.body.appendChild(form);
				form.submit();
			} else {
				alert("There is nothing selected");
			}
		}
	');

$action		= KT_Filter::get('action','go', '');
$gedcom_id	= safe_get('gedcom_id', array_keys(KT_Tree::getAll()), KT_GED_ID);
$surn		= KT_Filter::get('surname', '[^<>&%{};]*');
$givn		= KT_Filter::get('given', '[^<>&%{};]*');
$exact_givn	= KT_Filter::getBool('exact_givn');
$exact_surn	= KT_Filter::getBool('exact_surn');
$married	= KT_Filter::getBool('married');
$gender		= KT_Filter::get('gender');
$date 		= KT_Filter::getInteger('date');
$range 		= KT_Filter::getInteger('range');

$births 	= "'" . str_replace("|", "','", KT_EVENTS_BIRT) . "'";
$deaths 	= "'" . str_replace("|", "','", KT_EVENTS_DEAT) . "'";

// the sql query used to identify duplicates
$sql = '
	SELECT DISTINCT n_id, n_full, n_type, n_sort
	FROM `##name`
';
if ($date || preg_match('/\d{4}(?<!0000)/', $date)) {
	$minDate = $date - $range;
	$maxDate = $date + $range;
	$sql .= '
		INNER JOIN `##dates` ON d_gid = n_id
		WHERE n_file = '. $gedcom_id . '
		AND (
			(d_fact IN ('. $births . ') AND d_year <= ' . $maxDate . ' AND d_year >= ' . $minDate . ')
			 OR
			(d_fact IN ('. $deaths . ') AND d_year <= ' . $maxDate . ' AND d_year >= ' . $minDate . ')
		)
	';
} else {
	$sql .= 'WHERE n_file = '. $gedcom_id . ' ';
}
if ($exact_surn) {
	$sql .= 'AND n_surn = "' . $surn  . '" ';
} else {
	$sql .= 'AND n_surn LIKE "%' . $surn . '%" ';
}
if ($exact_givn) {
	$sql .= 'AND n_givn = "' . $givn  . '" ';
} else {
	$sql .= 'AND n_givn LIKE "%' . $givn . '%" ';
}
if (!$married) {
	$sql .= 'AND n_type NOT LIKE "_MARNM" ';
}
$sql .= 'ORDER BY n_sort ASC';

$SHOW_EST_LIST_DATES=get_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES');

echo '<div id="admin_dup">
	<h2>' . $controller->getPageTitle() . '</h2>
	<form method="get" name="duplicates_form" action="', KT_SCRIPT_NAME, '">
		<input type="hidden" name="action" value="go">
		<div class="gm_check">
			<div id="famtree">
				<label>', KT_I18N::translate('Family tree'), '</label>
				<select name="ged">';
				foreach (KT_Tree::getAll() as $tree) {
					echo '<option value="', $tree->tree_name_html, '"';
					if (empty($ged) && $tree->tree_id == KT_GED_ID || !empty($ged) && $ged == $tree->tree_name) {
						echo ' selected="selected"';
					}
					echo ' dir="auto">', $tree->tree_title_html, '</option>';
				}
				echo '</select>
			</div>
			<div id="surnm">
				<label for="SURN">', KT_I18N::translate('Surname'), '</label>
						<div class="exact" title="', KT_I18N::translate('Match exactly'), '">
							<input data-autocomplete-type="SURN" type="text" name="surname" id="SURN" value="', htmlspecialchars((string) $surn), '" dir="auto">
							<input type="checkbox" name="exact_surn" value="1"';
								if ($exact_surn) {
									echo ' checked="checked"';
								}
							echo '>',
							KT_I18N::translate('Tick for exact match'), '
						</div>
			</div>
			<div id="givnm">
				<label for="GIVN">', KT_I18N::translate('Given name'), '</label>
					<div class="exact" title="', KT_I18N::translate('Match exactly'), '">
						<input data-autocomplete-type="GIVN" type="text" name="given" id="GIVN" value="', htmlspecialchars((string) $givn), '" dir="auto">
						<input type="checkbox" name="exact_givn" value="1" title="', KT_I18N::translate('Match exactly'), '"';
							if ($exact_givn) {
								echo ' checked="checked"';
							}
						echo '>',
						KT_I18N::translate('Tick for exact match'), '
					</div>
			</div>
			<hr>
			<div id="gender">
				<label>', KT_I18N::translate('Gender'), '</label>
				<select name="gender">
					<option value="A"';
						if ($gender == 'A' || empty($gender)) echo ' selected="selected"';
						echo '>', KT_I18N::translate('Any'), '
					</option>
					<option value="M"';
						if ($gender == 'M') echo ' selected="selected"';
						echo '>', KT_I18N::translate('Male'), '
					</option>
					<option value="F"';
						if ($gender == 'F') echo ' selected="selected"';
						echo '>', KT_I18N::translate('Female'), '
					</option>
					<option value="U"';
						if ($gender == 'U') echo ' selected="selected"';
						echo '>', KT_I18N::translate_c('unknown gender', 'Unknown'), '
					</option>
				</select>
			</div>
			<div id="marname">
				<label>', KT_I18N::translate('Include married names'), '</label>
				<input type="checkbox" name="married" value="1"';
				if ($married) {
					echo ' checked="checked"';
				}
				echo '>
			</div>
			<hr>
			<div id="date_range">
				<label>' . KT_I18N::translate('Enter a birth or death year, and a range either side of that') .'</label>
				<div id="date">
					<label for="date">', KT_I18N::translate('Date'), '</label>
					<input type="number" value="' . $date . '" id="date" name="date" placeholder="' . KT_I18N::translate('4-digit year') . '">
				</div>
				<div id="range">
					<label for="range">' . KT_I18N::translate('Range of years (plus / minus)') . '</label>
					<input type="number" value="' . $range . '" name="range" id="range" min="0" max="10">
				</div>
			</div>
			<button type="submit" class="btn btn-primary">
				<i class="fa fa-eye"></i>' ,
				KT_I18N::translate('Show'), '
			</button>
		</div>
	</form>';
	// START OUTPUT
	if ($action == 'go') {
		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		$count = 0;
		foreach ($rows as $row) {
			$count ++;
		}
		if ($rows && $count > 1) {
			$name1 = '';
			$name2 = '';
			echo '<h3>' . KT_I18N::translate('%s possible duplicates found', $count) . '</h3>
				<div class="scrollableContainer">
				<div class="scrollingArea">
					<table id="duplicates_table">
						<thead>
							<tr>
								<th rowspan="2"><div class="col1">' . KT_I18N::translate('Name') . '</div></th>
								<th colspan="2">' . KT_I18N::translate('Birth') . '</th>
								<th colspan="2">' . KT_I18N::translate('Death') . '</th>
								<th rowspan="2">
									<div class="col6" style="min-width: 50px;">
										<input type="button" value="' . KT_I18N::translate('Merge selected') . '" onclick="return checkbox_test();">
									</div>
								</th>
							</tr>
							<tr>
								<th><div class="col2">' . KT_I18N::translate('Date') . '</div></th>
								<th><div class="col3">' . KT_I18N::translate('Place') . '</div></th>
								<th><div class="col4">' . KT_I18N::translate('Date') . '</div></th>
								<th><div class="col5">' . KT_I18N::translate('Place') . '</div></th>
							</tr>
						</thead>
						<tbody>';
							$i = 0;
							foreach ($rows as $row) {
								$i++;
								$bdate	= '&nbsp;';
								$bplace	= '';
								$ddate	= '&nbsp;';
								$dplace	= '';
								$name1	= $row['n_full'];
								if ($row['n_type'] == '_MARNM') {
									$marr = '<span style="font-style:italic;font-size:80%;">('.KT_I18N::translate('Married name').')</span>';
								} else {
									$marr = '';
								}
								$id = $row['n_id'];
								$person = KT_Person::getInstance($id);
								if ($person->getSex() == $gender || $gender == 'A') {
									// find birth/death dates
									if ($birth_dates=$person->getAllBirthDates()) {
										foreach ($birth_dates as $num=>$birth_date) {
											if ($num) {$bdate .= '<br>';}
											$bdate .= $birth_date->Display();
										}
									} else {
										$birth_date	= $person->getEstimatedBirthDate();
										if ($SHOW_EST_LIST_DATES) {
											$bdate .= $birth_date->Display();
										} else {
											$bdate .= '&nbsp;';
										}
										$birth_dates[0] = new KT_Date('');
									}

									//find birth places
									foreach ($person->getAllBirthPlaces() as $n=>$birth_place) {
										$tmp = new KT_Place($birth_place, KT_GED_ID);
										if ($n) {$bplace .= '<br>';}
										$bplace .= $tmp->getShortName();
									}

									// find death dates
									if ($death_dates = $person->getAllDeathDates()) {
										foreach ($death_dates as $num=>$death_date) {
											if ($num) {$ddate .= '<br>';}
											$ddate .= $death_date->Display();
										}
									} else {
										$death_date	= $person->getEstimatedDeathDate();
										if ($SHOW_EST_LIST_DATES) {
											$ddate .= $death_date->Display();
										} else if ($person->isDead()) {
											$ddate .= KT_I18N::translate('yes');
										} else {
											$ddate .= '&nbsp;';
										}
										$death_dates[0]=new KT_Date('');
									}

									// find death places
									foreach ($person->getAllDeathPlaces() as $n=>$death_place) {
										$tmp=new KT_Place($death_place, KT_GED_ID);
										if ($n) {$dplace .= '<br>';}
										$dplace .= $tmp->getShortName();
									}

									//output result rows, grouping exact matches (on full name)
									if ($bdate !== '&nbsp;' && $ddate !== '&nbsp;') {
										if ($name2 == $name1) {
											echo '<tr>
												<td><div class="col1"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $name1. ' '. $marr. '</a></td>
												<td><div class="col2">' . $bdate . '</div></td>
												<td><div class="col3">'. $bplace. '</div></td>
												<td><div class="col4">' . $ddate . '</div></td>
												<td><div class="col5">'. $dplace. '</div></td>
												<td><div class="col6"><input type="checkbox" name="gid[]"  onclick="return addCheck(this);" class="check" value="'.$id.'"></div></td>
												</tr>';
										} else {
											$name2 = $row['n_full'];
											echo '<tr><td colspan="5" style="border:0;">&nbsp;</td></tr>
												<tr>
												<td><div class="col1"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $name2. ' '. $marr. '</a></div></td>
												<td><div class="col2">' . $bdate . '</div></td>
												<td><div class="col3">'. $bplace. '</div></td>
												<td><div class="col4">' . $ddate . '</div></td>
												<td><div class="col5">'. $dplace. '</div></td>
												<td><div class="col6"><input type="checkbox" name="gid[]"  onclick="return addCheck(this);" class="check" value="'.$id.'"></div></td>
												</tr>';
										}
									}
								}
							}
						echo '</tbody>
					</table>
				</div>
			</div>';
		} else {
			echo '<h4>' . KT_I18N::translate('No duplicates to display') . '</h4>';
		}
	}
echo '</div>';
