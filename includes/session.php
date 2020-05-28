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

if (!defined('KT_SCRIPT_NAME')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Identify ourself
define('KT_KIWITREES',		'Kiwitrees');
define('KT_VERSION',		'3.3.8');
define('KT_VERSION_TEXT',	trim(KT_VERSION));

// External URLs
define('KT_KIWITREES_URL',		'https://kiwitrees.net/');
define('KT_SUPPORT_URL', 		'https://kiwitrees.net/forums/forum/support-forum/');
define('KT_TRANSLATORS_URL',	'https://kiwitrees.net/forums/forum/support-forum/translation/');

// Optionally, specify a CDN server for static content (e.g. CSS, JS, PNG)
// For example, http://my.cdn.com/kiwitrees-static-1.3.1/
define('KT_STATIC_URL', ''); // For example, http://my.cdn.com/kiwitrees-static-1.3.1/

// Optionally, load major JS libraries from Google’s public CDN
define ('KT_USE_GOOGLE_API', false);
if (KT_USE_GOOGLE_API) {
	define('KT_JQUERY_URL',        'https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
	define('KT_JQUERYUI_URL',      'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js');
} else {
	define('KT_JQUERY_URL',			KT_STATIC_URL . 'js/jquery.min.js');				// 1.12.4	Updated 03-09-2016
	define('KT_JQUERYUI_URL',		KT_STATIC_URL . 'js/jquery-ui.min.js');			    // 1.12.0	Updated 03-09-2016
}
define('KT_JQUERY_COLORBOX_URL',	KT_STATIC_URL . 'js/jquery.colorbox-min.js');	    // 1.6.1	Updated 18-06-2015
define('KT_JQUERY_DATATABLES_URL',	KT_STATIC_URL . 'js/jquery.dataTables.min.js');	    // 1.10.13	Updated 12-03-2017
define('KT_JQUERY_DT_HTML5',		KT_STATIC_URL . 'js/buttons.html5.min.js');	    	// Updated 12-03-2017
define('KT_JQUERY_DT_BUTTONS',		KT_STATIC_URL . 'js/dataTables.buttons.min.js');	// Updated 12-03-2017
define('KT_JQUERY_DT_RESPONSIVE',	KT_STATIC_URL . 'js/dataTables.responsive.min.js'); // 2.1.1	Updated 12-03-2017
define('KT_JQUERY_JEDITABLE_URL',	KT_STATIC_URL . 'js/jquery.jeditable.js');		    // 1.7.3
define('KT_JQUERY_WHEELZOOM_URL',	KT_STATIC_URL . 'js/jquery.wheelzoom.js');		    // 2.0.0
define('KT_MODERNIZR_URL',			KT_STATIC_URL . 'js/modernizr.custom.js');		    // 2.6.2
define('KT_JQUERY_AUTOSIZE',		KT_STATIC_URL . 'js/jquery.autosize.min.js');	    // 1.18.18	Updated 18-06-2015
define('KT_JQUERYUI_TOUCH_PUNCH',	KT_STATIC_URL . 'js/jquery.ui.touch-punch.min.js');
define('KT_JQUERY_SHORTEN',			KT_STATIC_URL . 'js/jquery.shorten.js');
define('KT_PASSWORDSCHECK',			KT_STATIC_URL . 'js/passwordscheck.js');			// Installed 09-11-2016
define('KT_D3_JS',					KT_STATIC_URL . 'js/d3.min.js');			// v4

// Loation of our own javascript libraries
define('KT_KIWITREES_JS_URL',		KT_STATIC_URL . 'js/kiwitrees.js');					// used system wide, via Pages WT class
define('KT_AUTOCOMPLETE_JS_URL',	KT_STATIC_URL . 'js/autocomplete.js');				// used system wide
define('KT_JQUERY_TREEVIEW_JS_URL',	KT_STATIC_URL . 'js/jquery.treeview.js');			// used in branches.php
define('KT_FANCY_TREEVIEW_JS_URL',	KT_STATIC_URL . 'js/fancytreeview.js');				// used in fancy_treeview_descendants & fancy_treeview_ancestors

// Location of our modules and themes.  These are used as URLs and folder paths.
define('KT_MODULES_DIR', 'modules_v4/'); // Update setup.php when this changes
define('KT_THEMES_DIR',  'themes/' );

// Enable debugging output?
define('KT_DEBUG',      false);
define('KT_DEBUG_SQL',  false);

//Font used to watermark images
define('KT_FONT_DEJAVU_SANS_TTF',	KT_STATIC_URL . 'library/KT/Fonts/DejaVuSans.ttf');

// Error reporting
define('KT_ERROR_LEVEL', 2); // 0=none, 1=minimal, 2=full

// Required version of database tables/columns/indexes/etc.
define('KT_SCHEMA_VERSION', 41);

// Regular expressions for validating user input, etc.
define('KT_MINIMUM_PASSWORD_LENGTH', 6);

define('KT_REGEX_XREF',     '[A-Za-z0-9:_-]+');
define('KT_REGEX_TAG',      '[_A-Z][_A-Z0-9]*');
define('KT_REGEX_INTEGER',  '-?\d+');
define('KT_REGEX_ALPHA',    '[a-zA-Z]+');
define('KT_REGEX_ALPHANUM', '[a-zA-Z0-9]+');
define('KT_REGEX_BYTES',    '[0-9]+[bBkKmMgG]?');
define('KT_REGEX_USERNAME', '[^<>"%{};]+');
define('KT_REGEX_PASSWORD', '.{' . KT_MINIMUM_PASSWORD_LENGTH . ',}');
define('KT_REGEX_NOSCRIPT', '[^<>"&%{};]*');
define('KT_REGEX_URL',      '[\/0-9A-Za-z_!~*\'().;?:@&=+$,%#-]+'); // Simple list of valid chars
define('KT_REGEX_EMAIL',    '[^\s<>"&%{};@]+@[^\s<>"&%{};@]+');
define('KT_REGEX_UNSAFE',   '[\x00-\xFF]*'); // Use with care and apply additional validation!

// UTF8 representation of various characters
define('KT_UTF8_BOM',    "\xEF\xBB\xBF"); // U+FEFF

// UTF8 control codes affecting the BiDirectional algorithm (see http://www.unicode.org/reports/tr9/)
define('KT_UTF8_LRM',    "\xE2\x80\x8E"); // U+200E  (Left to Right mark:  zero-width character with LTR directionality)
define('KT_UTF8_RLM',    "\xE2\x80\x8F"); // U+200F  (Right to Left mark:  zero-width character with RTL directionality)
define('KT_UTF8_LRO',    "\xE2\x80\xAD"); // U+202D  (Left to Right override: force everything following to LTR mode)
define('KT_UTF8_RLO',    "\xE2\x80\xAE"); // U+202E  (Right to Left override: force everything following to RTL mode)
define('KT_UTF8_LRE',    "\xE2\x80\xAA"); // U+202A  (Left to Right embedding: treat everything following as LTR text)
define('KT_UTF8_RLE',    "\xE2\x80\xAB"); // U+202B  (Right to Left embedding: treat everything following as RTL text)
define('KT_UTF8_PDF',    "\xE2\x80\xAC"); // U+202C  (Pop directional formatting: restore state prior to last LRO, RLO, LRE, RLE)

// Alternatives to BMD events for lists, charts, etc.
define('KT_EVENTS_BIRT', 'BIRT|CHR|BAPM|_BRTM|ADOP');
define('KT_EVENTS_DEAT', 'DEAT|BURI|CREM');
define('KT_EVENTS_MARR', 'MARR|_NMR');
define('KT_EVENTS_DIV',  'DIV|ANUL|_SEPR');

// Use these line endings when writing files on the server
define('KT_EOL', "\r\n");

// Gedcom specification/definitions
define ('KT_GEDCOM_LINE_LENGTH', 255-strlen(KT_EOL)); // Characters, not bytes

// Used in Google charts
define ('KT_GOOGLE_CHART_ENCODING', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.');

// Privacy constants
define('KT_PRIV_PUBLIC',  2); // Allows visitors to view the marked information
define('KT_PRIV_USER',    1); // Allows members to access the marked information
define('KT_PRIV_NONE',    0); // Allows managers to access the marked information
define('KT_PRIV_HIDE',   -1); // Hide the item to all users

// For performance, it is quicker to refer to files using absolute paths
define ('KT_ROOT', realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);

// Keep track of time statistics, for the summary in the footer
$start_time		= microtime(true);
$PRIVACY_CHECKS = 0;

// We want to know about all PHP errors
error_reporting(E_ALL | E_STRICT);

// Invoke the Zend Framework Autoloader, so we can use Zend_XXXXX and KT_XXXXX classes
set_include_path(KT_ROOT . 'library' . PATH_SEPARATOR . get_include_path());
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('KT_');

// Check configuration issues that affect various versions of PHP
if (version_compare(PHP_VERSION, '6.0', '<')) {
	if (get_magic_quotes_runtime()) {
		// Magic quotes were deprecated in PHP5.3 and removed in PHP6.0
		// Disabling them on PHP5.3 will cause a strict-warning, so ignore errors.
		@set_magic_quotes_runtime(false);
	}
	// magic_quotes_gpc can’t be disabled at run-time, so clean them up as necessary.
	if (get_magic_quotes_gpc() || ini_get('magic_quotes_sybase') && strtolower(ini_get('magic_quotes_sybase')) != 'off') {
		$in = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE);
		foreach ($in as $k => $v) {
			foreach ($v as $key => $val) {
				if (!is_array($val)) {
					$in[$k][$key] = stripslashes($val);
					continue;
				}
				$in[] =& $in[$k][$key];
			}
		}
		unset($in);
	}
}
// PHP requires a time zone to be set in php.ini
if (!ini_get('date.timezone')) {
	date_default_timezone_set(@date_default_timezone_get());
}

// Split the request protocol://host:port/path/to/script.php?var=value into parts
// KT_SERVER_NAME  = protocol://host:port
// KT_SCRIPT_PATH  = /path/to/   (begins and ends with /)
// KT_SCRIPT_NAME  = script.php  (already defined in the calling script)
// KT_QUERY_STRING = ?var=value  (generate as needed from $_GET.  lang=xx and theme=yy are removed as used.)
// TODO: we ought to generate this dynamically, but lots of code currently relies on this global
$QUERY_STRING = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

// Calculate the base URL, so we can generate absolute URLs.
$https    	= strtolower(KT_Filter::server('HTTPS'));
$protocol	= ($https === '' || $https === 'off') ? 'http' : 'https';
$protocol	= KT_Filter::server('HTTP_X_FORWARDED_PROTO', 'https?', $protocol);
$host		= KT_Filter::server('SERVER_ADDR', null, '127.0.0.1');
$host		= KT_Filter::server('SERVER_NAME', null, $host);
$port		= KT_Filter::server('SERVER_PORT', null, '80');
$port		= KT_Filter::server('HTTP_X_FORWARDED_PORT', '80|443', $port);
// Ignore the default port.
if ($protocol === 'http' && $port === '80' || $protocol === 'https' && $port === '443') {
	$port = '';
} else {
	$port = ':' . $port;
}

define('KT_SERVER_NAME', $protocol . '://' . $host . $port);

// SCRIPT_NAME should always be correct, but is not always present.
// PHP_SELF should always be present, but may have trailing path: /path/to/script.php/FOO/BAR
if (!empty($_SERVER['SCRIPT_NAME'])) {
	define('KT_SCRIPT_PATH', substr($_SERVER['SCRIPT_NAME'], 0, stripos($_SERVER['SCRIPT_NAME'], KT_SCRIPT_NAME)));
} elseif (!empty($_SERVER['PHP_SELF'])) {
	define('KT_SCRIPT_PATH', substr($_SERVER['PHP_SELF'], 0, stripos($_SERVER['PHP_SELF'], KT_SCRIPT_NAME)));
} else {
	// No server settings - probably running as a command line script
	define('KT_SCRIPT_PATH', '/');
}

// Microsoft IIS servers don’t set REQUEST_URI, so generate it for them.
if (!isset($_SERVER['REQUEST_URI']))  {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
	if (isset($_SERVER['QUERY_STRING'])) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

if (version_compare(PHP_VERSION, '5.6.0', '<')) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'site-php-version.php');
}

// Some browsers do not send a user-agent string
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
	$_SERVER['HTTP_USER_AGENT'] = '';
}

