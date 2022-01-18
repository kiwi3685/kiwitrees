<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class tab_changes_KT_Module extends KT_Module implements KT_Module_Tab {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ KT_I18N::translate('Changes');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ KT_I18N::translate('A tab summarising changes to an individual\'s record');
	}

	// Implement KT_Module_Tab
	public function defaultTabOrder() {
		return 30;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
        return KT_PRIV_NONE; // Access to GEDCOM manager only
	}

	// Implement KT_Module_Tab
	public function getTabContent() {
        global $controller;
        require_once KT_ROOT.'library/php-diff/lib/Diff.php';
        require_once KT_ROOT.'library/php-diff/lib/Diff/Renderer/Html/SideBySide.php';

        $controller->addExternalJavascript(KT_JQUERY_DATATABLES_URL);
		if (KT_USER_CAN_EDIT) {
			$controller
				->addExternalJavascript(KT_JQUERY_DT_HTML5)
				->addExternalJavascript(KT_JQUERY_DT_BUTTONS);
		}
        $controller->addInlineJavascript('
            jQuery("#changes_table").dataTable({
                "sDom": \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
				' . KT_I18N::datatablesI18N() . ',
				buttons: [{extend: "csv", exportOptions: {}}],
				jQueryUI: true,
				autoWidth: false,
				displayLength: 10,
				pagingType: "full_numbers",
                columns: [
					/* 0-Timestamp */   { },
					/* 1-User */        { },
					/* 2-GEDCOM Data */ { },
                    /* 3-Status */      { },
				],
				stateSave: true,
				stateDuration: -1,
			});
    	');

        switch (KT_SCRIPT_NAME) {
            case 'individual.php':
            	$item  = $controller->getSignificantIndividual();
                $title = KT_I18N::translate('All recorded data changes for this person.');
            break;
            case 'family.php':
			case 'note.php':
            case 'source.php':
            case 'repo.php':
            case 'mediaviewer.php':
                $item  = $controller->record;
                $title = '';
            break;
		}
        $xref		= $item->getXref();
        $rows       = $this->getChangeList($xref);

        if ($rows) {
            foreach ($rows as $row) {
                $a = explode("\n", htmlspecialchars($row->old_gedcom));
                $b = explode("\n", htmlspecialchars($row->new_gedcom));
                // Generate a side by side diff
                $renderer = new Diff_Renderer_Html_SideBySide;
                // Options for generating the diff
                $options = array();
                // Initialize the diff class
                $diff = new Diff($a, $b, $options);
                $row->old_gedcom = $diff->Render($renderer);
                $row->new_gedcom = '';
            }
    		?>
            <style>
                #tab_changes.ui-widget-content tbody {
                    background: #ffffff
                }
                #tab_changes_content thead th {
                    vertical-align: top;
                    white-space: nowrap;
                    padding: 5px;
                }
                #tab_changes_content tbody td {
                    vertical-align: top;
                    white-space: pre-wrap;
                    font-size: 90%;
                }
                .dataTables_wrapper table.DifferencesSideBySide {
                    border-collapse:collapse;
                    table-layout:fixed;
                }
                .dataTables_wrapper table.DifferencesSideBySide th {
                    height: auto;
                    padding: 0 3px;
                    background-color: #c9c9c9;
                    font-weight: normal;
                    width: 5%;
                }
                .dataTables_wrapper table.DifferencesSideBySide td.Left {
                    width: 45%;
                }
                #tab_changes_content tbody.ChangeReplace,
                #tab_changes_content tbody.ChangeInsert,
                #tab_changes_content tbody.ChangeDelete {background-color: yellow;}
                #tab_changes_content tbody.ChangeReplace ins,
                #tab_changes_content tbody.ChangeInsert ins,
                #tab_changes_content tbody.ChangeDelete ins {color: red; text-decoration: initial;}
                #tab_changes_content tbody.ChangeReplace del,
                #tab_changes_content tbody.ChangeInsert del,
                #tab_changes_content tbody.ChangeDelete del {color: blue; text-decoration: initial;}
            </style>

    		<div id="tab_changes_content">
    			<?php if ($item && $item->canDisplayDetails()) { ?>
                    <h3><?php echo $title; ?></h3>
    				<table id="changes_table" style="width: 100%;">
    					<thead>
    						<tr>
    							<th><?php echo KT_I18N::translate('Timestamp'); ?></th>
    							<th><?php echo KT_I18N::translate('User'); ?></th>
    							<th><?php echo KT_I18N::translate('GEDCOM Data'); ?></th>
    							<th><?php echo KT_I18N::translate('Status'); ?></th>
    						</tr>
    					</thead>
    					<tbody>
                            <?php foreach($rows as $row) { ?>
						        <tr>
        							<td><?php echo $row->change_time; ?></td>
        							<td><?php echo $row->user_name; ?></td>
        							<td><?php echo $row->old_gedcom; ?></td>
        							<td><?php echo $row->status; ?></td>
						        </tr>
                            <?php } ?>
    					</tbody>
    				</table>
    			<?php } ?>
    		</div>
		<?php } else { ?>
            <div> <?php echo KT_I18N::translate('No change data available'); ?></div>
        <?php }
	}

	// Implement KT_Module_Tab
	public function hasTabContent() {
		return KT_USER_CAN_EDIT || $this->getChangeList();
	}

    // Implement KT_Module_Tab
	public function isGrayedOut() {
        return count($this->getChangeList()) == 0;
	}

	// Implement KT_Module_Tab
	public function canLoadAjax() {
		return false;
	}

	// Implement KT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	private function getChangeList($xref = '') {
        $sql =
        	"SELECT *, `user_name` FROM `##change`
             LEFT JOIN `##user` USING (user_id)
             WHERE `xref` LIKE ?
             AND `gedcom_id` = ?
             ORDER BY `change_id` DESC";
         $rows = KT_DB::prepare($sql)->execute(array($xref, KT_GED_ID))->fetchAll();

		return $rows;
	}

}
