<?php
/* ----------------------------------------------------------------------

   MyOOS [Dumper]
   http://www.oos-shop.de/

   Copyright (c) 2017 by the MyOOS Development Team.
   ----------------------------------------------------------------------
   Based on:

   MySqlDumper
   http://www.mysqldumper.de

   Copyright (C)2004-2011 Daniel Schlichtholz (admin@mysqldumper.de)
   ----------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------- */

if (!defined('MOD_VERSION')) {
    exit('No direct access.');
}

//SQL-Library laden
include './inc/sqllib.php';

if (!isset($config['sql_limit'])) {
    $config['sql_limit'] = 30;
}
if (!isset($config['bb_width'])) {
    $config['bb_width'] = 300;
}
if (!isset($config['bb_textcolor'])) {
    $config['bb_textcolor'] = '#990033';
}

function ReadSQL()
{
    global $SQL_ARRAY, $config;
    $sf = $config['paths']['config'].'sql_statements';
    if (!is_file($sf)) {
        $fp = fopen($sf, 'w+');
        fclose($fp);
        @chmod($sf, 0777);
    }

    if ((is_array($SQL_ARRAY) && 0 == count($SQL_ARRAY)) && filesize($sf) > 0) {
        $SQL_ARRAY = file($sf);
    }
}

function WriteSQL()
{
    global $SQL_ARRAY, $config;
    $sf = $config['paths']['config'].'sql_statements';
    $str = '';
    for ($i = 0; $i < count($SQL_ARRAY); ++$i) {
        $str .= $SQL_ARRAY[$i];
        if ("\n" != substr($str, -1) && $i != (count($SQL_ARRAY) - 1)) {
            $str .= "\n";
        }
    }

    $fp = fopen($sf, 'wb');
    fwrite($fp, $str);
    fclose($fp);
}

function SQL_Name($index)
{
    global $SQL_ARRAY;
    $s = explode('|', $SQL_ARRAY[$index]);
    return $s[0];
}

function SQL_String($index)
{
    global $SQL_ARRAY;
    if (isset($SQL_ARRAY[$index]) && !empty($SQL_ARRAY[$index])) {
        $s = explode('|', $SQL_ARRAY[$index], 2);
        return (isset($s[1])) ? $s[1] : '';
    }
}

function SQL_ComboBox()
{
    global $SQL_ARRAY, $tablename, $nl;
    $s = '';
    if (is_array($SQL_ARRAY) && count($SQL_ARRAY) > 0) {
        $s = $nl.$nl.'<select class="SQLCombo" name="sqlcombo" onchange="this.form.sqltextarea.value=this.options[this.selectedIndex].value;">'.$nl;
        $s .= '<option value="" selected>---</option>'.$nl;
        for ($i = 0; $i < count($SQL_ARRAY); ++$i) {
            $s .= '<option value="'.htmlspecialchars(stripslashes(SQL_String($i))).'">'.SQL_Name($i).'</option>'.$nl;
        }
        $s .= '</select>'.$nl.$nl;
    }
    return $s;
}

function Table_ComboBox()
{
    global $db, $config, $lang, $nl;
    $tabellen = mysqli_query($config['dbconnection'], 'SHOW TABLES FROM `'.$db.'`');
    $num_tables = 0;
    if (is_resource($tabellen)) {
        $num_tables = mysqli_num_rows($tabellen);
    }
    $s = $nl.$nl.'<select class="SQLCombo" name="tablecombo" onchange="this.form.sqltextarea.value=this.options[this.selectedIndex].value;this.form.execsql.click();">'.$nl.'<option value="" selected> ---  </option>'.$nl;
    for ($i = 0; $i < $num_tables; ++$i) {
        $t = mysqli_fetch_row($tabellen);
        $s .= '<option value="SELECT * FROM `'.$db.'`.`'.$t[0].'`">'.$lang['L_TABLE'].' `'.$t[0].'`</option>'.$nl;
    }
    $s .= '</select>'.$nl.$nl;
    return $s;
}

function TableComboBox($default = '')
{
    global $db, $config, $lang, $nl;

    $sql = "SHOW TABLES FROM $db";
    $tabellen = mod_query($sql);
    $s = '<option value="" '.(('' == $default) ? ' selected="selected"' : '').'>                 </option>'.$nl;
    while ($row = mysqli_fetch_row($tabellen)) {
        $t = $row[0];
        $s .= '<option value="`'.$t.'`"'.(($default == '`'.$t.'`') ? ' selected="selected"' : '').'>`'.$t.'`</option>'.$nl;
    }
    return $s;
}

function DB_Exists($db)
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $erg = false;

    $dbs = mod_query('SHOW DATABASES');
    while ($row = mysqli_fetch_assoc($dbs)) {
        if (strtolower($row['Database']) == strtolower($db)) {
            $erg = true;
            break;
        }
    }
    return $erg;
}

function Table_Exists($db, $table)
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $sqlt = "SHOW TABLES FROM `$db`";
    $res = mod_query($sqlt);
    if ($res) {
        $tables = [];
        while ($row = mysqli_fetch_row($res)) {
            $tables[] = $row[0];
        }
        if (in_array($table, $tables)) {
            return true;
        }
    }
    return false;
}

function DB_Empty($dbn)
{
    $r = "DROP DATABASE `$dbn`;\nCREATE DATABASE `$dbn`;";
    return MOD_DoSQL($r);
}

function sqlReturnsRecords($sql)
{
    global $mysql_SQLhasRecords;
    $s = explode(' ', $sql);
    return in_array(strtoupper($s[0]), $mysql_SQLhasRecords) ? 1 : 0;
}

function getCountSQLStatements($sql)
{
    $z = 0;
    $l = strlen($sql);
    $inQuotes = false;
    for ($i = 0; $i < $l; ++$i) {
        if ("'" == $sql[$i] || '"' == $sql[$i]) {
            $inQuotes = !$inQuotes;
        }
        if ((';' == $sql[$i] && false == $inQuotes) || $i == $l - 1) {
            ++$z;
        }
    }
    return $z;
}

