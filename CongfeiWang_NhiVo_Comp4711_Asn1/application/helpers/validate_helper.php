

<?php

    /*function validate_phone() will take in an input and use regular expression
    * to compare if the input format is right and return true
    * otherwise it will return false 
    */
    function validate_phone($input) {
        if (preg_match('/^\(?[0-9]{3}\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/', $input)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*function validate_email() will take in an input and use regular expression
    * to compare if the input format is right and return true
    * otherwise it will return false 
    */
    function validate_email($str) {
        if (preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    //function validate_name() will take in an input and compare if it's null return false  
    function validate_name($name) {
        $name = trim($name);
        return ($name == '') ? FALSE : TRUE;
    }

    ?>
