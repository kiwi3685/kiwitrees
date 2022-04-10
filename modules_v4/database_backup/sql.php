<?php
/* ----------------------------------------------------------------------

   MyOOS [Dumper]
   http://www.oos-shop.de/

   Copyright (c) 2013 - 2022 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   MySqlDumper
   http://www.mysqldumper.de

   Copyright (C)2004-2011 Daniel Schlichtholz (admin@mysqldumper.de)
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

define('OOS_VALID_MOD', true);

if (!@ob_start('ob_gzhandler')) {
    @ob_start();
}

$download = (isset($_POST['f_export_submit']) && (isset($_POST['f_export_sendresult']) && 1 == $_POST['f_export_sendresult']));
include './inc/header.php';
include 'language/'.$config['language'].'/lang.php';
include 'language/'.$config['language'].'/lang_sql.php';
include './inc/functions_sql.php';
include './'.$config['files']['parameter'];
include './inc/template.php';
include './inc/define_icons.php';
$key = '';
// stripslashes and trimming is done in runtime.php which is included and executet above
if (isset($_GET['rk'])) {
    $rk = urldecode($_GET['rk']);
    $key = urldecode($rk);
    if (!$rk = @unserialize($key)) {
        $rk = $key;
    }
} else {
    $rk = '';
}
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';

if (isset($_GET['recordkey'])) {
    $recordkey = $_GET['recordkey'];
    $key = isset($_GET['recordkey']) ? urldecode($recordkey) : $recordkey;
    if (!$recordkey = @unserialize(urldecode($key))) {
        $recordkey = urldecode($key);
    }
}
if (isset($_POST['recordkey'])) {
    $recordkey = urldecode($_POST['recordkey']);
}

$context = (!isset($_GET['context'])) ? 0 : $_GET['context'];
$context = (!isset($_POST['context'])) ? $context : $_POST['context'];

if (!$download) {
    echo MODHeader();
    ReadSQL();
    echo '<script>
		var auswahl  =  "document.getElementsByName(\"f_export_tables[]\")[0]";
		var msg1 = "'.$lang['L_SQL_NOTABLESSELECTED'].'";
		</script>';
}
//Variabeln
$mysql_help_ref = 'http://dev.mysql.com/doc/';
$mysqli_errorhelp_ref = 'http://dev.mysql.com/doc/mysql/en/error-handling.html';
$no_order = false;
$config['interface_table_compact'] = isset($config['interface_table_compact']) ? $config['interface_table_compact'] : 1;
$tdcompact = (isset($_GET['tdc'])) ? $_GET['tdc'] : $config['interface_table_compact'];
$db = (!isset($_GET['db'])) ? $databases['db_actual'] : $_GET['db'];
$dbid = (!isset($_GET['dbid'])) ? $databases['db_selected_index'] : $_GET['dbid'];
$context = (!isset($_GET['context'])) ? 0 : $_GET['context'];
$context = (!isset($_POST['context'])) ? $context : $_POST['context'];
$tablename = (!isset($_GET['tablename'])) ? '' : $_GET['tablename'];
$limitstart = (isset($_POST['limitstart'])) ? intval($_POST['limitstart']) : 0;
if (isset($_GET['limitstart'])) {
    $limitstart = intval($_GET['limitstart']);
}
$orderdir = (!isset($_GET['orderdir'])) ? '' : $_GET['orderdir'];
$order = (!isset($_GET['order'])) ? '' : $_GET['order'];
$sqlconfig = (isset($_GET['sqlconfig'])) ? 1 : 0;
$norder = ('DESC' == $orderdir) ? 'ASC' : 'DESC';
$sql['order_statement'] = ('' != $order) ? ' ORDER BY `'.$order.'` '.$norder : '';
$sql['sql_statement'] = (isset($_GET['sql_statement'])) ? urldecode($_GET['sql_statement']) : '';
if (isset($_POST['sql_statement'])) {
    $sql['sql_statement'] = $_POST['sql_statement'];
}

$showtables = (!isset($_GET['showtables'])) ? 0 : $_GET['showtables'];
$limit = $add_sql = '';
$bb = (isset($_GET['bb'])) ? $_GET['bb'] : -1;
if (isset($_POST['tablename'])) {
    $tablename = $_POST['tablename'];
}
$search = (isset($_GET['search'])) ? $_GET['search'] : 0;

//SQL-Statement geposted
if (isset($_POST['execsql'])) {
    $sql['sql_statement'] = (isset($_POST['sqltextarea'])) ? $_POST['sqltextarea'] : '';
    $db = $_POST['db'];
    $dbid = $_POST['dbid'];
    $tablename = $_POST['tablename'];
    if (isset($_POST['tablecombo']) && $_POST['tablecombo'] > '') {
        $sql['sql_statement'] = $_POST['tablecombo'];
        $tablename = ExtractTablenameFromSQL($sql['sql_statement']);
    }
    if (isset($_POST['sqltextarea']) && $_POST['sqltextarea'] > '') {
        $tablename = ExtractTablenameFromSQL($_POST['sqltextarea']);
    }
    if ('' == $tablename) {
        $tablename = ExtractTablenameFromSQL($sql['sql_statement']);
    }
}

if ('' == $sql['sql_statement']) {
    if ('' != $tablename && 0 == $showtables) {
        $sql['sql_statement'] = "SELECT * FROM `$tablename`";
    } else {
        $sql['sql_statement'] = "SHOW TABLE STATUS FROM `$db`";
        $showtables = 1;
    }
}

//sql-type
$sql_to_display_data = 0;
$Anzahl_SQLs = getCountSQLStatements($sql['sql_statement']);
$sql_to_display_data = sqlReturnsRecords($sql['sql_statement']);
if ($Anzahl_SQLs > 1) {
    $sql_to_display_data = 0;
}
if (1 == $sql_to_display_data) {
    // only one SQL statement
    $limitende = ($limitstart + $config['sql_limit']);

    // Is it allowed to edit?
    $no_edit = ('SELECT' != strtoupper(substr($sql['sql_statement'], 0, 6)) || 1 == $showtables || preg_match('@^((-- |#)[^\n]*\n|/\*.*?\*/)*(UNION|JOIN)@im', $sql['sql_statement']));
    if ($no_edit) {
        $no_order = true;
    }

    // May be sorted?
    $op = strpos(strtoupper($sql['sql_statement']), ' ORDER ');
    if ($op > 0) {
        //is order by last ?
        $sql['order_statement'] = substr($sql['sql_statement'], $op);
        if (strpos($sql['order_statement'], ')') > 0) {
            $sql['order_statement'] = '';
        } else {
            $sql['sql_statement'] = substr($sql['sql_statement'], 0, $op);
        }
    }
}

