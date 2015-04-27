<?php
session_start();
$u = "";

// Make sure the _GET username is set, and sanitize it
if(isset($_GET["u"])){
	$u = preg_replace('#[^a-z0-9]#i', '', $_GET['u']);
    //echo $u;
} else {
   // header("location: http://www.calit2.uci.edu/");
   // exit();	
   $u ='calplug';
}
?>
<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/jquery.simpleWeather-2.3.min.js"></script>
    <script src="js/jquery-1.8.2.js"></script>
    <script src="js/highcharts.js"></script>
    <script src="js/highcharts-more.js"></script>

    <script src="js/jquery.simpleWeather-2.3.min.js"></script>
    <script src="js/jquery-ui-1.10.3.custom.min.js"></script>    
<!--    <script src="js/carhartl-jquery-cookie-92b7715/jquery.cookie.js"></script> -->
        
    <style>
        @font-face {font-family:rockwell;src:url(fonts/RockwellStd.otf);}
        body{
            font-family: "rockwell";
            font-weight:bold;
            font-size: 40px;
            color: #FFFFFF;
            background-color:black;
    /*		background-image:url('img/CNN tv background.jpg');
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
            background: rgba(92,92,92,0.65);

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
        #DateDisp, #WeatherDisp, #Welcome, a{
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
        
    <title>Household Energy Consumption Display</title>
    
    
    
   
    
    
        
        
        
    <script type='text/javascript'>
        window.onerror=function(){
            return true;
        }

        //navigator.setResolution(1920,1080);
        /*
        *			Setup Global Information
        */
        //var userZipcode = '92617';
        var tempUnit = 'f';
        var screenWidth = 1920; //1920;
        var screenHeight =1080;// 1080;	
        /*          
        *   Theme Color
         */          
        var mainThemeColor = '#E9BE00';
        var secondThemeColor = '#DFD6A5';
        var strongThemeColor = '#E9BE00';
        var newMainThemeColor = "#FFFFFF";
        
        var pos = 1;
        var userZipcode;
        var date = new Date();
        var time_day;
        var cycle_start;
        var timestamp;
        var conspt;
        var day_conspt;
        var day_budget;
        var day_cost;
        var cycle_conspt;
        var cycle_budget;
        var cycle_cost;
        var week_sum = new Array();
        var hour_con_bud = new Array();
        var power;
        
        var tier;
        var bill;
        var billToday;
        
        var billingStartDate = 13;
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

        var barDataBlockWidth = screenWidth/4 - 2*marginVal;
        var barDataBlockHeight = screenHeight/16*15 - 2*marginVal;

        var barDataTitleWidth = currentPowerWidth;
        var barDataTitleHeight = currentPowerHeight;
        
        var curveDataCurIndex = 0;

        // ----------------------------------------------begin render the html-----------------------------------------------
        $(document).ready(function(){

        	

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
            $('#EdisonLogo').css({"top":infoBlockHeight+marginVal+px, "left":2*marginVal+tierWidth+px,"width":EdisonLogoWidth+px,"height":EdisonLogoHeight+px}).html("<img src=\"./img/EdisonLogo.png\" alt=\"Edison Logo\" height=\""+EdisonLogoHeight+"\">");
            // show curve data block
            $('#CurveDataBlock').css({"top":infoBlockHeight+marginVal*7+EdisonLogoHeight+px,"left":20*marginVal+tierWidth+px,"width":0.85*dataBlockWidth+px,"height":0.75*dataBlockHeight+px});
            // show curve data block title
            //$('#CurveDataTitle').css({"width":dataBlockTitleWidth+px,"height":dataBlockTitleHeight+px});
            //$('#CurveDataUsage').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.7*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});

            $('#CurveDataUsage').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.6*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});
            $('#CurveDataUsageHourly').css({"width":0.85*dataBlockCurveDataUsageWidth+px,"height":0.6*dataBlockHeight+px, "padding-top":0.05*dataBlockHeight+px});


            // show warning block  
            //$('#WarningBlock').css({"top":infoBlockHeight*0.4+marginVal*2+EdisonLogoHeight+dataBlockHeight+px,"left":2*marginVal+tierWidth+WarningBlockLeft+px,"width":WarningBlockWidth+px,"height":WarningBlockHeight+px, "padding-left":"8%"});

            // show 
            $('#RightBlock').css({"top":infoBlockHeight+marginVal*2+px, "left":4*marginVal+tierWidth+dataBlockWidth+px,"width":tierWidth+px,"height":tierHeight+px});
            // show tier
            $("#BillingCycle").css({"width":billingCycleWidth+px,"height":1*billingCycleHeight+px, "padding-top":1/7*currentPowerHeight+px, "margin-left":2/11*billingCycleWidth});
            // show current power
            $('#CurrentPower').css({"width":currentPowerWidth+px,"height":6/7*currentPowerHeight+px, "padding-top":1/7*currentPowerHeight+px, "border":"Gray", "border-bottom-style":"solid"});
            $('#Meter').css({"margin-left":-meterWidth*0.1, "margin-top":-meterHeight*0.05, "width":meterWidth*1.1+px,"height":meterHeight*1.4+px});

            $('#NexttierLevel').css({"top":marginVal*3+currentPowerHeight+billingCycleHeight+meterHeight/10*7+px,"left":meterWidth/2+px,"width":meterWidth/2+px,"height":meterHeight/4+px});

             
            // update every 10 mins

            setTimeout('refreshHTML()',600000); 	//Zhimin add

            ProcessDataFromDatabase();

            var ProcessDataTimer=setInterval(function(){ProcessDataFromDatabase()},10000);

        });

    });
