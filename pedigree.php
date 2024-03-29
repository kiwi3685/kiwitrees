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

define('KT_SCRIPT_NAME', 'pedigree.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Pedigree();
$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

?>
<div id="pedigree-page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
		<!-- print the form to change the number of displayed generations -->
		<form name="people" id="people" method="get" action="?">
			<input type="hidden" name="show_full" value="' . $controller->show_full . '">
			<div class="chart_options">
				<label for = "rootid" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Individual'); ?></label>
					<input class="pedigree_form" data-autocomplete-type="INDI" type="text" id="rootid" name="rootid" value="<?php echo $controller->rootid; ?>">
					<?php echo print_findindi_link('rootid'); ?>
			</div>
			<div class="chart_options">
				<label for = "pedigree_generations" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Generations'); ?></label>
				<select name="PEDIGREE_GENERATIONS" id="pedigree_generations">
					<?php
						for ($p=3; $p<=$MAX_PEDIGREE_GENERATIONS; $p++) {
							echo '<option value="', $p, '"';
							if ($p == $controller->PEDIGREE_GENERATIONS) echo ' selected="selected"';
							echo '>', $p, '</option>';
						}
					?>
				</select>
			</div>
			<div class="chart_options">
				<label for = "talloffset" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Layout'); ?></label>
				<?php
					echo select_edit_control('talloffset', array(0=>KT_I18N::translate('Portrait'), 1=>KT_I18N::translate('Landscape'), 2=>KT_I18N::translate('Oldest at top'), 3=>KT_I18N::translate('Oldest at bottom')), null, $talloffset);
				?>
			</div>
			<div class="chart_options">
				<label for = "showfull" style="display:block; font-weight:900;"><?php echo KT_I18N::translate('Show Details'); ?></label>
					<?php
						echo '<input type="checkbox" id="showfull" value="';
							if ($controller->show_full) echo '1" checked="checked" onclick="document.people.show_full.value=\'0\';';
							else echo '0" onclick="document.people.show_full.value=\'1\';';
						echo '">';
					?>
			</div>
 			<div class="btn btn-primary" style="display: inline-block;">
 				<button type="submit" value="<?php echo KT_I18N::translate('View'); ?>"><?php echo KT_I18N::translate('View'); ?></button>
 			</div>
		</form>
		<hr style="clear:both;">
		<!-- end of form -->
<?php
if ($controller->error_message) {
	echo '<p class="ui-state-error">', $controller->error_message, '</p>';
	exit;
}
?>
<div id="pedigree_chart"<?php echo $controller->PEDIGREE_GENERATIONS <6 ? 'style="left:' . 50 / $controller->PEDIGREE_GENERATIONS . '%"' : ''; ?>>
<?php
//-- echo the boxes
$curgen = 1;
$xoffset = 0;
$yoffset = 0;     // -- used to offset the position of each box as it is generated
$prevxoffset = 0; // -- used to track the horizontal x position of the previous box
$prevyoffset = 0; // -- used to track the vertical y position of the previous box
$maxyoffset = 0;
$lineDrawx = array(); // -- used to position joining lines on <canvas>
$lineDrawy = array(); // -- used to position joining lines on <canvas>

for ($i=($controller->treesize-1); $i>=0; $i--) {
	// set positions for joining lines
	$lineDrawx[$i] = $xoffset;
	$lineDrawy[$i] = $yoffset-200; //200 adjustment necessary to move canvas below menus and options. Matched to similar amount on canvas style.
	// -- check to see if we have moved to the next generation
	if ($i < (int)($controller->treesize / (pow(2, $curgen)))) {
		$curgen++;
	}
	$prevxoffset = $xoffset;
	$prevyoffset = $yoffset;
	if ($talloffset < 2) { // Portrate 0 Landscape 1 top 2 bottom 3
		$xoffset = $controller->offsetarray[$i]["x"];
		$yoffset = $controller->offsetarray[$i]["y"];
	} else {
		$xoffset = $controller->offsetarray[$i]["y"];
		$yoffset = $controller->offsetarray[$i]["x"];
	}
	// -- draw the box
	if ($yoffset>$maxyoffset) {
		$maxyoffset=$yoffset;
	}
	if ($i==0) {
		$iref = rand();
	} else {
		$iref = $i;
	}
	// Can we go back to an earlier generation?
	$can_go_back = $curgen == 1 && KT_Person::getInstance($controller->treeid[$i]) && KT_Person::getInstance($controller->treeid[$i])->getChildFamilies();

	if ($talloffset == 2) { // oldest at top
		echo '<div id="uparrow" dir="';
		if ($TEXT_DIRECTION=="rtl") {echo 'rtl" style="right:';} else {echo 'ltr" style="left:';
		}
		echo ($xoffset+$controller->pbwidth/2), 'px; top:', ($yoffset), 'px;">';
		if ($can_go_back) {
			$did = 1;
			if ($i > (int)($controller->treesize/2) + (int)($controller->treesize/4)) {
				$did++;
			}
			echo '<a href=pedigree.php?PEDIGREE_GENERATIONS=', $controller->PEDIGREE_GENERATIONS, '&amp;rootid=', $controller->treeid[$did], '&amp;show_full=', $controller->show_full, '&amp;talloffset=', $controller->talloffset, ' class="icon-uarrow noprint"></a>';
		}
		echo '</div>';
	}
	// beginning of box setup and display
	echo '<div class="shadow" id="box';
	if (empty($controller->treeid[$i])) {
		echo "$iref";
	} else {
		echo $controller->treeid[$i];
	}
	echo '.1' . $iref;
	if ($TEXT_DIRECTION=="rtl") {echo '" style="right:';} else {echo '" style="left:';}
	//Correct box spacing for different layouts
	if ($talloffset == 2) {$zindex = $PEDIGREE_GENERATIONS-$curgen;} else {$zindex = 0;}
	if (($talloffset == 3) && ($curgen ==1)) {$yoffset +=25;}
	if (($talloffset == 3) && ($curgen ==2)) {$yoffset +=10;}
	echo $xoffset, "px; top:", $yoffset, "px; width:", ($controller->pbwidth), "px; height:", $controller->pbheight, "px; z-index:", $zindex, ";\">";
	if (!isset($controller->treeid[$i])) {$controller->treeid[$i] = false;}
	print_pedigree_person(KT_Person::getInstance($controller->treeid[$i]), 1, $iref, 1);
	if ($can_go_back) {
		$did = 1;
		if ($i > (int)($controller->treesize/2) + (int)($controller->treesize/4)) {
			$did++;
		}
		if ($TEXT_DIRECTION=="rtl") {$posn = 'right'; $arrow = 'icon-larrow';} else {$posn = 'left';	$arrow = 'icon-rarrow';	}
		if ($talloffset==3) {
			echo '<div class="ancestorarrow" style="position:absolute; ',$posn,':', $controller->pbwidth/2, 'px; top:', $controller->pbheight, 'px;">';
				echo '<a href="pedigree.php?PEDIGREE_GENERATIONS=' . $controller->PEDIGREE_GENERATIONS . '&amp;rootid=' . $controller->treeid[$did] . '&amp;show_full=' . $controller->show_full . '&amp;talloffset=' . $controller->talloffset . ' class="icon-darrow noprint"></a>';
			echo '</div>';
		} elseif ($talloffset < 2) {
			echo '<div class="ancestorarrow" style="position:absolute; ',$posn,':', $controller->pbwidth+5, 'px; top:', ($controller->pbheight/2-10), 'px;">';
			echo '<a href="pedigree.php?PEDIGREE_GENERATIONS=' . $controller->PEDIGREE_GENERATIONS . '&amp;rootid=' . $controller->treeid[$did] . '&amp;show_full=' . $controller->show_full . '&amp;talloffset=' . $talloffset . '" class=" ',$arrow,' noprint"></a>';
			echo '</div>';
		}
	}
	echo '</div>';
}
// -- echo left arrow for decendants so that we can move down the tree
$yoffset += ($controller->pbheight / 2)-10;
$famids = $controller->root->getSpouseFamilies();
//-- make sure there is more than 1 child in the family with parents
$cfamids = $controller->root->getChildFamilies();
if (count($famids)>0) {
	echo '<div id="childarrow" dir="';
	if ($TEXT_DIRECTION=='rtl') {
		echo 'rtl" style="right:'; $arrow = 'icon-rarrow';} else {echo 'ltr" style="left:'; $arrow = 'icon-larrow';}
	switch ($talloffset) {
	case 0:
		if ($PEDIGREE_GENERATIONS<6) {
			$addxoffset = 60*(5-$PEDIGREE_GENERATIONS);
		} else {
			$addxoffset = 0;
		}
		echo $addxoffset, 'px; top:', $yoffset, 'px;">';
		echo '<a href="#" onclick="togglechildrenbox(); return false;" class=" ',$arrow,'"></a>';
		break;
	case 1:
		if ($PEDIGREE_GENERATIONS<4) $basexoffset += 60;
		echo $basexoffset, 'px; top:', $yoffset, 'px;">';
		echo '<a href="#" onclick="togglechildrenbox(); return false;" class=" ',$arrow,'"></a>';
		break;
	case 2:
		echo ($xoffset-10+$controller->pbwidth/2), 'px; top:', ($yoffset+$controller->pbheight/2+10), 'px;">';
		echo '<a href="#" onclick="togglechildrenbox(); return false;" class="icon-darrow"></a>';
		break;
	case 3:
		echo ($xoffset-10+$controller->pbwidth/2), 'px; top:', ($yoffset-$controller->pbheight/2-10), 'px;">';
		echo '<a href="#" onclick="togglechildrenbox(); return false;" class="icon-uarrow"></a>';
		break;
	}
	echo '</div>';
	$yoffset += ($controller->pbheight / 2)+10;
	echo '<div id="childbox" dir="';
	if ($TEXT_DIRECTION=='rtl') {echo 'rtl" style="right:';} else {echo 'ltr" style="left:';}
	echo $xoffset, 'px; top:', $yoffset, 'px;">';
	foreach ($famids as $family) {
		$spouse=$family->getSpouse($controller->root);
		if ($spouse) {
			echo "<a href=\"pedigree.php?PEDIGREE_GENERATIONS={$controller->PEDIGREE_GENERATIONS}&amp;rootid=" . $spouse->getXref() . "&amp;show_full={$controller->show_full}&amp;talloffset={$talloffset}\"><span ";
			$name = $spouse->getFullName();
			echo 'class="name1">';
			echo $name;
			echo '<br></span></a>';
		}
		$children = $family->getChildren();
		foreach ($children as $child) {
			echo "&nbsp;&nbsp;<a href=\"pedigree.php?PEDIGREE_GENERATIONS={$controller->PEDIGREE_GENERATIONS}&amp;rootid=" . $child->getXref() . "&amp;show_full={$controller->show_full}&amp;talloffset={$talloffset}\"><span ";
			$name = $child->getFullName();
			echo "class=\"name1\">&lt; ";
			echo $name;
			echo '<br></span></a>';
		}
	}
	//-- echo the siblings
	foreach ($cfamids as $family) {
		if ($family!=null) {
			$children = $family->getChildren();
			if (count($children)>2) {
				echo '<span class="name1"><br>', KT_I18N::translate('Siblings'), '<br></span>';
			}
			if (count($children)==2) {
				echo '<span class="name1"><br>', KT_I18N::translate('Sibling'), '<br></span>';
			}
			foreach ($children as $child) {
				if (!$controller->root->equals($child) && !is_null($child)) {
					echo "&nbsp;&nbsp;<a href=\"pedigree.php?PEDIGREE_GENERATIONS={$controller->PEDIGREE_GENERATIONS}&amp;rootid=" . $child->getXref() . "&amp;show_full={$controller->show_full}&amp;talloffset={$talloffset}\"><span ";
					$name = $child->getFullName();
					echo 'class="name1"> ';
					echo $name;
					echo '<br></span></a>';
				}
			}
		}
	}
	echo '</div>';
}
// calculate canvas width
if ($talloffset < 2) {
	$canvaswidth = $PEDIGREE_GENERATIONS*($controller->pbwidth+20);
} else {
	$canvaswidth = pow(2,$PEDIGREE_GENERATIONS-1)*($controller->pbwidth+20);
}
echo '<canvas id="pedigree_canvas" width="'.(int)($canvaswidth) . '" height="' . (int)($maxyoffset) . '"><p>No lines between boxes? Unfortunately your browser does not support he HTML5 canvas feature.</p></canvas>';
echo '</div>'; //close #pedigree_chart
echo '</div>'; //close #pedigree-page

// Expand <div id="content"> to include the absolutely-positioned elements.
$controller->addInlineJavascript('
	content_div = document.getElementById("content");
	if (content_div) {
		content_div.style.height="' . ($maxyoffset+30) . 'px";
	}

	// Draw joining lines in <canvas>
	// need to be able to read styles from style.css files
	function getStyle(oElm, strCssRule){
		var strValue = "";
		if(document.defaultView && document.defaultView.getComputedStyle){
			strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
		}
		else if(oElm.currentStyle){
			strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
				return p1.toUpperCase();
			});
			strValue = oElm.currentStyle[strCssRule];
		}
		return strValue;
	}
	// Set variables
		var c=document.getElementById("pedigree_canvas");
		var ctx=c.getContext("2d");
		var textdirection = "' . $TEXT_DIRECTION . '";
		var talloffset = ' . $talloffset . ';
		var canvaswidth = ' . ($canvaswidth) . ';
		var offset_x = 20;
		var offset_y = ' . $controller->pbheight . '/2+' . $controller->linewidth . ';
		var lineDrawx = new Array("' . join('","', array_reverse($lineDrawx)) . '");
		var lineDrawy = new Array("' . join('","', array_reverse($lineDrawy)) . '");
		var offset_x2 = ' . $controller->pbwidth . '/2+' . $controller->linewidth . ';
		var offset_y2 = ' . $controller->pbheight . '*2;
		var lineDrawx2 = new Array("' . join('","', $lineDrawx) . '");
		var lineDrawy2 = new Array("' . join('","', $lineDrawy) . '");
		var maxjoins = Math . pow(2,' . $PEDIGREE_GENERATIONS . ');
	//Draw the lines
		if (talloffset < 2) { // landscape and portrait styles
			for (var i = 0; i <= maxjoins-3; i++) {
				if(i%2==0){
					if (textdirection == "rtl") {
						ctx.moveTo(canvaswidth-lineDrawx[i],lineDrawy[i]-0+offset_y+offset_x/2);
						ctx.lineTo(canvaswidth-lineDrawx[i]+offset_x,lineDrawy[i]-0+offset_y+offset_x/2);
						ctx.lineTo(canvaswidth-lineDrawx[i+1]+offset_x,lineDrawy[i+1]-0+offset_y-offset_x/2);
						ctx.lineTo(canvaswidth-lineDrawx[i+1],lineDrawy[i+1]-0+offset_y-offset_x/2);
					} else {
						ctx.moveTo(lineDrawx[i],lineDrawy[i]-0+offset_y+offset_x/2);
						ctx.lineTo(lineDrawx[i]-offset_x,lineDrawy[i]-0+offset_y+offset_x/2);
						ctx.lineTo(lineDrawx[i+1]-offset_x,lineDrawy[i+1]-0+offset_y-offset_x/2);
						ctx.lineTo(lineDrawx[i+1],lineDrawy[i+1]-0+offset_y-offset_x/2);
					}
				}
			}
		}
		if (talloffset == 2) { // oldest at top
			for (var i = 0; i <= maxjoins; i++) {
				if(i%2!=0){
					if (textdirection == "rtl") {
						ctx.moveTo(lineDrawx2[i]-0+offset_x2-offset_x,lineDrawy2[i]);
						ctx.lineTo(lineDrawx2[i]-0+offset_x2-offset_x,lineDrawy2[i]-0+offset_y2);
						ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]-0+offset_y2);
						ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]);
					} else {
						ctx.moveTo(lineDrawx2[i]-0+offset_x2-offset_x/2,lineDrawy2[i]);
						ctx.lineTo(lineDrawx2[i]-0+offset_x2-offset_x/2,lineDrawy2[i]-0+offset_y2);
						ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]-0+offset_y2);
						ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]);
					}
				}
			}
		}
		if (talloffset == 3) { // oldest at bottom
			for (var i = 0; i <= maxjoins; i++) {
				if(i%2!=0){
					ctx.moveTo(lineDrawx2[i]-0+offset_x2-offset_x,lineDrawy2[i]);
					ctx.lineTo(lineDrawx2[i]-0+offset_x2-offset_x,lineDrawy2[i]-offset_y2/2);
					ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]-offset_y2/2);
					ctx.lineTo(lineDrawx2[i+1]-0+offset_x2+offset_x/2,lineDrawy2[i]);
				}
			}
		}
		// Set line styles
		ctx.strokeStyle = getStyle(document.getElementById("pedigree_canvas"), "color");
		ctx.lineWidth = ' . $controller->linewidth . ';
		ctx.shadowColor = "' . $controller->shadowcolor . '";
		ctx.shadowBlur = ' . $controller->shadowblur . ';
		ctx.shadowOffsetX = ' . $controller->shadowoffsetX . ';
		ctx.shadowOffsetY = ' . $controller->shadowoffsetY . ';
		ctx.stroke();
');
