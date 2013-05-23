<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('CI_Model')) { class CI_Model extends Model {} }

class Common_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database('default');
	}

	public function getByField($table, $fieldName, $value) {
		$this->db->where($fieldName, $value);
		$query = $this->db->get($table);
		$result = $query->result_array();
		if(sizeof($result) > 0) {
			return $result[0];
		} else {
			return array();
		}
			
	}
	
	public function getAll($table, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
	}
	
	public function getAllFor($table, $fieldName, $value, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$this->db->where($fieldName, $value);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
	}

	public function getAllOrderBy($table, $orderBy, $order, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by($orderBy, $order);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
	}
	
	public function getAllForOrderBy($table, $fieldName, $value, $orderBy, $order, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$this->db->where($fieldName, $value);
		$this->db->order_by($orderBy, $order);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
	}
	
	public function searchFor($table, $criteria, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$this->db->where($criteria);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
		
	}

	public function searchForOrderBy($table, $criteria, $orderBy, $order, $limit=false, $offset=0) {
		if($limit != false) {
			$this->db->limit($limit, $offset);
		}
		$this->db->where($fieldName, $value);
		$this->db->order_by($orderBy, $order);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result;
	}
	
	public function insert($table, $data) {
		$this->db->insert($table, $data);
		return $this->db->insert_id();
	}

	public function update($table, $data, $condition) {
		$this->db->update($table, $data, $condition);
	}
	
	public function last_query() {
		return $this->db->last_query();
	}
	
	public function getPrimaryKey($table) {
		$fields = $this->db->field_data($table);
		foreach ($fields as $field)
		{
		   if($field->primary_key == 1) {
				return $field->name;
		   }
		}
	}
}
?>