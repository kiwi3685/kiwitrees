<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

class KT_Controller_Chart extends KT_Controller_Page {
	public $root;
	public $rootid;
	public $error_message = null;

	public function __construct() {
		parent::__construct();

		$this->rootid = KT_Filter::get('rootid', KT_REGEX_XREF);
		if ($this->rootid) {
			$this->root = KT_Person::getInstance($this->rootid);
		} else {
			// Missing rootid parameter?  Do something.
			$this->root   = $this->getSignificantIndividual();
			$this->rootid = $this->root->getXref();
		}

		if (!$this->root || !$this->root->canDisplayName()) {
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
			$this->error_message=KT_I18N::translate('This individual does not exist or you do not have permission to view it.');
			$this->rootid = null;
		}
	}

	public function getSignificantIndividual() {
		if ($this->root) {
			return $this->root;
		} else {
			return parent::getSignificantIndividual();
		}
	}
}