function splitSQLStatements2Array($sql)
{
    $z = 0;
    $sqlArr = [];
    $tmp = '';
    $sql = str_replace("\n", '', $sql);
    $l = strlen($sql);
    $inQuotes = false;
    for ($i = 0; $i < $l; ++$i) {
        $tmp .= $sql[$i];
        if ("'" == $sql[$i] || '"' == $sql[$i]) {
            $inQuotes = !$inQuotes;
        }
        if (';' == $sql[$i] && false == $inQuotes) {
            ++$z;
            $sqlArr[] = $tmp;
            $tmp = '';
        }
    }
    if ('' != trim($tmp)) {
        $sqlArr[] = $tmp;
    }
    return $sqlArr;
}

function DB_Copy($source, $destination, $drop_source = 0, $insert_data = 1)
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $SQL_Array = $t = '';
    if (!DB_Exists($destination)) {
        $res = MOD_DoSQL("CREATE DATABASE `$destination`;");
        if (!$res) {
            return false;
        }
    }
    $SQL_Array .= "USE `$destination` ;\n";
    $sql = "SHOW TABLES FROM $source";
    $tabellen = mod_query($sql);
    while ($row = mysqli_fetch_row($tabellen)) {
        $table = strtolower($row[0]);
        $sqlt = "SHOW CREATE TABLE `$source`.`$table`";
        $res = mod_query($sqlt);
        if ($res) {
            $row = mysqli_fetch_row($res);
            $c = $row[1];
            if (';' == substr($c, -1)) {
                $c = substr($c, 0, strlen($c) - 1);
            }
            $SQL_Array .= (1 == $insert_data) ? "$c SELECT * FROM `$source`.`$table` ;\n" : "$c ;\n";
        } else {
            return false;
        }
    }
    mysqli_select_db($config['dbconnection'], $destination);
    $res = MOD_DoSQL($SQL_Array);
    if (1 == $drop_source && $res) {
        mod_query("DROP DATABASE `$source`;");
    }
    return $res;
}

function Table_Copy($source, $destination, $insert_data, $destinationdb = '')
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $SQL_Array = $t = '';
    $sqlc = "SHOW CREATE TABLE $source";
    $res = mod_query($sqlc);
    $row = mysqli_fetch_row($res);
    $c = $row[1];
    $a1 = strpos($c, '`');
    $a2 = strpos($c, '`', $a1 + 1);
    $c = substr($c, 0, $a1 + 1).$destination.substr($c, $a2);
    if (';' == substr($c, -1)) {
        $c = substr($c, 0, strlen($c) - 1);
    }
    $SQL_Array .= (1 == $insert_data) ? "$c SELECT * FROM $source ;\n" : "$c ;\n";
    //echo "<h5>$SQL_Array</h5>";
    MOD_DoSQL($SQL_Array);
}

function MOD_DoSQL($sqlcommands, $limit = '')
{
    global $config, $out, $numrowsabs, $numrows, $num_befehle, $time_used, $sql;

    if (!isset($sql['parser']['sql_commands'])) {
        $sql['parser']['sql_commands'] = 0;
    }
    if (!isset($sql['parser']['sql_errors'])) {
        $sql['parser']['sql_errors'] = 0;
    }

    $sql['parser']['time_used'] = getmicrotime();
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $out = $sqlcommand = '';
    $allSQL = splitSQLStatements2Array($sqlcommands); //explode(';',preg_replace('/\r\n|\n/', '', $sqlcommands));
    $sql_queries = count($allSQL);

    if (!isset($allSQL[$sql_queries - 1])) {
        --$sql_queries;
    }
    if (1 == $sql_queries) {
        SQLParser($allSQL[0]);
        ++$sql['parser']['sql_commands'];
        $out .= Stringformat(($sql['parser']['sql_commands']), 4).': '.$allSQL[0]."\n";
        $result = mod_query($allSQL[0]);
    } else {
        $result = true;
        for ($i = 0; $i < $sql_queries; ++$i) {
            $allSQL[$i] = trim(rtrim($allSQL[$i]));

            if ('' != $allSQL[$i]) {
                $sqlcommand .= $allSQL[$i];
                $sqlcommand = SQLParser($sqlcommand);
                if (0 == $sql['parser']['start'] && 0 == $sql['parser']['end'] && '' != $sqlcommand) {
                    //sql complete
                    ++$sql['parser']['sql_commands'];
                    $out .= Stringformat(($sql['parser']['sql_commands']), 4).': '.$sqlcommand."\n";
                    $result = $result && mod_query($sqlcommand);
                    $sqlcommand = '';
                }
            }
        }
    }
    $sql['parser']['time_used'] = getmicrotime() - $sql['parser']['time_used'];
    return $result;
}