// Common functions
require KT_ROOT . 'includes/functions/functions.php';
require KT_ROOT . 'includes/functions/functions_db.php';
// TODO: Not all pages require all of these.  Only load them in scripts that need them?
require KT_ROOT . 'includes/functions/functions_print.php';
require KT_ROOT . 'includes/functions/functions_rtl.php';
require KT_ROOT . 'includes/functions/functions_mediadb.php';
require KT_ROOT . 'includes/functions/functions_date.php';
require KT_ROOT . 'includes/functions/functions_charts.php';
require KT_ROOT . 'includes/functions/functions_utf-8.php';

set_error_handler('kt_error_handler');

// Load our configuration file, so we can connect to the database
if (file_exists(KT_ROOT . 'data/config.ini.php')) {
	$dbconfig = parse_ini_file(KT_ROOT . 'data/config.ini.php');
	// Invalid/unreadable config file?
	if (!is_array($dbconfig)) {
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'site-unavailable.php');
		exit;
	}
	// Down for maintenance?
	if (file_exists(KT_ROOT . 'data/offline.txt')) {
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'site-offline.php');
		exit;
	}
} else {
	// No config file. Set one up.
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'setup.php');
	exit;
}

$KT_REQUEST = new Zend_Controller_Request_Http();

require KT_ROOT . 'includes/authentication.php';

