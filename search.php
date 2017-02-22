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

define('WT_SCRIPT_NAME', 'search.php');
require './includes/session.php';
require_once WT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new WT_Controller_Search();
$controller
	->pageHeader()
	->setPageTitle(WT_I18N::translate('Search'))
	->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		jQuery("#search-tabs").tabs();
		jQuery("#search-page").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');
?>

<script>
	function checknames(frm) {
		action = "<?php echo $controller->action; ?>";
		if (action == "general") {
			if (frm.query.value.length<2) {
				alert("<?php echo WT_I18N::translate('Please enter more than one character'); ?>");
				frm.query.focus();
				return false;
			}
		} else if (action == "soundex") {
			year = frm.year.value;
			fname = frm.firstname.value;
			lname = frm.lastname.value;
			place = frm.place.value;

			// display an error message if there is insufficient data to perform a search on
			if (year == "") {
				message = true;
				if (fname.length >= 2)
					message = false;
				if (lname.length >= 2)
					message = false;
				if (place.length >= 2)
					message = false;
				if (message) {
					alert("<?php echo WT_I18N::translate('Please enter more than one character'); ?>");
					return false;
				}
			}

			// display a special error if the year is entered without a valid Given Name, Last Name, or Place
			if (year != "") {
				message = true;
				if (fname != "")
					message = false;
				if (lname != "")
					message = false;
				if (place != "")
					message = false;
				if (message) {
					alert("<?php echo WT_I18N::translate('Please enter a Given name, Last name, or Place in addition to Year'); ?>");
					frm.firstname.focus();
					return false;
				}
			}
			return true;
		}
		return true;
	}
