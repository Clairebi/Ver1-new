<!doctype html>
<html>
<head>
<style>
    @font-face {font-family:rockwell;src:url(fonts/RockwellStd.otf);}
	body{
        font-family: "rockwell";
        font-weight:bold;
        font-size: 40px;
        color: #FFFFFF;
	}
	div{
		border:hidden;
        text-align:center;
/*
		border:#000;
		border-width:thin;
		border-style:dashed;
        border-radius:5px;		
 */
	}
	table {
		text-align:center;
		border-collapse:collapse;
		margin:auto;
		margin-top: 8%;
    }	
    p {
        margin:0; padding:0;
    }
	#mainFrame {
		background: rgba(92,92,92,0.85);
	}
	#TestArea1, #TestArea2 {
		color:#000;
	}
	#CurveDataUsage {
		
		border-radius: 30px;
    }
    #InfoBlock {
        background:#000000;
    }
    #DateDisp, #WeatherDisp, #Welcome{
        font-size: 20px;
        color: #FEF5D4;
    }
    #DateDisp {
        text-align:left;
    }
    #WeatherDisp {
        text-align:right;
    }
	#Welcome{
		text-align:center;
	}
    #NextTireLevel {
        font-size:30px;
    }
	@font-face {font-family:LEDFont;src:url(fonts/DS-DIGI.TTF);}
	#BillingCycle{
		border-bottom: solid;
		border-color: gray;
	}
	#BillingCycle td, #BillingCycle th 
	{
        font-size:70px;
        color:#000000;
		padding:3px 7px 2px 7px;
		background-color: #E2E2E2;
	}
	#BillingCycle th 
	{
		font-size:40px;
		text-align:center;
		padding-top:5px;
		padding-bottom:4px;
		background-color:#0187F9;
		color:#FFFFFF;
	}
	#BillingCycle tr.alt td 
	{
		color:#DFD6A5;
		background-color:#EAF2D3;
    }
    #WarningBlock {
        font-size:20px;
        text-align:left;
        color:#FF6600;
        font-weight:lighter;
    }
	
	.axisText {
		margin-top: -10px;
	}
	
	.barTierBlock{
		float:left;
	}

</style>
<?php
function GetConsumptionData(){
	$user = 'root';  
	$pswd = 'calplug2012';  
	$db = 'smart_meter_reading';  
	$conn = mysql_connect('localhost', $user, $pswd);  
	mysql_select_db($db, $conn);
	$query = "select Consumption from smart_meter_reading.calplugmeter order by Timestamp ASC";
	//$query = "select Consumption from smart_meter_reading.housemeter order by Timestamp ASC";
	//$query = "select Consumption from smart_meter_reading.calplugmeter where Timestamp >= '2014-01-06 00:03:18' order by Timestamp ASC";
	$result = mysql_query($query);
	$prev = 0;
	$counter = 0;
	while($row = mysql_fetch_array($result))
	{
		if ($counter <= 1){ // ignore the first element and use the second element as bases
			$prev = intval($row[0]) / 1000;
			$counter++;
		}
		else {
			$value = intval($row[0]) / 1000 - $prev;
			echo round($value,4);
			echo ",";
			$prev = intval($row[0]) / 1000;
		}
	}
}
function GetDateData(){
	$user = 'root';  
	$pswd = 'calplug2012';  
	$db = 'smart_meter_reading';  
	$conn = mysql_connect('localhost', $user, $pswd);  
	mysql_select_db($db, $conn);
	$query = "select TimeStamp from smart_meter_reading.calplugmeter order by Timestamp ASC";
	//$query = "select TimeStamp from smart_meter_reading.housemeter order by Timestamp ASC";
	//$query = "select TimeStamp from smart_meter_reading.calplugmeter where Timestamp >= '2014-01-06 00:03:18' order by Timestamp ASC";
	$result = mysql_query($query);
	$prev = 0;
	$counter = 0;
	while($row = mysql_fetch_array($result))
	{
		if ($counter <= 1){ // ignore the first element (which is empty) and use the second element as bases
			$counter++;
		}
		else {
			$val = $row[0];
			$tok = strtok($val, "- :");
			while ($tok !== false) {
    			echo intval($tok);
				echo ",";
    			$tok = strtok("- :");
			}
		}
	}		
}
?>
<meta charset="UTF-8">
<title>Household Energy Consumption Display</title>
<script src="js/jquery-1.8.2.js"></script>
<script src="js/highcharts.js"></script>
<script src="js/highcharts-more.js"></script>
<!--<script src="js/modules/exporting.js"></script>-->
<script src="js/jquery.simpleWeather-2.3.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script> 

<!-- 
<script src="slider/js-image-slider.js" type="text/javascript"></script>
-->      
<script>
window.onerror=function(){
	return true;
}

//navigator.setResolution(1920,1080);
/*
*			Setup Global Information
*/
//var userZipcode = '92617';
var tempUnit = 'f';
var screenWidth = 1860;
var screenHeight = 1040;	
/*          Theme Color
*
 */          
var mainThemeColor = '#E9BE00';
var secondThemeColor = '#DFD6A5';
var strongThemeColor = '#E9BE00';
var newMainThemeColor = "#FFFFFF";

//----------------------------------------------------------------------------JSON data-----------------------------
var loc = '{ "location" : [' +
'{ "user":"Arthur" , "city":"Irvine" , "zipcode":"92617", "regioncode":"6" },' +
'{ "user":"Steve" , "city":"El Segundo" , "zipcode":"90245", "regioncode":"10" }]}';
var locObj = eval ("(" + loc + ")");

var rates = '{ "rates" : [' +
'{ "summer":"0.04476" , "winter":"0.04476", "lowerbound":"0" , "upperbound":"1"},' +
'{ "summer":"0.07603" , "winter":"0.07603", "lowerbound":"1.0" , "upperbound":"1.3"},' +
'{ "summer":"0.18396" , "winter":"0.18396", "lowerbound":"1.3", "upperbound":"2.0" },'+
'{ "summer":"0.22396" , "winter":"0.22396", "lowerbound":"2.0" , "upperbound":"-1.0"} ]}';
var ratesObj = eval ("(" + rates + ")");


var basic = '{ "basicCharge" : [' +
'{ "familyType":"single" , "price":"0.03"},' +
'{ "familyType":"multiple" , "price":"0.023"}]}';
var basicObj = eval ("(" + basic + ")");

var mini = '{ "minimumCharge" : [' +
'{ "familyType":"single" , "price":"0.059"},' +
'{ "familyType":"multiple" , "price":"0.044"}]}';
var miniObj = eval ("(" + mini + ")");

var summer = '{ "summer" : [' +
'{ "startMonth":"6" , "startDay":"1" , "endMonth":"10" , "endDay":"1"} ]}';
var summerObj = eval ("(" + summer + ")");

var winter = '{ "winter" : [' +
'{ "startMonth":"10" , "startDay":"1" , "endMonth":"6" , "endDay":"1"} ]}';
var winterObj = eval ("(" + winter + ")");

var baseline = '{ "baseline" : [' +
'{ "region":"5" , "summer":"9.1" , "winter":"9.8"},' +
'{ "region":"6" , "summer":"9.2" , "winter":"9.6"},' +
'{ "region":"8" , "summer":"10.2" , "winter":"9.2"},' +
'{ "region":"9" , "summer":"13.9" , "winter":"10.5"},' +
'{ "region":"10" , "summer":"16.0" , "winter":"10.5"},' +
'{ "region":"13" , "summer":"18.6" , "winter":"11.0"},' +
'{ "region":"14" , "summer":"16.1" , "winter":"10.6"},' +
'{ "region":"15" , "summer":"43.9" , "winter":"9.0"},' +
'{ "region":"16" , "summer":"11.5" , "winter":"10.9"} ]}';
var baseObj = eval ("(" + baseline + ")");
//----------------------------------------------------------------------JSON ends-----------------------------------
/*
*
*           Page Setup
 */
var marginVal = 5;
var px = "px";
var infoBlockWidth = screenWidth - 2*marginVal;
var infoBlockHeight = screenHeight/18 - 2*marginVal;
	
var tireWidth = screenWidth/4 - 3*marginVal;
var tireHeight = screenHeight/16*15 - 2*marginVal;
var billingCycleWidth = tireWidth*8/10 - marginVal;
var billingCycleHeight = tireHeight/11*3 - 2*marginVal;

var currentPowerWidth = tireWidth - marginVal;
//var currentPowerHeight = tireHeight/9*1 - 2*marginVal;
var currentPowerHeight = screenHeight/18*3 - 2*marginVal; //zhimin modify

var meterWidth = tireWidth -marginVal;
var meterHeight = tireHeight/9*4 -2*marginVal;
	
var EdisonLogoWidth = screenWidth/2 - 2*marginVal;
var EdisonLogoHeight = screenHeight/16*3 - 2*marginVal;
	
var dataBlockWidth = screenWidth/2 - 2*marginVal;
var dataBlockHeight = screenHeight/8*5 - 2*marginVal;

var WarningBlockLeft = dataBlockWidth/8;
var WarningBlockWidth = dataBlockWidth/8*7;
var WarningBlockHeight = screenHeight/8*2 - 2*marginVal;

var dataBlockCurveDataUsageWidth = dataBlockWidth;
var dataBlockCurveDataUsageHeight = dataBlockHeight;
var dataBlockCurveDataUsageTopMargin = dataBlockWidth/8-marginVal;
	
var barDataBlockWidth = screenWidth/4 - 2*marginVal;
var barDataBlockHeight = screenHeight/16*15 - 2*marginVal;

var barDataTitleWidth = currentPowerWidth;
var barDataTitleHeight = currentPowerHeight;


/* 
*		prepare consumption data from the system		
*/
// get all consumption data
var consumption;
var timeStamp;
// Tire Information
var tire;
// Bar Information
var barDataThisMonth;
var barDataToday;
// Billing Information
var date = new Date();
var billingStartDate = 15;
var billingStartMonth;
var daysSinceThisBillingCycle;
var billingCycleLength;

if (date.getDate() >= billingStartDate) {
    billingStartMonth = date.getMonth();
} else {
    billingStartMonth = date.getMonth()-1;
    if (billingStartMonth == -1)
        billingStartMonth = 11;
}

// variable names give their purposes
var eachDayUsageDivideByBillingCycle = new Array();
var eachBillingCycleUsage = new Array();
var eachHourUsageDivideByDay = new Array();

var recentNWeek = 10;
var recentNWeekdaysUsage = new Array();
var recentNWeekendsUsage = new Array();
var estimatedHourlyUsageOnMon = new Array();
var estimatedHourlyUsageOnTue = new Array();
var estimatedHourlyUsageOnWed = new Array();
var estimatedHourlyUsageOnThu = new Array();
var estimatedHourlyUsageOnFri = new Array();
var estimatedHourlyUsageOnSat = new Array();
var estimatedHourlyUsageOnSun = new Array();
var numHourlyUsageOnMon = new Array();
var numHourlyUsageOnTue = new Array();
var numHourlyUsageOnWed = new Array();
var numHourlyUsageOnThu = new Array();
var numHourlyUsageOnFri = new Array();
var numHourlyUsageOnSat = new Array();
var numHourlyUsageOnSun = new Array();

