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
$tag	= KT_Filter::get('tag');


switch ($table) {
	case 'totalIndis':
		if ($option == NULL) {
			$title 		= KT_I18N::translate('Total individuals');
			$content	= format_indi_table($stats->individualsList($ged_id));
		} else {
			switch ($option){
				case 'male':
					$title 		= KT_I18N::translate('Total males');
					$content	= format_indi_table($stats->individualsList($ged_id, 'male'));
					break;
				case 'female':
					$title 		= KT_I18N::translate('Total females');
					$content	= format_indi_table($stats->individualsList($ged_id, 'female'));
					break;
				case 'unknown':
					$title 		= KT_I18N::translate('Total unknown gender');
					$content	= format_indi_table($stats->individualsList($ged_id, 'unknown'));
					break;
				case 'living':
					$title 		= KT_I18N::translate('Total living');
					$content	= format_indi_table($stats->individualsList($ged_id, 'living'));
					break;
				case 'deceased':
					$title 		= KT_I18N::translate('Total deceased');
					$content	= format_indi_table($stats->individualsList($ged_id, 'deceased'));
					break;
			}
		}
	break;
	case 'century': {
		switch ($tag) {
			case 'birt':
				$gTag	= 'BIRT';
				$label	= 'births';
			break;
			case 'deat':
				$gTag	= 'DEAT';
				$label	= 'deaths';
			break;
			case 'marr':
				$gTag	= 'MARR';
				$label	= 'marriages';
			break;
			case 'div':
				$gTag	= 'DIV';
				$label	= 'divorces';
			break;
		}

		$year = $option * 100 - 100;
		$rows = KT_DB::prepare("
			SELECT DISTINCT `d_gid` FROM `##dates`
				WHERE `d_file`=? AND
				`d_year` >= ? AND
				`d_year` < ? AND
				`d_fact`='" . $gTag . "' AND
				`d_type` IN ('@#DGREGORIAN@', '@#DJULIAN@')
		")->execute(array($ged_id, $year, $year + 100))->fetchAll(PDO::FETCH_ASSOC);
		$list	= array();

		switch ($tag){
			case 'birt':
			case 'deat':
				foreach ($rows as $row) {
					$person = KT_Person::getInstance($row['d_gid']);
						$list[] = clone $person;
				}
				$title 		= KT_I18N::translate('Number of %s in the %s century', $label, $stats->_centuryName($option));
				$content	= format_indi_table($list);
			break;
			case 'marr':
			case 'div':
				foreach ($rows as $row) {
					$family = KT_Family::getInstance($row['d_gid']);
						$list[] = clone $family;
				}
				$title 		= KT_I18N::translate('Number of %s in the %s century', $label, $stats->_centuryName($option));
				$content	= format_fam_table($list);
			break;
		}
	}
	break;
	case 'totalFams' :
		switch ($tag){
			case 'marr' :
				$title 		= KT_I18N::translate('Total marriages');
				$content	= format_fam_table($stats->totalEvents(array('MARR'), true));
			break;
			case 'div' :
				$title 		= KT_I18N::translate('Total divorces');
				$content	= format_fam_table($stats->totalEvents(array('DIV'), true));
			break;
		}
	break;
    case 'totalBirths' :
        $list       = $stats->totalBirths();
        $title 		= KT_I18N::translate('Total births');
        $content	= format_indi_table($list['list']);
    break;
    case 'datedBirths' :
        $list       = $stats->totaldatedBirths();
        $title 		= KT_I18N::translate('Total dated births');
        $content	= format_indi_table($list['list']);
    break;
    case 'undatedBirths' :
        $list       = $stats->totalUndatedBirths();
        $title 		= KT_I18N::translate('Total undated births');
        $content	= format_indi_table($list['list']);
    break;
    case 'totalDeaths' :
        $list       = $stats->totalDeaths();
        $title 		= KT_I18N::translate('Total deaths');
        $content	= format_indi_table($list['list']);
    break;
    case 'datedDeaths' :
        $list       = $stats->totaldatedDeaths();
        $title 		= KT_I18N::translate('Total dated deaths');
        $content	= format_indi_table($list['list']);
    break;
    case 'undatedDeaths' :
        $list       = $stats->totalUndatedDeaths();
        $title 		= KT_I18N::translate('Total undated deaths');
        $content	= format_indi_table($list['list']);
    break;

	default:
		$title 		= '';
		$content	= KT_I18N::translate('No table selected');
	break;
}

?>

<div id="statTables-page">
	<h2 class="center">
		<?php echo $title; ?>
	</h2>
	<?php if (!KT_USER_ID) { ?>
		<h4 class="center">
			<em>
				<?php echo KT_I18N::translate('Due to privacy settings the number of items in this list may be less than the number on the statistics chart'); ?>
			</em>
		</h4>
	<?php } ?>
	<div>
		<?php echo $content; ?>
	</div>
</div>

<?php
