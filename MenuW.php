<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
      
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
        
        p {
            font-family:Trebuchet MS;
            color:#FFFFFF;
            margin:0; padding:0;
        }
        #mainFrame {
            background-color: rgba(34,85,136,1.0);
            border-style: solid;
            border-color: #FFFFFF;
            border-width: 4px;
            /*border-radius: 20px;*/

        }

    </style>

    
    
<meta charset="UTF-8">
<title>Household Energy Consumption Display</title>
<script src="js/jquery-1.8.2.js"></script>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="bootstrap/js/bootstrap.min.js"></script>
      
<script src="js/jquery.simpleWeather-2.3.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script> 
<script src="js/carhartl-jquery-cookie-92b7715/jquery.cookie.js"></script>
    
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">    
    
    
<script type='text/javascript'>
window.onerror=function(){
	return true;
}

//navigator.setResolution(1920,1080);
/*
*			Setup Global Information
*/
var userZipcode = '92617';
var tempUnit = 'f';
var screenWidthNew = 1920/100*16;
var screenWidth = 1920;
var screenHeightNew = 1060*26/100;
var screenHeight = 1060;
	
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

var px = "px";
    
// ----------------------------------------------begin render the html-----------------------------------------------
$(document).ready(function(){
	
	$(function PageSetup() {  
		// show background
        //$('#mainFrame').css({"top":"0px","left":(screenWidth-screenWidthNew)+px,"width":screenWidthNew+80+px,"height":screenHeightNew+50+px});
		
        setTimeout('refreshHTML()',600000); 	//Zhimin add
		
        ProcessDataFromDatabase();
		
        var ProcessDataTimer=setInterval(function(){ProcessDataFromDatabase()},1000000);
		//$("#TestArea1").html("bill:"+bill+" billToday:"+billToday+" percentage2NextTire:"+percentage2NextTire+" currentTire:"+currentTire+" currentRate: "+currentRate);
	});
	
});
    
    
//------------------------------------------------------------------------------------------------
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
                    
                    
                    $("#hour").html(data[3][0].sum + ' ' + data[3][0].budget);
                    hour_con_bud = data[3];
                    $("#hour_con_bud").html(hour_con_bud[0].sum);
                    
                    
                    $("#power").html(data[4].currentpower);
                    timestamp = data[4].timestamp;
                    conspt = data[4].conspt;
                    power = data[4].currentpower;
        
    tier = CalcPriceTier(time_day, cycle_start, timestamp, conspt, day_conspt, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj);
    var bill = tier[0], billToday = tier[1];
    
   
		// show widget title
	
	//var heightTitle = screenHeight*0.05;
	//var widthTitle = screenWidthNew;
	
	//$('#widgetTitle').css({"width":widthTitle+px, "height":heightTitle+px, "margin-top":0.4*heightTitle+px});
	//$('#widgetTitle').html('<div style="float:left; margin-left:8%"><p style="vertical-align:middle; text-align:center; font-family:Trebuchet MS; font-size: 120%">Smart Energy</p></div><img src="./img/smarttitle.png" style="float:left;height:90%; margin-left:3%"/>');
   
    
	/*
	*			Show Bills   //IE6 does not support
    */
    
	UpdateTodayCharges("#TodayCharge", billToday);
	UpdateMonthlyCharges("#MonthlyCharge", bill);

   });

    });

}



function UpdateTodayCharges(id, billToday){
	var htmlContent = '<p style="font-size:150%;text-align:left; ">Today&nbsp&nbsp&nbsp&nbsp<span style="font-size:120%">$'+billToday+'</span></p>'
	$(id).html(htmlContent);
}

function UpdateMonthlyCharges(id, bill){
	var htmlContent = '<p style="font-size:150%;text-align:left">Monthly&nbsp<span style="font-size:120%">$'+bill+'</span></p>'
	$(id).html(htmlContent);
}



function GetZipCode(locObj) {
	return locObj.location[1].zipcode;
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
        
        
        var percentage2Nexttier = 0.0, currenttier = 0, currentRate = 0.0, nextRate = 0.0;
 
        
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
    <div class = "row" style = "margin-top:10%">
        <div id="mainFrame" class="withboarder col-lg-1.1 col-lg-offset-10" >
            <div class = "row">
                <div id="widgetTitle" class = "col-lg-10 col-lg-offset-1" style = "margin-top:5%">
                    
                    <img src="./img/smarttitle.png" style="float:left; height:100%"/>
                    <p style="font-size: 200%">Smart Energy</p>
                </div>
            </div>
            
            <div class = "row">
                <div id="billsTitle" class = "col-lg-8 col-lg-offset-2"><p style="font-size:180%;text-align:left">Current Bills</p></div>
            </div>
            <div class = "row">
                <div id="TodayCharge" class = "col-lg-8 col-lg-offset-2"></div>
            </div>
            <div class = "row">
                <div id="MonthlyCharge" class = "col-lg-8 col-lg-offset-2" style = "margin-bottom:5%"></div>
            </div>
        </div>
    </div>
    
</body>
</html>