var estimatedDailyUsage = 0.0;
var estimatedMonthlyUsage = 0.0;
var todayUsage = 0.0;
var thisCycleUsage = 0.0;


// ProcessDataFromDatabase();

// global value for curve update
var curveDataCurIndex = 0;
// begin render the html
$(document).ready(function(){
	/*			Read Configuration
	*
	*/
	//$("#TestArea1").html(date.getUTCDay()+" "+ date.getHours());
	//xmlDoc = ReadSettingXML();//xml changed
	userZipcode = GetZipCode(locObj); //xml changed
	// alert(Date.UTC(2013, 3, 03));
	// alert(date.getDate());	
	
	$(function PageSetup() {  
		// show background
        $('#mainFrame').css({"top":"0px","left":"0px","width":screenWidth+px,"height":screenHeight+px});
        // show information block
        $('#InfoBlock').css({"top":marginVal+px,"left":marginVal+px,"width":infoBlockWidth+px,"height":infoBlockHeight+px});
        $('#DateDisp').css({"width":infoBlockWidth/4+px,"height":infoBlockHeight+px,"font-size":infoBlockHeight*0.7+px, "font-family":"Technic", "vertical-align":"center"});
        $('#WeatherDisp').css({"width":infoBlockWidth/4+px,"height":infoBlockHeight+px,"font-size":infoBlockHeight*0.7+px, "font-family":"Technic", "vertical-align":"center"});
		$('#Welcome').css({"width":infoBlockWidth/100*45+px,"height":infoBlockHeight+px,"font-size":infoBlockHeight*0.7+px, "font-family":"Technic", "vertical-align":"center"});

		// show cumulative usage
        $('#BarDataBlock').css({"top":infoBlockHeight+2*marginVal+px,"left":marginVal+px,"width":barDataBlockWidth+px,"height":barDataBlockHeight+px});
        $('#BarDataTitle').css({"width":barDataTitleWidth+px,"height":6/7*barDataTitleHeight+px, "padding-top":1/7*barDataTitleHeight+px,"vertical-align":"middle"});
		$('#BarData1stBlock').css({"width":barDataBlockWidth/2-marginVal*2+px,"height":barDataBlockHeight/5*3+px, "margin-top":"20px"});
        $('#BarData2ndBlock').css({"width":barDataBlockWidth/2-marginVal*2+px,"height":barDataBlockHeight/5*3+px, "margin-top":"20px"});
		// show Edison Logo
		$('#EdisonLogo').css({"top":infoBlockHeight+marginVal+px, "left":2*marginVal+tireWidth+px,"width":EdisonLogoWidth+px,"height":EdisonLogoHeight+px}).html("<img src=\"./img/EdisonLogo.png\" alt=\"Edison Logo\" height=\""+EdisonLogoHeight+"\">");
		// show curve data block
		$('#CurveDataBlock').css({"top":infoBlockHeight+marginVal*7+EdisonLogoHeight+px,"left":20*marginVal+tireWidth+px,"width":0.85*dataBlockWidth+px,"height":0.75*dataBlockHeight+px});
		// show curve data block title
		//$('#CurveDataTitle').css({"width":dataBlockTitleWidth+px,"height":dataBlockTitleHeight+px});
        //$('#CurveDataUsage').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.7*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});
        
        $('#CurveDataUsage').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.6*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});
		$('#CurveDataUsageHourly').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.6*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});


        // show warning block  
		//$('#WarningBlock').css({"top":infoBlockHeight*0.4+marginVal*2+EdisonLogoHeight+dataBlockHeight+px,"left":2*marginVal+tireWidth+WarningBlockLeft+px,"width":WarningBlockWidth+px,"height":WarningBlockHeight+px, "padding-left":"8%"});
            
        // show 
        $('#RightBlock').css({"top":infoBlockHeight+marginVal*2+px, "left":4*marginVal+tireWidth+dataBlockWidth+px,"width":tireWidth+px,"height":tireHeight+px});
        // show tire
        $("#BillingCycle").css({"width":billingCycleWidth+px,"height":1*billingCycleHeight+px, "padding-top":1/7*currentPowerHeight+px, "margin-left":2/11*billingCycleWidth});
        // show current power
        $('#CurrentPower').css({"width":currentPowerWidth+px,"height":6/7*currentPowerHeight+px, "padding-top":1/7*currentPowerHeight+px, "border":"Gray", "border-bottom-style":"solid"});
        $('#Meter').css({"margin-left":-meterWidth*0.1, "margin-top":-meterHeight*0.05, "width":meterWidth*1.1+px,"height":meterHeight*1.4+px});
		
        $('#NextTireLevel').css({"top":marginVal*3+currentPowerHeight+billingCycleHeight+meterHeight/10*7+px,"left":meterWidth/2+px,"width":meterWidth/2+px,"height":meterHeight/4+px});
        
		// show CalPlug logo
		//$('#CalPlugLogo').css({"top":infoBlockHeight+barDataBlockHeight+4*marginVal+px,"left":4*marginVal+tireWidth+dataBlockWidth+px,"width":CalPlugLogoWidth+px,"height":calPlugLogoHeight+px}).html("<img src=\"./img/calplug_logo.png\" alt=\"Edison Logo\" width=\""+CalPlugLogoWidth+"\">");
		/*
		*			Show charts
         */
        // update every 10 mins
		
        setTimeout('refreshHTML()',600000); 	//Zhimin add
		
        ProcessDataFromDatabase();
		
        var ProcessDataTimer=setInterval(function(){ProcessDataFromDatabase()},10000);
		//$("#TestArea1").html("bill:"+bill+" billToday:"+billToday+" percentage2NextTire:"+percentage2NextTire+" currentTire:"+currentTire+" currentRate: "+currentRate);
	});
	
});
function refreshHTML()
{
    window.location.reload(true);
}

