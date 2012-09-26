<?php

/**
 * XML_Model
 * 
 * A CodeIgniter model suited to working with XML documents instead of an RDB.
 * 
 * @author		J.L. Parry, B.C.I.T.
 * @copyright           Copyright (c) 2010, J.L. Parry
 * @license		Free, as in beer
 * 
 * ----------------------------------------------------------------------------- 
 * This is intended for use with an XML document used to store the
 * state of a collection of like things. Each direct child element of
 * the root is treated as a "record", with its attributes used as
 * a lookup key.
 * 
 * SimpleXML is used for actual document manipulation, hence there
 * are no CRUD methods here, unlike a conventional Active Record.
 * 
 * ----------------------------------------------------------------------------- 
 * The CI model is not intended for manipulation of an XML document - that
 * is up to conventional SimpleXMLElement methods. 
 * The CI model makes it easy to manage an XML document in the CodeIgniter
 * M-V-C fashion, and also to search for elements by "key" in a way not
 * provided by SimpleXMLElement.
 * 
 * ----------------------------------------------------------------------------- 
 * This file should go inside the application/models folder, and
 * it should be added to the application/config/autoload.php, as
 *      $autoload['model'] = array('xml_model');
 * This will be near the bottom of the script.
 * 
 * You may find it convenient to add any other models to the autoload too.
 * 
 * ----------------------------------------------------------------------------- 
 * XML files are assumed to be in the webapp root (./), unless
 * you have an XML_data_folder configuration property.
 * This would be defined in application/config/config.php, eg.
 *      $config['XML_data_folder'] = '/data/timetable/';
 * This can go at the top of the config script.
 * 
 * The folder containing a specific XML document can be over-ridden when
 * the model for that document is setup.
 * 
 * Example: models/properties.php
 * 
 * class Properties extends Xml_model {
 *   function Properties() {
 *     parent::Xml_model();
 *     $this->setup('properties', 'properties.xml', 'MyXMLFolder', 'key');
 *   }
 * }
 * 
 * The above would be a CI model for MyXMLFolder/properties.xml:
 * <properties>
 *   <property key=”abc”>value</property>
 *   ...
 * </properties>
 * 
 * ----------------------------------------------------------------------------- 
 * Our model maintains an array of the first level elements in the XML
 * document, i.e. the direct children of the root element.
 * This lets us find stuff quickly, and to traverse the document with ordered elements, 
 * even if they weren't ordered to begin with. 
 * 
 * If child elements are added to the root, invoke the reindex() method
 * to rebuild the lookup array.
 * 
 * ----------------------------------------------------------------------------- 
 * A sample use, for an inventory.xml document containing product elements:
 * 
 * models/Inventory.php
 *   
 *   class Inventory extends Xml_model {
 *     function Inventory() {
 *       parent::XmlModel();
 *       $this->setup('inventory','MyInventory.xml',,'code');
 *     }
 *   }
 * 
 * controllers/something.php
 * 
 *   ...
 *   $inventory = $this->load->model('inventory');
 *   // $inventory is now a SimpleXMLElement object, set to the root of the 
 *   // corresponding XML document.
 *   // We could treat it just like a regular SimpleXMLElement, or ...
 *   $product = $inventory->get('123'); // get the element for product code 123
 *   $product->onhand--;    // record that we took one out
 *   // and, wait for it ...
 *   $inventory->store();   // rewrite the XML document
 * 
 * ----------------------------------------------------------------------------- 
 * @package		Timetabler
 * @author		JLP
 * @copyright           Copyright (c) 2010, JL Parry
 * @since		Version 1.0.0
 * ------------------------------------------------------------------------
 */
class Xml_model extends CI_Model {

    var $_document_name;         // Which document is this a model for?
    var $_root_name;             // name of the root element (tag)
    var $_key_attr;              // name of the child element attribute used as a 'primary key'
    var $_key2_attr;             // name of the second child element used with a 'composite key'
    var $_data;                 // array of ordered children
    var $_folder;               // folder this document is in
    var $_real_name;            // where did the document really come from
    var $root;                  // SimpleXMLElement to hold the document root, accessible

    /**
     * Constructor - initialize fields to defaults only
     * 
     * @access public
     */

    function __construct() {
        parent::__construct();
        // assume that the doctype is the same as the model name
        $this->_root_name = get_class($this);
        $this->_folder = './';
    }

