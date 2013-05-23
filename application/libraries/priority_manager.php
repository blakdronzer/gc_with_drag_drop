<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Prioirty / Row order management
 *
 * An independant library that allows the user / developer to take advantage of rearranging rows 
 * in a specific table. The library can be used independantly with any application / code or can be 
 * used with other CRUD libraries like GROCERY CRUD.
 * Each table is assumed to have a priority field in the table. A rows priority can be either be across
 * the table or can be grouped up by a column. As for now, the code revolves around a single group 
 * field in the table.
 *
 * A Sample Implementation for the application as how to use it. (This one is using grocery crud)
 * 
 * 	function updatePosition($table, $sourceId, $distance, $direction) {
 *
 *		$this->load->library('Priority_manager');     //loads the library
 *		$manager = new Priority_manager();            
 *		$manager->setTable($table);					//sets the table
 *		$manager->setPriorityField('priority');		//sets the priority field
 *
 *OR in case the user want to manage a group based re-ordering
 *	function updateGroupPosition($table, $group, $sourceId, $distance, $direction) {
	
 *		$this->load->library('Priority_manager');     //loads the library
 *		$manager = new Priority_manager();            
 *		$manager->setTable($table);					//sets the table
 *		$manager->setPriorityField('priority');		//sets the priority field
 *		$manager->setGroupField($group);			//Sets the group field
 *
 *		//based on the direction / instruction / command.. makes the respective call 
 *
 *		switch ($direction) {
 *			case 'up' :
 *				$manager->moveUpBy($sourceId, $distance);
 *				break;
 *			case 'down' :
 *				$manager->moveDownBy($sourceId, $distance);
 *				break;
 *			case 'top' :
 *				$manager->moveToTop($sourceId);
 *				break;
 *			case 'bottom' :
 *				$manager->moveToBottom($sourceId);
 *				break;
 *			case 'default' :
 *				$manager->moveTo($sourceId, $distance);
 *				break;
 *		}
 *	}
 * Copyright (C) 2013  Amit Shah.
 *
 * You are free to use, modify and distribute this code, but all copyright information must remain.
 *
 * @copyright  	Copyright (c) 2013 Amit Shah
 * @version    	1
 * @author     	Amit Shah <amitbs@gmail.com>
 */

class Priority_manager {
	
	/**
	 * @var String table - name of the table on which the manager will perform all the actions. 
	 */
	protected $table;
	protected $priority_field;
	protected $group_field;
	protected $primary_key;
	/**
	 * __get
	 *
	 * Enables the use of CI super-global without having to define an extra variable.
	 *
	 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
	 *
	 * @access	public
	 * @param	$var
	 * @return	mixed
	 */
	public function __get($var)
	{
		return get_instance()->$var;
	}
	
	
	/**
	 * __construct
	 *
	 * initialized the class level variables with default values and loads required models 
	 * @return void
	 * @author Amit Shah
	 **/
	public function __construct()
	{
		$this->group_field = FALSE;
		$this->table = '';
		$this->priority_field = 'priority';
		$this->primary_key = 'id';
		
		$this->load->model('priority_model');
		$this->load->model('common_model');
	}
	
	/**
	 * @param string $_table - Name of the table that will be provided by the user to be set
	 * @return void
	 * @author Amit Shah
	 */
	public function setTable($_table) {
		$this->table = $_table;
		$this->primary_key = $this->common_model->getPrimaryKey($_table);
	}
	
	/**
	 * @param string $field - Name of the field that will act as a group
	 * @return void
	 * @author Amit Shah
	 */
	public function setGroupField($field) {
		$this->group_field = $field;
	}
	
	/**
	 * @param string $field - Name of the field that will be act as priority field
	 * function sets the priority field that will be refered for position setting of each row
	 * 
	 * @return void
	 * @author Amit Shah
	 */
	public function setPriorityField($field) {
		$this->priority_field = $field;
	}
	
	
	/**
	 * @param int $sourceId
	 * @param int $distance
	 * function moves the specified row up by the provided distance 
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function moveUpBy($sourceId, $distance=1) {
		$rows = $this->priority_model->getRowsBefore($this->table, $this->primary_key, $this->priority_field, $this->group_field, $sourceId, $distance);
		$ids = array();
		if(count($rows) > 0) {
			foreach($rows as $row)
				$ids[] = $row[$this->primary_key];
			$top_row = $rows[count($rows)-1];
			$this->priority_model->moveRowsDown($this->table, $this->primary_key, $this->priority_field, $this->group_field, $ids);
			$data = array(
					$this->priority_field=>$top_row['priority']
				);
			$this->common_model->update($this->table, $data, array($this->primary_key=>$sourceId));
		}
	}
	
	/**
	 * @param int $sourceId
	 * @param int $distance
	 * function moves the specified row down by the provided distance 
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function moveDownBy($sourceId, $distance=1) {
		$rows = $this->priority_model->getRowsAfter($this->table, $this->primary_key, $this->priority_field, $this->group_field, $sourceId, $distance);
		if(count($rows) > 0) {
			foreach($rows as $row)
				$ids[] = $row[$this->primary_key];
			$bottom_row = $rows[count($rows)-1];
			$this->priority_model->moveRowsUp($this->table, $this->primary_key, $this->priority_field, $this->group_field, $ids);
			$data = array(
					$this->priority_field=>$bottom_row['priority']
			);
			$this->common_model->update($this->table, $data, array($this->primary_key=>$sourceId));
		}
	}
	
	/**
	 * @param int $sourceId
	 * @param int $destined_position
	 * function moves the specified row to the destined position
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function moveTo($sourceId, $destined_position) {
		$source_row = $this->common_model->getByField($this->table, $this->primary_key, $this->primary_key, $source_id);
		$source_position = $source_row[$this->priority_field];
		if($destined_position > $source_position) {
			$this->moveDownBy($sourceId, $destined_position-$source_position);
		} else {
			$this->moveUpBy($sourceId, $source_position-$destined_position);
		}
	}
	
	/**
	 * @param int $sourceId
	 * function moves the specified row to the top in the table / group
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function moveToTop($sourceId) {
		$this->priority_model->stepDownFromTop($this->table, $this->primary_key, $this->priority_field, $this->group_field, $sourceId);
		$data = array($this->priority_field=>1);
		$this->common_model->update($this->table, $data, array($this->primary_key=>$sourceId));
	}
	
	/**
	 * @param int $sourceId
	 * function moves the specified row to the bottom in the table / group
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function moveToBottom($sourceId) {
		$this->priority_model->stepUpFromBottom($this->table, $this->primary_key, $this->priority_field, $this->group_field, $sourceId);
		if($this->group_field != FALSE) {
			$source_row = $this->common_model->getByField($table, $this->primary_key, $sourceId);
			$group_value = $source_row[$this->group_field];
		} else {
			$group_value = FALSE;
		}
		$maxCount = $this->priority_model->getMaxCount($this->table, $this->primary_key, $this->priority_field, $this->group_field, $group_value);
		$data = array(
					$this->priority_field=>($maxCount+1)
				);
		$this->common_model->update($this->table, $data, array($this->primary_key=>$sourceId));
	}
	
	/**
	 * @param bool group_value - if a group value is provided, the code will re-position rows only for 
	 * the given group (with value) 
	 * function re-positions all the rows in the table / group in the order they are retrieved
	 *
	 * @return void
	 * @author Amit Shah
	 */
	function rearrangePriorities($group_value=FALSE) {
		$this->priority_model->resetPriorities($this->table, $this->primary_key, $this->priority_field, $this->group_field, $group_value);
	}
}