</script>
<div class="loading-image">&nbsp;</div>
<div id="search-page" style="visibility: hidden;">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<div id="search-tabs">
		<ul>
			<li><a href="#general"><span><?php echo WT_I18N::translate('General'); ?></span></a></li>
			<li><a href="#soundex"><span><?php echo WT_I18N::translate('Phonetic'); ?></span></a></li>
			<?php if (WT_USER_GEDCOM_ADMIN) { ?>
				<li><a href="#replace"><span><?php echo WT_I18N::translate('Search and replace'); ?></span></a></li>
			<?php }
			if (WT_USER_ID) { ?>
				<li><a href="#advanced"><span><?php echo WT_I18N::translate('Advanced'); ?></span></a></li>
			<?php } ?>
		</ul>
		<!-- General search form -->
		<div id="general">
			<form method="post" name="searchform" onsubmit="return checknames(this);" action="search.php#general">
				<input type="hidden" name="action" value="general">
				<input type="hidden" name="isPostBack" value="true">
				<div class="search-page-table">
					<div class="label"><?php echo WT_I18N::translate('Search for'); ?></div>
					<div class="value">
						<input tabindex="1" id="query" type="text" name="query" value="<?php if (isset($controller->myquery)) echo $controller->myquery; ?>" size="40" autofocus >
						<?php echo print_specialchar_link('query'); ?>
					</div>
					<div class="label">
						<?php echo WT_I18N::translate('Records'); ?>
					</div>
					<div class="value">
						<p>
							<input type="checkbox" <?php if (isset ($controller->srindi) || !$controller->isPostBack) echo ' checked="checked"'; ?> value="yes" id="srindi" name="srindi" >
							<label for="srindi"><?php echo WT_I18N::translate('Individuals'); ?></label>
						</p>
						<p>
							<input type="checkbox" <?php if (isset ($controller->srfams)) echo ' checked="checked"'; ?> value="yes" id="srfams" name="srfams" >
							<label for="srfams"><?php echo WT_I18N::translate('Families'); ?></label>
						</p>
						<p>
							<input type="checkbox" <?php if (isset ($controller->srsour)) echo ' checked="checked"'; ?> value="yes" id="srsour" name="srsour" >
							<label for="srsour"><?php echo WT_I18N::translate('Sources'); ?></label>
						</p>
						<p>
							<input type="checkbox" <?php if (isset ($controller->srnote)) echo 'checked="checked"'; ?> value="yes" id="srnote" name="srnote" >
							<label for="srnote"><?php echo WT_I18N::translate('Shared notes'); ?></label>
						</p>
						<?php if (array_key_exists('stories', WT_Module::getActiveModules())) { ?>
							<p>
								<input type="checkbox" <?php if (isset ($controller->srstor)) echo 'checked="checked"'; ?> value="yes" id="srstor" name="srstor" >
								<label for="srstor"><?php echo WT_I18N::translate('Stories'); ?></label>
							</p>
						<?php } ?>
					</div>
					<div class="label">
						<?php echo WT_I18N::translate('Associates'); ?>
					</div>
					<div class="value">
						<input type="checkbox" id="showasso" name="showasso" value="on" <?php if ($controller->showasso == 'on') echo ' checked="checked"'; ?> >
						<label for="showasso"><?php echo WT_I18N::translate('Show related persons/families'); ?></label>
					</div>
					<hr>
					<?php echo search_trees(); ?>
				</div>
				<button class="btn btn-primary" type="submit">
					<i class="fa fa-search"></i>
					<?php echo WT_I18N::translate('search'); ?>
				</button>
			</form>
		</div>
		<!-- soundex search form -->
		<div id="soundex">
			<form method="post" name="searchform" onsubmit="return checknames(this);" action="search.php#soundex">
				<input type="hidden" name="action" value="soundex">
				<input type="hidden" name="isPostBack" value="true">
				<div class="search-page-table">
					<div class="label"><?php echo WT_I18N::translate('Given name'); ?></div>
					<div class="value">
						<input tabindex="3" type="text" data-autocomplete-type="GIVN" name="firstname" value="<?php echo htmlspecialchars($controller->firstname); ?>" autofocus>
					</div>
					<div class="label"><?php echo WT_I18N::translate('Last name'); ?></div>
					<div class="value">
						<input tabindex="4" type="text" data-autocomplete-type="SURN" name="lastname" value="<?php echo htmlspecialchars($controller->lastname); ?>">
					</div>
					<div class="label"><?php echo WT_I18N::translate('Place'); ?></div>
					<div class="value">
						<input tabindex="5" type="text" data-autocomplete-type="PLAC2" name="place" value="<?php echo WT_Filter::escapeHtml($controller->place); ?>">
					</div>
					<div class="label"><?php echo WT_I18N::translate('Year'); ?></div>
					<div class="value">
						<input tabindex="6" type="text" name="year" value="<?php echo htmlspecialchars($controller->year); ?>">
					</div>
					<!-- Soundex type options (Russell, DaitchM) -->
					<div class="label"><?php echo WT_I18N::translate('soundex algorithm'); ?></div>
						<div class="value">
							<p>
								<input type="radio" name="soundex" value="Russell" <?php if ($controller->soundex == "Russell") echo ' checked="checked" '; ?> >
								<?php echo WT_I18N::translate('Russell'); ?>
							</p>
							<p>
								<input type="radio" name="soundex" value="DaitchM" <?php if ($controller->soundex == "DaitchM" || $controller->soundex == "") echo ' checked="checked"'; ?> >
								<?php echo WT_I18N::translate('Daitch-Mokotoff'); ?>
							</p>
						</div>
					<!-- Associates Section -->
					<div class="label"><?php echo WT_I18N::translate('Associates'); ?></div>
					<div class="value">
						<input type="checkbox" name="showasso" value="on" <?php  if ($controller->showasso == "on") echo ' checked="checked" '; ?> >
						<?php echo WT_I18N::translate('Show related persons/families'); ?>
					</div>
				</div>
				<button class="btn btn-primary" type="submit">
					<i class="fa fa-search"></i>
					<?php echo WT_I18N::translate('search'); ?>
				</button>
			</form>
		</div>
		<!-- Search and replace Search form -->
		<?php if (WT_USER_GEDCOM_ADMIN) { ?>
			<div id="replace">
				<form method="post" name="searchform" onsubmit="return checknames(this);" action="search.php">
					<input type="hidden" name="action" value="replace">
					<input type="hidden" name="isPostBack" value="true">
					<div class="search-page-table">
						<div class="label"><?php echo WT_I18N::translate('Search for'); ?></div>
						<div class="value">
							<input tabindex="1" id="query" name="query" value="" type="text" autofocus>
							<?php echo print_specialchar_link('query'); ?>
						</div>
						<div class="label"><?php echo WT_I18N::translate('Replace with'); ?></div>
						<div class="value">
							<input tabindex="2" id="replace" name="replace" value="" type="text">
							<?php echo print_specialchar_link('replace'); ?>
						</div>
						<script>
							function checkAll(box) {
								if (!box.checked) {
									box.form.replaceNames.disabled = false;
									box.form.replacePlaces.disabled = false;
									box.form.replacePlacesWord.disabled = false;
								}
								else {
									box.form.replaceNames.disabled = true;
									box.form.replacePlaces.disabled = true;
									box.form.replacePlacesWord.disabled = true;
								}
							}
						</script>
						<div class="label"><?php echo WT_I18N::translate('Search'); ?></div>
						<div class="value">
							<p>
								<input id="replaceAll" checked="checked" onclick="checkAll(this);" value="yes" name="replaceAll" type="checkbox">
								<label for="replaceAll"><?php echo WT_I18N::translate('Entire record'); ?></label>
								<hr>
							</p>
							<p>
								<input id="replaceNames" checked="checked" disabled="disabled" value="yes" name="replaceNames" type="checkbox">
								<label for="replaceNames"><?php echo WT_I18N::translate('Individuals'); ?></label>
							</p>
							<p>
								<input id="replacePlace" checked="checked" disabled="disabled" value="yes" name="replacePlaces" type="checkbox">
								<label for="replacePlace"><?php echo WT_I18N::translate('Place'); ?></label>
							</p>
							<p>
								<input id="replaceWords" checked="checked" disabled="disabled" value="yes" name="replacePlacesWord" type="checkbox">
								<label for="replaceWords"><?php echo WT_I18N::translate('Whole words only'); ?></label>
							</p>
						</div>
					</div>
					<button class="btn btn-primary" type="submit">
						<i class="fa fa-search"></i>
						<?php echo WT_I18N::translate('search'); ?>
					</button>
				</form>
			</div>
		<?php } ?>
		<!-- Advanced search form -->
		<?php if (WT_USER_ID) { ?>
			<div id="advanced">
				<script>
					function checknames(frm) {
						action = "advanced";
						return true;
					}

					var numfields = <?php echo count($controller->fields); ?>;
					/**
					 * add a row to the table of fields
					 */
					function addFields() {
						// get the table
						var tbl = document.getElementById('field_table').tBodies[0];
						// create the new row
						var trow = document.createElement('tr');
						// create the new label cell
						var label = document.createElement('td');
						label.className='list_label';
						// create a select for the user to choose the field
						var sel = document.createElement('select');
						sel.name = 'fields['+numfields+']';
						sel.rownum = numfields;
						sel.onchange = function() {
							showDate(this, this.rownum);
						};

						// all of the field options
						<?php foreach ($controller->getOtherFields() as $field=>$label) { ?>
						opt = document.createElement('option');
						opt.value='<?php echo $field; ?>';
						opt.text='<?php echo addslashes($label); ?>';
						sel.options.add(opt);
						<?php } ?>
						label.appendChild(sel);
						trow.appendChild(label);
						// create the new value cell
						var val = document.createElement('td');
						val.id = 'vcell'+numfields;
						val.className='list_value';

						var inp = document.createElement('input');
						inp.name='values['+numfields+']';
						inp.type='text';
						inp.id='value'+numfields;
						inp.tabindex=numfields+1;
						val.appendChild(inp);
						trow.appendChild(val);
						var lastRow = tbl.lastChild.previousSibling;

						tbl.insertBefore(trow, lastRow.nextSibling);
						numfields++;
					}

					/**
					 * add the date options selection
					 */
					function showDate(sel, row) {
						var type = sel.options[sel.selectedIndex].value;
						var pm = document.getElementById('plusminus'+row);
						if (!type.match("DATE$")) {
							// if it is not a date do not show the date
							if (pm) pm.parentNode.removeChild(pm);
							return;
						}
						// if it is a date and the plusminus is already show, then leave
						if (pm) return;
						var elm = document.getElementById('vcell'+row);
						var sel = document.createElement('select');
						sel.id = 'plusminus'+row;
						sel.name = 'plusminus['+row+']';
						var opt = document.createElement('option');
						opt.value='';
						opt.text='<?php echo WT_I18N::translate('Exact date'); ?>';
						sel.appendChild(opt);
						opt = document.createElement('option');
						opt.value='';
						/* The translation strings use HTML entities, but javascript does not.  See bug 687980 */
						opt.text='<?php echo html_entity_decode(WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 2, 2), ENT_COMPAT, 'UTF-8'); ?>';
						sel.appendChild(opt);
						opt = document.createElement('option');
						opt.value='5';
						opt.text='<?php echo html_entity_decode(WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 5, 5), ENT_COMPAT, 'UTF-8'); ?>';
						sel.appendChild(opt);
						opt = document.createElement('option');
						opt.value='10';
						opt.text='<?php echo html_entity_decode(WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 10, 10), ENT_COMPAT, 'UTF-8'); ?>';
						sel.appendChild(opt);
						var spc = document.createTextNode(' ');
						elm.appendChild(spc);
						elm.appendChild(sel);
					}
				</script>
				<form method="post" name="searchform" onsubmit="return checknames(this);" action="search.php#advanced">
					<input type="hidden" name="action" value="advanced">
					<input type="hidden" name="isPostBack" value="true">
					<table id="field_table">
						<!-- // search terms -->
						<?php
						$fct = count($controller->fields);
						for ($i=0; $i<$fct; $i++) {
							if (strpos($controller->getField($i), "FAMC:HUSB:NAME")===0) continue;
							if (strpos($controller->getField($i), "FAMC:WIFE:NAME")===0) continue;
						?>
						<tr>
							<td class="list_label">
								<?php echo $controller->getLabel($controller->getField($i)); ?>
							</td>
							<td id="vcell<?php echo $i; ?>" class="list_value">
								<?php
								$currentFieldSearch = $controller->getField($i); // Get this field's name and the search criterion
								$currentField = substr($currentFieldSearch, 0, strrpos($currentFieldSearch, ':')); // Get the actual field name
								?>
									<input tabindex="<?php echo $i+1; ?>" type="text" id="value<?php echo $i; ?>" name="values[<?php echo $i; ?>]" value="<?php echo WT_Filter::escapeHtml($controller->getValue($i)); ?>"<?php echo (substr($controller->getField($i),-4)=='PLAC') ? 'data-autocomplete-type="PLAC"' : ''; ?>>
								<?php if (preg_match("/^NAME:/", $currentFieldSearch)>0) { ?>
									<select name="fields[<?php echo $i; ?>]">
										<option value="<?php echo $currentField; ?>:EXACT"<?php if (preg_match("/:EXACT$/", $currentFieldSearch)>0) echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Exact'); ?></option>
										<option value="<?php echo $currentField; ?>:BEGINS"<?php if (preg_match("/:BEGINS$/", $currentFieldSearch)>0) echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Begins with'); ?></option>
										<option value="<?php echo $currentField; ?>:CONTAINS"<?php if (preg_match("/:CONTAINS$/", $currentFieldSearch)>0) echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Contains'); ?></option>
										<option value="<?php echo $currentField; ?>:SDX"<?php if (preg_match("/:SDX$/", $currentFieldSearch)>0) echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Sounds like'); ?></option>
									</select>
								<?php } else { ?>
								<input type="hidden" name="fields[<?php echo $i; ?>]" value="<?php echo $controller->getField($i); ?>">
								<?php }
								if (preg_match("/:DATE$/", $currentFieldSearch)>0) {
									?>
									<select name="plusminus[<?php echo $i; ?>]">
										<option value=""><?php echo WT_I18N::translate('Exact date'); ?></option>
										<option value="2" <?php if (!empty($controller->plusminus[$i]) && $controller->plusminus[$i]==2) echo " selected=\"selected\""; ?>><?php echo WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 2, 2); ?></option>
										<option value="5" <?php if (!empty($controller->plusminus[$i]) && $controller->plusminus[$i]==5) echo "selected=\"selected\""; ?>><?php echo WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 5, 5); ?></option>
										<option value="10" <?php if (!empty($controller->plusminus[$i]) && $controller->plusminus[$i]==10) echo "selected=\"selected\""; ?>><?php echo WT_I18N::plural('&plusmn;%d year','&plusmn;%d years', 10, 10); ?></option>
									</select>
								<?php } ?>
							</td>
							<?php
							//-- relative fields
							if ($i==0 && $fct>4) {
								$j=$fct;
								// Get the current options for Father's and Mother's name searches
								$fatherGivnOption = 'SDX';
								$fatherSurnOption = 'SDX';
								$motherGivnOption = 'SDX';
								$motherSurnOption = 'SDX';
								for ($k=0; $k<$fct; $k++) {
									$searchField = $controller->getField($k);
									$searchOption = substr($searchField, 20); // Assume we have something like "FAMC:HUSB:NAME:GIVN:foo"
									switch (substr($searchField, 0, 20)) {
									case 'FAMC:HUSB:NAME:GIVN:':
										$fatherGivnOption = $searchOption;
										break;
									case 'FAMC:HUSB:NAME:SURN:':
										$fatherSurnOption = $searchOption;
										break;
									case 'FAMC:WIFE:NAME:GIVN:':
										$motherGivnOption = $searchOption;
										break;
									case 'FAMC:WIFE:NAME:SURN:':
										$motherSurnOption = $searchOption;
										break;
									}
								}
								?>
								<td rowspan="100" class="list_value">
									<table>
										<!--  father -->
										<tr>
											<td colspan="2" class="facts_label03" style="text-align:center;">
												<?php echo WT_I18N::translate('Father'); ?>
											</td>
										</tr>
										<tr>
											<td class="list_label">
												<?php echo WT_Gedcom_Tag::getLabel('GIVN'); ?>
											</td>
											<td class="list_value">
												<input type="text" name="values[<?php echo $j; ?>]" value="<?php echo $controller->getValue($controller->getIndex('FAMC:HUSB:NAME:GIVN:'.$fatherGivnOption)); ?>">
												<select name="fields[<?php echo $j; ?>]">
													<option value="FAMC:HUSB:NAME:GIVN:EXACT"<?php if ($fatherGivnOption == 'EXACT') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Exact'); ?></option>
													<option value="FAMC:HUSB:NAME:GIVN:BEGINS"<?php if ($fatherGivnOption == 'BEGINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Begins with'); ?></option>
													<option value="FAMC:HUSB:NAME:GIVN:CONTAINS"<?php if ($fatherGivnOption == 'CONTAINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Contains'); ?></option>
													<option value="FAMC:HUSB:NAME:GIVN:SDX"<?php if ($fatherGivnOption == 'SDX') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Sounds like'); ?></option>
												</select>
											</td>
										</tr>
										<tr>
											<?php $j++; ?>
											<td class="list_label">
												<?php echo WT_Gedcom_Tag::getLabel('SURN'); ?>
											</td>
											<td class="list_value">
												<input type="text" name="values[<?php echo $j; ?>]" value="<?php echo $controller->getValue($controller->getIndex('FAMC:HUSB:NAME:SURN:'.$fatherSurnOption)); ?>">
												<select name="fields[<?php echo $j; ?>]">
													<option value="FAMC:HUSB:NAME:SURN:EXACT"<?php if ($fatherSurnOption == 'EXACT') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Exact'); ?></option>
													<option value="FAMC:HUSB:NAME:SURN:BEGINS"<?php if ($fatherSurnOption == 'BEGINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Begins with'); ?></option>
													<option value="FAMC:HUSB:NAME:SURN:CONTAINS"<?php if ($fatherSurnOption == 'CONTAINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Contains'); ?></option>
													<option value="FAMC:HUSB:NAME:SURN:SDX"<?php if ($fatherSurnOption == 'SDX') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Sounds like'); ?></option>
												</select>
											</td>
										</tr>
										<!--  mother -->
										<?php $j++; ?>
										<tr><td colspan="2">&nbsp;</td></tr>
										<tr>
											<td colspan="2" class="facts_label03" style="text-align:center;">
												<?php echo WT_I18N::translate('Mother'); ?>
											</td>
										</tr>
										<tr>
											<td class="list_label">
												<?php echo WT_Gedcom_Tag::getLabel('GIVN'); ?>
											</td>
											<td class="list_value">
												<input type="text" name="values[<?php echo $j; ?>]" value="<?php echo $controller->getValue($controller->getIndex('FAMC:WIFE:NAME:GIVN:'.$motherGivnOption)); ?>">
												<select name="fields[<?php echo $j; ?>]">
													<option value="FAMC:WIFE:NAME:GIVN:EXACT"<?php if ($motherGivnOption == 'EXACT') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Exact'); ?></option>
													<option value="FAMC:WIFE:NAME:GIVN:BEGINS"<?php if ($motherGivnOption == 'BEGINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Begins with'); ?></option>
													<option value="FAMC:WIFE:NAME:GIVN:CONTAINS"<?php if ($motherGivnOption == 'CONTAINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Contains'); ?></option>
													<option value="FAMC:WIFE:NAME:GIVN:SDX"<?php if ($motherGivnOption == 'SDX') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Sounds like'); ?></option>
												</select>
											</td>
											<?php $j++; ?>
										</tr>
										<tr>
											<td class="list_label">
												<?php echo WT_Gedcom_Tag::getLabel('SURN'); ?>
											</td>
											<td class="list_value">
												<input type="text" name="values[<?php echo $j; ?>]" value="<?php echo $controller->getValue($controller->getIndex('FAMC:WIFE:NAME:SURN:'.$motherSurnOption)); ?>">
												<select name="fields[<?php echo $j; ?>]">
													<option value="FAMC:WIFE:NAME:SURN:EXACT"<?php if ($motherSurnOption == 'EXACT') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Exact'); ?></option>
													<option value="FAMC:WIFE:NAME:SURN:BEGINS"<?php if ($motherSurnOption == 'BEGINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Begins with'); ?></option>
													<option value="FAMC:WIFE:NAME:SURN:CONTAINS"<?php if ($motherSurnOption == 'CONTAINS') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Contains'); ?></option>
													<option value="FAMC:WIFE:NAME:SURN:SDX"<?php if ($motherSurnOption == 'SDX') echo " selected=\"selected\""; ?>><?php echo WT_I18N::translate('Sounds like'); ?></option>
												</select>
											</td>
											<?php $j++; ?>
										</tr>
										<!-- spouse -->
										<!--tr-->
										<?php $j++; ?>
										<!--/tr-->
									</table>
								</td>
							<?php } ?>
						</tr>

						<?php } ?>
					</table>
					<p class="buttons">
						<button class="btn btn-primary" type="submit">
							<i class="fa fa-search"></i>
							<?php echo WT_I18N::translate('Search'); ?>
						</button>
						<button class="btn btn-primary" onclick="addFields(); return false;">
							<i class="fa fa-plus"></i>
							<?php echo WT_I18N::translate('Add more fields'); ?>
						</button>
					</p>
				</form>
			</div>
		<?php }
		echo $somethingPrinted = $controller->printResults(); ?>
	</div> <!-- close div id "search-tabs" -->
