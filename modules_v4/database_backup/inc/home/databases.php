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
include './language/'.$config['language'].'/lang_sql.php';
$checkit = (isset($_GET['checkit'])) ? urldecode($_GET['checkit']) : '';
$repair = (isset($_GET['repair'])) ? $_GET['repair'] : 0;
$enableKeys = (isset($_GET['enableKeys'])) ? $_GET['enableKeys'] : '';
for ($i = 0; $i < count($databases['Name']); ++$i) {
    if (isset($_POST['empty'.$i])) {
        EmptyDB($databases['Name'][$i]);
        $dba = '<p class="green">'.$lang['L_DB'].' '.$databases['Name'][$i].' '.$lang['L_INFO_CLEARED'].'</p>';
        break;
    }
    if (isset($_POST['kill'.$i])) {
        $res = mysqli_query($config['dbconnection'], 'DROP DATABASE `'.$databases['Name'][$i].'`') or exit(mysqli_error($config['dbconnection']));
        $dba = '<p class="green">'.$lang['L_DB'].' '.$databases['Name'][$i].' '.$lang['L_INFO_DELETED'].'</p>';
        SetDefault();
        include $config['files']['parameter'];
        echo '<script>parent.MyOOS_Dumper_menu.location.href="menu.php?action=dbrefresh";</script>';
        break;
    }
    if (isset($_POST['optimize'.$i])) {
        mysqli_select_db($config['dbconnection'], $databases['Name'][$i]);
        $res = mysqli_query($config['dbconnection'], 'SHOW TABLES FROM `'.$databases['Name'][$i].'`');
        $tabellen = '';
        while ($row = mysqli_fetch_row($res)) {
            $tabellen .= '`'.$row[0].'`,';
        }
        $tabellen = substr($tabellen, 0, (strlen($tabellen) - 1));
        if ($tabellen > '') {
            $query = 'OPTIMIZE TABLE '.$tabellen;
            $res = mysqli_query($config['dbconnection'], $query) or exit(mysqli_error($config['dbconnection']).'');
        }
        $_GET['dbid'] = $i;
        $dba = '<p class="green">'.$lang['L_DB'].' <b>'.$databases['Name'][$i].'</b> '.$lang['L_INFO_OPTIMIZED'].'.</p>';
        break;
    }
    if (isset($_POST['check'.$i])) {
        $checkit = 'ALL';
        $_GET['dbid'] = $i;
    }
    if (isset($_POST['enableKeys'.$i])) {
        $enableKeys = 'ALL';
        $_GET['dbid'] = $i;
    }
}

//list databases
$tpl = new MODTemplate();
$tpl->set_filenames([
    'show' => './tpl/home/databases_list_dbs.tpl', ]);
$tpl->assign_vars([
    'ICONPATH' => $config['files']['iconpath'], ]);

if (!isset($config['dbconnection'])) {
    mod_mysqli_connect();
}
for ($i = 0; $i < count($databases['Name']); ++$i) {
    $rowclass = ($i % 2) ? 'dbrow' : 'dbrow1';
    if ($i == $databases['db_selected_index']) {
        $rowclass = 'dbrowsel';
    }

    //gibts die Datenbank überhaupt?
    if (!mysqli_select_db($config['dbconnection'], $databases['Name'][$i])) {
        $tpl->assign_block_vars('DB_NOT_FOUND', [
            'ROWCLASS' => $rowclass,
            'NR' => ($i + 1),
            'DB_NAME' => $databases['Name'][$i],
            'DB_ID' => $i, ]);
    } else {
        mysqli_select_db($config['dbconnection'], $databases['Name'][$i]);
        $tabellen = mysqli_query($config['dbconnection'], 'SHOW TABLES FROM `'.$databases['Name'][$i].'`');
        $num_tables = mysqli_num_rows($tabellen);
        $tpl->assign_block_vars('ROW', [
            'ROWCLASS' => $rowclass,
            'NR' => ($i + 1),
            'DB_NAME' => $databases['Name'][$i],
            'DB_ID' => $i,
            'TABLE_COUNT' => $num_tables, ]);
        if (1 == $num_tables) {
            $tpl->assign_block_vars('ROW.TABLE', []);
        } else {
            $tpl->assign_block_vars('ROW.TABLES', []);
        }
    }
}
$tpl->pparse('show');

