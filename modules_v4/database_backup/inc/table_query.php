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
include './language/'.$config['language'].'/lang.php';
include './language/'.$config['language'].'/lang_dump.php';
include './inc/template.php';
$tblr = ('dump' == $tblfrage_refer) ? 'Backup' : 'Restore';
$filename = (isset($_GET['filename'])) ? $_GET['filename'] : '';
if (isset($_POST['file'][0])) {
    $filename = $_POST['file'][0];
}

ob_start();
$tpl = new MODTemplate();
$sel_dump_encoding = isset($_POST['sel_dump_encoding']) ? $_POST['sel_dump_encoding'] : '';
$tpl = new MODtemplate();

//Informationen zusammenstellen
if ('Backup' == $tblr) {
    $tpl->set_filenames([
                            'show' => './tpl/dump_select_tables.tpl',
    ]);
    $button_name = 'dump_tbl';
    //Info aus der Datenbank lesen
    mod_mysqli_connect();
    $res = mysqli_query($config['dbconnection'], 'SHOW TABLE STATUS FROM `'.$databases['db_actual'].'`');
    $numrows = mysqli_num_rows($res);
    $tbl_zeile = '';
    for ($i = 0; $i < $numrows; ++$i) {
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        //v($row);
        // Get nr of records -> need to do it this way because of incorrect returns when using InnoDBs
        $sql_2 = 'SELECT count(*) as `count_records` FROM `'.$databases['db_actual'].'`.`'.$row['Name'].'`';
        $res2 = mysqli_query($config['dbconnection'], $sql_2);
        if (false === $res2) {
            $read_error = mysqli_error($config['dbconnection']);
            $row['Rows'] = '<span class="error">'.$lang['L_ERROR'].': '.$read_error.'</span>';
        } else {
            $row2 = mysqli_fetch_array($res2);
            $row['Rows'] = $row2['count_records'];
        }

        $klasse = ($i % 2) ? 1 : '';
        $table_size = $row['Data_length'] + $row['Index_length'];
        $table_type = $row['Engine'];
        if ('VIEW' == substr($row['Comment'], 0, 4)) {
            $table_type = 'View';
            $table_size = '-';
        }
        $tpl->assign_block_vars('ROW', [
                                            'CLASS' => 'dbrow'.$klasse,
                                            'ID' => $i,
                                            'NR' => $i + 1,
                                            'TABLENAME' => $row['Name'],
                                            'TABLETYPE' => $table_type,
                                            'RECORDS' => 'View' == $table_type ? '<i>'.$row['Rows'].'</i>' : '<strong>'.$row['Rows'].'</strong>',
                                            'SIZE' => is_int($table_size) ? byte_output($table_size) : $table_size,
                                            'LAST_UPDATE' => $row['Update_time'],
        ]);
    }
} else {
    $tpl->set_filenames([
                            'show' => './tpl/restore_select_tables.tpl',
    ]);
    //Restore - Header aus Backupfile lesen
    $button_name = 'restore_tbl';
    $gz = (substr($filename, -3)) == '.gz' ? 1 : 0;
    if ($gz) {
        $fp = gzopen($fpath.$filename, 'r');
        $statusline = gzgets($fp, 40960);
        $offset = gztell($fp);
    } else {
        $fp = fopen($fpath.$filename, 'r');
        $statusline = fgets($fp, 5000);
        $offset = ftell($fp);
    }
    //Header auslesen
    $sline = ReadStatusline($statusline);

    $anzahl_tabellen = $sline['tables'];
    $anzahl_eintraege = $sline['records'];
    $tbl_zeile = '';
    $part = ('' == $sline['part']) ? 0 : substr($sline['part'], 3);
    if (-1 == $anzahl_eintraege) {
        // not a backup of MySQLDumper
        $tpl->assign_block_vars('NO_MOD_BACKUP', []);
    } else {
        $tabledata = [];
        $i = 0;
        //Tabellenfinos lesen
        gzseek($fp, $offset);
        $eof = false;
        while (!$eof) {
            $line = $gz ? gzgets($fp, 40960) : fgets($fp, 40960);

            if ('-- TABLE|' == substr($line, 0, 9)) {
                $d = explode('|', $line);
                $tabledata[$i]['name'] = $d[1];
                $tabledata[$i]['records'] = $d[2];
                $tabledata[$i]['size'] = $d[3];
                $tabledata[$i]['update'] = $d[4];
                $tabledata[$i]['engine'] = isset($d[5]) ? $d[5] : '';
                ++$i;
            }
            if ('-- EOF' == substr($line, 0, 6)) {
                $eof = true;
            }
            if ('create' == substr(strtolower($line), 0, 6)) {
                $eof = true;
            }
        }
        for ($i = 0; $i < sizeof($tabledata); ++$i) {
            $klasse = ($i % 2) ? 1 : '';
            $tpl->assign_block_vars('ROW', [
                                                'CLASS' => 'dbrow'.$klasse,
                                                'ID' => $i,
                                                'NR' => $i + 1,
                                                'TABLENAME' => $tabledata[$i]['name'],
                                                'RECORDS' => $tabledata[$i]['records'],
                                                'SIZE' => byte_output($tabledata[$i]['size']),
                                                'LAST_UPDATE' => $tabledata[$i]['update'],
                                                'TABLETYPE' => $tabledata[$i]['engine'],
            ]);
        }
    }
    if ($gz) {
        gzclose($fp);
    } else {
        fclose($fp);
    }
}

if (!isset($dk)) {
    $dk = '';
}

$confirm_restore = $lang['L_FM_ALERTRESTORE1'].' `'.$databases['db_actual'].'`  '.$lang['L_FM_ALERTRESTORE2'].' '.$filename.' '.$lang['L_FM_ALERTRESTORE3'];

$tpl->assign_vars([
                        'PAGETITLE' => $tblr.' -'.$lang['L_TABLESELECTION'],
                        'L_NAME' => $lang['L_NAME'],
                        'L_DATABASE' => $lang['L_DB'],
                        'DATABASE' => $databases['db_actual'],
                        'L_LAST_UPDATE' => $lang['L_LASTBUFROM'],
                        'SEL_DUMP_ENCODING' => $sel_dump_encoding,
                        'FILENAME' => $filename,
                        'DUMP_COMMENT' => $dk,
                        'BUTTON_NAME' => $button_name,
                        'L_START_BACKUP' => $lang['L_STARTDUMP'],
                        'L_START_RESTORE' => $lang['L_FM_RESTORE'],
                        'L_ROWS' => $lang['L_INFO_RECORDS'],
                        'L_SIZE' => $lang['L_INFO_SIZE'],
                        'L_TABLE_TYPE' => $lang['L_TABLE_TYPE'],
                        'L_SELECT_ALL' => $lang['L_SELECTALL'],
                        'L_DESELECT_ALL' => $lang['L_DESELECTALL'],
                        'L_RESTORE' => $lang['L_RESTORE'],
                        'L_NO_MOD_BACKUP' => $lang['L_NOT_SUPPORTED'],
                        'L_CONFIRM_RESTORE' => $confirm_restore,
]);

$tpl->pparse('show');
ob_end_flush();
exit();
