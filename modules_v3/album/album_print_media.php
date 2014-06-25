<?php
// Album module for kiwitrees
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2007 to 2009  PGV Development Team.  All rights reserved.
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

/**
 * -----------------------------------------------------------------------------
 * Print the links to media objects
 * @param string $pid		The the xref id of the object to find media records related to
 * @param int $level		The level of media object to find
 * @param boolean $related	Whether or not to grab media from related records
 */
function album_print_media($pid, $level=1, $related=false, $kind=0, $noedit=false) {
	global $GEDCOM;

	$ALBUM_GROUPS = get_module_setting('album', 'ALBUM_GROUPS');
	$ALBUM_TITLES = unserialize(get_module_setting('album', 'ALBUM_TITLES'));
	$ALBUM_OPTIONS = unserialize(get_module_setting('album', 'ALBUM_OPTIONS'));

	if (!isset($ALBUM_GROUPS)) {
		$ALBUM_GROUPS = 4;
	}

	if (empty($ALBUM_TITLES)) {
		$ALBUM_TITLES = array(
			WT_I18N::translate('Photos'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Census'),
			WT_I18N::translate('Other')
		);
	}

	$default_groups = array(
			WT_I18N::translate('Other'),
			WT_I18N::translate('Other'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Other'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Census'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Census'),
			WT_I18N::translate('Census'),
			WT_I18N::translate('Documents'),
			WT_I18N::translate('Other'),
			WT_I18N::translate('Photos'),
			WT_I18N::translate('Photos'),
			WT_I18N::translate('Photos'),
			WT_I18N::translate('Other')
	);

	if (empty($ALBUM_OPTIONS))	{
		$ALBUM_OPTIONS = array_combine(array_keys(WT_Gedcom_Tag::getFileFormTypes()), $default_groups);
	}

	$ged_id = get_id_from_gedcom($GEDCOM);
	$person = WT_Person::getInstance($pid);
	$ctf = 0;
	if ($level > 0) {
		$regexp = '/\n' . $level . ' OBJE @(.*)@/';
	} else {
		$regexp = '/\n\d OBJE @(.*)@/';
	}
	//-- find all of the related individuals
	$ids = array($person->getXref());
	if ($related) {
		foreach ($person->getSpouseFamilies() as $family) {
			$ids[] = $family->getXref();
			$ctf += preg_match_all($regexp, $family->getGedcomRecord(), $match, PREG_SET_ORDER);
		}
	}
	//-- If they exist, get a list of the sorted current objects in the indi gedcom record  -  (1 _WT_OBJE_SORT @xxx@ .... etc) ----------
	$sort_current_objes = array();
	$sort_ct = preg_match_all('/\n1 _WT_OBJE_SORT @(.*)@/', $person->getGedcomRecord(), $sort_match, PREG_SET_ORDER);
	for ($i = 0; $i < $sort_ct; $i++) {
		if (!isset($sort_current_objes[$sort_match[$i][1]])) {
			$sort_current_objes[$sort_match[$i][1]] = 1;
		} else {
			$sort_current_objes[$sort_match[$i][1]]++;
		}
		$sort_obje_links[$sort_match[$i][1]][] = $sort_match[$i][0];
	}

	// create ORDER BY list from Gedcom sorted records list  ---------------------------
	$orderbylist = ' ORDER BY '; // initialize
	foreach ($sort_match as $id) {
		$orderbylist .= "m_id='$id[1]' DESC, ";
	}
	$orderbylist = rtrim($orderbylist, ', ');

	//-- get a list of the current objects in the record
	$current_objes = array();
	$ct = preg_match_all($regexp, $person->getGedcomRecord(), $match, PREG_SET_ORDER);
	for ($i = 0; $i < $ct; $i++) {
		if (!isset($current_objes[$match[$i][1]])) {
			$current_objes[$match[$i][1]] = 1;
		} else {
			$current_objes[$match[$i][1]]++;
		}
		$obje_links[$match[$i][1]][] = $match[$i][0];
	}

	$media_found = false;

	// Get the related media items
	$sqlmm =
		"SELECT DISTINCT m_id, m_ext, m_filename, m_titl, m_file, m_gedcom, l_from AS pid" .
		" FROM `##media`" .
		" JOIN `##link` ON (m_id=l_to AND m_file=l_file AND l_type='OBJE')" .
		" WHERE m_file=? AND l_from IN (";
	$i=0;
	$vars = array(WT_GED_ID);
	foreach ($ids as $media_id) {
		if ($i > 0) $sqlmm .= ", ";
		$sqlmm .= "?";
		$vars[] = $media_id;
		$i++;
	}
	$sqlmm .= ')';

	if ($ALBUM_GROUPS != 0) {
		// Set type of media from call in album
		for ($i = 0; $i < $ALBUM_GROUPS; $i++) {
			if ($i == $kind) {
				$tt = WT_I18N::translate($ALBUM_TITLES[$i]);
				$sqlmm .= " AND (";
				foreach ($ALBUM_OPTIONS as $key=>$value) {
					if ($value == WT_I18N::translate($ALBUM_TITLES[$i])) {
						$sqlmm .= "m_gedcom LIKE '%TYPE " .strtolower($key). "%' OR ";
					}
					if (WT_I18N::translate($ALBUM_TITLES[$i]) == WT_I18N::translate('Other')) {
						$sqlmm .= "m_gedcom NOT LIKE '%TYPE %' OR ";					
					}
				}
				$sqlmm = rtrim($sqlmm, ' OR ');
				$sqlmm .= ')';
			}
		}
	}

	if ($sort_ct > 0) {
		$sqlmm .= $orderbylist;
	}

	$rows = WT_DB::prepare($sqlmm)->execute($vars)->fetchAll(PDO::FETCH_ASSOC);
	$numm = count($rows);
	$foundObjs = array();

// Begin to Layout the Album Media Rows
	if ($numm > 0) {
		if ($ALBUM_GROUPS != 0) {
			echo '<table class="facts_table">
				<tr>
					<td class="descriptionbox" style="width:120px; text-align:center; vertical-align:middle;">
						<span style="font-weight:900;">', $tt,'</span>
					</td>
					<td class="optionbox">';
		}
					echo '<div id="thumbcontainer', $kind, '">';
						// Start pulling media items into thumbcontainer div ==============================
						foreach ($rows as $rowm) {
							if (isset($foundObjs[$rowm['m_id']])) {
								if (isset($current_objes[$rowm['m_id']])) {
									$current_objes[$rowm['m_id']]--;
								}
								continue;
							}
							$rows=array();

							//-- if there is a change to this media item then get the
							//-- updated media item and show it
							if (($newrec=find_updated_record($rowm['m_id'], $ged_id))) {
								$row = array();
								$row['m_id'] = $rowm['m_id'];
								$row['m_file'] = $ged_id;
								$row['m_filename'] = get_gedcom_value('FILE', 1, $newrec);
								$row['m_titl'] = get_gedcom_value('TITL', 1, $newrec);
								if (empty($row['m_titl'])) $row['m_titl'] = get_gedcom_value('FILE:TITL', 1, $newrec);
								$row['m_gedcom'] = $newrec;
								$et = preg_match('/\.(\w+)$/', $row['m_filename'], $ematch);
								$ext = '';
								if ($et > 0) $ext = $ematch[1];
								$row['m_ext'] = $ext;
								$row['pid'] = $pid;
								$rows['new'] = $row;
								$rows['old'] = $rowm;
							} else {
								if (!isset($current_objes[$rowm['m_id']]) && ($rowm['pid'] == $pid)) {
									$rows['old'] = $rowm;
								} else {
									$rows['normal'] = $rowm;
									if (isset($current_objes[$rowm['m_id']])) {
										$current_objes[$rowm['m_id']]--;
									}
								}
							}
							foreach ($rows as $rtype => $rowm) {
								$res = album_print_media_row($rtype, $rowm, $pid);
								$media_found = $media_found || $res;
								$foundObjs[$rowm['m_id']] = true;
							}
						}
					echo '</div>';
		if ($ALBUM_GROUPS != 0)	 {
				echo '</td>
			</tr>
			</table>';
		}
	}
}

/**
 * print a media row in a table
 * @param string $rtype whether this is a 'new', 'old', or 'normal' media row... this is used to determine if the rows should be printed with an outline color
 * @param array $rowm - An array with the details about this media item
 * @param string $pid - The record id this media item was attached to
 */
function album_print_media_row($rtype, $rowm, $pid) {
	global $sort_i, $notes;

	$media = WT_Media::getInstance($rowm['m_id']);

	if ($media && !$media->canDisplayDetails()) {
		// This media object is private;
		return false;
	}

	if (!canDisplayFact($rowm['m_id'], $rowm['m_file'], $rowm['m_gedcom'])) {
		// The link to this media object is private.  e.g. 1 OBJE/2 RESN
		return false;
	}

	// Highlight Album Thumbnails - Changed=new (blue), Changed=old (red), Changed=no (none)
	 if ($rtype=='new') {
		echo '<div class="album_new"><div class="pic">';
	} else if ($rtype=='old') {
		echo '<div class="album_old"><div class="pic">';
	} else {
		echo '<div class="album_norm"><div class="pic">';
	}

	//  Get the title of the media
	if ($media) {
		$mediaTitle = $media->getFullName();
	} else {
		$mediaTitle = $rowm['m_id'];
	}

	//Get media item Notes
	$haystack = $rowm['m_gedcom'];
	$needle   = '1 NOTE';
	$before   = substr($haystack, 0, strpos($haystack, $needle));
	$after    = substr(strstr($haystack, $needle), strlen($needle));
	$final    = $before.$needle.$after;
	$notes    = htmlspecialchars(addslashes(print_fact_notes($final, 1, true, true)), ENT_QUOTES);

	// Prepare Below Thumbnail  menu ----------------------------------------------------
	$mtitle = '<div class="album_media_title">' . $mediaTitle . '</div>';
	$menu = new WT_Menu();
	$menu->addLabel($mtitle, 'right');

	if ($rtype=='old') {
		// Do not print menu if item has changed and this is the old item
	} else {
		// Continue printing menu
		$menu->addClass('', 'submenu');

		// View Notes
		if (strpos($rowm['m_gedcom'], "\n1 NOTE")) {
			$submenu = new WT_Menu(WT_I18N::translate('View Notes'), '#');
			// Notes Tooltip ----------------------------------------------------
			$submenu->addOnclick("modalNotes('". $notes ."','". WT_I18N::translate('View Notes') ."'); return false;");
			$submenu->addClass("submenuitem");
			$menu->addSubMenu($submenu);
		}
		//View Details
		$submenu = new WT_Menu(WT_I18N::translate('View Details'), WT_SERVER_NAME.WT_SCRIPT_PATH . "mediaviewer.php?mid=".$rowm['m_id'].'&amp;ged='.WT_GEDURL, 'right');
		$submenu->addClass("submenuitem");
		$menu->addSubMenu($submenu);

		//View Sources
		$source_menu = null;
		foreach ($media->getAllFactsByType('SOUR') as $source_fact) {
			$source = WT_Source::getInstance(trim($source_fact->detail, '@'));
			if ($source && $source->canDisplayDetails()) {
				if (!$source_menu) {
					// Group sources under a top level menu
					$source_menu = new WT_Menu(WT_I18N::translate('Sources'), '#', null, 'right', 'right');
					$source_menu->addClass('submenuitem', 'submenu');
				}
				//now add a link to the actual source as a submenu
				$submenu = new WT_Menu(new WT_Menu(strip_tags($source->getFullName()), $source->getHtmlUrl()));
				$submenu->addClass('submenuitem', 'submenu');
				$source_menu->addSubMenu($submenu);
			}
		}
		if ($source_menu) {
			$menu->addSubMenu($source_menu);
		}

		if (WT_USER_CAN_EDIT) {
			// Edit Media
			$submenu = new WT_Menu(WT_I18N::translate('Edit media'));
			$submenu->addOnclick("return window.open('addmedia.php?action=editmedia&amp;pid={$rowm['m_id']}', '_blank', edit_window_specs);");
			$submenu->addClass("submenuitem");
			$menu->addSubMenu($submenu);
			if (WT_USER_IS_ADMIN) {
				// Manage Links
				if (array_key_exists('GEDFact_assistant', WT_Module::getActiveModules())) {
					$submenu = new WT_Menu(WT_I18N::translate('Manage links'));
					$submenu->addOnclick("return window.open('inverselink.php?mediaid={$rowm['m_id']}&amp;linkto=manage', '_blank', find_window_specs);");
					$submenu->addClass("submenuitem");
					$menu->addSubMenu($submenu);
				} else {
					$submenu = new WT_Menu(WT_I18N::translate('Set link'), '#', null, 'right', 'right');
					$submenu->addClass('submenuitem', 'submenu');

					$ssubmenu = new WT_Menu(WT_I18N::translate('To Person'));
					$ssubmenu->addOnclick("return window.open('inverselink.php?mediaid={$rowm['m_id']}&amp;linkto=person', '_blank', find_window_specs);");
					$ssubmenu->addClass('submenuitem', 'submenu');
					$submenu->addSubMenu($ssubmenu);

					$ssubmenu = new WT_Menu(WT_I18N::translate('To Family'));
					$ssubmenu->addOnclick("return window.open('inverselink.php?mediaid={$rowm['m_id']}&amp;linkto=family', '_blank', find_window_specs);");
					$ssubmenu->addClass('submenuitem', 'submenu');
					$submenu->addSubMenu($ssubmenu);

					$ssubmenu = new WT_Menu(WT_I18N::translate('To Source'));
					$ssubmenu->addOnclick("return window.open('inverselink.php?mediaid={$rowm['m_id']}&amp;linkto=source', '_blank', find_window_specs);");
					$ssubmenu->addClass('submenuitem', 'submenu');
					$submenu->addSubMenu($ssubmenu);

					$menu->addSubMenu($submenu);
				}
				// Unlink Media
				$submenu = new WT_Menu(WT_I18N::translate('Unlink Media'));
				$submenu->addOnclick("return delete_fact('$pid', 'OBJE', '".$rowm['m_id']."', '".WT_I18N::translate('Are you sure you want to delete this fact?')."');");
				$submenu->addClass("submenuitem");
				$menu->addSubMenu($submenu);
			}
		}
	}

	// Start Thumbnail Enclosure table ---------------------------------------------
	// Print Thumbnail
	if ($media) {echo $media->displayImage();}
	echo '</div>';

	//View Edit Menu
	echo '<div>', $menu->getMenu(), '</div>';
	echo '</div>';

	return true;
}
