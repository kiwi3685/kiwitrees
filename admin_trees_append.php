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

define('KT_SCRIPT_NAME', 'admin_trees_append.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Append family trees'))
	->pageHeader();

echo '<h2>', $controller->getPageTitle(), '</h2>
	<a class="current faq_link" href="' . KT_KIWITREES_URL . '/kb/user-guide/merging-family-trees/" target="_blank" rel="noopener noreferrer" title="'. KT_I18N::translate('View FAQ for this page.'). '">'. KT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a>
';

$ged1_id = KT_Filter::post('ged1_id');
$ged2_id = KT_Filter::post('ged2_id');

if ($ged1_id && $ged2_id != $ged1_id ) {
	// Every XREF used by both trees
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
		" SELECT xref FROM `##change` WHERE gedcom_id = ?" .
		"  UNION " .
		" SELECT i_id AS xref FROM `##individuals` WHERE i_file = ?" .
		"  UNION " .
		" SELECT f_id AS xref FROM `##families` WHERE f_file = ?" .
		"  UNION " .
		" SELECT s_id AS xref FROM `##sources` WHERE s_file = ?" .
		"  UNION " .
		" SELECT m_id AS xref FROM `##media` WHERE m_file = ?" .
		"  UNION " .
		" SELECT o_id AS xref FROM `##other` WHERE o_file = ? AND o_type NOT IN ('HEAD', 'TRLR')" .
		") AS other_trees USING (xref)"
	)->execute(array(
			$ged1_id, $ged1_id, $ged1_id, $ged1_id, $ged1_id,
			$ged2_id, $ged2_id, $ged2_id, $ged2_id, $ged2_id, $ged2_id
	))->fetchAssoc();

	if ($xrefs) {
		echo
		'<p class="error">',
		KT_I18N::plural(
			'The two family trees have %1$s record which uses the same “XREF”.',
			'The two family trees have %1$s records which uses the same “XREF”.',
			count($xrefs), count($xrefs)
		),
		'</p>',
		'<p class="error">',
		KT_I18N::translate('You must renumber the records in one of the trees before you can combine them.'),
		'</p>';
	} else {
		KT_DB::exec("START TRANSACTION");
		KT_DB::exec(
			"LOCK TABLE" .
			" `##individuals` WRITE," .
			" `##individuals` AS individuals2 READ," .
			" `##families` WRITE," .
			" `##families` AS families2 READ," .
			" `##sources` WRITE," .
			" `##sources` AS sources2 READ," .
			" `##media` WRITE," .
			" `##media` AS media2 READ," .
			" `##other` WRITE," .
			" `##other` AS other2 READ," .
			" `##name` WRITE," .
			" `##name` AS name2 READ," .
			" `##placelinks` WRITE," .
			" `##placelinks` AS placelinks2 READ," .
			" `##change` WRITE," .
			" `##change` AS change2 READ," .
			" `##dates` WRITE," .
			" `##dates` AS dates2 READ," .
			" `##default_resn` WRITE," .
			" `##default_resn` AS default_resn2 READ," .
			" `##hit_counter` WRITE," .
			" `##hit_counter` AS hit_counter2 READ," .
			" `##link` WRITE," .
			" `##link` AS link2 READ"
		);
		KT_DB::prepare(
			"INSERT INTO `##individuals` (i_id, i_file, i_rin, i_sex, i_gedcom)" .
			" SELECT i_id, ?, i_rin, i_sex, i_gedcom FROM `##individuals` AS individuals2 WHERE i_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##families` (f_id, f_file, f_husb, f_wife, f_gedcom, f_numchil)" .
			" SELECT f_id, ?, f_husb, f_wife, f_gedcom, f_numchil FROM `##families` AS families2 WHERE f_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##sources` (s_id, s_file, s_name, s_gedcom)" .
			" SELECT s_id, ?, s_name, s_gedcom FROM `##sources` AS sources2 WHERE s_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##media` (m_id, m_ext, m_type, m_titl, m_filename, m_file, m_gedcom)" .
			" SELECT m_id, m_ext, m_type, m_titl, m_filename, ?, m_gedcom FROM `##media` AS media2 WHERE m_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##other` (o_id, o_file, o_type, o_gedcom)" .
			" SELECT o_id, ?, o_type, o_gedcom FROM `##other` AS other2 WHERE o_file = ? AND o_type NOT IN ('HEAD', 'TRLR')"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##name` (n_file, n_id, n_num, n_type, n_sort, n_full, n_surname, n_surn, n_givn, n_soundex_givn_std, n_soundex_surn_std, n_soundex_givn_dm, n_soundex_surn_dm)" .
			" SELECT ?, n_id, n_num, n_type, n_sort, n_full, n_surname, n_surn, n_givn, n_soundex_givn_std, n_soundex_surn_std, n_soundex_givn_dm, n_soundex_surn_dm FROM `##name` AS name2 WHERE n_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##placelinks` (pl_p_id, pl_gid, pl_file)" .
			" SELECT pl_p_id, pl_gid, ? FROM `##placelinks` AS placelinks2 WHERE pl_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##dates` (d_day, d_month, d_mon, d_year, d_julianday1, d_julianday2, d_fact, d_gid, d_file, d_type)" .
			" SELECT d_day, d_month, d_mon, d_year, d_julianday1, d_julianday2, d_fact, d_gid, ?, d_type FROM `##dates` AS dates2 WHERE d_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##default_resn` (gedcom_id, xref, tag_type, resn, comment, updated)" .
			" SELECT ?, xref, tag_type, resn, comment, updated FROM `##default_resn` AS default_resn2 WHERE gedcom_id = ?"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::prepare(
			"INSERT INTO `##link` (l_file, l_from, l_type, l_to)" .
			" SELECT ?, l_from, l_type, l_to FROM `##link` AS link2 WHERE l_file = ?"
		)->execute(array($ged2_id, $ged1_id));
		// This table may contain old (deleted) references, which could clash.  IGNORE these.
		KT_DB::prepare(
			"INSERT IGNORE INTO `##change` (change_time, status, gedcom_id, xref, old_gedcom, new_gedcom, user_id)" .
			" SELECT change_time, status, ?, xref, old_gedcom, new_gedcom, user_id FROM `##change` AS change2 WHERE gedcom_id = ?"
		)->execute(array($ged2_id, $ged1_id));
		// This table may contain old (deleted) references, which could clash.  IGNORE these.
		KT_DB::prepare(
			"INSERT IGNORE INTO `##hit_counter` (gedcom_id, page_name, page_parameter, page_count)" .
			" SELECT ?, page_name, page_parameter, page_count FROM `##hit_counter` AS hit_counter2 WHERE gedcom_id = ? AND page_name <> 'index.php'"
		)->execute(array($ged2_id, $ged1_id));
		KT_DB::exec("UNLOCK TABLES");
		KT_DB::exec("COMMIT");
		echo '<p>', KT_I18N::translate('The family trees were combined successfully.'), '</p>';
	}
} else {
	echo '<form method="POST" action="', KT_SCRIPT_NAME, '">';
	echo '<input type="hidden" name="continue" value="1">';
	echo '<p>', KT_I18N::translate(
	/* I18N:  Copy all the records from [family tree 1] into [family tree 2] */ 'Copy all the records from %1$s into %2$s',
		select_edit_control('ged1_id', KT_Tree::getIdList(), '', null),
		select_edit_control('ged2_id', KT_Tree::getIdList(), '', null)
	), '</p>';

	echo '<button type="submit" class="btn btn-primary">';
	echo '<i class="fa fa-check"></i> ', /* I18N: Button label */ KT_I18N::translate('continue');
	echo '</button>';
	echo '</form>';
}
