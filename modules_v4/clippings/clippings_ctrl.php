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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once KT_ROOT.'includes/functions/functions_export.php';
require_once KT_ROOT.'library/pclzip.lib.php';

/**
* Main controller class for the Clippings page.
*/
#[AllowDynamicProperties]
class KT_Controller_Clippings {

	var $download_data;
	var $media_list = array();
	var $addCount = 0;
	var $privCount = 0;
	var $type="";
	var $id="";
	var $IncludeMedia;
	var $conv_path;
	var $privatize_export;
	var $Zip;
	var $level1;  // number of levels of ancestors
	var $level2;
	var $level3; // number of levels of descendents

	public function __construct() {
		global $SCRIPT_NAME, $MEDIA_DIRECTORY, $KT_SESSION;

		// Our cart is an array of items in the session
		if (!is_array($KT_SESSION->cart)) {
			$KT_SESSION->cart=array();
		}
		if (!array_key_exists(KT_GED_ID, $KT_SESSION->cart)) {
			$KT_SESSION->cart[KT_GED_ID]=array();
		}

		$this->action           = KT_Filter::get('action');
		$this->id               = KT_Filter::get('id');
		$convert                = KT_Filter::get('convert', 'yes|no', 'no');
		$this->Zip              = KT_Filter::get('Zip');
		$this->IncludeMedia     = KT_Filter::get('IncludeMedia');
		$this->conv_path        = KT_Filter::get('conv_path');
		$this->privatize_export = KT_Filter::get('privatize_export', 'none|visitor|user|gedadmin', 'visitor');
		$this->level1           = KT_Filter::getInteger('level1');
		$this->level2           = KT_Filter::getInteger('level2');
		$this->level3           = KT_Filter::getInteger('level3');
		$others                 = KT_Filter::get('others');
		$this->type             = KT_Filter::get('type');

		if (($this->privatize_export=='none' || $this->privatize_export=='none') && !KT_USER_GEDCOM_ADMIN) {
			$this->privatize_export='visitor';
		}
		if ($this->privatize_export=='user' && !KT_USER_CAN_ACCESS) {
			$this->privatize_export='visitor';
		}

		if ($this->action == 'add') {
			if (empty($this->type) && !empty($this->id)) {
				$this->type="";
				$obj = KT_GedcomRecord::getInstance($this->id);
				if (is_null($obj)) {
					$this->id="";
					$this->action="";
				}
				else $this->type = strtolower($obj->getType());
			}
			else if (empty($this->id)) $this->action="";
			if (!empty($this->id) && $this->type != 'fam' && $this->type != 'indi' && $this->type != 'sour')
			$this->action = 'add1';
		}

		if ($this->action == 'add1') {
			$this->add_clipping(KT_GedcomRecord::getInstance($this->id));
			if ($this->type == 'sour') {
				if ($others == 'linked') {
					foreach (fetch_linked_indi($this->id, 'SOUR', KT_GED_ID) as $indi) {
						$this->add_clipping($indi);
					}
					foreach (fetch_linked_fam($this->id, 'SOUR', KT_GED_ID) as $fam) {
						$this->add_clipping($fam);
					}
				}
			}
			if ($this->type == 'fam') {
				if ($others == 'parents') {
					$this->add_clipping($obj->getHusband());
					$this->add_clipping($obj->getWife());
				} elseif ($others == "members") {
					$this->add_family_members(KT_Family::getInstance($this->id));
				} elseif ($others == "descendants") {
					$this->add_family_descendancy(KT_Family::getInstance($this->id));
				}
			} elseif ($this->type == 'indi') {
				if ($others == 'parents') {
					foreach (KT_Person::getInstance($this->id)->getChildFamilies() as $family) {
						$this->add_family_members($family);
					}
				} elseif ($others == 'ancestors') {
					$this->add_ancestors_to_cart(KT_Person::getInstance($this->id), $this->level1);
				} elseif ($others == 'ancestorsfamilies') {
					$this->add_ancestors_to_cart_families(KT_Person::getInstance($this->id), $this->level2);
				} elseif ($others == 'members') {
					foreach (KT_Person::getInstance($this->id)->getSpouseFamilies() as $family) {
						$this->add_family_members($family);
					}
				} elseif ($others == 'descendants') {
					foreach (KT_Person::getInstance($this->id)->getSpouseFamilies() as $family) {
						$this->add_clipping($family);
						$this->add_family_descendancy($family, $this->level3);
					}
				}
				uksort($KT_SESSION->cart[KT_GED_ID], array('KT_Controller_Clippings', 'compare_clippings'));
			}
		} elseif ($this->action == 'remove') {
			unset ($KT_SESSION->cart[KT_GED_ID][$this->id]);
		} elseif ($this->action == 'empty') {
			$KT_SESSION->cart[KT_GED_ID]=array();
		} elseif ($this->action == 'download') {
			$media = array ();
			$mediacount = 0;
			$filetext = gedcom_header(KT_GEDCOM);
			// Include SUBM/SUBN records, if they exist
			$subn=
				KT_DB::prepare("SELECT o_gedcom FROM `##other` WHERE o_type=? AND o_file=?")
				->execute(array('SUBN', KT_GED_ID))
				->fetchOne();
			if ($subn) {
				$filetext .= $subn."\n";
			}
			$subm=
				KT_DB::prepare("SELECT o_gedcom FROM `##other` WHERE o_type=? AND o_file=?")
				->execute(array('SUBM', KT_GED_ID))
				->fetchOne();
			if ($subm) {
				$filetext .= $subm."\n";
			}
			if ($convert == "yes") {
				$filetext = str_replace("UTF-8", "ANSI", $filetext);
				$filetext = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $filetext);
			}

			switch($this->privatize_export) {
			case 'gedadmin':
				$access_level=KT_PRIV_NONE;
				break;
			case 'user':
				$access_level=KT_PRIV_USER;
				break;
			case 'visitor':
				$access_level=KT_PRIV_PUBLIC;
				break;
			case 'none':
				$access_level=KT_PRIV_HIDE;
				break;
			}

			foreach (array_keys($KT_SESSION->cart[KT_GED_ID]) as $xref) {
				$object=KT_GedcomRecord::getInstance($xref);
				if ($object) { // The object may have been deleted since we added it to the cart....
					list($record)=$object->privatizeGedcom($access_level);
					// Remove links to objects that aren't in the cart
					preg_match_all('/\n1 '.KT_REGEX_TAG.' @('.KT_REGEX_XREF.')@(\n[2-9].*)*/', $record, $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						if (!array_key_exists($match[1], $KT_SESSION->cart[KT_GED_ID])) {
							$record=str_replace($match[0], '', $record);
						}
					}
					preg_match_all('/\n2 '.KT_REGEX_TAG.' @('.KT_REGEX_XREF.')@(\n[3-9].*)*/', $record, $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						if (!array_key_exists($match[1], $KT_SESSION->cart[KT_GED_ID])) {
							$record=str_replace($match[0], '', $record);
						}
					}
					preg_match_all('/\n3 '.KT_REGEX_TAG.' @('.KT_REGEX_XREF.')@(\n[4-9].*)*/', $record, $matches, PREG_SET_ORDER);
					foreach ($matches as $match) {
						if (!array_key_exists($match[1], $KT_SESSION->cart[KT_GED_ID])) {
							$record=str_replace($match[0], '', $record);
						}
					}
					$record = convert_media_path($record, $this->conv_path);
					$savedRecord = $record; // Save this for the "does this file exist" check
					if ($convert=='yes') {
						$record=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $record);
					}
					switch ($object->getType()) {
					case 'INDI':
						$filetext .= $record."\n";
						$filetext .= "1 SOUR @KIWITREES@\n";
						$filetext .= "2 PAGE ". KT_SERVER_NAME . KT_SCRIPT_PATH.$object->getRawUrl()."\n";
						break;
					case 'FAM':
						$filetext .= $record."\n";
						$filetext .= "1 SOUR @KIWITREES@\n";
						$filetext .= "2 PAGE ". KT_SERVER_NAME . KT_SCRIPT_PATH.$object->getRawUrl()."\n";
						break;
					case 'SOUR':
						$filetext .= $record."\n";
						$filetext .= "1 NOTE ". KT_SERVER_NAME . KT_SCRIPT_PATH.$object->getRawUrl()."\n";
						break;
					default:
						$ft = preg_match_all("/\n\d FILE (.+)/", $savedRecord, $match, PREG_SET_ORDER);
						for ($k = 0; $k < $ft; $k++) {
							// Skip external files and non-existant files
							if (file_exists(KT_DATA_DIR . $MEDIA_DIRECTORY . $match[$k][1])) {
								$media[$mediacount] = array (
									PCLZIP_ATT_FILE_NAME          => KT_DATA_DIR . $MEDIA_DIRECTORY . $match[$k][1],
									PCLZIP_ATT_FILE_NEW_FULL_NAME =>                                  $match[$k][1],
								);
								$mediacount++;
							}
						}
						$filetext .= trim($record) . "\n";
						break;
					}
				}
			}