function SQLParser($command, $debug = 0)
{
    global $sql;
    $sql['parser']['start'] = $sql['parser']['end'] = 0;
    $sql['parser']['sqlparts'] = 0;
    if (!isset($sql['parser']['drop'])) {
        $sql['parser']['drop'] = 0;
    }
    if (!isset($sql['parser']['create'])) {
        $sql['parser']['create'] = 0;
    }
    if (!isset($sql['parser']['insert'])) {
        $sql['parser']['insert'] = 0;
    }
    if (!isset($sql['parser']['update'])) {
        $sql['parser']['update'] = 0;
    }
    if (!isset($sql['parser']['comment'])) {
        $sql['parser']['comment'] = 0;
    }
    $Backslash = chr(92);
    $s = rtrim(trim(($command)));

    //Was ist das für eine Anfrage ?
    if ('#' == substr($s, 0, 1) || '--' == substr($s, 0, 2)) {
        ++$sql['parser']['comment'];
        $s = '';
    } elseif ('DROP ' == strtoupper(substr($s, 0, 5))) {
        ++$sql['parser']['drop'];
    } elseif ('CREATE ' == strtoupper(substr($s, 0, 7))) {
        //Hier nur die Anzahl der Klammern zählen
        $sql['parser']['start'] = 1;
        $kl1 = substr_count($s, '(');
        $kl2 = substr_count($s, ')');
        if (0 == $kl2 - $kl1) {
            $sql['parser']['start'] = 0;
            ++$sql['parser']['create'];
        }
    } elseif ('INSERT ' == strtoupper(substr($s, 0, 7)) || 'UPDATE ' == strtoupper(substr($s, 0, 7))) {
        if ('INSERT ' == strtoupper(substr($s, 0, 7))) {
            ++$sql['parser']['insert'];
        } else {
            ++$sql['parser']['update'];
        }
        $i = strpos(strtoupper($s), ' VALUES') + 7;
        $st = substr($s, $i);
        $i = strpos($st, '(') + 1;
        $st = substr($st, $i);
        $st = substr($st, 0, strlen($st) - 2);

        $tb = explode(',', $st);
        for ($i = 0; $i < count($tb); ++$i) {
            $first = $B_Esc = $B_Ticks = $B_Dashes = 0;
            $v = trim($tb[$i]);
            //Ticks + Dashes zählen
            for ($cpos = 2; $cpos <= strlen($v); ++$cpos) {
                if ("'" == substr($v, (-1 * $cpos), 1)) {
                    ++$B_Ticks;
                } else {
                    break;
                }
            }
            for ($cpos = 2; $cpos <= strlen($v); ++$cpos) {
                if ('"' == substr($v, (-1 * $cpos), 1)) {
                    ++$B_Dashes;
                } else {
                    break;
                }
            }

            //Backslashes zählen
            for ($cpos = 2 + $B_Ticks; $cpos <= strlen($v); ++$cpos) {
                if ('\\' == substr($v, (-1 * $cpos), 1)) {
                    ++$B_Esc;
                } else {
                    break;
                }
            }

            if ('NULL' == $v && 0 == $sql['parser']['start']) {
                $sql['parser']['start'] = 1;
                $sql['parser']['end'] = 1;
            }
            if (0 == $sql['parser']['start'] && is_numeric($v)) {
                $sql['parser']['start'] = 1;
                $sql['parser']['end'] = 1;
            }
            if (0 == $sql['parser']['start'] && '0X' == substr($v, 0, 2) && false == strpos($v, ' ')) {
                $sql['parser']['start'] = 1;
                $sql['parser']['end'] = 1;
            }
            if (0 == $sql['parser']['start'] && is_object($v)) {
                $sql['parser']['start'] = 1;
                $sql['parser']['end'] = 1;
            }

            if ("'" == substr($v, 0, 1) && 0 == $sql['parser']['start']) {
                $sql['parser']['start'] = 1;
                if (1 == strlen($v)) {
                    $first = 1;
                }
                $DELIMITER = "'";
            }
            if ('"' == substr($v, 0, 1) && 0 == $sql['parser']['start']) {
                $sql['parser']['start'] = 1;
                if (1 == strlen($v)) {
                    $first = 1;
                }
                $DELIMITER = '"';
            }
            if (1 == $sql['parser']['start'] && 1 != $sql['parser']['end'] && 0 == $first) {
                if (substr($v, -1) == $DELIMITER) {
                    $B_Delimiter = ("'" == $DELIMITER) ? $B_Ticks : $B_Dashes;
                    //ist Delimiter maskiert?
                    if (($B_Esc % 2) == 1 && ($B_Delimiter % 2) == 1 && strlen($v) > 2) {
                        $sql['parser']['end'] = 1;
                    } elseif (($B_Delimiter % 2) == 1 && strlen($v) > 2) {
                        //ist mit `'` maskiert
                        $sql['parser']['end'] = 0;
                    } elseif (($B_Esc % 2) == 1) {
                        //ist mit Backslash maskiert
                        $sql['parser']['end'] = 0;
                    } else {
                        $sql['parser']['end'] = 1;
                    }
                }
            }
            if (1 == $debug) {
                echo "<font color='#0000FF'>".$sql['parser']['start'].'/'.$sql['parser']['end']."</font> Feld $i: ".htmlspecialchars($tb[$i]).'<font color=#008000>- '.$sql['parser']['sqlparts']."  ($B_Ticks / $B_Esc)</font><br>";
            }
            if (1 == $sql['parser']['start'] && 1 == $sql['parser']['end']) {
                ++$sql['parser']['sqlparts'];
                $sql['parser']['start'] = $sql['parser']['end'] = 0;
            }
        }
    }
    return $s;
}

function SQLOutput($sqlcommand, $meldung = '')
{
    global $sql, $lang;
    $s = '<h6 align="center">'.$lang['L_SQL_OUTPUT'].'</h6><div id="sqloutbox"><strong>';
    if ('' != $meldung) {
        $s .= trim($meldung);
    }

    if (isset($sql['parser']['sql_commands'])) {
        $s .= ' '.$sql['parser']['sql_commands'].'</strong>'.$lang['L_SQL_COMMANDS_IN'].round($sql['parser']['time_used'], 4).$lang['L_SQL_COMMANDS_IN2'].'<br><br>';
        $s .= $lang['L_SQL_OUT1'].'<strong>'.$sql['parser']['drop'].'</strong> <span style="color:#990099;font-weight:bold;">DROP</span>-, ';
        $s .= '<strong>'.$sql['parser']['create'].'</strong> <span style="color:#990099;font-weight:bold;">CREATE</span>-, ';
        $s .= '<strong>'.$sql['parser']['insert'].'</strong> <span style="color:#990099;font-weight:bold;">INSERT</span>-, ';
        $s .= '<strong>'.$sql['parser']['update'].'</strong> <span style="color:#990099;font-weight:bold;">UPDATE</span>-'.$lang['L_SQL_OUT2'].'<br>';
        $s .= $lang['L_SQL_OUT3'].'<strong>'.$sql['parser']['comment'].'</strong> '.$lang['L_SQL_OUT4'].'<br>';
        if ($sql['parser']['sql_commands'] < 50) {
            $s .= '<pre>'.Highlight_SQL($sqlcommand).'</pre>';
        } else {
            $s .= $lang['L_SQL_OUT5'];
        }
    } elseif ('' != $sqlcommand) {
        $s .= '<h5 align="center">'.$lang['L_SQL_OUTPUT'].'</h5><pre>'.Highlight_SQL($sqlcommand).'</pre>';
    }
    return $s.'</div>';
}

