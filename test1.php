<?php

//parameters
$dbServerName = "192.168.0.171:4045";
$dbUsername = "user_wallet";
$dbPassword = "wallet!22@";
$dbName = "apep_wallet_mobile";


//connection to the database
$conn = new mysqli($dbServerName, $dbUsername, $dbPassword, $dbName);

// check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";


    //for all csv file:
    $files = glob("data/*.csv");           

    foreach($files as $file) {

        $table = $file;
        $table = substr($table, 5);
        $table = substr($table, 0, -13);

        // get structure from csv 
        ini_set('auto_detect_line_endings',TRUE);
        $handle = fopen($file,'r');

        //check if there is an error in csv file
        if ( ($data = fgetcsv($handle, 4096, ",") ) === FALSE ) {
            echo "Cannot read from csv $file";die();
        }
        
        
        //a new variable for fields of column
        global $fields;
        $fields = array();  
        $field_count = 0;
        for($i=0;$i<count($data); $i++) {

            $f = strtolower(trim($data[$i]));

            if ($f) {
    
                // normalize the field name, strip to 20 chars if too long
    
                //$f = substr(preg_replace ('/[^0-9a-z]/', '_', $f), 0, 20);          //remove special caracters
                $f = preg_replace('/[0-9\@\.\;\" "]+/', ' ', $f);
                //$f= trim($f, '/!.');
    
                $field_count++;

                //precise type of field:
                    if (strpos($f, 'date') !== false) {

                        $fields[] =$f." DATETIME"; 

                    } elseif (strpos($f, 'code') !== false || strpos($f, 'nb') !== false) {

                        $fields[] = $f.' INT';
                    } else {

                        $fields[] = $f.' VARCHAR(50)';
                    }
    
            }
        }


        //create table with exception :
        $sql = "CREATE TABLE $table (" . implode(', ', $fields) . ')';

        echo "sql request for creating table </br> : ".$sql;
        
        //check the query
        if ($conn->query($sql) === TRUE) {
            echo "Table created successfully";
          } else {
            echo "Error creating table: " . $conn->error;
          }

    

          //read the data and run the insert query
        while ( ($data = fgetcsv($handle, 4096, ",") ) !== FALSE ) {

            $values = array();

            for($i=0;$i<$field_count; $i++) {

 
                //faire le traitement sur le type du tableau : date ou non : 
                //echo "Le tableau contient ".count($fields)."éléments";
                //echo "je suis l'element du nombre : ".$i." et ma valeur est  : ".$fields[$i];  //affiche le nom de kl'attribut + son type a cote


                //faire condition pour voir les dates : 
                if (strpos($fields[$i], 'DATETIME') !== false) {
                    //$fields[] =$f." DATETIME"; 

                    //echo "<br> datetime value :  ".$data[$i]." </br>";  //la bonne date comme csv

                    //change the format of datetime :

                        if(empty($data[$i])){
                            
                            //echo '$v1 est vide.'. '<br />';
                            $values[] = 'NULL';
                        }
                        else {

                            echo "date 0 : ".$data[$i];


                            $data[$i] = str_replace('/','-', $data[$i]);

                            echo "date 1 : ".$data[$i];

                            $timestamp = strtotime($data[$i]);

                            echo "date 2 : ".$timestamp;

    
                            $date = date("Y-m-d H:i:s", $timestamp);

                            echo "date 3 : ".$date;

    
                            $values[] = '\''.$date.'\''; 

                            echo "date 3 : ".'\''.$date.'\'';;


                        } 
 
                

                                 
                
                } else {

                    $values[] = '\''.addslashes($data[$i]).'\'';
                }


            }


            $sql = "Insert into $table values(" . implode(', ', $values) . ')';

            echo "request to insert data </br> : ".$sql;

            if ($conn->query($sql) === TRUE) {
                //echo "Data upload successfully";
            } else {
                echo "Error uploading data: " . $conn->error;
            }

   
        }
        fclose($handle);


        //auto_increment_column_name int not null auto_increment primary key,
        $sqlid = "ALTER TABLE $table ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY";


        if ($conn->query($sqlid) === TRUE) {
        } else {
            echo "Error updating table: " . $conn->error;
        }
        ini_set('auto_detect_line_endings',FALSE);

        

    }

    

?>