if (isset($_POST['tableselect']) && '1' != $_POST['tableselect']) {
    $tablename = $_POST['tableselect'];
}
mod_mysqli_connect();
mysqli_select_db($config['dbconnection'], $db);

///*** EDIT / UPDATES / INSERTS ***///
///***                          ***///

// handle update action after submitting it
if (isset($_POST['update']) || isset($_GET['update'])) {
    GetPostParams();
    $f = explode('|', $_POST['feldnamen']);
    $sqlu = 'UPDATE `'.$_POST['db'].'`.`'.$tablename.'` SET ';
    for ($i = 0; $i < count($f); ++$i) {
        $index = isset($_POST[$f[$i]]) ? $f[$i] : correct_post_index($f[$i]);
        // Check if field is set to null
        if (isset($_POST['null_'.$index])) {
            // Yes, set it to NULL in Querystring
            $sqlu .= '`'.$f[$i].'`=NULL, ';
        } else {
            $sqlu .= '`'.$f[$i].'`=\''.db_escape(convert_to_latin1($_POST[$index])).'\', ';
        }
    }
    $sqlu = substr($sqlu, 0, strlen($sqlu) - 2).' WHERE '.$recordkey;
    $res = mod_query($sqlu);
    $msg = '<p class = "success">'.$lang['L_SQL_RECORDUPDATED'].'</p>';
    if (isset($mode) && 'searchedit' == $mode) {
        $search = 1;
    }
    $sql_to_display_data = 1;
}
// handle insert action after submitting it
if (isset($_POST['insert'])) {
    GetPostParams();
    $f = explode('|', $_POST['feldnamen']);
    $sqlu = 'INSERT INTO `'.$tablename.'` SET ';
    for ($i = 0; $i < count($f); ++$i) {
        $index = isset($_POST[$f[$i]]) ? $f[$i] : correct_post_index($f[$i]);
        if (isset($_POST['null_'.$index])) {
            // Yes, set it to NULL in Querystring
            $sqlu .= '`'.$f[$i].'` = NULL, ';
        } else {
            $sqlu .= '`'.$f[$i].'` = \''.db_escape(convert_to_latin1($_POST[$index])).'\', ';
        }
    }
    $sqlu = substr($sqlu, 0, strlen($sqlu) - 2);
    $res = mod_query($sqlu);
    $msg = '<p class = "success">'.$lang['L_SQL_RECORDINSERTED'].'</p>';
    $sql_to_display_data = 1;
}

if (isset($_POST['cancel'])) {
    GetPostParams();
}

//Tabellenansicht
$showtables = ('SHOW TABLE' == substr(strtoupper($sql['sql_statement']), 0, 10)) ? 1 : 0;
$tabellenansicht = ('SHOW ' == substr(strtoupper($sql['sql_statement']), 0, 5)) ? 1 : 0;

if (!isset($limitstart)) {
    $limitstart = 0;
}
$limitende = $config['sql_limit'];
if ('select' == strtolower(substr($sql['sql_statement'], 0, 6))) {
    $limit = ' LIMIT '.$limitstart.', '.$limitende.';';
}

