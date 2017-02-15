gc_with_drag_drop
================

This is an example application which features the power of Drag and Drop library implemented with Grocery Crud

The files you need to look out for pulling up into your project

1 - application/libraries/priority_manager.php

2 - application/models/priority_model.php

3 - application/models/common_model.php

4 - assets/images/navigate-bottom-icon.png

5 - assets/images/navigate-down-icon.png

6 - assets/images/navigate-top-icon.png

7 - assets/images/navigate-up-icon.png


Changes that you need to incorporate in your code

1 - assets/grocery_crud/themes/flexigrid/js

				this_form.ajaxSubmit({
					 success:    function(data){
					 .....
						if (makeTableSortable && typeof(makeTableSortable) == "function")
							makeTableSortable();
					 }
				}); 

2 - assets/grocery_crud/themes/flexigrid/views/list_template.php

	add the following lines below 
	$this->set_js($this->default_javascript_path.'/'.grocery_CRUD::JQUERY);
	.......
	$this->set_css($this->default_css_path.'/ui/simple/'.grocery_CRUD::JQUERY_UI_CSS);
	$this->set_js($this->default_javascript_path.'/jquery_plugins/ui/'.grocery_CRUD::JQUERY_UI_JS);

3 - assets/grocery_crud/themes/flexigrid/views/list.php

	replace  ----- <tr  <?php if($num_row % 2 == 1){?>class="erow"<?php }?>>
	.......
	with   ----- <tr  <?php if($num_row % 2 == 1){?>class="erow"<?php }?> data_id="<?php echo $row->$primary_key ?>">


