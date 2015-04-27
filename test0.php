<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="js/jquery.simpleWeather-2.3.min.js"></script>
    
    
    </head>
    
    <body>

        <!-- this UL will be populated with the data from the php array -->
        <ul></ul>
        
        <p id = "day"></p>
        <p id = "cycle"></p>
        <p id = "hour"></p>
        <p id = "power"></p>
        
        <p id = "hour_con_bud"></p>

        <script type='text/javascript'>
        $(document).ready(function(){
                /* call the php that has the php array which is json_encoded */
                $.getJSON('php/get.php', function(data) {
                        /* data will hold the php array as a javascript object */
                    
                    $("#day").html(data[0].sum + ' ' + data[0].budget);
                    
                    $("#cycle").html(data[1].sum + ' ' + data[1].budget);
                    
                    $("#hour").html(data[2][0].sum + ' ' + data[2][0].budget);
                    hour_con_bud = data[2];
                    $("#hour_con_bud").html(hour_con_bud[0].sum);
                    
                    $("#power").html(data[3].currentpower);
                    
                        /*$.each(data, function(key, val) {
                                $('ul').append('<li id="' + key + '">' + val.sum + ' ' + val.budget + '</li>');
                        });*/
                });

        });
        </script>

</body>
</html>