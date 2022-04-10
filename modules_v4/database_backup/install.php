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

$install_ftp_server = $install_ftp_user_name = $install_ftp_user_pass = $install_ftp_path = '';
$dbhost = $dbuser = $dbpass = $dbport = $dbsocket = $manual_db = '';
foreach ($_GET as $getvar => $getval) {
    ${$getvar} = $getval;
}
foreach ($_POST as $postvar => $postval) {
    ${$postvar} = $postval;
}
include_once './inc/functions.php';
include_once './inc/mysqli.php';
include_once './inc/runtime.php';
if (!isset($language)) {
    $language = 'en';
}

$config['language'] = $language;
include './language/lang_list.php';
include 'language/'.$language.'/lang_install.php';
include 'language/'.$language.'/lang_main.php';
include 'language/'.$language.'/lang_config_overview.php';

// Passing the parameters via FORM
if (isset($_POST['dbhost'])) {
    $config['dbhost'] = $dbhost;
    $config['dbuser'] = $dbuser;
    $config['dbpass'] = $dbpass;
    $config['dbport'] = $dbport;
    $config['dbsocket'] = $dbsocket;
    $config['manual_db'] = $manual_db;
} else {
    // If connection string exists -> read connection data from connstr
    if (isset($connstr) && !empty($connstr)) {
        $p = explode('|', $connstr);
        $dbhost = $config['dbhost'] = $p[0];
        $dbuser = $config['dbuser'] = $p[1];
        $dbpass = $config['dbpass'] = $p[2];
        $dbport = $config['dbport'] = $p[3];
        $dbsocket = $config['dbsocket'] = $p[4];
        $manual_db = $config['manual_db'] = $p[5];
    } else {
        $connstr = '';
    }
}

//Variabeln
$phase = (isset($phase)) ? $phase : 0;
if (isset($_POST['manual_db'])) {
    $manual_db = trim($_POST['manual_db']);
}
$connstr = "$dbhost|$dbuser|$dbpass|$dbport|$dbsocket|$manual_db";
$connection = '';
$delfiles = [];

