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

function CheckCSVOptions()
{
    global $sql;
    if (!isset($sql['export']['trenn'])) {
        $sql['export']['trenn'] = ';';
    }
    if (!isset($sql['export']['enc'])) {
        $sql['export']['enc'] = '"';
    }
    if (!isset($sql['export']['esc'])) {
        $sql['export']['esc'] = '\\';
    }
    if (!isset($sql['export']['ztrenn'])) {
        $sql['export']['ztrenn'] = '\\r\\n';
    }
    if (!isset($sql['export']['null'])) {
        $sql['export']['null'] = 'NULL';
    }
    if (!isset($sql['export']['namefirstline'])) {
        $sql['export']['namefirstline'] = 0;
    }
    if (!isset($sql['export']['format'])) {
        $sql['export']['format'] = 0;
    }
    if (!isset($sql['export']['sendfile'])) {
        $sql['export']['sendfile'] = 0;
    }
    if (!isset($sql['export']['tables'])) {
        $sql['export']['tables'] = [];
    }
    if (!isset($sql['export']['compressed'])) {
        $sql['export']['compressed'] = 0;
    }
    if (!isset($sql['export']['htmlstructure'])) {
        $sql['export']['htmlstructure'] = 0;
    }
    if (!isset($sql['export']['xmlstructure'])) {
        $sql['export']['xmlstructure'] = 0;
    }

    if (!isset($sql['import']['trenn'])) {
        $sql['import']['trenn'] = ';';
    }
    if (!isset($sql['import']['enc'])) {
        $sql['import']['enc'] = '"';
    }
    if (!isset($sql['import']['esc'])) {
        $sql['import']['esc'] = '\\';
    }
    if (!isset($sql['import']['ztrenn'])) {
        $sql['import']['ztrenn'] = '\\r\\n';
    }
    if (!isset($sql['import']['null'])) {
        $sql['import']['null'] = 'NULL';
    }
    if (!isset($sql['import']['namefirstline'])) {
        $sql['import']['namefirstline'] = 0;
    }
    if (!isset($sql['import']['format'])) {
        $sql['import']['format'] = 0;
    }
}

function ExportCSV()
{
    global $sql, $config;
    $t = '';
    $time_start = time();
    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    for ($table = 0; $table < count($sql['export']['tables']); ++$table) {
        $sqlt = 'SHOW Fields FROM `'.$sql['export']['db'].'`.`'.$sql['export']['tables'][$table].'`;';
        $res = mod_query($sqlt);
        if ($res) {
            $numfields = mysqli_num_rows($res);
            if (1 == $sql['export']['namefirstline']) {
                for ($feld = 0; $feld < $numfields; ++$feld) {
                    $row = mysqli_fetch_row($res);
                    if ('' != $sql['export']['enc']) {
                        $t .= $sql['export']['enc'].$row[0].$sql['export']['enc'].(($feld + 1 < $numfields) ? $sql['export']['trenn'] : '');
                    } else {
                        $t .= $row[0].(($feld + 1 < $numfields) ? $sql['export']['trenn'] : '');
                    }
                }
                $t .= $sql['export']['endline'];
                ++$sql['export']['lines'];
            }
        }
        $sqlt = 'SELECT * FROM `'.$sql['export']['db'].'`.`'.$sql['export']['tables'][$table].'`;';
        $res = mod_query($sqlt);
        if ($res) {
            $numrows = mysqli_num_rows($res);
            for ($data = 0; $data < $numrows; ++$data) {
                $row = mysqli_fetch_row($res);
                for ($feld = 0; $feld < $numfields; ++$feld) {
                    if (!isset($row[$feld]) || is_null($row[$feld])) {
                        $t .= $sql['export']['null'];
                    } elseif ('0' == $row[$feld] || '' != $row[$feld]) {
                        if ('' != $sql['export']['enc']) {
                            $t .= $sql['export']['enc'].str_replace($sql['export']['enc'], $sql['export']['esc'].$sql['export']['enc'], $row[$feld]).$sql['export']['enc'];
                        } else {
                            $t .= $row[$feld];
                        }
                    } else {
                        $t .= '';
                    }
                    $t .= ($feld + 1 < $numfields) ? $sql['export']['trenn'] : '';
                }
                $t .= $sql['export']['endline'];
                ++$sql['export']['lines'];
                if ('' == $config['memory_limit']) {
                    $config['memory_limit'] = 0;
                }
                if (strlen($t) > $config['memory_limit']) {
                    CSVOutput($t);
                    $t = '';
                }
                $time_now = time();
                if ($time_start >= $time_now + 30) {
                    $time_start = $time_now;
                    header('X-MODPing: Pong');
                }
            }
        }
    }
    CSVOutput($t, 1);
}

