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

if (!defined('MOD_VERSION')) {
    exit('No direct access.');
}

//Feldspezifikationen
$feldtypen = [
                'VARCHAR',
                'TINYINT',
                'TEXT',
                'DATE',
                'SMALLINT',
                'MEDIUMINT',
                'INT',
                'BIGINT',
                'FLOAT',
                'DOUBLE',
                'DECIMAL',
                'DATETIME',
                'TIMESTAMP',
                'TIME',
                'YEAR',
                'CHAR',
                'TINYBLOB',
                'TINYTEXT',
                'BLOB',
                'MEDIUMBLOB',
                'MEDIUMTEXT',
                'LONGBLOB',
                'LONGTEXT',
                'ENUM',
                'SET',
];
$feldattribute = [
                    '',
                    'BINARY',
                    'UNSIGNED',
                    'UNSIGNED ZEROFILL',
];
$feldnulls = [
                'NOT NULL',
                'NULL',
];
$feldextras = [
                '',
                'AUTO_INCREMENT',
];
$feldkeys = [
                '',
                'PRIMARY KEY',
                'UNIQUE KEY',
                'FULLTEXT',
];
$feldrowformat = [
                    '',
                    'FIXED',
                    'DYNAMIC',
                    'COMPRESSED',
];

$rechte_daten = [
                    'SELECT',
                    'INSERT',
                    'UPDATE',
                    'DELETE',
                    'FILE',
];
$rechte_struktur = [
                    'CREATE',
                    'ALTER',
                    'INDEX',
                    'DROP',
                    'CREATE TEMPORARY TABLES',
];
$rechte_admin = [
                    'GRANT',
                    'SUPER',
                    'PROCESS',
                    'RELOAD',
                    'SHUTDOWN',
                    'SHOW DATABASES',
                    'LOCK TABLES',
                    'REFERENCES',
                    'EXECUTE',
                    'REPLICATION CLIENT',
                    'REPLICATION SLAVE',
];
$rechte_resourcen = [
                        'MAX QUERIES PER HOUR',
                        'MAX UPDATES PER HOUR',
                        'MAX CONNECTIONS PER HOUR',
];

$sql_keywords = [
                    'ALTER',
                    'AND',
                    'ADD',
                    'AUTO_INCREMENT',
                    'BETWEEN',
                    'BINARY',
                    'BOTH',
                    'BY',
                    'BOOLEAN',
                    'CHANGE',
                    'CHARSET',
                    'CHECK',
                    'COLLATE',
                    'COLUMNS',
                    'COLUMN',
                    'CROSS',
                    'CREATE',
                    'DATABASES',
                    'DATABASE',
                    'DATA',
                    'DELAYED',
                    'DESCRIBE',
                    'DESC',
                    'DISTINCT',
                    'DELETE',
                    'DROP',
                    'DEFAULT',
                    'ENCLOSED',
                    'ENGINE',
                    'ESCAPED',
                    'EXISTS',
                    'EXPLAIN',
                    'FIELDS',
                    'FIELD',
                    'FLUSH',
                    'FOR',
                    'FOREIGN',
                    'FUNCTION',
                    'FROM',
                    'GROUP',
                    'GRANT',
                    'HAVING',
                    'IGNORE',
                    'INDEX',
                    'INFILE',
                    'INSERT',
                    'INNER',
                    'INTO',
                    'IDENTIFIED',
                    'JOIN',
                    'KEYS',
                    'KILL',
                    'KEY',
                    'LEADING',
                    'LIKE',
                    'LIMIT',
                    'LINES',
                    'LOAD',
                    'LOCAL',
                    'LOCK',
                    'LOW_PRIORITY',
                    'LEFT',
                    'LANGUAGE',
                    'MEDIUMINT',
                    'MODIFY',
                    'MyISAM',
                    'NATURAL',
                    'NOT',
                    'NULL',
                    'NEXTVAL',
                    'OPTIMIZE',
                    'OPTION',
                    'OPTIONALLY',
                    'ORDER',
                    'OUTFILE',
                    'OR',
                    'OUTER',
                    'ON',
                    'PROCEEDURE',
                    'PROCEDURAL',
                    'PRIMARY',
                    'READ',
                    'REFERENCES',
                    'REGEXP',
                    'RENAME',
                    'REPLACE',
                    'RETURN',
                    'REVOKE',
                    'RLIKE',
                    'RIGHT',
                    'SHOW',
                    'SONAME',
                    'STATUS',
                    'STRAIGHT_JOIN',
                    'SELECT',
                    'SETVAL',
                    'TABLES',
                    'TEMINATED',
                    'TO',
                    'TRAILING',
                    'TRUNCATE',
                    'TABLE',
                    'TEMPORARY',
                    'TRIGGER',
                    'TRUSTED',
                    'UNIQUE',
                    'UNLOCK',
                    'USE',
                    'USING',
                    'UPDATE',
                    'UNSIGNED',
                    'VALUES',
                    'VARIABLES',
                    'VIEW',
                    'WITH',
                    'WRITE',
                    'WHERE',
                    'ZEROFILL',
                    'XOR',
                    'ALL',
                    'ASC',
                    'AS',
                    'SET',
                    'IN',
                    'IS',
                    'IF',
];
$mysql_doc = [
                'Feldtypen' => 'http://dev.mysql.com/doc/mysql/de/Column_types.html',
];
$mysql_string_types = [
    'char',
    'varchar',
    'tinytext',
    'text',
    'mediumtext',
    'longtext',
    'binary',
    'varbinary',
    'tinyblob',
    'mediumblob',
    'blob',
    'longblob',
    'enum',
    'set',
];
$mysql_SQLhasRecords = [
                        'SELECT',
                        'SHOW',
                        'EXPLAIN',
                        'DESCRIBE',
                        'DESC',
];

