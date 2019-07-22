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

define('KT_SCRIPT_NAME', 'statisticsTables.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Page();
$controller
	->setPageTitle(KT_I18N::translate('Statistics tables'))
	->pageHeader();

global $GEDCOM;
$ged_id	= get_id_from_gedcom($GEDCOM);
$stats	= new KT_Stats($GEDCOM);
$table	= KT_Filter::get('table');
$option	= KT_Filter::get('option');


switch ($table) {
	case 'totalIndis':
		$title 		= KT_I18N::translate('Total individuals');
		$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'male'));
		switch ($option){
			case 'male':
				$title 		= KT_I18N::translate('Total males');
				$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'male'));
				break;
			case 'female':
				$title 		= KT_I18N::translate('Total females');
				$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'female'));
				break;
			case 'unknown':
				$title 		= KT_I18N::translate('Total unknown gender');
				$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'unknown'));
				break;
			case 'living':
				$title 		= KT_I18N::translate('Total living');
				$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'living'));
				break;
			case 'deceased':
				$title 		= KT_I18N::translate('Total deceased');
				$content	= format_indi_table(KT_Stats::individualsList($ged_id, 'deceased'));
				break;
		}
	break;
	default:
		$title 		= '';
		$content	= KT_I18N::translate('No table selected');
}

?>
<div id="statTables-page">
	<h2 class="center">
		<?php echo $title; ?>
	</h2>
	<div>
		<?php echo $content; ?>
	</div>
</div>
