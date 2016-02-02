<?php
// Media Link Assistant Control module for webtrees
//
// Media Link information about an individual
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

// GEDFact Media assistant replacement code for inverselink.php: ===========================

//-- extra page parameters and checking
$more_links = safe_REQUEST($_REQUEST, 'more_links', WT_REGEX_UNSAFE);
$exist_links = safe_REQUEST($_REQUEST, 'exist_links', WT_REGEX_UNSAFE);
$gid = safe_GET_xref('gid');
$update_CHAN = safe_REQUEST($_REQUEST, 'preserve_last_changed', WT_REGEX_UNSAFE);

$controller
	->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
	->addInlineJavascript('autocomplete();');

$paramok =  true;
if (!empty($linktoid)) $paramok = WT_GedcomRecord::getInstance($linktoid)->canDisplayDetails();

if ($action == 'choose' && $paramok) { ?>
	<script>
	// Javascript variables
	var id_empty = "<?php echo WT_I18N::translate('When adding a Link, the ID field cannot be empty.'); ?>";

	function blankwin() {
		if (document.getElementById('gid').value == "" || document.getElementById('gid').value.length<=1) {
			alert(id_empty);
		} else {
			var iid = document.getElementById('gid').value;
			var winblank = window.open('module.php?mod=GEDFact_assistant&mod_action=media_query_3a&iid='+iid, 'winblank', 'top=100, left=200, width=400, height=20, toolbar=0, directories=0, location=0, status=0, menubar=0, resizable=1, scrollbars=1');
		}
	}

	var GEDFact_assist = 'installed';
	</script>
	<style>
		#link-page {margin-bottom: 50px; overflow: hidden;}
		#link-page .media-title{font-weight: 600; padding: 0 5px; vertical-align: top; width: 25%;}
		#link-page .media-item{}
		#link-page #existLinkTbl, #link-page #addlinkQueue {border-collapse: separate; border-spacing: 1px; table-layout: fixed; max-width: 450px;}
		#link-page #existLinkTbl th, #link-page #addlinkQueue th {background: #ddd; border: 1px solid; padding: 4px; text-align: center;}
		#link-page .last_change {clear: both; max-width: 700px;}
		#link-page .last_change label {font-weight: 600; line-height: 18px; width: 25%;}
		#link-page .input {display: inline-block; padding: 0 5px;}
		#link-page .preserve td {padding-top: 30px;}
	</style>

	<div id="link-page">
		<form class="medialink" name="link" method="get" action="inverselink.php">
			<input type="hidden" name="action" value="update">
			<?php if (!empty($mediaid)) { ?>
				<input type="hidden" name="mediaid" value="', $mediaid, '">
			<?php }
			if (!empty($linktoid)) { ?>
				<input type="hidden" name="linktoid" value="', $linktoid, '">
			<?php } ?>
			<input type="hidden" name="linkto" value="', $linkto, '">
			<input type="hidden" name="ged" value="', $GEDCOM, '">
			<table>
				<tr>
					<td colspan="2"><h2><?php echo WT_I18N::translate('Link to an existing media object'); ?></h2></td>
				</tr>
				<tr>
					<td class="media-title"><?php echo WT_I18N::translate('Media'); ?></td>
					<td class="media-item">
						<?php if (!empty($mediaid)) {
							//-- Get the title of this existing Media item
							$title=
								WT_DB::prepare("SELECT m_titl FROM `##media` where m_id=? AND m_file=?")
								->execute(array($mediaid, WT_GED_ID))
								->fetchOne();
							if ($title) {
								echo '<b>', $title, '</b>';
							} else {
								echo '<b>', $mediaid, '</b>';
							} ?>
							<table>
								<tr>
									<td>
										<?php //-- Get the filename of this existing Media item
										$filename=
											WT_DB::prepare("SELECT m_filename FROM `##media` where m_id=? AND m_file=?")
											->execute(array($mediaid, WT_GED_ID))
											->fetchOne();
										$media=WT_Media::getInstance($mediaid);
										echo $media->displayImage(); ?>
									</td>
								</tr>
							</table>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="media-title"><?php echo WT_I18N::translate('Links'); ?></td>
					<td class="media-item">
						<table>
							<tr>
								<td>
									<table id="existLinkTbl">
										<tr>
											<th style="">#</td>
											<th style=""><?php echo WT_I18N::translate('Record'); ?></th>
											<th style="min-width: 300px; width: 60%">
												<?php echo WT_I18N::translate('Name'); ?>
											</th>
											<th style=""><?php echo WT_I18N::translate('Keep'); ?></th>
											<th style=""><?php echo WT_I18N::translate('Remove'); ?></th>
											<th style=""><?php echo WT_I18N::translate('Navigator'); ?></th>
										</tr>
										<?php $links = array_merge(
											$media->fetchLinkedIndividuals(),
											$media->fetchLinkedFamilies(),
											$media->fetchLinkedSources()
										);
										$i=1;
										foreach ($links as $record) { ?>
											<tr>
												<td>
													<?php echo $i++; ?>
												</td>
												<td id="existId_<?php echo $i; ?>" class="row2">
													<?php echo $record->getXref(); ?>
												</td>
												<td>
													<?php echo $record->getFullName(); ?>
												</td>
												<td align='center'>
													<input alt="<?php echo WT_I18N::translate('Keep Link in list'); ?>" title="<?php echo WT_I18N::translate('Keep Link in list'); ?>" type='radio' id="<?php echo $record->getXref(); ?>_off" name="<?php echo $record->getXref(); ?>" checked>
												</td>
												<td align='center'>
													<input alt="<?php echo WT_I18N::translate('Remove Link from list'); ?>" title="<?php echo WT_I18N::translate('Remove Link from list'); ?>" type='radio' id="<?php echo $record->getXref(); ?>_on" name="<?php echo $record->getXref(); ?>">
												</td>
												<?php if ($record->getType()=='INDI') { ?>
													<td align="center"><a href="#" class="icon-button_family" title="<?php echo WT_I18N::translate('Family navigator'); ?>" name="family_'<?php echo $record->getXref(); ?>'" onclick="openFamNav('<?php echo $record->getXref(); ?>'); return false;"></a></td>
												<?php } elseif ($record->getType()=='FAM') {
													if ($record->getHusband()) {
														$head=$record->getHusband()->getXref();
													} elseif ($record->getWife()) {
														$head=$record->getWife()->getXref();
													} else {
														$head='';
													} ?>
													<td align="center"><a href="#" class="icon-button_family" title="<?php echo WT_I18N::translate('Family navigator'); ?>" name="family_'<?php echo $record->getXref(); ?>'" onclick="openFamNav('<?php echo $head; ?>');"></a></td>

												<?php } else { ?>
													<td></td>
												<?php } ?>
											</tr>
										<?php } ?>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
<?php // } ?>

				<?php if (!isset($linktoid)) { $linktoid = ""; } ?>

				<tr>
					<td class="media-title">
						<?php echo WT_I18N::translate('Add links'); ?>
					</td>
					<td class="media-item">
						<?php if ($linktoid=="") {
							// ----
						} else {
							$record = WT_Person::getInstance($linktoid); ?>
							<b><?php echo $record->getFullName(); ?></b>
						<?php } ?>
						<table>
							<tr>
								<td>
									<input type="text" data-autocomplete-type="IFS" name="gid" id="gid" size="6" value="">
									<?php // echo ' Enter Name or ID &nbsp; &nbsp; &nbsp; <b>OR</b> &nbsp; &nbsp; &nbsp;Search for ID '; ?>
								</td>
								<td style=" padding-bottom:2px; vertical-align:middle">
									&nbsp;
									<?php if (isset($WT_IMAGES["add"])) { ?>
										<img style="border-style:none;" src="<?php echo $WT_IMAGES["add"]; ?>" alt="<?php echo WT_I18N::translate('Add'); ?>" title="<?php echo WT_I18N::translate('Add'); ?>" align="middle" name="addLink" value="" onclick="blankwin(); return false;">
										<?php } else { ?>
										<button name="addLink" value="" type="button" onclick="blankwin(); return false;">
											<?php echo WT_I18N::translate('Add'); ?>
										</button>
									<?php }
									echo ' ', print_findindi_link('gid');
									echo ' ', print_findfamily_link('gid');
									echo ' ', print_findsource_link('gid'); ?>
								</td>
							</tr>
						</table>
						<sub><?php echo WT_I18N::translate('Enter or search for the ID of the person, family, or source to which this media item should be linked.'); ?></sub>
						<br><br>
						<input type="hidden" name="idName" id="idName" size="36" value="Name of ID">

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
		//id=document.getElementById('gid').value;
		if (id.match("I")=="I" || id.match("i")=="i") {
			id = id.toUpperCase();
			winNav = window.open('edit_interface.php?action=addmedia_links&noteid=newnote&pid='+id, 'winNav', fam_nav_specs);
			if (window.focus) {winNav.focus();}
		} else if (id.match("F")=="F") {
			id = id.toUpperCase();
			// TODO --- alert('Opening Navigator with family id entered will come later');
		}
	}

	var ifamily = "<?php echo WT_I18N::translate('Family navigator'); ?>";
	var remove = "<?php echo WT_I18N::translate('Remove'); ?>";
	/* ===icons === */
	var removeLinkIcon = "<?php echo $WT_IMAGES['remove']; ?>";
	var familyNavIcon = "<?php echo $WT_IMAGES['button_family']; ?>";


	var INPUT_NAME_PREFIX = 'InputCell_'; // this is being set via script
	var RADIO_NAME = "totallyrad"; // this is being set via script
	var TABLE_NAME = 'addlinkQueue'; // this should be named in the HTML
	var ROW_BASE = 1; // first number (for display)
	var hasLoaded = false;

	window.onload=fillInRows;

	function fillInRows() {
		hasLoaded = true;
		//insertRowToTable();
		//addRowToTable();
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
	//function insertRowToTable(pid, nam, label, gend, cond, yob, age, YMD, occu, birthpl)
	function insertRowToTable(pid, nam, head) {
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
				addRowToTable(rowToInsertAt, pid, nam, head);
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
	// function addRowToTable(num, pid, nam, label, gend, cond, yob, age, YMD, occu, birthpl)
	function addRowToTable(num, pid, nam, head) {
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

				// CONFIG: requires class
//				row.className = 'descriptionbox';

				// CONFIG: This whole section can be configured

				// cell 0 - Count
				var cell0 = row.insertCell(0);
//				cell0.style.fontSize="11px";
				var textNode = document.createTextNode(iteration);
				cell0.appendChild(textNode);

				// cell 1 - ID:
				var cell1 = row.insertCell(1);
				if (pid=='') {
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
//					txtInp1.style.background='transparent';
//					txtInp1.style.border='0px';
//					txtInp1.style.fontSize="11px";
				cell1.appendChild(txtInp1);

				// cell 2 - Name
				var cell2 = row.insertCell(2);
				var txtInp2 = document.createElement('div');
					txtInp2.setAttribute('type', 'text');
					txtInp2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_2');
//					txtInp2.style.background='transparent';
//					txtInp2.style.border='0px';
//					txtInp2.style.fontSize="11px";
					txtInp2.innerHTML = removeHTMLTags(unescape(nam));
				cell2.appendChild(txtInp2);

				// cell btn - remove img button
				var cellbtn = row.insertCell(3);
					cellbtn.setAttribute('align', 'center');
				var btnEl = document.createElement('img');
					btnEl.setAttribute('type', 'img');
					btnEl.setAttribute('src', removeLinkIcon);
					btnEl.setAttribute('alt', remove);
					btnEl.setAttribute('title', remove);
//					btnEl.setAttribute('height', '14px');
					btnEl.onclick = function () {deleteCurrentRow(this)};
				cellbtn.appendChild(btnEl);

				// cell btn - family img button
				var cellbtn2 = row.insertCell(4);
					cellbtn2.setAttribute('align', 'center');
				if (pid.match("I")=="I" || pid.match("i")=="i") {
					var btn2El = document.createElement('img');
						btn2El.setAttribute('type', 'img');
						btn2El.setAttribute('src', familyNavIcon);
						btn2El.setAttribute('alt', ifamily);
						btn2El.setAttribute('title', ifamily);
						btn2El.onclick = function () {openFamNav(pid)};
					cellbtn2.appendChild(btn2El);
				} else if (pid.match("F")=="F" || pid.match("f")=="f") {
					var btn2El = document.createElement('img');
						btn2El.setAttribute('type', 'img');
						btn2El.setAttribute('src', familyNavIcon);
						btn2El.setAttribute('alt', ifamily);
						btn2El.setAttribute('title', ifamily);
						btn2El.onclick = function () {openFamNav(head)};
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

	function openInNewWindow(frm) {
		// open a blank window
		var aWindow = window.open('', 'TableAddRow2NewWindow',
		'scrollbars=yes,menubar=yes,resizable=yes,location=no,toolbar=no,width=550,height=700');
		aWindow.focus();

		// set the target to the blank window
		frm.target = 'TableAddRow2NewWindow';

		// submit
		frm.submit();
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
			var remtr = tbl.rows[i];
			if (remtr.cells[4].childNodes[0].checked)  {
				remstr += (remstr==''?'':',') + remtr.cells[4].childNodes[0].name;
			}
		}
		document.link.exist_links.value = remstr;
	}

	function shiftlinks() {
		parseRemLinks();
		parseAddLinks();
		if (winNav) {
			winNav.close();
		}
	}

</script>

						<table id="addlinkQueue">
							<thead>
								<tr>
									<th style="">#</td>
									<th style=""><?php echo WT_I18N::translate('Record'); ?></th>
									<th style="min-width: 300px; width: 60%">
										<?php echo WT_I18N::translate('Name'); ?>
									</th>
									<th style=""><?php echo WT_I18N::translate('Remove'); ?></th>
									<th style=""><?php echo WT_I18N::translate('Navigator'); ?></th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</td>
				</tr>
			<?php
			// Admin Option CHAN log update override =======================
			if (WT_USER_IS_ADMIN) { ?>
				<tr class="preserve">
					<td class="media-title"><?php echo WT_Gedcom_Tag::getLabel('CHAN'); ?></td>
					<td class="media-item">
						<?php if ($NO_UPDATE_CHAN) { ?>
							<input type="checkbox" checked="checked" name="preserve_last_changed">
						<?php } else { ?>
							<input type="checkbox" name="preserve_last_changed">
						<?php }
						echo WT_I18N::translate('Do not update the “last change” record'), help_link('no_update_CHAN'); ?>
					</td>
				</tr>
			<?php }?>
			</table>
			<input type="hidden" name="more_links" value="No_Values">
			<input type="hidden" name="exist_links" value="No_Values">
			<p id="save-cancel">
				<button class="btn btn-primary" type="submit" onclick="shiftlinks();">
					<i class="fa fa-save"></i>
					<?php echo WT_I18N::translate('save'); ?>
				</button>
				<button class="btn btn-primary" type="button"  onclick="window.close();">
					<i class="fa fa-times"></i>
					<?php echo WT_I18N::translate('close'); ?>
				</button>
			</p>
		</form>
<?php
} elseif ($action == "update" && $paramok) {
	// Unlink records indicated by radio button =========
	if ($exist_links) {
		foreach (explode(',', $exist_links) as $remLinkId) {
			unlinkMedia($remLinkId, 'OBJE', $mediaid, 1, $update_CHAN!='no_change');
		}
	}
	// Add new Links ====================================
	if ($more_links) {
		foreach (explode(',', $more_links) as $addLinkId) {
			linkMedia($mediaid, $addLinkId, 1, $update_CHAN!='no_change');
		}
	}
	$controller->addInlineJavascript('closePopupAndReloadParent();');
}

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
	if ($level!=1) return false; // Level 2 items get unlinked elsewhere (maybe ??)
	// find Indi, Family, or Source record to unlink from
	$gedrec = find_gedcom_record($linktoid, WT_GED_ID, true);

	//-- when deleting/unlinking a media link
	//-- $linenum comes as an OBJE and the $mediaid to delete should be set
	if (!is_numeric($linenum)) {
		$newged = remove_media_subrecord($gedrec, $mediaid);
	} else {
		$newged = remove_subline($gedrec, $linenum);
	}
	replace_gedrec($linktoid, WT_GED_ID, $newged, $chan);
}
