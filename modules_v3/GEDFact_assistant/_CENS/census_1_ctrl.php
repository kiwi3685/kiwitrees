<?php
// Census Assistant Control
//
// Census information about an individual
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

 if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $summary, $censyear, $censdate;

$pid        = safe_get('pid');
$censdate   = new WT_Date('31 MAR 1901');
$censyear   = $censdate->date1->y;
$ctry       = 'UK';

// $married = WT_Date::Compare($censdate, $marrdate);
// === Set $married to "Not married as we only want the Birth name here" ===
$married    = -1;

$person     = WT_Person::getInstance($pid);
$nam        = $person->getAllNames();
if ($person->getDeathYear() == 0) { $DeathYr = ''; } else { $DeathYr = $person->getDeathYear(); }
if ($person->getBirthYear() == 0) { $BirthYr = ''; } else { $BirthYr = $person->getBirthYear(); }
$fulln      = rtrim($nam[0]['givn'],'*') . " " . $nam[0]['surname'];
$fulln      = str_replace('@N.N.', '(' . WT_I18N::translate('unknown') . ')', $fulln);
$fulln      = str_replace('@P.N.', '(' . WT_I18N::translate('unknown') . ')', $fulln);
$wholename  = $fulln;
$currpid    = $pid;

$controller = new WT_Controller_Page();
$controller
    ->setPageTitle(WT_I18N::translate('Create a new Shared Note using Assistant'))
    ->pageHeader();

if (isset($_REQUEST['pid'])) $pid = $_REQUEST['pid'];
global $pid;
?>

<script src="<?php echo WT_STATIC_URL . WT_MODULES_DIR . 'GEDFact_assistant/_CENS/js/dynamicoptionlist.js'; ?>"></script>
<script src="<?php echo WT_STATIC_URL . WT_MODULES_DIR . 'GEDFact_assistant/_CENS/js/date.js'; ?>"></script>
<script>
    var TheCenYear = opener.document.getElementById("setyear").value;
    var TheCenCtry = opener.document.getElementById("setctry").value;
</script>

<div id="census_assist-page">
    <h2><?php echo $controller->getPageTitle(); ?>
        <a class="faq_link" href="http://kiwitrees.net/faqs/modules/census-assistant/" alt="' . WT_I18N::translate('View FAQ for this page.') . '" target="_blank">
            <?php echo WT_I18N::translate('View FAQ for this page.'); ?>
            <i class="fa fa-comments-o"></i>
        </a>
    </h2>
    <div>
        <form method="post" action="edit_interface.php" onsubmit="return check_form(this);">
            <input type="hidden" name="action" value="addnoteaction_assisted">
            <input type="hidden" name="noteid" value="newnote">
            <input id="pid_array" type="hidden" name="pid_array" value="none">
            <input id="pid" type="hidden" name="pid" value=' . $pid . '>
            <!-- Header of assistant window ===================================================== -->
            <div class="cens_header">
                <h3>
                    <?php echo WT_I18N::translate('Head of Household:') . '&nbsp;' . $wholename; ?>
                </h3>
                <?php if ($summary) { ?>
                    <div class="head_summary"><?php echo $summary; ?></div>
                <?php } ?>
            </div>
            <div class="cens_left">
                <!-- Census & Source Information Area =============================================== -->
                <div class="cens_container">
                    <?php require WT_ROOT . WT_MODULES_DIR.'GEDFact_assistant/_CENS/census_2_source_input.php'; ?>
                </div>
                <!-- Census Text Input Area ========================================================= -->
                <div class="cens_textinput">
                	<div class="cens_textinput_left">
                		<input type="button" value="<?php echo WT_I18N::translate('Add/Insert Blank Row'); ?>" onclick="insertRowToTable('', '', '', '', '', '', '', '', 'Age', '', '', '', '', '', '');">
                	</div>
                	<div class="cens_textinput_right">
                		<?php echo WT_I18N::translate('Add'); ?>
                		<input  type="radio" name="totallyrad" value="0" checked="checked">
                	</div>
                	<!--  Census Add Rows Area ================================================== -->
                    <div class="cens_addrows">
                    	<?php require WT_ROOT . WT_MODULES_DIR . 'GEDFact_assistant/_CENS/census_5_input.php'; ?>
                    </div>
                </div>
                <!-- Proposed Census Text Area ============================================== -->
                <div class="cens_result">
                    <span>
                        <?php require WT_ROOT . WT_MODULES_DIR . 'GEDFact_assistant/_CENS/census_4_text.php'; ?>
                    </span>
                </div>
            </div>
            <div class="cens_right">
                <!-- Search  and Add Family Members Area ============================================ -->
                <div class="cens_search">
                        <?php require WT_ROOT . WT_MODULES_DIR . 'GEDFact_assistant/_CENS/census_3_search_add.php'; ?>
                </div>
            </div>
		</form>
	</div>
</div>
<script>
    window.onLoad = initDynamicOptionLists();
</script>