			if ($this->IncludeMedia == "yes") {
				$this->media_list = $media;
			}
			$filetext .= "0 @KIWITREES@ SOUR\n1 TITL ". KT_SERVER_NAME . KT_SCRIPT_PATH."\n";
			if ($user_id=get_gedcom_setting(KT_GED_ID, 'CONTACT_EMAIL')) {
				$filetext .= "1 AUTH " . getUserFullName($user_id) . "\n";
			}
			$filetext .= "0 TRLR\n";
			//-- make sure the preferred line endings are used
			$filetext = preg_replace("/[\r\n]+/", KT_EOL, $filetext);
			$this->download_data = $filetext;
			$this->download_clipping();
		}
	}

	// Loads everything in the clippings cart into a zip file.
	function zip_cart() {
		$tempFileName = 'clipping'.rand().'.ged';
		$fp = fopen(KT_DATA_DIR.$tempFileName, "wb");
		if ($fp) {
			flock($fp,LOCK_EX);
			fwrite($fp,$this->download_data);
			flock($fp,LOCK_UN);
			fclose($fp);
			$zipName = "clippings".rand(0, 1500).".zip";
			$fname = KT_DATA_DIR.$zipName;
			$comment = "Created by ".KT_KIWITREES." ".KT_VERSION_TEXT." on ".date("d M Y").".";
			$archive = new PclZip($fname);
			// add the ged file to the root of the zip file (strip off the data folder)
			$this->media_list[]= array (PCLZIP_ATT_FILE_NAME => KT_DATA_DIR.$tempFileName, PCLZIP_ATT_FILE_NEW_FULL_NAME => $tempFileName);
			$v_list = $archive->create($this->media_list, PCLZIP_OPT_COMMENT, $comment);
			if ($v_list == 0) {
				echo "Error : ".$archive->errorInfo(true)."</td></tr>";
			} else {
				$openedFile = fopen($fname,"rb");
				$this->download_data = fread($openedFile,filesize($fname));
				fclose($openedFile);
				unlink($fname);
			}
			unlink(KT_DATA_DIR.$tempFileName);
		} else {
			echo KT_I18N::translate('Cannot create')." ".KT_DATA_DIR."$tempFileName ".KT_I18N::translate('Check access rights on this directory.')."<br><br>";
		}
	}

	// Brings up the download dialog box and allows the user to download the file
	// based on the options he or she selected
	function download_clipping() {
		Zend_Session::writeClose();

		if ($this->IncludeMedia == "yes" || $this->Zip == "yes") {
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment; filename="clipping.zip"');
			$this->zip_cart();
		} else {
			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="clipping.ged"');
		}

		header('Content-length: ' . strlen($this->download_data));
		echo $this->download_data;

		// Notify admin of download and add to log
		$adminId	= get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
		$userName	= get_user_name(KT_USER_ID);
		if (get_user_setting($adminId, 'notify_clipping')) {
			global $KT_TREE;
			KT_I18N::init(get_user_setting($adminId, 'language'));
			KT_Mail::systemMessage(
				$KT_TREE,
				$adminId,
				KT_I18N::translate(strip_tags(KT_TREE_TITLE) . ' Clippings cart'),
				KT_I18N::translate('User %s has just downloaded a clippings cart file', KT_USER_NAME)
			);
		}
		AddToLog("Clippings cart downloaded by user " .  KT_USER_NAME, 'edit');

		exit;
	}

	// Inserts a clipping into the clipping cart
	function add_clipping($record) {
		global $KT_SESSION;

		if ($record->canDisplayName()) {
			$KT_SESSION->cart[KT_GED_ID][$record->getXref()]=true;
			// Add directly linked records
			preg_match_all('/\n\d (?:OBJE|NOTE|SOUR|REPO) @('.KT_REGEX_XREF.')@/', $record->getGedcomRecord(), $matches);
			foreach ($matches[1] as $match) {
				$KT_SESSION->cart[KT_GED_ID][$match]=true;
			}
		}
	}

	// --------------------------------- Recursive function to traverse the tree
	function add_family_descendancy($family, $level=PHP_INT_MAX) {
		if (!$family) {
			return;
		}
		foreach ($family->getSpouses() as $spouse) {
			$this->add_clipping($spouse);
		}
		foreach ($family->getChildren() as $child) {
			$this->add_clipping($child);
			foreach ($child->getSpouseFamilies() as $child_family) {
				$this->add_clipping($child_family);
				if ($level>0) {
					$this->add_family_descendancy($child_family, $level-1); // recurse on the childs family
				}
			}
		}
	}

	// Add a family, and all its members
	function add_family_members($family) {
		if (!$family) {
			return;
		}
		$this->add_clipping($family);
		foreach ($family->getSpouses() as $spouse) {
			$this->add_clipping($spouse);
		}
		foreach ($family->getChildren() as $child) {
			$this->add_clipping($child);
		}
	}

	//-- recursively adds direct-line ancestors to cart
	function add_ancestors_to_cart($person, $level) {
		if (!$person) {
			return;
		}
		$this->add_clipping($person);
		if ($level>0) {
			foreach ($person->getChildFamilies() as $family) {
				$this->add_clipping($family);
				$this->add_ancestors_to_cart($family->getHusband(), $level-1);
				$this->add_ancestors_to_cart($family->getWife(), $level-1);
			}
		}
	}

	//-- recursively adds direct-line ancestors and their families to the cart
	function add_ancestors_to_cart_families($person, $level) {
		if (!$person) {
			return;
		}
		if ($level>0) {
			foreach ($person->getChildFamilies() as $family) {
				$this->add_family_members($family);
				$this->add_ancestors_to_cart_families($family->getHusband(), $level-1);
				$this->add_ancestors_to_cart_families($family->getWife(), $level-1);
			}
		}
	}

	// Helper function to sort records by type/name
	static function compare_clippings($a, $b) {
		$a=KT_GedcomRecord::getInstance($a);
		$b=KT_GedcomRecord::getInstance($b);
		if ($a && $b) {
			switch ($a->getType()) {
			case 'INDI': $t1=1; break;
			case 'FAM':  $t1=2; break;
			case 'SOUR': $t1=3; break;
			case 'REPO': $t1=4; break;
			case 'OBJE': $t1=5; break;
			case 'NOTE': $t1=6; break;
			default:     $t1=7; break;
			}
			switch ($b->getType()) {
			case 'INDI': $t2=1; break;
			case 'FAM':  $t2=2; break;
			case 'SOUR': $t2=3; break;
			case 'REPO': $t2=4; break;
			case 'OBJE': $t2=5; break;
			case 'NOTE': $t2=6; break;
			default:     $t2=7; break;
			}
			if ($t1!=$t2) {
				return $t1-$t2;
			} else {
				return KT_GedcomRecord::compare($a, $b);
			}
		} else {
			return 0;
		}
	}
}
