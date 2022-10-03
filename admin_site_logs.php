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

define('KT_SCRIPT_NAME', 'admin_site_logs.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Logs'));

require KT_ROOT.'includes/functions/functions_edit.php';

$earliest	= KT_DB::prepare("SELECT DATE(MIN(log_time)) FROM `##log`")->execute(array())->fetchOne();
$latest		= KT_DB::prepare("SELECT DATE(MAX(log_time)) FROM `##log`")->execute(array())->fetchOne();

// Filtering
$action	= KT_Filter::get('action');
$from	= KT_Filter::get('from', '\d\d\d\d-\d\d-\d\d', $earliest);
$to		= KT_Filter::get('to',   '\d\d\d\d-\d\d-\d\d', $latest);
$type	= safe_GET('type', array('auth','change','config','debug','edit','error','media','search','spam'));
$text	= KT_Filter::get('text');
$ip		= KT_Filter::get('ip');
$user	= KT_Filter::get('user');
if (KT_USER_IS_ADMIN) {
	// Administrators can see all logs
	$gedc = KT_Filter::get('gedc');
} else {
	// Managers can only see logs relating to this gedcom
	$gedc = KT_GEDCOM;
}

$query=array();
$args =array();
if ($from) {
	$query[]='log_time>=?';
	$args []=$from;
}
if ($to) {
	$query[]='log_time<TIMESTAMPADD(DAY, 1 , ?)'; // before end of the day
	$args []=$to;
}
if ($type) {
	$query[]='log_type=?';
	$args []=$type;
}
if ($text) {
	$query[]="log_message LIKE CONCAT('%', ?, '%')";
	$args []=$text;
}
if ($ip) {
	$query[]="ip_address LIKE CONCAT('%', ?, '%')";
	$args []=$ip;
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
	"SELECT SQL_CALC_FOUND_ROWS log_time, log_type, log_message, ip_address, IFNULL(user_name, '<none>') AS user_name, IFNULL(gedcom_name, '<none>') AS gedcom_name".
	" FROM `##log`".
	" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
	" LEFT JOIN `##gedcom` USING (gedcom_id)"; // gedcom may be deleted
$SELECT2=
	"SELECT COUNT(*) FROM `##log`".
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
		"DELETE `##log` FROM `##log`".
		" LEFT JOIN `##user`   USING (user_id)".   // user may be deleted
		" LEFT JOIN `##gedcom` USING (gedcom_id)". // gedcom may be deleted
		$WHERE;
	KT_DB::prepare($DELETE)->execute($args);
	break;
case 'export':
	Zend_Session::writeClose();
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="kiwitrees-logs.csv"');
	$rows=KT_DB::prepare($SELECT1.$WHERE.' ORDER BY log_id')->execute($args)->fetchAll();
	foreach ($rows as $row) {
		echo
			'"', $row->log_time, '",',
			'"', $row->log_type, '",',
			'"', str_replace('"', '""', $row->log_message), '",',
			'"', $row->ip_address, '",',
			'"', str_replace('"', '""', $row->user_name), '",',
			'"', str_replace('"', '""', $row->gedcom_name), '"',
			"\n";
	}
	exit;
case 'load_json':
	Zend_Session::writeClose();
	$iDisplayStart	= (int) KT_Filter::get('iDisplayStart');
	$iDisplayLength	= (int) KT_Filter::get('iDisplayLength');
	set_user_setting(KT_USER_ID, 'admin_site_log_page_size', $iDisplayLength);
	if ($iDisplayLength > 0) {
		$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
	} else {
		$LIMIT = "";
	}
	$iSortingCols	= KT_Filter::get('iSortingCols');
	if ($iSortingCols) {
		$ORDER_BY	= ' ORDER BY ';
		for ($i = 0; $i < $iSortingCols; ++$i) {
			// Datatables numbers columns 0, 1, 2, ...
			// MySQL numbers columns 1, 2, 3, ...
			switch (KT_Filter::get('sSortDir_' . $i)) {
			case 'asc':
				if ((int) KT_Filter::get('iSortCol_' . $i) == 0) {
					$ORDER_BY.='log_id ASC '; // column 0 is "timestamp", using log_id gives the correct order for events in the same second
				} else {
					$ORDER_BY.=(1+(int) KT_Filter::get('iSortCol_'.$i)).' ASC ';
				}
				break;
			case 'desc':
				if ((int) KT_Filter::get('iSortCol_'.$i)==0) {
					$ORDER_BY .= 'log_id DESC ';
				} else {
					$ORDER_BY .= (1 + (int) KT_Filter::get('iSortCol_'.$i)).' DESC ';
				}
				break;
			}
			if ($i<$iSortingCols-1) {
				$ORDER_BY .= ',';
			}
		}
	} else {
		$ORDER_BY = '1 DESC';
	}

	// This becomes a JSON list, not array, so need to fetch with numeric keys.
	$aaData = KT_DB::prepare($SELECT1.$WHERE.$ORDER_BY.$LIMIT)->execute($args)->fetchAll(PDO::FETCH_NUM);
	foreach ($aaData as &$row) {
		$row[2] = htmlspecialchars((string) $row[2]);
	}

	// Total filtered/unfiltered rows
	$iTotalDisplayRecords	= KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
	$iTotalRecords			= KT_DB::prepare($SELECT2.$WHERE)->execute($args)->fetchColumn();

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'               => (int) KT_Filter::get('sEcho'),
		'iTotalRecords'       => $iTotalRecords,
		'iTotalDisplayRecords'=> $iTotalDisplayRecords,
		'aaData'              => $aaData
	));
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
	->addInlineJavascript('
		var oTable=jQuery("#log_list").dataTable( {
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "' .
				KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?action=load_json&from=' . $from . '&to=' . $to . '&type=' . $type . '&text=' . rawurlencode((string) $text) . '&ip=' . rawurlencode((string) $ip) . '&user=' . rawurlencode((string) $user) . '&gedc=' . rawurlencode((string) $gedc) . '",' . KT_I18N::datatablesI18N(array(10,20,50,100,500,1000,-1)) . ',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"aaSorting": [[ 0, "desc" ]],
			"iDisplayLength": ' . get_user_setting(KT_USER_ID, 'admin_site_log_page_size', 20) . ',
			"sPaginationType": "full_numbers"
		});
	');

$url=
	KT_SCRIPT_NAME.'?from='.rawurlencode((string) $from).
	'&amp;to='.rawurlencode((string) $to).
	'&amp;type='.rawurlencode((string) $type).
	'&amp;text='.rawurlencode((string) $text).
	'&amp;ip='.rawurlencode((string) $ip).
	'&amp;user='.rawurlencode((string) $user).
	'&amp;gedc='.rawurlencode((string) $gedc);

$users_array=array_combine(get_all_users(), get_all_users());
uksort($users_array, 'strnatcasecmp');

echo
	'<form name="logs" method="get" action="'.KT_SCRIPT_NAME.'">',
		'<input type="hidden" name="action" value="show">',
		'<table class="site_logs">',
			'<tr>',
				'<td colspan="6">',
					// I18N: %s are both user-input date fields
					KT_I18N::translate('From %s to %s', '<input class="log-date" name="from" value="'.htmlspecialchars((string) $from).'">', '<input class="log-date" name="to" value="'.htmlspecialchars((string) $to).'">'),
				'</td>',
			'</tr><tr>',
				'<td>',
					KT_I18N::translate('Type'), '<br>', select_edit_control('type', array(''=>'', 'auth'=>'auth','config'=>'config','debug'=>'debug','edit'=>'edit','error'=>'error','media'=>'media','search'=>'search','spam'=>'spam'), null, $type, ''),
				'</td>',
				'<td>',
					KT_I18N::translate('Message'), '<br><input class="log-filter" name="text" value="', htmlspecialchars((string) $text), '"> ',
				'</td>',
				'<td>',
					KT_I18N::translate('IP address'), '<br><input class="log-filter" name="ip" value="', htmlspecialchars((string) $ip), '"> ',
				'</td>',
				'<td>',
					KT_I18N::translate('User'), '<br>', select_edit_control('user', $users_array, '', $user, ''),
				'</td>',
				'<td>',
					KT_I18N::translate('Family tree'), '<br>',  select_edit_control('gedc', KT_Tree::getNameList(), '', $gedc, KT_USER_IS_ADMIN ? '' : 'disabled'),
				'</td>',
			'</tr><tr>',
				'<td colspan="6">',
					'<input type="submit" value="', KT_I18N::translate('Filter'), '">',
					'<input type="submit" value="', KT_I18N::translate('Export'), '" onclick="document.logs.action.value=\'export\';return true;" ', ($action=='show' ? '' : 'disabled="disabled"'),'>',
					'<input type="submit" value="', KT_I18N::translate('Delete'), '" onclick="if (confirm(\'', htmlspecialchars(KT_I18N::translate('Permanently delete these records?')) , '\')) {document.logs.action.value=\'delete\';return true;} else {return false;}" ', ($action=='show' ? '' : 'disabled="disabled"'),'>',
				'</td>',
			'</tr>',
		'</table>',
	'</form>';

if ($action) {
	echo
		'<br>',
		'<table id="log_list">',
			'<thead>',
				'<tr>',
					'<th>', KT_I18N::translate('Timestamp'), '</th>',
					'<th>', KT_I18N::translate('Type'), '</th>',
					'<th>', KT_I18N::translate('Message'), '</th>',
					'<th>', KT_I18N::translate('IP address'), '</th>',
					'<th>', KT_I18N::translate('User'), '</th>',
					'<th>', KT_I18N::translate('Family tree'), '</th>',
				'</tr>',
			'</thead>',
			'<tbody>',
	 	'</tbody>',
		'</table>';
}