// Connect to the database
try {
	KT_DB::createInstance($dbconfig['dbhost'], $dbconfig['dbport'], $dbconfig['dbname'], $dbconfig['dbuser'], $dbconfig['dbpass']);
	define('KT_TBLPREFIX', $dbconfig['tblpfx']);
	unset($dbconfig);
	// Some of the FAMILY JOIN HUSBAND JOIN WIFE queries can excede the MAX_JOIN_SIZE setting
	KT_DB::exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci', SQL_BIG_SELECTS=1");
	try {
		KT_DB::updateSchema(KT_ROOT . 'includes/db_schema/', 'KT_SCHEMA_VERSION', KT_SCHEMA_VERSION);
	} catch (PDOException $ex) {
		// The schema update scripts should never fail.  If they do, there is no clean recovery.
		die($ex);
	}
} catch (PDOException $ex) {
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . 'site-unavailable.php');
	exit;
}

// The config.ini.php file must always be in a fixed location.
// Other user files can be stored elsewhere...
define('KT_DATA_DIR', realpath(KT_Site::preference('INDEX_DIRECTORY') ? KT_Site::preference('INDEX_DIRECTORY') : 'data').DIRECTORY_SEPARATOR);

// If we have a preferred URL (e.g. www.example.com instead of www.isp.com/~example), then redirect to it.
$SERVER_URL = KT_Site::preference('SERVER_URL');
if ($SERVER_URL && $SERVER_URL != KT_SERVER_NAME . KT_SCRIPT_PATH) {
	header('Location: ' . $SERVER_URL . KT_SCRIPT_NAME . ($QUERY_STRING ? '?' . $QUERY_STRING : ''), true, 301);
	exit;
}

