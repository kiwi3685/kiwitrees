<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

if (!defined('KT_KIWITREES') || !defined('KT_SCRIPT_NAME') || KT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'privacy_status':
	$title=KT_I18N::translate('Privacy status');
	$text='<style>#privacy_status_help dt{float:left;clear:left;width:100px;}#privacy_status_help dd{margin: 0 0 8px 110px;}html[dir=\'rtl\'] #privacy_status_help dt{float:right;clear:right;}html[dir=\'rtl\'] #privacy_status_help dd{margin: 0 110px 8px 0;</style>';
	$text.=KT_I18N::translate('There are three possible indicators of privacy status: Dead, Presumed dead, and Living.<br>If <u>either of the first two</u> are set, then the person will be displayed  in accordance with the family tree and site privacy settings.<br>');
	$text.=KT_I18N::translate('The age at which a person is assumed to be dead is set at %s years.', $MAX_ALIVE_AGE);
	$text.='<br><br><dl id="privacy_status_help">';
	$text.=KT_I18N::translate('<dt>Dead</dt><dd>Used when a person is clearly marked as dead by the inclusion of a death record with a date or date range.</dd>');
	$text.=KT_I18N::translate('<dt>Presumed dead</dt><dd>This is set when a person either has a death recorded but with no date, or has no death record but <b>kiwitrees</b> has calculated that the person can reasonably be expected to be dead.</dd>');
	$text.=KT_I18N::translate('<dt>Living</dt><dd>If there is no record of a death and no other related facts that imply death, then the person is assumed to be living.</dd>');
	$text.='</dl>';
	break;
}
