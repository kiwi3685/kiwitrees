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
class KT_Tree {
	// Tree attributes
	public $tree_id         	= null; // The "gedcom ID" number
	public $tree_name       	= null; // The "gedcom name" text
	public $tree_name_url   	= null;
	public $tree_name_html  	= null;
	public $tree_title      	= null; // The "gedcom title" text
	public $tree_title_html 	= null;
	public $tree_subtitle		= null;
	public $tree_subtitle_html	= null;
	public $imported        	= null;

	// List of all trees
	private static $trees   = null;

	// Tree settings
	private $preference			= null;    // _gedcom_setting table
	private $user_preference	= array(); // _user_gedcom_setting table

	// Create a tree object.  This is a private constructor - it can only
	// be called from KT_Tree::getAll() to ensure proper initialisation.
	private function __construct($tree_id, $tree_name, $tree_title, $tree_subtitle, $imported) {
		if (strpos((string) $tree_title, '%') === false) {
			// Allow users to translate tree titles.
			//$tree_title = KT_I18N::Translate($tree_title);
		}
		$this->tree_id        		= $tree_id;
		$this->tree_name      		= $tree_name;
		$this->tree_name_url  		= rawurlencode((string) $tree_name);
		$this->tree_name_html 		= htmlspecialchars((string) $tree_name);
		$this->tree_title     		= $tree_title;
		$this->tree_title_html		= '<span class="treetitle" dir="auto">' . htmlspecialchars((string) $tree_title) . '</span>';
		$this->tree_subtitle		= $tree_subtitle;
		$this->tree_subtitle_html	= '<span class="subtitle" dir="auto">' . htmlspecialchars((string) $tree_subtitle) . '</span>';
		$this->imported       		= $imported;
	}

	// Get and Set the tree's configuration settings
	public function preference($setting_name, $setting_value = null) {
		// There are lots of settings, and we need to fetch lots of them on every page
		// so it is quicker to fetch them all in one go.
		if ($this->preference === null) {
			$this->preference = KT_DB::prepare(
				"SELECT setting_name, setting_value FROM `##gedcom_setting` WHERE gedcom_id = ?"
			)->execute(array($this->tree_id))->fetchAssoc();
		}

		// If $setting_value is null, then GET the setting
		if ($setting_value === null) {
			// If parameter two is not specified, GET the setting
			if (!array_key_exists($setting_name, $this->preference)) {
				$this->preference[$setting_name] = null;
			}
			return $this->preference[$setting_name];
		} else {
			// If parameter two is specified, then SET the setting
			if ($this->preference($setting_name) != $setting_value) {
				$this->preference[$setting_name] = $setting_value;
				// Audit log of changes
				AddToLog('Gedcom setting "' . $setting_name . '" set to "' . $setting_value . '"', 'config');
			}
			KT_DB::prepare(
				"REPLACE INTO `##gedcom_setting` (gedcom_id, setting_name, setting_value) VALUES (?, ?, LEFT(?, 255))"
			)->execute(array($this->tree_id, $setting_name, $setting_value));
			return $this;
		}
	}

	// Get and Set the tree's configuration settings
	public function userPreference($user_id, $setting_name, $setting_value = null) {
		// There are lots of settings, and we need to fetch lots of them on every page
		// so it is quicker to fetch them all in one go.
		if (!array_key_exists($user_id, $this->user_preference)) {
			$this->user_preference[$user_id] = KT_DB::prepare(
				"SELECT setting_name, setting_value FROM `##user_gedcom_setting` WHERE user_id = ? AND gedcom_id = ?"
			)->execute(array($user_id, $this->tree_id))->fetchAssoc();
		}

		// If $setting_value is null, then GET the setting
		if ($setting_value === null) {
			// If parameter two is not specified, GET the setting
			if (!array_key_exists($setting_name, $this->user_preference[$user_id])) {
				$this->user_preference[$user_id][$setting_name]=null;
			}
			return $this->user_preference[$user_id][$setting_name];
		} else {
			// If parameter two is specified, then SET the setting.
			if ($this->preference($setting_name) != $setting_value) {
				// Audit log of changes
				AddToLog('Gedcom setting "' . $setting_name . '" set to "' . $setting_value . '"', 'config');
			}
			KT_DB::prepare(
				"REPLACE INTO `##user_gedcom_setting` (user_id, gedcom_id, setting_name, setting_value) VALUES (?, ?, ?, LEFT(?, 255))"
			)->execute(array($user_id, $this->tree_id, $setting_name, $setting_value));
			return $this;
		}
	}