// Request more resources - if we can/want to
if (!ini_get('safe_mode')) {
	$memory_limit = KT_Site::preference('MEMORY_LIMIT');
	if ($memory_limit && strpos(ini_get('disable_functions'), 'ini_set') === false) {
		ini_set('memory_limit', $memory_limit);
	}
	$max_execution_time = KT_Site::preference('MAX_EXECUTION_TIME');
	if ($max_execution_time && strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
		set_time_limit($max_execution_time);
	}
}

$rule = KT_DB::prepare(
	"SELECT SQL_CACHE rule FROM `##site_access_rule`" .
	" WHERE IFNULL(INET_ATON(?), 0) BETWEEN ip_address_start AND ip_address_end" .
	" AND ? LIKE user_agent_pattern" .
	" ORDER BY ip_address_end LIMIT 1"
)->execute(array($KT_REQUEST->getClientIp(), $_SERVER['HTTP_USER_AGENT']))->fetchOne();

switch ($rule) {
case 'allow':
	$SEARCH_SPIDER = false;
	break;
case 'deny':
	header('HTTP/1.1 403 Access Denied');
	exit;
case 'robot':
case 'unknown':
	// Search engines don’t send cookies, and so create a new session with every visit.
	// Make sure they always use the same one
	Zend_Session::setId('search-engine-'.str_replace('.', '-', $KT_REQUEST->getClientIp()));
	$SEARCH_SPIDER = true;
	break;
case '':
	KT_DB::prepare(
		"INSERT INTO `##site_access_rule` (ip_address_start, ip_address_end, user_agent_pattern, comment) VALUES (IFNULL(INET_ATON(?), 0), IFNULL(INET_ATON(?), 4294967295), ?, '')"
	)->execute(array($KT_REQUEST->getClientIp(), $KT_REQUEST->getClientIp(), $_SERVER['HTTP_USER_AGENT']));
	$SEARCH_SPIDER = true;
	break;
}

