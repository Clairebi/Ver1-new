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
		border-style: solid;
		border-color: #000000;
		border-width: 7px;
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
    #NexttierLevel {
        font-size:30px;
    }
	@font-face {font-family:LEDFont;src:url(fonts/DS-DIGI.TTF);}
	
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

    
    
<meta charset="UTF-8">
<title>Household Energy Consumption Display</title>
<script src="js/jquery-1.8.2.js"></script>
<script src="js/highcharts.js"></script>
<script src="js/highcharts-more.js"></script>
<!--<script src="js/modules/exporting.js"></script>-->
<script src="js/jquery.simpleWeather-2.3.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
    <script src="js/carhartl-jquery-cookie-92b7715/jquery.cookie.js"></script>

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
var userZipcode = '92617';
var tempUnit = 'f';
var screenWidthNew = 1920/5;
var screenWidth = 1920;
var screenHeight = 1060;
    
    
//navigator.setResolution(1920,1080);	
/*          Theme Color
*
 */          
var mainThemeColor = '#E9BE00';
var secondThemeColor = '#DFD6A5';
var strongThemeColor = '#E9BE00';
var newMainThemeColor = "#FFFFFF";
    
        $.cookie("yesterday");
        $.cookie("last_cycle_start");
        $.cookie("lastMo");
        $.cookie("bill");
        $.cookie("billToday");
        
        var userZipcode;
        var date = new Date();
        var time_day;
        var cycle_start;
        var timestamp;
        var conspt;
        var day_conspt;
        var day_budget;
        var cycle_conspt;
        var cycle_budget;
        var week_sum = new Array();
        var hour_con_bud = new Array();
        var power;
        
        var tier;
        var bill;
        var billToday;
        
        var billingStartDate = 11;
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
    
    if (date.getDate() >= billingStartDate) {
        daysSinceThisBillingCycle = date.getDate() - billingStartDate + 1;
    } else {
        daysSinceThisBillingCycle = getNumberDaysInMonth(billingStartMonth, date.getFullYear()) - billingStartDate + 1 
            + date.getDate();
    }
        // Billing Cycle Length
    billingCycleLength = getNumberDaysInMonth(billingStartMonth, date.getFullYear()) - billingStartDate + 1 + 14;


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
	
var tierWidth = screenWidth/4 - 3*marginVal;
var tierHeight = screenHeight/16*15 - 2*marginVal;
var billingCycleWidth = tierWidth*8/10 - marginVal;
var billingCycleHeight = tierHeight/11*3 - 2*marginVal;

var currentPowerWidth = tierWidth - marginVal;
//var currentPowerHeight = tierHeight/9*1 - 2*marginVal;
var currentPowerHeight = screenHeight/18*3 - 2*marginVal; //zhimin modify

var meterWidth = tierWidth -marginVal;
var meterHeight = tierHeight/9*4 -2*marginVal;
	
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
	
var barDataBlockWidth = screenWidth/5 - 2*marginVal;
var barDataBlockHeight = screenHeight/16*15 - 2*marginVal;

var barDataTitleWidth = currentPowerWidth;
var barDataTitleHeight = currentPowerHeight;

var curveDataCurIndex = 0;

    
// ----------------------------------------------begin render the html------------------------------------------------------------------------------------------
$(document).ready(function(){
	
	$(function PageSetup() {  
		// show background
        $('#mainFrame').css({"top":"0px","left":(screenWidth-screenWidthNew)+px,"width":screenWidthNew+px,"height":screenHeight+px});
       

		// show cumulative usage
        $('#BarDataBlock').css({"top":'0px',"left":'0px',"width":barDataBlockWidth+px,"height":barDataBlockHeight+px});
        $('#BarDataTitle').css({"width":barDataTitleWidth+px,"height":6/7*barDataTitleHeight+px, "padding-top":1/7*barDataTitleHeight+px,"vertical-align":"middle"});
		$('#BarData1stBlock').css({"width":barDataBlockWidth/2-marginVal*2+px,"height":barDataBlockHeight/100*42+px, "margin-top":"20px"});
        $('#BarData2ndBlock').css({"width":barDataBlockWidth/2-marginVal*2+px,"height":barDataBlockHeight/100*42+px, "margin-top":"20px"});
        
		// show alert module
		$('#ContactAndWarning').css({"height":screenHeight*0.15+px, "width":screenWidthNew*0.9+px, "background-color":"rgb(65,65,65, 0.8)", "margin-left":screenWidthNew*0.05+px});
		
		
		/*
		*			Show charts
         */
        // update every 10 mins
		
        setTimeout('refreshHTML()',600000); 	//Zhimin add
		
        ProcessDataFromDatabase();
		
        var ProcessDataTimer=setInterval(function(){ProcessDataFromDatabase()},10000);
		//$("#TestArea1").html("bill:"+bill+" billToday:"+billToday+" percentage2Nexttier:"+percentage2Nexttier+" currenttier:"+currenttier+" currentRate: "+currentRate);
	});
	
});
function refreshHTML()
{
    window.location.reload(true);
}

