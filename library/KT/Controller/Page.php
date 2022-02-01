<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class KT_Controller_Page extends KT_Controller_Base {
	// Page header information
	const     DOCTYPE       = '<!DOCTYPE html>';  // HTML5
	private   $canonical_url= '';
	private   $meta_robots  = 'noindex,nofollow'; // Most pages are not intended for robots
	private   $page_title   = KT_KIWITREES;        // <head><title> $page_title </title></head>

	// Startup activity
	public function __construct() {
		parent::__construct();
		// Every page uses these scripts
		$this
			->addExternalJavascript(KT_JQUERY_URL)
			->addExternalJavascript(KT_JQUERYUI_URL)
			->addExternalJavascript(KT_JQUERY_SHORTEN)
			->addExternalJavascript(KT_KIWITREES_JS_URL);
	}

	// Shutdown activity
	public function __destruct() {
		// If we printed a header, automatically print a footer
		if ($this->page_header) {
			$this->pageFooter();
		}
	}

	// What should this page show in the browser's title bar?
	public function setPageTitle($page_title) {
		$this->page_title = $page_title;
		return $this;
	}
	// Some pages will want to display this as <h2> $page_title </h2>
	public function getPageTitle() {
		return $this->page_title;
	}

	// What is the preferred URL for this page?
	public function setCanonicalUrl($canonical_url) {
		$this->canonical_url = $canonical_url;
		return $this;
	}

	// Should robots index this page?
	public function setMetaRobots($meta_robots) {
		$this->meta_robots = $meta_robots;
		return $this;
	}

	/**
	 * Restrict access
	 *
	 * @param bool $condition
	 *
	 * @return $this
	 */
	public function restrictAccess($condition) {
		require_once KT_ROOT . 'includes/functions/functions.php'; // for get_query_url
		if ($condition === false) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireAdminLogin() {
		require_once KT_ROOT.'includes/functions/functions.php'; // for get_query_url
		if (!KT_USER_IS_ADMIN) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireManagerLogin($ged_id=KT_GED_ID) {
		require_once KT_ROOT.  'includes/functions/functions.php'; // for get_query_url
		if (
			$ged_id == KT_GED_ID && !KT_USER_GEDCOM_ADMIN ||
			$ged_id != KT_GED_ID && userGedcomAdmin(KT_USER_ID, $gedcom_id)
		) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireAcceptLogin() {
		require_once KT_ROOT . 'includes/functions/functions.php'; // for get_query_url
		if (!KT_USER_CAN_ACCEPT) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireEditorLogin() {
		require_once KT_ROOT . 'includes/functions/functions.php'; // for get_query_url
		if (!KT_USER_CAN_EDIT) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Restrict access
	public function requireMemberLogin() {
		require_once KT_ROOT . 'includes/functions/functions.php'; // for get_query_url
		if (!KT_USER_ID) {
			header('Location: ' . KT_LOGIN_URL . '?url=' . rawurlencode(get_query_url()));
			exit;
		}
		return $this;
	}

	// Print the page header, using the theme
	public function pageHeader($maintenance=false) {
		// Import global variables into the local scope, for the theme's header.php
		global $SEARCH_SPIDER, $TEXT_DIRECTION, $headerfile, $view;
		// Maintenance mode
		// Note: $maintenance is 'true' on login.php so admin can always log in.
		if (KT_Site::preference('MAINTENANCE') == 1 && !KT_USER_IS_ADMIN && $maintenance != true) {
			header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH .'site-maintenance.php');
			exit;
		}

		// The title often includes the names of records, which may have markup
		// that cannot be used in the page title.
		$title = html_entity_decode(strip_tags($this->page_title), ENT_QUOTES, 'UTF-8');

		// Initialise variables for the theme's header.php
		$LINK_CANONICAL		= $this->canonical_url;
		$META_ROBOTS		= $this->meta_robots;
		$META_DESCRIPTION	= KT_GED_ID ? get_gedcom_setting(KT_GED_ID, 'META_DESCRIPTION') : '';
		if (!$META_DESCRIPTION) {
			$META_DESCRIPTION = strip_tags(KT_TREE_TITLE);
		}
		$META_GENERATOR		= KT_KIWITREES . '-' . KT_VERSION_TEXT . ' - ' . KT_KIWITREES_URL;
		$META_TITLE			= KT_GED_ID ? get_gedcom_setting(KT_GED_ID, 'META_TITLE') : '';
		if ($META_TITLE) {
			$title .= ' - ' . $META_TITLE;
		}

		// This javascript needs to be loaded in the header, *before* the CSS.
		// All other javascript should be defered until the end of the page
		$javascript= '<script src="' . KT_MODERNIZR_URL . '"></script>';

		// Give Javascript access to some PHP constants
		$this->addInlineJavascript('
			var KT_STATIC_URL  = "' . KT_Filter::escapeJs(KT_STATIC_URL)             . '";
			var KT_THEME_DIR   = "' . KT_Filter::escapeJs(KT_THEME_DIR)              . '";
			var KT_MODULES_DIR = "' . KT_Filter::escapeJs(KT_MODULES_DIR)            . '";
			var KT_GEDCOM      = "' . KT_Filter::escapeJs(KT_GEDCOM)                 . '";
			var KT_GED_ID      = "' . KT_Filter::escapeJs(KT_GED_ID)                 . '";
			var KT_USER_ID     = "' . KT_Filter::escapeJs(KT_USER_ID)                . '";
			var textDirection  = "' . KT_Filter::escapeJs($TEXT_DIRECTION)           . '";
			var KT_SCRIPT_NAME = "' . KT_Filter::escapeJs(KT_SCRIPT_NAME)            . '";
			var KT_LOCALE      = "' . KT_Filter::escapeJs(KT_LOCALE)                 . '";
			var accesstime     = "' . KT_Filter::escapeJs(KT_TIMESTAMP)              . '";
			var KT_CSRF_TOKEN  = "' . KT_Filter::escapeJs(KT_Filter::getCsrfToken()) . '";
		', self::JS_PRIORITY_HIGH);

		// Temporary fix for access to main menu hover elements on android/blackberry touch devices
		$this->addInlineJavascript('
			if(navigator.userAgent.match(/Android|PlayBook/i)) {
				jQuery("#main-menu > li > a").attr("href", "#");
				jQuery("a.icon_arrow").attr("href", "#");
			}
		');

		// Common help_content shortening script
		$this->addInlineJavascript('
			jQuery(".helpcontent").shorten({
			    showChars: 300,
				moreText: "' . KT_I18N::translate('More') . '",
				lessText: "' . KT_I18N::translate('Less') . '"
			});
		');

		header('Content-Type: text/html; charset=UTF-8');
		require KT_ROOT . $headerfile;

		// Flush the output, so the browser can render the header and load javascript
		// while we are preparing data for the page
		if (ini_get('output_buffering')) {
			ob_flush();
		}
		flush();

		// Once we've displayed the header, we should no longer write session data.
		Zend_Session::writeClose();

		// We've displayed the header - display the footer automatically
		$this->page_header = true;
		return $this;
	}

	// Print the page footer, using the theme
	protected function pageFooter() {
		global $footerfile, $view, $start_time, $PRIVACY_CHECKS;

		(KT_GED_ID ? require KT_ROOT . $footerfile : '');
		(KT_DEBUG_SQL ? KT_DB::getQueryLog() : '');
		echo
			$this->getJavascript() .
			'</body>
			</html>' . PHP_EOL .
			'<!-- Kiwitrees: ' . KT_VERSION_TEXT . ' - ' .
			KT_I18N::translate(
				'Execution time: %1$s seconds. Database queries: %2$s. Privacy checks: %3$s. Memory usage: %4$s KB.',
				KT_I18N::number(microtime(true)-$start_time, 3),
				KT_I18N::number(KT_DB::getQueryCount()),
				KT_I18N::number($PRIVACY_CHECKS),
				KT_I18N::number(memory_get_peak_usage(true)/1024)) .
			' -->';

		return $this;
	}

	// Get significant information from this page, to allow other pages such as
	// charts and reports to initialise with the same records
	public function getSignificantIndividual() {
		static $individual; // Only query the DB once.

		if (!$individual && KT_USER_ROOT_ID) {
			$individual = KT_Person::getInstance(KT_USER_ROOT_ID);
		}
		if (!$individual && KT_USER_GEDCOM_ID) {
			$individual = KT_Person::getInstance(KT_USER_GEDCOM_ID);
		}
		if (!$individual) {
			$individual = KT_Person::getInstance(get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID'));
		}
		if (!$individual) {
			$individual = KT_Person::getInstance(
				KT_DB::prepare(
					"SELECT MIN(i_id) FROM `##individuals` WHERE i_file=?"
				)->execute(array(KT_GED_ID))->fetchOne()
			);
		}
		if (!$individual) {
			// always return a record
			$individual = new KT_Person('0 @I@ INDI');
		}
		return $individual;
	}
	public function getSignificantFamily() {
		$individual = $this->getSignificantIndividual();
		if ($individual) {
			foreach ($individual->getChildFamilies() as $family) {
				return $family;
			}
			foreach ($individual->getSpouseFamilies() as $family) {
				return $family;
			}
		}
		// always return a record
		return new KT_Family('0 @F@ FAM');
	}
	public function getSignificantSurname() {
		return '';
	}
}
