<?php
/*
 * Plugin for simpl_research sidebar module
 * 
 * Copyright (C) 2013 Nigel Osborne and kiwtrees.net. All rights reserved.
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2013 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ancestry_plugin extends research_base_plugin {
	static function getName() {
		return 'Ancestry';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		$domain = array(
			// these are all the languages supported by ancestry. See: http://corporate.ancestry.com/about-ancestry/international/
			'de'		=> 'de',		// German
			'en_GB' 	=> 'co.uk',
			'en_US'		=> 'com',
			'en_AUS'	=> 'com.au',	// not used by webtrees
			'fr'		=> 'fr',
			'it'		=> 'it',
			'sv'		=> 'se',		// Swedish
			);
		// ancestry supports Canada in English and French versions, too; but webtrees doesn't support these language versions
		if (isset($domain[WT_LOCALE])) {
			$ancestry_domain = $domain[WT_LOCALE];
		} else {
			$ancestry_domain = $domain['en_US'];
		}

		return $link = 'http://search.ancestry.'
						.$ancestry_domain
						.'/cgi-bin/sse.dll?new=1&gsfn='
						.rawurlencode($givn)
						.'&gsln='
						.rawurlencode($surname)
						.'&gl=ROOT_CATEGORY&rank=1';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}
}