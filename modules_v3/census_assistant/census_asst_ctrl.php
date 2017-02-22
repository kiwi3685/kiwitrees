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
 */$controller = new WT_Controller_Individual();

global $tabno, $linkToID, $SEARCH_SPIDER;
global $GEDCOM, $ABBREVIATE_CHART_LABELS;
global $show_full, $famid;

$summary = $controller->record->format_first_major_fact(WT_EVENTS_BIRT, 2);
if (!($controller->record->isDead())) {
	// If alive display age
	$bdate = $controller->record->getBirthDate();
	$age = WT_Date::GetAgeGedcom($bdate);
	if ($age != '') {
		$summary .= '
			<span class="label">' . WT_I18N::translate('Age') . ':</span>
			<span class="field">' . get_age_at_event($age, true) . '</span>
		';
	}
}
$summary .= $controller->record->format_first_major_fact(WT_EVENTS_DEAT, 2);

$controller->census_assistant();
