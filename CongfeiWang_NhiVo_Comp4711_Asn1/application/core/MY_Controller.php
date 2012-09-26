<?php

/**
 * core/MY_Controller.php
 *
 * Default application controller
 *
 * @author		JLP
 * @copyright           2010-2012, James L. Parry
 * ------------------------------------------------------------------------
 */
class Application extends CI_Controller {
    // to qualify the requirement for assignment1
    // new links are added into the menu bar
    protected $data = array();      // parameters for view components
    protected $id;                  // identifier for our content
    protected $choices = array(// our menu navbar
        'Welcome' => '/',
        'Roster' => 'roster',
        'League' => 'league',
        'Schedule' => 'schedule',
        'Update' => 'update',
        'Shop' => 'shop'
    );

    /**
     * Constructor.
     * Establish view parameters & load common helpers
     */
    function __construct() {
        parent::__construct();
        $this->data = array();
        $this->data['errors'] = array();
        $this->data['title'] = 'Welcome to Jacsonville Jaguars';
    }

    /**
     * Render this page
     */
    function render() {
        $this->data['menubar'] = build_menu_bar($this->choices);
        $this->data['content'] = $this->parser->parse($this->data['pagebody'],$this->data,true);
        $this->data['data'] = &$this->data;
        $this->parser->parse('_template', $this->data);
    }

}

/* End of file MY_Controller.php */
/* Location: application/core/MY_Controller.php */