function GetCreateTable($db, $tabelle)
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    $res = mysqli_query($config['dbconnection'], "SHOW CREATE TABLE `$db`.`$tabelle`");
    if ($res) {
        $row = mysqli_fetch_array($res);
        if (isset($row['Create Table'])) {
            return $row['Create Table'];
        } elseif (isset($row['Create View'])) {
            return $row['Create View'];
        } else {
            return false;
        }
    } else {
        return mysqli_error($config['dbconnection']);
    }
}

function KindSQL($sql)
{
    if (preg_match('@^((-- |#)[^\n]*\n|/\*.*?\*/)*(DROP|CREATE)[[:space:]]+(IF EXISTS[[:space:]]+)?(TABLE|DATABASE)[[:space:]]+(.+)@im', $sql)) {
        return 2;
    } elseif (preg_match('@^((-- |#)[^\n]*\n|/\*.*?\*/)*(DROP|CREATE)[[:space:]]+(IF EXISTS[[:space:]]+)?(TABLE|DATABASE)[[:space:]]+(.+)@im', $sql)) {
        return 1;
    }
}

function GetPostParams()
{
    global $db, $dbid, $tablename, $context, $limitstart, $order, $orderdir, $sql;
    $db = $_POST['db'];
    $dbid = $_POST['dbid'];
    $tablename = $_POST['tablename'];
    $context = $_POST['context'];
    $limitstart = $_POST['limitstart'];
    $order = $_POST['order'];
    $orderdir = $_POST['orderdir'];
    $sql['sql_statement'] = (isset($_POST['sql_statement'])) ? $_POST['sql_statement'] : "SELECT * FROM `$tablename`";
}

// when fieldnames contain spaces or dots they are replaced with underscores
// we need to built the same index to get the postet values for inserts and updates
function correct_post_index($index)
{
    $index = str_replace(' ', '_', $index);
    $index = str_replace('.', '_', $index);
    return $index;
}
function ComboCommandDump($when, $index, $disabled = '')
{
    global $SQL_ARRAY, $nl, $databases, $lang;
    if ((is_array($SQL_ARRAY) && 0 == count($SQL_ARRAY)) || !is_array($SQL_ARRAY)) {
        $r = '<a href="sql.php?context=1" class="uls">'.$lang['L_SQL_BEFEHLE'].'</a>';
        if (0 == $when) {
            $r .= '<input type="hidden" name="command_before_'.$index.'" value="">';
        } else {
            $r .= '<input type="hidden" name="command_after_'.$index.'" value="">';
        }
    } else {
        if (0 == $when) {
            $r = '<select class="SQLCombo" name="command_before_'.$index.'"'
             .$disabled.'>';
            $csql = $databases['command_before_dump'][$index];
        } else {
            $r = '<select class="SQLCombo" name="command_after_'.$index.'"'
                      .$disabled.'>';
            $csql = $databases['command_after_dump'][$index];
        }

        $r .= '<option value="" '.(('' == $csql) ? ' selected="selected"' : '').'>&nbsp;</option>'."\n";
        if (is_array($SQL_ARRAY) && count($SQL_ARRAY) > 0) {
            for ($i = 0; $i < count($SQL_ARRAY); ++$i) {
                $s = trim(SQL_String($i));
                $r .= '<option value="'.htmlspecialchars($s).'" '.(($csql == $s) ? ' selected="selected"' : '').'>'.SQL_Name($i).'</option>'."\n";
            }
        }
        $r .= '</select>';
    }
    return $r;
}

function EngineCombo($default = '')
{
    global $config;
    if (!$config['dbconnection']) {
        mod_mysqli_connect();
    }

    $r = '<option value="" '.(('' == $default) ? ' selected="selected"' : '').'></option>';
    if (!MOD_NEW_VERSION) {
        //BDB | HEAP | ISAM | InnoDB | MERGE | MRG_MYISAM | MYISAM
        $r .= '<option value="BDB" '.(('BDB' == $default) ? ' selected="selected"' : '').'>BDB</option>';
        $r .= '<option value="HEAP" '.(('HEAP' == $default) ? ' selected="selected"' : '').'>HEAP</option>';
        $r .= '<option value="ISAM" '.(('ISAM' == $default) ? ' selected="selected"' : '').'>ISAM</option>';
        $r .= '<option value="InnoDB" '.(('InnoDB' == $default) ? ' selected="selected"' : '').'>InnoDB</option>';
        $r .= '<option value="MERGE" '.(('MERGE' == $default) ? ' selected="selected"' : '').'>MERGE</option>';
        $r .= '<option value="MRG_MYISAM" '.(('MRG_MYISAM' == $default) ? ' selected="selected"' : '').'>MRG_MYISAM</option>';
        $r .= '<option value="MYISAM" '.(('MyISAM' == $default) ? ' selected="selected"' : '').'>MyISAM</option>';
    } else {
        $res = mysqli_query($config['dbconnection'], 'SHOW ENGINES');
        $num = mysqli_num_rows($res);
        for ($i = 0; $i < $num; ++$i) {
            $row = mysqli_fetch_array($res);
            $r .= '<option value="'.$row['Engine'].'" '.(($row['Engine'] == $default) ? ' selected="selected"' : '').'>'.$row['Engine'].'</option>';
        }
    }
    return $r;
}

function CharsetCombo($default = '')
{
    global $config;
    if (!MOD_NEW_VERSION) {
        return '';
    } else {
        if (!isset($config['dbconnection'])) {
            mod_mysqli_connect();
        }
        $res = mysqli_query($config['dbconnection'], 'SHOW Charset');
        $num = mysqli_num_rows($res);
        $r = '<option value="" '.(('' == $default) ? ' selected="selected"' : '').'></option>';
        $charsets = [];
        for ($i = 0; $i < $num; ++$i) {
            $charsets[] = mysqli_fetch_array($res);
        }

        if (is_array($charsets)) {
            $charsets = mu_sort($charsets, 'Charset');
            foreach ($charsets as $row) {
                $r .= '<option value="'.$row['Charset'].'" '.(($row['Charset'] == $default) ? ' selected="selected"' : '').'>'.$row['Charset'].'</option>';
            }
        }
        return $r;
    }
}

