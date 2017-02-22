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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Controller_GedcomRecord extends WT_Controller_Page {
	public $record; // individual, source, repository, etc.

	public function __construct() {
		parent::__construct();
		
		// We want robots to index this page
		$this->setMetaRobots('index,follow');
	
		// Set a page title
		if ($this->record) {
			$this->setCanonicalUrl($this->record->getHtmlUrl());
			if ($this->record->canDisplayName()) {
				// e.g. "John Doe" or "1881 Census of Wales"
				$this->setPageTitle($this->record->getFullName());
			} else {
				// e.g. "Individual" or "Source"
				$this->setPageTitle(WT_Gedcom_Tag::getLabel($this->record->getType()));
			}
		} else {
			// No such record
			$this->setPageTitle(WT_I18N::translate('Private'));
		}
	}
}