function mod_mysqli_connect($encoding = 'utf8mb4', $keycheck_off = false, $actual_table = '')
{
    global $config, $databases;

    if (isset($config['dbconnection']) && is_resource($config['dbconnection'])) {
        return $config['dbconnection'];
    }

    $port = (isset($config['dbport']) && !empty($config['dbport'])) ? ':'.$config['dbport'] : '';
    $socket = (isset($config['dbsocket']) && !empty($config['dbsocket'])) ? ':'.$config['dbsocket'] : '';

    // Forcing error reporting mode to OFF, which is no longer the default
    // starting with PHP 8.1
    @mysqli_report(MYSQLI_REPORT_OFF);

    $config['dbconnection'] = @mysqli_connect($config['dbhost'].$port.$socket, $config['dbuser'], $config['dbpass']);

    if (!$config['dbconnection']) {
        exit(SQLError('Error establishing a database connection!', mysqli_connect_error()));
    }
    if (!defined('MOD_MYSQL_VERSION')) {
        GetMySQLVersion();
    }

    if (!isset($config['mysql_standard_character_set']) || '' == $config['mysql_standard_character_set']) {
        get_sql_encodings();
    }

    if ($config['mysql_standard_character_set'] != $encoding) {
        $set_encoding = mysqli_query($config['dbconnection'], 'SET NAMES \''.$encoding.'\'');
        if (false === $set_encoding) {
            $config['mysql_can_change_encoding'] = false;
        } else {
            $config['mysql_can_change_encoding'] = true;
        }
    }
    if ($keycheck_off) {
        // only called with this param when restoring
        mysqli_query($config['dbconnection'], 'SET FOREIGN_KEY_CHECKS=0');
        // also set SQL-Mode NO_AUTO_VALUE_ON_ZERO for magento users
        mysqli_query($config['dbconnection'], 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
    }

    return $config['dbconnection'];
}

function GetMySQLVersion()
{
    global $config;
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }

    $res = mod_query('SELECT VERSION()');
    $row = mysqli_fetch_array($res);
    $str = $row[0];
    $version = str_replace(':', '--', $str);
    if (!defined('MOD_MYSQL_VERSION')) {
        define('MOD_MYSQL_VERSION', $version);
    }
    $versions = explode('.', $version);
    $new = false;
    if (4 == $versions[0] && $versions[1] >= 1) {
        $new = true;
    }
    if ($versions[0] > 4) {
        $new = true;
    }
    if (!defined('MOD_NEW_VERSION')) {
        define('MOD_NEW_VERSION', $new);
    }

    return $version;
}