function ProcessDataFromDatabase() {
	
    $(document).ready(function(){
                /* call the php that has the php array which is json_encoded */
                $.getJSON('php/get.php', function(data) {
                        /* data will hold the php array as a javascript object */
                    
                    $("#day").html(data[0].sum + ' ' + data[0].budget);
                    time_day = data[0].time_day;
                    day_conspt = data[0].sum;
                    day_budget = data[0].budget;
                    
                    $("#cycle").html(data[1].sum + ' ' + data[1].budget);
                    cycle_start = data[1].cycle_start;
                    cycle_conspt = data[1].sum;
                    cycle_budget = data[1].budget;
                    
                    $("#week").html(data[2][0].weekdays + ' ' + data[2][0].weekends);
                    week_sum = data[2];
                    
                    var leng = week_sum.length;
 //                   for (var i = 0; i<8; i++){
 //                       if (week_sum[i] == NULL)    {week_sum[i].weekdays = 0; week_sum[i].weekends = 0;}
//                    }
                    $("#week").html(week_sum[0].weekdays);
                    
                    
                    
                    $("#hour").html(data[3][0].sum + ' ' + data[3][0].budget);
                    hour_con_bud = data[3];
                    $("#hour_con_bud").html(hour_con_bud[0].sum);
                    
                    
                    $("#power").html(data[4].currentpower);
                    timestamp = data[4].timestamp;
                    conspt = data[4].conspt;
                    power = data[4].currentpower;
        
    tier = CalcPriceTier(time_day, cycle_start, timestamp, conspt, day_conspt, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj);
    var bill = tier[0], billToday = tier[1];
	
    curveDataCurIndex = 1 - curveDataCurIndex;

		// show widget title
	
	var heightTitle = screenHeight*0.05;
	var widthTitle = screenWidthNew;
	
	$('#widgetTitle').css({"width":widthTitle, "height":heightTitle, "background-color":'#000000'});
	$('#widgetTitle').html('<div style="float:left; margin-left:20%"><p style="vertical-align:middle; text-align:center; font-family:Trebuchet MS; font-size: 90%">Smart Energy</p></div><img src="./img/smarttitle_ori.png" style="float:left;height:90%; margin-left:3%"/>');
   
     /*
	*			Show Costs //IE6 supports
    */
   // $('#BarDataTitle').html("<p style=\"font-size:75%; font-family:Trebuchet MS\">Current Rate</p><span style=\"font-size:110%;color:#FFFFFF; font-famliy:Rockwell\">&nbsp$" + tier[4].toFixed(3) + "</span><span style=\"font-size:20px;\">&nbsp&nbsp&nbsp</span>");
	/*
	*			Show Bars   //IE6 does not support
    */
    barDataToday = {id:'#BarData1stBlock',title:"<br>Today",maximum:day_budget,value:day_conspt, cost:billToday};
	barDataThisMonth = {id:'#BarData2ndBlock',title:"<br>This Month",maximum:cycle_budget,value:cycle_conspt,cost:bill};
	
	$(UpdateBar(parseFloat(day_budget/1000).toFixed(0), parseFloat(day_conspt/1000).toFixed(0), parseFloat(cycle_budget/1000).toFixed(0), parseFloat(cycle_conspt/1000).toFixed(0), curveDataCurIndex, bill, billToday));	

	/*
	*
	* show warning
	*/
	if (barDataToday.value > barDataToday.maximum){
		message =  '<p style="font-size:75%; font-family:Trebuchet MS; margin-top:10px; margin-left: 10%; text-align:left" >Demand Response Event Alert:</p>';
	}
	else{
		message =  '<p style="font-size:75%; font-family:Trebuchet MS; margin-top:10px; margin-left: 10%; text-align:left" >Demand Response Event Alert:</p><p style="font-size:70%; font-family:Trebuchet MS;  color:#FF6600;  margin-left: 10%; text-align:left" >Save Power Day Event Today!</p>';
	}
	$('#ContactAndWarning').html(message);
	
	
	/*
	* 
	* Show Billing Cycle //IE6 supports
	*
	*/
	UpdateBillingCycle("#BillingCycle");   
	    
	
    /*
    * 
    * 		Show Electric Meter  //IE6 supports, but the format changed
    *
    */ 
	
    UpdateCurrentPower("#CurrentPower", power); 
	
	/*
	*
	*  Show current charges
	*
	*/
	UpdateCurrentCharges("#CurrentCharges", bill); 
        
        });

    });
}

