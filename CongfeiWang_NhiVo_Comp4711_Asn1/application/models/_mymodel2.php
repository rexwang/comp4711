<?php
/**
 * Generic domain model, with composite (2-part) key
 *
 * This builds on Mymodel (above).
 * @author		JLP
 * ------------------------------------------------------------------------
 */
class _Mymodel2 extends _Mymodel {

    var $_keyField2;                 // second part of composite primary key

    // Constructor

    function __construct() {
        parent::__construct();
        $this->_tableName = get_class($this);
    }

//---------------------------------------------------------------------------
//  Table management functions
//---------------------------------------------------------------------------
    // Load contents from & associate this object with a table
    function setTable($table, $key1='ID', $key2='id') {
        // prime our state
        $this->_tableName = $table;
        $this->_keyField = $key1;
        $this->_keyField2 = $key2;
    }

//---------------------------------------------------------------------------
//  Record-oriented functions
//---------------------------------------------------------------------------
    // Retrieve an existing DB record as an object
    function get($key1, $key2) {
        $this->db->where($this->_keyField, $key1);
        $this->db->where($this->_keyField2, $key2);
        $query = $this->db->get($this->_tableName);
        if ($query->num_rows() < 1)
            return null;
        return $query->row();
    }

    // Update a record in the DB
    function update($record) {
        // convert object to associative array, if needed
        if (is_object($record)) {
            $data = get_object_vars($record);
        } else {
            $data = $record;
        }
        // update the DB table appropriately
        $key = $data[$this->_keyField];
        $key2 = $data[$this->_keyField2];
        $this->db->where($this->_keyField, $key);
        $this->db->where($this->_keyField2, $key2);
        $object = $this->db->update($this->_tableName, $data);
    }

    // Delete a record from the DB
    function delete($key1, $key2) {
        $this->db->where($this->_keyField, $key1);
        $this->db->where($this->_keyField2, $key2);
        $object = $this->db->delete($this->_tableName);
    }

    // Determine if a key exists
    function exists($key1, $key2) {
        $this->db->where($this->_keyField, $key1);
        $this->db->where($this->_keyField2, $key2);
        $query = $this->db->get($this->_tableName);
        if ($query->num_rows() < 1)
            return false;
        return true;
    }

//---------------------------------------------------------------------------
//  Composite functions
//---------------------------------------------------------------------------
    // Return all records associated with the first part of the composite key
    function getSome($key) {
        $this->db->where($this->_keyField, $key);
        $this->db->order_by($this->_keyField, 'asc');
        $this->db->order_by($this->_keyField2, 'asc');
        $query = $this->db->get($this->_tableName);
        return $query->result();
    }

    // Count the # of records associated with the first part of a composite key
    function countSome($key) {
        $this->db->where($this->_keyField, $key);
        $this->db->order_by($this->_keyField, 'asc');
        $this->db->order_by($this->_keyField2, 'asc');
        $query = $this->db->get($this->_tableName);
        return $query->num_rows();
    }

    // Delete all records associated with the first part of a composite key
    function deleteSome($key) {
        $this->db->where($this->_keyField, $key);
        $object = $this->db->delete($this->_tableName);
    }

//---------------------------------------------------------------------------
//  Aggregate functions
//---------------------------------------------------------------------------
    // Return all records as an array of objects
    function getAll() {
        $this->db->order_by($this->_keyField, 'asc');
        $this->db->order_by($this->_keyField2, 'asc');
        $query = $this->db->get($this->_tableName);
        return $query->result();
    }

}