function GetCollationArray()
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }

    $res = mysqli_query($config['dbconnection'], 'SHOW Collation');
    $num = mysqli_num_rows($res);
    $r = [];
    if (is_array($r)) {
        for ($i = 0; $i < $num; ++$i) {
            $row = mysqli_fetch_array($res);
            $r[$i]['Collation'] = isset($row['Collation']) ? $row['Collation'] : '';
            $r[$i]['Charset'] = isset($row['Charset']) ? $row['Charset'] : '';
            $r[$i]['Id'] = isset($row['Id']) ? $row['Id'] : '';
            $r[$i]['Default'] = isset($row['Default']) ? $row['Default'] : '';
            $r[$i]['Compiled'] = isset($row['Compiled']) ? $row['Compiled'] : '';
            $r[$i]['Sortlen'] = isset($row['Sortlen']) ? $row['Sortlen'] : '';
        }
    }
    return $r;
}

function CollationCombo($default = '', $withcharset = 0)
{
    if (!MOD_NEW_VERSION) {
        return '';
    } else {
        $r = GetCollationArray();
        sort($r);
        $s = '';
        $s = '<option value=""'.(('' == $default) ? ' selected="selected"' : '').'></option>';
        $group = '';
        for ($i = 0; $i < count($r); ++$i) {
            $gc = $r[$i]['Charset'];
            if ($gc != $group) {
                $group = $gc;
                if ($i > 0) {
                    $s .= '</optgroup>';
                }
                $s .= '<optgroup label="'.$group.'">';
            }
            $s .= '<option value="'.((1 == $withcharset) ? $group.'|' : '').$r[$i]['Collation'].'" '.(($r[$i]['Collation'] == $default) ? 'selected' : '').'>'.$r[$i]['Collation'].'</option>';
        }
        return $s.'</optgroup>';
    }
}

function AttributeCombo($default = '')
{
    $s = '<option value=""'.(('' == $default) ? ' selected="selected"' : '').'></option>';
    $s .= '<option value="unsigned" '.(('unsigned' == $default) ? ' selected="selected"' : '').'>unsigned</option>';
    $s .= '<option value="unsigned zerofill"'.(('unsigned zerofill' == $default) ? ' selected="selected"' : '').'>unsigned zerofill</option>';
    return $s;
}

function simple_bbcode_conversion($a)
{
    global $config;
    $tag_start = '';
    $tag_end = '';

    //replacements
    $a = nl2br($a);
    $a = str_replace('<br>', ' <br>', $a);
    $a = str_replace('<br />', ' <br>', $a);

    $a = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/si", '<a class="small"  href="$1" target="blank">$2</a>', $a);
    $a = preg_replace("/\[urltargetself=(.*?)\](.*?)\[\/urltargetself\]/si", '<a class="small"  href="$1" target="blank">$2</a>', $a);
    $a = preg_replace("/\[url\](.*?)\[\/url\]/si", '<a class="small"  href="$1" target="blank">$1</a>', $a);
    $a = preg_replace("/\[ed2k=\+(.*?)\](.*?)\[\/ed2k\]/si", '<a class="small"  href="$1" target="blank">$2</a>', $a);
    $a = preg_replace("/\[ed2k=(.*?)\](.*?)\[\/ed2k\]/si", '<a class="small"  href="$1" target="blank">$2</a>', $a);

    $a = preg_replace("/\[center\](.*?)\[\/center\]/si", '<div align="center">$1</div>', $a);
    $a = preg_replace("/\[size=([1-2]?[0-9])\](.*?)\[\/size\]/si", '<span style="font-size= $1px;">$2</span>', $a);
    $a = preg_replace("/\[size=([1-2]?[0-9]):(.*?)\](.*?)\[\/size(.*?)\]/si", '<span style="font-size= $1px;">$3</span>', $a);
    $a = preg_replace("/\[font=(.*?)\](.*?)\[\/font\]/si", '<span style="font-family:$1;">$2</span>', $a);
    $a = preg_replace("/\[color=(.*?)\](.*?)\[\/color\]/si", '<span style="color= $1;">$2</span>', $a);
    $a = preg_replace("/\[color=(.*?):(.*?)\](.*?)\[\/color(.*?)\]/si", '<span style="color= $1;">$3</span>', $a);
    $a = preg_replace("/\[img\](.*?)\[\/img\]/si", '<img src="$1" vspace=4 hspace=4>', $a);
    //$a=preg_replace("/\[b\](.*?)\[\/b\]/si", "<strong>$1</strong>", $a);
    $a = preg_replace("/\[b(.*?)\](.*?)\[\/b(.*?)\]/si", '<strong>$2</strong>', $a);
    //$a=preg_replace("/\[u\](.*?)\[\/u\]/si", "<u>$1</u>", $a);
    $a = preg_replace("/\[u(.*?)\](.*?)\[\/u(.*?)\]/si", '<u>$2</u>', $a);
    //$a=preg_replace("/\[i\](.*?)\[\/i\]/si", "<em>$1</em>", $a);
    $a = preg_replace("/\[i(.*?)\](.*?)\[\/i(.*?)\]/si", '<em>$2</em>', $a);
    //$a=preg_replace("/\[quote\](.*?)\[\/quote\]/si", "<p align=\"left\" style=\"border: 2px solid silver;padding:4px;\">$1</p>", $a);
    $a = preg_replace("/\[quote(.*?)\](.*?)\[\/quote(.*?)\]/si", '<p align="left" style="border: 2px solid silver;padding:4px;">$2</p>', $a);
    $a = preg_replace("/\[code(.*?)\](.*?)\[\/code(.*?)\]/si", '<p align="left" style="border: 2px solid red;color:green;padding:4px;">$2</p>', $a);
    $a = preg_replace("/\[hide\](.*?)\[\/hide\]/si", '<div style="background-color:#ccffcc;">$1</div>', $a);
    $a = preg_replace("/(^|\s)+((http:\/\/)|(www.))(.+)(\s|$)+/Uis", ' <a href="http://$4$5" target="_blank">http://$4$5</a> ', $a);
    return $tag_start.$a.$tag_end;
}

