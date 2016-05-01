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

define('WT_SCRIPT_NAME', 'admin_trees_duplicates.php');

require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Find duplicate individuals'))
	->pageHeader()
	->addExternalJavascript(WT_STATIC_URL.'js/autocomplete.js')
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
			form.setAttribute("action", "admin_site_merge.php");
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

$action		= safe_get('action','go', '');
$gedcom_id	= safe_get('gedcom_id', array_keys(WT_Tree::getAll()), WT_GED_ID);
$surn		= WT_Filter::get('surname', '[^<>&%{};]*');
$givn		= WT_Filter::get('given', '[^<>&%{};]*');
$exact_givn	= safe_GET_bool('exact_givn');
$exact_surn	= safe_GET_bool('exact_surn');
$married	= safe_GET_bool('married');
$gender		= safe_GET('gender');

// the sql query used to identify duplicates
$sql = '
	SELECT n_id, n_full, n_surn, n_givn, n_type, n_sort
	FROM `##name` ';
	if ($exact_surn) {
		$sql .= 'WHERE n_surn = "' . $surn  . '" ';
	} else {
		$sql .= 'WHERE n_surn LIKE "%' . $surn . '%"';
	}
	if ($exact_givn) {
		$sql .= 'AND n_givn = "' . $givn  . '" ';
	} else {
		$sql .= 'AND n_givn LIKE "%' . $givn . '%"';
	}
	if (!$married) {
		$sql .= 'AND n_type NOT LIKE "_MARNM" ';
	}
	$sql .= 'AND n_file = '. $gedcom_id. ' ';
	$sql .= 'AND n_full IN (
		SELECT n_full
		FROM `##name`
		GROUP BY n_full
		HAVING count(n_full) > 1
		)
	ORDER BY n_sort ASC';

$SHOW_EST_LIST_DATES=get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES');