</div> <!-- close div id "search-page" -->

<?php
function search_trees() {
	// If more than one GEDCOM, switching is allowed AND DB mode is set, let the user select
	if ((count(WT_Tree::getAll()) > 1) && WT_Site::preference('ALLOW_CHANGE_GEDCOM')) {
		// More Than 3 Gedcom Filess enable elect all & select none buttons
		if (count(WT_Tree::getAll())>3) {
			echo '<div class="label">&nbsp;</div>
				<div class="value">
				<input type="button" value="', /* I18N: select all (of the family trees) */ WT_I18N::translate('select all'), '" onclick="jQuery(\'#search_trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', true);});return false;">
					<input type="button" value="', /* I18N: select none (of the family trees) */ WT_I18N::translate('select none'), '" onclick="jQuery(\'#search_trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', false);});return false;">';
					// More Than 10 Gedcom Files enable invert selection button
					if (count(WT_Tree::getAll())>10) {
						echo '<input type="button" value="', WT_I18N::translate('invert selection'), '" onclick="jQuery(\'#search_trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', !jQuery(this).attr(\'checked\'));});return false;">';
					}
				echo '</div>';
		}
		echo '<div class="label">' , WT_I18N::translate('Family trees'), '</div>
		<div id="search_trees" class="value">';
			//-- sorting menu by gedcom filename
			foreach (WT_Tree::getAll() as $tree) {
				$str = str_replace(array (".", "-", " "), array ("_", "_", "_"), $tree->tree_name);
				$controller->inputFieldNames[] = "$str";
				echo '<p><input type="checkbox" ';
				if (isset ($_REQUEST["$str"])) {
					echo 'checked="checked" ';
				}
				echo 'value="yes" id="checkbox_', $tree->tree_id , '" name="', $str, '"><label for="checkbox_', $tree->tree_id , '">', $tree->tree_title_html, '</label></p>', "\n";
			}
		echo '</div>';
	}
}