function ExtractTablenameFromSQL($q)
{
    global $databases, $db, $dbid;
    $tablename = '';
    if (strlen($q) > 100) {
        $q = substr($q, 0, 100);
    }
    $p = trim($q);
    // if we get a list of tables - no current table is selected -> return ''
    if ('SHOW TABLE STATUS' == strtoupper(substr($p, 0, 17))) {
        return '';
    }
    // check for SELECT-Statement to extract tablename after FROM
    if ('SELECT ' == strtoupper(substr($p, 0, 7))) {
        $parts = [];
        $p = substr($p, strpos(strtoupper($p), 'FROM') + 5);
        $parts = explode(' ', $p);
        $p = $parts[0];
    }
    // remove keyword DATABASES and the database name after that
    $p = preg_replace('/DATABASE [`]*\w+[`]*/i', '', $p);
    // remove other keywords
    $suchen = [
               'SHOW DATABASES',
                'SHOW ',
                'SELECT',
                'DROP',
                'INSERT',
                'UPDATE',
                'DELETE',
                'CREATE',
                'TABLE',
                'STATUS',
                'FROM',
                '*',
    ];
    $ersetzen = [
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
    ];
    $cleaned = trim(str_ireplace($suchen, $ersetzen, $p));
    $tablename = $cleaned;
    if (strpos($cleaned, ' ')) {
        $tablename = substr($cleaned, 0, strpos($cleaned, ' '));
    }
    $tablename = str_replace('`', '', $tablename); // remove backticks
    // take care of db-name.tablename
    if (strpos($tablename, '.')) {
        $p = explode('.', $tablename);
        $databases['db_actual'] = $p[0];
        // if database is changed in Query we need to get the index of the actual db
        $db_temp = array_flip($databases['Name']);
        if (isset($db_temp[$databases['db_actual']])) {
            $databases['db_selected_index'] = $db_temp[$databases['db_actual']];
            $dbid = $databases['db_selected_index'];
        }
        if (isset($_GET['tablename'])) {
            unset($_GET['tablename']);
        }
        //echo "<br>" . $db;
        $tablename = $p[1];
    }
    //	if (Table_Exists($databases['db_actual'], $tablename)) return $tablename;
    //	else return '';
    return $tablename;
}

function GetOptionsCombo($arr, $default)
{
    global $feldtypen, $feldattribute, $feldnull, $feldextras, $feldkeys, $feldrowformat;
    $r = '';
    foreach ($arr as $s) {
        $r .= '<option value="'.$s.'" '.((strtoupper($default) == strtoupper($s)) ? ' selected="selected"' : '').'>'.$s.'</option>'."\n";
    }
    return $r;
}

function make_options($arr, $selected)
{
    $r = '';
    foreach ($arr as $key => $val) {
        $r .= '<option value="'.$key.'"';
        if ($key == $selected) {
            $r .= ' selected';
        }
        $r .= '>'.$val.'</option>'."\n";
    }
    return $r;
}

/**
 * Reads MySQL field information (depricated: will be substituted by function getExtendedFieldInfos).
 *
 * Reads field information for each field of a MySQL table
 * and fills an array with the keys detected
 *
 * @param $db
 * @param $tabelle
 *
 * @return array
 */
function getFieldinfos($db, $tabelle)
{
    global $config;
    $fields_infos = [];
    $t = GetCreateTable($db, $tabelle);
    $sqlf = "SHOW FULL FIELDS FROM `$db`.`$tabelle`;";
    $res = mod_query($sqlf);
    $anz_fields = mysqli_num_rows($res);

    $fields_infos['_primarykeys_'] = [];
    $fields_infos['_key_'] = [];
    $fields_infos['_uniquekey_'] = [];
    $fields_infos['_fulltextkey_'] = [];

    $fields_infos['_tableinfo_'] = [
                                        'ENGINE' => 'MyISAM',
                                        'AUTO_INCREMENT' => '',
                                        'DEFAULT CHARSET' => '',
    ];

    for ($i = 0; $i < $anz_fields; ++$i) {
        // define defaults
        $fields_infos[$i]['name'] = '';
        $fields_infos[$i]['size'] = '';
        $fields_infos[$i]['default'] = '';
        $fields_infos[$i]['extra'] = '';
        $fields_infos[$i]['attributes'] = '';
        $fields_infos[$i]['null'] = '';
        $fields_infos[$i]['collate'] = '';
        $fields_infos[$i]['comment'] = '';
        $fields_infos[$i]['type'] = '';
        $fields_infos[$i]['privileges'] = '';

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        //v($row);
        if (isset($row['Collation'])) {
            $fields_infos[$i]['collate'] = $row['Collation'];
        }
        if (isset($row['COLLATE'])) {
            $fields_infos[$i]['collate'] = $row['COLLATE'];
        } // MySQL <4.1
        if (isset($row['Comment'])) {
            $fields_infos[$i]['comment'] = $row['Comment'];
        }
        if (isset($row['Type'])) {
            $fields_infos[$i]['type'] = $row['Type'];
        }
        if (isset($row['Field'])) {
            $fields_infos[$i]['name'] = $row['Field'];
        }
        $fields_infos[$i]['size'] = get_attribut_size_from_type($fields_infos[$i]['type']);
        // remove size from type for readability in output
        $fields_infos[$i]['type'] = str_replace('('.$fields_infos[$i]['size'].')', '', $fields_infos[$i]['type']);
        // look for attributes, everthing behind the first space is an atribut
        $attributes = explode(' ', $fields_infos[$i]['type'], 2);
        if (isset($attributes[1])) {
            // we found attributes
            unset($attributes[0]); // delete type
            $fields_infos[$i]['attributes'] = trim(implode(' ', $attributes)); //merge all other attributes
            // remove attributes from type
            $fields_infos[$i]['type'] = trim(str_replace($fields_infos[$i]['attributes'], '', $fields_infos[$i]['type']));
        }
        if (isset($row['NULL'])) {
            $fields_infos[$i]['null'] = $row['NULL'];
        }
        if (isset($row['Null'])) {
            $fields_infos[$i]['null'] = $row['Null'];
        }
        if (isset($row['Default'])) {
            $fields_infos[$i]['default'] = $row['Default'];
        }
        if (isset($row['Extra'])) {
            $fields_infos[$i]['extra'] = $row['Extra'];
        }
        if (isset($row['Privileges'])) {
            $fields_infos[$i]['privileges'] = $row['Privileges'];
        }
        if (isset($row['privileges'])) {
            $fields_infos[$i]['privileges'] = $row['privileges'];
        }
    }

    // now get key definitions of the table and add info to fields
    $sql = 'SHOW KEYS FROM `'.$db.'`.`'.$tabelle.'`';
    $res = mod_query($sql);
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
        //v($row);
        $key_name = isset($row['Key_name']) ? $row['Key_name'] : '';
        $index_type = isset($row['Index_type']) ? $row['Index_type'] : '';
        $column_name = isset($row['Column_name']) ? $row['Column_name'] : '';
        $non_unique = isset($row['Non_unique']) ? $row['Non_unique'] : '';
        if ($column_name > '') {
            // first find indexnr of field
            for ($index = 0, $count = sizeof($fields_infos); $index < $count; ++$index) {
                if ($fields_infos[$index]['name'] == $column_name) {
                    break;
                }
            }
            if ('PRIMARY' == $key_name) {
                $fields_infos['_primarykeys_'][] = $column_name;
            } elseif ('FULLTEXT' == $index_type) {
                $fields_infos['_fulltextkey_'][] = $column_name;
            } elseif (0 == $non_unique) {
                $fields_infos['_uniquekey_'][] = $column_name;
            } else {
                $fields_infos['_key_'][] = $column_name;
            }
        }
    }
    //v($fields_infos);
    return $fields_infos;
}

