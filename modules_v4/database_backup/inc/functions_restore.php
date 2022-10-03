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

define('DEBUG', 0);
if (!defined('MOD_VERSION')) {
    exit('No direct access.');
}
function get_sqlbefehl()
{
    global $restore, $config, $databases, $lang;

    //Init
    $restore['fileEOF'] = false;
    $restore['EOB'] = false;
    $complete_sql = '';
    $sqlparser_status = 0;
    if (!isset($restore['eintraege_ready'])) {
        $restore['eintraege_ready'] = 0;
    }

    //Parsen
    while (100 != $sqlparser_status && !$restore['fileEOF'] && !$restore['EOB']) {
        //nächste Zeile lesen
        $zeile = ($restore['compressed']) ? gzgets($restore['filehandle']) : fgets($restore['filehandle']);
        if (DEBUG) {
            echo '<br><br>Zeile: '.htmlspecialchars((string) $zeile);
        }
        /******************* Setzen des Parserstatus *******************/
        // herausfinden um was für einen Befehl es sich handelt
        if (0 == $sqlparser_status) {
            //Vergleichszeile, um nicht bei jedem Vergleich strtoupper ausführen zu müssen
            $zeile2 = strtoupper(trim($zeile));
            // pre-built compare strings - so we need the CPU power only once :)
            $sub9 = substr($zeile2, 0, 9);
            $sub7 = substr($sub9, 0, 7);
            $sub6 = substr($sub7, 0, 6);
            $sub4 = substr($sub6, 0, 4);
            $sub3 = substr($sub4, 0, 3);
            $sub2 = substr($sub3, 0, 2);
            $sub1 = substr($sub2, 0, 1);

            if ('INSERT ' == $sub7) {
                $sqlparser_status = 3; //Datensatzaktion
                $restore['actual_table'] = get_tablename($zeile);
            }

            //Einfache Anweisung finden die mit Semikolon beendet werden
            elseif ('LOCK TA' == $sub7) {
                $sqlparser_status = 4;
            } elseif ('COMMIT' == $sub6) {
                $sqlparser_status = 7;
            } elseif ('BEGIN' == substr($sub6, 0, 5)) {
                $sqlparser_status = 7;
            } elseif ('UNLOCK TA' == $sub9) {
                $sqlparser_status = 4;
            } elseif ('SET' == $sub3) {
                $sqlparser_status = 4;
            } elseif ('START ' == $sub6) {
                $sqlparser_status = 4;
            } elseif ('/*!' == $sub3) {
                $sqlparser_status = 5;
            } //MySQL-Condition oder Kommentar
            elseif ('ALTER TAB' == $sub9) {
                $sqlparser_status = 4;
            } // Alter Table
            elseif ('CREATE TA' == $sub9) {
                $sqlparser_status = 2;
            } //Create Table
            elseif ('CREATE AL' == $sub9) {
                $sqlparser_status = 2;
            } //Create View
            elseif ('CREATE IN' == $sub9) {
                $sqlparser_status = 4;
            } //Indexaktion

            //Condition?
            elseif ((5 != $sqlparser_status) && ('/*' == substr($zeile2, 0, 2))) {
                $sqlparser_status = 6;
            }

            // Delete actions
            elseif ('DROP TABL' == $sub9) {
                $sqlparser_status = 1;
            } elseif ('DROP VIEW' == $sub9) {
                $sqlparser_status = 1;
            }

            // Befehle, die nicht ausgeführt werden sollen
            elseif ('CREATE DA' == $sub9) {
                $sqlparser_status = 7;
            } elseif ('DROP DATA ' == $sub9) {
                $sqlparser_status = 7;
            } elseif ('USE' == $sub3) {
                $sqlparser_status = 7;
            }

            // Am Ende eines MySQLDumper-Backups angelangt?
            elseif ('-- EOB' == $sub6 || '# EO' == $sub4) {
                $restore['EOB'] = true;
                $restore['fileEOF'] = true;
                $zeile = '';
                $zeile2 = '';
                $sqlparser_status = 100;
            }

            // Kommentar?
            elseif ('--' == $sub2 || '#' == $sub1) {
                $zeile = '';
                $zeile2 = '';
                $sqlparser_status = 0;
            }

            // Fortsetzung von erweiterten Inserts
            if (1 == $restore['flag']) {
                $sqlparser_status = 3;
            }

            if ((0 == $sqlparser_status) && (trim($complete_sql) > '') && (-1 == $restore['flag'])) {
                // Unbekannten Befehl entdeckt
                v($restore);
                echo '<br>Sql: '.htmlspecialchars((string) $complete_sql);
                echo '<br>Erweiterte Inserts: '.$restore['erweiterte_inserts'];
                exit('<br>'.$lang['L_UNKNOWN_SQLCOMMAND'].': '.$zeile.'<br><br>'.$complete_sql);
            }
            /******************* Ende von Setzen des Parserstatus *******************/
        }

        $last_char = substr(rtrim($zeile), -1);
        // Zeilenumbrüche erhalten - sonst werden Schlüsselwörter zusammengefügt
        // z.B. 'null' und in der nächsten Zeile 'check' wird zu 'nullcheck'
        $complete_sql .= $zeile."\n";

        if (3 == $sqlparser_status) {
            //INSERT
            if (SQL_Is_Complete($complete_sql)) {
                $sqlparser_status = 100;
                $complete_sql = trim($complete_sql);
                if ('*/' == substr($complete_sql, -2)) {
                    $complete_sql = remove_comment_at_eol($complete_sql);
                }

                // letzter Ausdruck des erweiterten Inserts erreicht?
                if (');' == substr($complete_sql, -2)) {
                    $restore['flag'] = -1;
                }

                // Wenn am Ende der Zeile ein Klammer Komma -> erweiterter Insert-Modus -> Steuerflag setzen
                elseif ('),' == substr($complete_sql, -2)) {
                    // letztes Komme gegen Semikolon tauschen
                    $complete_sql = substr($complete_sql, 0, -1).';';
                    $restore['erweiterte_inserts'] = 1;
                    $restore['flag'] = 1;
                }

                if ('INSERT ' != substr(strtoupper($complete_sql), 0, 7)) {
                    // wenn der Syntax aufgrund eines Reloads verloren ging - neu ermitteln
                    if (!isset($restore['insert_syntax'])) {
                        $restore['insert_syntax'] = get_insert_syntax($restore['actual_table']);
                    }
                    $complete_sql = $restore['insert_syntax'].' VALUES '.$complete_sql.';';
                } else {
                    // INSERT Syntax ermitteln und merken
                    $ipos = strpos(strtoupper($complete_sql), ' VALUES');
                    if (false === !$ipos) {
                        $restore['insert_syntax'] = substr($complete_sql, 0, $ipos);
                    } else {
                        $restore['insert_syntax'] = 'INSERT INTO `'.$restore['actual_table'].'`';
                    }
                }
            }
        } elseif (1 == $sqlparser_status) {
            //Löschaktion
            if (';' == $last_char) {
                $sqlparser_status = 100;
            } //Befehl komplett
            $restore['actual_table'] = get_tablename($complete_sql);
        } elseif (2 == $sqlparser_status) {
            // Createanweisung ist beim Finden eines ; beendet
            if (';' == $last_char) {
                if ($config['minspeed'] > 0) {
                    $restore['anzahl_zeilen'] = $config['minspeed'];
                }
                // Soll die Tabelle hergestellt werden?
                $do_it = true;
                if (is_array($restore['tables_to_restore'])) {
                    $do_it = false;
                    if (in_array($restore['actual_table'], $restore['tables_to_restore'])) {
                        $do_it = true;
                    }
                }
                if ($do_it) {
                    $tablename = submit_create_action($complete_sql);
                    $restore['actual_table'] = $tablename;
                    ++$restore['table_ready'];
                }
                // Zeile verwerfen, da CREATE jetzt bereits ausgefuehrt wurde und naechsten Befehl suchen
                $complete_sql = '';
                $sqlparser_status = 0;
            }
        }

        // Index
                elseif (4 == $sqlparser_status) { //Createindex
                        if (';' == $last_char) {
                            if ($config['minspeed'] > 0) {
                                $restore['anzahl_zeilen'] = $config['minspeed'];
                            }
                            $complete_sql = del_inline_comments($complete_sql);
                            $sqlparser_status = 100;
                        }
                }

        // Kommentar oder Condition
                    elseif (5 == $sqlparser_status) { //Anweisung
                            $t = strrpos($zeile, '*/;');
                        if (false === !$t) {
                            $restore['anzahl_zeilen'] = $config['minspeed'];
                            $sqlparser_status = 100;
                            if ($config['ignore_enable_keys'] &&
                                    false !== strrpos($zeile, 'ENABLE KEYS ')) {
                                $sqlparser_status = 100;
                                $complete_sql = '';
                            }
                        }
                    }

        // Mehrzeiliger oder Inline-Kommentar
        elseif (6 == $sqlparser_status) {
            $t = strrpos($zeile, '*/');
            if (false === !$t) {
                $complete_sql = '';
                $sqlparser_status = 0;
            }
        }

        // Befehle, die verworfen werden sollen
                            elseif (7 == $sqlparser_status) { //Anweisung
                                    if (';' == $last_char) {
                                        if ($config['minspeed'] > 0) {
                                            $restore['anzahl_zeilen'] = $config['minspeed'];
                                        }
                                        $complete_sql = '';
                                        $sqlparser_status = 0;
                                    }
                            }

        if (($restore['compressed']) && (gzeof($restore['filehandle']))) {
            $restore['fileEOF'] = true;
        }
        if ((!$restore['compressed']) && (feof($restore['filehandle']))) {
            $restore['fileEOF'] = true;
        }
    }
    // wenn bestimmte Tabellen wiederhergestellt werden sollen -> pruefen
    if (is_array($restore['tables_to_restore']) && !(in_array($restore['actual_table'], $restore['tables_to_restore']))) {
        $complete_sql = '';
    }
    return trim($complete_sql);
}