    //---------------------------------------------------------------------------
    //  Document management functions
    //---------------------------------------------------------------------------
    /**
     * Specify the proper attributes for this model.
     * 
     * The document is parsed into a DOM if it exists, and an empty one
     * with the proper element name if the file doesn't exist.
     *
     * @access	public
     * @param	string	the document type, i.e. element name of the root element
     * @param	string	the file name, with suffix, of the XML document
     * @param	string  the folder this document is in, with trailing slash
     * @param   string  the name of the child attribute to use as a 'primary key'
     * @param   string  the name of the child attribute to use as the second
     *                  part of a compositite key. 
     *                  If specified, the "key" is constructed as "aaa-bbb", where
     *                  aaa is the value of the first key attribute, and
     *                  bbb is the value of the second key attribute.
     * @return	SimpleXMLElement    the root element of the document.
     */
    function setup($doctype, $filename, $key='id', $foldername=null, $key2=null) {
        // remember everything
        $this->_root_name = $doctype;
        $this->_document_name = $filename;
        $this->_key_attr = $key;
        $this->_key2_attr = $key2;

        // if there is a suitable config item, assume that is the folder
        // where the XML document will be found
        if ($this->config->item('XML_data_folder'))
            $this->_folder = $this->config->item('XML_data_folder');

        // over-ride folder name if supplied as parameter
        if (isset($foldername))
            $this->_folder = $foldername;

        // let's load the XML document, if we can
        $this->load();
        return $this->root;
    }

    /**
     * Load an XML document, allowing document name to be overridden.
     * 
     * The data in the current model is replaced with that for the XML
     * document previously configured. 
     * This effectively reverts to the original XML document.
     *
     * This method is called automatically by the "setup" method.
     *
     * @access	public
     * @param	string	the file name, with suffix, of the XML document, if
     *                  it differs from the preconfigured one
     * @return	SimpleXMLElement    the root element of the document.
     */
    function load($fileName='') {
        // use the stored folder & filename if not supplied as parameter
        if (strlen($fileName) < 1)
            $this->_real_name = $this->_folder . $this->_document_name;
        else
            $this->_real_name = $fileName;
        // is it there? if so, get it
        if (file_exists($this->_real_name))
            $this->root = simplexml_load_file($this->_real_name);
        // if not, make an empty document
        else
            $this->root = simplexml_load_string('<' . $this->_root_name . '/>');

        // rebuild the keys table
        $this->reindex();
    }

    /**
     * Store the XML document this is a model for.
     * 
     * One simple way to write out nicely indented XML data is by converting 
     * the SimpleXML root element to a DOM document, and using its formatOutput option.
     * @access	public
     */
    function store() {
        $doc = new DOMDocument('1.0');
        $doc->formatOutput = true;
        $domnode = dom_import_simplexml($this->root);
        $domnode = $doc->importNode($domnode, true);
        $domnode = $doc->appendChild($domnode);

        $doc->save($this->_real_name);
    }

    /**
     * Rebuild and resort the ordered data copy
     * 
     * @access	public
     */
    function reindex() {
        // reset them
        $this->_data = array();
        // get the DOM root's children
        foreach ($this->root->children() as $kid) {
            // extract the attribute value to use for indexing
            $key = (string) $kid[$this->_key_attr];
            // append (hyphen separated) second part of key if appropriate
            if (isset($this->_key2_attr))
                $key .= '-' . $kid[$this->_key2_attr];
            // store this in our kay table
            $this->_data[$key] = $kid;
        }
        // sort the table by key
        if (count($this->_data) > 0)
            ksort($this->_data);
        // reset the cursor
        reset($this->_data);
    }

    /**
     * Return the number of entries in our data array
     *
     * @access	public
     * @return  int     the number of entries in _data
     */
    function count() {
        return count($this->_data);
    }

    //---------------------------------------------------------------------------
    //  DOM traversal or randomaccess
    //---------------------------------------------------------------------------
    /**
     * Find and return a specific element in the XML document.
     *
     * Construct a key from the supplied parameters, and look
     * for the root's child element with those attributes.
     * 
     * @access	public
     * @param   string  the key to look for
     * @param   string  the second part of the key to look for, if needed
     */
    function get($which, $which2=null) {
        // build the search key
        $key = $which;
        // append (hyphen separated) second part of key if appropriate
        if (isset($which2))
            $key .= '-' . $which2;
        // find it if we can
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        } else {
            return null;
        }
    }

    /**
     * Return the first ordered element.
     *
     * This uses the data copy ordered by the specified key,
     * and does not use SimpleXML.
     *
     * @access	public
     * @return  SimpleXMLElement    the desired "record"
     */
    function first() {
        return reset($this->_data);
    }

    /**
     * Return the last ordered element.
     *
     * This uses the data copy ordered by the specified key,
     * and does not use SimpleXML.
     *
     * @access	public
     * @return  SimpleXMLElement    the desired "record"
     */
    function last() {
        return last($this->_data);
    }

    /**
     * Return the next ordered element.
     *
     * This uses the data copy ordered by the specified key,
     * and does not use SimpleXML.
     *
     * @access	public
     * @return  SimpleXMLElement    the desired "record"
     */
    function next() {
        return next($this->_data);
    }

    /**
     * Return the previous ordered element.
     *
     * This uses the data copy ordered by the specified key,
     * and does not use SimpleXML.
     *
     * @access	public
     * @return  SimpleXMLElement    the desired "record"
     */
    function previous() {
        return prev($this->_data);
    }

}

?>