function CSVOutput($str, $last = 0)
{
    global $sql, $config;
    if (0 == $sql['export']['sendfile']) {
        //Display
        echo $str;
    } else {
        if ('' == $sql['export']['header_sent']) {
            if (1 == $sql['export']['compressed'] & !function_exists('gzencode')) {
                $sql['export']['compressed'] = 0;
            }
            if ($sql['export']['format'] < 4) {
                $file = $sql['export']['db'].((1 == $sql['export']['compressed']) ? '.csv.gz' : '.csv');
            } elseif (4 == $sql['export']['format']) {
                $file = $sql['export']['db'].((1 == $sql['export']['compressed']) ? '.xml.gz' : '.xml');
            } elseif (5 == $sql['export']['format']) {
                $file = $sql['export']['db'].((1 == $sql['export']['compressed']) ? '.html.gz' : '.html');
            }
            $mime = (0 == $sql['export']['compressed']) ? 'x-type/subtype' : 'application/x-gzip';

            header('Content-Disposition: attachment; filename="'.$file.'"');
            header('Pragma: no-cache');
            header('Content-Type: '.$mime);
            header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
            $sql['export']['header_sent'] = 1;
        }
        if (1 == $sql['export']['compressed']) {
            echo gzencode($str);
        } else {
            echo $str;
        }
    }
}

function DoImport()
{
    global $sql, $lang;
    $r = '<span class="swarnung">';
    $zeilen = count($sql['import']['csv']) - $sql['import']['namefirstline'];
    $sql['import']['first_zeile'] = explode($sql['import']['trenn'], $sql['import']['csv'][0]);
    $importfelder = count($sql['import']['first_zeile']);

    if (0 == $sql['import']['tablecreate']) {
        $res = mod_query('show fields FROM '.$sql['import']['table']);
        $tabellenfelder = mysqli_num_rows($res);
        if ($importfelder != $tabellenfelder) {
            $r .= '<br>'.sprintf($lang['L_CSV_FIELDCOUNT_NOMATCH'], $tabellenfelder, $importfelder);
        } else {
            $ok = 1;
        }
    } else {
        $ok = ImportCreateTable();
        if (0 == $ok) {
            $r .= '<br>'.sprintf($lang['L_CSV_ERRORCREATETABLE'], $sql['import']['table']);
        }
    }
    if (1 == $ok) {
        $insert = '';
        if (1 == $sql['import']['emptydb'] && 0 == $sql['import']['tablecreate']) {
            MOD_DoSQL('TRUNCATE '.$sql['import']['table'].';');
        }
        $sql['import']['lines_imported'] = 0;
        $enc = ('' == $sql['import']['enc']) ? "'" : '';
        $zc = '';
        for ($i = $sql['import']['namefirstline']; $i < $zeilen + $sql['import']['namefirstline']; ++$i) {
            //Importieren
            $insert = 'INSERT INTO '.$sql['import']['table'].' VALUES(';
            if (1 == $sql['import']['createindex']) {
                $insert .= "'', ";
            }
            $zc .= trim(rtrim($sql['import']['csv'][$i]));
            //echo "Zeile $i: $zc<br>";
            if ('' != $zc) { // && substr($zc,-1)== $enc) {
                $zeile = explode($sql['import']['trenn'], $zc);
                for ($j = 0; $j < $importfelder; ++$j) {
                    $a = ('' == $zeile[$j] && '' == $enc) ? "''" : $zeile[$j];
                    $insert .= $enc.$a.$enc.(($j == $importfelder - 1) ? ");\n" : ',');
                }
                MOD_DoSQL($insert);
                ++$sql['import']['lines_imported'];
                $zc = '';
            }
        }
        $r .= sprintf($lang['L_CSV_FIELDSLINES'], $importfelder, $sql['import']['lines_imported']);
    }

    $r .= '</span>';
    return $r;
}

