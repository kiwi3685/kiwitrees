<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
  header('HTTP/1.0 403 Forbidden');
  exit;
}

class resource_changes_WT_Module extends WT_Module implements WT_Module_Resources {

  // Extend class WT_Module
  public function getTitle() {
    return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Changes');
  }

  // Extend class WT_Module
  public function getDescription() {
    return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A report of recent and pending changes.');
  }

  // Extend WT_Module
  public function modAction($mod_action) {
    switch($mod_action) {
    case 'show':
      $this->show();
      break;
    default:
      header('HTTP/1.0 404 Not Found');
    }
  }

  // Extend class WT_Module
  public function defaultAccessLevel() {
    return WT_PRIV_USER;
  }

  // Implement WT_Module_Resources
  public function getResourceMenus() {

    $menus  = array();
    $menu  = new WT_Menu(
      $this->getTitle(),
      'module.php?mod=' . $this->getName() . '&mod_action=show',
      'menu-resources-' . $this->getName()
    );
    $menus[] = $menu;

    return $menus;
  }

  // Implement class WT_Module_Menu
  public function show() {
    global $controller, $DATE_FORMAT, $GEDCOM;
    require_once WT_ROOT.'includes/functions/functions_print_lists.php';
    require_once WT_ROOT.'includes/functions/functions_edit.php';
    $controller = new WT_Controller_Page();
    $controller
      ->setPageTitle($this->getTitle())
      ->pageHeader();

    //Configuration settings ===== //
    $action     = WT_Filter::post('action');
    $set_days   = WT_Filter::post('set_days');
    $pending    = WT_Filter::post('pending','' , 0);
    $reset		= WT_Filter::post('reset');
    $from       = WT_Filter::post('date1');
    $to         = WT_Filter::post('date2');

    $earliest   = WT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();
    $latest     = WT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();

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
      $earliest     = $from ? strtoupper(date('d M Y', strtotime($from))) : date('d M Y', strtotime($earliest));
      $latest       = $to ? strtoupper(date('d M Y', strtotime($to))) : date('d M Y', strtotime($latest));
      $date1        = new DateTime($earliest);
      $date2        = new DateTime($latest);
      $days         = $date1->diff($date2)->format("%a") + 1;
    }

    $pending_changes = array();
    if ($pending) {
        $rows = WT_DB::prepare(
          "SELECT xref, change_time, IFNULL(user_name, '<none>') AS user_name".
          " FROM `##change`" .
          " LEFT JOIN `##user` USING (user_id)" .
          " WHERE status='pending' AND gedcom_id=?"
        )->execute(array(WT_GED_ID))->fetchAll();
        $pending_changes = array();
        foreach ($rows as $row) {
          $pending_changes[] = $row;
        }
    }

    // find changes in database
    if ($set_days){
        $arr = array(WT_GED_ID, 'DATE_ADD(NOW(), INTERVAL -' . $set_days . ' DAY)', 'DATE(NOW())');
    } else {
        $arr = array(WT_GED_ID, date('Y-m-d', strtotime($earliest)), date('Y-m-d', strtotime($latest)));
    }
    $rows = WT_DB::prepare(
        "SELECT xref, change_time, IFNULL(user_name, '<none>') AS user_name".
        " FROM `##change`" .
        " LEFT JOIN `##user` USING (user_id)" .
        " WHERE status='accepted' AND gedcom_id=?" .
        " AND change_time BETWEEN ? AND ?"
        )->execute($arr)->fetchAll();
    $recent_changes = array();
    foreach ($rows as $row) {
        $recent_changes[] = $row;
    }

/*    $sql = "SELECT xref, change_time, IFNULL(user_name, \'<none>\') AS user_name\n"
        . " FROM `kt_change`\n"
        . " LEFT JOIN `kt_user` USING (user_id)\n"
        . " WHERE status=\'accepted\' AND gedcom_id=2\n"
        . " AND change_time BETWEEN DATE_ADD(NOW(), INTERVAL -51 DAY) AND DATE(NOW())\n"
        . "ORDER BY `kt_change`.`change_time` DESC";
*/





