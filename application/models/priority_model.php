<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!class_exists('CI_Model')) {
	class CI_Model extends Model {}
}

/**
 * Prioirty / Row order model
 *
 * This is an model built on codeigniter but it surely can be modified / altered accoringly to make
 * run independant of codeigniter
 *
 * Copyright (C) 2013  Amit Shah.
 *
 * You are free to use, modify and distribute this code, but all copyright information must remain.
 *
 * @copyright  	Copyright (c) 2013 Amit Shah
 * @version    	1
 * @author     	Amit Shah <amitbs@gmail.com>
 */


class Priority_model extends CI_Model
{

	/**
	 * __construct
	 *
	 * @return void
	 * @author Amit Shah
	 **/
	public function __construct()
	{
		parent::__construct();
		$this->load->database('default');
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management 
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int $sourceId - ID or the row from which the operation will run
	 * @param int $distance - Distance / number of rows it needs to retrieve from before
	 * @return array of the rows of the given table
	 *
	 * @return void
	 * @author Amit Shah
	 * 
	 */
	public function getRowsBefore($table, $primary_key='id', $priority_field, $group_field=FALSE, $sourceId, $distance) {
		if($group_field !== FALSE) {
			$from_row = $this->common_model->getByField($table, $primary_key, $sourceId);
			$group_val = $from_row[$group_field];
			$sql = "select * from $table where $priority_field < (select $priority_field from $table where $primary_key='$sourceId') and $group_field = $group_val ORDER BY priority DESC limit $distance";
		} else {
			$sql = "select * from $table where $priority_field < (select $priority_field from $table where $primary_key='$sourceId') ORDER BY priority DESC limit $distance";
		}
		//echo "Executing $sql<Br>\n";
		$query = $this->db->query($sql);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management 
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int $sourceId - ID or the row from which the operation will run
	 * @param int $distance - Distance / number of rows it needs to retrieve from after
	 * @return array of the rows of the given table
	 *
	 * @return void
	 * @author Amit Shah
	 * 
	 */
	public function getRowsAfter($table, $primary_key='id', $priority_field, $group_field=FALSE, $sourceId, $distance) {
		if($group_field !== FALSE) {
			$from_row = $this->common_model->getByField($table, $primary_key, $sourceId);
			$group_val = $from_row[$group_field];
			$sql = "select * from $table where $priority_field > (select $priority_field from $table where $primary_key='$sourceId') and $group_field = $group_val order by $priority_field ASC limit $distance";
		} else {
			$sql = "select * from $table where $priority_field > (select $priority_field from $table where $primary_key='$sourceId') order by $priority_field ASC limit $distance";
		}
		//echo "Executing $sql<Br>\n";
		$query = $this->db->query($sql);
		$result = $query->result_array();
		return $result;
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management 
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int[] $ids - Collection of IDs where the priorities need to be reset
	 * @return void
	 *
	 * Increments the priority field of all the rows in the given $ids by 1 so the source row can 
	 * be shifted above them.
	 * 
	 * @return void
	 * @author Amit Shah
	 * 
	 */
	public function moveRowsDown($table, $primary_key='id', $priority_field, $group_field=FALSE, $ids) {

		if($group_field !== FALSE) {
			$from_row = $this->common_model->getByField($table, $primary_key, $ids[0]);
			$group_val = $from_row[$group_field];
			$this->db->where($group_field, $group_val);
		}
		$this->db->set($priority_field, "$priority_field + 1", FALSE);
		$this->db->where_in($primary_key, $ids);
		$this->db->update($table);
		//echo $this->db->last_query();
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int[] $ids - Collection of IDs where the priorities need to be reset
	 * @return void
	 *
	 * Decrements the priority field of all the rows in the given $ids by 1 so the source row can
	 * be shifted above them.
	 *
	 * @return void
	 * @author Amit Shah
	 *
	 */	
	public function moveRowsUp($table, $primary_key='id', $priority_field, $group_field=FALSE, $ids) {
		if($group_field !== FALSE) {
			$from_row = $this->common_model->getByField($table, $primary_key, $ids[0]);
			$group_val = $from_row[$group_field];
			$this->db->where($group_field, $group_val);
		}
		$this->db->set($priority_field, "$priority_field - 1", FALSE);
		$this->db->where_in($primary_key, $ids);
		$this->db->update($table);
		//echo $this->db->last_query();
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int $source_id - The row till which all the rows above it in priority needs to be shifted 
	 * 							down by 1 step
	 * @return void
	 *
	 * Increments the priority field of all the rows by 1 from top till the $source_id in priority 
	 * so the $source_id row can be shifted to the top.
	 *
	 * @return void
	 * @author Amit Shah
	 *
	 */	
	public function stepDownFromTop($table, $primary_key='id', $priority_field, $group_field=FALSE, $source_id) {
		$source_row = $this->common_model->getByField($table, $primary_key, $source_id);
		$source_priority = $source_row[$priority_field];
		if($group_field !== FALSE) {
			$group_val = $source_row[$group_field];
			$this->db->where($group_field, $group_val);
		}

		$this->db->set($priority_field, "$priority_field + 1", FALSE);
		$this->db->where($priority_field . "<", $source_row[$priority_field], FALSE);
		$this->db->update($table);
		//echo $this->db->last_query();
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int $source_id - The row from which all the rows below it in priority needs to be shifted 
	 * 							up by 1 step
	 * @return void
	 *
	 * Decrements the priority field of all the rows by 1 from top till the $source_id in priority 
	 * so the $source_id row can be shifted to the bottom.
	 *
	 * @return void
	 * @author Amit Shah
	 *
	 */	
	public function stepUpFromBottom($table, $primary_key, $priority_field, $group_field=FALSE, $source_id) {
		$source_row = $this->common_model->getByField($table, $primary_key, $source_id);
		$source_priority = $source_row[$priority_field];
		if($group_field !== FALSE) {
			$group_val = $source_row[$group_field];
			$this->db->where($group_field, $group_val);
		}

		$this->db->set($priority_field, "$priority_field - 1", FALSE);
		$this->db->where($priority_field . ">", $source_row[$priority_field], FALSE);
		$this->db->update($table);
		//echo $this->db->last_query();
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param int $group_value - Reference point for group value in case there is a group field set / provided  
	 *  
	 * @return int - max priorirt
	 *
	 * @return void
	 * @author Amit Shah
	 *
	 */	
	public function getMaxCount($table, $primary_key='id', $priority_field, $group_field=FALSE, $group_value=FALSE) {
		if($group_field !== FALSE && $group_value !== FALSE) {
			$this->db->where($group_field, $group_value);
		}
		$this->db->select_max($priority_field);
		$query = $this->db->get($table);
		$result = $query->result_array();
		return $result[0][$priority_field];
	}

	/**
	 * @param string $table - Name of the table on which the operation will run
	 * @param string $priority_field - Name of the priority field on which the values will be set
	 * @param string $group_field - Name of the field that represents the group for order management
	 * 								of rows. If none, it is highly recomended to be set to false.
	 * @param String $group_value - The value if provided will be used for repositioning the rows 
	 * only for the group with the given value
	 *
	 * @return void 
	 *
	 * The function re-sets the position of all the rows it retrieves and in the order it received it.
	 * @return void
	 * @author Amit Shah
	 *
	 */	
	public function resetPriorities($table, $primary_key='id', $priority_field, $group_field=FALSE, $group_value=FALSE) {
		if($group_value !== FALSE && $group_field !== FALSE) {
			$this->db->where($group_field,$group_value);
			$this->db->order_by($priority_field, 'asc');
			$query = $this->db->get($table);
			$result = $query->result_array();
			$n=1;
			foreach($result as $row) {
				$row[priority_field] = $n;
				$this->common_model->update($table, $row, array($primary_key=>$row[$primary_key]));
				$n++;
			}
		} else if ($group_field !== FALSE) {
			$this->db->distinct($group_field);
			$query = $this->db->get($table);
			$result = $query->result_array();
			foreach($result as $row) {
				$this->db->where($group_field,$row[$group_field]);
				$this->db->order_by($priority_field, 'asc');
				$query = $this->db->get($table);
				$result2 = $query->result_array();
				$n=1;
				foreach($result2 as $row2) {
					$row2[priority_field] = $n;
					$this->common_model->update($table, $row2, array($primary_key=>$row2[$primary_key]));
					$n++;
				}
			}
		} else {
			$this->db->order_by($priority_field, 'asc');
			$query = $this->db->get($table);
			$result = $query->result_array();
			$n=1;
			foreach($result as $row) {
				$row[$priority_field] = $n;
				$this->common_model->update($table, $row, array($primary_key=>$row[$primary_key]));
				$n++;
			}
		}
	}
	
}
?>