function ImportCreateTable()
{
    global $sql, $lang, $db, $config;
    $tbl = [];
    $sql = "SHOW TABLES FROM $db";
    $tabellen = mod_query($sql);
    // while ($row = mysqli_fetch_row($num_tables))
    while ($row = mysqli_fetch_row($tabellen)) {
        $tbl[] = strtolower($row[0]);
    }
    $i = 0;
    $sql['import']['table'] = $sql['import']['table'].$i;
    while (in_array($sql['import']['table'], $tbl)) {
        $sql['import']['table'] = substr($sql['import']['table'], 0, strlen($sql['import']['table']) - 1).++$i;
    }
    $create = 'CREATE TABLE `'.$sql['import']['table'].'` ('.((1 == $sql['import']['createindex']) ? '`import_id` int(11) unsigned NOT NULL auto_increment, ' : '');
    if ($sql['import']['namefirstline']) {
        for ($i = 0; $i < count($sql['import']['first_zeile']); ++$i) {
            $create .= '`'.$sql['import']['first_zeile'][$i].'` VARCHAR(250) NOT NULL, ';
        }
    } else {
        for ($i = 0; $i < count($sql['import']['first_zeile']); ++$i) {
            $create .= '`FIELD_'.$i.'` VARCHAR(250) NOT NULL, ';
        }
    }
    if (1 == $sql['import']['createindex']) {
        $create .= 'PRIMARY KEY (`import_id`) ';
    } else {
        $create = substr($create, 0, strlen($create) - 2);
    }

    $create .= ') '.((MOD_NEW_VERSION) ? 'ENGINE' : 'TYPE')."=MyISAM COMMENT='imported at ".date('l dS of F Y H:i:s A')."'";
    $res = mysqli_query($config['dbconnection'], $create) || exit(SQLError($create, mysqli_error($config['dbconnection'])));
    return 1;
}

function ExportXML()
{
    global $sql, $config;
    $tab = "\t";
    $level = 0;
    $t = '<?xml version="1.0" encoding="UTF-8" ?>'."\n".'<database name="'.$sql['export']['db'].'">'."\n";
    ++$level;
    $time_start = time();

    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    for ($table = 0; $table < count($sql['export']['tables']); ++$table) {
        $t .= str_repeat($tab, $level++).'<table name="'.$sql['export']['tables'][$table].'">'."\n";
        $sqlt = 'SHOW Fields FROM `'.$sql['export']['db'].'`.`'.$sql['export']['tables'][$table].'`;';
        $res = mod_query($sqlt);
        if ($res) {
            $numfields = mysqli_num_rows($res);
            if (1 == $sql['export']['xmlstructure']) {
                $t .= str_repeat($tab, $level++).'<structure>'."\n";
                for ($feld = 0; $feld < $numfields; ++$feld) {
                    $row = mysqli_fetch_array($res);
                    $t .= str_repeat($tab, $level++).'<field no="'.$feld.'">'."\n";
                    $t .= str_repeat($tab, $level).'<name>'.$row['Field'].'</name>'."\n";
                    $t .= str_repeat($tab, $level).'<type>'.$row['Type'].'</type>'."\n";
                    $t .= str_repeat($tab, $level).'<null>'.$row['Null'].'</null>'."\n";
                    $t .= str_repeat($tab, $level).'<key>'.$row['Key'].'</key>'."\n";
                    $t .= str_repeat($tab, $level).'<default>'.$row['Default'].'</default>'."\n";
                    $t .= str_repeat($tab, $level).'<extra>'.$row['Extra'].'</extra>'."\n";
                    $t .= str_repeat($tab, --$level).'</field>'."\n";
                }
                $t .= str_repeat($tab, --$level).'</structure>'."\n";
            }
        }
        $t .= str_repeat($tab, $level++).'<data>'."\n";
        $sqlt = 'SELECT * FROM `'.$sql['export']['db'].'`.`'.$sql['export']['tables'][$table].'`;';
        $res = mod_query($sqlt);
        if ($res) {
            $numrows = mysqli_num_rows($res);
            for ($data = 0; $data < $numrows; ++$data) {
                $t .= str_repeat($tab, $level)."<row>\n";
                ++$level;
                $row = mysqli_fetch_row($res);
                for ($feld = 0; $feld < $numfields; ++$feld) {
                    $t .= str_repeat($tab, $level).'<field no="'.$feld.'">'.$row[$feld].'</field>'."\n";
                }
                $t .= str_repeat($tab, --$level)."</row>\n";
                ++$sql['export']['lines'];
                if ('' == $config['memory_limit']) {
                    $config['memory_limit'] = 0;
                }
                if (strlen($t) > $config['memory_limit']) {
                    CSVOutput($t);
                    $t = '';
                }
                $time_now = time();
                if ($time_start >= $time_now + 30) {
                    $time_start = $time_now;
                    header('X-MODPing: Pong');
                }
            }
        }
        $t .= str_repeat($tab, --$level).'</data>'."\n";
        $t .= str_repeat($tab, --$level).'</table>'."\n";
    }
    $t .= str_repeat($tab, --$level).'</database>'."\n";
    CSVOutput($t, 1);
}

