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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'admin_trees_merge.php');
require './includes/session.php';

$controller = new KT_Controller_Page;
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Merge records'))
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();')
	->pageHeader();

require_once KT_ROOT.'includes/functions/functions_edit.php';
require_once KT_ROOT.'includes/functions/functions_import.php';

$ged    = $GEDCOM;
$gid1   = KT_Filter::post('gid1', KT_REGEX_XREF);
$gid2   = KT_Filter::post('gid2', KT_REGEX_XREF);
$action = KT_Filter::post('action', 'choose|select|merge', 'choose');
$ged1   = KT_Filter::post('ged1', null, $ged);
$ged2   = KT_Filter::post('ged2', null, $ged);
$keep1  = KT_Filter::postArray('keep1');
$keep2  = KT_Filter::postArray('keep2');

if ($action != 'choose') {
	if ($gid1 == $gid2 && $GEDCOM == $ged2) {
		$action='choose'; ?>
		<span class="error"><?php echo KT_I18N::translate('You entered the same IDs.  You cannot merge the same records.'); ?></span>
	<?php } else {
		$gedrec1 = find_gedcom_record($gid1, KT_GED_ID, true);
		$gedrec2 = find_gedcom_record($gid2, get_id_from_gedcom($ged2), true);

		// Fetch the original XREF - may differ in case from the supplied value
		$tmp = new KT_Person($gedrec1); $gid1 = $tmp->getXref();
		$tmp = new KT_Person($gedrec2); $gid2 = $tmp->getXref();

		if (empty($gedrec1)) { ?>
			<span class="error"><?php echo KT_I18N::translate('Unable to find record with ID'); ?>:</span> <?php echo $gid1; ?>, <?php echo $ged1;
			$action = 'choose';
		} elseif (empty($gedrec2)) { ?>
			<span class="error"><?php echo KT_I18N::translate('Unable to find record with ID'); ?>:</span> <?php echo $gid2; ?>, <?php echo $ged2;
			$action = 'choose';
		} else {
			$type1 = '';
			$ct = preg_match("/0 @$gid1@ (.*)/", $gedrec1, $match);
			if ($ct>0) {
				$type1 = trim($match[1]);
			}
			$type2 = "";
			$ct = preg_match("/0 @$gid2@ (.*)/", $gedrec2, $match);
			if ($ct>0) $type2 = trim($match[1]);
			if (!empty($type1) && ($type1!=$type2)) { ?>
				<span class="error"><?php echo KT_I18N::translate('Records are not the same type.  Cannot merge records that are not the same type.'); ?></span>
				<?php
				$action = 'choose';
			} else {
				$facts1		= array();
				$facts2		= array();
				$prev_tags	= array();
				$ct = preg_match_all('/\n1 (\w+)/', $gedrec1, $match, PREG_SET_ORDER);
				for ($i=0; $i<$ct; $i++) {
					$fact = trim($match[$i][1]);
					if (isset($prev_tags[$fact])) {
						$prev_tags[$fact]++;
					} else {
						$prev_tags[$fact] = 1;
					}
					$subrec = get_sub_record(1, "1 $fact", $gedrec1, $prev_tags[$fact]);
					$facts1[] = array('fact'=>$fact, 'subrec'=>trim($subrec));
				}
				$prev_tags = array();
				$ct = preg_match_all('/\n1 (\w+)/', $gedrec2, $match, PREG_SET_ORDER);
				for ($i=0; $i<$ct; $i++) {
					$fact = trim($match[$i][1]);
					if (isset($prev_tags[$fact])) {
						$prev_tags[$fact]++;
					} else {
						$prev_tags[$fact] = 1;
					}
					$subrec = get_sub_record(1, "1 $fact", $gedrec2, $prev_tags[$fact]);
					$facts2[] = array('fact'=>$fact, 'subrec'=>trim($subrec));
				}
				if ($action == 'select') { ?>
					<div id="merge2">
						<h3><?php echo KT_I18N::translate('Merge records'); ?></h3>
						<form method="post" action="admin_trees_merge.php">
							<?php echo KT_I18N::translate('The following facts were exactly the same in both records and will be merged automatically.'); ?>
							<br>
							<input type="hidden" name="gid1" value="<?php echo $gid1; ?>">
							<input type="hidden" name="gid2" value="<?php echo $gid2; ?>">
							<input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
							<input type="hidden" name="ged2" value="<?php echo $ged2; ?>">
							<input type="hidden" name="action" value="merge">
							<?php
							$equal_count	= 0;
							$skip1			= array();
							$skip2			= array();
							?>
							<table>
								<?php foreach ($facts1 as $i=>$fact1) {
									foreach ($facts2 as $j=>$fact2) {
										if (utf8_strtoupper($fact1['subrec']) == utf8_strtoupper($fact2['subrec'])) {
											$skip1[] = $i;
											$skip2[] = $j;
											$equal_count++; ?>
											<tr>
												<td><?php echo KT_I18N::translate($fact1['fact']); ?>
													<input type="hidden" name="keep1[]" value="<?php echo $i; ?>">
												</td>
												<td>
													<?php echo nl2br($fact1['subrec']); ?>
												</td>
											</tr>
										<?php }
									}
								}
								if ($equal_count == 0) { ?>
									<tr>
										<td>
											<?php echo KT_I18N::translate('No matching facts found'); ?>
										</td>
									</tr>
								<?php } ?>
							</table>
							<br>
							<?php echo KT_I18N::translate('The following facts did not match.  Select the information you would like to keep.'); ?>
							<table>
								<tr>
									<th>
										<?php echo KT_I18N::translate('Record') . ' ' . $gid1; ?>
									</th>
									<th>
										<?php echo KT_I18N::translate('Record') . ' ' . $gid2; ?>
									</th>
								</tr>
								<tr>
									<td>
										<table>
											<?php foreach ($facts1 as $i=>$fact1) {
												if (($fact1['fact'] != 'CHAN') && (!in_array($i, $skip1))) { ?>
													<tr>
														<td>
															<input type="checkbox" name="keep1[]" value="<?php echo $i; ?>" checked="checked">
														</td>
														<td>
															<?php echo nl2br($fact1['subrec']); ?>
														</td>
													</tr>
												<?php }
											} ?>
										</table>
									</td>
									<td>
										<table>
											<?php foreach ($facts2 as $j=>$fact2) {
												if (($fact2['fact'] != 'CHAN') && (!in_array($j, $skip2))) { ?>
													<tr>
														<td>
															<input type="checkbox" name="keep2[]" value="<?php echo $j; ?>" checked="checked">
														</td>
														<td>
															<?php echo nl2br($fact2['subrec']); ?>
														</td>
													</tr>
												<?php }
											} ?>
										</table>
									</td>
								</tr>
							</table>
							<p>
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-floppy-o"></i>
									<?php echo KT_I18N::translate('save'); ?>
								</button>
							</p>
						</form>
					</div>
				<?php } elseif ($action == 'merge') { ?>
					<div id="merge3">
						<h3><?php echo KT_I18N::translate('Merge records'); ?></h3>
						<?php if ($GEDCOM == $ged2) {
							$success = delete_gedrec($gid2, KT_GED_ID);
							echo KT_I18N::translate('GEDCOM record successfully deleted.') . '<br>';
							//-- replace all the records that linked to gid2
							$ids = fetch_all_links($gid2, KT_GED_ID);
							foreach ($ids as $id) {
								$record = find_gedcom_record($id, KT_GED_ID, true);
								echo KT_I18N::translate('Updating linked record') . ' ' . $id . '<br>';
								$newrec = str_replace("@$gid2@", "@$gid1@", $record);
								$newrec = preg_replace(
									'/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
									'$2',
									$newrec
								);
								replace_gedrec($id, KT_GED_ID, $newrec);
							}
							// Update any linked user-accounts
							KT_DB::prepare(
								"UPDATE `##user_gedcom_setting`".
								" SET setting_value=?".
								" WHERE gedcom_id=? AND setting_name='gedcomid' AND setting_value=?"
							)->execute(array($gid2, KT_GED_ID, $gid1));

							// Merge hit counters
							$hits=KT_DB::prepare(
								"SELECT page_name, SUM(page_count)".
								" FROM `##hit_counter`".
								" WHERE gedcom_id=? AND page_parameter IN (?, ?)".
								" GROUP BY page_name"
							)->execute(array(KT_GED_ID, $gid1, $gid2))->fetchAssoc();
							foreach ($hits as $page_name=>$page_count) {
								KT_DB::prepare(
									"UPDATE `##hit_counter` SET page_count=?".
									" WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
								)->execute(array($page_count, KT_GED_ID, $page_name, $gid1));
							}
							KT_DB::prepare(
								"DELETE FROM `##hit_counter`".
								" WHERE gedcom_id=? AND page_parameter=?"
							)->execute(array(KT_GED_ID, $gid2));
						}
						$newgedrec = "0 @$gid1@ $type1\n";
						for ($i=0; ($i<count($facts1) || $i<count($facts2)); $i++) {
							if (isset($facts1[$i])) {
								if (in_array($i, $keep1)) {
									$newgedrec .= $facts1[$i]['subrec']."\n";
									echo KT_I18N::translate('Adding') . ' ' . $facts1[$i]['fact'] . ' ' . KT_I18N::translate('from') . ' ' . $gid1 . '<br>';
								}
							}
							if (isset($facts2[$i])) {
								if (in_array($i, $keep2)) {
									$newgedrec .= $facts2[$i]['subrec']."\n";
									echo KT_I18N::translate('Adding') . ' ' . $facts2[$i]['fact'] . ' ' . KT_I18N::translate('from') . ' ' . $gid2 . '<br>';
								}
							}
						}
						replace_gedrec($gid1, KT_GED_ID, $newgedrec);
						$rec = KT_GedcomRecord::getInstance($gid1); ?>
						<p>
							<?php echo KT_I18N::translate('Record %s successfully updated.', '<a href="' . $rec->getHtmlUrl() . '">' . $rec->getXref() . '</a>' ); ?>
						</p>
						<?php $fav_count = update_favorites($gid2, $gid1);
						if ($fav_count > 0) { ?>
							<p>
								<?php echo $fav_count . ' ' . KT_I18N::translate('favorites updated.'); ?>
							<p>
						<?php } ?>
					</div>
				<?php }
			}
		}
	}
}
if ($action == 'choose') {
	$controller->addInlineJavascript('
		function iopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findIndi(textbox, null, ged);
		}
		function fopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findFamily(textbox, ged);
		}
		function sopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findSource(textbox, null, ged);		}
	'); ?>

	<div id="merge">
		<h3><?php echo KT_I18N::translate('Merge records'); ?></h3>
		<form method="post" name="merge" action="admin_trees_merge.php">
			<input type="hidden" name="action" value="select">
			<p>
				<?php echo KT_I18N::translate('Select two GEDCOM records to merge.  The records must be of the same type.'); ?>
			</p>
			<table>
				<tr>
					<td>
						<?php echo KT_I18N::translate('Merge To ID:'); ?>
					</td>
					<td>
						<select
							name="ged"
							tabindex="4"
							onchange="jQuery('#gid1').data('autocomplete-ged', jQuery(this).val());"
							<?php echo count(KT_Tree::getAll()) == 1 ? 'style="width:1px; visibility:hidden;"' : ''; ?>
						>
							<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<option value="<?php echo $tree->tree_name_html; ?>"
									<?php if (empty($ged) && $tree->tree_id == KT_GED_ID || !empty($ged) && $ged == $tree->tree_name) { ?>
										selected="selected"
									<?php } ?>
									dir="auto">
									<?php echo $tree->tree_title_html; ?>
								</option>
							<?php } ?>
						</select>
						<input data-autocomplete-type="INDI" type="text" name="gid1" id="gid1" value="<?php echo $gid1; ?>" size="10" tabindex="1" autofocus="autofocus">
						<a href="#" onclick="iopen_find(document.merge.gid1, document.merge.ged);" tabindex="6" class="icon-button_indi" title="<?php echo KT_I18N::translate('Find an individual'); ?>"></a>
						<a href="#" onclick="fopen_find(document.merge.gid1, document.merge.ged);" tabindex="8" class="icon-button_family" title="<?php echo KT_I18N::translate('Find a family'); ?>"></a>
						<a href="#" onclick="sopen_find(document.merge.gid1, document.merge.ged);" tabindex="10" class="icon-button_source" title="<?php echo KT_I18N::translate('Find a source'); ?>"></a>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo KT_I18N::translate('Merge from ID:'); ?>
					</td>
					<td>
						<select
							name="ged2"
							tabindex="5"
							onchange="jQuery('#gid2').data('autocomplete-ged', jQuery(this).val());"
							<?php echo count(KT_Tree::getAll()) == 1 ? 'style="width:1px;visibility:hidden;"' : ''; ?>
						>
							<?php foreach (KT_Tree::getAll() as $tree) { ?>
								<option value="<?php echo $tree->tree_name_html; ?>"
									<?php if (empty($ged2) && $tree->tree_id == KT_GED_ID || !empty($ged2) && $ged2 == $tree->tree_name) { ?>
										selected="selected"
									<?php } ?>
									dir="auto"><?php echo $tree->tree_title_html; ?>
								</option>
							<?php } ?>
						</select>
						<input data-autocomplete-type="INDI" type="text" name="gid2" id="gid2" value="<?php echo $gid2; ?>" size="10" tabindex="2">
						<a href="#" onclick="iopen_find(document.merge.gid2, document.merge.ged2);" tabindex="7" class="icon-button_indi" title="<?php echo KT_I18N::translate('Find an individual'); ?>"></a>
						<a href="#" onclick="fopen_find(document.merge.gid2, document.merge.ged2);" tabindex="9" class="icon-button_family" title="<?php echo KT_I18N::translate('Find a family'); ?>"></a>
						<a href="#" onclick="sopen_find(document.merge.gid2, document.merge.ged2);" tabindex="11" class="icon-button_source" title="<?php echo KT_I18N::translate('Find a source'); ?>"></a>
					</td>
				</tr>
			</table>
			<input type="submit" value="<?php echo KT_I18N::translate('next'); ?>" tabindex="3">
		</form>
	</div>
	<?php
}
