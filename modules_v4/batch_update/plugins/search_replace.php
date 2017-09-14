<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class search_replace_bu_plugin extends base_plugin {
	var $search  = null; // Search string
	var $replace = null; // Replace string
	var $method  = null; // simple/wildcards/regex
	var $regex   = null; // Search string, converted to a regex
	var $case    = null; // "i" for case insensitive, "" for case sensitive
	var $error   = null; // Message for bad user parameters

	static function getName() {
		return KT_I18N::translate('Search and replace');
	}

	static function getDescription() {
		return /* I18N: Description of the “Search and replace” option of the batch update module */ KT_I18N::translate('Search and replace text, using simple searches or advanced pattern matching.');
	}

	// Default is to operate on INDI records
	function getRecordTypesToUpdate() {
		return array('INDI', 'FAM', 'SOUR', 'REPO', 'NOTE', 'OBJE');
	}

	function doesRecordNeedUpdate($xref, $gedrec) {
		return !$this->error && preg_match('/(?:'.$this->regex.')/mu'.$this->case, $gedrec);
	}

	function updateRecord($xref, $gedrec) {
		// Allow "\n" to indicate a line-feed in replacement text.
		// Back-references such as $1, $2 are handled automatically.
		return preg_replace('/'.$this->regex.'/mu'.$this->case, str_replace('\n', "\n", $this->replace), $gedrec);
	}

	function getOptions() {
		parent::getOptions();
		$this->search  = safe_GET('search', KT_REGEX_UNSAFE);
		$this->replace = safe_GET('replace', KT_REGEX_UNSAFE);
		$this->method  = safe_GET('method', array('exact', 'words', 'wildcards', 'regex'), 'exact');
		$this->case    = safe_GET('case', 'i');

		$this->error='';
		switch ($this->method) {
		case 'exact':
			$this->regex = preg_quote($this->search, '/');
			break;
		case 'words':
			$this->regex = '\b'.preg_quote($this->search, '/').'\b';
			break;
		case 'wildcards':
			$this->regex = '\b'.str_replace(array('\*', '\?'), array('.*', '.'), preg_quote($this->search, '/')).'\b';
			break;
		case 'regex':
			$this->regex=$this->search;
			// Check for invalid regular expressions.
			// A valid regex on a null string returns zero.
			// An invalid regex on a null string returns false (and throws a warning).
			if (@preg_match('/'.$this->search.'/', null) === false) {
				$this->error = '<br><span class="error">'.KT_I18N::translate('The regex appears to contain an error.  It can’t be used.').'</span>';
			}
			break;
		}
	}

	function getOptionsForm() {
		$descriptions = array(
			'exact'		=> KT_I18N::translate('Match the exact text, even if it occurs in the middle of a word.'),
			'words'		=> KT_I18N::translate('Match the exact text, unless it occurs in the middle of a word.'),
			'wildcards'	=> KT_I18N::translate('Use a &laquo;?&raquo; to match a single character, use &laquo;*&raquo; to match zero or more characters.'),
			'regex'		=> KT_I18N::translate('Regular expressions are an advanced pattern matching technique.  See <a href="http://php.net/manual/en/regexp.reference.php" target="_new">php.net/manual/en/regexp.reference.php</a> for futher details.'),
		);

		return
			'<label>
				<span>' . KT_I18N::translate('Search method') . '</span>
				<select name="method" onchange="this.form.submit();">
					<option value="exact"'    .($this->method=='exact'     ? ' selected="selected"' : '').'>'.KT_I18N::translate('Exact text')    .'</option>
					<option value="words"'    .($this->method=='words'     ? ' selected="selected"' : '').'>'.KT_I18N::translate('Whole words only')    .'</option>
					<option value="wildcards"'.($this->method=='wildcards' ? ' selected="selected"' : '').'>'.KT_I18N::translate('Wildcards').'</option>
					<option value="regex"'    .($this->method=='regex'     ? ' selected="selected"' : '').'>'.KT_I18N::translate('Regular expression')    .'</option>
				</select>
				<p><em>' . $descriptions[$this->method] . '</em>' . $this->error . '</p>
			</label>
			<label>
				<span>' . KT_I18N::translate('Case insensitive') . '</span>
				<input type="checkbox" name="case" value="i" ' . ($this->case=='i' ? 'checked="checked"' : '') . '" onchange="this.form.submit();">
				<p><em>' . KT_I18N::translate('Tick this box to match both upper and lower case letters.') . '</em></p>
			</label>' .
			parent::getOptionsForm() . '
			<hr>
			<label>
				<span>' . KT_I18N::translate('Search text/pattern') . '</span>
				<input id="search" name="search" value="' . KT_Filter::escapeHtml($this->search) . '" onchange="this.form.submit();">' . print_specialchar_link('search') . '
			</label>
			<label>
				<span>' . KT_I18N::translate('Replacement text') . '</span>
				<input id="replace" name="replace" value="' . KT_Filter::escapeHtml($this->replace) . '" onchange="this.form.submit();">' . print_specialchar_link('replace') . '
			</label>';
	}
}