function ExportHTML()
{
    global $sql, $config, $lang;
    $header = '<html><head><title>MOD Export</title></head>';
    $footer = "\n\n</body>\n</html>";
    $content = '';
    $content .= '<h1>'.$lang['L_DB'].' '.$sql['export']['db'].'</h1>';

    $time_start = time();

    if (!isset($config['dbconnection'])) {
        mod_mysqli_connect();
    }
    for ($table = 0; $table < count($sql['export']['tables']); ++$table) {
        $content .= '<h2>Tabelle '.$sql['export']['tables'][$table].'</h2>'."\n";
        $fsql = 'show fields from `'.$sql['export']['tables'][$table].'`';
        $dsql = 'select * from `'.$sql['export']['tables'][$table].'`';
        //Struktur
        $res = mod_query($fsql);
        if ($res) {
            $field = $fieldname = $fieldtyp = [];
            $structure = "<table class=\"Table\">\n";
            $numfields = mysqli_num_rows($res);
            for ($feld = 0; $feld < $numfields; ++$feld) {
                $row = mysqli_fetch_row($res);
                $field[$feld] = $row[0];

                if (0 == $feld) {
                    $structure .= "<tr class=\"Header\">\n";
                    for ($i = 0; $i < count($row); ++$i) {
                        $str = mysqli_fetch_field($res, $i);
                        $fieldname[$i] = $str->name;
                        $fieldtyp[$i] = $str->type;
                        $structure .= '<th>'.$str->name."</th>\n";
                    }
                    $structure .= "</tr>\n<tr>\n";
                }
                for ($i = 0; $i < count($row); ++$i) {
                    $structure .= '<td class="Object">'.(('' != $row[$i]) ? $row[$i] : '&nbsp;')."</td>\n";
                }
                $structure .= "</tr>\n";
            }
            $structure .= "</table>\n";
        }
        if (1 == $sql['export']['htmlstructure']) {
            $content .= "<h3>Struktur</h3>\n".$structure;
        }
        //Daten

        $res = mod_query($dsql);
        if ($res) {
            $anz = mysqli_num_rows($res);
            $content .= "<h3>Daten ($anz Datens&auml;tze)</h3>\n";
            $content .= "<table class=\"Table\">\n";
            for ($feld = 0; $feld < count($field); ++$feld) {
                if (0 == $feld) {
                    $content .= "<tr class=\"Header\">\n";
                    for ($i = 0; $i < count($field); ++$i) {
                        $content .= '<th>'.$field[$i]."</th>\n";
                    }
                    $content .= "</tr>\n";
                }
            }
            for ($d = 0; $d < $anz; ++$d) {
                $row = mysqli_fetch_row($res);
                $content .= "<tr>\n";
                for ($i = 0; $i < count($row); ++$i) {
                    $content .= '<td class="Object">'.(('' != $row[$i]) ? $row[$i] : '&nbsp;')."</td>\n";
                }
                $content .= "</tr>\n";
            }
        }
        $content .= '</table>';
    }
    CSVOutput($header.$content.$footer);
}
