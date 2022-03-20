

<?php

//$conn=mysqli_connect("localhost","root","","test");


//verifier la connection

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "test";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

//parcurir au niveau du dossier x et chercher le nom de la table comme variable 

$file = 'filename.csv';
$table = 'test';
$indexdate = array();

// get structure from csv and insert db
ini_set('auto_detect_line_endings',TRUE);
$handle = fopen($file,'r');
// first row, structure
if ( ($data = fgetcsv($handle) ) === FALSE ) {
    echo "Cannot read from csv $file";die();
}
$fields = array();
$field_count = 0;
for($i=0;$i<count($data); $i++) {
    $f = strtolower(trim($data[$i]));
    if ($f) {
        // normalize the field name, strip to 20 chars if too long
        $f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 20);

        //si le f contient varchar date ajouter l'index au niveau di tableau

        if (strpos($f, 'date') !== false) {

            //$fields[] =$f." DATETIME"; 
            echo "contient date ! ";
            array_push($indexdate, $field_count);

        } 

        $field_count++;
        $fields[] = $f.' VARCHAR(50)';
    }
}


print_r ($indexdate);

//$sql = "CREATE TABLE $table (" . implode(', ', $fields) . ')';
//echo $sql . "<br /><br />";
// $db->query($sql);
while ( ($data = fgetcsv($handle) ) !== FALSE ) {
    $fields = array();
    for($i=0;$i<$field_count; $i++) {

        if(in_array($i,$indexdate)){
            echo 'trouvé';

            //convertion:

            $data[$i] = str_replace('/','-', $data[$i]);
            $timestamp = strtotime($data[$i]);
            $date = date("Y-m-d H:i:s", $timestamp);
            $fields[] = '\''.$date.'\''; 

        }else{
            $fields[] = '\''.addslashes($data[$i]).'\'';
        }
        

    }
    $sql = "Insert into $table values(" . implode(', ', $fields) . ')';
    echo $sql; 
    // $db->query($sql);
    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
      } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }
      
      
}
fclose($handle);
ini_set('auto_detect_line_endings',FALSE);
mysqli_close($conn);

/** for index of date datetime :
 *  - create table which contains index of the fields where the varchar contains date ---> array of index indexdate =[1,4]
 * 
 *  - where field count is in indexdate  ---. conversion
 */

?>