/**
 * Reads extened MySQL field information.
 *
 * Reads extened field information for each field of a MySQL table
 * and fills an array like
 * array(
 *  [Fieldname][attribut] =value,
 *  ['primary_key'] =keys
 * )
 *
 * @param $db
 * @param $table
 *
 * @return array Field infos
 */
function getExtendedFieldInfo($db, $table)
{
    global $config;
    $fields_infos = [];
    $t = GetCreateTable($db, $table);
    $sqlf = "SHOW FULL FIELDS FROM `$db`.`$table`;";
    $res = mod_query($sqlf);
    $num_fields = mysqli_num_rows($res);

    $f = []; //will hold all info
    for ($x = 0; $x < $num_fields; ++$x) {
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        //v($row);
        $i = $row['Field']; // define name of field as index of array
        //define field defaults - this way the index of the array is defined anyway
        $f[$i]['field'] = '';
        $f[$i]['collation'] = '';
        $f[$i]['comment'] = '';
        $f[$i]['type'] = '';
        $f[$i]['size'] = '';
        $f[$i]['attributes'] = '';
        $f[$i]['null'] = '';
        $f[$i]['default'] = '';
        $f[$i]['extra'] = '';
        $f[$i]['privileges'] = '';
        $f[$i]['primary_keys'] = [];

        if (isset($row['Collation'])) {
            $f[$i]['collate'] = $row['Collation'];
        }
        if (isset($row['COLLATE'])) {
            $f[$i]['collate'] = $row['COLLATE'];
        } // MySQL <4.1
        if (isset($row['Comment'])) {
            $f[$i]['comment'] = $row['Comment'];
        }
        if (isset($row['Type'])) {
            $f[$i]['type'] = $row['Type'];
        }
        if (isset($row['Field'])) {
            $f[$i]['field'] = $row['Field'];
        }
        $f[$i]['size'] = get_attribut_size_from_type($f[$i]['type']);
        // remove size from type for readability in output
        $f[$i]['type'] = str_replace('('.$f[$i]['size'].')', '', $f[$i]['type']);
        // look for attributes, everthing behind the first space is an atribut
        $attributes = explode(' ', $f[$i]['type'], 2);
        if (isset($attributes[1])) {
            // we found attributes
            unset($attributes[0]); // delete type
            $f[$i]['attributes'] = trim(implode(' ', $attributes)); //merge all other attributes
            // remove attributes from type
            $f[$i]['type'] = trim(str_replace($f[$i]['attributes'], '', $f[$i]['type']));
        }
        if (isset($row['NULL'])) {
            $f[$i]['null'] = $row['NULL'];
        }
        if (isset($row['Null'])) {
            $f[$i]['null'] = $row['Null'];
        }
        if (isset($row['Default'])) {
            $f[$i]['default'] = $row['Default'];
        }
        if (isset($row['Extra'])) {
            $f[$i]['extra'] = $row['Extra'];
        }
        if (isset($row['Privileges'])) {
            $f[$i]['privileges'] = $row['Privileges'];
        }
        if (isset($row['privileges'])) {
            $f[$i]['privileges'] = $row['privileges'];
        }
    }

    // now get key definitions of the table and add info to field-array
    $sql = 'SHOW KEYS FROM `'.$db.'`.`'.$table.'`';
    $res = mod_query($sql);
    while ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
        //echo "<br>Keys of $table: ";v($row);
        $key_name = isset($row['Key_name']) ? $row['Key_name'] : '';
        $index_type = isset($row['Index_type']) ? $row['Index_type'] : '';
        $column_name = isset($row['Column_name']) ? $row['Column_name'] : '';
        // to do: add other info about index etc.
        if ('PRIMARY' == $key_name) {
            $f['primary_keys'][] = $column_name;
        }
    }
    return $f;
}

function ChangeKeys($ok, $nk, $fld, $size, $restriction = '')
{
    if ($ok[0] == $nk[0] && $ok[1] == $nk[1] && $ok[2] == $nk[2] && $ok[3] == $nk[3]) {
        return '';
    } else {
        $s = '';
        if (0 == $ok[0] && 1 == $nk[0]) {
            if ('drop_only' != $restriction) {
                $s .= "ADD PRIMARY KEY (`$fld`), ";
            }
        } elseif (1 == $ok[0] && 0 == $nk[0]) {
            $s .= 'DROP PRIMARY KEY, ';
        }
        if (0 == $ok[1] && 1 == $nk[1]) {
            if ('drop_only' != $restriction) {
                $s .= "ADD UNIQUE INDEX `$fld` (`$fld`), ";
            }
        } elseif (1 == $ok[1] && 0 == $nk[1]) {
            $s .= "DROP INDEX `$fld`, ";
        }
        if (0 == $ok[2] && 1 == $nk[2]) {
            if ('drop_only' != $restriction) {
                $s .= "ADD INDEX `$fld` (`$fld`), ";
            }
        } elseif (1 == $ok[2] && 0 == $nk[2]) {
            $s .= "DROP INDEX `$fld`, ";
        }
        if (0 == $ok[3] && 1 == $nk[3]) {
            if ('drop_only' != $restriction) {
                $s .= "ADD FULLTEXT INDEX `$fld` (`$fld`($size)), ";
            }
        } elseif (1 == $ok[3] && 0 == $nk[3]) {
            $s .= "DROP FULLTEXT INDEX `$fld`, ";
        }
    }
    if ('' != $s) {
        $s = substr($s, 0, strlen($s) - 2);
    }
    return $s;
}