// Store our session data in the database.
// Only update the session table once per minute, unless the session data has actually changed.
// Store our session data in the database.
session_set_save_handler(
	// open
	function () {
		return true;
	},
	// close
	function () {
		return true;
	},
	// read
	function ($id) {
		return (string) KT_DB::prepare("SELECT session_data FROM `##session` WHERE session_id=?")->execute(array($id))->fetchOne();
	},
	// write
	function ($id, $data) {
		global $KT_REQUEST;
		// Only update the session table once per minute, unless the session data has actually changed.
		KT_DB::prepare(
			"INSERT INTO `##session` (session_id, user_id, ip_address, session_data, session_time)" .
			" VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP - SECOND(CURRENT_TIMESTAMP))" .
			" ON DUPLICATE KEY UPDATE" .
			" user_id      = VALUES(user_id)," .
			" ip_address   = VALUES(ip_address)," .
			" session_data = VALUES(session_data)," .
			" session_time = CURRENT_TIMESTAMP - SECOND(CURRENT_TIMESTAMP)"
		)->execute(array($id, KT_USER_ID, $KT_REQUEST->getClientIp(), $data));

		return true;
	},
	// destroy
	function ($id) {
		KT_DB::prepare("DELETE FROM `##session` WHERE session_id = ?")->execute(array($id));

		return true;
	},
	// gc
	function ($maxlifetime) {
		KT_DB::prepare("DELETE FROM `##session` WHERE session_time < DATE_SUB(NOW(), INTERVAL ? SECOND)")->execute(array($maxlifetime));

		return true;
	}
);

// Use the Zend_Session object to start the session.
// This allows all the other Zend Framework components to integrate with the session
define('KT_SESSION_NAME', 'KT_SESSION');
$cfg = array(
	'name'            => KT_SESSION_NAME,
	'cookie_lifetime' => 0,
	'gc_maxlifetime'  => KT_Site::preference('SESSION_TIME'),
	'gc_probability'  => 1,
	'gc_divisor'      => 100,
	'cookie_path'     => KT_SCRIPT_PATH,
	'cookie_httponly' => true,
);

// Search engines don’t send cookies, and so create a new session with every visit.
// Make sure they always use the same one
if ($SEARCH_SPIDER) {
	Zend_Session::setId('search-engine-'.str_replace('.', '-', $KT_REQUEST->getClientIp()));
}

Zend_Session::start($cfg);

// Register a session “namespace” to store session data.  This is better than
// using $_SESSION, as we can avoid clashes with other modules or applications,
// and problems with servers that have enabled “register_globals”.
$KT_SESSION = new Zend_Session_Namespace('KIWITREES');

if (!$SEARCH_SPIDER && !$KT_SESSION->initiated) {
	// A new session, so prevent session fixation attacks by choosing a new PHPSESSID.
	Zend_Session::regenerateId();
	$KT_SESSION->initiated = true;
} else {
	// An existing session
}

// Who are we?
define('KT_USER_ID',       getUserId());
define('KT_USER_NAME',     getUserName());
define('KT_USER_IS_ADMIN', userIsAdmin(KT_USER_ID));

// Set the active GEDCOM
if (isset($_REQUEST['ged'])) {
	// .... from the URL or form action
	$GEDCOM = $_REQUEST['ged'];
} elseif ($KT_SESSION->GEDCOM) {
	// .... the most recently used one
	$GEDCOM = $KT_SESSION->GEDCOM;
} else {
	// Try the site default
	$GEDCOM = KT_Site::preference('DEFAULT_GEDCOM');
}

// Choose the selected tree (if it exists), or any valid tree otherwise
$KT_TREE = null;
foreach (KT_Tree::getAll() as $tree) {
	$KT_TREE = $tree;
	if ($KT_TREE->tree_name == $GEDCOM && ($KT_TREE->imported || KT_USER_IS_ADMIN)) {
		break;
	}
}

