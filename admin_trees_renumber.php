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

define('KT_SCRIPT_NAME', 'admin_trees_renumber.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Renumber family tree'))
	->pageHeader();

// Every XREF used by this tree and also used by some other tree
$xrefs = KT_DB::prepare(
	"SELECT xref, type FROM (" .
	" SELECT i_id AS xref, 'INDI' AS type FROM `##individuals` WHERE i_file = ?" .
	"  UNION " .
	" SELECT f_id AS xref, 'FAM' AS type FROM `##families` WHERE f_file = ?" .
	"  UNION " .
	" SELECT s_id AS xref, 'SOUR' AS type FROM `##sources` WHERE s_file = ?" .
	"  UNION " .
	" SELECT m_id AS xref, 'OBJE' AS type FROM `##media` WHERE m_file = ?" .
	"  UNION " .
	" SELECT o_id AS xref, o_type AS type FROM `##other` WHERE o_file = ? AND o_type NOT IN ('HEAD', 'TRLR')" .
	") AS this_tree JOIN (".
	" SELECT xref FROM `##change` WHERE gedcom_id <> ?" .
	"  UNION " .
	" SELECT i_id AS xref FROM `##individuals` WHERE i_file <> ?" .
	"  UNION " .
	" SELECT f_id AS xref FROM `##families` WHERE f_file <> ?" .
	"  UNION " .
	" SELECT s_id AS xref FROM `##sources` WHERE s_file <> ?" .
	"  UNION " .
	" SELECT m_id AS xref FROM `##media` WHERE m_file <> ?" .
	"  UNION " .
	" SELECT o_id AS xref FROM `##other` WHERE o_file <> ? AND o_type NOT IN ('HEAD', 'TRLR')" .
	") AS other_trees USING (xref)"
)->execute(array(
		KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID,
		KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID
	))->fetchAssoc();

echo '<h2>', $controller->getPageTitle(), ' — ', $KT_TREE->tree_title_html, '</h2>
	<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/merging-family-trees/" target="_blank" rel="noopener noreferrer" title="'. KT_I18N::translate('View FAQ for this page.'). '">'. KT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a>
';

