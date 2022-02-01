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

require_once KT_ROOT.'includes/functions/functions_print_facts.php';
require_once KT_ROOT.'includes/functions/functions_import.php';

class KT_Controller_Note extends KT_Controller_GedcomRecord {
	public function __construct() {
		$xref=safe_GET_xref('nid');

		$gedrec=find_other_record($xref, KT_GED_ID);
		if (KT_USER_CAN_EDIT) {
			$newrec=find_updated_record($xref, KT_GED_ID);
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
				[$gedrec] = explode("\n", $newrec);
			}
		}

		$this->record = new KT_Note($gedrec);

		// If there are pending changes, merge them in.
		if ($newrec!==null) {
			$diff_record=new KT_Note($newrec);
			$diff_record->setChanged(true);
			$this->record->diffMerge($diff_record);
		}

		parent::__construct();
	}

	/**
	* get edit menu
	*/
	function getEditMenu() {
		$SHOW_GEDCOM_RECORD=get_gedcom_setting(KT_GED_ID, 'SHOW_GEDCOM_RECORD');

		if (!$this->record || $this->record->isMarkedDeleted()) {
			return null;
		}

		// edit menu
		$menu = new KT_Menu(KT_I18N::translate('Edit'), '#', 'menu-note');

		if (KT_USER_CAN_EDIT) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit note'), '#', 'menu-note-edit');
			$submenu->addOnclick('return edit_note(\''.$this->record->getXref().'\');');
			$menu->addSubmenu($submenu);
		}

		// edit/view raw gedcom
		if (KT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-note-editraw');
			$submenu->addOnclick("return edit_raw('".$this->record->getXref()."');");
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('View GEDCOM Record'), '#', 'menu-note-viewraw');
			if (KT_USER_CAN_EDIT) {
				$submenu->addOnclick("return show_gedcom_record('new');");
			} else {
				$submenu->addOnclick("return show_gedcom_record();");
			}
			$menu->addSubmenu($submenu);
		}

		// delete
		if (KT_USER_CAN_EDIT) {
			$submenu = new KT_Menu(KT_I18N::translate('Delete'), '#', 'menu-note-del');
			$submenu->addOnclick("if (confirm('".KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($this->record->getFullName()))."')) jQuery.post('action.php',{action:'delete-note',xref:'".$this->record->getXref()."'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('widget_favorites', KT_Module::getActiveModules())) {
			$submenu = new KT_Menu(
				/* I18N: Menu option.  Add [the current page] to the list of favorites */ KT_I18N::translate('Add to favorites'),
				'#',
				'menu-note-addfav'
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
}