echo '<div id="admin_dup">
	<h2>' .$controller->getPageTitle(). '</h2>
	<form method="get" name="duplicates_form" action="', WT_SCRIPT_NAME, '">
		<div class="gm_check">
			<div id="famtree">
				<label>', WT_I18N::translate('Family tree'), '</label>
				<select name="ged">';
				foreach (WT_Tree::getAll() as $tree) {
					echo '<option value="', $tree->tree_name_html, '"';
					if (empty($ged) && $tree->tree_id == WT_GED_ID || !empty($ged) && $ged == $tree->tree_name) {
						echo ' selected="selected"';
					}
					echo ' dir="auto">', $tree->tree_title_html, '</option>';
				}
				echo '</select>
			</div>
			<div id="surnm">
				<label for="SURN">', WT_I18N::translate('Surname'), '</label>
						<div class="exact" title="', WT_I18N::translate('Match exactly'), '">
							<input data-autocomplete-type="SURN" type="text" name="surname" id="SURN" value="', htmlspecialchars($surn), '" dir="auto">
							<input type="checkbox" name="exact_surn" value="1"';
								if ($exact_surn) {
									echo ' checked="checked"';
								}
							echo '>',
							WT_I18N::translate('Tick for exact match'), '
						</div>
			</div>
			<div id="givnm">
				<label for="GIVN">', WT_I18N::translate('Given name'), '</label>
					<div class="exact" title="', WT_I18N::translate('Match exactly'), '">
						<input data-autocomplete-type="GIVN" type="text" name="given" id="GIVN" value="', htmlspecialchars($givn), '" dir="auto">
						<input type="checkbox" name="exact_givn" value="1" title="', WT_I18N::translate('Match exactly'), '"';
							if ($exact_givn) {
								echo ' checked="checked"';
							}
						echo '>',
						WT_I18N::translate('Tick for exact match'), '
					</div>
			</div>
			<div id="gender">
				<label>', WT_I18N::translate('Gender'), '</label>
				<select name="gender">
					<option value="A"';
						if ($gender == 'A' || empty($gender)) echo ' selected="selected"';
						echo '>', WT_I18N::translate('Any'), '
					</option>
					<option value="M"';
						if ($gender == 'M') echo ' selected="selected"';
						echo '>', WT_I18N::translate('Male'), '
					</option>
					<option value="F"';
						if ($gender == 'F') echo ' selected="selected"';
						echo '>', WT_I18N::translate('Female'), '
					</option>
					<option value="U"';
						if ($gender == 'U') echo ' selected="selected"';
						echo '>', WT_I18N::translate_c('unknown gender', 'Unknown'), '
					</option>
				</select>
			</div>
			<div id="marname">
				<label>', WT_I18N::translate('Include married names: '), '</label>
				<input type="checkbox" name="married" value="1"';
				if ($married) {
					echo ' checked="checked"';
				}
				echo '>
			</div>
			<div id="view">
				<input type="submit" name="action" value="',WT_I18N::translate('View'),'">
			</div>
		</div>
	</form>';
	// START OUTPUT
	if ($surn) {
		$rows=WT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
		if ($rows) {
			$name1 = '';
			$name2 = '';
			echo '<div class="scrollableContainer">
				<div class="scrollingArea">
					<table id="duplicates_table">
						<thead>
							<tr>
								<th rowspan="2"><div class="col1">',WT_I18N::translate('Name'),'</div></th>
								<th colspan="2">',WT_I18N::translate('Birth'),'</th>
								<th colspan="2">',WT_I18N::translate('Death'),'</th>
								<th rowspan="2">
									<div class="col6"><input type="button" value="',WT_I18N::translate('Merge selected'),'" onclick="return checkbox_test();"></div>
								</th>
							</tr>
							<tr>
								<th><div class="col2">',WT_I18N::translate('Date'),'</div></th>
								<th><div class="col3">',WT_I18N::translate('Place'),'</div></th>
								<th><div class="col4">',WT_I18N::translate('Date'),'</div></th>
								<th><div class="col5">',WT_I18N::translate('Place'),'</div></th>
							</tr>
						</thead>
						<tbody>';
							$i = 0;
							foreach ($rows as $row) {
								$i++;
								$bdate	= '';
								$bplace	= '';
								$ddate	= '';
								$dplace	= '';
								$name1	= $row['n_full'];
								if ($row['n_type'] == '_MARNM') {
									$marr = '<span style="font-style:italic;font-size:80%;">('.WT_I18N::translate('Married name').')</span>';
								} else {
									$marr = '';
								}
								$id = $row['n_id'];
								$person = WT_Person::getInstance($id);
								if ($person->getSex() == $gender || $gender == 'A') {
									// find birth/death dates
									if ($birth_dates=$person->getAllBirthDates()) {
										foreach ($birth_dates as $num=>$birth_date) {
											if ($num) {$bdate .= '<br>';}
											$bdate .= $birth_date->Display();
										}
									} else {
										$birth_date	= $person->getEstimatedBirthDate();
										$birth_jd	= $birth_date->JD();
										if ($SHOW_EST_LIST_DATES) {
											$bdate .= $birth_date->Display();
										} else {
											$bdate .= '&nbsp;';
										}
										$birth_dates[0] = new WT_Date('');
									}

									//find birth places
									foreach ($person->getAllBirthPlaces() as $n=>$birth_place) {
										$tmp = new WT_Place($birth_place, WT_GED_ID);
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
										$death_jd	= $death_date->JD();
										if ($SHOW_EST_LIST_DATES) {
											$ddate .= $death_date->Display();
										} else if ($person->isDead()) {
											$ddate .= WT_I18N::translate('yes');
										} else {
											$ddate .= '&nbsp;';
										}
										$death_dates[0]=new WT_Date('');
									}

									// find death places
									foreach ($person->getAllDeathPlaces() as $n=>$death_place) {
										$tmp=new WT_Place($death_place, WT_GED_ID);
										if ($n) {$dplace .= '<br>';}
										$dplace .= $tmp->getShortName();
									}

									//output result rows, grouping exact matches (on full name)
									if ($name2 == $name1) {
										echo '<tr>
											<td><div class="col1"><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $name1. ' '. $marr. '</a></td>
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
											<td><div class="col1"><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $name2. ' '. $marr. '</a></div></td>
											<td><div class="col2">' . $bdate . '</div></td>
											<td><div class="col3">'. $bplace. '</div></td>
											<td><div class="col4">' . $ddate . '</div></td>
											<td><div class="col5">'. $dplace. '</div></td>
											<td><div class="col6"><input type="checkbox" name="gid[]"  onclick="return addCheck(this);" class="check" value="'.$id.'"></div></td>
											</tr>';
									}
								}
							}
						echo '</tbody>
					</table>
				</div>
			</div>';
		} else {
			echo '<h4>', WT_I18N::translate('No duplicates to display'), '</h4>';
		}
	}
echo '</div>';
