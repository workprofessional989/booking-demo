jQuery('#vehicletype').on('change', function (e) {
	var optionSelected = jQuery("option:selected", this);
	var valueSelected = this.value;
	jQuery.ajax({ 
			url: my_ajax_object.ajax_url, 
			data: {    
				'action': 'getvehicles', 	 
				'vehicletype': valueSelected			 
			}, 
			success:function(data) { 		
				
				jQuery('#vehicleselect').find('option').remove();
				jQuery('#vehicleselect').append(data);
			},    
				error: function(errorThrown){

				alert("Failed :( ");
			}  
		}); 
});


jQuery('#vehicleselect').on('change', function (e) {
	var optionSelected = jQuery("option:selected", this);
	var value = this.value;
	jQuery('#price').val("");
	jQuery.ajax({ 
			url: my_ajax_object.ajax_url, 
			data: {    
				'action': 'getvehiclesprice', 	 
				'vehicle': value			 
			}, 
			success:function(data) { 		
				
				jQuery('#price').val(data);
			
			},    
				error: function(errorThrown){

				alert("Failed :( ");
			}  
		}); 
});