function mod_query($query, $error_output = true)
{
    global $config;
    // print_mem();
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    // echo "<br>Query: ".htmlspecialchars($query).'<br>';
    $res = mysqli_query($config['dbconnection'], $query);
    // print_mem();
    if (false === $res && $error_output) {
        SQLError($query, mysqli_error($config['dbconnection']));
    }
    return $res;
}

function print_mem()
{
    /* Currently used memory */
    $mem_usage = memory_get_usage();

    /* Peak memory usage */
    $mem_peak = memory_get_peak_usage();

    echo 'The script is now using: <strong>'.round($mem_usage / 1024).' KB</strong> of memory.<br>';
    echo 'Peak usage: <strong>'.round($mem_peak / 1024).' KB</strong> of memory.<br><br>';
}

function SQLError($sql, $error, $return_output = false)
{
    global $lang;

    $ret = '<div align="center"><table style="border:1px solid #ff0000" cellspacing="0">
<tr bgcolor="#ff0000"><td style="color:white;font-size:16px;"><strong>MySQL-ERROR</strong></td></tr>
<tr><td style="width:80%;overflow: auto;">'.$lang['L_SQL_ERROR2'].'<br><span style="color:red;">'.$error.'</span></td></tr>
<tr><td width="600"><br>'.$lang['L_SQL_ERROR1'].'<br>'.Highlight_SQL($sql).'</td></tr>
</table></div><br />';
    if ($return_output) {
        return $ret;
    } else {
        echo $ret;
    }
}

function Highlight_SQL($sql)
{
    global $sql_keywords;

    $end = '';
    $tickstart = false;
    if (function_exists('token_get_all')) {
        $a = @token_get_all("<?php $sql?>");
    } else {
        return $sql;
    }
    foreach ($a as $token) {
        if (!is_array($token)) {
            if ('`' == $token) {
                $tickstart = !$tickstart;
            }
            $end .= $token;
        } else {
            if ($tickstart) {
                $end .= $token[1];
            } else {
                switch (token_name($token[0])) {
                    case 'T_STRING':
                    case 'T_AS':
                    case 'T_FOR':
                        $end .= (in_array(strtoupper($token[1]), $sql_keywords)) ? '<span style="color:#990099;font-weight:bold;">'.$token[1].'</span>' : $token[1];
                        break;
                    case 'T_IF':
                    case 'T_LOGICAL_AND':
                    case 'T_LOGICAL_OR':
                    case 'T_LOGICAL_XOR':
                        $end .= (in_array(strtoupper($token[1]), $sql_keywords)) ? '<span style="color:#0000ff;font-weight:bold;">'.$token[1].'</span>' : $token[1];
                        break;
                    case 'T_CLOSE_TAG':
                    case 'T_OPEN_TAG':
                        break;
                    default:
                        $end .= $token[1];
                }
            }
        }
    }
    $end = preg_replace('/`(.*?)`/si', '<span style="color:red;">`$1`</span>', $end);
    return $end;
}

function Fieldlist($db, $tbl)
{
    $fl = '';
    $res = mod_query("SHOW FIELDS FROM `$db`.`$tbl`;");
    if ($res) {
        $fl = '(';
        for ($i = 0; $i < mysqli_num_rows($res); ++$i) {
            $row = mysqli_fetch_row($res);
            $fl .= '`'.$row[0].'`,';
        }
        $fl = substr($fl, 0, strlen($fl) - 1).')';
    }
    return $fl;
}

