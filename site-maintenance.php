<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'site-maintenance.php');

// This script does not load session.php.
// session.php won't run until a configuration file and database connection exist...
// This next block of code is a minimal version of session.php
define('KT_KIWITREES', 'kiwitrees');
define('KT_ROOT', '');
define('KT_GED_ID', 0);
define('KT_USER_ID', 0);
define('KT_DATA_DIR', realpath('data') . DIRECTORY_SEPARATOR);
$KT_SESSION = new stdClass();
$KT_SESSION->locale='';
// Invoke the Zend Framework Autoloader, so we can use Zend_XXXXX and KT_XXXXX classes
set_include_path(KT_ROOT . 'library' . PATH_SEPARATOR . get_include_path());
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('KT_');
require 'includes/functions/functions.php';
require 'includes/functions/functions_utf-8.php';
define('KT_LOCALE', KT_I18N::init());

header('Content-Type: text/html; charset=UTF-8');
header($_SERVER['SERVER_PROTOCOL'].' 503 Service Temporarily Unavailable');
?>

<!DOCTYPE html>
<html <?php echo KT_I18N::html_markup(); ?> >
	<head>
		<meta charset="UTF-8">
		<title><?php echo KT_KIWITREES . ' - ' . KT_I18N::translate('Maintenance'); ?></title>
		<meta name="robots" content="noindex,follow">
		<style type="text/css">
			body {color: gray; background-color: white; font: 16px tahoma, arial, helvetica, sans-serif; padding:10px; width: 90%;}
			h1, h2, h3 {font-weight: normal; text-align:center;}
			h1 {color: #81A9CB;}
			a {color: #81A9CB; font-weight: bold; text-decoration: none;}
			a:hover {text-decoration: none;}
			.content {border:1px solid gray; padding:15px; border-radius:15px; text-align: center;}
			p {text-align: center;}
			p a {font-weight:normal}
		</style>
	</head>
	<body>
		<h1><?php echo KT_I18N::translate('This website is temporarily unavailable'); ?></h1>
		<div class="content">
			<h2>
				<?php echo KT_I18N::translate('Sorry for any inconvenience but the site is currently undergoing important maintenance'); ?>
			</h2>
			<h3>
				<?php echo KT_I18N::translate('It shouldn\'t take too long, so please <a href="index.php">try again</a> later'); ?>
			</h3>
		</div>
		<p>
			<a href="login.php"><?php echo KT_I18N::translate('administration'); ?></a>
		<p>
	</body>
</html>
