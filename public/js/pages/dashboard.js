//[Dashboard Javascript]

//Project:	Unique Admin - Responsive Admin Template
//Primary use:   Used only for the main dashboard (index.html)


$(function () {

  'use strict';


	
/*--- Sparkline charts - mini charts ---*/ 
	function sparkline_charts() {
		$('.sparklines').sparkline('html');
	}
	if ($('.sparklines').length) {
		sparkline_charts();
	}
	

//sparkline charts
$(document).ready(function() {
   var sparklineLogin = function() { 
         
        $("#sparkline8").sparkline([2,4,4,6,8,5,6,4,8,6,6,2 ], {
            type: 'line',
            width: '100%',
            height: '45',
            lineColor: '#fb9678',
            fillColor: '#fb9678',
            maxSpotColor: '#fb9678',
            highlightLineColor: 'rgba(0, 0, 0, 0.2)',
            highlightSpotColor: '#fb9678'
        });
        
   }
    var sparkResize;
 
        $(window).resize(function(e) {
            clearTimeout(sparkResize);
            sparkResize = setTimeout(sparklineLogin, 500);
        });
        sparklineLogin();

});

//---------------------------------------------------	
    
	
	// Bar chart
	
	
	if( $('#chart_8').length > 0 ){
		var ctx2 = document.getElementById("chart_8").getContext("2d");
		var data2 = {
			labels: ["Week 1", "Week 2", "Week 3", "Week 4", "Week 5", "Week 6", "Week 7"],
			datasets: [
				{
					label: "Page Views",
					backgroundColor: "#fec107",
					borderColor: "#fec107",
					data: [15, 20, 70, 51, 36, 85, 50]
				}
			]
		};
		
		var hBar = new Chart(ctx2, {
			type:"bar",
			data:data2,
			
			options: {
				tooltips: {
					mode:"label"
				},
				scales: {
					yAxes: [{
						stacked: true,
						gridLines: {
							color: "rgba(135,135,135,0)",
						},
						ticks: {
							fontFamily: "Poppins",
							fontColor:"#878787"
						}
					}],
					xAxes: [{
						stacked: true,
						gridLines: {
							color: "rgba(135,135,135,0)",
						},
						ticks: {
							fontFamily: "Poppins",
							fontColor:"#878787"
						}
					}],
					
				},
				elements:{
					point: {
						hitRadius:40
					}
				},
				animation: {
					duration:	3000
				},
				responsive: true,
				maintainAspectRatio:false,
				legend: {
					display: false,
				},
				
				tooltip: {
					backgroundColor:'rgba(33,33,33,1)',
					cornerRadius:0,
					footerFontFamily:"'Poppins'"
				}
				
			}
		});
	}
	
	
// Morris-chart
	
	Morris.Area({
        element: 'morris-area-chart2',
        data: [{
            period: '2010',
            SiteA: 485,
            SiteB: 358,
            
        }, {
            period: '2011',
            SiteA: 359,
            SiteB: 210,
            
        }, {
            period: '2012',
            SiteA: 589,
            SiteB: 410,
            
        }, {
            period: '2013',
            SiteA: 458,
            SiteB: 344,
            
        }, {
            period: '2014',
            SiteA: 254,
            SiteB: 240,
            
        }, {
            period: '2015',
            SiteA: 754,
            SiteB: 630,
            
        },
         {
            period: '2016',
            SiteA: 845,
            SiteB: 711,
           
        }],
        xkey: 'period',
        ykeys: ['SiteA', 'SiteB'],
        labels: ['Download', 'Listening'],
        pointSize: 0,
        fillOpacity: 1,
        pointStrokeColors:['#b4becb', '#01c0c8'],
        behaveLikeLine: true,
        gridLineColor: '#e0e0e0',
        lineWidth: 0,
        smooth: false,
        hideHover: 'auto',
        lineColors: ['#398bf7', '#f96197'],
        resize: true
        
    });
	


}); // End of use strict
