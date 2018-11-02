<?php

$host = "##############";
$username="##############";
$password="##############";
$database="##############";


// Opens a connection to a MySQL server
$connection=mysql_connect ($host, $username, $password);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}

if(isset($_POST['radius'])){


        $earthRadius = $_POST['radius'] * 0.000621371; // In miles by metter
        $latitude = $_POST['lat'];
        $longitude = $_POST['lng'];
        $query = "SELECT zipcode, 
            ( 3959 * 
                ACOS( 
                    COS( RADIANS( latitude ) ) * 
                    COS( RADIANS($latitude) ) * 
                    COS( RADIANS( $longitude ) - 
                    RADIANS( longitude ) ) + 
                    SIN( RADIANS( latitude ) ) * 
                    SIN( RADIANS( $latitude) ) 
                ) 
            ) 
            AS distance FROM zipcode HAVING distance <= $earthRadius ORDER BY distance ASC";

}else if(isset($_POST['lat1'])){

        $lat1 = (isset($_POST['lat1'])) ? $_POST['lat1'] : '';
        $lat2 = (isset($_POST['lat2'])) ? $_POST['lat2'] : '';
        $lng1 = (isset($_POST['lng1'])) ? $_POST['lng1'] : '';
        $lng2 = (isset($_POST['lng2'])) ? $_POST['lng2'] : ''; 

        $fst = $lat1;
        $snd = $lat2;
        if($lat1 > $lat2){
            $fst = $lat2;
            $snd = $lat1;
        }


        // Select all the rows in the markers table
        $query = "SELECT * FROM zipcode WHERE (latitude  BETWEEN '$fst' AND '$snd' ) AND (longitude  BETWEEN '$lng1' AND '$lng2' )";
       

}else{
    $points  = $_POST['poly_area'];
    $str_point = '';
    foreach($points as $key=>$val){
        $explode = explode(',',$val);
        if($key==(count($points)-1)){
            $str_point .= $explode[1] .' '.$explode[0];
        }else{
            $str_point .= $explode[1] .' '.$explode[0] .', ';
        }
    }

    $query = "SELECT * FROM zipcode WHERE MBRContains( GeomFromText( 'LINESTRING($str_point )' ), zipcode.points) ";



}


$result = mysql_query($query);
if (!$result) {
die('Invalid query: ' . mysql_error());
}


// Iterate through the rows, adding XML nodes for each
$zipcodes = array();
while ($row = @mysql_fetch_assoc($result)){
    $zipcodes[] = $row['zipcode'];
}    

echo json_encode($zipcodes);
exit;



?>
