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
$var = (isset($_GET['var'])) ? $_GET['var'] : 'prozesse';
$Titelausgabe = [
'variables' => $lang['L_VARIABELN'], 'status' => $lang['L_STATUS'], 'prozesse' => $lang['L_PROZESSE'], ];
echo '<h5>'.$lang['L_MYSQLVARS'].'</h5><strong>'.$Titelausgabe[$var].'</strong>&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<a href="main.php?action=vars&amp;var=prozesse">'.$lang['L_PROZESSE'].'</a>&nbsp;&nbsp;&nbsp;';
echo '<a href="main.php?action=vars&amp;var=status">'.$lang['L_STATUS'].'</a>&nbsp;&nbsp;&nbsp;';
echo '<a href="main.php?action=vars&amp;var=variables">'.$lang['L_VARIABELN'].'</a>&nbsp;&nbsp;&nbsp;';

echo '<p>&nbsp;</p>';
//Variabeln
switch ($var) {
    case 'variables':
        $res = mysqli_query($config['dbconnection'], 'SHOW variables');
        if ($res) {
            $numrows = mysqli_num_rows($res);
        }
        if (0 == $numrows) {
            echo $lang['L_INFO_NOVARS'];
        } else {
            echo '<table class="bdr"><tr class="thead"><th><strong>Name</strong></th><th><strong>'.$lang['L_INHALT'].'</strong></th></tr>';
            for ($i = 0; $i < $numrows; ++$i) {
                $row = mysqli_fetch_array($res);
                $cl = ($i % 2) ? 'dbrow' : 'dbrow1';
                echo '<tr class="'.$cl.'"><td align="left">'.$row[0].'</td><td  align="left">'.$row[1].'</td></tr>';
            }
        }
        echo '</table>';
        break;
    case 'status':
        $res = mysqli_query($config['dbconnection'], 'SHOW STATUS');
        if ($res) {
            $numrows = mysqli_num_rows($res);
        }
        if (0 == $numrows) {
            echo $lang['L_INFO_NOSTATUS'];
        } else {
            echo '<table class="bdr"><tr class="thead"><th>Name</th><th>'.$lang['L_INHALT'].'</th></tr>';
            for ($i = 0; $i < $numrows; ++$i) {
                $cl = ($i % 2) ? 'dbrow' : 'dbrow1';
                $row = mysqli_fetch_array($res);
                echo '<tr class="'.$cl.'"><td align="left" valign="top">'.$row[0].'</td><td align="left" valign="top">'.$row[1].'</td></tr>';
            }
        }
        echo '</table>';
        break;
    case 'prozesse':
        if ($config['processlist_refresh'] < 1000) {
            $config['processlist_refresh'] = 2000;
        }
        if (isset($_GET['killid']) && $_GET['killid'] > 0) {
            $killid = (isset($_GET['killid'])) ? $_GET['killid'] : 0;
            $wait = (isset($_GET['wait'])) ? $_GET['wait'] : 0;
            if (0 == $wait) {
                $ret = mysqli_query($config['dbconnection'], 'KILL '.$_GET['killid']);
                $wait = 2;
            } else {
                $wait += 2;
            }

            if (0 == $wait) {
                echo '<p class="success">'.$lang['L_PROCESSKILL1'].$_GET['killid'].' '.$lang['L_PROCESSKILL2'].'</p>';
            } else {
                echo '<p class="success">'.$lang['L_PROCESSKILL3'].$wait.$lang['L_PROCESSKILL4'].$_GET['killid'].' '.$lang['L_PROCESSKILL2'].'</p>';
            }
        }

        $killid = $wait = 0;
        $res = mysqli_query($config['dbconnection'], 'SHOW FULL PROCESSLIST ');
        if ($res) {
            $numrows = mysqli_num_rows($res);
        }
        if (0 == $numrows) {
            echo $lang['L_INFO_NOPROCESSES'];
        } else {
            echo '<table class="bdr" style="width:100%"><tr class="thead"><th>ID</th><th>User</th><th>Host</th><th>DB</th><th>Command</th><th>Time</th><th>State</th><th width="800">Info</th><th nowrap="nowrap">RT: '.round($config['processlist_refresh'] / 1000).' sec</th></tr>';
            for ($i = 0; $i < $numrows; ++$i) {
                $cl = ($i % 2) ? 'dbrow' : 'dbrow1';
                $row = mysqli_fetch_array($res);
                echo '<tr><td>'.$row[0].'</td><td>'.$row[1].'</td>
					<td>'.$row[2].'</td><td>'.$row[3].'</td><td>'.$row[4].'</td><td>'.$row[5].'</td>
					<td>'.$row[6].'</td><td>'.$row[7].'</td>
					<td><a href="main.php?action=vars&amp;var=prozesse&amp;killid='.$row[0].'">kill</a></td></tr>';
                if ($row[0] == $killid && 'Killed' == $row[4]) {
                    $wait = $killid = 0;
                }
            }
        }
        echo '</table>';
        echo '<form name="f" method="get" action="main.php">
			<input type="hidden" name="wait" value="'.$wait.'">
			<input type="hidden" name="killid" value="'.$killid.'">
			<input type="hidden" name="action" value="vars">
			<input type="hidden" name="var" value="prozesse"></form>';
        echo '<script>window.setTimeout("document.f.submit();","'.$config['processlist_refresh'].'");</script>';

        break;
}