// These attributes of the currently-selected tree are used frequently
if ($KT_TREE) {
	define('KT_GEDCOM',            $KT_TREE->tree_name);
	define('KT_GED_ID',            $KT_TREE->tree_id);
	define('KT_GEDURL',            $KT_TREE->tree_name_url);
	define('KT_TREE_TITLE',        $KT_TREE->tree_title_html);
	define('KT_TREE_SUBTITLE',     $KT_TREE->tree_subtitle_html);
	define('KT_IMPORTED',          $KT_TREE->imported);
	define('KT_USER_GEDCOM_ADMIN', KT_USER_IS_ADMIN     || userGedcomAdmin(KT_USER_ID, KT_GED_ID));
	define('KT_USER_CAN_ACCEPT',   $KT_TREE->canAcceptChanges(KT_USER_ID));
	define('KT_USER_CAN_EDIT',     KT_USER_CAN_ACCEPT   || userCanEdit    (KT_USER_ID, KT_GED_ID));
	define('KT_USER_CAN_ACCESS',   KT_USER_CAN_EDIT     || userCanAccess  (KT_USER_ID, KT_GED_ID));
	define('KT_USER_GEDCOM_ID',    $KT_TREE->userPreference(KT_USER_ID, 'gedcomid'));
	define('KT_USER_ROOT_ID',      $KT_TREE->userPreference(KT_USER_ID, 'rootid') ? $KT_TREE->userPreference(KT_USER_ID, 'rootid') : KT_USER_GEDCOM_ID);
	define('KT_USER_PATH_LENGTH',  $KT_TREE->userPreference(KT_USER_ID, 'RELATIONSHIP_PATH_LENGTH'));
	if (KT_USER_GEDCOM_ADMIN) {
		define('KT_USER_ACCESS_LEVEL', KT_PRIV_NONE);
	} elseif (KT_USER_CAN_ACCESS) {
		define('KT_USER_ACCESS_LEVEL', KT_PRIV_USER);
	} else {
		define('KT_USER_ACCESS_LEVEL', KT_PRIV_PUBLIC);
	}
	load_gedcom_settings(KT_GED_ID);
} else {
	define('KT_GEDCOM',            '');
	define('KT_GED_ID',            null);
	define('KT_GEDURL',            '');
	define('KT_TREE_TITLE',        KT_KIWITREES);
	define('KT_TREE_SUBTITLE',     '');
	define('KT_IMPORTED',          false);
	define('KT_USER_GEDCOM_ADMIN', false);
	define('KT_USER_CAN_ACCEPT',   false);
	define('KT_USER_CAN_EDIT',     false);
	define('KT_USER_CAN_ACCESS',   false);
	define('KT_USER_GEDCOM_ID',    '');
	define('KT_USER_ROOT_ID',      '');
	define('KT_USER_PATH_LENGTH',  0);
	define('KT_USER_ACCESS_LEVEL', KT_PRIV_PUBLIC);
}

$GEDCOM = KT_GEDCOM;

// With no parameters, init() looks to the environment to choose a language
define('KT_LOCALE', KT_I18N::init());
$KT_SESSION->locale = KT_I18N::$locale;

// Non-latin languages may need non-latin digits
define('KT_NUMBERING_SYSTEM', Zend_Locale_Data::getContent(KT_LOCALE, 'defaultnumberingsystem'));

// Set our gedcom selection as a default for the next page
$KT_SESSION->GEDCOM = KT_GEDCOM;

if (empty($KIWITREES_EMAIL)) {
	$KIWITREES_EMAIL = 'kiwitrees-noreply@'.preg_replace('/^www\./i', '', $_SERVER['SERVER_NAME']);
}

// Note that the database/webservers may not be synchronised, so use DB time throughout.
define('KT_TIMESTAMP', (int)KT_DB::prepare("SELECT UNIX_TIMESTAMP()")->fetchOne());

// Server timezone is defined in php.ini
define('KT_SERVER_TIMESTAMP', KT_TIMESTAMP + (int)date('Z'));

if (KT_USER_ID) {
	define('KT_CLIENT_TIMESTAMP', KT_TIMESTAMP - $KT_SESSION->timediff);
} else {
	define('KT_CLIENT_TIMESTAMP', KT_SERVER_TIMESTAMP);
}

define('KT_CLIENT_JD', 2440588 + (int)(KT_CLIENT_TIMESTAMP/86400));

// Application configuration data - things that aren’t (yet?) user-editable
require KT_ROOT . 'includes/config_data.php';

//-- load the privacy functions
require KT_ROOT . 'includes/functions/functions_privacy.php';