//list tables of selected database
if (isset($_GET['dbid'])) {
    $disabled_keys_found = false;

    // Output list of tables of the selected database
    $tpl = new MODTemplate();
    $tpl->set_filenames([
        'show' => 'tpl/home/databases_list_tables.tpl', ]);
    $dbid = $_GET['dbid'];

    $numrows = 0;
    $res = mysqli_query($config['dbconnection'], 'SHOW TABLE STATUS FROM `'.$databases['Name'][$dbid].'`');
    mysqli_select_db($config['dbconnection'], $databases['Name'][$dbid]);
    if ($res) {
        $numrows = mysqli_num_rows($res);
    }
    $tpl->assign_vars([
        'DB_NAME' => $databases['Name'][$dbid],
        'DB_NAME_URLENCODED' => urlencode($databases['Name'][$dbid]),
        'DB_ID' => $dbid,
        'TABLE_COUNT' => $numrows,
        'ICONPATH' => $config['files']['iconpath'], ]);
    $numrows = intval($numrows);
    if ($numrows > 1) {
        $tpl->assign_block_vars('MORE_TABLES', []);
    } elseif (1 == $numrows) {
        $tpl->assign_block_vars('1_TABLE', []);
    } elseif (0 == $numrows) {
        $tpl->assign_block_vars('NO_TABLE', []);
    }
    if ($numrows > 0) {
        $last_update = '2000-01-01 00:00:00';
        $sum_records = $sum_data_length = 0;
        for ($i = 0; $i < $numrows; ++$i) {
            $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
            // Get nr of records -> need to do it this way because of incorrect returns when using InnoDBs
            $sql_2 = 'SELECT count(*) as `count_records` FROM `'.$databases['Name'][$dbid].'`.`'.$row['Name'].'`';
            $res2 = mysqli_query($config['dbconnection'], $sql_2);
            if (false === $res2) {
                $row['Rows'] = 0;
                $rowclass = 'dbrowsel';
            } else {
                $row2 = mysqli_fetch_array($res2);
                $row['Rows'] = $row2['count_records'];
                $rowclass = ($i % 2) ? 'dbrow' : 'dbrow1';
            }

            if (isset($row['Update_time']) && strtotime($row['Update_time']) > strtotime($last_update)) {
                $last_update = $row['Update_time'];
            }
            $sum_records += $row['Rows'];
            $sum_data_length += $row['Data_length'] + $row['Index_length'];

            $keys_disabled = false;
            if ('MyIsam' == $row['Engine']) {
            }
            $tpl->assign_block_vars('ROW', [
                'ROWCLASS' => $rowclass,
                'NR' => ($i + 1),
                'TABLE_NAME' => $row['Name'],
                'TABLE_NAME_URLENCODED' => urlencode($row['Name']),
                'RECORDS' => $row['Rows'],
                'SIZE' => byte_output($row['Data_length'] + $row['Index_length']),
                'LAST_UPDATE' => $row['Update_time'],
                'ENGINE' => $row['Engine'],
            ]);

            // Otimize & Repair - only for MyISAM-Tables
            if ('MyISAM' == $row['Engine']) {
                if (0 == $row['Data_free']) {
                    $tpl->assign_block_vars('ROW.OPTIMIZED', []);
                } else {
                    $tpl->assign_block_vars('ROW.NOT_OPTIMIZED', []);
                }

                if ($checkit == $row['Name'] || 1 == $repair) {
                    $tmp_res = mysqli_query($config['dbconnection'], 'REPAIR TABLE `'.$row['Name'].'`');
                }

                if (($checkit == $row['Name'] || 'ALL' == $checkit)) {
                    // table needs to be checked
                    $tmp_res = mysqli_query($config['dbconnection'], 'CHECK TABLE `'.$row['Name'].'`');
                    if ($tmp_res) {
                        $tmp_row = mysqli_fetch_row($tmp_res);
                        if ('OK' == $tmp_row[3]) {
                            $tpl->assign_block_vars('ROW.CHECK_TABLE_OK', []);
                        } else {
                            $tpl->assign_block_vars('ROW.CHECK_TABLE_NOT_OK', []);
                        }
                    }
                } else {
                    // Show Check table link
                    $tpl->assign_block_vars('ROW.CHECK_TABLE', []);
                }
                if ($enableKeys == $row['Name'] || 'ALL' == $enableKeys) {
                    $sSql = 'ALTER TABLE `'.$databases['Name'][$dbid].'`.`'.$row['Name'].'` ENABLE KEYS';
                    $tmp_res = mysqli_query($config['dbconnection'], $sSql);
                }
                $res3 = mysqli_query($config['dbconnection'], 'SHOW INDEX FROM `'.$databases['Name'][$dbid].'`.`'.$row['Name'].'`');
                while ($row3 = mysqli_fetch_array($res3, MYSQLI_ASSOC)) {
                    if ('disabled' == $row3['Comment']) {
                        $keys_disabled = true;
                        $disabled_keys_found = true;
                    }
                }
                if ($keys_disabled) {
                    $tpl->assign_block_vars('ROW.KEYS_DISABLED', []);
                } else {
                    $tpl->assign_block_vars('ROW.KEYS_ENABLED', []);
                }
            }
        }
        // Output sum-row
        $tpl->assign_block_vars('SUM', [
            'RECORDS' => number_format($sum_records, 0, ',', '.'),
            'SIZE' => byte_output($sum_data_length),
            'LAST_UPDATE' => $last_update, ]);
        if ($disabled_keys_found) {
            $tpl->assign_block_vars('DISABLED_KEYS_FOUND', []);
        }
    }
    $tpl->pparse('show');
}