$params = 'sql.php?db='.$db.'&amp;tablename='.$tablename.'&amp;dbid='.$dbid.'&amp;context='.$context.'&amp;sql_statement='.urlencode($sql['sql_statement']).'&amp;tdc='.$tdcompact.'&amp;showtables='.$showtables;
if ('' != $order) {
    $params .= '&amp;order='.$order.'&amp;orderdir='.$orderdir.'&amp;context='.$context;
}
if ($bb > -1) {
    $params .= '&amp;bb='.$bb;
}

$aus = headline($lang['L_SQL_BROWSER']);

if (0 == $search && !$download) {
    echo $aus;
    $aus = '';
    include './inc/sqlbrowser/sqlbox.php';

    if ($mode > '' && 0 == $context) {
        if (isset($recordkey) && $recordkey > '') {
            $rk = urldecode($recordkey);
        }
        if (isset($_GET['tablename'])) {
            $tablename = $_GET['tablename'];
        }

        if ('kill' == $mode || 'kill_view' == $mode) {
            if (0 == $showtables) {
                $sqlk = "DELETE FROM `$tablename` WHERE ".$rk.' LIMIT 1';
                $res = mod_query($sqlk);
                //echo "<br>".$sqlk;
                $aus .= '<p class = "success">'.$lang['L_SQL_RECORDDELETED'].'</p>';
            } else {
                $sqlk = "DROP TABLE `$rk`";
                if ('kill_view' == $mode) {
                    $sqlk = 'DROP VIEW `'.$rk.'`';
                }
                $res = mod_query($sqlk);
                $aus .= '<p class = "success">'.sprintf($lang['L_SQL_RECORDDELETED'], $rk).'</p>';
            }
        }
        if ('empty' == $mode) {
            if (0 != $showtables) {
                $sqlk = "TRUNCATE `$rk`";
                $res = mod_query($sqlk);
                $aus .= '<p class = "success">'.sprintf($lang['L_SQL_TABLEEMPTIED'], $rk).'</p>';
            }
        }
        if ('emptyk' == $mode) {
            if (0 != $showtables) {
                $sqlk = "TRUNCATE `$rk`;";
                $res = mod_query($sqlk);
                $sqlk = "ALTER TABLE `$rk` AUTO_INCREMENT = 1;";
                $res = mod_query($sqlk);
                $aus .= '<p class = "success">'.sprintf($lang['L_SQL_TABLEEMPTIEDKEYS'], $rk).'</p>';
            }
        }

        $javascript_switch = '<script>
function switch_area(textarea)
{
	var t = document.getElementById(\'area_\'+textarea);
	var c = document.getElementById(\'null_\'+textarea);
	if (c.checked == true) { t.className = "off";t.disabled = true;  }
	else { t.className = "";t.disabled = false;  }
}
</script>';

        if ('edit' == $mode || 'searchedit' == $mode) {
            include './inc/sqlbrowser/sql_record_update_inputmask.php';
        }
        if ('new' == $mode) {
            include './inc/sqlbrowser/sql_record_insert_inputmask.php';
        }
    }
    if (0 == $context) {
        include_once './inc/sqlbrowser/sql_dataview.php';
    }
    if (1 == $context) {
        include './inc/sqlbrowser/sql_commands.php';
    }
    if (2 == $context) {
        include './inc/sqlbrowser/sql_tables.php';
    }
    if (3 == $context) {
        include './inc/sql_tools.php';
    }
}
if (4 == $context) {
    include './inc/sql_importexport.php';
}
if (1 == $search) {
    include './inc/sqlbrowser/mysql_search.php';
}

if (!$download) {
    ?>
<script>
function BrowseInput(el)
{
	var txt = document.getElementsByName('imexta')[0].value;
	var win = window.open('about:blank','MOD_Output','resizable = 1,scrollbars = yes');
	win.document.write(txt);
	win.document.close();
	win.focus();
}
</script>
<?php

    echo '<br><br><br>';
    echo MODFooter();
    ob_end_flush();
    exit();
}

function FormHiddenParams()
{
    global $db, $dbid, $tablename, $context, $limitstart, $order, $orderdir;

    $s = '<input type = "hidden" name = "db" value = "'.$db.'">';
    $s .= '<input type = "hidden" name = "dbid" value = "'.$dbid.'">';
    $s .= '<input type = "hidden" name = "tablename" value = "'.$tablename.'">';
    $s .= '<input type = "hidden" name = "context" value = "'.$context.'">';
    $s .= '<input type = "hidden" name = "limitstart" value = "'.$limitstart.'">';
    $s .= '<input type = "hidden" name = "order" value = "'.$order.'">';
    $s .= '<input type = "hidden" name = "orderdir" value = "'.$orderdir.'">';

    return $s;
}