// If we are logged in, and logout=1 has been added to the URL, log out
// If we were logged in, but our account has been deleted, log out.
if (KT_USER_ID && (safe_GET_bool('logout') || !KT_USER_NAME)) {
	userLogout(KT_USER_ID);
	header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH);
	exit;
}

// The login URL must be an absolute URL, and can be user-defined
if (KT_Site::preference('LOGIN_URL')) {
	define('KT_LOGIN_URL', KT_Site::preference('LOGIN_URL'));
} else {
	define('KT_LOGIN_URL', KT_SERVER_NAME . KT_SCRIPT_PATH . 'login.php');
}

// If there is no current tree and we need one, then redirect somewhere
if (KT_SCRIPT_NAME != 'admin_trees_manage.php' && KT_SCRIPT_NAME != 'login.php' && KT_SCRIPT_NAME != 'import.php' && KT_SCRIPT_NAME != 'help_text.php' && KT_SCRIPT_NAME != 'message.php') {
	if (!$KT_TREE || !KT_IMPORTED) {
		if (KT_USER_IS_ADMIN) {
			header('Location: '. KT_SERVER_NAME . KT_SCRIPT_PATH . 'admin_trees_manage.php');
		} else {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(KT_SCRIPT_NAME . '?' . $QUERY_STRING));
		}
		exit;
	}
}

if (KT_USER_ID) {
	//-- update the login time once per minute
	if (KT_TIMESTAMP-$KT_SESSION->activity_time >= 60) {
		set_user_setting(KT_USER_ID, 'sessiontime', KT_TIMESTAMP);
		$KT_SESSION->activity_time = KT_TIMESTAMP;
	}
}

// Set the theme
if (substr(KT_SCRIPT_NAME, 0, 5) == 'admin' || KT_SCRIPT_NAME == 'module.php' && substr(safe_GET('mod_action'), 0, 5) == 'admin') {
	// Administration scripts begin with “admin” and use a special administration theme
	define('KT_THEME_DIR', KT_THEMES_DIR.'_administration/');
} else {
	// Requested change of theme?
	$THEME_DIR = safe_GET('theme', get_theme_names());
	unset($_GET['theme']);
	if (!$THEME_DIR) {
		// 1) gedcom setting
		// 3) kiwitrees
		// 4) first one found
		if (KT_GED_ID) {
			$THEME_DIR = get_gedcom_setting(KT_GED_ID, 'THEME_DIR');
		}
		if (!in_array($THEME_DIR, get_theme_names())) {
			$THEME_DIR = 'kiwitrees';
		}
		if (!in_array($THEME_DIR, get_theme_names())) {
			list($THEME_DIR) = get_theme_names();
		}
	}
	define('KT_THEME_DIR', KT_THEMES_DIR . $THEME_DIR.'/');
}

// If we have specified a CDN, use it for static theme resources
define('KT_THEME_URL', KT_STATIC_URL . KT_THEME_DIR);

require KT_ROOT . KT_THEME_DIR . 'theme.php';

// Page hit counter - load after theme, as we need theme formatting
if ($KT_TREE && $KT_TREE->preference('SHOW_COUNTER') && !$SEARCH_SPIDER) {
	require KT_ROOT . 'includes/hitcount.php';
} else {
	$hitCount = '';
}

// define constants to be used when setting permissions after creating files/directories
if (substr(PHP_SAPI, 0, 3) == 'cgi') {  // cgi-mode, should only be writable by owner
	define('KT_PERM_EXE',  0755);  // to be used on directories, php files, etc.
	define('KT_PERM_FILE', 0644);  // to be used on images, text files, etc.
} else { // mod_php mode, should be writable by everyone
	define('KT_PERM_EXE',  0777);
	define('KT_PERM_FILE', 0666);
}

// Search engines are only allowed to see certain pages.
if ($SEARCH_SPIDER && !in_array(KT_SCRIPT_NAME , array(
	'index.php', 'indilist.php', 'module.php', 'mediafirewall.php',
	'individual.php', 'family.php', 'mediaviewer.php', 'note.php', 'repo.php', 'source.php',
))) {
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	$controller = new KT_Controller_Page();
	$controller->setPageTitle(KT_I18N::translate('Search engine'));
	$controller->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('You do not have permission to view this page.'), '</p>';
	exit;
}
