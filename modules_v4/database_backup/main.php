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

include_once './inc/header.php';
include_once './inc/runtime.php';
include_once './language/'.$config['language'].'/lang_main.php';
include './inc/template.php';

$action = (isset($_GET['action'])) ? $_GET['action'] : 'status';

if ('phpinfo' == $action) {
    // output phpinfo
    echo '<p align="center"><a href="main.php">&lt;&lt; Home</a></p>';
    phpinfo();
    echo '<p align="center"><a href="main.php">&lt;&lt; Home</a></p>';
    exit();
}

if (isset($_POST['htaccess']) || 'schutz' == $action) {
    include './inc/home/protection_create.php';
}
if ('edithtaccess' == $action) {
    include './inc/home/protection_edit.php';
}
if ('deletehtaccess' == $action) {
    include './inc/home/protection_delete.php';
}

// Output headnavi
$tpl = new MODTemplate();
$tpl->set_filenames([
    'show' => 'tpl/home/headnavi.tpl', ]);
$tpl->assign_vars([
    'HEADER' => MODHeader(),
    'HEADLINE' => headline($lang['L_HOME']), ]);
$tpl->pparse('show');

mod_mysqli_connect();
if ('status' == $action) {
    include './inc/home/home.php';
} elseif ('db' == $action) {
    include './inc/home/databases.php';
} elseif ('sys' == $action) {
    include './inc/home/system.php';
} elseif ('vars' == $action) {
    include './inc/home/mysql_variables.php';
}

echo MODFooter();
ob_end_flush();
exit();