//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------        
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
                    day_cost = data[0].cost;
                    
                    $("#cycle").html(data[1].sum + ' ' + data[1].budget);
                    cycle_start = data[1].cycle_start;
                    cycle_conspt = data[1].sum;
                    cycle_budget = data[1].budget;
                    cycle_cost = data[1].cost;
                    
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
        
        var bill;
        var billToday;
        bill = parseFloat(cycle_cost).toFixed(2), billToday = parseFloat(day_cost).toFixed(2);
                    
        /*---------------------------------------------------------------------------------------------
        *			Show weekly & hourly charts   //IE6 does not support
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
            UpdateWeeklyChart('#CurveDataUsage', week_sum);
            $('#redText').html('Weekends');
            $('#blueText').html('Weekdays');
            $('#ComparisonPic').css({"visibility":"visible"});
            
        } else {
            $('#CurveDataTitleText').html("Hourly Usage");
            $('#CurveDataXaxisText').html("Hours Ago");
            $('#CurveDataUsage').css({"visibility":"hidden", "position":"absolute", "top":"0px", "left":"0px"});
            $('#CurveDataUsageHourly').css({"position":"","float":"left","visibility":"visible"});
            $('#CurveDataXaxis').css({'margin-left':'40%'});
            //$('#legendImg').attr("src","img/legendHourly.png");
            //$('#legendImg').attr("height","30%");
            //$('#legendImg').attr("width","30%");
            UpdateHourlyChart('#CurveDataUsageHourly', hour_con_bud);
            $('#redText').html('Actual');
            $('#blueText').html('Average');
            $('#ComparisonPic').css({"visibility":"hidden"});
        }

        curveDataCurIndex = 1 - curveDataCurIndex;
                    
        /*---------------------------------------------------------------------------------------------
        *			Show Bars   //IE6 does not support
        */

        $(UpdateBar(parseFloat(day_budget/1000).toFixed(0), parseFloat(day_conspt/1000).toFixed(0), parseFloat(cycle_budget/1000).toFixed(0), parseFloat(cycle_conspt/1000).toFixed(0), curveDataCurIndex, bill, billToday));
        
        if (pos == 0)   {$('#ComparisonPic').css({"top":"365px"});}
        if (pos == 1)   {$('#ComparisonPic').css({"top":"500px"});}
        /*---------------------------------------------------------------------------------------------
        *			Show Costs //IE6 supports
        */
        $('#BarDataTitle').html("<p style=\"font-size:75%; font-family:Trebuchet MS\">Current Rate</p><span style=\"font-size:110%;color:#FFFFFF; font-famliy:Rockwell\">&nbsp$" + tier[2].toFixed(3) + "</span><span style=\"font-size:20px;\">&nbsp&nbsp&nbsp</span>");

        /*---------------------------------------------------------------------------------------------
	    *			Show Warning   //IE6 supports
        */
        var widthForWarningString = $('#CurveDataBlock').css("width");
        var heightForWarningString = $('#CurveDataBlock').css("height");
        var widthForWarning = parseFloat(widthForWarningString.substr(0, widthForWarningString.length-2))*0.7;
        var heightForWarning = parseFloat(heightForWarningString.substr(0, heightForWarningString.length-2))*0.38;
        //alert(widthForWarningString);
        $('#ContactAndWarning').css({"top":(infoBlockHeight+marginVal*7+EdisonLogoHeight+0.75*dataBlockHeight)*1.13+px, "left":(2*marginVal+tierWidth)*1.45+px, "height":heightForWarning+px, "width":widthForWarning+px, "background-color":"rgb(65,65,65, 0.8)"});
        var message = "";
        message =  '<p style="font-size:80%; font-family:Trebuchet MS; margin-top:3px; color:#FF6600" >Save Power Day Event Today!<br/>Reduce your energy usage </br>2pm-6pm on 11/06/14</p><p style="font-size:80%; font-family:Trebuchet MS; margin-top:10px">Questions For SCE: 1-800-655-4555</p>';
        $('#ContactAndWarning').html(message);

        //show greeting
        //var welcomeMessage="Welcome "+locObj.location[1].user+"!";
        var welcomeMessage="Welcome <?php echo $u; ?> <a href = 'http://localhost/opt_zt/logout.php'>Log out</a>";
        $('#Welcome').html(welcomeMessage);
        
        /*---------------------------------------------------------------------------------------------
        *			Show Weather
        */
        userZipcode = GetZipCode(locObj);
        $(UpdateTimeWeather(userZipcode));	

        /*--------------------------------------------------------------------------------------------- 
        * Show Billing Cycle //IE6 supports
        */
        UpdateBillingCycle("#BillingCycle");   
        /*---------------------------------------------------------------------------------------------
        *			Show tier  //IE6 doesnot support
        */	    
        Updatetier(tier,"#Meter");  

        /*---------------------------------------------------------------------------------------------
        * 		Show Electric Meter  //IE6 supports, but the format changed
        */ 

        UpdateCurrentPower("#CurrentPower", power);
    });

    });
                
                
    }
     
        
        
        
        
        
        
        
        
        
    /*----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    * 		Show current power in Electric Meter
    */ 

    function UpdateCurrentPower(currentPowerId, power) {
        var lastHourPower = power;
        

        $("#CurrentPower").html("<p style=\"font-size:75%;font-family:Trebuchet MS\">Current Power Demand</p><span style=\"font-size:200%;font-family:Rockwell;color:#FFFFFF;\"> " + parseFloat(power/1000).toFixed(2) + "</span><span style=\"color:#FFFFFF;\">kw</p>");
    }
        
    /*-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       Show Billing Cycle
    */
    function UpdateBillingCycle(billingCycleId) {

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
        
    /*-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *		Show tier
    */   
    function Updatetier(tier, tierId){
        var percentage2Nexttier = tier[0],currenttier = tier[1], currentRate= tier[2], nextRate = tier[3];

        var nextTier = currenttier+1, text;
        var widthtierBlock = $('#Meter').css('width');
        widthtierBlock = parseFloat(widthtierBlock.substr(0, widthtierBlock.length-2));
        var heighttierBlock = $('#Meter').css('height');
        heighttierBlock = parseFloat(heighttierBlock.substr(0, heighttierBlock.length-2));
        //alert(widthtierBlock);
        var widthColumnTotal = widthtierBlock/4;
        var heightFirstColumn = heighttierBlock/2.5;
        var heightStep = (heighttierBlock-heightFirstColumn)/3;
        var widthColumn = 6.5/10*widthColumnTotal;
        var widthMargin = 3.0/10*widthColumnTotal;
        $('.oneTierBlock').css({'width':widthColumn, 'margin-left':widthMargin, 'border-style':'solid', 'border-color': '#FFFFFF'});
        for(var i=1; i<=4; ++i){
            var id = '#Tier'+i;
            $(id).css({'height':heightFirstColumn+(i-1)*heightStep+px, 'margin-top':heighttierBlock-(heightFirstColumn+(i-1)*heightStep)+px});
        }
        var heightUsed=new Array();
        for(var i=1; i<=4; ++i){
            if(i<currenttier){
                var id='#Tier'+i;
                $(id).css({'background-color':'#FF6600'});
                var idRest='#Tier'+i+'Rest';
                $(idRest).attr("valign","top");
                $(idRest).html('<p style="font-family:rockwell; font-size:60%">100%</p>');
            }
            else if(i==currenttier){
                var usedHeight = percentage2Nexttier*(heightFirstColumn+(i-1)*heightStep-8);
                var idUsed='#Tier'+i+'Used';
                var idRest='#Tier'+i+'Rest';
                if (percentage2Nexttier < 3.0/4) // green
                    colorOfBar = '#55BF3B';
                else if (percentage2Nexttier <4.0/5) // yellow
                    colorOfBar = '#FACC2E';
                else // red
                    colorOfBar = '#FF6600';
                $(idUsed).css({'height':usedHeight+px, 'background-color':colorOfBar});
                if(percentage2Nexttier>=0.1){
                    $(idUsed).attr("valign","top");
                    $(idUsed).html('<p style="font-family:rockwell; font-size:60%">'+(percentage2Nexttier*100).toFixed(0)+'%</p>');
                }
                else{
                    $(idRest).attr("valign","bottom");
                    $(idRest).html('<p style="font-family:rockwell; font-size:60%">'+(percentage2Nexttier*100).toFixed(0)+'%</p>');
                }
            }
        }
	
	
    }
    
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       update daily & monthly bars
    */
    function UpdateBar(estimatedDailyUsage, todayUsage, estimatedMonthlyUsage, thisCycleUsage, curveDataCurIndex, billMonth, billToday){
        var heightBar = barDataBlockHeight/5*2.5;
        $('#Bar1st').css({"border":"3px solid white", "width":barDataBlockWidth*0.5/2-marginVal*2+px,"height":heightBar+px, "margin-top":"4%"});
        var heightUsed = todayUsage/Math.max(estimatedDailyUsage, todayUsage)*heightBar;
        heightUsed = Math.max(heightUsed, 0.01);
        if (todayUsage < estimatedDailyUsage/5*3) // green
            {colorOfBar = '#55BF3B';    pos=0;    }
        else if (todayUsage < estimatedDailyUsage/5*4) // yellow
            {colorOfBar = '#FACC2E';    pos=1;    }
        else // red
            {colorOfBar = '#FF6600';    pos=1;    }
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
            {colorOfBar = '#55BF3B';  pos=0;}
        else if (thisCycleUsage < estimatedMonthlyUsage/5*4) // yellow
            {colorOfBar = '#FACC2E';  pos=1;}
        else // red
            {colorOfBar = '#D26464';  pos=1;}
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
    
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       update weekly bars
    */
    function UpdateWeeklyChart(id, week_sum) {
    var category = new Array();
   // var len = Math.min(recentNWeek, recentNWeekdaysUsage.length);
	var len=9;
    for (var i = 1; i < len; ++i) {
        category.push(len-1-i);
    }
	
	var weekDaysVal = new Array();
	var weekEndsVal = new Array();
	var weekTotalVal = new Array();
	
	for(var i=7; i>=0; i--){
		weekDaysVal.push(week_sum[i].weekdays/1000);
	}
	for(var i=7; i>=0; i--){
		weekEndsVal.push(week_sum[i].weekends/1000);
	}
	var maxWeekTotalVal=0;
	for(var i=7; i>=0; i--){
		weekTotalVal.push(weekDaysVal[i]+weekEndsVal[i]);
		if(maxWeekTotalVal<weekTotalVal[i])
			maxWeekTotalVal = weekTotalVal[i];
	}
	
	var totalHeight = parseFloat($('#CurveDataUsage').css('height'));
	var totalWidth = parseFloat($('#CurveDataUsage').css('width'));
	var tableWidth = totalWidth/20;
	var tableHeight = totalHeight*9/10;
	
	//var maxYaxisVal = Math.round(maxWeekTotalVal/5/100)*100*5;
	//var yaxisHeight = maxYaxisVal/maxWeekTotalVal*tableHeight;
        
    /*-- fixed part start --*/
	//var maxYaxisVal = Math.round(maxWeekTotalVal/5/100)*100*5;
	var maxYaxisVal = maxWeekTotalVal;
	//var yaxisHeight = maxYaxisVal/maxWeekTotalVal*tableHeight;
	var yaxisHeight = tableHeight;
	var yaxisBlockHeight = yaxisHeight/6;
	var yaxisWidth = 0.9*tableWidth;
	var diffHeight = tableHeight-yaxisHeight;
	
	$('#divYaxis').css({'color':'#FFFFFF', 'font-size':'30px', 'font-family':'rockwell', 'margin-top':diffHeight, 'margin-right':'20px', 'border-right-style':'solid', 'border-right-width':'1px', 'padding-right':'10px'});
	$('#yaxis').css({'width':yaxisWidth});
	for(var i=0; i<=5; ++i){
		var id='val'+i;
		$('#'+id).css({'height':yaxisBlockHeight});
		var currYaxisVal = maxYaxisVal/5*i;
		if(maxYaxisVal<10 && currYaxisVal!=0)
			currYaxisVal = currYaxisVal.toFixed(1);
		else
			currYaxisVal = Math.round(currYaxisVal/10)*10;
		$('#'+id).html('<p style="color:#FFFFFF;font-size:25px;font-family:rockwell">'+currYaxisVal+'kwh</p>');
	}
	/*-- fixed part end --*/    
    
	
	$('.subdiv').css({'width':tableWidth+px});
	$('.hoursTag').css({'font-family':'Rockwell', 'font-size': '30px'});
	for(var i=0; i<len-1; ++i){
        //alert(i+' '+weekDaysVal[len-1-i-1]+' '+weekEndsVal[len-1-i-1]);
		//var currHeight = weekTotalVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
		var weekEndsHeight = weekEndsVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
		var weekDaysHeight = weekDaysVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
		var emptyHeight = tableHeight-weekEndsHeight-weekDaysHeight;
	/*	
        if(weekEndsHeight==0){
		//	currHeight = weekDaysVal[len-1-i-1]/maxWeekTotalVal*tableHeight;
			weekEndsHeight = 0;
			weekDaysHeight = currHeight;
			emptyHeight = tableHeight-weekEndsHeight-weekDaysHeight;
		}
	*/	
		var id='week'+i;
		$('#divWeek'+i).css({'margin-right':tableWidth});
		$('#'+id).css({'height':tableHeight});
		$('#'+id+'Empty').css({'height':emptyHeight});
		$('#'+id+'Rest').css({'height':weekEndsHeight, 'background-color': '#FF6600'}); 
		$('#'+id+'Used').css({'height':weekDaysHeight, 'background-color': '#ccddcc'}); 
        //alert(i+' '+weekDaysHeight+' '+weekEndsHeight);
	}
}
    
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       update hourly bars
    */
    function UpdateHourlyChart(id, hour_con_bud) {
        
        var est_data_4hour = new Array();
        var actual_data_4hour = new Array();
        
        for(var i=5; i>=0; i--){
            est_data_4hour.push(parseFloat(hour_con_bud[i].budget/1000).toFixed(2));
            actual_data_4hour.push(parseFloat(hour_con_bud[i].sum/1000).toFixed(2));
        }
        
        var max_est_data = Math.max.apply(Math, est_data_4hour);
        var max_actual_data = Math.max.apply(Math, actual_data_4hour);
        var max_data = Math.max(max_est_data, max_actual_data);

        var totalHeight = parseFloat($('#CurveDataUsageHourly').css('height'));
        var totalWidth = parseFloat($('#CurveDataUsageHourly').css('width'));
        var tableWidth = totalWidth/22;
        var tableHeight = totalHeight*9/10;


        //var maxYaxisVal = Math.round(max_data/5/5)*5*5;
        //var yaxisHeight = maxYaxisVal/max_data*tableHeight;
        
        	/*-- fixed part start --*/
        var maxYaxisVal = max_data;
        //var yaxisHeight = maxYaxisVal/max_data*tableHeight;
        var yaxisHeight = tableHeight;
        //alert(maxYaxisVal/max_data);
        //alert(max_data);
        var yaxisBlockHeight = yaxisHeight/6;
        var yaxisWidth = 2.5*tableWidth;
        var diffHeight = tableHeight-yaxisHeight;
        $('.colorLegend').css({'width':0.5*tableWidth, 'height':0.5*tableWidth});
        $('#divYaxisHourly').css({'color':'#FFFFFF', 'font-size':'30px', 'font-family':'rockwell', 'margin-top':diffHeight, 'margin-right':'20px', 'border-right-style':'solid', 'border-right-width':'1px', 'padding-right':'10px'});
        $('#yaxisHourly').css({'width':yaxisWidth});
        //draw the Y axis
        for(var i=0; i<=5; ++i){
            var id='val'+i+'Hourly';
            $('#'+id).css({'height':yaxisBlockHeight});
            var currYaxisVal = maxYaxisVal/5*i;
            if(maxYaxisVal<10 && currYaxisVal!=0)
                currYaxisVal = currYaxisVal.toFixed(1);
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
            $('#hour'+id+'AverageUsed').css({"background-color":"#ccddcc","height":tableHeight*est_data_4hour[i]/max_data});
            $('#hour'+id+'RealRest').css({"height":tableHeight*(1-actual_data_4hour[i]/max_data)});
            $('#hour'+id+'RealUsed').css({"background-color":"#FF6600","height":tableHeight*actual_data_4hour[i]/max_data});
        }

        $('#axisHourly').css({'margin-top':5, 'margin-right':0, 'margin-bottom':0, 'margin-left':tableWidth+yaxisWidth+20});
        $('.axisColumn').css({'width':3*tableWidth, 'font-size':30, 'text-align':'left', 'margin-top':'-10px'});
    }
    
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       time & weather
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
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       zipcode from json
    */    
    function GetZipCode(locObj) {
        return locObj.location[0].zipcode;
        //return xmlDoc.getElementsByTagName("zipcode")[0].childNodes[0].nodeValue;
    }
        
    
        
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       tier & price
    */
    function CalcPriceTier(time_day, cycle_start, timestamp, conspt, consumptionToday, billingStartDate, locObj,ratesObj,basicObj,miniObj,summerObj,winterObj,baseObj) {
        consumptionToday = consumptionToday/1000;

        var season;
        var now = new Date();
        
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
        
        
        return ([percentage2Nexttier.toFixed(2),currenttier+1, currentRate, nextRate]);
        
    }
        
    
    /*---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    *       miscellaneous
    */
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
  
        <div id="mainFrame" class="withboarder" style="position:absolute;margin-top:120px;margin-left:50px">

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

            <div id="BarData1stBlock" style="float:left; margin:1  auto">
                <p id="titleBar1st" style = "text-align:center"></p>
                <table id="Bar1st">
                    <tr>
                        <td id="restBar1st" valign="bottom"><p></p></td>
                    </tr>
                    <tr>
                        <td id="usedBar1st" valign="top"><p></p></td>
                    </tr>
                </table>
                <p style="font-size:70%;font-family:Trebuchet MS;text-align:center">Today's</br>Budget</p>
            </div>

            <div id="BarData2ndBlock" style="float:right; margin:1  auto">
                <p id="titleBar2nd" style = "text-align:center"></p>
                <table id="Bar2nd">
                    <tr>
                        <td id="restBar2nd" valign="bottom"><p></p></td>
                    </tr>
                    <tr>
                        <td id="usedBar2nd"  valign="top"><p></p></td>
                    </tr>
                </table>
                <p style="font-size:70%;font-family:Trebuchet MS;text-align:center">This Month's</br>Budget</p>
            </div>
            
        </div>
        
        <div id="ComparisonPic" style="margin-top:60px; position:absolute; left: 300px"><img src="./img/Picture1.png" width="240"; height="240"/></div>

        <div id="EdisonLogo" style="position:absolute; margin:1 auto; padding-left: 140px"></div>

        <div id="CurveDataBlock" style="position:absolute; margin:1 auto">

        <div id="CurveDataTitle" style="margin-bottom:2%"><p id="CurveDataTitleText" style="font-size:80%; font-family:Trebuchet MS; text-align:center"></p></div>

		<div id="CurveDataLegend">
			<div id="RedLegend"><div class="colorLegend" style="background-color:#FF6600; float:left; margin-left:30%"></div><p id="redText" style="float:left; font-size: 70%; font-family:Trebuchet MS; margin-left:5px; margin-top:-10px"></p></div>
			<div id="BlueLegend"><div class="colorLegend" style="background-color:#ccddcc; float:left; margin-left:10%"></div><p id="blueText" style="float:left; font-size: 70%; font-family:Trebuchet MS; margin-left:5px; margin-top:-10px"></p></div>
		</div>

		<div id="CurveDataUsage" style="margin:1 auto">

			<div id = "divYaxis" style="float:left">
				<table id="yaxis">
                    <tr>
						<td id="val5" valign="top"></td>
					</tr>
					<tr>
						<td id="val4" valign="center"></td>
					</tr>
					<tr>
						<td id="val3" valign="center"></td>
					</tr>
					<tr>
						<td id="val2" valign="center"></td>
					</tr>
					<tr>
						<td id="val1" valign="center"></td>
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
						<td id="week0Empty" ></td>
					</tr>
					<tr>
						<td id="week0Rest" ></td>
					</tr>
					<tr>
						<td id="week0Used" ></td>
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
						<td id="val5Hourly" valign="top"></td>
					</tr>
                    <tr>
						<td id="val4Hourly" valign="center"></td>
					</tr>
					<tr>
						<td id="val3Hourly" valign="center"></td>
					</tr>
					<tr>
						<td id="val2Hourly" valign="center"></td>
					</tr>
					<tr>
						<td id="val1Hourly" valign="center"></td>
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
						<td class="axisColumn" id="aColumn0"><p class="axisText" >5</p></td>
						<td class="axisColumn" id="aColumn1"><p class="axisText" >4</p></td>
						<td class="axisColumn" id="aColumn2"><p class="axisText" >3</p></td>
						<td class="axisColumn" id="aColumn3"><p class="axisText" >2</p></td>
						<td class="axisColumn" id="aColumn4"><p class="axisText" >1</p></td>
						<td class="axisColumn" id="aColumn5"><p class="axisText" >0</p></td>
					</tr>
				</table>
			</div>
		</div>

		<div id="CurveDataXaxis" style="padding-top:2%; float:left;  margin-top:-5px"><p id="CurveDataXaxisText" style="font-size:80%; font-family:Trebuchet MS"></p></div>
	    </div>


        <div id="RightBlock" style="position:absolute; margin:1 auto">

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



	    <div  id="ContactAndWarning"style = "position:absolute; border-radius:25px"></div>
        </div>
         
    </body>
</html>