function submit_create_action($sql)
{
    global $config;

    //executes a create command
    $tablename = get_tablename($sql);
    if ('CREATE ALGORITHM' == strtoupper(substr($sql, 0, 16))) {
        // It`s a VIEW. We need to substitute the original DEFINER with the actual MySQL-User
        $parts = explode(' ', $sql);
        for ($i = 0, $count = sizeof($parts); $i < $count; ++$i) {
            if ('DEFINER=' == strtoupper(substr($parts[$i], 0, 8))) {
                $parts[$i] = 'DEFINER=`'.$config['dbuser'].'`@`'.$config['dbhost'].'`';
                $sql = implode(' ', $parts);
                $i = $count;
            }
        }
    }

    $res = mysqli_query($config['dbconnection'], $sql);
    if (false === $res) {
        // erster Versuch fehlgeschlagen -> zweiter Versuch - vielleicht versteht der Server die Inline-Kommentare nicht?
        $sql = del_inline_comments($sql);
        $res = mysqli_query($config['dbconnection'], downgrade($sql));
    }
    if (false === $res) {
        // wenn wir hier angekommen sind hat nichts geklappt -> Fehler ausgeben und abbrechen
        SQLError($sql, mysqli_error($config['dbconnection']));
        exit("<br>Fatal error: Couldn't create table or view `".$tablename.'´');
    }
    return $tablename;
}

