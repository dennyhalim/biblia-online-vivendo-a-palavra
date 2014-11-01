<?php

/*
Script Name: *Bovp Bible Insert Data Class
Script URI: http://www.vivendoapalavra.org/
Description: PHP class that insert the bible text.
Script Version: 0.1
Author: Andre Brum Sampaio
Author URI: http://www.vivendoapalavra.org
*/

class bovpDataInsert {

#default params
var $id = 1;
var $group = 1; 
var $delimiter = ';';
var $field_delimiter = '"';
var $db = '';

    #construct
    function bovpInsertData(){}

    #file name
    function setFileName($file_name) {

        if (isset($file_name) && !empty($file_name)) {$this->file_name = $file_name;} else {$this->file_name = false;}

    }

    #line delimiter
    function setDelimiter($delimiter) {

        if (isset($delimiter) && !empty($delimiter)) {$this->delimiter = $delimiter;} 

    }

    #field delimiter
    function setFieldDelimiter($field_delimiter) {

        if (isset($field_delimiter) && !empty($field_delimiter)) {$this->field_delimiter = $field_delimiter;} 

    }  

    #set table to insert
    function setTable($table) {

        if (isset($table) && !empty($table)) {$this->table = $table;} 

    }         


    function insertFile() {

        global $wpdb;

        $records = '';

        if($this->file_name) {

            // open file for ready only
            $text_bible = fopen($this->file_name.'.csv', 'r');

            if ($text_bible) { 

                // read the header
                $header = fgetcsv($text_bible, 0, $this->delimiter, $this->field_delimiter);

                // while not EOF
                while (!feof($text_bible)) { 

                    // read line
                    $currency_line = fgetcsv($text_bible, 0, $this->delimiter, $this->field_delimiter);

                    if($currency_line) {

                        // indexed record
                        $field = array_combine($header, $currency_line);


                        // Sql prepare
                        $records .=  '('. $this->id . ',' . $field['book'] . ',' . $field['cp'] . ',' . $field['vs'] . ",'" . addslashes($field['text']) . "')";

                    }

                    if((($this->id+1)/(400*$this->group))==1) {

                        $insert = 'INSERT INTO `' . $this->table . '` VALUES ' . $records.';';
                        $inserted = $wpdb->query($insert);
                        $this->group++;
                        $records = "";

                    } else {

                        if($currency_line) { $records .=  ",";} else {

                            $insert = 'INSERT INTO `' . $this->table . '` VALUES ' . substr($records,0,-1).';';
                            $inserted = $wpdb->query($insert);

                        }

                    }
                    

                    $this->id++;
                }

                fclose($text_bible);
            }

        }



    }


}