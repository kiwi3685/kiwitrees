<?php
echo
	'<!DOCTYPE html>
	<html>
	<head>
	<meta name="robots" content="noindex,nofollow" />
	<link rel="stylesheet" type="text/css" href="style.css">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/overcast/jquery-ui.css"/>
	<script>
	</script>
	</head>
	<body>';

	//set male/female colors
	echo '<b id="fanchart_page_ff"></b>';
	echo '<b id="fanchart_page_mm"></b>';

	echo
	'<div id="fanchart_page">
		<div id="fanchart-title"><span>Fanchart</span>
			<div class="hidden_box noPrint">
					<fieldset>
						<legend>Layout</legend>
						<div id="slider-container">
							<div id="slider-deg"></div>
							<input type="text" id="deg-num">
						</div>
					</fieldset>
					<fieldset>
						<legend>Generations</legend>
						<div id="slider-container">
							<div id="slider-gen"></div>
							<input type="text" id="gen-num">
						</div>
					</fieldset>
					<fieldset>
						<legend>Width</legend>
						<div id="slider-container">
							<div id="slider-rad"></div>
							<input type="text" id="rad-num">
						</div>
					</fieldset>
			</div>
		</div>';

	echo
		'<canvas id="myCanvas">Canvas not supported.</canvas>';
	$cen_x = 700;
	$cen_y = 600;
	echo
		'<div id="chart_text"></div>';

	echo
		'<div id="footer" class="noPrint">
			<div id="footer-container">
				<div id="footer-menu-left">Powered by <span>webtrees&#8482;</span></div>
				<div id="footer-menu-center"><a href="#" onClick="history.back();return false;" title="Back">Back</a> | <a href="index.php" title="Back">Start</a></div>
				<div id="footer-menu-right">For technical support and information contact Greg Roach</div>
			</div>
		</div>';

	echo
	'</div>';// close fanchart_page
?>
    <script>
 		jQuery(document).ready(function() {
			jQuery("#slider-rad").slider({
				range: "min",
				min: 40,
				max: 100,
				value: 75,
				slide: function( event, ui ) {
					drawChart();
					jQuery( "#rad-num" ).val( ui.value );
				}
			});

			jQuery("#slider-gen").slider({
				range: "min",
				min: 2,
				max: 10,
				value: Math.floor(window.innerHeight / (80 * 2.1)),
				slide: function( event, ui ) {
					drawChart();
					jQuery( "#gen-num" ).val( ui.value );
				}
			});

			jQuery("#slider-deg").slider({
				range: "min",
				min: 180,
				max: 360,
				value: 270,
				slide: function( event, ui ) {
					drawChart();
					jQuery( "#deg-num" ).val( ui.value );
				}
			});

			jQuery( "#gen-num" ).val( jQuery( "#slider-gen" ).slider( "value" ) );
			jQuery( "#rad-num" ).val( jQuery( "#slider-rad" ).slider( "value" ) );
			jQuery( "#deg-num" ).val( jQuery( "#slider-deg" ).slider( "value" ) );
			drawChart();
		});

		function drawChart() {
			// standard settings
			var canvas = document.getElementById("myCanvas");
			var context = canvas.getContext("2d");
			context.canvas.width = window.innerWidth - 60;
			context.canvas.height = window.innerHeight - 180;
			var centerX = canvas.width / 2;
			var centerY = canvas.height / 2;
			var radius = jQuery("#slider-rad").slider("value");
			var gen =  jQuery("#slider-gen").slider("value");
			var style = jQuery("#slider-deg").slider("value");
			var start = (360 - style) / 2 + 90;
			var pie = Math.PI / 180;

			// create shadow
			var segments = Math.pow(2, gen - 1);
			var arc = style / segments;
			context.lineWidth = 2;
			context.strokeStyle = "";
			context.fillStyle = "#999";
			context.shadowColor = "#999";
			context.shadowBlur = 15;
			context.shadowOffsetX = 10;
			context.shadowOffsetY = 5;
			context.beginPath();
			context.moveTo(centerX, centerY);
			context.arc(centerX, centerY, radius * gen - 5, start * pie, (start + arc * (segments)) * pie, false);
			context.lineTo(centerX, centerY);
			context.arc(centerX, centerY, radius, start * pie, (start + arc * (segments)) * pie, true);
			context.fill();
			context.shadowBlur = 0;
			context.shadowOffsetX = 0;
			context.shadowOffsetY = 0;

			// add segments
			context.lineWidth = 2;
			context.strokeStyle = "white";
			for ( z = gen; z > 0; z--){
				var segments = Math.pow(2, z - 1);
				var arc = style / segments;
				for(i = 0; i < segments; i++){
					if (z == 1) {
						context.beginPath();
						context.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
					}else{
						context.beginPath();
						context.moveTo(centerX, centerY);
						context.arc(centerX, centerY, radius * z, (start + arc * i) * pie, (start + arc * (i + 1)) * pie, false);
						context.lineTo(centerX, centerY);
					}
					if (i % 2 == 0){
						context.fillStyle = getStyle(document.getElementById("fanchart_page_mm"), "color");
					}else{
						context.fillStyle = getStyle(document.getElementById("fanchart_page_ff"), "color");
					}
					context.fill();
					context.stroke();
				}
			}

			jQuery("#myCanvas").css({
				"position":"absolute",
				"left":"50%",
				"margin-left":-centerX
			})

			jQuery("#chart_text").css({
				"position":"relative",
				"width":centerX * 2,
				"height":centerY * 2,
				"left":"50%",
				"margin-left":-centerX,
				"font-size":radius / 100 * 16 + "px"
			})

			var name = ["1", "2", "3", "4", "John Osborne<br>1929 - 2008", "Barbara Waghorn<br>1927 - 2002", "Nigel Osborne<br>1951 - ", "Barbara Waghorn<br>1927 - 2002"];

			var segments = Math.pow(2, gen)-1;
			var arc = style / Math.pow(2, gen-1);alert(arc);
			var deg = 90;
			for (i = 0; i < segments; i++){
				var deg = deg + arc;
				jQuery("#chart_text").append('<?php echo '<div class="chart_names';?>' + i + '<?php echo '">';?>' + name[i] + '<?php echo '</div>';?>');
				jQuery(".chart_names" + i).css({
					"position":"absolute",
					"text-align":"center",
					"font-size":radius / 100 * 16 + "px",
					"width":radius * 2,
					//these locate the text
					"top":centerY + (radius * i / gen),
					"left":centerX + (radius * i / gen),
					"transform": "rotate("+deg+"deg)"
				});
			}

		};

		//function to draw text on arc
		function drawTextAlongArc(context, str, centerX, centerY, radius, angle){
			context.save();
			context.translate(centerX, centerY);
			context.rotate(-1 * angle / 2);
			context.rotate(-1 * (angle / str.length) / 2);
			for (var n = 0; n < str.length; n++) {
				context.rotate(angle / str.length);
				context.save();
				context.translate(0, -1 * radius);
				var char = str[n];
				context.fillText(char, 0, 0);
				context.restore();
			}
			context.restore();
		}

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

   </script>

</body>
</html>