function get_insert_syntax($table)
{
    global $config;

    $insert = '';
    $sql = 'SHOW COLUMNS FROM `'.$table.'`';
    $res = mysqli_query($config['dbconnection'], $sql);
    if ($res) {
        $insert = 'INSERT INTO `'.$table.'` (';
        while ($row = mysqli_fetch_object($res)) {
            $insert .= '`'.$row->Field.'`,';
        }
        $insert = substr($insert, 0, strlen($insert) - 1).') ';
    } else {
        global $restore;
        v($restore);
        SQLError($sql, mysqli_error($config['dbconnection']));
    }
    return $insert;
}

function del_inline_comments($sql)
{
    //$sql=str_replace("\n",'<br>', $sql);
    $array = [];
    preg_match_all("/(\/\*(.+)\*\/)/U", $sql, $array);
    if (is_array($array[0])) {
        $sql = str_replace($array[0], '', $sql);
        if (DEBUG) {
            echo 'Nachher: :<br>'.$sql.'<br><hr>';
        }
    }
    //$sql=trim(str_replace('<br>',"\n", $sql));
    //Wenn nach dem Entfernen nur noch ein ; übrigbleibt -> entfernen
    if (';' == $sql) {
        $sql = '';
    }
    return $sql;
}

// extrahiert auf einfache Art den Tabellennamen aus dem "Create",Drop"-Befehl
function get_tablename($t)
{
    // alle Schluesselbegriffe entfernen, bis der Tabellenname am Anfang steht
    $t = substr($t, 0, 150); // verkuerzen, um Speicher zu sparen - wir brauchenhier nur den Tabellennamen
    $t = str_ireplace('DROP TABLE', '', $t);
    $t = str_ireplace('DROP VIEW', '', $t);
    $t = str_ireplace('CREATE TABLE', '', $t);
    $t = str_ireplace('INSERT INTO', '', $t);
    $t = str_ireplace('REPLACE INTO', '', $t);
    $t = str_ireplace('IF NOT EXISTS', '', $t);
    $t = str_ireplace('IF EXISTS', '', $t);
    if ('CREATE ALGORITHM' == substr(strtoupper($t), 0, 16)) {
        $pos = strpos($t, 'DEFINER VIEW ');
        $t = substr($t, $pos, strlen($t) - $pos);
    }
    $t = str_ireplace(';', ' ;', $t); // tricky -> insert space as delimiter
    $t = trim($t);

    // jetzt einfach nach dem ersten Leerzeichen suchen
    $delimiter = substr($t, 0, 1);
    if ('`' != $delimiter) {
        $delimiter = ' ';
    }
    $found = false;
    $position = 1;
    while (!$found) {
        if (substr($t, $position, 1) == $delimiter) {
            $found = true;
        }
        if ($position >= strlen($t)) {
            $found = true;
        }
        ++$position;
    }
    $t = substr($t, 0, $position);
    $t = trim(str_replace('`', '', $t));
    return $t;
}

// decide if an INSERT-Command is complete - simply count quotes and look for ); at the end of line
function SQL_Is_Complete($string)
{
    $string = str_replace('\\\\', '', trim($string)); // trim and remove escaped backslashes
    $string = trim($string);
    $quotes = substr_count($string, '\'');
    $escaped_quotes = substr_count($string, '\\\'');
    if (($quotes - $escaped_quotes) % 2 == 0) {
        $compare = substr($string, -2);
        if ('*/' == $compare) {
            $compare = substr(trim(remove_comment_at_eol($string)), -2);
        }
        if (');' == $compare) {
            return true;
        }
        if ('),' == $compare) {
            return true;
        }
    }
    return false;
}

function remove_comment_at_eol($string)
{
    // check for Inline-Comments at the end of the line
    if ('*/' == substr(trim($string), -2)) {
        $pos = strrpos($string, '/*');
        if ($pos > 0) {
            $string = trim(substr($string, 0, $pos));
        }
    }
    return $string;
}