function UpdateCurrentCharges(currentPowerId, bill) {
	$(currentPowerId).html("<p style=\"font-size:75%;text-align:left; font-family:Trebuchet MS; margin-left:10px\">Current Charge</p><span style=\"font-size:100%;font-family:rockwell;color:#FFFFFF; font-weight:100\"> $" + bill + "</span>");
}


function UpdateCurrentPower(currentPowerId, power) {
        var lastHourPower = power;     

        $("#CurrentPower").html("<p style=\"font-size:75%;text-align:left;font-family:Trebuchet MS;margin-left:10px\">Current Power Demand</p><span style=\"font-size:100%;font-family:rockwell;color:#FFFFFF;font-weight:100\"> " + parseFloat(power/1000).toFixed(2) + "</span><span style=\"color:#FFFFFF;\">kw</p>");
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
	var title = "<p style=\"vertical-align:middle;text-align:left;font-size:75%;font-family:Trebuchet MS; margin-left:10px\">Billing Cycle Information</p>";
	var table;
	table = "<table id='BillingCycleTable'>";
	table += "<tr>";
	
    table += "<th style=\"font-family:Trebuchet MS; background-color:#557766\">"+ nextBillingStartMonth + "</th>";
	//table += "<th>"+ "Feb" + "</th>" //zhimin add
    
	table += "<td rowspan=\"2\" style=\"background-color:#FFDD44\"><p>&nbsp&nbsp" + daysLeft + "&nbsp&nbsp</p><p style=\"font-size:40%; font-family:Trebuchet MS\">Days Left</p></td>";
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

/*table updatebar */
    function UpdateBar(estimatedDailyUsage, todayUsage, estimatedMonthlyUsage, thisCycleUsage, curveDataCurIndex, billMonth, billToday){
        var heightBar = barDataBlockHeight/100*27;
        $('#Bar1st').css({"border":"3px solid white", "width":barDataBlockWidth*0.5/2-marginVal*2+px,"height":heightBar+px, "margin-top":"4%"});
        var heightUsed = todayUsage/Math.max(estimatedDailyUsage, todayUsage)*heightBar;
        heightUsed = Math.max(heightUsed, 0.01);
        if (todayUsage < estimatedDailyUsage/4*3) // green
            colorOfBar = '#55BF3B';
        else if (todayUsage < estimatedDailyUsage/5*4) // yellow
            colorOfBar = '#FACC2E';
        else // red
            colorOfBar = '#FF6600';
        $('#usedBar1st').css({"height":heightUsed +px, "background-color":colorOfBar});
        $('#titleBar1st').css({"font-family":"rockwell", "font-size":"70%"});
        var maxUsageToday = Math.max(estimatedDailyUsage, todayUsage);
        var maxBillToday = maxUsageToday*billToday/todayUsage;
        if(curveDataCurIndex==1)
            $('#titleBar1st').html(maxUsageToday+'</br>kwh');
        else
            $('#titleBar1st').html('&nbsp</br>$'+maxBillToday.toFixed(2));
        //update today usage
        if(curveDataCurIndex==1){
            if(heightUsed>0.3*heightBar)
                $('#usedBar1st').html('<p style="font-size:70%; font-family:rockwell">'+todayUsage+'<br>kwh</p>');
            else
                $('#restBar1st').html('<p style="font-size:70%; font-family:rockwell">'+todayUsage+'<br>kwh</p>');
        }
        else{
            if(heightUsed>0.3*heightBar)
                $('#usedBar1st').html('<p style="font-size:70%; font-family:rockwell">$'+billToday+'</p>');
            else
                $('#restBar1st').html('<p style="font-size:70%; font-family:rockwell">$'+billToday+'</p>');
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
        else
            $('#titleBar2nd').html('&nbsp</br>$'+maxBillCycle.toFixed(2));
        if(curveDataCurIndex==1){
            if(heightUsed>0.3*heightBar)
                $('#usedBar2nd').html('<p style="font-size:70%; font-family:rockwell">'+thisCycleUsage+'<br>kwh</p>');
            else
                $('#restBar2nd').html('<p style="font-size:70%; font-family:rockwell">'+thisCycleUsage+'<br>kwh</p>');
        }
        else{
            if(heightUsed>0.3*heightBar)
                $('#usedBar2nd').html('<p style="font-size:70%; font-family:rockwell">$'+billMonth+'</p>');
            else
                $('#restBar2nd').html('<p style="font-size:70%; font-family:rockwell">$'+billMonth+'</p>');
        }
    }



function GetZipCode(locObj) {
	return locObj.location[1].zipcode;
	//return xmlDoc.getElementsByTagName("zipcode")[0].childNodes[0].nodeValue;
}

    
function CalcPriceTier(time_day, cycle_start, timestamp, conspt, consumptionToday, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj) {
        consumptionToday = consumptionToday/1000;

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
        var tierLen = ratesObj.rates.length;
        for (var i = 0; i < tierLen; ++i) {
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
        // calculate tier level
        // Note: here I separate the dataByThisMonth and datayByToday is because of display reason.
        // for the data is incomplete, I have to select the specific day instead of today.
        
        
        var percentage2Nexttier = 0.0, currenttier = 0, currentRate = 0.0, nextRate = 0.0;
 //       var date = new Date();
 //       var tomonth = date.getMonth();
 //       var toyear = date.getFullYear();
//        var today = timeStamp[6*(consumption.length-1)+2];
        
 //       var monthBilling = (today>=billingStartDate?tomonth:tomonth-1);
//        var yearBilling = toyear;
        
        //var count = 0;
        
        var bill = $.cookie("bill");
        var billToday = $.cookie("billToday");
            
                    if ($.cookie("yesterday") != time_day) {
                        $.cookie("yesterday",time_day);
                        //consumptionToday = consumption[i];
                        //bill += billToday;
                        billToday = 0.0;
                        
                        if ($.cookie("last_cycle_start") != cycle_start) {
                            $.cookie("last_cycle_start",cycle_start);
                            bill = 0.0;
                            billToday = 0.0;
                        }
                    }
                

                    if (consumptionToday <= baseline * upperbounds[0]) {
                        currenttier = 0;
                        percentage2Nexttier = (consumptionToday - baseline*lowerbounds[0]) / (baseline * (upperbounds[0] - lowerbounds[0]));
                        currentRate = prices[0];
                        nextRate = prices[1];
                    } else if (consumptionToday <= baseline*upperbounds[1]) {
                        currenttier = 1;	
                        percentage2Nexttier = (consumptionToday - baseline*lowerbounds[1]) / (baseline * (upperbounds[1] -lowerbounds[1]));
                        currentRate = prices[1];
                        nextRate = prices[2];
                    } else if (consumptionToday <= baseline*upperbounds[2]) {
                        currenttier = 2;
                        percentage2Nexttier = (consumptionToday - baseline*lowerbounds[2]) / (baseline * (upperbounds[2] - lowerbounds[2]));
                        currentRate = prices[2];
                        nextRate = prices[3];
                    } else  {
                        currenttier = 3;
                        percentage2Nexttier = 1.0;
                        currentRate = prices[3];
                        nextRate = prices[3];
                    }
                    
                    billToday = parseFloat(billToday);
                    bill = parseFloat(bill);
        
                    if ($.cookie("lastMo") != timestamp) {
                        
                        $.cookie("lastMo", timestamp);
                        
                        billToday += currentRate * (conspt/1000);
                        
                        bill += currentRate * (conspt/1000);
                    }
                    
                  //  if (i == consumption.length-1) {
                    
                  //  }
                   //alert($.cookie("yesterday") +'     ' + time_day);
       $.cookie("bill", bill); 
       $.cookie("billToday", billToday);    
        
        
        return ([bill.toFixed(2), billToday.toFixed(2), percentage2Nexttier.toFixed(2),currenttier+1, currentRate, nextRate]);
        
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
<div id="mainFrame" class="withboarder" style="position:absolute;margin-top:25px;margin-left:-40px">


	<div id="BarDataBlock" style="position:absolute; margin:1 auto">
		<div id="widgetTitle"></div>
		<div id="BillingCycle" ></div>
		<div id="CurrentCharges" style="margin-top:10px"></div>
		<div id="CurrentPower" style="margin-top:10px"></div>
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
			<p style="font-size:65%;font-family:Trebuchet MS">Today's </br>Budget</p>
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
			<p style="font-size:65%;font-family:Trebuchet MS">This Month's</br>Budget</p>
		</div>
		<div style="clear:both"></div>
		<div  id="ContactAndWarning"style = "position:absolute; border-radius:25px"></div>
	</div>
</div>


</body>
</html>
