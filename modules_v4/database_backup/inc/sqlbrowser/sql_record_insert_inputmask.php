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

// insert a new record
$tpl = new MODTemplate();
$tpl->set_filenames([
    'show' => './tpl/sqlbrowser/sql_record_insert_inputmask.tpl', ]);

$sqledit = "SHOW FIELDS FROM `$tablename`";
$res = mod_query($sqledit);
if ($res) {
    $num = mysqli_num_rows($res);

    $feldnamen = '';
    for ($x = 0; $x < $num; ++$x) {
        $row = mysqli_fetch_object($res);
        $feldnamen .= $row->Field.'|';
        $tpl->assign_block_vars('ROW', [
            'CLASS' => ($x % 2) ? 1 : 2,
            'FIELD_NAME' => $row->Field,
            'FIELD_ID' => correct_post_index($row->Field), ]);

        $type = strtoupper($row->Type);

        if ('YES' == strtoupper($row->Null)) {
            //field is nullable
            $tpl->assign_block_vars('ROW.IS_NULLABLE', []);
        }

        if (in_array($type, [
            'BLOB',
            'TEXT', ])) {
            $tpl->assign_block_vars('ROW.IS_TEXTAREA', []);
        } else {
            $tpl->assign_block_vars('ROW.IS_TEXTINPUT', []);
        }
    }
}

$tpl->assign_vars([
    'HIDDEN_FIELDS' => FormHiddenParams(),
    'FIELDNAMES' => substr($feldnamen, 0, strlen($feldnamen) - 1),
    'SQL_STATEMENT' => my_quotes($sql['sql_statement']), ]);

$tpl->pparse('show');
