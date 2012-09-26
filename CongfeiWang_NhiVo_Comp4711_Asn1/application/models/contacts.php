<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Contacts table.
 *
 * @author		JLP
 * ------------------------------------------------------------------------
 */

class Contacts extends _Mymodel {

    // Constructor
    function __construct() {
        parent::__construct();
        $this->setTable('contacts', 'ID');
    }

 }

/* End of file contacts.php */
/* Location: application/models/contacts.php */