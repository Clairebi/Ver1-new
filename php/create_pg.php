<?php
include_once("db_con_pg.php");
/*
$tbl_users = "CREATE TABLE IF NOT EXISTS users (
              id                 SERIAL PRIMARY KEY NOT NULL,
			  username           VARCHAR(16) NOT NULL,
			  email              VARCHAR(255) NOT NULL,
			  password           VARCHAR(255) NOT NULL,
			  address1           VARCHAR(255) NULL,
			  address2           VARCHAR(255) NULL,
              city               VARCHAR(255) NULL,
              state              VARCHAR(255) NULL,
			  country            VARCHAR(255) NULL,
			  
			 
			  ip                 VARCHAR(255) NOT NULL,
			  signup             timestamp NOT NULL,
			  lastlogin          timestamp NOT NULL,
			  notescheck         timestamp NOT NULL,
			  UNIQUE (username,email)
             )";
$result0 = pg_query($tbl_users);
*/

// create tables
$umeter = $u."meter";

$tbl_raw = "CREATE TABLE IF NOT EXISTS $umeter (
            timestamp       timestamp,
            id              integer PRIMARY KEY NOT NULL,
            consumption     double precision,
            delta_conspt    double precision,
            currentpower    double precision
            );";
$result1 = mysqli_query($tbl_raw);


$u_hour = $u."_hour";

$tbl_hour = "CREATE TABLE IF NOT EXISTS $u_hour(
  time_hour timestamp without time zone,
  hourly_sum double precision,
  hour_budget double precision)";
$result7 = mysqli_query($tbl_hour);

$u_daily = $u."_daily";

$tbl_day = "CREATE TABLE IF NOT EXISTS $u_daily(
  time_day timestamp without time zone,
  daily_sum double precision,
  daily_budget double precision,
  daily_cost double precision)";
$result8 = mysqli_query($tbl_day);

$u_week = $u."_week";

$tbl_week = "CREATE TABLE IF NOT EXISTS $u_week(
  week_start timestamp without time zone,
  weekday_sum double precision,
  weekend_sum double precision
)";
$result9 = mysqli_query($tbl_week);

/*
$u_cycle = $u."_cycle";
    
$tbl_cycle = "CREATE TABLE IF NOT EXISTS $u_cycle(
  cycle_start timestamp without time zone,
  cycle_sum double precision,
  cycle_budget double precision,
  cycle_cost double precision)";
$result10 = pg_query($tbl_cycle); */



// create triggers 
/* $tg_raw = "CREATE TRIGGER delta_tg_$u
            BEFORE INSERT OR UPDATE
            ON $umeter
            FOR EACH ROW
            EXECUTE PROCEDURE gen_delta()"; 
$result2 = pg_query($tg_raw); */


/*$tg_hour = "CREATE TRIGGER hour_tg_$u
  BEFORE INSERT OR UPDATE
  ON $umeter
  FOR EACH ROW
  EXECUTE PROCEDURE insert_hour()";*/
$tg_hour = "CREATE TRIGGER $umeter_AFTER_INSERT 
            AFTER INSERT 
            ON $umeter 
            FOR EACH ROW  
            begin
              call update_hour(new._timestamp, new.id, new.consumption, new.delta_conspt, new.currentpower, new.UserID);
            end "
$result3 = mysqli_query($tg_hour);

/*$tg_day = "CREATE TRIGGER day_tg_$u
  AFTER INSERT OR UPDATE
  ON $umeter
  FOR EACH ROW
  EXECUTE PROCEDURE daily_sum();"; */
$tg_day = "CREATE TRIGGER calplug_hour_AFTER_INSERT 
              AFTER INSERT 
              ON  $u_hour
              /* "calplug_hour" is the table name of hourly data, which need to update */
              FOR EACH ROW
              begin
                call update_day(new.time_hour,new.hourly_sum, new.UserID);
              end "
$result4 = mysqli_query($tg_day);
    
/* $tg_week ="CREATE TRIGGER week_tg_$u
  BEFORE INSERT OR UPDATE
  ON $umeter
  FOR EACH ROW
  EXECUTE PROCEDURE weekly_sum();";*/
$tg_week = "CREATE TRIGGER calplug_daily_AFTER_INSERT 
            AFTER INSERT 
            ON $u_daily
            FOR EACH ROW
            begin 
              call update_week(new.time_day, new.daily_sum, new.UserID);
            end "    
$result5 = mysqli_query($tg_week);    

/*$tg_cycle = "CREATE TRIGGER cycle_tg_$u
  AFTER INSERT OR UPDATE
  ON $umeter
  FOR EACH ROW
  EXECUTE PROCEDURE cycle_sum();";
$result6 = pg_query($tg_cycle); */


// only after insert and/or after update? 

?>