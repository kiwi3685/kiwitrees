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
$Sum_Files = $Sum_Size = 0;
$Last_BU = [];
$is_htaccess = (file_exists('./.htaccess'));
$is_protected = IsAccessProtected();
$is_new_version_available = (isset($update) && is_object($update) && $check_update === true) ? $update->newVersionAvailable() : false;

// find latest backup file
$available = [];
if ('' == $databases['multisetting']) {
    $available[0] = $databases['db_actual'];
} else {
    $available = explode(';', $databases['multisetting']);
}
$dh = opendir($config['paths']['backup']);
while (false !== ($filename = readdir($dh))) {
    if ('.' != $filename && '..' != $filename && !is_dir($config['paths']['backup'].$filename)) {
        foreach ($available as $item) {
            $pos = strpos($filename, $item);
            if ($pos === false) {
                // Der Datenbankname wurde nicht in der Konfiguration gefunden;
            } else {
                $files[] = $filename;
                ++$Sum_Files;
                $Sum_Size += filesize($config['paths']['backup'].$filename);
                $ft = filectime($config['paths']['backup'].$filename);
                if (!isset($Last_BU[2]) || (isset($Last_BU[2]) && $ft > $Last_BU[2])) {
                    $Last_BU[0] = $filename;
                    $Last_BU[1] = date('d.m.Y H:i', $ft);
                    $Last_BU[2] = $ft;
                }
            }
        }
    }
}


if (!is_writable($config['paths']['temp'])) {
    $ret = SetFileRechte($config['paths']['temp'], 1, 0777);
}
if (!is_writable($config['paths']['cache'])) {
    $ret = SetFileRechte($config['paths']['cache'], 1, 0777);
}
$directory_warnings = DirectoryWarnings();

if ($is_new_version_available) {
    $update_info = $lang['L_NEW_MOD_VERSION'] . ': ' . $update->getLatestVersion();
}

$tpl = new MODTemplate();
$tpl->set_filenames([
    'show' => 'tpl/home/home.tpl', ]);
$tpl->assign_vars([
    'THEME' => $config['theme'],
    'MOD_VERSION' => MOD_VERSION,
    'OS' => MOD_OS,
    'OS_EXT' => MOD_OS_EXT,
    'MYSQL_VERSION' => MOD_MYSQL_VERSION,
    'PHP_VERSION' => PHP_VERSION,
    'MEMORY' => byte_output($config['php_ram'] * 1024 * 1024),
    'MAX_EXECUTION_TIME' => $config['max_execution_time'],
    'PHP_EXTENSIONS' => $config['phpextensions'],
    'SERVER_NAME' => $_SERVER['SERVER_NAME'],
    'MOD_PATH' => $config['paths']['root'],
    'DB' => $databases['db_actual'],
    'NR_OF_BACKUP_FILES' => $Sum_Files,
    'SIZE_BACKUPS' => byte_output($Sum_Size),
    'FREE_DISKSPACE' => MD_FreeDiskSpace(),
]);



if ($is_new_version_available) {
    $tpl->assign_block_vars('NEW_VERSION_EXISTS', []);
}

if ($update_info > '') {
    $tpl->assign_block_vars('UPDATE_INFO', [
    'MSG' => $update_info, ]);
}


if ($directory_warnings > '') {
    $tpl->assign_block_vars('DIRECTORY_WARNINGS', [
    'MSG' => $directory_warnings, ]);
}

if ($config['disabled'] > '') {
    $tpl->assign_block_vars('DISABLED_FUNCTIONS', [
    'PHP_DISABLED_FUNCTIONS' => str_replace(',', ', ', $config['disabled']), ]);
}

if (!extension_loaded('ftp')) {
    $tpl->assign_block_vars('NO_FTP', []);
}
if (!$config['zlib']) {
    $tpl->assign_block_vars('NO_ZLIB', []);
}

if (false === $is_protected) {
    $tpl->assign_block_vars('DIRECTORY_PROTECTION_STATUS_ERROR', ['MSG' => $lang['L_HTACC_CHECK_ERROR']]);
} elseif (1 === $is_protected && !$is_htaccess) {
    $tpl->assign_block_vars('DIRECTORY_PROTECTION_STATUS', ['MSG' => $lang['L_HTACC_NOT_NEEDED']]);
} elseif (1 === $is_protected && $is_htaccess) {
    $tpl->assign_block_vars('DIRECTORY_PROTECTION_STATUS', ['MSG' => $lang['L_HTACC_COMPLETE']]);
} elseif (0 === $is_protected && $is_htaccess) {
    $tpl->assign_block_vars('DIRECTORY_PROTECTION_STATUS_ERROR', ['MSG' => $lang['L_HTACC_INCOMPLETE']]);
} else {
    $tpl->assign_block_vars('DIRECTORY_PROTECTION_STATUS_ERROR', ['MSG' => $lang['L_HTACC_PROPOSED']]);
}

if ($is_htaccess) {
    $tpl->assign_block_vars('HTACCESS_EXISTS', []);
} else {
    $tpl->assign_block_vars('HTACCESS_DOESNT_EXISTS', []);
}

if ($Sum_Files > 0 && isset($Last_BU[1])) {
    $tpl->assign_block_vars('LAST_BACKUP', [
    'LAST_BACKUP_INFO' => $Last_BU[1],
    'LAST_BACKUP_LINK' => $config['paths']['backup'].urlencode($Last_BU[0]),
    'LAST_BACKUP_NAME' => $Last_BU[0], ]);
}
$tpl->pparse('show');