    // Prepare table headers and footers
    $table_header = '
    earliest = ' . date('Y-m-d', strtotime($earliest)) . '<br>
    latest = ' . date('Y-m-d', strtotime($latest)) . '<br>
      <table class="changes width100">
        <thead>
          <tr>
            <th>&nbsp;</th>
            <th>' . WT_I18N::translate('Record') . '</th>
            <th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
            <th>' . WT_I18N::translate('Username') . '</th>
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
      <div id="resource-page" class="recent_changes" style="margin: auto; width: 90%;">
        <h2>' . $this->getTitle() . '</h2>
        <div class="help_text">
            <div class="help_content">
                <h5>' . $this->getDescription() . '</h5>
                <a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
                <div class="hidden">
                    ' . /* I18N help for resource facts and events module */ WT_I18N::translate('View a list of data changes to the current family tree  based on <b>either</b> a range of dates <b>or</b> a number of days up to and including today. If you enter one or more dates plus a number of days, the dates will be ignored.') . '
                </div>
            </div>
        </div>
        <div class="noprint">
          <form name="changes" id="changes" method="post" action="module.php?mod=' . $this->getName() . '&mod_action=show">
            <input type="hidden" name="action" value="?">
            <div class="chart_options">
              <label for = "DATE1">' . WT_I18N::translate('Starting range of change dates') . '</label>
              <input type="text" name="date1" id="DATE1" value="' . ($set_days ? '' : $earliest) . '">' . print_calendar_popup("DATE1") . '
            </div>
            <div class="chart_options">
              <label for = "DATE2">' . WT_I18N::translate('Ending range of change dates') . '</label>
              <input type="text" name="date2" id="DATE2" value="' . ($set_days ? '' : $latest) . '" pattern="\d\d [a-zA-Z]{3} \d\d\d\d">' . print_calendar_popup("DATE2") . '
            </div>
            <div class="chart_options">
              <label for = "DAYS">' . WT_I18N::translate('Number of days to show') . '</label>
              <input type="text" name="set_days" id="DAYS" value="' . ($set_days ? $set_days : '') . '">
            </div>
            <div class="chart_options">
            <label>' . WT_I18N::translate('Show pending changes') . '</label>' .
              edit_field_yes_no('pending', $pending) .'
            </div>
            <button class="btn btn-primary show" type="submit">
              <i class="fa fa-eye"></i>' . WT_I18N::translate('show') . '
            </button>
            <button class="btn btn-primary" type="submit" name="reset" value="reset">
                <i class="fa fa-refresh"></i>' . WT_I18N::translate('Reset') . '
            </button>
          </form>
        </div>
        <hr style="clear:both;">
    ';

    if (($recent_changes || $pending_changes) && $action) {
      $controller
        ->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
        ->addExternalJavascript(WT_JQUERY_DT_HTML5)
        ->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
        ->addInlineJavascript('
          jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
          jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
          jQuery(".changes").dataTable({
            dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
            ' . WT_I18N::datatablesI18N() . ',
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
            ]
          });
        ');
      // Print pending changes
      if ($pending) {
        $content .= '<h3>' . WT_I18N::translate('Pending changes') . '</h3>';
        if ($pending_changes) {
          // table headers
          $content .= $table_header;
          //-- table body
          $content .= $this->change_data($pending_changes);
          //-- table footer
          $content .= $table_footer;
        } else {
          $content .= WT_I18N::translate('There are no pending changes.');
        }
      }
      // Print approved changes
      if ($recent_changes) {
        $content .= '
          <h3>' .
            ($set_days ? WT_I18N::plural('Changes in the last day', 'Changes in the last %s days', $set_days, WT_I18N::number($set_days)) : WT_I18N::translate('%1$s - %2$s (%3$s days)', $earliest, $latest, WT_I18N::number($days))) . '
          </h3>';
        // table headers
        $content .= $table_header;
        //-- table body
        $content .= $this->change_data($recent_changes);
        //-- table footer
        $content .= $table_footer;
      } else {
        $content .= WT_I18N::translate('There have been no changes within the last %s days.', WT_I18N::number($days));
      }
    }
    $content .= '</div>';

    echo $content;
  }

  private function change_data ($type) {
    $change_data = '';

    foreach ($type as $change_id) {
      $record = WT_GedcomRecord::getInstance($change_id->xref);
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
