<?php
// Controller for the media page
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once WT_ROOT.'includes/functions/functions_print_facts.php';
require_once WT_ROOT.'includes/functions/functions_import.php';

class WT_Controller_Media extends WT_Controller_GedcomRecord {

	public function __construct() {
		$xref = safe_GET_xref('mid');

		$gedrec=find_media_record($xref, WT_GED_ID);
		if (WT_USER_CAN_EDIT) {
			$newrec=find_updated_record($xref, WT_GED_ID);
		} else {
			$newrec=null;
		}

		if ($gedrec===null) {
			if ($newrec===null) {
				// Nothing to see here.
				parent::__construct();
				return;
			} else {
				// Create a dummy record from the first line of the new record.
				// We need it for diffMerge(), getXref(), etc.
				list($gedrec)=explode("\n", $newrec);
			}
		}

		$this->record = new WT_Media($gedrec);

		// If there are pending changes, merge them in.
		if ($newrec!==null) {
			$diff_record=new WT_Media($newrec);
			$diff_record->setChanged(true);
			$this->record->diffMerge($diff_record);
		}

		parent::__construct();
	}

	/**
	* get edit menu
	*/
	function getEditMenu() {
		$SHOW_GEDCOM_RECORD=get_gedcom_setting(WT_GED_ID, 'SHOW_GEDCOM_RECORD');

		if (!$this->record || $this->record->isMarkedDeleted()) {
			return null;
		}

		// edit menu
		$menu = new WT_Menu(WT_I18N::translate('Edit'), 'addmedia.php?action=editmedia&pid=' . $this->record->getXref(), 'menu-obje');
		$menu->addTarget('_blank');

		if (WT_USER_CAN_EDIT) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit media object'), 'addmedia.php?action=editmedia&pid=' . $this->record->getXref(), 'menu-obje-edit');
			$submenu->addTarget('_blank');
			$menu->addSubmenu($submenu);

			// main link displayed on page
			if (array_key_exists('GEDFact_assistant', WT_Module::getActiveModules())) {
				$submenu = new WT_Menu(WT_I18N::translate('Manage links'), '#', 'menu-obje-link');
				$submenu = new WT_Menu(WT_I18N::translate('Manage links'), 'inverselink.php?mediaid=' . $this->record->getXref() . '&linkto=manage&ged=' . WT_GEDCOM, 'menu-obje-edit');
				$submenu->addTarget('_blank');
			} else {
				$submenu = new WT_Menu(WT_I18N::translate('Set link'), '#', 'menu-obje-link');
				$ssubmenu = new WT_Menu(WT_I18N::translate('To individual'), '#', 'menu-obje-link-indi');
				$ssubmenu->addOnclick("return ilinkitem('".$this->record->getXref()."','person');");
				$submenu->addSubMenu($ssubmenu);

				$ssubmenu = new WT_Menu(WT_I18N::translate('To family'), '#', 'menu-obje-link-fam');
				$ssubmenu->addOnclick("return ilinkitem('".$this->record->getXref()."','family');");
				$submenu->addSubMenu($ssubmenu);

				$ssubmenu = new WT_Menu(WT_I18N::translate('To source'), '#', 'menu-obje-link-sour');
				$ssubmenu->addOnclick("return ilinkitem('".$this->record->getXref()."','source');");
				$submenu->addSubMenu($ssubmenu);
			}

			$menu->addSubmenu($submenu);
		}

		// edit/view raw gedcom
		if (WT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-obje-editraw');
			$submenu->addOnclick("return edit_raw('".$this->record->getXref()."');");
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new WT_Menu(WT_I18N::translate('View GEDCOM Record'), '#', 'menu-obje-viewraw');
			if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT) {
				$submenu->addOnclick("return show_gedcom_record('new');");
			} else {
				$submenu->addOnclick("return show_gedcom_record();");
			}
			$menu->addSubmenu($submenu);
		}

		// delete
		if (WT_USER_CAN_EDIT) {
			$submenu = new WT_Menu(WT_I18N::translate('Delete'), '#', 'menu-obje-del');
			$submenu->addOnclick("if (confirm('".WT_Filter::escapeJS(WT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($this->record->getFullName())))."')) jQuery.post('action.php',{action:'delete-media',xref:'".$this->record->getXref()."'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('widget_favorites', WT_Module::getActiveModules())) {
			$submenu = new WT_Menu(
				/* I18N: Menu option.  Add [the current page] to the list of favorites */ WT_I18N::translate('Add to favorites'),
				'#',
				'menu-obje-addfav'
			);
			$submenu->addOnclick("jQuery.post('module.php?mod=widget_favorites&amp;mod_action=menu-add-favorite',{xref:'".$this->record->getXref()."'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		//-- get the link for the first submenu and set it as the link for the main menu
		if (isset($menu->submenus[0])) {
			$link = $menu->submenus[0]->onclick;
			$menu->addOnclick($link);
		}
		return $menu;
	}

	/**
	* return a list of facts
	* @return array
	*/
	function getFacts($includeFileName=true) {
		$facts = $this->record->getFacts(array());

		// Add some dummy facts to show additional information
		if ($this->record->fileExists()) {
			// get height and width of image, when available
			$imgsize = $this->record->getImageAttributes();
			if (!empty($imgsize['WxH'])) {
				$facts[] = new WT_Event('1 __IMAGE_SIZE__ '.$imgsize['WxH'], $this->record, 0);
			}
			//Prints the file size
			$facts[] = new WT_Event('1 __FILE_SIZE__ '.$this->record->getFilesize(), $this->record, 0);
		}

		sort_facts($facts);
		return $facts;
	}

	/**
	* edit menu items used in album tab and media list
	*/
	static function getMediaListMenu($mediaobject) {
		$html = '
			<div class="lightbox-menu">
				<ul class="makeMenu lb-menu">';
					$menu = new WT_Menu(WT_I18N::translate('Set link'));
					$menu->addClass('', '', 'lb-image_link');

					$submenu = new WT_Menu(WT_I18N::translate('To individual'), 'inverselink.php?mediaid=' . $mediaobject->getXref() . '&amp;linkto=person&ged=' . WT_GEDCOM, 'menu-obje-link-indi');
					$submenu->addTarget('_blank');
					$menu->addSubMenu($submenu);

					$submenu = new WT_Menu(WT_I18N::translate('To family'), 'inverselink.php?mediaid=' . $mediaobject->getXref() . '&amp;linkto=family&ged=' . WT_GEDCOM, 'menu-obje-link-fam');
					$submenu->addTarget('_blank');
					$menu->addSubMenu($submenu);

					$submenu = new WT_Menu(WT_I18N::translate('To source'), 'inverselink.php?mediaid=' . $mediaobject->getXref() . '&amp;linkto=source&ged=' . WT_GEDCOM, 'menu-obje-link-sour');
					$submenu->addTarget('_blank');
					$menu->addSubMenu($submenu);
					$html .= $menu->getMenuAsList();

					$menu = new WT_Menu(WT_I18N::translate('View details'), $mediaobject->getHtmlUrl());
					$menu->addClass('', '', 'lb-image_view');
					$html .= $menu->getMenuAsList();

					$menu = new WT_Menu(WT_I18N::translate('Edit details'), 'addmedia.php?action=editmedia&pid=' . $mediaobject->getXref(), 'menu-obje-edit');
					$menu->addClass('', '', 'lb-image_edit');
					$menu->addTarget('_blank');
					$html .= $menu->getMenuAsList();
				$html.='</ul>
			</div>
		';
		return $html;
	}
}