	// Can a user accept changes for this tree?
	public function canAcceptChanges($user_id) {
		return
			userIsAdmin($user_id) ||
			$this->userPreference($user_id, 'canedit') == 'admin' ||
			$this->userPreference($user_id, 'canedit') == 'accept';
	}

	// Fetch all the trees that we have permission to access.
	public static function getAll() {
		if (self::$trees === null) {
			self::$trees=array();
			$rows = KT_DB::prepare(
				"SELECT g.gedcom_id AS tree_id, g.gedcom_name AS tree_name, gs1.setting_value AS tree_title, gs2.setting_value AS imported, gs3.setting_value AS tree_subtitle".
				" FROM `##gedcom` g".
				" LEFT JOIN `##gedcom_setting`      gs1 ON (g.gedcom_id=gs1.gedcom_id AND gs1.setting_name='title')".
				" LEFT JOIN `##gedcom_setting`      gs2 ON (g.gedcom_id=gs2.gedcom_id AND gs2.setting_name='imported')".
				" LEFT JOIN `##user_gedcom_setting` ugs ON (g.gedcom_id=ugs.gedcom_id AND ugs.setting_name='canedit' AND ugs.user_id = ?)".
				" LEFT JOIN `##gedcom_setting`      gs3 ON (g.gedcom_id=gs3.gedcom_id AND gs3.setting_name='subtitle')".
				" WHERE ".
				"  g.gedcom_id>0 AND (".          // exclude the "template" tree
				"    EXISTS (SELECT 1 FROM `##user_setting` WHERE user_id = ? AND setting_name='canadmin' AND setting_value=1)". // Admin sees all
				"   ) OR (".
				"    gs2.setting_value = 1 AND (".                // Allow imported trees, with either:
				"     gs3.setting_value <> 1 OR".                 // visitor access
				"     IFNULL(ugs.setting_value, 'none')<>'none'". // explicit access
				"   )".
				"  )".
				" ORDER BY g.sort_order, 3"
			)->execute(array(KT_USER_ID, KT_USER_ID))->fetchAll();
			foreach ($rows as $row) {
				self::$trees[$row->tree_id] = new KT_Tree($row->tree_id, $row->tree_name, $row->tree_title, $row->tree_subtitle, $row->imported);
			}
		}
		return self::$trees;
	}

	// Get the tree with a specific ID.  TODO - is this function needed long-term, or just while
	// we integrate this class into the rest of the code?
	public static function get($tree_id) {
		$trees=self::getAll();
		return $trees[$tree_id];
	}

	/**
	 * The ID of this tree
	 *
	 * @return int
	 */
	public function getTreeId() {
		return $this->tree_id;
	}

	// Create arguments to select_edit_control()
	// Note - these will be escaped later
	public static function getIdList() {
		$list=array();
		foreach (self::getAll() as $tree) {
			$list[$tree->tree_id]=$tree->tree_title;
		}
		return $list;
	}

	// Create arguments to select_edit_control()
	// Note - these will be escaped later
	public static function getNameList() {
		$list=array();
		foreach (self::getAll() as $tree) {
			$list[$tree->tree_name]=$tree->tree_title;
		}
		return $list;
	}

	/**
	 * Find the tree with a specific name.
	 *
	 * @param string $tree_name
	 *
	 * @return Tree|null
	 */
	public static function getIdFromName($tree_name) {
		foreach (self::getAll() as $tree) {
			if ($tree->tree_name === $tree_name) {
				return $tree;
			}
		}

		return null;
	}

