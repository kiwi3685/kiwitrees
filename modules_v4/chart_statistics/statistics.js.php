<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

?>
<script>
	// BASIC BAR CHART
	function barChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartStatsBirth":
				var data	= JSON.parse(`<?php echo $stats->statsBirth(); ?>`);
			break;
			case "#chartStatsDeath":
				var data	= JSON.parse(`<?php echo $stats->statsDeath(); ?>`);
			break;
			default:
				var data = "";
		}

		// set the dimensions and margins of the graph
		var margin = {top: 40, right: 30, bottom: 50, left: 80},
			width = 460 - margin.left - margin.right,
			height = 300 - margin.top - margin.bottom;

		// set the ranges
		var x = d3.scaleBand().range([0, width]).padding(0.1);
		var y = d3.scaleLinear().range([height, 0]);

		var svg = d3.select(element).append("svg")
			.attr("preserveAspectRatio", "xMinYMin meet")
			.attr("viewBox", "0 0 460 300")
			.append("g")
				.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		// format the data
			data.forEach(function(d) {
			d.count = +d.count;
		});

		// Scale the range of the data in the domains
		x.domain(data.map(function(d) { return d.category; }));
		y.domain([0, d3.max(data, function(d) { return d.count; })]);

		// append the rectangles for the bar chart
		svg.selectAll(".bar")
			.data(data)
				.enter().append("rect")
					.attr("class", function(d){ return "bar-" +  d.color; })
					.attr("x", function(d) { return x(d.category); })
					.attr("width", x.bandwidth())
					.attr("y", function(d) { return y(d.count); })
					.attr("height", function(d) { return height - y(d.count); });


		// Labels at the top of each bar.
		svg.selectAll(".text")
			.data(data)
				.enter().append("text")
					.attr("class","barLabel")
					.attr("x", (function(d) { return x(d.category) + (x.bandwidth() / 2) ; }  ))
					.attr("y", function(d) { return y(d.count) - 8; })
					.text(function(d) { return d.percent; });

		// Add the X Axis
		svg.append("g")
			.attr("transform", "translate(0," + height + ")")
			.call(d3.axisBottom(x));
		// Text label for the x axis
	    svg.append("text")
	        .attr("transform", "translate(" + (width / 2) + " ," + (height + margin.top) + ")")
			.attr("class","axisLabel")
	        .text("<?php echo KT_I18N::translate('Century'); ?>");

		// Add the Y Axis
		svg.append("g")
			.call(d3.axisLeft(y).ticks(5));
		// Text label for the y axis
	    svg.append("text")
	        .attr("transform", "rotate(-90)")
	        .attr("y", 0 - margin.left * 0.67)
	        .attr("x",0 - (height / 2 + 10))
			.attr("class","axisLabel")
	        .text("<?php echo KT_I18N::translate('Count'); ?>");

	}

	// PIE CHART
	function pieChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartSex":
				var data = JSON.parse(`<?php echo $stats->chartSex(); ?>`);
			break;
			case "#chartMortality":
				var data = JSON.parse(`<?php echo $stats->chartMortality(); ?>`);
			break;
			case "#chartCommonSurnames":
				var data = JSON.parse(`<?php echo $stats->chartCommonSurnames(array(25,10)); ?>`);
			break;
			case "#chartCommonGiven":
				var data = JSON.parse(`<?php echo $stats->chartCommonGiven(array(0,10)); ?>`);
			break;
			default:
				var data = "";
		}
		var text	= "";
		var width	= 200;
		var height	= 200;
		var padding	= 50;
		var radius	= Math.min(width-padding, height-padding) / 2;
		var color = d3.scaleOrdinal(d3.schemeCategory20);

		var svg = d3.select(element)
			.append("svg")
				.attr("width", '100%')
				.attr("height", '100%')
				.attr("viewBox", "0 0 200 200")

		var g = svg.append("g")
			.attr("transform", "translate(" + (width / 2) + "," + (height / 2) + ")");

		var arc = d3.arc()
			.innerRadius(0)
			.outerRadius(radius);

		var pie = d3.pie()
			.value(function(d) { return d.count; })
			.sort(null);

		var path = g.selectAll("path")
			.data(pie(data))
				.enter().append("g")
					.append("path")
					.attr("fill", function(d, i) { return color(i); } )
					.attr("class", function(d){ return "bar-" +  d.data.color; }) // css over-rides fill color if d.data.color exists
					.attr("d", arc);

		// Add labels
		g.selectAll(".text")
			.data(pie(data))
				.enter().append("text")
				.each(function(d) {
					if (d.data.percent == "0.0%") {
						var category	= "";
						var count		= "";
					} else {
						var category	= d.data.category;
						var count		= " (" + d.data.count + ")";
					}
					d3.select(this)
						.attr("class", "pieLabel")
						.attr("transform", function(d) {
						return "translate(" + ( (radius + 10) * Math.sin( ((d.endAngle - d.startAngle) / 2) + d.startAngle ) ) + "," + ( -1 * (radius + 10) * Math.cos( ((d.endAngle - d.startAngle) / 2) + d.startAngle ) ) + ")"; })
						.style("text-anchor", function(d) {
							var rads = ((d.endAngle - d.startAngle) / 2) + d.startAngle;
							if ( (rads > 7 * Math.PI / 4 && rads < Math.PI / 4) || (rads > 3 * Math.PI / 4 && rads < 5 * Math.PI / 4) ) {
								return "middle";
							} else if (rads >= Math.PI / 4 && rads <= 3 * Math.PI / 4) {
								return "start";
							} else if (rads >= 5 * Math.PI / 4 && rads <= 7 * Math.PI / 4) {
								return "end";
							} else {
								return "middle";
							}
						})
						.text(category + count);
				});
	}

	// GROUPED BAR CHART
	function groupChart(element) {
		var element	= "#" + element;
		switch(element) {
			case "#chartStatsAge":
				var data	= JSON.parse(`<?php echo $stats->statsAge(); ?>`);
			break;
			default:
				var data = "";
		}

		w = 960;
		h = 320;
		var svg = d3.select(element).append("svg"),
			margin = {top: 40, right: 20, bottom: 50, left: 60},
			width = w - margin.left - margin.right,
			height = h - margin.top - margin.bottom,
			g = svg
				.attr("width", w)
				.attr("height", h)
				.append("g")
					.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

		// The scale spacing the groups:
		var x0 = d3.scaleBand().rangeRound([0, width]).paddingInner(0.1);
		// The scale for spacing each group's bar:
		var x1 = d3.scaleBand().padding(0.05);
		var y = d3.scaleLinear().rangeRound([height, 0]);
		var z = d3.scaleOrdinal().range(["<?php echo $KT_STATS_CHART_COLOR1; ?>", "<?php echo $KT_STATS_CHART_COLOR2 ?>", "<?php echo $KT_STATS_CHART_COLOR3; ?>"]);
		var keys = d3.keys(data[0]).slice(1);

		x0.domain(data.map(function(d) { return d.century; }));
		x1.domain(keys).rangeRound([0, x0.bandwidth()]);
		y.domain([0, d3.max(data, function(d) {
			return d3.max(keys, function(key) {
				return d[key];
			});
		})]).nice();

		// columns
		g.append("g")
			.selectAll("g")
				.data(data)
				.enter().append("g")
				.attr("class", "bar")
				.attr("transform", function(d) { return "translate(" + x0(d.century) + ",0)"; })
			.selectAll("rect")
				.data(function(d) {
					return keys.map(function(key) { return { key: key, value: d[key] }; });
				})
				.enter().append("rect")
				.attr("x", function(d) { return x1(d.key); })
				.attr("y", function(d) { return y(d.value); })
				.attr("width", x1.bandwidth())
				.attr("height", function(d) { return height - y(d.value); })
				.attr("fill", function(d) { return z(d.key); });

		//Column labels
		g.append("g")
			.selectAll("g")
				.data(data)
				.enter().append("g")
				.attr("transform", function(d) { return "translate(" + x0(d.century) + ",0)"; })
			.selectAll("text")
				.data(function(d) {
					return keys.map(function(key) { return { key: key, value: d[key] }; });
				})
				.enter().append("text")
				.attr("x", function(d) { return x1(d.key) + x1.bandwidth() / 2; })
				.attr("y", function(d) { return y(d.value) - 10; })
				.attr("class","barLabel")
				.text(function(d) { return d.value; })

		// x-axis
		g.append("g")
			.attr("class", "axis")
			.attr("transform", "translate(0," + height + ")")
			.call(d3.axisBottom(x0));
		// text label for the x axis
	    g.append("text")
	        .attr("transform", "translate(" + (width / 2) + " ," + (height + margin.top) + ")")
			.attr("class","axisLabel")
	        .text("<?php echo KT_I18N::translate('Century'); ?>");

		// y-axis
		g.append("g")
			.attr("class", "axis")
			.call(d3.axisLeft(y).ticks(null, "s"))
			.append("text")
				.attr("x", 2)
				.attr("y", y(y.ticks().pop()) + 0.5);
		// text label for the y axis
	    svg.append("text")
	        .attr("transform", "rotate(-90)")
	        .attr("y", 0 + margin.left - 40)
	        .attr("x",0 - (height / 2 + 30))
			.attr("class","axisLabel")
	        .text("<?php echo KT_I18N::translate('Age'); ?>");

		// Legend
		var legend = g.append("g")
			.attr("text-anchor", "end")
			.selectAll("g")
	  		    .data(keys.slice())
			    .enter().append("g")
				      .attr("class", "legend")
					  .attr("transform", function(d, i) { return "translate("+ ((i * 100) + (width / 2) - 100) +"," + (margin.top - 30) + ")"; });
			legend.append("circle")
	  		    .attr("cx", 20)
	  		    .attr("cy", -6)
	  		    .attr("r", 12)
	  		    .style("fill", z);
			legend.append("text")
				.attr("x", 0)
				.attr("y", 0)
				.style("text-anchor", "end")
				.text(function(d) { return d; });
	}

</script>


<?php