function build_where_from_record($data)
{
    if (!is_array($data)) {
        return false;
    }
    $ret = '';
    foreach ($data as $key => $val) {
        if (is_string($val)) {
            $val = str_replace('<span class="treffer">', '', $val);
            $val = str_replace('</span>', '', $val);
        }
        $nLen = strlen($val);
        if (!empty($val) && ($nLen < 200)) {
            $ret .= '`'.$key.'`="'.addslashes($val).'" AND ';
        }
    }
    $ret = substr($ret, 0, -5);

    return $ret;
}

/*
 * Array mit Primaerschluesseln aufbauen
 * INPUT: Datenbank.Tabelle (oder nur Tabelle)
 * OUTPUT: Array mit allen Tabellenschluesseln
 * Author: DH
 */
function getPrimaryKeys($db, $table)
{
    $keys = [];
    $sqlk = 'SHOW KEYS FROM `'.$db.'`.`'.$table.'`;';
    $res = mod_query($sqlk);
    while ($row = mysqli_fetch_array($res)) {
        //wenn Primaerschluessel
        if ('PRIMARY' == $row['Key_name']) {
            $keys['name'][] = $row['Column_name'];
        }
        if (null != $row['Sub_part']) {
            $keys['size'][] = $row['Sub_part'];
        } else {
            $keys['size'][] = '';
        }
    }

    return $keys;
}

/*
 * Array mit allen Feldern aufbauen
 * INPUT: Datenbank.Tabelle
 * OUTPUT: Array mit allen Feldern der Tabelle
 * Author: DH
 */
function getAllFields($db, $table)
{
    $fields = [];
    $sqlk = 'DESCRIBE `'.$db.'`.`'.$table.'`;';
    $res = mod_query($sqlk);
    while ($row = mysqli_fetch_array($res)) {
        $fields[] = $row['Field'];
    }
    return $fields;
}

/*
 * Alte Primaerschluessel verwerfen, neue Primaerschluessel setzen
 * INPUT: Datenbank.Tabelle, Array mit neuen Primaerschluesseln
 * OUTPUT: true/false
 * Author: DH
 */
function setNewPrimaryKeys($db, $table, $newKeys, $indexSizes)
{
    $sqlSetNewPrimaryKeys = 'ALTER TABLE `'.$db.'`.`'.$table.'`';
    //wenn es Primaerschluessel gibt, diese loeschen
    $existingKeys = getPrimaryKeys($db, $table);
    if (count($existingKeys) > 0) {
        $sqlSetNewPrimaryKeys .= ' DROP PRIMARY KEY';
    }
    //wenn min. 1 Schluessel im Array, sonst nur loeschen
    if (count($newKeys) > 0) {
        if (count($existingKeys) > 0) {
            $sqlSetNewPrimaryKeys .= ', ';
        }
        $sqlSetNewPrimaryKeys .= ' ADD PRIMARY KEY (';
        foreach ($newKeys as $id => $name) {
            if ($id > 0) {
                $sqlSetNewPrimaryKeys .= ', ';
            }
            $sqlSetNewPrimaryKeys .= '`'.$name.'`';
            if ($indexSizes[$id]) {
                $sqlSetNewPrimaryKeys .= ' ('.$indexSizes[$id].')';
            }
        }
        $sqlSetNewPrimaryKeys .= ')';
    }
    $sqlSetNewPrimaryKeys .= ';';
    $res = mod_query($sqlSetNewPrimaryKeys);
    return $res;
}

function setNewKeys($db, $table, $newKeys, $indexType, $indexName, $indexSizes)
{
    $sqlSetNewKeys = 'ALTER TABLE `'.$db.'`.`'.$table.'` ';
    $sqlSetNewKeys .= 'ADD '.$indexType.' ';
    if ($indexName) {
        $sqlSetNewKeys .= '`'.$indexName.'` ';
    }
    $sqlSetNewKeys .= '(';
    foreach ($newKeys as $id => $name) {
        if ($id > 0) {
            $sqlSetNewKeys .= ', ';
        }
        $sqlSetNewKeys .= '`'.$name.'`';
        if ($indexSizes[$id]) {
            $sqlSetNewKeys .= ' ('.$indexSizes[$id].')';
        }
    }
    $sqlSetNewKeys .= ');';
    $res = mod_query($sqlSetNewKeys);
    return $res;
}

function killKey($db, $table, $indexName)
{
    $sqlKillKey = 'ALTER TABLE `'.$db.'`.`'.$table.'` DROP INDEX `'.$indexName.'`';
    $res = mod_query($sqlKillKey);
    return $res;
}

function get_output_attribut_null($null)
{
    global $lang;
    if ('' == $null) {
        return $lang['L_YES'];
    }
    $not_null = [
                    'NO',
                    'NOT NULL',
    ];
    if (in_array($null, $not_null)) {
        return $lang['L_NO'];
    } else {
        return $lang['L_YES'];
    }
}

function get_attribut_size_from_type($type)
{
    $size = '';
    $matches = [];
    $pattern = '/\((\d.*?)\)/msi';
    preg_match($pattern, $type, $matches);
    if (isset($matches[1])) {
        $size = $matches[1];
    }
    return $size;
}

// Bildet die WHERE-Bedingung zur eindeutigen Identifizierung wenn kein Primaerschluessel vorhanden ist
// erwartet ein Array mit $data[feldname] =wert
function build_recordkey($data)
{
    if (!is_array($data)) {
        return urlencode($data);
    } else {
        return urlencode(serialize($data));
    }
}
