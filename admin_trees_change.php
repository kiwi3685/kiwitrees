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

define('KT_SCRIPT_NAME', 'admin_trees_change.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Changes'));

require KT_ROOT.'includes/functions/functions_edit.php';
require_once KT_ROOT.'library/php-diff/lib/Diff.php';
require_once KT_ROOT.'library/php-diff/lib/Diff/Renderer/Html/SideBySide.php';

$statuses=array(
	''        =>'',
	'accepted'=>/* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('accepted'),
	'rejected'=>/* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('rejected'),
	'pending' =>/* I18N: the status of an edit accepted/rejected/pending */ KT_I18N::translate('pending' ),
);

$earliest=KT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change`")->execute(array())->fetchOne();
$latest  =KT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change`")->execute(array())->fetchOne();

// Filtering
$action=KT_Filter::get('action');
$from  =KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to    =KT_Filter::get('to',   '\d\d\d\d-\d\d-\d\d', $latest);
$type  =KT_Filter::get('type', 'accepted|rejected|pending');
$oldged=KT_Filter::get('oldged');
$newged=KT_Filter::get('newged');
$xref  =KT_Filter::get('xref');
$user  =KT_Filter::get('user');
if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc=KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc=KT_GEDCOM;
}

$query=array();
$args =array();
if ($from) {
	$query[]='change_time>=?';
	$args []=$from;
}
if ($to) {
	$query[]='change_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
	$args []=$to;
}
if ($type) {
	$query[]='status=?';
	$args []=$type;
}
if ($oldged) {
	$query[]="old_gedcom LIKE CONCAT('%', ?, '%')";
	$args []=$oldged;
}
if ($newged) {
	$query[]="new_gedcom LIKE CONCAT('%', ?, '%')";
	$args []=$newged;
}
if ($xref) {
	$query[]="xref = ?";
	$args []=$xref;
}
if ($user) {
	$query[]="user_name LIKE CONCAT('%', ?, '%')";
	$args []=$user;
}
if ($gedc) {
	$query[]="gedcom_name LIKE CONCAT('%', ?, '%')";
	$args []=$gedc;
}

$SELECT1=
	"SELECT SQL_CALC_FOUND_ROWS change_time, status, xref, old_gedcom, new_gedcom, IFNULL(user_name, '<none>') AS user_name, IFNULL(gedcom_name, '<none>') AS gedcom_name".
	" FROM `##change`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
$SELECT2=
	"SELECT COUNT(*) FROM `##change`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
if ($query) {
	$WHERE=" WHERE ".implode(' AND ', $query);
} else {
	$WHERE='';
}