	public static function getNameFromId($tree_id) {
		return self::get($tree_id)->tree_name;
	}

	// Create a new tree
	public static function create($tree_name, $tree_title) {
		try {
			// Create a new tree
			KT_DB::prepare(
				"INSERT INTO `##gedcom` (gedcom_name) VALUES (?)"
			)->execute(array($tree_name));
			$tree_id = KT_DB::prepare("SELECT LAST_INSERT_ID()")->fetchOne();
		} catch (PDOException $ex) {
			// A tree with that name already exists?
			return;
		}

		// Update the list of trees - to include this new one
		self::$trees=null;

		// Module privacy
		KT_Module::setDefaultAccess($tree_id);

		// Gedcom and privacy settings
		set_gedcom_setting($tree_id, 'ABBREVIATE_CHART_LABELS',      false);
		set_gedcom_setting($tree_id, 'ADVANCED_NAME_FACTS',          'NICK,_AKA');
		set_gedcom_setting($tree_id, 'ADVANCED_PLAC_FACTS',          '');
        set_gedcom_setting($tree_id, 'AUTOCOMPLETE_PLACES',          'Advanced');
		set_gedcom_setting($tree_id, 'CALENDAR_FORMAT',              'gregorian');
		set_gedcom_setting($tree_id, 'CHART_BOX_TAGS',               '');
		set_gedcom_setting($tree_id, 'COMMON_NAMES_ADD',             '');
		set_gedcom_setting($tree_id, 'COMMON_NAMES_REMOVE',          '');
		set_gedcom_setting($tree_id, 'COMMON_NAMES_THRESHOLD',       '40');
		set_gedcom_setting($tree_id, 'COMMON_TYPES_THRESHOLD',       '6');
		set_gedcom_setting($tree_id, 'CONTACT_USER_ID',              KT_USER_ID);
		set_gedcom_setting($tree_id, 'DEFAULT_PEDIGREE_GENERATIONS', '4');
		set_gedcom_setting($tree_id, 'EXPAND_NOTES',                 false);
		set_gedcom_setting($tree_id, 'EXPAND_SOURCES',               false);
		set_gedcom_setting($tree_id, 'FAM_FACTS_ADD',                'CENS,MARR,RESI,SLGS,MARR_CIVIL,MARR_RELIGIOUS,MARR_PARTNERS,RESN');
		set_gedcom_setting($tree_id, 'FAM_FACTS_QUICK',              'MARR,DIV,_NMR');
		set_gedcom_setting($tree_id, 'FAM_FACTS_UNIQUE',             'NCHI,MARL,DIV,ANUL,DIVF,ENGA,MARB,MARC,MARS,_NMR');
		set_gedcom_setting($tree_id, 'FAM_ID_PREFIX',                'F');
		set_gedcom_setting($tree_id, 'FULL_SOURCES',                 false);
		set_gedcom_setting($tree_id, 'GEDCOM_ID_PREFIX',             'I');
		set_gedcom_setting($tree_id, 'GEDCOM_MEDIA_PATH',            '');
		set_gedcom_setting($tree_id, 'GENERATE_UIDS',                false);
		set_gedcom_setting($tree_id, 'HIDE_GEDCOM_ERRORS',           true);
		set_gedcom_setting($tree_id, 'HIDE_LIVE_PEOPLE',             true);
		set_gedcom_setting($tree_id, 'IMAGE_EDITOR',				 'https://pixlr.com/x/');
		set_gedcom_setting($tree_id, 'INDI_FACTS_ADD',               'AFN,BIRT,DEAT,BURI,CREM,ADOP,BAPM,BARM,BASM,BLES,CHRA,CONF,FCOM,ORDN,NATU,EMIG,IMMI,CENS,PROB,WILL,GRAD,RETI,DSCR,EDUC,IDNO,NATI,NCHI,NMR,OCCU,PROP,RELI,RESI,SSN,TITL,BAPL,CONL,ENDL,SLGC,_MILI,ASSO,RESN');
		set_gedcom_setting($tree_id, 'INDI_FACTS_QUICK',             'BIRT,BURI,BAPM,CENS,DEAT,OCCU,RESI');
		set_gedcom_setting($tree_id, 'INDI_FACTS_UNIQUE',            '');
		set_gedcom_setting($tree_id, 'KEEP_ALIVE_YEARS_BIRTH',       '');
		set_gedcom_setting($tree_id, 'KEEP_ALIVE_YEARS_DEATH',       '');
		set_gedcom_setting($tree_id, 'KIWITREES_EMAIL',              '');
		set_gedcom_setting($tree_id, 'LANGUAGE',                     KT_LOCALE); // Defualt to the current admin's language`
		set_gedcom_setting($tree_id, 'MAX_ALIVE_AGE',                120);
		set_gedcom_setting($tree_id, 'MAX_DESCENDANCY_GENERATIONS',  '15');
		set_gedcom_setting($tree_id, 'MAX_PEDIGREE_GENERATIONS',     '10');
		set_gedcom_setting($tree_id, 'MEDIA_DIRECTORY',              'media/');
		set_gedcom_setting($tree_id, 'MEDIA_ID_PREFIX',              'M');
		set_gedcom_setting($tree_id, 'MEDIA_UPLOAD',                 KT_PRIV_USER);
		set_gedcom_setting($tree_id, 'META_DESCRIPTION',             '');
		set_gedcom_setting($tree_id, 'META_TITLE',                   KT_KIWITREES);
		set_gedcom_setting($tree_id, 'NOTE_FACTS_ADD',               'SOUR,RESN');
		set_gedcom_setting($tree_id, 'NOTE_FACTS_QUICK',             '');
		set_gedcom_setting($tree_id, 'NOTE_FACTS_UNIQUE',            '');
		set_gedcom_setting($tree_id, 'NOTE_ID_PREFIX',               'N');
		set_gedcom_setting($tree_id, 'NO_UPDATE_CHAN',               false);
		set_gedcom_setting($tree_id, 'PEDIGREE_FULL_DETAILS',        true);
		set_gedcom_setting($tree_id, 'PEDIGREE_LAYOUT',              true);
		set_gedcom_setting($tree_id, 'PEDIGREE_ROOT_ID',             '');
		set_gedcom_setting($tree_id, 'PEDIGREE_SHOW_GENDER',         false);
		set_gedcom_setting($tree_id, 'PREFER_LEVEL2_SOURCES',        '1');
		set_gedcom_setting($tree_id, 'QUICK_REQUIRED_FACTS',         'BIRT,DEAT');
		set_gedcom_setting($tree_id, 'QUICK_REQUIRED_FAMFACTS',      'MARR');
		set_gedcom_setting($tree_id, 'REPO_FACTS_ADD',               'PHON,EMAIL,FAX,WWW,NOTE,SHARED_NOTE,RESN');
		set_gedcom_setting($tree_id, 'REPO_FACTS_QUICK',             '');
		set_gedcom_setting($tree_id, 'REPO_FACTS_UNIQUE',            'NAME,ADDR');
		set_gedcom_setting($tree_id, 'REPO_ID_PREFIX',               'R');
		set_gedcom_setting($tree_id, 'SANITY_BAPTISM',				 5);
		set_gedcom_setting($tree_id, 'SANITY_OLDAGE',				 120);
		set_gedcom_setting($tree_id, 'SANITY_MARRIAGE',				 14);
		set_gedcom_setting($tree_id, 'SANITY_SPOUSE_AGE',			 30);
		set_gedcom_setting($tree_id, 'SANITY_CHILD_Y',				 15);
		set_gedcom_setting($tree_id, 'SANITY_CHILD_O',				 50);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_BD',		 1);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_BP',		 1);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_BS',		 1);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_DD',		 1);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_DP',		 1);
		set_gedcom_setting($tree_id, 'SANITY_INCOMPLETE_DS',		 1);
		set_gedcom_setting($tree_id, 'SAVE_WATERMARK_IMAGE',         false);
		set_gedcom_setting($tree_id, 'SAVE_WATERMARK_THUMB',         false);
		set_gedcom_setting($tree_id, 'SHOW_COUNTER',                 true);
		set_gedcom_setting($tree_id, 'SHOW_DEAD_PEOPLE',             KT_PRIV_PUBLIC);
		set_gedcom_setting($tree_id, 'SHOW_EST_LIST_DATES',          false);
		set_gedcom_setting($tree_id, 'SHOW_FACT_ICONS',              true);
		set_gedcom_setting($tree_id, 'SHOW_GEDCOM_RECORD',           false);
		set_gedcom_setting($tree_id, 'SHOW_HIGHLIGHT_IMAGES',        true);
		set_gedcom_setting($tree_id, 'SHOW_LDS_AT_GLANCE',           false);
		set_gedcom_setting($tree_id, 'SHOW_LIVING_NAMES',            KT_PRIV_USER);
		set_gedcom_setting($tree_id, 'SHOW_MEDIA_DOWNLOAD',          false);
		set_gedcom_setting($tree_id, 'SHOW_NO_WATERMARK',            KT_PRIV_USER);
		set_gedcom_setting($tree_id, 'SHOW_PARENTS_AGE',             true);
		set_gedcom_setting($tree_id, 'SHOW_PEDIGREE_PLACES',         '9');
		set_gedcom_setting($tree_id, 'SHOW_PEDIGREE_PLACES_SUFFIX',  false);
		set_gedcom_setting($tree_id, 'SHOW_PRIVATE_RELATIONSHIPS',   true);
		set_gedcom_setting($tree_id, 'SHOW_RELATIVES_EVENTS',        '_BIRT_CHIL,_BIRT_SIBL,_MARR_CHIL,_MARR_PARE,_DEAT_CHIL,_DEAT_PARE,_DEAT_GPAR,_DEAT_SIBL,_DEAT_SPOU');
		set_gedcom_setting($tree_id, 'SOURCE_ID_PREFIX',             'S');
		set_gedcom_setting($tree_id, 'SOUR_FACTS_ADD',               'NOTE,REPO,SHARED_NOTE,RESN');
		set_gedcom_setting($tree_id, 'SOUR_FACTS_QUICK',             'TEXT,NOTE,REPO');
		set_gedcom_setting($tree_id, 'SOUR_FACTS_UNIQUE',            'AUTH,ABBR,TITL,PUBL,TEXT');
		set_gedcom_setting($tree_id, 'SUBLIST_TRIGGER_I',            '200');
		set_gedcom_setting($tree_id, 'SURNAME_LIST_STYLE',           'style2');
		switch (KT_LOCALE) {
		case 'es':    set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'spanish');    break;
		case 'is':    set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'icelandic');  break;
		case 'lt':    set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'lithuanian'); break;
		case 'pl':    set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'polish');     break;
		case 'pt':
		case 'pt-BR': set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'portuguese'); break;
		default:      set_gedcom_setting($tree_id, 'SURNAME_TRADITION', 'paternal');   break;
		}
		set_gedcom_setting($tree_id, 'THEME_DIR',                    'kiwitrees');
		set_gedcom_setting($tree_id, 'THUMBNAIL_WIDTH',              '100');
		set_gedcom_setting($tree_id, 'USE_GEONAMES',                 false);
		set_gedcom_setting($tree_id, 'USE_RIN',                      false);
		set_gedcom_setting($tree_id, 'USE_SILHOUETTE',               true);
		set_gedcom_setting($tree_id, 'WATERMARK_THUMB',              false);
		set_gedcom_setting($tree_id, 'WEBMASTER_USER_ID',            KT_USER_ID);
		set_gedcom_setting($tree_id, 'WORD_WRAPPED_NOTES',           false);
		set_gedcom_setting($tree_id, 'imported',                     0);
		set_gedcom_setting($tree_id, 'title',                        $tree_title);
		set_gedcom_setting($tree_id, 'subtitle',                     '');
		if (file_exists(KT_Site::preference('INDEX_DIRECTORY').'histo.'.KT_LOCALE.'.php')) {
			set_gedcom_setting($tree_id, 'EXPAND_HISTO_EVENTS',      false);
		}


		// Default restriction settings
		$statement = KT_DB::prepare(
			"INSERT INTO `##default_resn` (gedcom_id, xref, tag_type, resn) VALUES (?, NULL, ?, ?)"
		);
		$statement->execute(array($tree_id, 'SSN',  'confidential'));
		$statement->execute(array($tree_id, 'SOUR', 'privacy'));
		$statement->execute(array($tree_id, 'REPO', 'privacy'));
		$statement->execute(array($tree_id, 'SUBM', 'confidential'));
		$statement->execute(array($tree_id, 'SUBN', 'confidential'));

		// Genealogy data
		// It is simpler to create a temporary/unimported GEDCOM than to populate all the tables...
		$john_doe	= /* I18N: This should be a common/default/placeholder name of an individual. Put slashes around the surname. */
			KT_I18N::translate('John /DOE/');
		$note		= KT_I18N::translate('Edit this individual and replace their details with your own');
		KT_DB::prepare("INSERT INTO `##gedcom_chunk` (gedcom_id, chunk_data) VALUES (?, ?)")->execute(array(
			$tree_id,
			"0 HEAD\n1 CHAR UTF-8\n0 @I1@ INDI\n1 NAME {$john_doe}\n1 SEX M\n1 BIRT\n2 DATE 01 JAN 1850\n2 NOTE {$note}\n0 TRLR\n"
		));

		// Set the initial blocks
		KT_DB::prepare(
			"INSERT INTO `##block` (gedcom_id, location, block_order, module_name)".
			" SELECT ?, location, block_order, module_name".
			" FROM `##block`".
			" WHERE gedcom_id=-1"
		)->execute(array($tree_id));

		// Update the list of trees - to include the new configuration settings
		self::$trees = null;
	}

	// Delete everything relating to a tree
	public static function delete($tree_id) {
		// If this is the default tree, then unset
		if (KT_Site::preference('DEFAULT_GEDCOM') == self::getNameFromId($tree_id)) {
			KT_Site::preference('DEFAULT_GEDCOM', '');
		}
		// Don't delete the logs.
		KT_DB::prepare("UPDATE `##log` SET gedcom_id = NULL   WHERE gedcom_id = ?")->execute(array($tree_id));

		KT_DB::prepare("DELETE `##block_setting` FROM `##block_setting` JOIN `##block` USING (block_id) WHERE gedcom_id = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##block`               WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##dates`               WHERE d_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##families`            WHERE f_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##user_gedcom_setting` WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##gedcom_setting`      WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##individuals`         WHERE i_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##link`                WHERE l_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##media`               WHERE m_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##module_privacy`      WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##name`                WHERE n_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##next_id`             WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##other`               WHERE o_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##placelinks`          WHERE pl_file    = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##places`              WHERE p_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##sources`             WHERE s_file     = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##hit_counter`         WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##change`              WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##default_resn`        WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##gedcom_chunk`        WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##log`                 WHERE gedcom_id  = ?")->execute(array($tree_id));
		KT_DB::prepare("DELETE FROM `##gedcom`              WHERE gedcom_id  = ?")->execute(array($tree_id));

		// After updating the database, we need to fetch a new (sorted) copy
		self::$trees = null;
	}

	//////////////////////////////////////////////////////////////////////////////
	//
	// Export the tree to a GEDCOM file
	//
	//////////////////////////////////////////////////////////////////////////////

	public function exportGedcom($gedcom_file) {

		// TODO: these functions need to be moved to the GedcomRecord(?) class
		require_once KT_ROOT.'includes/functions/functions_export.php';

		// To avoid partial trees on timeout/diskspace/etc, write to a temporary file first
		$tmp_file = $gedcom_file . '.tmp';

		$file_pointer = @fopen($tmp_file, 'w');
		if ($file_pointer === false) {
			return false;
		}

		$buffer = reformat_record_export(gedcom_header($this->tree_name));

		$stmt = KT_DB::prepare(
			"SELECT i_gedcom AS gedcom FROM `##individuals` WHERE i_file = ?" .
			" UNION ALL " .
			"SELECT f_gedcom AS gedcom FROM `##families`    WHERE f_file = ?" .
			" UNION ALL " .
			"SELECT s_gedcom AS gedcom FROM `##sources`     WHERE s_file = ?" .
			" UNION ALL " .
			"SELECT o_gedcom AS gedcom FROM `##other`       WHERE o_file = ? AND o_type NOT IN ('HEAD', 'TRLR')" .
			" UNION ALL " .
			"SELECT m_gedcom AS gedcom FROM `##media`       WHERE m_file = ?"
		)->execute(array($this->tree_id, $this->tree_id, $this->tree_id, $this->tree_id, $this->tree_id));

		while ($row = $stmt->fetch()) {
			$buffer .= reformat_record_export($row->gedcom);
			if (strlen($buffer)>65535) {
				fwrite($file_pointer, $buffer);
				$buffer = '';
			}
		}

		fwrite($file_pointer, $buffer . '0 TRLR' . KT_EOL);
		fclose($file_pointer);

		return @rename($tmp_file, $gedcom_file);
	}

	/**
	 * Create a new record from GEDCOM data.
	 *
	 * @param string $gedcom
	 *
	 * @throws \Exception
	 *
	 * @return GedcomRecord
	 */
	public function createRecord($gedcom) {
		if (preg_match('/^0 @(' . KT_REGEX_XREF . ')@ (' . KT_REGEX_TAG . ')/', $gedcom, $match)) {
			$xref = $match[1];
			$type = $match[2];
		} else {
			throw new \Exception('Invalid argument to GedcomRecord::createRecord(' . $gedcom . ')');
		}

		if (strpos("\r", $gedcom) !== false) {
			// MSDOS line endings will break things in horrible ways
			throw new \Exception('Evil line endings found in GedcomRecord::createRecord(' . $gedcom . ')');
		}

		// kiwitrees creates XREFs containing digits. Anything else (e.g. “new”) is just a placeholder.
		if (!preg_match('/\d/', $xref)) {
			$xref	= get_new_xref($type, KT_GED_ID);
			$gedcom = preg_replace('/^0 @(' . KT_REGEX_XREF . ')@/', '0 @' . $xref . '@', $gedcom);
		}

		// Create a change record, if not already present
		if (!preg_match('/\n1 CHAN/', $gedcom)) {
			$gedcom .= "\n1 CHAN\n2 DATE " . date('d M Y') . "\n3 TIME " . date('H:i:s') . "\n2 _KT_USER " . getUserName(KT_USER_ID);
		}

		// Create a pending change
		KT_DB::prepare(
			"INSERT INTO `##change` (gedcom_id, xref, old_gedcom, new_gedcom, user_id) VALUES (?, ?, '', ?, ?)"
		)->execute(array(
			$this->tree_id,
			$xref,
			$gedcom,
			KT_USER_ID,
		));

		AddToLog('Create: ' . $type . ' ' . $xref, 'edit');

		if (get_user_setting(KT_USER_ID, 'auto_accept')) {
			accept_all_changes($xref, KT_GED_ID);
		}
		// Return the newly created record. Note that since GedcomRecord
		// has a cache of pending changes, we cannot use it to create a
		// record with a newly created pending change.
		return KT_GedcomRecord::getInstance($xref, $this, $gedcom);
	}
}
