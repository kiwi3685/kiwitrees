<?php
// Plugin for calendar_utilities module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team. All rights reserved.
//
// Modifications Copyright (c) 2010 Greg Roach
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

$plugin_name = "Calculators"; // need double quotes, as file is scanned/parsed by script

// DISPLAY
$html .= '
<style type="text/css">.result {color:blue;font-weight: 900;}
	#utility_tools{min-height:280px;width: 100%;margin: auto;text-align: center;}
	#utility_tools h3{text-align:center;}
	#utility_tools h3.header{background-color:#d3d3d3;border-bottom:1px solid #c9c9c9;margin-top:0;padding:3px 0;border-top-left-radius: 8px;border-top-right-radius:8px;font-weight:900;font-size:12px;text-align:left;}
	#utility_tools h3 span{padding:0 5px;}
	.utility{background-color: #DDD; border:1px solid #c0c0c0;border-radius:8px;height:200px;margin:10px;display:inline-block;width:31%;min-width: 380px;overflow: hidden;}
	.utility table{margin:auto;}
	.utility label{margin:0 10px;}
	#days .button {cursor: pointer;font-size: 80%;}
	#relationships .result{margin:0 20px;padding:2px 5px;width:250px;}
	#relationships td{padding:5px;}
	#dob_calc p.main {width:300px; margin:20px auto;}
	#dob_calc p.main span {margin-right:20px;}
	#dob_calc label {margin:0 40px 0 0;}
	#dob_calc label.age_part {margin:0 15px 0 0;}
	#dob_calc input {padding:3px;}
	.age_part {width:20px;}
	.icon-button_bday {display:inline-block; background-image:url(http://our-families.info/themes/simpl_grey/images/silk-sprite.png); background-color:transparent;background-repeat:no-repeat;margin:0 2px;vertical-align:middle; height:16px;width:16px;background-position:-64px -32px}
</style>
<div id="utility_tools">';
// UTILITY 1 - DAY OF THE WEEK -->
$html .= '<div class="utility" id="days">
<h3 class="header"><span>Day of the Week Calculator</span></h3>';
?>
<!-- SCRIPTS -->
<script>
<!-- Original:  Abraham I. (abraham_824@hotmai.com) -->
<!-- Idea:  Peter Bonnett (PeterBonnett@hotmail.com) -->
<!-- This script and many more are available free online at -->
<!-- The JavaScript Source!! http://javascript.internet.com -->
<!-- Begin
var months = new Array("January","February","March","April","May","June","July","August","September","October","November","December");
var days = new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
var mtend = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var opt = new Array("Past","Future");
function getDateInfo() {
var y = document.form.year.value;
var m = document.form.month.options[document.form.month.options.selectedIndex].value;
var d = document.form.day.options[document.form.day.options.selectedIndex].value;
var hlpr = mtend[m];
if (d < mtend[m] + 1) {
if (m == 1 && y % 4 == 0) { hlpr++; }
var c = new Date(y,m,d);
var dayOfWeek = c.getDay();
document.form.dw.value = days[dayOfWeek];
if(c.getTime() > new Date().getTime()) {
document.form.time.value = opt[1];
}
else {
document.form.time.value = opt[0];
   }
}
else {
alert("The date "+months[m]+" "+d+", "+y+" is invalid.\nCheck it again.");
   }
}
function setY() {
var y = new Date().getYear();
if (y < 2000) y += 1900;
document.form.year.value = y;
}
//  End -->
</script>
<?php
$html .= '
<form name="form">&nbsp;</form>

<table>
	<tbody>
		<tr>
			<td valign="top">
			<p><label for="day">Day</label> <select id="day" name="day" size="1"><option selected="selected" value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option> </select> <label for="month">Month</label> <select id="month" name="month" size="1"><option selected="selected" value="0">January</option><option value="1">February</option><option value="2">March</option><option value="3">April</option><option value="4">May</option><option value="5">June</option><option value="6">July</option><option value="7">August</option><option value="8">September</option><option value="9">October</option><option value="10">November</option><option value="11">December</option> </select> <label for="year">Year</label> <input id="year" name="year" size="4" type="text" /></p>

			<h3><input class="button" name="gdi" onclick="getDateInfo()" type="button" value="Get Date" /></h3>

			<p><label for="dow">Day of the week</label><input class="result" id="dow" name="dw" size="12" type="text" /> <label for="time">Time</label><input class="result" id="time" name="time" size="10" type="text" /></p>
			</td>
		</tr>
	</tbody>
</table>
</div>';
// UTILITY 2 - RELATIONSHIP CALCULATOR
$html .= '
<div class="utility" id="relationships">
<h3 class="header"><span>Relationship Calculator</span></h3>

<form action="" method="post" name="generations">
<table>
	<tbody>
		<tr>
			<td colspan="3">Given a common blood ancestor, A....</td>
		</tr>
		<tr>
			<td>If you are the</td>
			<td><input name="yores" type="text" value="" /> <input onclick="incGen(1)" type="button" value="+" /> <input onclick="decGen(1)" type="button" value="-" /></td>
			<td>of A and</td>
		</tr>
		<tr>
			<td>D is the</td>
			<td><input name="thares" type="text" value="" /> <input onclick="incGen(2)" type="button" value="+" /> <input onclick="decGen(2)" type="button" value="-" /></td>
			<td>of A,</td>
		</tr>
		<tr>
			<td colspan="3">then<input class="result" name="therelation" type="text" value="" /></td>
		</tr>
	</tbody>
</table>
</form>';
?>
<script language="JavaScript"> 
  var generationsData = new Object();
  generationsData.genYou = 0;
  generationsData.genD = 0;
  generationsData.genArray = new Array(3);
  generationsData.genArray[0] = "Child";
  generationsData.genArray[1] = "Grandchild";
  generationsData.genArray[2] = "Great grandchild";

  function initialBuild()
  { 
    updateDisplays(); // initialize form text contents
  }

  function popupTable(yores, thares)
  {
     while( generationsData.genYou < yores )
        incGen(1);

     while( generationsData.genD < thares )
        incGen(2);

     generationsData.genYou = yores;
     generationsData.genD = thares;

     updateDisplays();
  }
  
  function numSuffix(n)
  {
    var numString = " " + n;
    var retStr = "th"; // default return
	    
    if(numString.length > 0)
    {	    
       if( numString.match(/11$/)
            || numString.match(/12$/)
            || numString.match(/13$/)
         )
            retStr = "th";
       else if( numString.charAt(numString.length - 1) == '1' )
            retStr = "st";
       else if( numString.charAt(numString.length - 1) == '2' )
            retStr = "nd";
       else if( numString.charAt(numString.length - 1) == '3' )
            retStr = "rd";
    }
	       
   return n + retStr;
  }

  function generationDisplay(genNumber)
  {
     var lgth = generationsData.genArray.length;
     if( genNumber >= lgth )
     {
       for(var ix=lgth; ix<genNumber+1; ix++)
         ///
         // Increase array size and init values
         ///
         generationsData.genArray[ix] = numSuffix(ix -1) + " " 
                     + generationsData.genArray[2];
     }
    return( generationsData.genArray[genNumber] );
  }

  function relationshipDisplay(genAsker, genComp)
  {
    var theirrelation = "You and D are siblings";
    var t1=genAsker; // your generation
    var t2=genComp;  // generation to compare
    ///
    //  return the relationship display for the two generations
    ///
        if( t1 == t2 )
        {
                if( t1 == 0 ) // both 1st gen
                {
		            theirrelation="You and D are Siblings";
		}
                else
                {
                    theirrelation = "You and D are " 
                           + numSuffix(t1) + " Cousins";
                }
        }
        else if( t1 == 0 && t2 > 0 )
        {
                var grandind="";
                if( t2 == 2 )
                {
                    grandind="Grand";
                }
                else if( t2 == 3 )
                {
                    grandind="Great Grand";
                }
                else if( t2 > 3 )
                {
                    grandind=  numSuffix(t2 - 2) + " Great Grand";
                }
          
                theirrelation="D is your " + grandind + " niece/nephew";
                    
        }
        else if( t1 > 0 && t2 == 0 )
        {
                var grandind="";
                if( t1 == 2 )
                {
                    grandind="Grand";
                }
                else if( t1 == 3 )
                {
                    grandind="Great Grand";
                }
                else if( t1 > 3 )
                {
                    grandind = numSuffix(t1 - 2) + " Great Grand";
                }
          
                theirrelation="You are the " + grandind + " niece/nephew of D";
        }
        else
        {
            var lesser = 1;
            var removed = 0;
            if( t1 > t2 )
            {
                lesser = t2;
                removed = t1 - t2;
            }
            else
            {
                lesser = t1;
                removed = t2 - t1;
            }
              
            if( removed > 0 )
            {
                theirrelation = "You and D are " 
                     + numSuffix(lesser) + " cousins " + removed; 

                if( removed == 1 )
                    theirrelation += " time removed";
                else
                    theirrelation += " times removed";
            }
            else
                theirrelation="You and D are " + numSuffix(lesser)
                    + " cousins";
        }
     return theirrelation;
  }

  function updateDisplays()
  {
    var t1 = generationsData.genYou;
    var t2 = generationsData.genD;

    document.generations.yores.value = generationDisplay(t1);
    document.generations.thares.value = generationDisplay(t2);
    document.generations.therelation.value =
         relationshipDisplay(t1, t2);
  }

  function incGen(gen)
  {
     var lgth = generationsData.genArray.length;
     if( gen == 1 )
        generationsData.genYou++;
     else
        generationsData.genD++;

     if( generationsData.genYou == lgth ||
         generationsData.genD == lgth)
     {
         ///
         // Increase array size and init value
         ///
         generationsData.genArray[lgth] = numSuffix(lgth -1) + " " 
                     + generationsData.genArray[2];
     }
     updateDisplays();
  }

  function decGen(gen)
  {
     if( gen == 1 && generationsData.genYou > 0 )
          generationsData.genYou--;
 
     if( gen == 2 && generationsData.genD > 0 )
          generationsData.genD--;

     updateDisplays();
  }

</script><script language="JavaScript">
	  initialBuild();
</script>
<?php
// close UTILITY 2 -->
// UTILITY 3 - DATE OF BIRTH CALCULATOR -->
$html .= '
</div>

<div class="utility" id="dob_calc">';
?>
<script>
	var eventDate;
	var yy, mm, dd;
	var dlm = "/";

	function initPage() {
		setEventDate();
		setAgeYY();
		setAgeMM();
		setAgeDD();
		doCalc();
	}

	function setEventDateGui() {
		setEventDate();
		doCalc();
	}

	function setAgeYYGui() {
		setAgeYY();
		doCalc();
	}

	function setAgeMMGui() {
		setAgeMM();
		if (theForm.ageYY.value == null) {
			theForm.ageYY.value = 0;
			setAgeYY();
		}
		doCalc();
	}

	function setAgeDDGui() {
		setAgeDD();
		if (theForm.ageMM.value == null) {
			theForm.ageMM.value = 0;
			setAgeMM();
		}
		doCalc();
	}


	function setEventDate() {
		var dateFields = theForm.eventDate.value.split(dlm);
		for (i=0; i<dateFields.length; i++) {
			if (isNaN(parseInt(dateFields[i]))) {
				i = 4;
			}
		}
		if (i != 3) {
			Alert ("Please enter a valid date.");
		} else {
			eventDate = newDate(dateFields[0], dateFields[1]-1, dateFields[2]);
			theForm.eventDate.value = dateToStr(eventDate);
		}
	}

	function setAgeYY() {
		var value = theForm.ageYY.value;
		yy = toInt(value);
		theForm.ageYY.value = yy;
		doCalc();
	}

	function setAgeMM() {
		var value = theForm.ageMM.value;
		mm = toInt(value);
		theForm.ageMM.value = mm;
		doCalc();
	}

	function setAgeDD() {
		var value = theForm.ageDD.value;
		dd = toInt(value);
		theForm.ageDD.value = dd;
		doCalc();
	}

	function doCalc() {
		var result = "";
		if (dd != null) {
			var resD = eventDate.getDate() - dd;
			var resM = eventDate.getMonth() - mm;
			var resY = eventDate.getFullYear() - yy;
			var resDt = newDate(resD, resM, resY);
			result = dateToStr(resDt);
		} else if (mm != null) {
			var resM = eventDate.getMonth() - mm;
			if (eventDate.getDate() < 16) {
				resM = resM - 1;
			}
			var resY = eventDate.getFullYear() - yy;
			var resDt = newDate(1, resM, resY);
			result = "ABT " + dateToMonthStr(resDt);
		} else if (yy != null) {
			var resM = eventDate.getMonth() + 1;
			var resY = eventDate.getFullYear() - yy;
			var resDt1 = newDate(1, resM, resY - 1);
			var resDt2 = newDate(1, resM - 1, resY); // Berts version has var resDt2 = newDate(1, resM - 2, resY); which I disagree with
			result = dateToMonthStr(resDt1) + " - " + dateToMonthStr(resDt2);
		} else {
			result = dateToStr(eventDate);
		}
		showResult.innerHTML = result;
	}

	function toInt(value) {
		var newVal = parseInt(value);
		if (isNaN(newVal)) {
			newVal = null;
		}
		return newVal;
	}

	function newDate(d, m, y) {
		var result = new Date();
		result.setFullYear(y);
		result.setDate(d);
		result.setMonth(m);
		return result;
	}

	function dateToStr(value) {
		return format99(value.getDate()) + dlm + format99(value.getMonth()+1) + dlm + value.getFullYear();
	}

	function format99(value) {
		if (value < 10) {
			return "0" + value;
		} else {
			return value;
		}
	}

	function dateToMonthStr(value) {
		return monthName(value.getMonth()) + " " + value.getFullYear();
	}

	function monthName(value) {
		if (value == 0) return "JAN";
		else if (value == 1) return "FEB";
		else if (value == 2) return "MAR";
		else if (value == 3) return "APR";
		else if (value == 4) return "MAY";
		else if (value == 5) return "JUN";
		else if (value == 6) return "JUL";
		else if (value == 7) return "AUG";
		else if (value == 8) return "SEP";
		else if (value == 9) return "OCT";
		else if (value == 10) return "NOV";
		else if (value == 11) return "DEC";
		else return "???";
	}
</script>
<?php
$html .= '
	<h3 class="header"><span>Birth Date Calculator</span></h3>

	<form name="theForm">
	<p class="main"><label for="">Event Date:</label> <input id="eventDate" name="eventDate" onchange="setEventDateGui()" placeholder="31/12/1905" size="10" type="text" /></p>

	<p class="main"><label>Age:</label> <input class="age_part" id="ageYY" name="ageYY" onchange="setAgeYYGui()" placeholder="27" size="2" type="text" /> <label for="">yrs</label> <input class="age_part" id="ageMM" name="ageMM" onchange="setAgeMMGui()" placeholder="0" size="2" type="text" /> <label for="">mths</label> <input class="age_part" id="ageDD" name="ageDD" onchange="setAgeDDGui()" placeholder="0" size="2" type="text" /> <label for="">days</label></p>

	<p class="main"><span>Estimated DoB:</span></p>
	</form>
	</div>

	</div>
';