function ProcessDataFromDatabase() {
	
    // reinitialize
    date = new Date();

    eachDayUsageDivideByBillingCycle = new Array();
    eachBillingCycleUsage = new Array();
    eachHourUsageDivideByDay = new Array();
    estimatedDailyUsage = 0.0;
    estimatedMonthlyUsage = 0.0;
    todayUsage = 0.0;
    thisCycleUsage = 0.0;

	// calculate the current power
	powersInHour = new Array();

    consumption  = [<?php GetConsumptionData() ?>];	
	//alert(consumption.length);
    timeStamp = [<?php GetDateData() ?>];
    for(var i = consumption.length; i--;) {
    	if (consumption[i] > 30) {
		    consumption[i] = 0;
        }
        --timeStamp[6*i+1];
    }	
	// for eachDayUsageDivideByBillingCycle & eachBillingCycleUsage
	var billingCycleIndex = 0;
	var dayIndex4BillingCycle = 0;
	var yesterday = 100;
	var dailyUsage = new Array();  
	dailyUsage[0] = [Date.UTC(timeStamp[0], timeStamp[1], billingStartDate,0,0,0,0), 0.0];
	// for eachBillingCycleUsage
	var usagePerBilling = 0.0;
	// for eachHourUsageDivideByDay
	var dayIndex = 0;
	var hourIndex = 0;
	var lastHour = 100;
	var hourlyUsage = new Array(); // will be initialize in the loop
	// for estimation
	var days = 1;
	var cycles = 1;
	var overallConsumption = 0.0;
    // weekly analysis
    var weekdaysUsage = 0.0;
    var weekendsUsage = 0.0;
    var lastWeek =  new Date(timeStamp[0], timeStamp[1], timeStamp[2], 0, 0, 0, 0);
    recentNWeekdaysUsage = new Array();
    recentNWeekendsUsage = new Array();
    estimatedHourlyUsageOnMon = new Array();
    estimatedHourlyUsageOnTue = new Array();
    estimatedHourlyUsageOnWed = new Array();
    estimatedHourlyUsageOnThu = new Array();
    estimatedHourlyUsageOnFri = new Array();
    estimatedHourlyUsageOnSat = new Array();
    estimatedHourlyUsageOnSun = new Array();
    numHourlyUsageOnMon = new Array();
    numHourlyUsageOnTue = new Array();
    numHourlyUsageOnWed = new Array();
    numHourlyUsageOnThu = new Array();
    numHourlyUsageOnFri = new Array();
    numHourlyUsageOnSat = new Array();
    numHourlyUsageOnSun = new Array();
	
    for (var i = 0; i < 24; ++i) {
        estimatedHourlyUsageOnMon.push(0.0);
        estimatedHourlyUsageOnTue.push(0.0);
        estimatedHourlyUsageOnWed.push(0.0);
        estimatedHourlyUsageOnThu.push(0.0);
        estimatedHourlyUsageOnFri.push(0.0);
        estimatedHourlyUsageOnSat.push(0.0);
        estimatedHourlyUsageOnSun.push(0.0);
        numHourlyUsageOnMon.push(0.1);
        numHourlyUsageOnTue.push(0.1);
        numHourlyUsageOnWed.push(0.1);
        numHourlyUsageOnThu.push(0.1);
        numHourlyUsageOnFri.push(0.1);
        numHourlyUsageOnSat.push(0.1);
        numHourlyUsageOnSun.push(0.1);
    }


    // begin traverse all data
    for (var i = 0; i < consumption.length; ++i) {
		// Note: if this is the last data, then add to the current cycle
		if (i == consumption.length-1) {
			var m;
			if (timeStamp[6*i+2] >= billingStartDate) {
				m = timeStamp[6*i+1];
			} else {
                m = timeStamp[6*i+1] - 1;
                if (m == -1)
                    m = 11;
			}
			eachDayUsageDivideByBillingCycle[billingCycleIndex] = dailyUsage;
			eachBillingCycleUsage[billingCycleIndex] = [Date.UTC(timeStamp[6*i], m,1,0,0,0,0),usagePerBilling];
			 eachHourUsageDivideByDay[dayIndex] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],0,0,0,0),hourlyUsage];
			 break;
		}
		// Note: if starting billing day is 1. We need to consider it seperately
			if ((timeStamp[6*i+2] >= billingStartDate && yesterday < billingStartDate) ||
			(billingStartDate == 1 && timeStamp[6*i+2] == 1 && yesterday <= 31))
		{
            // new billing cycle
            // get yesterday's billing cycle month
            var m = timeStamp[6*i+1]-1;
            if (m == -1)
                m = 11;
			
			eachDayUsageDivideByBillingCycle[billingCycleIndex] = dailyUsage;
			eachBillingCycleUsage[billingCycleIndex] = [Date.UTC(timeStamp[6*i], m, 1,0,0,0,0),usagePerBilling];
	
			++billingCycleIndex;
			dayIndex4BillingCycle = 1;
			dailyUsage = new Array();
			// dailyUsage[0] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], billingStartDate,0,0,0,0), 0.0];
			dailyUsage[0] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],0,0,0,0), 0.0];
			
			usagePerBilling = 0.0;
			++cycles;
		}
		// different days
		if (timeStamp[6*i+2] != yesterday) { // will run in the first time
			// a new day
			++dayIndex4BillingCycle;
			dailyUsage[dayIndex4BillingCycle] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],0,0,0,0), 0.0];
			yesterday = timeStamp[6*i+2];
			// update eachHourUsageDivideByDay
			eachHourUsageDivideByDay[dayIndex] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],0,0,0,0),hourlyUsage];
	
			++dayIndex;
			hourIndex = 1;
			hourlyUsage = new Array();
			hourlyUsage[0] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],0,0,0,0), 0.0];
			hourlyUsage[1] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],timeStamp[6*i+3],0,0,0), 0.0];
	
			++days;
		}
		// different hours
		if (timeStamp[6*i+3] != lastHour) { // will run in the first time
			// a new hour
			++hourIndex;
			hourlyUsage[hourIndex] = [Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],timeStamp[6*i+3],0,0,0), 0.0];
			lastHour = timeStamp[6*i+3];
		}
		// eachDayUsageDivideByBillingCycle
		dailyUsage[dayIndex4BillingCycle][1] += consumption[i];
		// eachBillingCycleUsage
		usagePerBilling += consumption[i];
		// eachHourUsageDivideByDay
		hourlyUsage[hourIndex][1] += consumption[i]; 
		// overallConsumption
		overallConsumption += consumption[i];
		// update powersInHour for power calculation
		powersInHour.push([Date.UTC(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2],timeStamp[6*i+3],timeStamp[6*i+4],timeStamp[6*i+5],0), consumption[i]]);
		while ((powersInHour.length > 1) && 
		(powersInHour[powersInHour.length-1][0] - powersInHour[0][0] > 3600000)) {
			powersInHour.shift();
        }
        // update weekly data
        var timeNow = new Date(timeStamp[6*i], timeStamp[6*i+1], timeStamp[6*i+2], timeStamp[6*i+3],0 , 0, 0);
        if (i > 0) {
            // it's a new week
            if (timeNow.getTime() - lastWeek.getTime() > 7*24*3600*1000) {
                recentNWeekdaysUsage.push(weekdaysUsage);  
                recentNWeekendsUsage.push(weekendsUsage);
                weekdaysUsage = 0.0;
                weekendsUsage = 0.0;
                lastWeek = timeNow;
            } 
            if (timeNow.getDay() <= 4) {
                // if it is a weekday
                weekdaysUsage += consumption[i];
            } else {
                // if it is a weekend           
                weekendsUsage += consumption[i];    
            }            
        }
        // update hourly estimated data in a week
        switch (timeNow.getDay()) {
        case 0:
            estimatedHourlyUsageOnMon[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnMon[timeNow.getHours()];
            break;
        case 1:
            estimatedHourlyUsageOnTue[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnTue[timeNow.getHours()];
            break;
        case 2:
            estimatedHourlyUsageOnWed[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnWed[timeNow.getHours()];
            break;
        case 3:
            estimatedHourlyUsageOnThu[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnThu[timeNow.getHours()];
            break;
        case 4:
            estimatedHourlyUsageOnFri[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnFri[timeNow.getHours()];
            break;
        case 5:
            estimatedHourlyUsageOnSat[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnSat[timeNow.getHours()];
            break;
        case 6:
            estimatedHourlyUsageOnSun[timeNow.getHours()] += consumption[i];
            ++numHourlyUsageOnSun[timeNow.getHours()];            
            break;        
        }
    }
	//alert(weekdaysUsage);
	recentNWeekdaysUsage.push(weekdaysUsage);  
    recentNWeekendsUsage.push(weekendsUsage);
    // process weekly usage
    var len = recentNWeekdaysUsage.length;
    if (len > recentNWeek) {
        recentNWeekdaysUsage = recentNWeekdaysUsage.slice(len-recentNWeek, len);
    }
    len = recentNWeekendsUsage.length;
    if (len > recentNWeek) {
        recentNWeekendsUsage = recentNWeekendsUsage.slice(len-recentNWeek, len);
    }
    for (var i = 0; i < 24; ++i) {
        estimatedHourlyUsageOnMon[i] /= numHourlyUsageOnMon[i]/4;
        estimatedHourlyUsageOnTue[i] /= numHourlyUsageOnTue[i]/4;
        estimatedHourlyUsageOnWed[i] /= numHourlyUsageOnWed[i]/4;
        estimatedHourlyUsageOnThu[i] /= numHourlyUsageOnThu[i]/4;
        estimatedHourlyUsageOnFri[i] /= numHourlyUsageOnFri[i]/4;
        estimatedHourlyUsageOnSat[i] /= numHourlyUsageOnSat[i]/4;
        estimatedHourlyUsageOnSun[i] /= numHourlyUsageOnSun[i]/4;    
    }

    // update tire information
    //xmlDoc = ReadSettingXML();
    //tire = CalcPriceTire(consumption, timeStamp, billingStartDate, xmlDoc);
    tire = CalcPriceTire(consumption, timeStamp, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj);
    // Update Bar information
    estimatedDailyUsage = overallConsumption / (days);
    estimatedDailyUsage = parseFloat(estimatedDailyUsage.toFixed(0));
    estimatedMonthlyUsage = overallConsumption /days*30;
    estimatedMonthlyUsage = parseFloat(estimatedMonthlyUsage.toFixed(0));
        // Calc today's usage and this mongth's usage
    todayUsage = eachDayUsageDivideByBillingCycle[eachDayUsageDivideByBillingCycle.length-1];
    todayUsage = parseFloat(todayUsage[todayUsage.length-1][1].toFixed(0));
    thisCycleUsage = parseFloat(eachBillingCycleUsage[eachBillingCycleUsage.length-1][1].toFixed(0));
        //Calc the days has passed since beginning of this billing cycle
    if (date.getDate() >= billingStartDate) {
        daysSinceThisBillingCycle = date.getDate() - billingStartDate + 1;
    } else {
        daysSinceThisBillingCycle = getNumberDaysInMonth(billingStartMonth, date.getFullYear()) - billingStartDate + 1 
            + date.getDate();
    }
        // Billing Cycle Length
    billingCycleLength = getNumberDaysInMonth(billingStartMonth, date.getFullYear()) - billingStartDate + 1 + 14;

    var estimatedDailyUsageByFar = estimatedDailyUsage / 24.0 * (date.getHours()+1);
    var estimatedMontlyUsageByFar = estimatedMonthlyUsage / billingCycleLength * daysSinceThisBillingCycle;
    estimatedDailyUsageByFar = parseFloat(estimatedDailyUsageByFar.toFixed(2));
    estimatedMontlyUsageByFar = parseFloat(estimatedMontlyUsageByFar.toFixed(2));
    
    var bill = tire[0], billToday = tire[1];


    /*
	*			Show curve charts   //IE6 does not support
	*/	
    var titleChart = ['Hourly Energy Consumption in 24 Hours', 'Weekly Consumption'];
	if (curveDataCurIndex == 1) {
		$('#CurveDataTitleText').html("Weekly Usage");
		$('#CurveDataXaxisText').html("Weeks Ago");
		//$('#CurveDataTitle').after(element);
		$('#CurveDataUsageHourly').css({"visibility":"hidden", "position":"absolute", "top":"0px", "left":"0px"});
		$('#CurveDataUsage').css({"position":"","float":"left","visibility":"visible"});
		$('#CurveDataXaxis').css({'margin-left':'40%'});
		//$('#legendImg').attr("src","img/legend.png");
		//$('#legendImg').attr("height","40%");
		//$('#legendImg').attr("width","40%");
        UpdateWeeklyChart('#CurveDataUsage');
		$('#redText').html('Weekends');
		$('#blueText').html('Weekdays');
    } else {
		$('#CurveDataTitleText').html("Hourly Usage");
		$('#CurveDataXaxisText').html("Hours");
		$('#CurveDataUsage').css({"visibility":"hidden", "position":"absolute", "top":"0px", "left":"0px"});
		$('#CurveDataUsageHourly').css({"position":"","float":"left","visibility":"visible"});
		$('#CurveDataXaxis').css({'margin-left':'50%'});
		//$('#legendImg').attr("src","img/legendHourly.png");
		//$('#legendImg').attr("height","30%");
		//$('#legendImg').attr("width","30%");
        UpdateHourlyChart('#CurveDataUsageHourly');
		$('#redText').html('Actual');
		$('#blueText').html('Average');
    }
	
    curveDataCurIndex = 1 - curveDataCurIndex;
     /*
	*			Show Costs //IE6 supports
    */
    $('#BarDataTitle').html("<p style=\"font-size:75%; font-family:Trebuchet MS\">Current Rate</p><span style=\"font-size:110%;color:#FFFFFF; font-famliy:Rockwell\">&nbsp$" + tire[4].toFixed(3) + "</span><span style=\"font-size:20px;\">&nbsp&nbsp&nbsp</span>");
	/*
	*			Show Bars   //IE6 does not support
    */
    barDataToday = {id:'#BarData1stBlock',title:"<br>Today",maximum:estimatedDailyUsage,value:todayUsage, expected:estimatedDailyUsageByFar,cost:billToday};
	barDataThisMonth = {id:'#BarData2ndBlock',title:"<br>This Month",maximum:estimatedMonthlyUsage,value:thisCycleUsage,expected:estimatedMontlyUsageByFar,cost:bill};
	
	$(UpdateBar(estimatedDailyUsage, todayUsage, estimatedMonthlyUsage, thisCycleUsage, curveDataCurIndex, bill, billToday));	

	//$('#BarData1stValue').html('<p style="color:#FFFFFF; font-size:26px; font-family:Technic">'+Math.max(barDataToday.value, barDataToday.maximum)+'</p>');
	//$(UpdateBar(barDataToday));   
	//$(UpdateBar(barDataThisMonth));	
	
	/*
	*			Show Warning   //IE6 supports
    */
    /*var message1 = "", message2 = "";
    if (barDataToday.value > barDataToday.maximum)
        //message1 = '<p>&#8226 The energy consumption ' + barDataToday.value.toFixed(0)+' for today is higher than the average daily usage ' + barDataToday.maximum.toFixed(0)+'.</p>';
		message1 = '<img src=\"img/alert.png\" height=55%, width=55%>';
    if (barDataThisMonth.value > barDataThisMonth.maximum)
        message2 = '<p>&#8226 The energy consumption ' + barDataThisMonth.value.toFixed(0)+' in this billing cycle is higher than the average daily usage ' + barDataThisMonth.maximum.toFixed(0)+'.</p>';
    if (message1 == "" && message2 == "") {
        //message1 = '<p style=\"color:#AFAFAF\">&#8226 It’s all good! You don’t have any new alerts.</p>';
		message1 = '<img src=\"img/calplug_logo.png\" height=55%, width=55%>';
    } else{
        message1 = message1 + "<br>" + message2;
    }*/
    //$('#WarningBlock').html("<br>" + message1);
	
	var widthForWarningString = $('#CurveDataBlock').css("width");
	var heightForWarningString = $('#CurveDataBlock').css("height");
	var widthForWarning = parseFloat(widthForWarningString.substr(0, widthForWarningString.length-2))*0.7;
	var heightForWarning = parseFloat(heightForWarningString.substr(0, heightForWarningString.length-2))*0.38;
	//alert(widthForWarningString);
	$('#ContactAndWarning').css({"top":(infoBlockHeight+marginVal*7+EdisonLogoHeight+0.75*dataBlockHeight)*1.13+px, "left":(2*marginVal+tireWidth)*1.45+px, "height":heightForWarning+px, "width":widthForWarning+px, "background-color":"rgb(65,65,65, 0.8)"});
	var message = "";
	//alert(barDataToday.maximum);
	/*if (barDataToday.value > barDataToday.maximum){
		message =  '<p style="font-size:80%; font-family:Trebuchet MS; margin-top:10px" >Questions?</p><p style="font-size:80%; font-family:Trebuchet MS; margin-top:10px">Contact SCE: 1-800-655-4555</p>';
	}
	else{
		message =  '<p style="font-size:80%; font-family:Trebuchet MS; margin-top:3px; color:red" >Save Power Day Event Today!<br/>Reduce your energy usage </br>2pm-6pm on 3/26/14</p><p style="font-size:80%; font-family:Trebuchet MS; margin-top:10px">Contact SCE: 1-800-655-4555</p>';
	}*/
	message =  '<p style="font-size:80%; font-family:Trebuchet MS; margin-top:3px; color:red" >Save Power Day Event Today!<br/>Reduce your energy usage </br>2pm-6pm on 3/26/14</p><p style="font-size:80%; font-family:Trebuchet MS; margin-top:10px">Contact SCE: 1-800-655-4555</p>';
	$('#ContactAndWarning').html(message);
	
	//show greeting
	var welcomeMessage="Welcome "+locObj.location[1].user+"!";
	$('#Welcome').html(welcomeMessage);
	
	/*
	*			Show Weather
	*/
	$(UpdateTimeWeather(userZipcode));	
	
	/*
	* 
	* Show Billing Cycle //IE6 supports
	*
	*/
	UpdateBillingCycle("#BillingCycle");   
	/*
	*			Show Tire  //IE6 doesnot support
    */	    
    UpdateTire(tire,"#Meter");  
	
    /*
    * 
    * 		Show Electric Meter  //IE6 supports, but the format changed
    *
    */ 
	
    UpdateCurrentPower("#CurrentPower", powersInHour); 
	

}
/*
* 
* 		Show Electric Meter
*
*/ 

function UpdateCurrentPower(currentPowerId) {
	var lastHourPower = 0.0;
  	for (var i = 0; i < powersInHour.length; ++i) {
		lastHourPower += powersInHour[i][1];
	}
	if (powersInHour.length > 1) {
		lastHourPower = lastHourPower / ((powersInHour[powersInHour.length-1][0] - powersInHour[0][0]) / 3600000.0);
	} 
	
	$("#CurrentPower").html("<p style=\"font-size:75%;font-family:Trebuchet MS\">Current Power Demand</p><span style=\"font-size:200%;font-family:Rockwell;color:#FFFFFF;\"> " + lastHourPower.toFixed(2) + "</span><span style=\"color:#FFFFFF;\">kW</p>");
}
/*
* 
* Show Billing Cycle
*
*/
function UpdateBillingCycle(billingCycleId) {
	
    //var startDay = getMonthDayAbbr(billingStartMonth, billingStartDate);
    //var today = getMonthDayAbbr(date.getMonth(), date.getDate());
    //var endDay;
    //if (billingStartDate == 1)
    //    endDay = getNumberDaysInMonth(billingStartMonth+1, date.getFullYear());
    //else
    //    endDay = billingStartDate - 1;
    //endDay = getMonthDayAbbr(billingStartMonth+1, endDay);
	
    var nextBillingStartMonth;
    var daysLeft;
    if (date.getDate() > billingStartDate) {
        daysLeft = getNumberDaysInMonth(date.getMonth(), date.getFullYear()) - date.getDate()
 + 1 + billingStartDate;
		nextBillingStartMonth = date.getMonth()+1;
		if (nextBillingStartMonth == 12)
			nextBillingStartMonth = 0;
    } else {
        daysLeft = billingStartDate -  date.getDate();
		nextBillingStartMonth = date.getMonth();	
	}
	nextBillingStartMonth = getMonthAbbr(nextBillingStartMonth);
	var title = "<p style=\"vertical-align:middle;text-align:center;font-size:75%;font-family:Trebuchet MS\">Billing Cycle Information</p>";
	var table;
	table = "<table id='BillingCycleTable'>";
	table += "<tr>";
	
    table += "<th style=\"font-family:Trebuchet MS\">"+ nextBillingStartMonth + "</th>";
	//table += "<th>"+ "Feb" + "</th>" //zhimin add
    
	table += "<td rowspan=\"2\" style=\"background-color:#D1CB96\"><p>&nbsp&nbsp" + daysLeft + "&nbsp&nbsp</p><p style=\"font-size:40%; font-family:Trebuchet MS\">Days Left</p></td>";
	//table += "<td rowspan=\"2\"><p>&nbsp&nbsp" + "18" + "&nbsp&nbsp</p><p style=\"font-size:20px\">days left</p></td>"; //zhimin add
	
	table += "</tr>";
	table += "<tr>";
	
    table += "<td>&nbsp&nbsp"+ billingStartDate + "&nbsp&nbsp</td>";
	//table += "<td>&nbsp&nbsp"+ "2" + "&nbsp&nbsp</td>";
	
	table += "</tr>";
	table += "</table>";
	$(billingCycleId).html(title+table);
	$("#BillingCycleTable").css({"height":0.65*billingCycleHeight+px, "margin-top":'4%'});
}
/*
*		Show Tire
*/
/* highcharts one *
function UpdateTire(tire, tireId) {
    var percentage2NextTire = tire[2],currentTire = tire[3], currentRate= tire[4], nextRate = tire[5];
    var nextTier = currentTire+1, text;
    if (currentTire == 4) {
        nextTier = "Highest Tier";
		text = "<p>" + nextTier + "</p>";
	}
    else {
        nextTier = "Until Next Tier "+nextTier;
		text = "<p>" + nextTier + "</p><p style=\"font-size:18px\">Next Tier Rate $" + nextRate + "</p>";
	}
    $("#NextTireLevel").html(text); 
	$(tireId).highcharts({
		chart: {
	    	type: 'gauge',
		    backgroundColor: 'rgba(255,255,255,0.0)',
		    plotBackgroundImage: null,
		    plotBorderWidth: 0,
		    plotShadow: false
        },

    	credits: {
	    	enabled: false
	    },
		
        title: {
            text: "<p style=\"font-family:Trebuchet MS\"> Current Rate </p><br><p>" + "Tier: "+currentTire + "</p><br><p>$"+ currentRate +"</p>",
            style: {
		        fontWeight: 'bold',
                fontSize: '38px',
                color: '#FFFFFF',
                fontFamily: "rockwell"
            }	            
        },
		
        pane: {
            startAngle: -180,
            endAngle: 90,
		    background: {
			    backgroundColor: 'rgba(255,255,255,0.0)'
		    }
        },
		
        // the value axis
        xAxis: {
            labels: {
                style: {
                    color: secondThemeColor                
                }
            }
        },
        yAxis: {
            min: 0,
            max: 100,
        
	    	minorTickInterval: 'auto',
            minorTickWidth: 1,
            minorTickLength: 10,
            minorTickPosition: 'inside',
            minorTickColor: '#666',

            tickPixelInterval: 50,
            tickWidth: 4,
            tickPosition: 'inside',
            tickLength: 10,
            tickColor: '#666',
            labels: {
                step: 2,
                rotation: 'auto',
			    style: {
                    fontWeight: 'bold',
                    fontSize: '20px',
                    color: secondThemeColor
                }
            },
            plotBands: [{
                from: 0,
                to: 40,
                color: '#55BF3B' // green
            }, {
                from: 40,
                to: 80,
                color: '#DDDF0D' // yellow
            }, {
                from: 80,
                to: 100,
                color: '#DF5353' // red
            }]        
        },
        plotOptions: {
            gauge: {
                animation: false
            }
        },
        series: [{
            name: 'current tier percentage',
            data: [percentage2NextTire*100],
		    dataLabels: {
                formatter: function () {
                    var p = this.y;
                    return "<div style=\"border-width:0px;font-size:32px;color:" + secondThemeColor + "\">"+ p + " %</div>";
        		}
		    },
		    tooltip: {
                valueSuffix: ' %'
            }	
        }]
	});
}*/

/* previous one
function UpdateTire(tire, tireId) {
    var percentage2NextTire = tire[2],currentTire = tire[3], currentRate= tire[4], nextRate = tire[5];
	var nextTier = currentTire+1, text;
	if (currentTire == 4) {
        nextTier = "Highest Tier";
		text = '<p style="font-family:Trebuchet MS; font-size:40px">' + nextTier + '</p>';
	}
    else {
        nextTier = "Until Next Tier "+nextTier;
		text = '<p style="font-family:Trebuchet MS; font-size:40px">' + nextTier + "</p><p style=\"font-size:30px\">Next Tier Rate $" + nextRate + "</p>";
	}
	$("#NextTireLevel").html(text);
    var nextTier = currentTire+1, text;
	//alert(percentage2NextTire);
	$('#titleMeter').html("<p style=\"font-family:Trebuchet MS\"> Current Rate </p><p>" + "Tier: "+currentTire + "</p><p>$"+ currentRate +"</p>");
	
	$('#tableMeter').css({"width":meterWidth});
	$('#axis').css({"width":meterWidth, "height":0.08*meterWidth});
	$('#value').css({"width":meterWidth, "height":0.1*meterWidth});
	$('#percent1').css({"width":meterWidth*0.4+px, "height":0.08*meterWidth+px});
	$('#percent2').css({"width":meterWidth*0.4+px, "height":0.08*meterWidth+px});
	$('#percent3').css({"width":meterWidth*0.2+px, "height":0.08*meterWidth+px});
	$('#tableValue').css({"width":meterWidth*0.98, "height":0.2*meterWidth, "margin-top":0});
	//$('#value').css({"width":meterWidth, "height":0.2*meterWidth, "border-style":"solid", "border-color":"#FFFFFF"});
	$('#value').css({"width":meterWidth, "height":0.2*meterWidth});
	$('#used').css({"width":meterWidth*percentage2NextTire, "height":0.2*meterWidth, "background-color":'#0187F9'});
	$('#rest').css({"width":meterWidth*(1-percentage2NextTire), "height":0.2*meterWidth});
	if(percentage2NextTire<0.2)
		$('#rest').html('<p style="font-family:rockwell;font-size:20px;text-align:left">'+percentage2NextTire*100+'%</p>');
	else
		$('#used').html('<p style="font-family:rockwell;font-size:70%">'+percentage2NextTire*100+'%</p>');
	
}
*/


function UpdateTire(tire, tireId){
	var percentage2NextTire = tire[2],currentTire = tire[3], currentRate= tire[4], nextRate = tire[5];
	//var percentage2NextTire = 10%,currentTire = 3, currentRate= tire[4], nextRate = tire[5];
	//---------for test !!!!!!!
	//percentage2NextTire = 0.75;
	//currentTire = 3;
	//-------------------------
	

	
	var nextTier = currentTire+1, text;
	var widthTireBlock = $('#Meter').css('width');
	widthTireBlock = parseFloat(widthTireBlock.substr(0, widthTireBlock.length-2));
	var heightTireBlock = $('#Meter').css('height');
	heightTireBlock = parseFloat(heightTireBlock.substr(0, heightTireBlock.length-2));
	//alert(widthTireBlock);
	var widthColumnTotal = widthTireBlock/4;
	var heightFirstColumn = heightTireBlock/2.5;
	var heightStep = (heightTireBlock-heightFirstColumn)/3;
	var widthColumn = 6.5/10*widthColumnTotal;
	var widthMargin = 3.0/10*widthColumnTotal;
	$('.oneTierBlock').css({'width':widthColumn, 'margin-left':widthMargin, 'border-style':'solid', 'border-color': '#FFFFFF'});
	for(var i=1; i<=4; ++i){
		var id = '#Tier'+i;
		$(id).css({'height':heightFirstColumn+(i-1)*heightStep+px, 'margin-top':heightTireBlock-(heightFirstColumn+(i-1)*heightStep)+px});
	}
	var heightUsed=new Array();
	for(var i=1; i<=4; ++i){
		if(i<currentTire){
			var id='#Tier'+i;
			$(id).css({'background-color':'rgb(255,0,0)'});
			var idRest='#Tier'+i+'Rest';
			$(idRest).attr("valign","top");
			$(idRest).html('<p style="font-family:rockwell; font-size:60%">100%</p>');
		}
		else if(i==currentTire){
			var usedHeight = percentage2NextTire*(heightFirstColumn+(i-1)*heightStep-8);
			var idUsed='#Tier'+i+'Used';
			var idRest='#Tier'+i+'Rest';
			if (percentage2NextTire < 3.0/4) // green
				colorOfBar = '#55BF3B';
			else if (percentage2NextTire <4.0/5) // yellow
				colorOfBar = '#FACC2E';
			else // red
				colorOfBar = 'rgb(255,0,0)';
			$(idUsed).css({'height':usedHeight+px, 'background-color':colorOfBar});
			if(percentage2NextTire>=0.1){
				$(idUsed).attr("valign","top");
				$(idUsed).html('<p style="font-family:rockwell; font-size:60%">'+(percentage2NextTire*100).toFixed(0)+'%</p>');
			}
			else{
				$(idRest).attr("valign","bottom");
				$(idRest).html('<p style="font-family:rockwell; font-size:60%">'+(percentage2NextTire*100).toFixed(0)+'%</p>');
			}
		}
	}
	
	
}


/*
*		Show Bar Data
*/
/* high charts one 
function UpdateBar(data) {
	var colorOfBar;
	if (data.value < data.maximum/4*3) // green
		colorOfBar = '#55BF3B';
	else if (data.value < data.maximum/5*4) // yellow
		colorOfBar = '#FACC2E';
	else // red
		colorOfBar = '#D26464';
	$(data.id).highcharts({
		 chart: {
			type: 'column',
			backgroundColor: 'rgba(255,255,255,0.0)'
		},
		credits: {
			enabled: false
        },
		title: {
            text: Math.max(data.value, data.maximum)+'kw',
			style:{
				color: '#FFFFFF',
				fontSize: '20px'
			}
		},
		legend: {
			enabled: false
		},
		xAxis: {
			categories: [data.title],
			title: {
				enabled: false
			},
			labels: {
				style: {
                    fontSize: '25px',
                    color: '#FFFFFF'
				}
            }
		},
		yAxis: {
			//gridLineWidth: 0,
			min: 0,
			max: 1.00,
			title: {
				enabled: false
			},
			labels: {
				enabled: false
            },
             gridLineWidth: 0
		},
        plotOptions: {
            series: {
                borderColor: 'white',
                color: colorOfBar,
                pointWidth: 100,
                borderWidth: 4
            },
            column: {
                animation: false
            }
        },          
        series: [{
			name: 'Power Consumed',
            data: [{
                    y: 1.00,
                    x: 0,
                    color: 'rgba(255,255,255,0.0)'
                },
                {
                    y: data.value / Math.max(data.value, data.maximum),
                    x: 0,
                    dataLabels: {
                        y: 40,
					    enabled: true,
                        formatter: function () {
                            return "<p style=\"font-size:20px;color:" + "#FFFFFF" + "\">" + data.value + "KWh</p>";
                        }
                    }

                }]        
		    }]
	});
}*/
/*table updatebar */
function UpdateBar(estimatedDailyUsage, todayUsage, estimatedMonthlyUsage, thisCycleUsage, curveDataCurIndex, billMonth, billToday){
	var heightBar = barDataBlockHeight/5*2.5;
	$('#Bar1st').css({"border":"3px solid white", "width":barDataBlockWidth*0.5/2-marginVal*2+px,"height":heightBar+px, "margin-top":"4%"});
	var heightUsed = todayUsage/Math.max(estimatedDailyUsage, todayUsage)*heightBar;
	heightUsed = Math.max(heightUsed, 0.01);
	if (todayUsage < estimatedDailyUsage/4*3) // green
		colorOfBar = '#55BF3B';
	else if (todayUsage < estimatedDailyUsage/5*4) // yellow
		colorOfBar = '#FACC2E';
	else // red
		colorOfBar = '#D26464';
	$('#usedBar1st').css({"height":heightUsed +px, "background-color":colorOfBar});
	$('#titleBar1st').css({"font-family":"rockwell", "font-size":"70%"});
	var maxUsageToday = Math.max(estimatedDailyUsage, todayUsage);
	var maxBillToday = maxUsageToday*billToday/todayUsage;
	if(curveDataCurIndex==1)
		$('#titleBar1st').html(maxUsageToday+'</br>kwh');
	else{
		
		/*-- fixed part start --*/
		if (maxBillToday>=100)
			maxBillToday = maxBillToday.toFixed(0);
		else
			maxBillToday = maxBillToday.toFixed(2);
		/*-- fixed part end --*/
		
		$('#titleBar1st').html('&nbsp</br>$'+maxBillToday);
	}
	//update today usage
	if(curveDataCurIndex==1){
		if(heightUsed>0.3*heightBar)
			$('#usedBar1st').html('<p style="font-size:70%; font-family:rockwell">'+todayUsage+'<br>kwh</p>');
		else
			$('#restBar1st').html('<p style="font-size:70%; font-family:rockwell">'+todayUsage+'<br>kwh</p>');
	}
	else{
		/*-- fixed part start --*/
		var billTodayFloat = parseFloat(billToday);
		if(billTodayFloat>=100)
			billTodayFloat = billTodayFloat.toFixed(0);
		else
			billTodayFloat = billTodayFloat.toFixed(2);
		/*-- fixed part end --*/
		//alert(billTodayFloat);
		if(heightUsed>0.3*heightBar)
			$('#usedBar1st').html('<p style="font-size:70%; font-family:rockwell">$'+billTodayFloat+'</p>');
		else
			$('#restBar1st').html('<p style="font-size:70%; font-family:rockwell">$'+billTodayFloat+'</p>');
	}
	
	$('#Bar2nd').css({"border":"3px solid white", "width":barDataBlockWidth*0.5/2-marginVal*2+px,"height":heightBar+px, "margin-top":"4%"});
	heightUsed = thisCycleUsage/Math.max(estimatedMonthlyUsage, thisCycleUsage)*heightBar;
	heightUsed = Math.max(heightUsed, 0.01);
	if (thisCycleUsage < estimatedMonthlyUsage/4*3) // green
		colorOfBar = '#55BF3B';
	else if (thisCycleUsage < estimatedMonthlyUsage/5*4) // yellow
		colorOfBar = '#FACC2E';
	else // red
		colorOfBar = '#D26464';
	$('#usedBar2nd').css({"height":heightUsed +px, "background-color":colorOfBar});
	$('#titleBar2nd').css({"font-family":"rockwell", "font-size":"70%"});
	//$('#titleBar2nd').html(Math.max(estimatedMonthlyUsage, thisCycleUsage)+'</br>kwh');
	var maxUsageCycle = Math.max(estimatedMonthlyUsage, thisCycleUsage);
	var maxBillCycle = maxUsageCycle*billMonth/thisCycleUsage;
	if(curveDataCurIndex==1)
		$('#titleBar2nd').html(maxUsageCycle +'</br>kwh');
	else{
		/*-- fixed part start --*/
		if(maxBillCycle>=100)
			maxBillCycle = maxBillCycle.toFixed(0);
		else
			maxBillCycle = maxBillCycle.toFixed(2);
		/*-- fixed part end --*/
		$('#titleBar2nd').html('&nbsp</br>$'+maxBillCycle);
	}
	if(curveDataCurIndex==1){
		if(heightUsed>0.3*heightBar)
			$('#usedBar2nd').html('<p style="font-size:70%; font-family:rockwell">'+thisCycleUsage+'<br>kwh</p>');
		else
			$('#restBar2nd').html('<p style="font-size:70%; font-family:rockwell">'+thisCycleUsage+'<br>kwh</p>');
	}
	else{
		/*-- fixed part start --*/
		var billMonthFloat = parseFloat(billMonth);
		if(billMonthFloat>=100)
			billMonthFloat=billMonthFloat.toFixed(0);
		else
			billMonthFloat=billMonthFloat.toFixed(2);
		/*-- fixed part end --*/
		if(heightUsed>0.3*heightBar)
			$('#usedBar2nd').html('<p style="font-size:70%; font-family:rockwell">$'+billMonthFloat+'</p>');
		else
			$('#restBar2nd').html('<p style="font-size:70%; font-family:rockwell">$'+billMonthFloat+'</p>');
	}
}

/*
*			Show Curve Data
*/
/*high charts version
function UpdateWeeklyChart(id) {
    var category = new Array();
    //var len = Math.min(recentNWeek, recentNWeekdaysUsage.length);
	var len=9;
    for (var i = 1; i < len; ++i) {
        category.push(len-1-i);
    }
	
	var weekDaysVal = new Array();
	var weekEndsVal = new Array();
	for(var i=0; i<len-1; ++i)
		weekDaysVal.push(recentNWeekdaysUsage[i]);
	for(var i=0; i<len-1; ++i)
		weekEndsVal.push(recentNWeekendsUsage[i]);

    $(id).highcharts({
        chart: {
            backgroundColor: 'rgba(255,255,255,0.0)'
        },
   		credits: {
			enabled: false
        },
        title: {
            text: '',
            style: {
		        fontWeight: 'bold',
                fontSize: '30px',
                color: newMainThemeColor,
                fontFamily: "rockwell"
            }	
        },
        xAxis: {
            categories: category,
            title: {
                text: null
            },
			labels: {
				x: 0,
				y: 30,
				style: {
                    fontSize: '25px',
                    color: '#FFFFFF'
                }
            },
            gridLineWidth: 1                
        },
		yAxis: {
			title: {
				text: 'kWH',
				style: {
					fontWeight: 'bold',
                    fontSize: '26px',
                    color: '#FFFFFF'
				}	
            },
			labels: {
				style: {
					fontWeight: 'bold',
                    fontSize: '20px',
                    color: '#FFFFFF'
				}	
            },
                
            gridLineWidth: 0
        },
        legend: {
            layout: 'horizontal',
            backgroundColor: 'rgba(255,255,255,0.0)',
            borderColor: 'rgba(255,255,255,0.0)',
            floating: true,
            align: 'center',
            verticalAlign: 'top',
            x: 10,
            y: -10,
            itemStyle: {
                paddingBottom: '30px'
            },
            labelFormatter: function() {
                return '<p style=\"font-size:25px;color:'+ '#FFFFFF' +'\">'+this.name+'</p>';
            }
        },      
        plotOptions: {
            column: {
                stacking: 'normal'
            }        
        },
        series: [{
                type: 'column',
                name: "Weekends",
				//name: "Actual",
				color: '#D70000',
                data: weekEndsVal
        },
		{
                type: 'column',
                name: "Weekdays",
				//name: "Average",
				color: '#0187F9',
                data: weekDaysVal
        }]
    })
}*/
/*table version */
function UpdateWeeklyChart(id) {
    var category = new Array();
    var len = Math.min(recentNWeek, recentNWeekdaysUsage.length);
	var len=9;
    for (var i = 1; i < len; ++i) {
        category.push(len-1-i);
    }
	
	var weekDaysVal = new Array();
	var weekEndsVal = new Array();
	var weekTotalVal = new Array();
	
	for(var i=0; i<len-1; ++i){
		weekDaysVal.push(recentNWeekdaysUsage[i]);
	}
	for(var i=0; i<len-1; ++i){
		weekEndsVal.push(recentNWeekendsUsage[i]);
	}
	var maxWeekTotalVal=0;
	for(var i=0; i<len-1; ++i){
		weekTotalVal.push(weekDaysVal[i]+weekEndsVal[i]);
		if(maxWeekTotalVal<weekTotalVal[i])
			maxWeekTotalVal = weekTotalVal[i];
	}
	
	var totalHeight = parseFloat($('#CurveDataUsage').css('height'));
	var totalWidth = parseFloat($('#CurveDataUsage').css('width'));
	var tableWidth = totalWidth/20;
	var tableHeight = totalHeight*9/10;
	
	/*-- fixed part start --*/
	//var maxYaxisVal = Math.round(maxWeekTotalVal/5/100)*100*5;
	var maxYaxisVal = maxWeekTotalVal/5*4;
	//var yaxisHeight = maxYaxisVal/maxWeekTotalVal*tableHeight;
	var yaxisHeight = tableHeight;
	var yaxisBlockHeight = yaxisHeight/5;
	var yaxisWidth = 0.9*tableWidth;
	var diffHeight = tableHeight-yaxisHeight;
	
	$('#divYaxis').css({'color':'#FFFFFF', 'font-size':'30px', 'font-family':'rockwell', 'margin-top':diffHeight, 'margin-right':'20px', 'border-right-style':'solid', 'border-right-width':'1px', 'padding-right':'10px'});
	$('#yaxis').css({'width':yaxisWidth});
	for(var i=0; i<5; ++i){
		var id='val'+i;
		$('#'+id).css({'height':yaxisBlockHeight});
		var currYaxisVal = maxYaxisVal/4*i;
		if(maxYaxisVal<10 && currYaxisVal!=0)
			currYaxisVal = currYaxisVal.toFixed(2);
		else
			currYaxisVal = currYaxisVal.toFixed(0);
		$('#'+id).html('<p style="color:#FFFFFF;font-size:25px;font-family:rockwell">'+currYaxisVal+'kwh</p>');
	}
	/*-- fixed part end --*/
	
	$('.subdiv').css({'width':tableWidth+px});
	$('.hoursTag').css({'font-family':'Rockwell', 'font-size': '30px'});
	for(var i=0; i<len-1; ++i){
		var currHeight = weekTotalVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
		var weekEndsHeight = weekEndsVal[len-1-i-1]/weekTotalVal[len-1-i-1]*currHeight;
		var weekDaysHeight = weekDaysVal[len-1-i-1]/weekTotalVal[len-1-i-1]*currHeight;
		var emptyHeight = tableHeight-currHeight;
		if(i==0){
			currHeight = weekDaysVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
			weekEndsHeight = 0;
			weekDaysHeight = currHeight;
			emptyHeight = tableHeight-currHeight;
		}
		
		var id='week'+i;
		$('#divWeek'+i).css({'margin-right':tableWidth});
		$('#'+id).css({'height':tableHeight});
		$('#'+id+'Empty').css({'height':emptyHeight});
		$('#'+id+'Rest').css({'height':weekEndsHeight, 'background-color': '#D70000'}); 
		$('#'+id+'Used').css({'height':weekDaysHeight, 'background-color': '#0187F9'}); 
	}
}




/* highcharts version 
function UpdateHourlyChart(id) {
    var category = new Array();
    for (var i = 0; i < 24; ++i) {
        if (i % 4 == 0) {
            if (i < 10)
                category.push(i );
            else
                category.push(i);
        } else {
            category.push('');
        }
    }
    var est_data;
    switch(date.getDay()) {
        case 0: est_data = estimatedHourlyUsageOnMon; break;
        case 1: est_data = estimatedHourlyUsageOnTue; break;
        case 2: est_data = estimatedHourlyUsageOnWed; break;
        case 3: est_data = estimatedHourlyUsageOnThu; break;
        case 4: est_data = estimatedHourlyUsageOnFri; break;
        case 5: est_data = estimatedHourlyUsageOnSat; break;
        case 6: est_data = estimatedHourlyUsageOnSun; break;
    }
    var temp = eachHourUsageDivideByDay[eachHourUsageDivideByDay.length-1][1];
    var actual_data = new Array();
    for (var i = 2; i < temp.length; ++i) {
        actual_data.push(temp[i][1]);
    }
    $(id).highcharts({   
        chart: {
            backgroundColor: 'rgba(255,255,255,0.0)'
        },
   		credits: {
			enabled: false
        },
        title: {
            text: '',
            style: {
		        fontWeight: 'bold',
                fontSize: '30px',
                color: '#FFFFFF',
                fontFamily: "rockwell"
            }	
        },
        xAxis: {
            categories: category,
            title: {
                text: null
            },
			labels: {
				x: 0,
				y: 30,
				style: {
                    fontSize: '25px',
                    color: '#FFFFFF'
                }
            },
            gridLineWidth: 1                
        },
		yAxis: {
			title: {
				text: 'kWH',
				style: {
					fontWeight: 'bold',
                    fontSize: '26px',
                    color: '#FFFFFF'
				}	
            },
			labels: {
				style: {
					fontWeight: 'bold',
                    fontSize: '20px',
                    color: '#FFFFFF'
				}	
            },
                
            gridLineWidth: 0
        },
        legend: {
            layout: 'vertical',
            backgroundColor: 'rgba(255,255,255,0.0)',
            borderColor: 'rgba(255,255,255,0.0)',
            floating: true,
            align: 'left',
            verticalAlign: 'top',
            x: 90,
            y: 45,
            itemStyle: {
                paddingBottom: '30px'
            },
            labelFormatter: function() {
                return '<p style=\"font-size:16px;color:'+ '#FFFFFF' +'\">'+this.name+'</p>';
            }
        },      
        plotOptions: {
            series: {
                marker: {
                    radius: 8
                },
                lineWidth: 5
            }
        },
        series: [{
                type: 'area',
                name: "Average Hourly Usage on " + getDayInAWeek(date.getDay()),
                data: est_data
        },{
                type: 'line',
                name: "Acutal Hourly Usage",
                data: actual_data
        }]
    })			
}*/
/* table version*/
function UpdateHourlyChart(id) {
    var category = new Array();
    for (var i = 0; i < 24; ++i) {
        if (i % 4 == 0) {
            if (i < 10)
                category.push(i );
            else
                category.push(i);
        } else {
            category.push('');
        }
    }
    var est_data;
    switch(date.getDay()) {
        case 0: est_data = estimatedHourlyUsageOnMon; break;
        case 1: est_data = estimatedHourlyUsageOnTue; break;
        case 2: est_data = estimatedHourlyUsageOnWed; break;
        case 3: est_data = estimatedHourlyUsageOnThu; break;
        case 4: est_data = estimatedHourlyUsageOnFri; break;
        case 5: est_data = estimatedHourlyUsageOnSat; break;
        case 6: est_data = estimatedHourlyUsageOnSun; break;
    }
    var temp = eachHourUsageDivideByDay[eachHourUsageDivideByDay.length-1][1];
    var actual_data = new Array();
    for (var i = 2; i < temp.length; ++i) {
        actual_data.push(temp[i][1]);
    }
	
	var est_data_4hour = new Array();
	var actual_data_4hour = new Array();
	for(var i=0; i<6; ++i){
		var est_data_sum4=0;
		var actual_data_sum4=0;
		for(var j=0; j<4; ++j)
			est_data_sum4+=est_data[i*4+j];
		est_data_4hour.push(est_data_sum4);
		actual_data_sum4=0;
		for(var j=0; j<4; ++j){
			if(i*4+j<actual_data.length){
				actual_data_sum4+=actual_data[i*4+j];
				$('#aColumn'+i).css({"color":"#FFFF00"});
			}
			else
				break;
		}
		actual_data_4hour.push(actual_data_sum4);
	}
	var max_est_data = Math.max.apply(Math, est_data_4hour);
	var max_actual_data = Math.max.apply(Math, actual_data_4hour);
	var max_data = Math.max(max_est_data, max_actual_data);
	
	var totalHeight = parseFloat($('#CurveDataUsageHourly').css('height'));
	var totalWidth = parseFloat($('#CurveDataUsageHourly').css('width'));
	var tableWidth = totalWidth/22;
	var tableHeight = totalHeight*9/10;
	
	/*-- fixed part start --*/
	var maxYaxisVal = max_data/5*4;
	//var yaxisHeight = maxYaxisVal/max_data*tableHeight;
	var yaxisHeight = tableHeight;
	//alert(maxYaxisVal/max_data);
	//alert(max_data);
	var yaxisBlockHeight = yaxisHeight/5;
	var yaxisWidth = 2.5*tableWidth;
	var diffHeight = tableHeight-yaxisHeight;
	$('.colorLegend').css({'width':0.5*tableWidth, 'height':0.5*tableWidth});
	$('#divYaxisHourly').css({'color':'#FFFFFF', 'font-size':'30px', 'font-family':'rockwell', 'margin-top':diffHeight, 'margin-right':'20px', 'border-right-style':'solid', 'border-right-width':'1px', 'padding-right':'10px'});
	$('#yaxisHourly').css({'width':yaxisWidth});
	//draw the Y axis
	for(var i=0; i<5; ++i){
		var id='val'+i+'Hourly';
		$('#'+id).css({'height':yaxisBlockHeight});
		var currYaxisVal = maxYaxisVal/4*i;
		if(maxYaxisVal<10 && currYaxisVal!=0)
			currYaxisVal = currYaxisVal.toFixed(2);
		else
			currYaxisVal = currYaxisVal.toFixed(0);
		$('#'+id).html('<p style="color:#FFFFFF;font-size:25px;font-family:rockwell">'+currYaxisVal+'kwh</p>');
	}
	/*-- fixed part end --*/
	
	$('.hourBlock').css({'width':2*tableWidth, "height":tableHeight, 'float':'left', 'margin-right':tableWidth});
	$('#hour24').css({'margin-right':'0px'});
	$('.hourTable').css({'width':0.9*tableWidth});
	$('.barBlock').css({'float':'left'})
	$('.colum').css({'width':tableWidth});
	for(var i=0; i<6; ++i){
		var id=i*4+4;
		$('#hour'+id+'AverageRest').css({"height":tableHeight*(1-est_data_4hour[i]/max_data)});
		$('#hour'+id+'AverageUsed').css({"background-color":"#0187F9","height":tableHeight*est_data_4hour[i]/max_data});
		$('#hour'+id+'RealRest').css({"height":tableHeight*(1-actual_data_4hour[i]/max_data)});
		$('#hour'+id+'RealUsed').css({"background-color":"#D70000","height":tableHeight*actual_data_4hour[i]/max_data});
	}
	
	$('#axisHourly').css({'margin-top':5, 'margin-right':0, 'margin-bottom':0, 'margin-left':tableWidth+yaxisWidth+20});
	$('.axisColumn').css({'width':3*tableWidth, 'font-size':30, 'text-align':'left', 'margin-top':'-10px'});
}
/*
*			Display information
*/
function UpdateTimeWeather(userZipcode) {
	var date = new Date();
	var month;
	switch(date.getMonth()) {
		case 0: month = 'January';break;
		case 1: month = 'February';break;
		case 2: month = 'March';break;
		case 3: month = 'April';break;
		case 4: month = 'May';break;
		case 5: month = 'June';break;
		case 6: month = 'July';break;
		case 7: month = 'August';break;
		case 8: month = 'September';break;
		case 9: month = 'October';break;
		case 10: month = 'November';break;
		case 11: month = 'December';
	}
	/*
	var hours = date.getHours();
	var timeSec = 'AM';
	if(hours>=12)
		timeSec = 'PM';
	if(hours>12)
		hours = hours-12;
	if(hours==0)
		hours = 12;
	
	
	var minutes = date.getMinutes();
	if (minutes < 10)
		minutes = '0' + minutes;
	$('#DateDisp').html("<p font-family=\"Technic\">"+"&nbsp&nbsp&nbsp&nbsp"+hours + ":" + minutes + " "+timeSec+" "+month + ' '+date.getDate()+"</p>");
	*/
	var minutes = date.getMinutes();
	if (minutes < 10)
		minutes = '0' + minutes;
	$('#DateDisp').html("&nbsp&nbsp&nbsp"+date.getHours() + ":" + minutes + ' ' + month + ' '+date.getDate());
	
	/*
	*				Get Weather
	*/
	var isOnline = false;
	var temperature;
	var city;
	var region;
	// var country;
	var unitDisplay;
	var code;
	var currently;
	var url = 'http://l.yimg.com/os/mit/media/m/weather/images/icons/l/';
	$.simpleWeather({ 
		zipcode: userZipcode,
		unit: 'f',
		success: function(weather) {
			temperature = weather.temp;
			unitDisplay = '°'+weather.units.temp;
			//unitDisplay = "deg";
			code = weather.todayCode;
			city = weather.city;
			region = weather.region;
			// country = weather.country;
			currently = weather.currently;
			isOnline = true;
			url += code;
			if (date.getHours() >= 6 && date.getHours() <= 18)
				url += 'd-100567.png';
			else
				url += 'n-100567.png';
            $('#WeatherIcon').html('<img style=\"float:middle\" src='+url+' height=\"' + infoBlockHeight*1.5 + '\"">');
            $('#WeatherLocation').html(city+', '+region+', '+temperature+" "+unitDisplay+"&nbsp&nbsp&nbsp");
		},
		error: function(error) {
			isOnline = false;
		}
	});
}
/*				
*				Read Data from configuration file

function ReadSettingXML () {
	if (window.XMLHttpRequest)
	{// code for IE7+, Firefox, Chrome, Opera, Safari
	xmlhttp=new XMLHttpRequest();
	}
	else
	{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.open("GET","doc/config.xml",false);
	xmlhttp.send();
	xmlDoc=xmlhttp.responseXML;
	return xmlDoc;
}
*/
function GetZipCode(locObj) {
	return locObj.location[1].zipcode;
	//return xmlDoc.getElementsByTagName("zipcode")[0].childNodes[0].nodeValue;
}
function CalcPriceTire(consumption, timeStamp, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj) {
	// get season
	//bill = 1321.55, billToday = 79.12, percentage2NextTire=1, currentTire = 3, nextRate = 0.224,currentRate = 0.224;
	//return ([bill.toFixed(2), billToday.toFixed(2), percentage2NextTire.toFixed(2),currentTire+1, currentRate, nextRate]);
	
	var season;
	var now = new Date();
	var searson;
	var startMonth = parseInt(summerObj.summer[0].startMonth);
	var endMonth = parseInt(summerObj.summer[0].endMonth);
	if (now.getMonth() >= startMonth-1 && now.getMonth() < endMonth-1) {
		season = "summer";
	} else {
		season = "winter";
	}
	// get prices
	var prices = new Array(), lowerbounds = new Array(), upperbounds = new Array();
	var tireLen = ratesObj.rates.length;
	for (var i = 0; i < tireLen; ++i) {
		prices[i] = parseFloat(ratesObj.rates[i][season]);
		lowerbounds[i] = parseFloat(ratesObj.rates[i].lowerbound);
		upperbounds[i] = parseFloat(ratesObj.rates[i].upperbound);		
	}
	// get base line
	var regioncode = parseInt(locObj.location[0].regioncode);
	var baseline;
	var baselineLen = baseObj.baseline.length;
	for (var i = 0; i < baselineLen; ++i) {
		if (regioncode == baseObj.baseline[i].region) {
			baseline = baseObj.baseline[i][season];
			break;
		}
	}   
	// calculate tire level
	// Note: here I separate the dataByThisMonth and datayByToday is because of display reason.
	// for the data is incomplete, I have to select the specific day instead of today.
	var bill = 0.0, billToday = 0.0, percentage2NextTire = 0.0, currentTire = 0, currentRate = 0.0, nextRate = 0.0;
	var date = new Date();
	var tomonth = date.getMonth();
    var toyear = date.getFullYear();
	var today = timeStamp[6*(consumption.length-1)+2];
    var yesterday = -100;
	var monthBilling = (today>=billingStartDate?tomonth:tomonth-1);
	var yearBilling = toyear;
	if(monthBilling==-1){
		monthBilling = 11;
		yearBilling -=1;
	}
	var count = 0;		
	for (var i = 0; i < consumption.length; ++i) {
		/*if (timeStamp[6*i] == toyear && 
            ((timeStamp[6*i+1] == tomonth-1 && timeStamp[6*i+2] >= billingStartDate) ||
            (timeStamp[6*i+1] == tomonth && timeStamp[6*i+2] < billingStartDate))
        )*/ 
		if ((timeStamp[6*i] == yearBilling && (timeStamp[6*i+1] > monthBilling || (timeStamp[6*i+1]==monthBilling && timeStamp[6*i+2]>=billingStartDate)))
		|| (timeStamp[6*i]==yearBilling+1 && mongthBilling==11 && tomonth==0 && timeStamp[6*i+2]<billingStartDate))
		//if ( (timeStamp[6*i+1] > monthBilling || (timeStamp[6*i+1]==monthBilling && timeStamp[6*i+2]>=billingStartDate)))//temporary
		{
				//++count;
				if (yesterday != timeStamp[6*i+2]) {
                    yesterday = timeStamp[6*i+2];
                    consumptionToday = consumption[i];
                    bill += billToday;
                    billToday = 0.0;
                } else {
                    consumptionToday += consumption[i];
                }
                
                if (consumptionToday <= baseline * upperbounds[0]) {
                    currentTire = 0;
                    percentage2NextTire = (consumptionToday - baseline*lowerbounds[0]) / (baseline * (upperbounds[0] - lowerbounds[0]));
		        	currentRate = prices[0];
					nextRate = prices[1];
                } else if (consumptionToday <= baseline*upperbounds[1]) {
		        	currentTire = 1;	
		        	percentage2NextTire = (consumptionToday - baseline*lowerbounds[1]) / (baseline * (upperbounds[1] -lowerbounds[1]));
			        currentRate = prices[1];
					nextRate = prices[2];
	        	} else if (consumptionToday <= baseline*upperbounds[2]) {
		        	currentTire = 2;
		        	percentage2NextTire = (consumptionToday - baseline*lowerbounds[2]) / (baseline * (upperbounds[2] - lowerbounds[2]));
		        	currentRate = prices[2];
					nextRate = prices[3];
		        } else  {
		        	currentTire = 3;
		        	percentage2NextTire = 1.0;
		        	currentRate = prices[3];
					nextRate = prices[3];
                }
                billToday += currentRate * consumption[i];
                if (i == consumption.length-1) {
                    bill += billToday;
                }
				//alert(count);
        }
    }
    // for display purpose, we may set values manually
	//bill = 7.78, billToday = 0.12, percentage2NextTire=0.47, currentTire = 1;
	//alert(currentTire+1);
	//alert("OK");
	return ([bill.toFixed(2), billToday.toFixed(2), percentage2NextTire.toFixed(2),currentTire+1, currentRate, nextRate]);
	//$("#TestArea1").html(baseline + "##"+ prices.toString()+ "##"+lowerbounds.toString() + "##"+upperbounds.toString());
	//$("#TestArea1").html("bill:"+bill+" billToday:"+billToday+" percentage2NextTire:"+percentage2NextTire+" currentTire:"+currentTire);
}
function getNumberDaysInMonth(monthNum, year) { // month starts from 0 to 11
    switch(monthNum) {
        case 0: return 31;
        case 1: if (year % 4 == 0 || (year % 100 == 0 && year % 400 == 0))
                    return 29;
                else
                    return 28;
        case 2: return 31;
        case 3: return 30;
        case 4: return 33;
        case 5: return 30;
        case 6: return 31;
        case 7: return 31;
        case 8: return 30;
        case 9: return 31;
        case 10: return 30;
        case 11: return 31;
    }
}
function getMonthDayAbbr(monthNum, dayNum) { // month starts from 0 to 11
    var str;
    switch(monthNum) {
        case 0: str = "Jan "; break;
        case 1: str = "Feb "; break;
        case 2: str = "Mar "; break;
        case 3: str = "Apr "; break;
        case 4: str = "May "; break;
        case 5: str = "Jun "; break;
        case 6: str = "Jul "; break;
        case 7: str = "Aug "; break;
        case 8: str = "Sep "; break;
        case 9: str = "Oct "; break;
        case 10: str = "Nov "; break;
        case 11: str = "Dec "; break;
    }
    return str + dayNum;
}
function getDayInAWeek(day) {
    var str;
    switch(day) {
        case 1: str = "Monday"; break;
        case 2: str = "Tuesday"; break;
        case 3: str = "Wednesday"; break;
        case 4: str = "Thursday"; break;
        case 5: str = "Friday"; break;
        case 6: str = "Saturday"; break;
        case 7: str = "Sunday"; break;
    }
    return str;
}
function getMonthAbbr(monthNum) { // month starts from 0 to 11
    var str;
    switch(monthNum) {
        case 0: str = "Jan "; break;
        case 1: str = "Feb "; break;
        case 2: str = "Mar "; break;
        case 3: str = "Apr "; break;
        case 4: str = "May "; break;
        case 5: str = "Jun "; break;
        case 6: str = "Jul "; break;
        case 7: str = "Aug "; break;
        case 8: str = "Sep "; break;
        case 9: str = "Oct "; break;
        case 10: str = "Nov "; break;
        case 11: str = "Dec "; break;
    }
    return str;
}
</script>
</head>

<body>
<div id="mainFrame" class="withboarder" style="position:absolute;margin-top:23px;margin-left:30px">

	<div id="InfoBlock" style="position:absolute; margin:1 auto">
		<div id="DateDisp" style="float:left; margin:1  auto"></div>
		<div id="Welcome" style = "float:left; margin: 1 auto"></div>
		<div id="WeatherDisp" style="float:right; margin:1  auto">
			<div id="WeatherLocation" style="float:right; margin:1  auto"></div>
			<div id="WeatherIcon" style="float:right"></div>
		</div>
	</div>

	<div id="BarDataBlock" style="position:absolute; margin:1 auto">
		<div id="BillingCycle" ></div>
	<!--
	<div id="CurrentPower" style="margin:1 auto"></div>
	<div id="BarDataTitle" style="margin: 1 auto"></div>
	-->
	<!--
	<div id="BarData1stBlock" style="float:left; margin:1  auto"></div>
	<div id="BarData2ndBlock" style="float:right; margin:1  auto"></div>
	-->
		<div id="BarData1stBlock" style="float:left; margin:1  auto">
			<p id="titleBar1st"></p>
			<table id="Bar1st">
				<tr>
					<td id="restBar1st" valign="bottom"><p></p></td>
				</tr>
				<tr>
					<td id="usedBar1st" valign="top"><p></p></td>
				</tr>
			</table>
			<p style="font-size:70%;font-family:Trebuchet MS">Today's </br>Budget</p>
		</div>

		<div id="BarData2ndBlock" style="float:right; margin:1  auto">
			<p id="titleBar2nd"></p>
			<table id="Bar2nd">
				<tr>
					<td id="restBar2nd" valign="bottom"><p></p></td>
				</tr>
				<tr>
					<td id="usedBar2nd"  valign="top"><p></p></td>
				</tr>
			</table>
			<p style="font-size:70%;font-family:Trebuchet MS">This Month's</br>Budget</p>
		</div>
		<div style="clear:both"></div>
		<div style="margin-top:60px"><p style="font-size:80%; font-family:Trebuchet MS; color:rgb(1, 135, 249)">CA Monthly Avg: $87.91</p></div>
	</div>

	<div id="EdisonLogo" style="position:absolute; margin:1 auto"></div>
<!--
<div id="CurveDataBlock" style="position:absolute; margin:1 auto">
<div id="CurveDataTitle" style="margin-bottom:2%"><p id="titleText" style="font-size:80%; font-family:Trebuchet MS"></p></div>
<div id="CurveDataUsage" style="margin:1 auto"></div>
<div id="CurveDataXaxis" style="padding-top:0%"><p id="xaxisText" style="font-size:80%; font-family:Trebuchet MS"></p></div>
</div>
-->
	<div id="CurveDataBlock" style="position:absolute; margin:1 auto">
<!--
<div id="CurveDataTitle" style="margin-bottom:2%"><p id="CurveDataTitleText" style="font-size:80%; font-family:Trebuchet MS"></p><img id="legendImg" src="img/legend.png" height=40%, width=40%></div>
-->
		<div id="CurveDataTitle" style="margin-bottom:2%"><p id="CurveDataTitleText" style="font-size:80%; font-family:Trebuchet MS"></p></div>

		<div id="CurveDataLegend">
			<div id="RedLegend"><div class="colorLegend" style="background-color:red; float:left; margin-left:30%"></div><p id="redText" style="float:left; font-size: 70%; font-family:Trebuchet MS; margin-left:5px; margin-top:-10px"></p></div>
			<div id="BlueLegend"><div class="colorLegend" style="background-color:rgb(1, 135, 249); float:left; margin-left:10%"></div><p id="blueText" style="float:left; font-size: 70%; font-family:Trebuchet MS; margin-left:5px; margin-top:-10px"></p></div>
		</div>

		<div id="CurveDataUsage" style="margin:1 auto">

			<div id = "divYaxis" style="float:left">
				<table id="yaxis">
					<tr>
						<td id="val4" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val3" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val2" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val1" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val0" valign="bottom"></td>
					</tr>
				</table>
			</div>

			<div id="divWeek7" style="float:left">
				<table id = "week7" class="subdiv">
					<tr>
						<td id="week7Empty"></td>
					</tr>
					<tr>
						<td id="week7Rest"></td>
					</tr>
					<tr>
						<td id="week7Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">7</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek6" style="float:left">
				<table id = "week6" class="subdiv">
					<tr>
						<td id="week6Empty"></td>
					</tr>
					<tr>
						<td id="week6Rest"></td>
					</tr>
					<tr>
						<td id="week6Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">6</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek5" style="float:left">
				<table id = "week5" class="subdiv">
					<tr>
						<td id="week5Empty"></td>
					</tr>
					<tr>
						<td id="week5Rest"></td>
					</tr>
					<tr>
						<td id="week5Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">5</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek4" style="float:left">
				<table id = "week4" class="subdiv">
					<tr>
						<td id="week4Empty"></td>
					</tr>
					<tr>
						<td id="week4Rest"></td>
					</tr>
					<tr>
						<td id="week4Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">4</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek3" style="float:left">
				<table id = "week3" class="subdiv">
					<tr>
						<td id="week3Empty"></td>
					</tr>
					<tr>
						<td id="week3Rest"></td>
					</tr>
					<tr>
						<td id="week3Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">3</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek2" style="float:left">
				<table id = "week2" class="subdiv">
					<tr>
						<td id="week2Empty"></td>
					</tr>
					<tr>
						<td id="week2Rest"></td>
					</tr>
					<tr>
						<td id="week2Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">2</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek1" style="float:left">
				<table id = "week1" class="subdiv">
					<tr>
						<td id="week1Empty"></td>
					</tr>
					<tr>
						<td id="week1Rest"></td>
					</tr>
					<tr>
						<td id="week1Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">1</p></td>
					</tr>
				</table>
			</div>

			<div id="divWeek0" style="float:left">
				<table  id = "week0" class="subdiv">
					<tr>
						<td id="week0Empty"></td>
					</tr>
					<tr>
						<td id="week0Rest"></td>
					</tr>
					<tr>
						<td id="week0Used"></td>
					</tr>
					<tr>
						<td><p class="hoursTag", style="color:#FFFFF">0</p></td>
					</tr>
				</table>
			</div>
		</div>

		<div id="CurveDataUsageHourly" style="margin:1 auto">
			<div id = "divYaxisHourly" style="float:left">
				<table id="yaxisHourly">
					<tr>
						<td id="val4Hourly" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val3Hourly" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val2Hourly" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val1Hourly" valign="bottom"></td>
					</tr>
					<tr>
						<td id="val0Hourly" valign="bottom"></td>
					</tr>
				</table>
			</div>

			<div id="hour4" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour4Table" class="hourTable">
						<tr>
							<td id="hour4AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour4AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour4Table" class="hourTable">
						<tr>
							<td id="hour4RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour4RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="hour8" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour8Table" class="hourTable">
						<tr>
							<td id="hour8AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour8AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour8Table" class="hourTable">
						<tr>
							<td id="hour8RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour8RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="hour12" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour12Table" class="hourTable">
						<tr>
							<td id="hour12AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour12AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour12Table" class="hourTable">
						<tr>
							<td id="hour12RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour12RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="hour16" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour16Table" class="hourTable">
						<tr>
							<td id="hour16AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour16AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour16Table" class="hourTable">
						<tr>
							<td id="hour16RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour16RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="hour20" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour20Table" class="hourTable">
						<tr>
							<td id="hour20AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour20AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour20Table" class="hourTable">
						<tr>
							<td id="hour20RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour20RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="hour24" class="hourBlock">
				<div class="barBlock">
					<table id="AverageHour24Table" class="hourTable">
						<tr>
							<td id="hour24AverageRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour24AverageUsed" class="column"></td>
						</tr>
					</table>
				</div>
				<div class="barBlock">
					<table id="RealHour24Table" class="hourTable">
						<tr>
							<td id="hour24RealRest" class="column"></td>
						</tr>
						<tr>
							<td id="hour24RealUsed" class="column"></td>
						</tr>
					</table>
				</div>
			</div>
			<div style="clear:both"></div>
			<div>
				<table id="axisHourly">
					<tr>
						<td class="axisColumn" id="aColumn0"><p class="axisText">0-4</p></td>
						<td class="axisColumn" id="aColumn1"><p class="axisText">4-8</p></td>
						<td class="axisColumn" id="aColumn2"><p class="axisText">8-12</p></td>
						<td class="axisColumn" id="aColumn3"><p class="axisText">12-16</p></td>
						<td class="axisColumn" id="aColumn4"><p class="axisText">16-20</p></td>
						<td class="axisColumn" id="aColumn5"><p class="axisText">20-24</p></td>
					</tr>
				</table>
			</div>
		</div>

		<div id="CurveDataXaxis" style="padding-top:2%; float:left;  margin-top:-5px"><p id="CurveDataXaxisText" style="font-size:80%; font-family:Trebuchet MS"></p></div>
	</div>
	
	<!--
	<div id="WarningBlock" style="position:absolute; margin:1 auto"></div>
	-->


	<div id="RightBlock" style="position:absolute; margin:1 auto">
<!--<div id="BillingCycle" ></div>-->
		<div id="CurrentPower" style="margin:1 auto"></div>
		<div id="BarDataTitle" style="margin: 1 auto"></div>

		<div id="Meter" style="position:absolute; margin: 1 auto">
			<div class="barTierBlock">
				<table class="oneTierBlock" id="Tier1">
					<tr>
						<td id="Tier1Rest"></td>
					</tr>
					<tr>
						<td id="Tier1Used"></td>
					</tr>
				</table>
				<div><p style="font-size: 60%; font-family:Trebuchet MS; text-align:right">Tier 1</br>$0.045</p></div>
			</div>
			<div class="barTierBlock">
				<table class="oneTierBlock" id="Tier2">
					<tr>
						<td id="Tier2Rest"></td>
					</tr>
					<tr>
						<td id="Tier2Used"></td>
					</tr>
				</table>
				<div><p style="font-size: 60%; font-family:Trebuchet MS; text-align:right">Tier 2</br>$0.076</p></div>
			</div>
			<div class="barTierBlock">
				<table class="oneTierBlock" id="Tier3">
					<tr>
						<td id="Tier3Rest"></td>
					</tr>
					<tr>
						<td id="Tier3Used"></td>
					</tr>
				</table>
				<div><p style="font-size: 60%; font-family:Trebuchet MS; text-align:right">Tier 3</br>$0.184</p></div>
			</div>
			<div class="barTierBlock">
				<table class="oneTierBlock" id="Tier4">
					<tr>
						<td id="Tier4Rest"></td>
					</tr>
					<tr>
						<td id="Tier4Used"></td>
					</tr>
				</table>
				<div><p style="font-size: 60%; font-family:Trebuchet MS; text-align:right">Tier 4</br>$0.224</p></div>
			</div>
		</div>
	</div>
	<!--
	<div id="CalPlugLogo" style="position:absolute; margin:1 auto"></div>
	-->
	<div  id="ContactAndWarning"style = "position:absolute; border-radius:25px"></div>
</div>



<!--
<div id="TestArea1", style="position:absolute; top:1150px; left:0px;margin:auto"></div>
<div id="TestArea2", style="position:absolute; top:1250px; left:0px;margin:auto"></div>
-->
</body>
</html>
