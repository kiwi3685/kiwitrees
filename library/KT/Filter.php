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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

#[AllowDynamicProperties]
class KT_Filter {
	// REGEX to match a URL
	// Some versions of RFC3987 have an appendix B which gives the following regex
	// (([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?
	// This matches far too much while a “precise” regex is several pages long.
	// This is a compromise.
	const URL_REGEX='((https?|ftp]):)(//([^\s/?#<>]*))?([^\s?#<>]*)(\?([^\s#<>]*))?(#[^\s?#<>]+)?';


	//////////////////////////////////////////////////////////////////////////////
	// Escape a string for use in HTML
	//////////////////////////////////////////////////////////////////////////////
	public static function escapeHtml($string) {
		if (defined('ENT_SUBSTITUTE')) {
			// PHP5.4 allows us to substitute invalid UTF8 sequences
			return htmlspecialchars((string) $string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		} else {
			return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8');
		}
	}

	//////////////////////////////////////////////////////////////////////////////
	// Escape a string for use in a URL
	//////////////////////////////////////////////////////////////////////////////
	public static function escapeUrl($string) {
		return rawurlencode((string) $string);
	}

	//////////////////////////////////////////////////////////////////////////////
	// Escape a string for use in Javascript
	//////////////////////////////////////////////////////////////////////////////
	public static function escapeJs($string) {
		return preg_replace_callback('/[^A-Za-z0-9,. _]/Su', function($x) {
			if (strlen($x[0]) == 1) {
				return sprintf('\\x%02X', ord($x[0]));
			} elseif (function_exists('iconv')) {
				return sprintf('\\u%04s', strtoupper(bin2hex(iconv('UTF-8', 'UTF-16BE', $x[0]))));
			} elseif (function_exists('mb_convert_encoding')) {
				return sprintf('\\u%04s', strtoupper(bin2hex(mb_convert_encoding($x[0], 'UTF-16BE', 'UTF-8'))));
			} else {
				return $x[0];
			}
		}, $string);
	}

	//////////////////////////////////////////////////////////////////////////////
	// Escape a string for use in a SQL "LIKE" clause
	//////////////////////////////////////////////////////////////////////////////
	public static function escapeLike($string) {
		return strtr(
			$string,
			array(
				'\\' => '\\\\',
				'%'  => '\%',
				'_'  => '\_',
			)
		);
	}

	/**
	 * Unescape an HTML string, giving just the literal text
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function unescapeHtml($string) {
		return html_entity_decode(strip_tags($string), ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Format block-level text such as notes or transcripts, etc.
	 *
	 * @param string  $text
	 *
	 * @return string
	 */
	public static function formatText($text) {
		return '<div style="display: inline; white-space: pre-wrap;" dir="auto">' . self::expandUrls($text) . '</div>';
	}

	/**
	 * Escape a string for use in HTML, and additionally convert URLs to links.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	 public static function expandUrls($text) {
 		return preg_replace_callback(
 			'/' . addcslashes('(?!>)' . self::URL_REGEX . '(?!</a>)', '/') . '/i',
 			function ($m) {
 				return '<a href="' . $m[0] . '" target="_blank">' . $m[0] . '</a>';
 			},
 			self::escapeHtml($text)
 		);
 	}

	//////////////////////////////////////////////////////////////////////////////
	// Validate INPUT requests
	//////////////////////////////////////////////////////////////////////////////
	private static function _input($source, $variable, $regexp=null, $default=null) {
		if ($regexp) {
			return filter_input(
				$source,
				$variable,
				FILTER_VALIDATE_REGEXP,
				array(
					'options' => array(
						'regexp'  => '/^(' . $regexp . ')$/u',
						'default' => $default,
					),
				)
			);
		} else {
			$tmp = filter_input(
				$source,
				$variable,
				FILTER_CALLBACK,
				array(
					'options' => function($x) {
						return !function_exists('mb_convert_encoding') || mb_check_encoding($x, 'UTF-8') ? $x : false;
					},
				)
			);
			return ($tmp === null || $tmp === false) ? $default : $tmp;
		}
	}

	private static function _inputArray($source, $variable, $regexp = null, $default = null) {
		if ($regexp) {
			// PHP5.3 requires the $tmp variable
			$tmp = filter_input_array(
				$source,
				array(
					$variable => array(
						'flags'   => FILTER_REQUIRE_ARRAY,
						'filter'  => FILTER_VALIDATE_REGEXP,
						'options' => array(
							'regexp'  => '/^(' . $regexp . ')$/u',
							'default' => $default,
						),
					),
				)
			);

            return null !== $tmp && null !== $tmp[$variable] ? $tmp[$variable] : array();

		} else {
			// PHP5.3 requires the $tmp variable
			$tmp = filter_input_array(
				$source,
				array(
					$variable => array(
						'flags'   => FILTER_REQUIRE_ARRAY,
						'filter'  => FILTER_CALLBACK,
						'options' => function($x) {
							return !function_exists('mb_convert_encoding') || mb_check_encoding($x, 'UTF-8') ? $x : false;
						}
					),
				)
			);

            return null !== $tmp && null !== $tmp[$variable] ? $tmp[$variable] : array();

		}
	}

	//////////////////////////////////////////////////////////////////////////////
	// Validate GET requests
	//////////////////////////////////////////////////////////////////////////////
	public static function get($variable, $regexp=null, $default=null) {
		return self::_input(INPUT_GET, $variable, $regexp, $default);
	}

	public static function getArray($variable, $regexp=null, $default=null) {
		return self::_inputArray(INPUT_GET, $variable, $regexp, $default);
	}

	public static function getBool($variable) {
		return (bool)filter_input(INPUT_GET, $variable, FILTER_VALIDATE_BOOLEAN);
	}

	public static function getInteger($variable, $min=0, $max=PHP_INT_MAX, $default=0) {
		return filter_input(INPUT_GET, $variable, FILTER_VALIDATE_INT, array('options'=>array('min_range'=>$min, 'max_range'=>$max, 'default'=>$default)));
	}

	public static function getEmail($variable, $default=null) {
		return filter_input(INPUT_GET, $variable, FILTER_VALIDATE_EMAIL) ?: $default;
	}

	public static function getUrl($variable, $default=null) {
		return filter_input(INPUT_GET, $variable, FILTER_VALIDATE_URL) ?: $default;
	}

	//////////////////////////////////////////////////////////////////////////////
	// Validate POST requests
	//////////////////////////////////////////////////////////////////////////////
	public static function post($variable, $regexp=null, $default=null) {
		return self::_input(INPUT_POST, $variable, $regexp, $default);
	}

	public static function postArray($variable, $regexp=null, $default=null) {
		return self::_inputArray(INPUT_POST, $variable, $regexp, $default);
	}

	public static function postBool($variable) {
		return (bool)filter_input(INPUT_POST, $variable, FILTER_VALIDATE_BOOLEAN);
	}

	public static function postInteger($variable, $min=0, $max=PHP_INT_MAX, $default=0) {
		return filter_input(INPUT_POST, $variable, FILTER_VALIDATE_INT, array('options'=>array('min_range'=>$min, 'max_range'=>$max, 'default'=>$default)));
	}

	public static function postEmail($variable, $default=null) {
		return filter_input(INPUT_POST, $variable, FILTER_VALIDATE_EMAIL) ?: $default;
	}

	public static function postUrl($variable, $default=null) {
		return filter_input(INPUT_POST, $variable, FILTER_VALIDATE_URL) ?: $default;
	}

	//////////////////////////////////////////////////////////////////////////////
	// Validate COOKIE requests
	//////////////////////////////////////////////////////////////////////////////
	public static function cookie($variable, $regexp=null, $default=null) {
		return self::_input(INPUT_COOKIE, $variable, $regexp, $default);
	}

	/**
	 * Validate SERVER parameters
	 *
	 * @param string      $variable
	 * @param string|null $regexp
	 * @param string|null $default
	 *
	 * @return null|string
	 */
	public static function server($variable, $regexp = null, $default = null) {
		// On some servers, variables that are present in $_SERVER cannot be
		// found via filter_input(INPUT_SERVER). Instead, they are found via
		// filter_input(INPUT_ENV). Since we cannot rely on filter_input(),
		// we must use the superglobal directly.
		if (array_key_exists($variable, $_SERVER) && ($regexp === null || preg_match('/^(' . $regexp . ')$/', $_SERVER[$variable]))) {
			return $_SERVER[$variable];
		} else {
			return $default;
		}
	}

	//////////////////////////////////////////////////////////////////////////////	//////////////////////////////////////////////////////////////////////////////
	// Cross-Site Request Forgery tokens - ensure that the user is submitting
	// a form that was generated by the current session.
	//////////////////////////////////////////////////////////////////////////////
	public static function getCsrfToken() {
		global $KT_SESSION;

		if ($KT_SESSION->CSRF_TOKEN === null) {
			$charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcedfghijklmnopqrstuvwxyz0123456789';
			for ($n=0; $n<32; ++$n) {
				$KT_SESSION->CSRF_TOKEN .= substr($charset, mt_rand(0, 61), 1);
			}
		}
		return $KT_SESSION->CSRF_TOKEN;
	}

	// Generate an <input> element - to protect the current form from CSRF attacks.
	public static function getCsrf() {
		return '<input type="hidden" name="csrf" value="' . KT_Filter::getCsrfToken() . '">';
	}

	// Check that the POST request contains the CSRF token generated above.
	public static function checkCsrf() {
		if (KT_Filter::post('csrf') !== KT_Filter::getCsrfToken()) {
			// Oops.  Something is not quite right
			AddToLog('CSRF mismatch - session expired or malicious attack', 'auth');
			KT_FlashMessages::addMessage(KT_I18N::translate('This form has expired.  Try again.'));
			return false;
		}
		return true;
	}
}