$config['files']['iconpath'] = './css/mod/icons/';
$img_ok = '<img src="'.$config['files']['iconpath'].'ok.gif" width="16" height="16" alt="ok">';
$img_failed = '<img src="'.$config['files']['iconpath'].'notok.gif" width="16" height="16" alt="failed">';
$href = "install.php?language=$language&phase=$phase&connstr=$connstr";
header('content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<meta name="robots" content="noindex,nofollow">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="cache-control" content="must-revalidate">
	<title>MyOOS [Dumper]  - Installation</title>

	<link rel="stylesheet" type="text/css" href="css/mod/style.css">
	<script src="js/script.js" type="text/javascript"></script>
<style type="text/css" media="screen">
td {
	border: 1px solid #ddd;
}

td table td {
	border: 0;
}
</style>
</head>
<body class="content">
<script>
function hide_tooldivs() {
	<?php
    foreach ($lang['languages'] as $key) {
        echo 'document.getElementById("'.$key.'").style.display = \'none\';'."\n";
    }
    ?>
}

function show_tooldivs(lab) {
	hide_tooldivs();
	switch(lab) {
		<?php
        foreach ($lang['languages'] as $key) {
            echo 'case "'.$key.'":'."\n".'document.getElementById("'.$key.'").style.display = \'block\';'."\n".'break;'."\n";
        }
        ?>

	}
}
</script>

<?php
if ($phase < 10) {
            if (0 == $phase) {
                $content = $lang['L_INSTALL'].' - '.$lang['L_INSTALLMENU'];
            } else {
                $content = $lang['L_INSTALL'].' - '.$lang['L_STEP'].' '.($phase);
            }
        } elseif ($phase > 9 && $phase < 12) {
            $content = $lang['L_INSTALL'].' - '.$lang['L_STEP'].' '.($phase - 7);
        } elseif ($phase > 19 && $phase < 100) {
            $content = $lang['L_TOOLS'];
        } else {
            $content = $lang['L_UNINSTALL'].' - '.$lang['L_STEP'].' '.($phase - 99);
        }

echo '<img src="css/mod/pics/h1_logo.gif" alt="'.$lang['L_INSTALL_TOMENU'].'">';
echo '<div id="pagetitle"><p>
'.$content.'
</p></div>';

echo '<div id="content" align="center"><p class="small"><strong>Version '.MOD_VERSION.'</strong><br></p>';

switch ($phase) {
    case 0: // Anfang - Sprachauswahl
        // da viele ja nicht in die Anleitung schauen -> versuchen die Perldateien automatisch richtig zu chmodden
        @chmod('./mod_cron/crondump.pl', 0755);
        @chmod('./mod_cron/perltest.pl', 0755);
        @chmod('./mod_cron/simpletest.pl', 0755);

        echo '<form action="install.php" method="get"><input type="hidden" name="phase" value="1">';
        echo '<table class="bdr"><tr class="thead"><th>Language</th><th>Tools</th></tr>';
        echo '<tr><td valign="top" width="300"><table>';
        echo GetLanguageCombo('radio', 'radio', 'language', '<tr><td>', '</td></tr>');
        echo '</table></td><td valign="top">';

        foreach ($lang['languages'] as $key) {
            echo "\n<div id=\"".$key.'"><a href="install.php?language='.$key.'&phase=100">'.$lang['L_TOOLS1'][$key].'</a><br><br>';
            echo '</div>';
        }

        echo "\n</td></tr><tr><td colspan=\"2\" style=\"padding: 4px\"><input type=\"submit\" name=\"submit\" value=\"Installation\" class=\"Formbutton\"></td></tr></table></form>";
        echo '<script>show_tooldivs("'.$language.'");</script>';
        break;
    case 1: // checken
        @chmod('config.php', 0666);
        echo '<h6>'.$lang['L_DBPARAMETER'].'</h6>';
        if (!is_writable('config.php')) {
            echo '<p class="warning">'.$lang['L_CONFIGNOTWRITABLE'].'</p>';
            echo '<a href="'.$href.'">'.$lang['L_TRYAGAIN'].'</a>';
            echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="install.php">'.$lang['L_INSTALL_TOMENU'].'</a>';
        } else {
            $tmp = file('config.php');

            $stored = 0;

            if (!isset($_POST['dbconnect'])) {
                // Erstaufruf - Daten aus config.php auslesen
                for ($i = 0; $i < count($tmp); ++$i) {
                    if ('$config[\'dbhost\']' == substr($tmp[$i], 0, 17)) {
                        $config['dbhost'] = extractValue($tmp[$i]);
                        $dbhost = $config['dbhost'];
                        ++$stored;
                    }
                    if ('$config[\'dbport\']' == substr($tmp[$i], 0, 17)) {
                        $config['dbport'] = extractValue($tmp[$i]);
                        $dbport = $config['dbport'];
                        ++$stored;
                    }
                    if ('$config[\'dbsocket\']' == substr($tmp[$i], 0, 19)) {
                        $config['dbsocket'] = extractValue($tmp[$i]);
                        $dbsocket = $config['dbsocket'];
                        ++$stored;
                    }
                    if ('$config[\'dbuser\']' == substr($tmp[$i], 0, 17)) {
                        $config['dbuser'] = extractValue($tmp[$i]);
                        $dbuser = $config['dbuser'];
                        ++$stored;
                    }
                    if ('$config[\'dbpass\']' == substr($tmp[$i], 0, 17)) {
                        $config['dbpass'] = extractValue($tmp[$i]);
                        $dbpass = $config['dbpass'];
                        ++$stored;
                    }
                    if ('$config[\'language\']' == substr($tmp[$i], 0, 19)) {
                        $config['language'] = extractValue($tmp[$i]);
                        ++$stored;
                    }
                    if (6 == $stored) {
                        break;
                    }
                }
            }

            if (!isset($config['dbport'])) {
                $config['dbport'] = '';
            }
            if (!isset($config['dbsocket'])) {
                $config['dbsocket'] = '';
            }

            echo '<form action="install.php?language='.$language.'&phase='.$phase.'" method="post">';
            echo '<table class="bdr" style="width:700px;">';
            echo '<tr><td>'.$lang['L_DB_HOST'].':</td><td><input type="text" name="dbhost" value="'.$dbhost.'" size="60" maxlength="100"></td></tr>';
            echo '<tr><td>'.$lang['L_DB_USER'].':</td><td><input type="text" name="dbuser" value="'.$dbuser.'" size="60" maxlength="100"></td></tr>';
            echo '<tr><td>'.$lang['L_DB_PASS'].':</td><td><input type="password" name="dbpass" value="'.$dbpass.'" size="60" maxlength="100"></td></tr>';
            echo '<tr><td>* '.$lang['L_DB'].':<p class="small">('.$lang['L_ENTER_DB_INFO'].')</p></td><td><input type="text" name="manual_db" value="'.$manual_db.'" size="60" maxlength="100"></td></tr>';
            echo '<tr><td>';
            echo $lang['L_PORT'].':</td><td><input type="text" name="dbport" value="'.$dbport.'" size="5" maxlength="5">&nbsp;&nbsp;'.$lang['L_INSTALL_HELP_PORT'].'</td></tr>';
            echo '<tr><td>'.$lang['L_SOCKET'].':</td><td><input type="text" name="dbsocket" value="'.$dbsocket.'" size="30" maxlength="255">&nbsp;&nbsp;'.$lang['L_INSTALL_HELP_SOCKET'].'</td></tr>';

            echo '<tr><td>'.$lang['L_TESTCONNECTION'].':</td><td><input type="submit" name="dbconnect" value="'.$lang['L_CONNECTTOMYSQL'].'" class="Formbutton"></td></tr>';
            if (isset($_POST['dbconnect'])) {
                echo '<tr class="thead"><th colspan="2">'.$lang['L_DBCONNECTION'].'</th></tr>';
                echo '<tr><td colspan="2">';
                $connection = mod_mysqli_connect();

                if (false === $connection) {
                    echo '<p class="error">'.$lang['L_CONNECTIONERROR'].'</p><span>&nbsp;';
                } else {
                    $databases = [];
                    echo '<p class="success">'.$lang['L_CONNECTION_OK'].'</p><span class="ssmall">';
                    $connection = 'ok';
                    $connstr = "$dbhost|$dbuser|$dbpass|$dbport|$dbsocket|$manual_db";
                    echo '<input type="hidden" name="connstr" value="'.$connstr.'">';
                    if ($manual_db > '') {
                        SearchDatabases(1, $manual_db);
                    } else {
                        SearchDatabases(1);
                    }
                    if (!isset($databases['Name']) || !in_array($manual_db, $databases['Name'])) {
                        // conect to manual db was not successful
                        $connstr = substr($connstr, 0, strlen($connstr) - strlen($manual_db));
                        $manual_db = '';
                    }
                }
                echo '</span></td></tr>';
            }
            echo '</table></form><br>';

            if ('ok' == $connection) {
                if (!isset($databases['Name'][0])) {
                    echo '<br>'.$lang['L_NO_DB_FOUND_INFO'];
                }

                echo '<form action="install.php?language='.$language.'&phase='.($phase + 1).'" method="post">';
                echo '<input type="hidden" name="dbhost" value="'.$config['dbhost'].'">
			<input type="hidden" name="dbuser" value="'.$config['dbuser'].'">
			<input type="hidden" name="dbpass" value="'.$config['dbpass'].'">
			<input type="hidden" name="manual_db" value="'.$manual_db.'">
			<input type="hidden" name="dbport" value="'.$config['dbport'].'">
			<input type="hidden" name="dbsocket" value="'.$config['dbsocket'].'">
			<input type="hidden" name="connstr" value="'.$connstr.'">';
                echo '<input type="submit" name="submit" value=" '.$lang['L_SAVEANDCONTINUE'].' " class="Formbutton"></form>';
            }
        }
        break;

    case 2:
        echo '<h6>MyOOS [Dumper] - '.$lang['L_CONFBASIC'].'</h6>';
        $tmp = @file('config.php');
        $stored = 0;
        for ($i = 0; $i < count($tmp); ++$i) {
            if ('$config[\'dbhost\']' == substr($tmp[$i], 0, 17)) {
                $tmp[$i] = '$config[\'dbhost\'] = \''.$dbhost.'\';'."\n";
                ++$stored;
            }
            if ('$config[\'dbport\']' == substr($tmp[$i], 0, 17)) {
                $tmp[$i] = '$config[\'dbport\'] = \''.$dbport.'\';'."\n";
                ++$stored;
            }
            if ('$config[\'dbsocket\']' == substr($tmp[$i], 0, 19)) {
                $tmp[$i] = '$config[\'dbsocket\'] = \''.$dbsocket.'\';'."\n";
                ++$stored;
            }
            if ('$config[\'dbuser\']' == substr($tmp[$i], 0, 17)) {
                $tmp[$i] = '$config[\'dbuser\'] = \''.$dbuser.'\';'."\n";
                ++$stored;
            }
            if ('$config[\'dbpass\']' == substr($tmp[$i], 0, 17)) {
                $tmp[$i] = '$config[\'dbpass\'] = \''.$dbpass.'\';'."\n";
                ++$stored;
            }

            if (6 == $stored) {
                break;
            }
        }
        $ret = true;
        if ($fp = fopen('config.php', 'wb')) {
            if (!fwrite($fp, implode('', $tmp))) {
                $ret = false;
            }
            @chmod('config.php', 0644);
        }
        if (!$ret) {
            echo '<p class="warnung">'.$lang['L_SAVE_ERROR'].'</p>';
        } else {
            echo $lang['L_INSTALL_STEP2FINISHED'];
            echo '<p>&nbsp;</p>';
            echo '<form action="install.php?language='.$language.'&phase='.($phase + 2).'" method="post" name="continue"><input type="hidden" name="connstr" value="'.$connstr.'"><input class="Formbutton" style="width:360px;" type="submit" name="continue2" value=" '.$lang['L_INSTALL_STEP2_1'].' "></form>';
            echo '<script>';
            echo 'document.forms["continue"].submit();';
            echo '</script>';
        }

        break;

    case 4: //Verzeichnisse
        if (isset($_POST['submit'])) {
            $ret = true;
            if ($fp = fopen('config.php', 'wb')) {
                if (!fwrite($fp, stripslashes(stripslashes($_POST['configfile'])))) {
                    $ret = false;
                }
                if (!fclose($fp)) {
                    $ret = false;
                }
            } else {
                $ret = false;
            }

            if (false == $ret) {
                echo '<br><strong>'.$lang['L_ERRORMAN'].' config.php '.$lang['L_MANUELL'].'.';
                exit();
            }
        }

        echo '<h6>'.$lang['L_CREATEDIRS'].'</h6>';
        $check_dirs = [
                            'work/',
                            'work/config/',
                            'work/log/',
                            'work/backup/',
                            'work/cache/',
                            'work/temp/'
        ];
        $msg = '';
        foreach ($check_dirs as $d) {
            $success = SetFileRechte($d, 1, 0777);
            if (1 != $success) {
                $msg .= $success.'<br>';
            }
        }

        if ($msg > '') {
            echo '<b>'.$msg.'</b>';
        }


        $iw[0] = IsWritable('work');
        $iw[1] = IsWritable('work/config');
        $iw[2] = IsWritable('work/log');
        $iw[3] = IsWritable('work/backup');
        $iw[4] = IsWritable('work/cache');
        $iw[5] = IsWritable('work/temp');

        if ($iw[0] && $iw[1] && $iw[2] && $iw[3] && $iw[4] && $iw[5]) {
            echo '<script>';
            echo 'self.location.href=\'install.php?language='.$language.'&phase=5&connstr='.$connstr.'\'';
            echo '</script>';
        }

        echo '<form action="install.php?language='.$language.'&phase=4" method="post"><table class="bdr"><tr class="thead">';
        echo '<th>'.$lang['L_DIR'].'</th><th>'.$lang['L_RECHTE'].'</th><th>'.$lang['L_STATUS'].'</th></tr>';
        echo '<tr><td><strong>work</strong></td><td>'.Rechte('work').'</td><td>'.(($iw[0]) ? $img_ok : $img_failed).'</td></tr>';
        echo '<tr><td><strong>work/config</strong></td><td>'.Rechte('work/config').'</td><td>'.(($iw[1]) ? $img_ok : $img_failed).'</td></tr>';
        echo '<tr><td><strong>work/log</strong></td><td>'.Rechte('work/log').'</td><td>'.(($iw[2]) ? $img_ok : $img_failed).'</td></tr>';
        echo '<tr><td><strong>work/backup</strong></td><td>'.Rechte('work/backup').'</td><td>'.(($iw[3]) ? $img_ok : $img_failed).'</td></tr>';
        echo '<tr><td><strong>work/cache</strong></td><td>'.Rechte('work/cache').'</td><td>'.(($iw[4]) ? $img_ok : $img_failed).'</td></tr>';
        echo '<tr><td><strong>work/temp</strong></td><td>'.Rechte('work/temp').'</td><td>'.(($iw[5]) ? $img_ok : $img_failed).'</td></tr>';

        echo '<tr><td colspan="3" align="right"><input type="hidden" name="connstr" value="'.$connstr.'"><input class="Formbutton" type="submit" name="dir_check" value=" '.$lang['L_CHECK_DIRS'].' "></td></tr>';
        if ($iw[0] && $iw[1] && $iw[2] && $iw[3] && $iw[4] && $iw[5]) {
            echo '<tr><td colspan="2">'.$lang['L_DIRS_CREATED'].'<br><br><input class="Formbutton" type="Button" value=" '.$lang['L_INSTALL_CONTINUE'].' " onclick="location.href=\'install.php?language='.$language.'&phase=5&connstr='.$connstr.'\'"></td></tr>';
        }
        echo '</table></form>';
        break;
    case 5:
        echo '<h6>'.$lang['L_LASTSTEP'].'</h6>';

        echo '<br><h4>'.$lang['L_INSTALLFINISHED'].'</h4>';
        SetDefault(1);
        include 'language/'.$language.'/lang_install.php';

        // direkt zum Start des Dumeprs
        echo '<script>self.location.href=\'index.php\';</script>';
        break;
    case 100: //uninstall
        echo '<h6>'.$lang['L_UI1'].'</h6>';
        echo '<h6>'.$lang['L_UI2'].'</h6>';
        echo '<a href="install.php">'.$lang['L_UI3'].'</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<a href="install.php?language='.$language.'&phase=101">'.$lang['L_UI4'].'</a>';
        break;
    case 101:
        echo '<h6>'.$lang['L_UI5'].'</h6>';
        $paths = [];
        $w = substr($config['paths']['work'], 0, strlen($config['paths']['work']) - 1);
        if (is_dir($w)) {
            $res = rec_rmdir($w);
        } else {
            $res = 0;
        }
        // wurde das Verzeichnis korrekt gelöscht
        if (0 == $res) {
            // das Verzeichnis wurde korrekt gelöscht
            echo '<p>'.$lang['L_UI6'].'</p>';
            echo $lang['L_UI7'].'<br>"'.Realpfad('./').'"<br> '.$lang['L_MANUELL'].'.<br><br>';
            echo '<a href="../">'.$lang['L_UI8'].'</a>';
        } else {
            echo '<p class="Warnung">'.$lang['L_UI9'].'"'.$paths[count($paths) - 1].'"';
        }
        break;
}

?>

</div>
</body>
</html>


<?php

//eigene Funktionen
// rec_rmdir - loesche ein Verzeichnis rekursiv
// Rueckgabewerte:
//   0  - alles ok
//   -1 - kein Verzeichnis
//   -2 - Fehler beim Loeschen
//   -3 - Ein Eintrag eines Verzeichnisses war keine Datei und kein Verzeichnis und
//        kein Link
function rec_rmdir($path)
{
    global $paths;
    $paths[] = $path;
    // schau' nach, ob das ueberhaupt ein Verzeichnis ist
    if (!is_dir($path)) {
        return -1;
    }
    // oeffne das Verzeichnis
    $dir = @opendir($path);
    // Fehler?
    if (!$dir) {
        return -2;
    }

    // gehe durch das Verzeichnis
    while ($entry = @readdir($dir)) {
        // wenn der Eintrag das aktuelle Verzeichnis oder das Elternverzeichnis
        // ist, ignoriere es
        if ('.' == $entry || '..' == $entry) {
            continue;
        }
        // wenn der Eintrag ein Verzeichnis ist, dann
        if (is_dir($path.'/'.$entry)) {
            // rufe mich selbst auf
            $res = rec_rmdir($path.'/'.$entry);
            // wenn ein Fehler aufgetreten ist
            if (-1 == $res) { // dies duerfte gar nicht passieren
                @closedir($dir); // Verzeichnis schliessen
                return -2; // normalen Fehler melden
            } elseif (-2 == $res) { // Fehler?
                @closedir($dir); // Verzeichnis schliessen
                return -2; // Fehler weitergeben
            } elseif (-3 == $res) { // nicht unterstuetzer Dateityp?
                @closedir($dir); // Verzeichnis schliessen
                return -3; // Fehler weitergeben
            } elseif (0 != $res) { // das duerfe auch nicht passieren...
                @closedir($dir); // Verzeichnis schliessen
                return -2; // Fehler zurueck
            }
        } elseif (is_file($path.'/'.$entry) || is_link($path.'/'.$entry)) {
            // ansonsten loesche diese Datei / diesen Link
            $res = @unlink($path.'/'.$entry);
            // Fehler?
            if (!$res) {
                @closedir($dir); // Verzeichnis schliessen
                return -2; // melde ihn
            }
        } else {
            // ein nicht unterstuetzer Dateityp
            @closedir($dir); // Verzeichnis schliessen
            return -3; // tut mir schrecklich leid...
        }
    }

    // schliesse nun das Verzeichnis
    @closedir($dir);

    // versuche nun, das Verzeichnis zu loeschen
    $res = @rmdir($path);

    // gab's einen Fehler?
    if (!$res) {
        return -2; // melde ihn
    }

    // alles ok
    return 0;
}

function Rechte($file)
{
    clearstatcache();

    return @substr(decoct(fileperms($file)), -3);
}

function extractValue($s)
{
    $r = trim(substr($s, strpos($s, '=') + 1));
    $r = substr($r, 0, strlen($r) - 1);
    if ("'" == substr($r, -1) || '"' == substr($r, -1)) {
        $r = substr($r, 0, strlen($r) - 1);
    }
    if ("'" == substr($r, 0, 1) || '"' == substr($r, 0, 1)) {
        $r = substr($r, 1);
    }

    return $r;
}

ob_end_flush();
exit();
