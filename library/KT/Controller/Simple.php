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

class KT_Controller_Simple extends KT_Controller_Page {

	// Popup windows don't always need a title
	public function __construct() {
		parent::__construct();
		$this->setPageTitle(KT_KIWITREES);
	}

	// Simple (i.e. popup) windows are deprecated.
	public function pageHeader($maintenance=false) {
		global $view;
		$view = 'simple';
		parent::pageHeader();
		return $this;
	}

	// Restrict access
	public function requireAdminLogin() {
		if (!KT_USER_IS_ADMIN) {
			$this->addInlineJavascript('opener.window.location.reload(); window.close();');
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireManagerLogin($ged_id=KT_GED_ID) {
		if (
			$ged_id==KT_GED_ID && !KT_USER_GEDCOM_ADMIN ||
			$ged_id!=KT_GED_ID && userGedcomAdmin(KT_USER_ID, $gedcom_id)
		) {
			$this->addInlineJavascript('opener.window.location.reload(); window.close();');
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireMemberLogin() {
		if (!KT_USER_ID) {
			$this->addInlineJavascript('opener.window.location.reload(); window.close();');
			exit;
		}
		return $this;
	}
}