Post this, in the controllers, add the following methods

	/*	This function is to be called to update the table where it is independant ... without a group / foriegn key of data. 
	*	This will update the data totally on the table (as implemented in the shared example)
	*/
	
	function updatePosition($table, $sourceId, $distance, $direction) {
		$this->load->library('Priority_manager');
		$manager = new Priority_manager();
		$manager->setTable($table);
		$manager->setPriorityField('priority');

		switch ($direction) {
			case 'up' :
				$manager->moveUpBy($sourceId, $distance);
				break;
			case 'down' :
				$manager->moveDownBy($sourceId, $distance);
				break;
			case 'top' :
				$manager->moveToTop($sourceId);
				break;
			case 'bottom' :
				$manager->moveToBottom($sourceId);
				break;
			case 'default' :
				$manager->moveTo($sourceId, $distance);
				break;
		}
	}
	
	/*	This function is to be called to update the table where it is having a group / foriegn key of data. 
	*	This will be a good example for tables like products along with categories. So category_id becomes foreign key.
	*	All / any new record that gets created will be prioritized along with the category id
	*/	
	function updateGroupPosition($table, $group, $sourceId, $distance, $direction) {
	
		$this->load->library('Priority_manager');
		$manager = new Priority_manager();
		$manager->setTable($table);
		$manager->setGroupField($group);
		$manager->setPriorityField('priority');
	
		switch ($direction) {
			case 'up' :
				$manager->moveUpBy($sourceId, $distance);
				break;
			case 'down' :
				$manager->moveDownBy($sourceId, $distance);
				break;
			case 'top' :
				$manager->moveToTop($sourceId);
				break;
			case 'bottom' :
				$manager->moveToBottom($sourceId);
				break;
			case 'default' :
				$manager->moveTo($sourceId, $distance);
				break;
		}
	}

	/*	This function generates dynamic js for drag drop ajax calls */
    function dragdrop_js() {
    	$js = '
    		var startPosition;
    		var endPosition;
    		var itemBeingDragged;
    		var allIds = new Array();
    			
    			
			function makeAjaxCall(_url) {
			  /* Send the data using post and put the results in a div */
			    $.ajax({
			      url: _url,
			      type: "get",
			      success: function(){
			           $(".pReload").click();
    				   makeTableSortable();
			      },
			      error:function(){
			          alert("There was a failure while repositioning the element");
			      }   
			    });
    		}
    			
			function moveUp(sourceId) {
    			url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/up";
    			makeAjaxCall(url);
    		}
    		
			function moveDown(sourceId) {
    			url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/down";
    			makeAjaxCall(url);
    		}
    					
			function moveToTop(sourceId) {
    			url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/top";
    			makeAjaxCall(url);
    		}

    		function moveToBottom(sourceId) {
    			url="' . $this->session->userdata('callableAction') . '/" + sourceId +"/1/bottom";
    			makeAjaxCall(url);
    		}
    			
    		// Return a helper with preserved width of cells
	    	var fixHelper = function(e, ui) {
	    		ui.children().each(function() {
	    			$(this).width($(this).width());
	    		});
	    		return ui;
	    	};
	    	
    		function makeTableSortable() {
				$("#flex1 tbody").sortable(
	    		{
	    			helper: fixHelper,
	    			cursor : "move",
	    			create: function(event, ui) {
	    				allRows = $( "#flex1 tbody" ).sortable({ items: "> tr" }).children();
	    				for(var i=0; i< allRows.length; i++) {
	    					var _row = allRows[i];
	    					_id = _row.attributes["data_id"].value;
	    					//_id = _id.substr(4);
	    					allIds.push(_id);
	    					//console.log("Pushed - " + _id);
	    				}
	    			},
	    			start : function(event, ui) {
	    				startPosition = ui.item.prevAll().length + 1;
	    				itemBeingDragged = ui.item.attr("data_id");
	    			},
	    			update : function(event, ui) {
	    				endPosition = ui.item.prevAll().length + 1;
	    				if(startPosition != endPosition) {
	    					if(startPosition > endPosition) {
    							distance = startPosition - endPosition;
    							url="' . $this->session->userdata('callableAction') . '/" + itemBeingDragged +"/" + distance + "/up";
    							makeAjaxCall(url);
    						} else {
    							distance = endPosition - startPosition;
    							url="' . $this->session->userdata('callableAction') . '/" + itemBeingDragged +"/" + distance + "/down";
    							makeAjaxCall(url);
    						}
	    				}
	    			}
	    		})
    		}
    					
	    	window.onload = function() {
	    		makeTableSortable();
	    	};';
    	header("Content-type: text/javascript");
		echo $js;
    }
    
	/* 	This function is used to do a reset of positions on a table. If it hasa group fields (ex. category_id) 
	*	pass it along with the call. It will make sure the categorization is done based on the group_field provided.
	*	If you want to reposition values of rows only for a specific group, pass up the group_value along with the call
	*	ex. resetPositions('products', 'category_id', '1'); - This will reposition all the rows with category_id=1
	*/
    function resetPositions($table, $group_field=FALSE, $group_value=FALSE) {
    	$this->load->library('Priority_manager');
    	$manager = new Priority_manager();
    	$manager->setTable($table);
    	$manager->setGroupField($group_field);
    	$manager->setPriorityField('priority');
    	$manager->rearrangePriorities($group_value);
    }
    
	/*	This is a column callback function to show up the arrows. */
    public function populate_up_down($value, $row) {
		$primary_key = $this->session->userdata('primary_key');
    	$str = "<a href='javascript:moveToTop(" . $row->$primary_key . ")'><img src='" . base_url() . "assets/images/navigate-top-icon.png'></a>";
    	$str .= "<a href='javascript:moveUp(" . $row->$primary_key . ")'><img src='" . base_url() . "assets/images/navigate-up-icon.png'></a>";
    	$str .= "<a href='javascript:moveDown(" . $row->$primary_key . ")'><img src='" . base_url() . "assets/images/navigate-down-icon.png'></a>";
    	$str .= "<a href='javascript:moveToBottom(" . $row->$primary_key . ")'><img src='" . base_url() . "assets/images/navigate-bottom-icon.png'></a>";
    	return $str;
    }
	
	
**********************************************************************

Rest all you can see stuff inside the code / explore new possibilities.

Asumptions: The table which you are planning to have a drag drop feature should have a priority field.

How to use this,

//Set the columns .... this is recommended if you are interested to show up the move up / down buttons

    $crud->columns('productCode', 'productName', 'productLine', 'productScale', 'productVendor', 'quantityInStock', 'buyPrice', 'MSRP', 'move_up_down');

//Add the column callback to populate the field with the buttons 

    $crud->callback_column('move_up_down', array($this, 'populate_up_down'));

//You can set the order by priority - this will ensure that your rows are rightly positioned as per the required priority

    $crud->order_by('priority');

//Add the following session values

//The 1st here registers the callback function for ajax requests. UpdatePosition is the function .. products is the table 

//if we have had a group / caetgory_id ... the call would be updateGroupPosition/products/category_id

//Rest of the parameters will be assigned / associated along with the url and be called

    $this->session->set_userdata('callableAction', site_url(). '/examples/updatePosition/products');

//This another key in session is very important. I am forcing it so we can be assured the we can have any 

//primary key .. not just id (earlier i made the mistake and then realized that not everyone will prefer 

//to have just ID as primary key). Hence i am forcing you also not to make the same mistake i did :)

    $this->session->set_userdata('primary_key', 'productCode');

//This call is required so the dynamic JS that gets generated .. gets added to the view and we can avail the facility

//of drag and drop

    $crud->set_js("index.php/examples/dragdrop_js");			


