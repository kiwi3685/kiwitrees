<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'site-unavailable.php');

// This script does not load session.php.
// session.php won't run until a configuration file and database connection exist...
// This next block of code is a minimal version of session.php
define('KT_KIWITREES', 'kiwitrees');
define('KT_ROOT', '');
define('KT_GED_ID', 0);
define('KT_USER_ID', 0);
define('KT_DATA_DIR', realpath('data').DIRECTORY_SEPARATOR);
$KT_SESSION			= new stdClass();
$KT_SESSION->locale = '';
// Invoke the Zend Framework Autoloader, so we can use Zend_XXXXX and KT_XXXXX classes
set_include_path(KT_ROOT.'library'.PATH_SEPARATOR.get_include_path());
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('KT_');
require 'includes/functions/functions.php';
require KT_ROOT . 'includes/functions/functions_utf-8.php';
define('KT_LOCALE', KT_I18N::init());

header('Content-Type: text/html; charset=UTF-8');
header($_SERVER["SERVER_PROTOCOL"].' 503 Service Temporarily Unavailable');

echo
	'<!DOCTYPE html>',
	'<html ', KT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<title>', KT_KIWITREES, '</title>',
	'<meta name="robots" content="noindex,follow">',
	'<style type="text/css">
		body {color: gray; background-color: white; font: 14px tahoma, arial, helvetica, sans-serif; padding:10px; }
		a {color: #81A9CB; font-weight: bold; text-decoration: none;}
		a:hover {text-decoration: underline;}
		h1 {color: #81A9CB; font-weight:normal; text-align:center;}
		li {line-height:2;}
		blockquote {color:red;}
		.content { /*margin:auto; width:800px;*/ border:1px solid gray; padding:15px; border-radius:15px;}
		.good {color: green;}
	</style>',
	'</head><body>',
	'<h1>', KT_I18N::translate('This website is temporarily unavailable'), '</h1>',
	'<div class="content">',
	'<p>', KT_I18N::translate('Oops!  The webserver is unable to connect to the database server.  It could be busy, undergoing maintenance, or simply broken.  You should <a href="index.php">try again</a> in a few minutes or contact the website administrator.'), '</p>';

$config_ini_php = parse_ini_file('data/config.ini.php');
if (is_array($config_ini_php) && array_key_exists('dbhost', $config_ini_php) && array_key_exists('dbport', $config_ini_php) && array_key_exists('dbuser', $config_ini_php) && array_key_exists('dbpass', $config_ini_php) && array_key_exists('dbname', $config_ini_php)) {
	try {
		$dbh = new PDO('mysql:host='.$config_ini_php['dbhost'].';port='.$config_ini_php['dbport'].';dbname='.$config_ini_php['dbname'], $config_ini_php['dbuser'], $config_ini_php['dbpass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ, PDO::ATTR_CASE=>PDO::CASE_LOWER, PDO::ATTR_AUTOCOMMIT=>true));
	} catch (PDOException $ex) {
		echo '<p>', KT_I18N::translate('The database reported the following error message:'), '</p>';
		echo '<blockquote>', $ex->getMessage(), '</blockquote>';
	}
}

echo KT_I18N::translate('If you are the website administrator, you should check that:');
echo '<ol>';
echo '<li>', /* I18N: [you should check that:] ... */ KT_I18N::translate('the database connection settings in the file <b>/data/config.ini.php</b> are still correct'), '</li>';
echo '<li>', /* I18N: [you should check that:] ... */ KT_I18N::translate('the directory <b>/data</b> and the file <b>/data/config.ini.php</b> have access permissions that allow the webserver to read them'), '</li>';
echo '<li>', /* I18N: [you should check that:] ... */ KT_I18N::translate('you can connect to the database using other applications, such as phpmyadmin'), '</li>';
echo '</ol>';
echo '<p class="good">', KT_I18N::translate('If you cannot resolve the problem yourself, you can ask for help on the forums at <a href="%s/forums/">kiwitrees.net</a>', KT_KIWITREES_URL), '</p>';
echo '</div>';
echo '</body>';
echo '</html>';