if (KT_Filter::get('continue')) {
	foreach ($xrefs as $old_xref=>$type) {
		KT_DB::exec("START TRANSACTION");
		KT_DB::exec(
			"LOCK TABLE `##individuals` WRITE," .
			" `##families` WRITE," .
			" `##sources` WRITE," .
			" `##media` WRITE," .
			" `##other` WRITE," .
			" `##name` WRITE," .
			" `##placelinks` WRITE," .
			" `##change` WRITE," .
			" `##next_id` WRITE," .
			" `##dates` WRITE," .
			" `##default_resn` WRITE," .
			" `##hit_counter` WRITE," .
			" `##link` WRITE," .
			" `##user_gedcom_setting` WRITE"
		);
		$new_xref = get_new_xref($type);
		switch ($type) {
		case 'INDI':
			KT_DB::prepare(
				"UPDATE `##individuals` SET i_id = ?, i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_id = ? AND i_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ INDI\n", "0 @$new_xref@ INDI\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'HUSB') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " HUSB @$old_xref@", " HUSB @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'WIFE') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " WIFE @$old_xref@", " WIFE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'CHIL') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " CHIL @$old_xref@", " CHIL @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'ASSO') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " ASSO @$old_xref@", " ASSO @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = '_ASSO') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " _ASSO @$old_xref@", " _ASSO @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'ASSO') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " ASSO @$old_xref@", " ASSO @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = '_ASSO') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " _ASSO @$old_xref@", " _ASSO @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##placelinks` SET pl_gid = ? WHERE pl_gid = ? AND pl_file = ?"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##dates` SET d_gid = ? WHERE d_gid = ? AND d_file = ?"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##user_gedcom_setting` SET setting_value = ? WHERE setting_value = ? AND gedcom_id = ? AND setting_name IN ('gedcomid', 'rootid')"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
			break;
		case 'FAM':
			KT_DB::prepare(
				"UPDATE `##families` SET f_id = ?, f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_id = ? AND f_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ FAM\n", "0 @$new_xref@ FAM\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'FAMC') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " FAMC @$old_xref@", " FAMC @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'FAMS') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " FAMS @$old_xref@", " FAMS @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##placelinks` SET pl_gid = ? WHERE pl_gid = ? AND pl_file = ?"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##dates` SET d_gid = ? WHERE d_gid = ? AND d_file = ?"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
			break;
		case 'SOUR':
			KT_DB::prepare(
				"UPDATE `##sources` SET s_id = ?, s_gedcom = REPLACE(s_gedcom, ?, ?) WHERE s_id = ? AND s_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ SOUR\n", "0 @$new_xref@ SOUR\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'SOUR') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " SOUR @$old_xref@", " SOUR @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'SOUR') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " SOUR @$old_xref@", " SOUR @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##media` JOIN `##link` ON (l_file = m_file AND l_to = ? AND l_type = 'SOUR') SET m_gedcom = REPLACE(m_gedcom, ?, ?) WHERE m_file = ?"
			)->execute(array($old_xref, " SOUR @$old_xref@", " SOUR @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##other` JOIN `##link` ON (l_file = o_file AND l_to = ? AND l_type = 'SOUR') SET o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_file = ?"
			)->execute(array($old_xref, " SOUR @$old_xref@", " SOUR @$new_xref@", KT_GED_ID));
			break;
		case 'REPO':
			KT_DB::prepare(
				"UPDATE `##other` SET o_id = ?, o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_id = ? AND o_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ REPO\n", "0 @$new_xref@ REPO\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##sources` JOIN `##link` ON (l_file = s_file AND l_to = ? AND l_type = 'REPO') SET s_gedcom = REPLACE(s_gedcom, ?, ?) WHERE s_file = ?"
			)->execute(array($old_xref, " REPO @$old_xref@", " REPO @$new_xref@", KT_GED_ID));
			break;
		case 'NOTE':
			KT_DB::prepare(
				"UPDATE `##other` SET o_id = ?, o_gedcom = REPLACE(REPLACE(o_gedcom, ?, ?), ?, ?) WHERE o_id = ? AND o_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ NOTE\n", "0 @$new_xref@ NOTE\n", "0 @$old_xref@ NOTE ", "0 @$new_xref@ NOTE ", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'NOTE') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " NOTE @$old_xref@", " NOTE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'NOTE') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " NOTE @$old_xref@", " NOTE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##media` JOIN `##link` ON (l_file = m_file AND l_to = ? AND l_type = 'NOTE') SET m_gedcom = REPLACE(m_gedcom, ?, ?) WHERE m_file = ?"
			)->execute(array($old_xref, " NOTE @$old_xref@", " NOTE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##sources` JOIN `##link` ON (l_file = s_file AND l_to = ? AND l_type = 'NOTE') SET s_gedcom = REPLACE(s_gedcom, ?, ?) WHERE s_file = ?"
			)->execute(array($old_xref, " NOTE @$old_xref@", " NOTE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##other` JOIN `##link` ON (l_file = o_file AND l_to = ? AND l_type = 'NOTE') SET o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_file = ?"
			)->execute(array($old_xref, " NOTE @$old_xref@", " NOTE @$new_xref@", KT_GED_ID));
			break;
		case 'OBJE':
			KT_DB::prepare(
				"UPDATE `##media` SET m_id = ?, m_gedcom = REPLACE(m_gedcom, ?, ?) WHERE m_id = ? AND m_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ OBJE\n", "0 @$new_xref@ OBJE\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ? AND l_type = 'OBJE') SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " OBJE @$old_xref@", " OBJE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ? AND l_type = 'OBJE') SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " OBJE @$old_xref@", " OBJE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##media` JOIN `##link` ON (l_file = m_file AND l_to = ? AND l_type = 'OBJE') SET m_gedcom = REPLACE(m_gedcom, ?, ?) WHERE m_file = ?"
			)->execute(array($old_xref, " OBJE @$old_xref@", " OBJE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##sources` JOIN `##link` ON (l_file = s_file AND l_to = ? AND l_type = 'OBJE') SET s_gedcom = REPLACE(s_gedcom, ?, ?) WHERE s_file = ?"
			)->execute(array($old_xref, " OBJE @$old_xref@", " OBJE @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##other` JOIN `##link` ON (l_file = o_file AND l_to = ? AND l_type = 'OBJE') SET o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_file = ?"
			)->execute(array($old_xref, " OBJE @$old_xref@", " OBJE @$new_xref@", KT_GED_ID));
			break;
		default:
			KT_DB::prepare(
				"UPDATE `##other` SET o_id = ?, o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_id = ? AND o_file = ?"
			)->execute(array($new_xref, "0 @$old_xref@ $type\n", "0 @$new_xref@ $type\n", $old_xref, KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##individuals` JOIN `##link` ON (l_file = i_file AND l_to = ?) SET i_gedcom = REPLACE(i_gedcom, ?, ?) WHERE i_file = ?"
			)->execute(array($old_xref, " @$old_xref@", " @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##families` JOIN `##link` ON (l_file = f_file AND l_to = ?) SET f_gedcom = REPLACE(f_gedcom, ?, ?) WHERE f_file = ?"
			)->execute(array($old_xref, " @$old_xref@", " @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##media` JOIN `##link` ON (l_file = m_file AND l_to = ?) SET m_gedcom = REPLACE(m_gedcom, ?, ?) WHERE m_file = ?"
			)->execute(array($old_xref, " @$old_xref@", " @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##sources` JOIN `##link` ON (l_file = s_file AND l_to = ?) SET s_gedcom = REPLACE(s_gedcom, ?, ?) WHERE s_file = ?"
			)->execute(array($old_xref, " @$old_xref@", " @$new_xref@", KT_GED_ID));
			KT_DB::prepare(
				"UPDATE `##other` JOIN `##link` ON (l_file = o_file AND l_to = ?) SET o_gedcom = REPLACE(o_gedcom, ?, ?) WHERE o_file = ?"
			)->execute(array($old_xref, " @$old_xref@", " @$new_xref@", KT_GED_ID));
			break;
		}
		KT_DB::prepare(
			"UPDATE `##name` SET n_id = ? WHERE n_id = ? AND n_file = ?"
		)->execute(array($new_xref, $old_xref, KT_GED_ID));
		KT_DB::prepare(
			"UPDATE `##default_resn` SET xref = ? WHERE xref = ? AND gedcom_id = ?"
		)->execute(array($new_xref, $old_xref, KT_GED_ID));
		KT_DB::prepare(
			"UPDATE `##hit_counter` SET page_parameter = ? WHERE page_parameter = ? AND gedcom_id = ?"
		)->execute(array($new_xref, $old_xref, KT_GED_ID));
		KT_DB::prepare(
			"UPDATE `##link` SET l_from = ? WHERE l_from = ? AND l_file = ?"
		)->execute(array($new_xref, $old_xref, KT_GED_ID));
		KT_DB::prepare(
			"UPDATE `##link` SET l_to = ? WHERE l_to = ? AND l_file = ?"
		)->execute(array($new_xref, $old_xref, KT_GED_ID));

		echo '<div class="renum_list">', KT_I18N::translate('The record %1$s was renamed to %2$s', $old_xref, $new_xref), '</div>';

		unset($xrefs[$old_xref]);
		KT_DB::exec("UNLOCK TABLES");
		KT_DB::exec("COMMIT");

		try {
			KT_DB::prepare(
				"UPDATE `##favorite` SET xref = ? WHERE xref = ? AND gedcom_id = ?"
			)->execute(array($new_xref, $old_xref, KT_GED_ID));
		} catch (Exception $ex) {
			// Perhaps the favorites module was not installed?
		}

		// How much time do we have left?
		if (microtime(true) - $start_time > ini_get('max_execution_time') - 5) {
			echo '<p class="warning clearfloat">', KT_I18N::translate('The server’s time limit was reached.'), '</p>';
			break;
		}
	}
	if ($xrefs) {

	}
} else {
	echo '<p>', KT_I18N::translate('In a family tree, each record has an internal reference number (called an “XREF”) such as “F123” or “R14”.') ,'</p>';
	echo '<p>', KT_I18N::translate('You can renumber a family tree so that these reference numbers are unique across all family trees.') ,'</p>';
}

echo '<p class="clearfloat">', KT_I18N::plural(
	'This family tree has %s record which uses the same “XREF” as another family tree.',
	'This family tree has %s records which use the same “XREF” as another family tree.',
	count($xrefs), count($xrefs)
), '</p>';

if ($xrefs) {
	// We use GET (not POST) for this update operation - because we want the user to
	// be able to press F5 to continue after a timeout.
	echo '
		<div id="renumber">
			<form method="GET" action="', KT_SCRIPT_NAME, '">
				<p>', KT_I18N::translate('You can renumber this family tree.'), '
				<input type="submit" name="continue" value="', /* I18N: button label */ KT_I18N::translate('continue') ,'"></p>
				<input type="hidden" name="ged" value="', KT_Filter::escapeUrl(KT_GEDCOM), '">
			</form>
			<p class="warning">', KT_I18N::translate('Caution!  This may take a long time.  Be patient.'), '</p>
			<p>', KT_I18N::translate('If your server times out, press F5 to continue'), '</p>
		</div>
	';
}
