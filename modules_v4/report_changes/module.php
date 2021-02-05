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

class report_changes_KT_Module extends KT_Module implements KT_Module_Report {

  // Extend class KT_Module
  public function getTitle() {
    return /* I18N: Name of a module. Tasks that need further research. */ KT_I18N::translate('Changes');
  }

  // Extend class KT_Module
  public function getDescription() {
    return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A report of recent and pending changes.');
  }

  // Extend KT_Module
  public function modAction($mod_action) {
    switch($mod_action) {
    case 'show':
      $this->show();
      break;
    default:
      header('HTTP/1.0 404 Not Found');
    }
  }

  // Extend class KT_Module
  public function defaultAccessLevel() {
    return KT_PRIV_USER;
  }

  // Implement KT_Module_Report
  public function getReportMenus() {

    $menus  = array();
    $menu  = new KT_Menu(
      $this->getTitle(),
      'module.php?mod=' . $this->getName() . '&mod_action=show',
      'menu-report-' . $this->getName()
    );
    $menus[] = $menu;

    return $menus;
  }

  // Implement class KT_Module_Menu
  public function show() {
    global $controller, $DATE_FORMAT, $GEDCOM;
    require_once KT_ROOT.'includes/functions/functions_print_lists.php';
    require_once KT_ROOT.'includes/functions/functions_edit.php';
    $controller = new KT_Controller_Page();
    $controller
      ->setPageTitle($this->getTitle())
      ->pageHeader();
    init_calendar_popup();

    //Configuration settings ===== //
    $action     = KT_Filter::post('action');
    $set_days   = KT_Filter::post('set_days');
    $pending    = KT_Filter::post('pending','' , 0);
    $reset		= KT_Filter::post('reset');
    $from       = KT_Filter::post('date1');
    $to         = KT_Filter::post('date2');

    $earliest   = KT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();
    $latest     = KT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();

    // reset all variables
    if ($reset == 'reset') {
        $action     = '';
        $set_days   = '';
        $pending    = 0;
        $reset		= '';
        $from       = '';
        $to         = '';
        $earliest   = $earliest;
        $latest     = $latest;
    }

    if (!$set_days){
        $earliest   = $from ? strtoupper(date('d M Y', strtotime($from))) : strtoupper(date('d M Y', strtotime($earliest)));
        $latest     = $to ? strtoupper(date('d M Y', strtotime($to))) : strtoupper(date('d M Y', strtotime($latest)));
        $date1      = new DateTime($earliest);
        $date2      = new DateTime($latest);
        $days       = $date1->diff($date2)->format("%a") + 1;
        $from_disp  = new KT_Date($earliest);
        $to_disp    = new KT_Date($latest);
    } else {
        $days = $set_days;
    }

    if($action == 'go') {
        if ($pending) {
            $rows = KT_DB::prepare(
              "SELECT xref, change_time, IFNULL(user_name, '<none>') AS user_name".
              " FROM `##change`" .
              " LEFT JOIN `##user` USING (user_id)" .
              " WHERE status='pending' AND gedcom_id=?"
            )->execute(array(KT_GED_ID))->fetchAll();
            $pending_changes = array();
            foreach ($rows as $row) {
              $pending_changes[] = $row;
            }
        }

        // find changes in database
        if ($set_days){
            $sql = "SELECT xref, change_time, IFNULL(user_name, '<none>') AS user_name"
                . " FROM `##change`"
                . " LEFT JOIN `##user` USING (user_id)"
                . " WHERE status='accepted' AND gedcom_id=" . KT_GED_ID
                . " AND `change_time` BETWEEN DATE_ADD(NOW(), INTERVAL - {$set_days} DAY) AND DATE(NOW())";
        } else {
            $sql = "SELECT xref, change_time, IFNULL(user_name, '<none>') AS user_name"
                . " FROM `##change`"
                . " LEFT JOIN `##user` USING (user_id)"
                . " WHERE status='accepted' AND gedcom_id=" . KT_GED_ID
                . " AND `change_time` BETWEEN '" . date('Y-m-d', strtotime($earliest)) . "' AND '" . date('Y-m-d', strtotime($latest . ' + 1 day')) . "'";
        }
        $recent_changes = KT_DB::prepare($sql)->execute()->fetchAll();
    }

    // Prepare table headers and footers
    $table_header = '
        <div class="loading-image">&nbsp;</div>
        <table class="changes width100" style="visibility:hidden;">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>' . KT_I18N::translate('Record') . '</th>
                    <th>' . KT_Gedcom_Tag::getLabel('CHAN') . '</th>
                    <th>' . KT_I18N::translate('Username') . '</th>
                    <th>DATE</th>
                    <th>SORTNAME</th>
                </tr>
            </thead>
            <tbody>
    ';

    $table_footer = '
      </tbody></table>
    ';

    // Common settings
    $content = '
    <div id="page" class="recent_changes">
        <h2>' . $this->getTitle() . '</h2>
        <div class="help_text">
            <div class="help_content">
                <h5>' . $this->getDescription() . '</h5>
                <a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
                <div class="hidden">
                    ' . /* I18N: help for resource facts and events module */ KT_I18N::translate('View a list of data changes to the current family tree  based on <b>either</b> a range of dates <b>or</b> a number of days up to and including today. If you enter one or more dates plus a number of days, the dates will be ignored.<br>If <b>Show pending changes</b> is selected these will <u>all</u> be shown regardless of date or day settings.') . '
                </div>
            </div>
        </div>
        <div class="noprint">
          <form name="changes" id="changes" method="post" action="module.php?mod=' . $this->getName() . '&mod_action=show">
            <input type="hidden" name="action" value="go">
            <div class="chart_options">
              <label for = "DATE1">' . KT_I18N::translate('Starting range of change dates') . '</label>
              <input type="text" name="date1" id="DATE1" value="' . ($set_days ? '' : $earliest) . '" onblur="valid_date(this);" onmouseout="valid_date(this);">' . print_calendar_popup("DATE1") . '
            </div>
            <div class="chart_options">
              <label for = "DATE2">' . KT_I18N::translate('Ending range of change dates') . '</label>
              <input type="text" name="date2" id="DATE2" value="' . ($set_days ? '' : $latest) . '" onblur="valid_date(this);" onmouseout="valid_date(this);">' . print_calendar_popup("DATE2") . '
            </div>
            <div class="chart_options">
              <label for = "DAYS">' . KT_I18N::translate('Number of days to show') . '</label>
              <input type="text" name="set_days" id="DAYS" value="' . ($set_days ? $set_days : '') . '">
            </div>
            <div class="chart_options">
            <label>' . KT_I18N::translate('Show pending changes') . '</label>' .
              edit_field_yes_no('pending', $pending) .'
            </div>
            <button class="btn btn-primary show" type="submit">
              <i class="fa fa-eye"></i>' . KT_I18N::translate('show') . '
            </button>
            <button class="btn btn-primary" type="submit" name="reset" value="reset">
                <i class="fa fa-refresh"></i>' . KT_I18N::translate('reset') . '
            </button>
          </form>
        </div>
        <hr style="clear:both;">
    ';

    if ($action == "go") {
      $controller
        ->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
        ->addExternalJavascript(KT_JQUERY_DT_HTML5)
        ->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
        ->addInlineJavascript('
          jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
          jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
          jQuery(".changes").dataTable({
            dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
            ' . KT_I18N::datatablesI18N() . ',
            buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
            autoWidth: false,
            paging: true,
            pagingType: "full_numbers",
            lengthChange: true,
            filter: true,
            info: true,
            jQueryUI: true,
            sorting: [[4,"desc"], [5,"asc"]],
            displayLength: 20,
            "aoColumns": [
              /* 0-Type */     {"bSortable": false, "sClass": "center"},
              /* 1-Record */   {"iDataSort": 5},
              /* 2-Change */   {"iDataSort": 4},
              /* 3-User */       null,
              /* 4-DATE */     {"bVisible": false},
              /* 5-SORTNAME */ {"sType": "unicode", "bVisible": false}
			],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: 300

          });
          jQuery(".changes").css("visibility", "visible");
          jQuery(".loading-image").css("display", "none");
        ');
      // Print pending changes
      if ($pending) {
        $content .= '<h3>' . KT_I18N::translate('Pending changes') . '</h3>';
        if ($pending_changes) {
          // table headers
          $content .= $table_header;
          //-- table body
          $content .= $this->change_data($pending_changes);
          //-- table footer
          $content .= $table_footer;
        } else {
          $content .= KT_I18N::translate('There are no pending changes.');
        }
        $content .= '<hr style="clear:both;">';
      }
      // Print approved changes
      if ($recent_changes) {
        $content .= '
            <h3>' . KT_I18N::translate('Recent changes') . '</h3>
            <h3>' .
                ($set_days ? KT_I18N::plural('Changes in the last day', 'Changes in the last %s days', $set_days, KT_I18N::number($set_days)) : KT_I18N::translate('%1$s - %2$s (%3$s days)', $from_disp->Display(), $to_disp->Display(), KT_I18N::number($days))) . '
            </h3>';
        // table headers
        $content .= $table_header;
        //-- table body
        $content .= $this->change_data($recent_changes);
        //-- table footer
        $content .= $table_footer;
      } else {
        $content .= KT_I18N::translate('There have been no changes within the last %s days.', KT_I18N::number($days));
      }
    }
    $content .= '</div>';

    echo $content;
  }

  private function change_data ($type) {
    $change_data = '';

    foreach ($type as $change_id) {
      $record = KT_GedcomRecord::getInstance($change_id->xref);
      if (!$record || !$record->canDisplayDetails()) {
        continue;
      }
      $change_data .= '
        <tr>
          <td>';
            $indi = false;
            switch ($record->getType()) {
              case "INDI":
                $icon = $record->getSexImage('small', '', '', false);
                $indi = true;
                break;
              case "FAM":
                $icon = '<i class="icon-button_family"></i>';
                break;
              case "OBJE":
                $icon = '<i class="icon-button_media"></i>';
                break;
              case "NOTE":
                $icon = '<i class="icon-button_note"></i>';
                break;
              case "SOUR":
                $icon = '<i class="icon-button_source"></i>';
                break;
              case "REPO":
                $icon = '<i class="icon-button_repository"></i>';
                break;
              default:
                $icon = '&nbsp;';
                break;
            }
            $change_data .= '<a href="'. $record->getHtmlUrl() .'">'. $icon . '</a>
          </td>';
          //-- Record name(s)
          $name = $record->getFullName();
          $change_data .= '<td class="wrap">
            <a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
            if ($indi) {
              $change_data .= '<p>' . $record->getLifeSpan() . '</p>';
              $addname = $record->getAddName();
              if ($addname) {
                $change_data .= '
                  <div class="indent">
                    <a href="'. $record->getHtmlUrl() .'">'. $addname . '</a>
                  </div>';
              }
            }
          $change_data .= '</td>';
          //-- Last change date/time
          $change_data .= '<td class="wrap">' . $change_id->change_time . '</td>';
          //-- Last change user
          $change_data .= '<td class="wrap">' . $change_id->user_name . '</td>';
          //-- change date (sortable) hidden by datatables code
          $change_data .= '<td>' . $change_id->change_time . '</td>';
          //-- names (sortable) hidden by datatables code
          $change_data .= '<td>' . $record->getSortName() . '</td>
        </tr>
      ';
    }
    return $change_data;
  }

}
