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

define('KT_SCRIPT_NAME', 'inverselink.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

//-- page parameters and checking
$linktoid = safe_GET_xref('linktoid');
$mediaid  = safe_GET_xref('mediaid');
$linkto   = safe_GET     ('linkto', array('person', 'source', 'family', 'manage', 'repository', 'note'));
$action   = safe_GET     ('action', KT_REGEX_ALPHA, 'choose');

//-- extra page parameters and checking
$more_links  = KT_Filter::get('more_links');
$exist_links = KT_Filter::get('exist_links');
$gid         = KT_Filter::get('gid', KT_REGEX_XREF);
$update_CHAN = KT_Filter::get('preserve_last_changed');

$paramok =  true;
if (!empty($linktoid)) {
	$paramok = KT_GedcomRecord::getInstance($linktoid)->canDisplayDetails();
}

$controller = new KT_Controller_Page();

$controller
	->requireEditorLogin()
	->setPageTitle(KT_I18N::translate('Link to an existing media object'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
	');

if ($action == 'choose' && $paramok) {
	$record = ''; ?>
	<script>
		// Javascript variables
		var id_empty = "<?php echo KT_I18N::translate('When adding a Link, the ID field cannot be empty.'); ?>";

		function blankwin() {
			if (document.getElementById('gid').value == "" || document.getElementById('gid').value.length<=1) {
				alert(id_empty);
			} else {
				var iid = document.getElementById('gid').value;
				jQuery.post('action.php',{action:'lookup_name',iid},function(iname){insertRowToTable(iid, iname)});
			}
		}
	</script>

	<div id="inverselink-page">
		<h2><?php echo KT_I18N::translate('Link to an existing media object'); ?></h2>
		<div class="inverselink-page-left">
			<form class="medialink" name="link" method="get" action="inverselink.php">
				<input type="hidden" name="action" value="update">
				<?php if (!empty($mediaid)) { ?>
					<input type="hidden" name="mediaid" value="<?php echo $mediaid; ?>">
				<?php }
				if (!empty($linktoid)) { ?>
					<input type="hidden" name="linktoid" value="<?php echo $linktoid; ?>">
				<?php } ?>
				<input type="hidden" name="linkto" value="<?php echo $linkto; ?>">
				<input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
				<?php if (!isset($linktoid)) {
					$linktoid = "";
				}
				if ($linkto == "manage") { ?>
					<div class="LINK_factdiv">
						<label><?php echo KT_I18N::translate('Media'); ?></label>
						<div class="input">
							<div>
								<?php $filename = KT_DB::prepare("SELECT m_filename FROM `##media` where m_id=? AND m_file=?")
									->execute(array($mediaid, KT_GED_ID))
									->fetchOne();
								$media = KT_Media::getInstance($mediaid);
								echo $media->displayImage(); ?>
							</div>
							<div class="bold">
								<?php $title = KT_DB::prepare("SELECT m_titl FROM `##media` where m_id=? AND m_file=?")->execute(array($mediaid, KT_GED_ID))->fetchOne();
								echo $title ? $title : $mediaid; ?>
							</div>
						</div>
					</div>
					<div class="LINK_factdiv">
						<label><?php echo KT_I18N::translate('Links'); ?></label>
						<div class="input">
							<table id="existLinkTbl">
									<tr>
										<th>#</th>
										<th><?php echo KT_I18N::translate('Record'); ?></th>
										<th><?php echo KT_I18N::translate('Name'); ?></th>
										<th><?php echo KT_I18N::translate('Keep'); ?></th>
										<th><?php echo KT_I18N::translate('Remove'); ?></th>
										<th><?php echo KT_I18N::translate('Navigator'); ?></th>
									</tr>
									<?php $links = array_merge(
										$media->fetchLinkedIndividuals(),
										$media->fetchLinkedFamilies(),
										$media->fetchLinkedSources()
									);
									$i = 1;
									foreach ($links as $record) { ?>
										<tr>
											<td>
												<span><?php echo $i++; ?></span>
											</td>
											<td id="existId_<?php echo $i; ?>" class="row2">
												<span><?php echo $record->getXref(); ?></span>
											</td>
											<td>
												<span><?php echo $record->getFullName(); ?></span>
											</td>
											<td class="center">
												<input title="<?php echo KT_I18N::translate('Keep Link in list'); ?>" type="radio" id="<?php echo $record->getXref(); ?>_off" name="<?php echo $record->getXref(); ?>" checked>
											</td>
											<td class="center">
												<input  title="<?php echo KT_I18N::translate('Remove Link from list'); ?>" type="radio" id="<?php echo $record->getXref(); ?>_on" name="<?php echo $record->getXref(); ?>">
											</td>
											<?php if ($record instanceof KT_Person) { ?>
												<td class="center">
													<a href="#" class="icon-button_indi" title="<?php echo KT_I18N::translate('Family navigator'); ?>" name="family_'<?php echo $record->getXref(); ?>'" onclick="openFamNav('<?php echo $record->getXref(); ?>'); return false;"></a>
												</td>
											<?php } elseif ($record instanceof KT_Family) {
												if ($record->getHusband()) {
													$head = $record->getHusband()->getXref();
												} elseif ($record->getWife()) {
													$head = $record->getWife()->getXref();
												} else {
													$head = '';
												} ?>
												<td class="center">
													<a href="#" class="icon-button_family" title="<?php echo KT_I18N::translate('Family navigator'); ?>" name="family_'<?php echo $record->getXref(); ?>'" onclick="openFamNav('<?php echo $head; ?>');"></a>
												</td>
											<?php } else { ?>
												<td><!-- // Show No Icon --></td>
											<?php } ?>
										</tr>
									<?php } ?>
								</table>
						</div>
					</div>
					<div class="LINK_factdiv">
						<label><?php echo KT_I18N::translate('Add links'); ?></label>
						<div class="input">
							<?php if ($linktoid !== "") {
								$record = KT_Person::getInstance($linktoid);
								echo $record->getFullName();
							} ?>
							<p>
								<input type="text" data-autocomplete-type="IFS" name="gid" id="gid" value="">
								<input type="hidden" name="idName" id="idName" value="Name of ID">
								<button name="addLink" value="" type="button" onclick="blankwin(); return false;">
									<i class="fa fa-plus"></i>
									<?php echo KT_I18N::translate('Add'); ?>
								</button>
							</p>
							<p><?php echo KT_I18N::translate('Enter or search for the ID of the person, family, or source to which this media item should be linked.'); ?></p>
							<!-- Add new links area -->
							<table id="addlinkQueue">
								<thead>
									<tr>
										<th>#</td>
										<th><?php echo KT_I18N::translate('Record'); ?></th>
										<th style="min-width: 300px; width: 60%">
											<?php echo KT_I18N::translate('Name'); ?>
										</th>
										<th><?php echo KT_I18N::translate('Remove'); ?></th>
										<th><?php echo KT_I18N::translate('Navigator'); ?></th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
					<?php // Admin Option CHAN log update override =======================
						echo $record ? no_update_chan($record) : '';
					?>
					<input type="hidden" name="more_links" value="No_Values">
					<input type="hidden" name="exist_links" value="No_Values">
					<p id="save-cancel">
						<button class="btn btn-primary" type="submit" onclick="shiftlinks();">
							<i class="fa fa-save"></i>
							<?php echo KT_I18N::translate('Save'); ?>
						</button>
						<button class="btn btn-primary" type="button"  onclick="window.close();">
							<i class="fa fa-times"></i>
							<?php echo KT_I18N::translate('close'); ?>
						</button>
					</p>
				<?php } else { ?>
					<div class="LINK_factdiv">
						<label><?php echo KT_I18N::translate('Media'); ?></label>
						<div class="input">
							<input data-autocomplete-type="OBJE" type="text" name="mediaid" id="mediaid">
						</div>
					</div>
					<div class="LINK_factdiv">
						<label>
							<?php echo KT_I18N::translate('Individual'); ?>
						</label>
						<div class="input">
							<?php $record = KT_Person::getInstance($linktoid);
							echo $record->format_list('span', false, $record->getFullName()); ?>
						</div>
					</div>
					<?php // Admin Option CHAN log update override =======================
						echo $record ? no_update_chan($record) : '';
					?>
					<p id="save-cancel">
						<button class="btn btn-primary" type="submit">
							<i class="fa fa-link"></i>
							<?php echo KT_I18N::translate('Set link'); ?>
						</button>
						<button class="btn btn-primary" type="button" onclick="closePopupAndReloadParent();">
							<i class="fa fa-times"></i>
							<?php echo KT_I18N::translate('close'); ?>
						</button>
					</p>
				<?php } ?>
			</form>
		</div>
		<div class="inverselink-page-right">
			<div id="fam_navigator"></div>
		</div>
		<div class="clearfloat"></div>
	</div>
<?php } elseif ($action == "update" && $paramok) {
	if ($linkto == "manage") {
		// Unlink records indicated by radio button =========
		if ($exist_links) {
			foreach (explode(',', $exist_links) as $remLinkId) {
				unlinkMedia($remLinkId, 'OBJE', $mediaid, 1, $update_CHAN != 'no_change');
			}
		}
		// Add new Links ====================================
		if ($more_links) {
			foreach (explode(',', $more_links) as $addLinkId) {
				linkMedia($mediaid, $addLinkId, 1, $update_CHAN != 'no_change');
			}
		}
	} else {
		linkMedia($mediaid, $linktoid, 1, $update_CHAN != 'no_change');
	}
	$controller->addInlineJavascript('closePopupAndReloadParent();');
}
?>
<script>
	function addlinks(iname) {
		// iid=document.getElementById('gid').value;
		if (document.getElementById('gid').value == "") {
			alert(id_empty);
		} else {
			addmedia_links(document.getElementById('gid'), document.getElementById('gid').value, iname );
			return false;
		}
	}

	function openFamNav(id) {
		winNav = 'edit_interface.php?action=addmedia_links&noteid=newnote&pid=' + id;
		jQuery('#fam_navigator').load(winNav, function() {
			jQuery('#fam_navigator').hide().slideDown('slow');
		});
	}

	function fam_nav_close() {
		jQuery('#fam_navigator').hide();
	};

	var ifamily = "<?php echo KT_I18N::translate('Family navigator'); ?>";
	var remove = "<?php echo KT_I18N::translate('Remove'); ?>";
	var INPUT_NAME_PREFIX	= 'InputCell_'; // this is being set via script
	var RADIO_NAME			= "totallyrad"; // this is being set via script
	var TABLE_NAME			= 'addlinkQueue'; // this should be named in the HTML
	var ROW_BASE			= 1; // first number (for display)
	var hasLoaded			= false;

	window.onload = fillInRows;

	function fillInRows() {
		hasLoaded = true;
	}

	// CONFIG:
	// myRowObject is an object for storing information about the table rows
	//function myRowObject(zero, one, two, three, four, five, six, seven, eight, nine, ten, cb, ra)
	function myRowObject(zero, one, two, cb, ra) {
		this.zero	 = zero;	 // text object
		this.one	 = one;		 // input text object
		this.two	 = two;		 // input text object
		this.cb		 = cb;		 // input checkbox object
		this.ra		 = ra;		 // input radio object
	}

	/*
	 * insertRowToTable
	 * Insert and reorder
	 */
	function insertRowToTable(pid, nam) {
		if (hasLoaded) {
			var tbl = document.getElementById(TABLE_NAME);
			var rowToInsertAt = "";

			// Get links list ====================================
			var links 	= document.getElementById('existLinkTbl');
			var numrows = links.rows.length;
			var strRow = '';
			for (var i=1; i<numrows; i++) {
				if (document.all) { // If Internet Explorer
					strRow += (strRow==''?'':', ') + links.rows[i].cells[1].innerText;
				} else {
					strRow += (strRow==''?'':', ') + links.rows[i].cells[1].textContent;
				}
			}
			strRow += (strRow==''?'':', ');

			//Check if id exists in Links list =================================
			if (strRow.match(pid+',')!= pid+',') {
				// alert('NO MATCH');
			} else {
				rowToInsertAt = 'EXIST' ;
			}

			// Check if id exists in "Add links" list ==========================
			for (var i=0; i<tbl.tBodies[0].rows.length; i++) {
				var cellText;
				if (typeof tbl.tBodies[0].rows[i].myRow.one.textContent !== "undefined") {
					cellText = tbl.tBodies[0].rows[i].myRow.one.textContent;
				} else {
					cellText = tbl.tBodies[0].rows[i].myRow.one.innerText;
				}
				if (cellText==pid) {
					rowToInsertAt = 'EXIST';
				} else
				if (tbl.tBodies[0].rows[i].myRow && tbl.tBodies[0].rows[i].myRow.ra.getAttribute('type') == 'radio' && tbl.tBodies[0].rows[i].myRow.ra.checked) {
					rowToInsertAt = i;
					break;
				}
			}

			// If Link does not exist then add it, or show alert ===============
			if (rowToInsertAt!='EXIST') {
				rowToInsertAt = i;
				addRowToTable(rowToInsertAt, pid, nam);
				reorderRows(tbl, rowToInsertAt);
			}

		}
	}

	function removeHTMLTags(htmlString) {
		if (htmlString) {
			var mydiv = document.createElement("div");
				mydiv.innerHTML = htmlString;
			if (typeof mydiv.textContent !== "undefined") {
				return mydiv.textContent;
			}
			return mydiv.innerText;
		}
	}

	/*
	 * addRowToTable
	 * Inserts at row 'num', or appends to the end if no arguments are passed in. Don't pass in empty strings.
	 */
	function addRowToTable(num, pid, nam) {
			if (hasLoaded) {
				var tbl = document.getElementById(TABLE_NAME);
				var nextRow = tbl.tBodies[0].rows.length;
				var iteration = nextRow + ROW_BASE;

				if (num == null) {
					num = nextRow;
				} else {
					iteration = num + ROW_BASE;
				}

				// add the row
				var row = tbl.tBodies[0].insertRow(num);
				// cell 0 - Count
				var cell0 = row.insertCell(0);
				var textNode = document.createTextNode(iteration);
				cell0.appendChild(textNode);

				var cell1 = row.insertCell(1);
				if (pid == '') {
					var txtInp1 = document.createElement('div');
					txtInp1.setAttribute('type', 'checkbox');
					if (txtInp1.checked!='') {
						txtInp1.setAttribute('value', 'no');
					} else {
						txtInp1.setAttribute('value', 'add');
					}
				} else {
					var txtInp1 = document.createElement('div');
					txtInp1.setAttribute('type', 'text');
					if (typeof txtInp1.textContent !== "undefined") {
						txtInp1.textContent = pid;
					} else {
						txtInp1.innerText = pid;
					}
				}
					txtInp1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_1');
				cell1.appendChild(txtInp1);

				// cell 2 - Name
				var cell2 = row.insertCell(2);
				var txtInp2 = document.createElement('div');
					txtInp2.setAttribute('type', 'text');
					txtInp2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_2');
					txtInp2.innerHTML = removeHTMLTags(unescape(nam));
				cell2.appendChild(txtInp2);

				// cell btn - remove img button
				var cellbtn = row.insertCell(3);
					cellbtn.setAttribute('align', 'center');
				var btnEl = document.createElement('a');
					btnEl.setAttribute('href', '#');
					btnEl.setAttribute('class', 'fa fa-times');
					btnEl.setAttribute('alt', remove);
					btnEl.setAttribute('title', remove);
					btnEl.onclick = function () {deleteCurrentRow(this)};
				cellbtn.appendChild(btnEl);

				// cell btn - family img button
				var cellbtn2 = row.insertCell(4);
					cellbtn2.setAttribute('align', 'center');
				if (pid.match("I")=="I" || pid.match("i")=="i") {
					var btn2El = document.createElement('a');
						btn2El.setAttribute('href', '#');
						btn2El.setAttribute('class', 'icon-button_indi');
						btn2El.setAttribute('alt', ifamily);
						btn2El.setAttribute('title', ifamily);
						btn2El.onclick = function () {openFamNav(pid)};
					cellbtn2.appendChild(btn2El);
				} else if (pid.match("F")=="F" || pid.match("f")=="f") {
					var btn2El = document.createElement('a');
						btn2El.setAttribute('href', '#');
						btn2El.setAttribute('class', 'icon-button_family');
						btn2El.setAttribute('alt', ifamily);
						btn2El.setAttribute('title', ifamily);
						btn2El.onclick = function () {openFamNav(pid)};
					cellbtn2.appendChild(btn2El);
				} else {
					// Show No Icon
				}

				// cell cb - input checkbox
				var cbEl = document.createElement('input');
				cbEl.type = "hidden";

				// cell ra - input radio
				//var cellra = row.insertCell(5);
				var cellra = document.createElement('input');
				cellra.type = "hidden";

				// Pass in the elements you want to reference later
				// Store the myRow object in each row
				row.myRow = new myRowObject(textNode, txtInp1, txtInp2, cbEl, cellra);
			}
	}

	// CONFIG: this entire function is affected by myRowObject settings
	// If there isn't a checkbox in your row, then this function can't be used.
	function deleteChecked() {
		if (hasLoaded) {
			var checkedObjArray = new Array();
			var cCount = 0;

			var tbl = document.getElementById(TABLE_NAME);
			for (var i=0; i<tbl.tBodies[0].rows.length; i++) {
				if (tbl.tBodies[0].rows[i].myRow && tbl.tBodies[0].rows[i].myRow.cb.getAttribute('type') == 'checkbox' && tbl.tBodies[0].rows[i].myRow.cb.checked) {
					checkedObjArray[cCount] = tbl.tBodies[0].rows[i];
					cCount++;
				}
			}
			if (checkedObjArray.length > 0) {
				var rIndex = checkedObjArray[0].sectionRowIndex;
				deleteRows(checkedObjArray);
				reorderRows(tbl, rIndex);
			}
		}
	}

	// If there isn't an element with an onclick event in your row, then this function can't be used.
	function deleteCurrentRow(obj) {
		if (hasLoaded) {
			var delRow = obj.parentNode.parentNode;
			var tbl = delRow.parentNode.parentNode;
			var rIndex = delRow.sectionRowIndex;
			var rowArray = new Array(delRow);
			deleteRows(rowArray);
			reorderRows(tbl, rIndex);
		}
	}

	function reorderRows(tbl, startingIndex) {
		if (hasLoaded) {
			if (tbl.tBodies[0].rows[startingIndex]) {
				var count = startingIndex + ROW_BASE;
				for (var i=startingIndex; i<tbl.tBodies[0].rows.length; i++) {

					// CONFIG: next line is affected by myRowObject settings
					tbl.tBodies[0].rows[i].myRow.zero.data	 = count; // text

					tbl.tBodies[0].rows[i].myRow.one.id		 = INPUT_NAME_PREFIX + count + '_1'; // input text
					tbl.tBodies[0].rows[i].myRow.two.id 	 = INPUT_NAME_PREFIX + count + '_2'; // input text

					tbl.tBodies[0].rows[i].myRow.one.name	 = INPUT_NAME_PREFIX + count + '_1'; // input text
					tbl.tBodies[0].rows[i].myRow.two.name 	 = INPUT_NAME_PREFIX + count + '_2'; // input text

					// tbl.tBodies[0].rows[i].myRow.cb.value = count; // input checkbox
					tbl.tBodies[0].rows[i].myRow.ra.value = count; // input radio

					// CONFIG: requires class named classy0 and classy1
					tbl.tBodies[0].rows[i].className = 'classy' + (count % 2);

					count++;
				}
			}
		}
	}

	function deleteRows(rowObjArray) {
		if (hasLoaded) {
			for (var i=0; i<rowObjArray.length; i++) {
				var rIndex = rowObjArray[i].sectionRowIndex;
				rowObjArray[i].parentNode.deleteRow(rIndex);
			}
		}
	}

	function parseAddLinks() {
		// start with the "newly added" ID.
		var str = document.getElementById('gid').value;
		// Add in the "keep" IDs.
		var tbl = document.getElementById('addlinkQueue');
		for (var i=1; i<tbl.rows.length; i++) { // start at i=1 because we need to avoid header
			var tr = tbl.rows[i];
			if (typeof tr.cells[1].childNodes[0].textContent !== "undefined") {
				str += (str==''?'':',') + tr.cells[1].childNodes[0].textContent;
			} else {
				str += (str==''?'':',') + tr.cells[1].childNodes[0].innerHTML;
			}
		}
		document.link.more_links.value = str;
	}

	function parseRemLinks() {
		var remstr = "";
		var tbl = document.getElementById('existLinkTbl');
		for (var i=1; i<tbl.rows.length; i++) { // start at i=1 because we need to avoid header
			var input = tbl.rows[i].getElementsByTagName( 'input' );
			if (input[1].checked)  {
				remstr += (remstr == '' ? '' : ',') + input[1].name;
			}
		}
		document.link.exist_links.value = remstr;
	}

	function shiftlinks() {
		parseRemLinks();
		parseAddLinks();
		if (winNav) {
			fam_nav_close();
		}
	}

</script>

<?php
/**
* unLink Media ID to Indi, Family, or Source ID
*
* @param  string  $mediaid Media ID to be unlinked.
* @param string $linktoid Indi, Family, or Source ID that the Media ID should be unlinked from.
* @param $linenum should be ALWAYS set to 'OBJE'.
* @param int $level Level where the Media Object reference should be removed from (not used)
* @param boolean $chan Whether or not to update/add the CHAN record
*
* @return  bool success or failure
*/
function unlinkMedia($linktoid, $linenum, $mediaid, $level=1, $chan=true) {
	if (empty($level)) $level = 1;
	if ($level != 1) return false; // Level 2 items get unlinked elsewhere (maybe ??)
	// find Indi, Family, or Source record to unlink from
	$gedrec = find_gedcom_record($linktoid, KT_GED_ID, true);
	//-- when deleting/unlinking a media link
	//-- $linenum comes as an OBJE and the $mediaid to delete should be set
	if (!is_numeric($linenum)) {
		$newged = remove_media_subrecord($gedrec, $mediaid);
	} else {
		$newged = remove_subline($gedrec, $linenum);
	}
	replace_gedrec($linktoid, KT_GED_ID, $newged, $chan);
}