// reads all Tableinfos and place them in $dump-Array
function getDBInfos()
{
    global $databases, $dump, $config, $tbl_sel, $flipped;
    for ($ii = 0; $ii < count($databases['multi']); ++$ii) {
        $dump['dbindex'] = $flipped[$databases['multi'][$ii]];
        $tabellen = mysqli_query($config['dbconnection'], 'SHOW TABLE STATUS FROM `'.$databases['Name'][$dump['dbindex']].'`') or exit('getDBInfos: '.mysqli_error($config['dbconnection']));
        $num_tables = mysqli_num_rows($tabellen);
        // Array mit den gewünschten Tabellen zusammenstellen... wenn Präfix angegeben, werden die anderen einfach nicht übernommen
        if ($num_tables > 0) {
            for ($i = 0; $i < $num_tables; ++$i) {
                $row = mysqli_fetch_array($tabellen);
                if (isset($row['Type'])) {
                    $row['Engine'] = $row['Type'];
                }
                if (isset($row['Comment']) && 'VIEW' == substr(strtoupper($row['Comment']), 0, 4)) {
                    $dump['table_types'][] = 'VIEW';
                } else {
                    $dump['table_types'][] = strtoupper($row['Engine']);
                }
                // check if data needs to be backed up
                if ('VIEW' == strtoupper($row['Comment']) || (isset($row['Engine']) && in_array(strtoupper($row['Engine']), [
                    'MEMORY',
                ]))) {
                    $dump['skip_data'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Name'];
                }
                if ((isset($config['optimize_tables_beforedump']) && (1 == $config['optimize_tables_beforedump'])) && -1 == $dump['table_offset']
                        && 'information_schema' != $databases['Name'][$dump['dbindex']]) {
                    mysqli_select_db($config['dbconnection'], $databases['Name'][$dump['dbindex']]);
                    $opt = 'OPTIMIZE TABLE `'.$row['Name'].'`';
                    $res = mysqli_query($config['dbconnection'], 'OPTIMIZE TABLE `'.$row['Name'].'`');
                    if (false === $res) {
                        exit('Error in '.$opt.' -> '.mysqli_error($config['dbconnection']));
                    }
                }

                if (isset($tbl_sel)) {
                    if (in_array($row['Name'], $dump['tblArray'])) {
                        $dump['tables'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Name'];
                        $dump['records'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Rows'];
                        $dump['totalrecords'] += $row['Rows'];
                    }
                } elseif ('' != $databases['praefix'][$dump['dbindex']] && !isset($tbl_sel)) {
                    if (substr($row['Name'], 0, strlen($databases['praefix'][$dump['dbindex']])) == $databases['praefix'][$dump['dbindex']]) {
                        $dump['tables'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Name'];
                        $dump['records'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Rows'];
                        $dump['totalrecords'] += $row['Rows'];
                    }
                } else {
                    $dump['tables'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Name'];
                    $dump['records'][] = $databases['Name'][$dump['dbindex']].'|'.$row['Rows'];

                    // Get nr of records -> need to do it this way because of incorrect returns when using InnoDBs
                    $sql_2 = 'SELECT count(*) as `count_records` FROM `'.$databases['Name'][$dump['dbindex']].'`.`'.$row['Name'].'`';
                    $res2 = mysqli_query($config['dbconnection'], $sql_2);
                    if (false === $res2) {
                        $read_error = mysqli_error($config['dbconnection']);
                        SQLError($read_error, $sql_2);
                        WriteLog($read_error);
                        if ($config['stop_with_error'] > 0) {
                            exit($read_error);
                        }
                    } else {
                        $row2 = mysqli_fetch_array($res2);
                        $row['Rows'] = $row2['count_records'];
                        $dump['totalrecords'] += $row['Rows'];
                    }
                }
            }
            // Correct total number of records; substract skipped data
            foreach ($dump['skip_data'] as $skip_data) {
                $index = false;
                $records_to_skip = 0;
                //find index of table to get the nr of records
                $count = sizeof($dump['tables']);
                for ($a = 0; $a < $count; ++$a) {
                    if ($dump['tables'][$a] == $skip_data) {
                        $index = $a;
                        $t = explode('|', $dump['records'][$a]);
                        $rekords_to_skip = $t[1];
                        break;
                    }
                }
                if ($index) {
                    $dump['totalrecords'] -= $rekords_to_skip;
                }
            }
        }
    }
}

// gets the numeric index in dump-array and returns it
function getDBIndex($db, $table)
{
    global $dump;
    $index = array_keys($dump['tables'], $db.'|'.$table);
    return $index[0];
}
