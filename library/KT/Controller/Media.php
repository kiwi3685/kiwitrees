<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

class KT_Controller_Media extends KT_Controller_GedcomRecord {

	public function __construct() {
		$xref = safe_GET_xref('mid');

		$gedrec = find_media_record($xref, KT_GED_ID);
		if (KT_USER_CAN_EDIT) {
			$newrec = find_updated_record($xref, KT_GED_ID);
		} else {
			$newrec = null;
		}

		if ($gedrec === null) {
			if ($newrec === null) {
				// Nothing to see here.
				parent::__construct();
				return;
			} else {
				// Create a dummy record from the first line of the new record.
				// We need it for diffMerge(), getXref(), etc.
				list($gedrec) = explode("\n", $newrec);
			}
		}

		$this->record = new KT_Media($gedrec);

		// If there are pending changes, merge them in.
		if ($newrec !== null) {
			$diff_record = new KT_Media($newrec);
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
		$menu = new KT_Menu(KT_I18N::translate('Edit'), 'addmedia.php?action=editmedia&amp;pid=' . $this->record->getXref(), 'menu-obje');
		$menu->addTarget('_blank');

		if (KT_USER_CAN_EDIT) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit media object'), 'addmedia.php?action=editmedia&amp;pid=' . $this->record->getXref(), 'menu-obje-edit');
			$submenu->addTarget('_blank');
			$menu->addSubmenu($submenu);

			$submenu = new KT_Menu(KT_I18N::translate('Manage links'), 'inverselink.php?mediaid=' . $this->record->getXref() . '&linkto=manage&ged=' . KT_GEDCOM, 'menu-obje-link');
			$submenu->addTarget('_blank');
			$menu->addSubmenu($submenu);
		}

		// edit/view raw gedcom
		if (KT_USER_IS_ADMIN || $SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('Edit raw GEDCOM record'), '#', 'menu-obje-editraw');
			$submenu->addOnclick("return edit_raw('" . $this->record->getXref() . "');");
			$menu->addSubmenu($submenu);
		} elseif ($SHOW_GEDCOM_RECORD) {
			$submenu = new KT_Menu(KT_I18N::translate('View GEDCOM Record'), '#', 'menu-obje-viewraw');
			if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) {
				$submenu->addOnclick("return show_gedcom_record('new');");
			} else {
				$submenu->addOnclick("return show_gedcom_record();");
			}
			$menu->addSubmenu($submenu);
		}

		// delete
		if (KT_USER_CAN_EDIT) {
			$submenu = new KT_Menu(KT_I18N::translate('Delete'), '#', 'menu-obje-del');
			$submenu->addOnclick("if (confirm('" . KT_Filter::escapeJS(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($this->record->getFullName())))."')) jQuery.post('action.php',{action:'delete-media',xref:'" . $this->record->getXref() . "'},function(){location.reload();})");
			$menu->addSubmenu($submenu);
		}

		// add to favorites
		if (array_key_exists('widget_favorites', KT_Module::getActiveModules())) {
			$submenu = new KT_Menu(
				/* I18N: Menu option.  Add [the current page] to the list of favorites */ KT_I18N::translate('Add to favorites'),
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
				$facts[] = new KT_Event('1 __IMAGE_SIZE__ '.$imgsize['WxH'], $this->record, 0);
			}
			//Prints the file size
			$facts[] = new KT_Event('1 __FILE_SIZE__ '.$this->record->getFilesize(), $this->record, 0);
		}

		sort_facts($facts);
		return $facts;
	}

	/**
	* edit menu items used in media list
	*/
	static function getMediaListMenu($mediaobject) {
		$html = '
			<div class="lightbox-menu">
				<ul class="makeMenu lb-menu">';

					$menu = new KT_Menu(KT_I18N::translate('Manage links'), 'inverselink.php?mediaid=' . $mediaobject->getXref() . '&linkto=manage&ged=' . KT_GEDCOM);
					$menu->addClass('', '', 'lb-image_link');
					$menu->addTarget('_blank');
					$html .= $menu->getMenuAsList();

					$menu = new KT_Menu(KT_I18N::translate('View details'), $mediaobject->getHtmlUrl());
					$menu->addClass('', '', 'lb-image_view');
					$html .= $menu->getMenuAsList();

					$menu = new KT_Menu(KT_I18N::translate('Edit details'), 'addmedia.php?action=editmedia&amp;pid=' . $mediaobject->getXref(), 'menu-obje-edit');
					$menu->addClass('', '', 'lb-image_edit');
					$menu->addTarget('_blank');
					$html .= $menu->getMenuAsList();
				$html.='</ul>
			</div>
		';
		return $html;
	}
}