switch($action) {
case 'delete':
	$DELETE=
		"DELETE `##change` FROM `##change`".
		" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
		" LEFT JOIN `##gedcom` USING (gedcom_id)". // gedcom may be deleted
		$WHERE;
	KT_DB::prepare($DELETE)->execute($args);
	break;
case 'export':
	Zend_Session::writeClose();
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="kiwitrees-changes.csv"');
	$rows=KT_DB::prepare($SELECT1.$WHERE.' ORDER BY change_id')->execute($args)->fetchAll();
	foreach ($rows as $row) {
		$row->old_gedcom = str_replace('"', '""', $row->old_gedcom);
		$row->old_gedcom = str_replace("\n", '""', $row->old_gedcom);
		$row->new_gedcom = str_replace('"', '""', $row->new_gedcom);
		$row->new_gedcom = str_replace("\n", '""', $row->new_gedcom);
		echo
			'"', $row->change_time, '",',
			'"', $row->status, '",',
			'"', $row->xref, '",',
			'"', $row->old_gedcom, '",',
			'"', $row->new_gedcom, '",',
			'"', str_replace('"', '""', $row->user_name), '",',
			'"', str_replace('"', '""', $row->gedcom_name), '"',
			"\n";
	}
	exit;
case 'load_json':
	Zend_Session::writeClose();
	$iDisplayStart =(int)KT_Filter::get('iDisplayStart');
	$iDisplayLength=(int)KT_Filter::get('iDisplayLength');
	set_user_setting(KT_USER_ID, 'admin_site_change_page_size', $iDisplayLength);
	if ($iDisplayLength>0) {
		$LIMIT=" LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
	} else {
		$LIMIT="";
	}
	$iSortingCols=KT_Filter::get('iSortingCols');
	if ($iSortingCols) {
		$ORDER_BY=' ORDER BY ';
		for ($i=0; $i<$iSortingCols; ++$i) {
			// Datatables numbers columns 0, 1, 2, ...
			// MySQL numbers columns 1, 2, 3, ...
			switch (KT_Filter::get('sSortDir_'.$i)) {
			case 'asc':
				if ((int)KT_Filter::get('iSortCol_'.$i)==0) {
					$ORDER_BY.='change_id ASC '; // column 0 is "timestamp", using change_id gives the correct order for events in the same second
				} else {
					$ORDER_BY.=(1+(int)KT_Filter::get('iSortCol_'.$i)).' ASC ';
				}
				break;
			case 'desc':
				if ((int)KT_Filter::get('iSortCol_'.$i)==0) {
					$ORDER_BY.='change_id DESC ';
				} else {
					$ORDER_BY.=(1+(int)KT_Filter::get('iSortCol_'.$i)).' DESC ';
				}
				break;
			}
			if ($i<$iSortingCols-1) {
				$ORDER_BY.=',';
			}
		}
	} else {
		$ORDER_BY='1 DESC';
	}

	// This becomes a JSON list, not array, so need to fetch with numeric keys.
	$aaData=KT_DB::prepare($SELECT1.$WHERE.$ORDER_BY.$LIMIT)->execute($args)->fetchAll(PDO::FETCH_NUM);
	foreach ($aaData as &$row) {

		$a = explode("\n", htmlspecialchars($row[3]));
		$b = explode("\n", htmlspecialchars($row[4]));

		// Generate a side by side diff
		$renderer = new Diff_Renderer_Html_SideBySide;

		// Options for generating the diff
		$options = array(
			//'ignoreWhitespace' => true,
			//'ignoreCase' => true,
		);

		// Initialize the diff class
		$diff = new Diff($a, $b, $options);

		$row[1]=KT_I18N::translate($row[1]);
		$row[2]='<a href="gedrecord.php?pid='.$row[2].'&ged='.$row[6].'" target="_blank" rel="noopener noreferrer">'.$row[2].'</a>';
		$row[3]=$diff->Render($renderer);
		$row[4]='';
	}

	// Total filtered/unfiltered rows
	$iTotalDisplayRecords=KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
	$iTotalRecords=KT_DB::prepare($SELECT2.$WHERE)->execute($args)->fetchColumn();

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'               =>(int)KT_Filter::get('sEcho'),
		'iTotalRecords'       =>$iTotalRecords,
		'iTotalDisplayRecords'=>$iTotalDisplayRecords,
		'aaData'              =>$aaData
	));
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
	->addInlineJavascript('
		var oTable=jQuery("#change_list").dataTable( {
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?action=load_json&from='.$from.'&to='.$to.'&type='.$type.'&oldged='.rawurlencode($oldged).'&newged='.rawurlencode($newged).'&xref='.rawurlencode($xref).'&user='.rawurlencode($user).'&gedc='.rawurlencode($gedc).'",
			'.KT_I18N::datatablesI18N(array(10,20,50,100,500,1000,-1)).',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"aaSorting": [[ 0, "desc" ]],
			"iDisplayLength": '.get_user_setting(KT_USER_ID, 'admin_site_change_page_size', 10).',
			"sPaginationType": "full_numbers",
			"aoColumns": [
			/* Timestamp   */ {},
			/* Status      */ {},
			/* Record      */ {},
			/* Old data    */ {"sClass":"raw_gedcom"},
			/* New data    */ { bVisible:false },
			/* User        */ {},
			/* Family tree */ {}
			]
		});
	');

$url=
	KT_SCRIPT_NAME.'?from='.rawurlencode($from).
	'&amp;to='.rawurlencode($to).
	'&amp;type='.rawurlencode($type).
	'&amp;oldged='.rawurlencode($oldged).
	'&amp;newged='.rawurlencode($newged).
	'&amp;xref='.rawurlencode($xref).
	'&amp;user='.rawurlencode($user).
	'&amp;gedc='.rawurlencode($gedc);

$users_array=array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');

echo
	'<form name="changes" method="get" action="'.KT_SCRIPT_NAME.'">',
		'<input type="hidden" name="action", value="show">',
		'<table class="site_change">',
			'<tr>',
				'<td colspan="6">',
					// I18N: %s are both user-input date fields
					KT_I18N::translate('From %s to %s', '<input class="log-date" name="from" value="'.htmlspecialchars($from).'">', '<input class="log-date" name="to" value="'.htmlspecialchars($to).'">'),
				'</td>',
			'</tr><tr>',
				'<td>',
					KT_I18N::translate('Status'), '<br>', select_edit_control('type', $statuses, null, $type, ''),
				'</td>',
				'<td>',
					KT_I18N::translate('Record'), '<br><input class="log-filter" name="xref" value="', htmlspecialchars($xref), '"> ',
				'</td>',
				'<td>',
					KT_I18N::translate('Old data'), '<br><input class="log-filter" name="oldged" value="', htmlspecialchars($oldged), '"> ',
				'</td>',
				'<td></td>',
				'<td>',
					KT_I18N::translate('User'), '<br>', select_edit_control('user', $users_array, '', $user, ''),
				'</td>',
				'<td>',
					KT_I18N::translate('Family tree'), '<br>',  select_edit_control('gedc', KT_Tree::getNameList(), '', $gedc, KT_USER_IS_ADMIN ? '' : 'disabled'),
				'</td>',
			'</tr><tr>',
				'<td colspan="6">',
					'<input type="submit" value="', KT_I18N::translate('Filter'), '">',
					'<input type="submit" value="', KT_I18N::translate('Export'), '" onclick="document.changes.action.value=\'export\';return true;" ', ($action=='show' ? '' : 'disabled="disabled"'),'>',
					'<input type="submit" value="', KT_I18N::translate('Delete'), '" onclick="if (confirm(\'', htmlspecialchars(KT_I18N::translate('Permanently delete these records?')) , '\')) {document.changes.action.value=\'delete\';return true;} else {return false;}" ', ($action=='show' ? '' : 'disabled="disabled"'),'>',
				'</td>',
			'</tr>',
		'</table>',
	'</form>';

if ($action) {
	echo
		'<br>',
		'<table id="change_list">',
			'<thead>',
				'<tr>',
					'<th>', KT_I18N::translate('Timestamp'), '</th>',
					'<th>', KT_I18N::translate('Status'), '</th>',
					'<th>', KT_I18N::translate('Record'), '</th>',
					'<th>', KT_I18N::translate('GEDCOM Data'), '</th>',
					'<th></th>',
					'<th>', KT_I18N::translate('User'), '</th>',
					'<th>', KT_I18N::translate('Family tree'), '</th>',
				'</tr>',
			'</thead>',
			'<tbody>',
	 	'</tbody>',
		'</table>';
}
