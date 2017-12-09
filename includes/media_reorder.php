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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

require_once KT_ROOT.'includes/functions/functions_print_facts.php';

function media_reorder_row($rowm) {
	$media = KT_Media::getInstance($rowm['m_id']);

	if (!$media->canDisplayDetails()) {
		return false;
	}

	?>
	<li class="facts_value" style="list-style:none;cursor:move;margin-bottom:2px;" id="li_<?php echo $media->getXref(); ?>" >
		<div class="pic">
			<?php echo $media->displayImage(); ?>
			<?php echo $media->getFullName(); ?>
		</div>
		<input type="hidden" name="order1[<?php echo $media->getXref(); ?>]" value="0">
	</li>
	<?php
	return true;
}

$controller->addInlineJavascript('
	jQuery("#reorder_media_list").sortable({forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});

	//-- update the order numbers after drag-n-drop sorting is complete
	jQuery("#reorder_media_list").bind("sortupdate", function(event, ui) {
			jQuery("#"+jQuery(this).attr("id")+" input").each(
				function (index, value) {
					value.value = index+1;
				}
			);
		});
	');
?>
<div id="reordermedia-page">
	<h2><?php echo KT_I18N::translate('Re-order media'); ?></h2>
	<span class="help_content">
		<?php echo KT_I18N::translate('Click a row then drag-and-drop to re-order media.'); ?>
	</span>
	<form name="reorder_form" method="post" action="edit_interface.php">
		<input type="hidden" name="action" value="reorder_media_update">
		<input type="hidden" name="pid" value="<?php echo $pid; ?>">

		<ul id="reorder_media_list">
			<?php
			$person = KT_Person::getInstance($pid);

			//-- find all of the related ids
			$ids = array($person->getXref());
			foreach ($person->getSpouseFamilies() as $family) {
				$ids[] = $family->getXref();
			}

			//-- If they exist, get a list of the sorted current objects in the indi gedcom record  -  (1 _KT_OBJE_SORT @xxx@ .... etc) ----------
			$sort_current_objes = array();
			$sort_ct = preg_match_all('/\n1 _KT_OBJE_SORT @(.*)@/', $person->getGedcomRecord(), $sort_match, PREG_SET_ORDER);
			for ($i=0; $i<$sort_ct; $i++) {
				if (!isset($sort_current_objes[$sort_match[$i][1]])) {
					$sort_current_objes[$sort_match[$i][1]] = 1;
				} else {
					$sort_current_objes[$sort_match[$i][1]]++;
				}
				$sort_obje_links[$sort_match[$i][1]][] = $sort_match[$i][0];
			}

			// create ORDER BY list from Gedcom sorted records list  ---------------------------
			$orderbylist = 'ORDER BY '; // initialize
			foreach ($sort_match as $id) {
				$orderbylist .= "m_id='$id[1]' DESC, ";
			}
			$orderbylist = rtrim($orderbylist, ', ');

			//-- get a list of the current objects in the record
			$current_objes = array();
			$regexp = '/\n\d OBJE @(.*)@/';
			$ct = preg_match_all($regexp, $person->getGedcomRecord(), $match, PREG_SET_ORDER);
			for ($i=0; $i<$ct; $i++) {
				if (!isset($current_objes[$match[$i][1]])) {
					$current_objes[$match[$i][1]] = 1;
				}  else {
					$current_objes[$match[$i][1]]++;
				}
				$obje_links[$match[$i][1]][] = $match[$i][0];
			}

			$media_found = false;

			// Get the related media items
			$sqlmm =
				"SELECT DISTINCT m_id, m_ext, m_filename, m_titl, m_file, m_gedcom" .
				" FROM `##media`" .
				" JOIN `##link` ON (m_id=l_to AND m_file=l_file AND l_type='OBJE')" .
				" WHERE m_file=? AND l_from IN (";
			$i=0;
			$vars=array(KT_GED_ID);
			foreach ($ids as $media_id) {
				if ($i>0) $sqlmm .= ",";
				$sqlmm .= "?";
				$vars[]=$media_id;
				$i++;
			}
			$sqlmm .= ')';

			if ($sort_ct>0) {
				$sqlmm .= $orderbylist;
			}

			$rows=KT_DB::prepare($sqlmm)->execute($vars)->fetchAll(PDO::FETCH_ASSOC);

			$foundObjs = array();
			foreach ($rows as $rowm) {
				if (isset($foundObjs[$rowm['m_id']])) {
					if (isset($current_objes[$rowm['m_id']])) {
						$current_objes[$rowm['m_id']]--;
					}
					continue;
				}
				$rows = array();
				$rows['normal'] = $rowm;
				if (isset($current_objes[$rowm['m_id']])) $current_objes[$rowm['m_id']]--;
				foreach ($rows as $rowm) {
					$res = media_reorder_row($rowm);
					$media_found = $media_found || $res;
					$foundObjs[$rowm['m_id']] = true;
				}
			}
			?>
		</ul>
		<?php echo no_update_chan($person); ?>
		<p id="save-cancel">
			<button class="btn btn-primary" type="submit">
				<i class="fa fa-save"></i>
				<?php echo KT_I18N::translate('save'); ?>
			</button>
			<button class="btn btn-primary" type="button" onclick="window.close();">
				<i class="fa fa-times"></i>
				<?php echo KT_I18N::translate('close'); ?>
			</button>
		</p>
	</form>
</div>
