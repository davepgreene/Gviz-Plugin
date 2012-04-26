jQuery(function() {
	var wNaN=false;
	var hNaN=false;
	var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;
	var count=jQuery(".wrap table.form_table").length;
	var varlist = "";
	var catlist = "";
	var numlist = "";
	jQuery('select[name$="_svar]"]:first option').each(function() {
		varlist += "<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>";
	});
	jQuery('select[name$="_axis0]"]:first option').each(function() {
		catlist += "<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>";
	});
	jQuery('select[name$="_axis1]"]:first option').each(function() {
		numlist += "<option value='"+jQuery(this).val()+"'>"+jQuery(this).text()+"</option>";
	});
	// Creates our new skeleton form ready for entering options
	jQuery("#new_form").click(function(e) {
		e.preventDefault();
		++count;
		var table_string = "<table class='deletable form_table table"+count+"'><thead><tr><th colspan=6><h3>Chart "+count+"</h3></th><th><a href='' class='del_table button-secondary'>X</a></th><th class='t_divider'>&nbsp;</th></tr></thead><tbody><tr><td><label for='gviz_opts[f"+count+"_title]'>Title:</label></td><td colspan=4><input type='text' name='gviz_opts[f"+count+"_title]' /></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=5><h4>Chart types</h4></td><td class='t_divider'>&nbsp;</td></tr><tr class='type_r1'><td><label for='gviz_opts[f"+count+"_type]'>Column:</label></td><td><input type='radio' name='gviz_opts[f"+count+"_type]' value='column' checked='yes'  /></td><td><label for='gviz_opts[f"+count+"_type]'>Line:</label></td><td colspan=2><input type='radio' name='gviz_opts[f"+count+"_type]' value='line' /></td><td class='t_divider'>&nbsp;</td></tr><tr class='type_r2'><td><label for='gviz_opts[f"+count+"_type]'>Gauge:</label></td><td><input type='radio' name='gviz_opts[f"+count+"_type]' value='gauge' /></td><td><label for='gviz_opts[f"+count+"_type]'>Pie:</label></td><td colspan=2><input type='radio' name='gviz_opts[f"+count+"_type]' value='pie' /></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=5><h4>Dimensions</h4></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=2><label for='gviz_opts[f"+count+"_width]'>Width:</label></td><td colspan=3><input type='text' name='gviz_opts[f"+count+"_width]' /></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=2><label for='gviz_opts[f"+count+"_height]'>Height:</label></td><td colspan=3><input type='text' name='gviz_opts[f"+count+"_height]' /></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=5><h4>Variables and Controls</h4</td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=2><label for='gviz_opts[f"+count+"_axis0]'>Category:</label></td><td colspan=3><select name='gviz_opts[f"+count+"_axis0]'>"+catlist+"</select></td><td class='t_divider'>&nbsp;</td></tr><tr><td colspan=2><label for='gviz_opts[f"+count+"_axis1]'>Numeric:</label></td><td colspan=2><select name='gviz_opts[f"+count+"_axis1]'>"+numlist+"</select></td><td><input type='hidden' class='color_picker' name='gviz_opts[f"+count+"_axis1_color]'></td><td><a href='' class='add_numvar button-secondary'>+</a></td><td class='t_divider'>&nbsp;</td></tr><tr><td><label for='gviz_opts[f"+count+"_slider]'>Slider:</label></td><td><input type='checkbox' name='gviz_opts[f"+count+"_slider]' value='slider' /></td><td><label for='gviz_opts[f"+count+"_picker]'>Picker:</label></td><td colspan=2><input type='checkbox' name='gviz_opts[f"+count+"_picker]' value='picker' /></td><td class='t_divider'>&nbsp;</td></tr><tr class='slider_var'><td colspan=2><label for='gviz_opts[f"+count+"_svar]'>Slider variable:</label></td><td colspan=2><select name='gviz_opts[f"+count+"_svar]'>"+varlist+"</select></td><td><input type='hidden' class='color_picker' name='gviz_opts[f"+count+"_svar_color]'></td><td class='t_divider'>&nbsp;</td></tr></tbody></table>";
		// Insert table after last existing table
		jQuery("#tbl_end").before(table_string);
		jQuery(".color_picker").miniColors();
		// Call function to attach events to elements in the newly created table
		handleNewChartControls(count);
	});
	// Function to attach events to elements after the new table is created
	function handleNewChartControls(count) {
		// Make sure that the slider variable selector isn't displayed until the user checks the slider box
		if(jQuery('input[name$="f'+count+'_slider]"]').is(':checked')) {
			jQuery('input[name$="f'+count+'_slider]"]').closest('tr').next().show();
		} else {
			jQuery('input[name$="'+count+'_slider]"]').closest('tr').next().hide();
		}
		// Add event handler to newly created slider checkbox so it can show the variable selctor
		jQuery('.table'+count).on("change", "input[name$='f"+count+"_slider]']", function(e) {
			if(jQuery('input[name$="f'+count+'_slider]"]').is(':checked')) {
				jQuery('input[name$="f'+count+'_slider]"]').closest('tr').next().show();
			} else {
				jQuery('input[name$="'+count+'_slider]"]').closest('tr').next().hide();
			}
		});
	}
	// Add event handler to newly created "delete table" button so a click removes the table
	jQuery(".wrap").on("click", ".del_table", function(e) {
		e.preventDefault();
		jQuery(this).closest('.deletable').remove();
		--count;
		return false;
	});
		
	// ADD NEW NUMERIC VARIABLE BUTTON
	jQuery(".wrap").on("click", ".add_numvar", function(e) {
		e.preventDefault();
		var var_counter = jQuery('.new_var').length+1;
		console.log(var_counter);
		var new_row = "<tr class='new_var'><td colspan=2><label for='gviz_opts[f"+count+"_axis1_"+var_counter+"]'>Numeric:</label></td><td colspan=2><select name='gviz_opts[f"+count+"_axis1_"+var_counter+"]'>"+numlist+"</select></td><td><input type='hidden' class='color_picker' name='gviz_opts[f"+count+"_axis1_"+var_counter+"_color]'></td><td><a href='' class='del_numvar button-secondary'>-</a></td><td class='t_divider'>&nbsp;</td></tr>";
		console.log(jQuery(this).closest('tr'));
		jQuery(this).closest('tr').after(new_row);
		jQuery(".color_picker").miniColors();
	});
	jQuery(".wrap").on("click", ".del_numvar", function(e) {
		e.preventDefault();
		jQuery(this).closest(".new_var").remove();
	});

	// Submit button
	// Trap form errors before the user submits
	jQuery("#submitbutton").click(function(e) {
		var num_tables = jQuery(".wrap table.form_table").length;
		// Loop through all the tables on the page
		for(i=1;i<=num_tables;i++) {
			// Test to see if a chart type is defined. If not, throw an error.
			var group_err=true;
			jQuery('input[name$="f'+i+'_type]"]').each(function(i,e) {
				if(jQuery(this).is(':checked')) {
					group_err=false;
				}
			});
			if(group_err==true) {
				e.preventDefault();
				jQuery('input[name$="f'+i+'_type]"]').closest('tr').addClass('err');
				jQuery('input[name$="f'+i+'_type]"]').closest('tr').removeClass('noerr');
				jQuery('#submitbutton').before("<p class='input_error' id='type_error'>There are errors in the form - You must specify a chart type</p>");
				jQuery('#type_error').fadeIn('slow');
			} else {
				jQuery('input[name$="f'+i+'_type]"]').closest('tr').removeClass('err');
				jQuery('input[name$="f'+i+'_type]"]').closest('tr').addClass('noerr');
				jQuery('#type_error').fadeOut('slow');
				jQuery('#type_error').remove();
			}
			// Test to see if a control type is defined. If not, throw an error.
			if(jQuery('input[name$="f'+i+'_slider]"]').is(':checked') == false && jQuery('input[name$="f'+i+'_picker]"]').is(':checked') == false ) {
				e.preventDefault();
				jQuery('input[name$="f'+i+'_slider]"]').closest('tr').addClass('err');
				jQuery('input[name$="f'+i+'_slider]"]').closest('tr').removeClass('noerr');
				jQuery(this).before("<p class='input_error' id='control_error'>There are errors in the form - You must specify at least one control</p>");
				jQuery('#control_error').fadeIn('slow');
			} else {
				jQuery('input[name$="f'+i+'_slider]"]').closest('tr').addClass('noerr');
				jQuery('input[name$="f'+i+'_slider]"]').closest('tr').removeClass('err');
				jQuery('#control_error').fadeOut('slow');
				jQuery('#control_error').remove();
			}
			// Test if width is a valid number
			if(!numberRegex.test(jQuery('input[name$="f'+i+'_width]"]').val())) {
				e.preventDefault();
				jQuery(this).before("<p class='input_error' id='width_err'>There are errors in the form - The width you have entered is not a valid number</p>");
				jQuery('#width_err').fadeIn('slow')
			}
			// Test if height is a valid number
			if(!numberRegex.test(jQuery('input[name$="f'+i+'_height]"]').val())) {
				e.preventDefault();
				jQuery(this).before("<p class='input_error' id='height_err'>There are errors in the form - The height you have entered is not a valid number</p>");
				jQuery('#height_err').fadeIn('slow')
			}
			// Test to see if a categorical variable is defined. If not, throw an error.
			if(jQuery('select[name$="f'+i+'_axis0]"]').val()=="") {
				e.preventDefault();
				jQuery('select[name$="f'+i+'_axis0]"]').addClass('err');
				jQuery('select[name$="f'+i+'_axis0]"]').removeClass('noerr');
				jQuery(this).before("<p class='input_error' id='axis0_error'>There are errors in the form - You must specify a categorical variable</p>");
				jQuery('#axis0_error').fadeIn('slow');
			} else {
				jQuery('select[name$="f'+i+'_axis0]"]').addClass('noerr');
				jQuery('select[name$="f'+i+'_axis0]"]').removeClass('err');
				jQuery('#axis0_error').fadeOut('slow');
				jQuery('#axis0_error').remove();
			}
			// Test to see if a scale variable is defined. If not, throw an error.
			if(jQuery('select[name$="f'+i+'_axis1]"]').val()=="") {
				e.preventDefault();
				jQuery('select[name$="f'+i+'_axis1]"]').addClass('err');
				jQuery('select[name$="f'+i+'_axis1]"]').removeClass('noerr');
				jQuery(this).before("<p class='input_error' id='axis1_error'>There are errors in the form - You must specify a scale variable</p>");
				jQuery('#axis1_error').fadeIn('slow');
			} else {
				jQuery('select[name$="f'+i+'_axis1]"]').addClass('noerr');
				jQuery('select[name$="f'+i+'_axis1]"]').removeClass('err');
				jQuery('#axis1_error').fadeOut('slow');
				jQuery('#axis1_error').remove();
			}
			// Test to see if, when slider is checked, that a slider variable is defined. If not, throw an error.
			if(jQuery('select[name$="f'+i+'_svar]"]').closest('.slider_var').is(':visible')) {
				if(jQuery('select[name$="f'+i+'_svar]"]').val()=="") {
					e.preventDefault();
					jQuery('select[name$="f'+i+'_svar]"]').addClass('err');
					jQuery('select[name$="f'+i+'_svar]"]').removeClass('noerr');
					jQuery(this).before("<p class='input_error' id='svar_error'>There are errors in the form - You must specify a slider variable</p>");
					jQuery('#svar_error').fadeIn('slow');
				} else {
					jQuery('select[name$="f'+i+'_svar]"]').addClass('noerr');
					jQuery('select[name$="f'+i+'_svar]"]').removeClass('err');
					jQuery('#svar_error').fadeOut('slow');
					jQuery('#svar_error').remove();
				}
			} else {
				jQuery('select[name$="f'+i+'_svar]"]').addClass('noerr');
				jQuery('select[name$="f'+i+'_svar]"]').removeClass('err');
				jQuery('#svar_error').fadeOut('slow');
				jQuery('#svar_error').remove();
			}
		}
	});
	
	// Chart types
	// Use change event to see if the user picks a type. If any type is clicked remove any errors related to missing type.
	jQuery('input[name$="_type]"]').change(function() {
		if(jQuery(this).closest('tr').hasClass('err')) {
			jQuery(this).closest('tbody').find('tr.type_r1').removeClass('err');
			jQuery(this).closest('tbody').find('tr.type_r2').removeClass('err');
			jQuery('#type_error').fadeOut('slow');
			jQuery('#type_error').remove();
		}
	});
	
	// Width and Height
	// Attach event to width to see if user enters a valid number
	jQuery(".wrap").on("change", 'input[name$="_width]"]', function(e) {
		var str = jQuery(this).val();
		// If number isn't valid, set wNaN to true which throws an error when user clicks submit
		if(!numberRegex.test(str)) {
			wNaN=true; 
			jQuery(this).closest('tr').addClass('err');
			jQuery(this).closest('tr').removeClass('noerr');
		// Else remove any existing width related errors
		} else { 
			wNaN=false; 
			jQuery(this).closest('tr').addClass('noerr');
			jQuery(this).closest('tr').removeClass('err');
			jQuery("#width_err").fadeOut('slow');
			jQuery("#width_err").remove();
		} 
	});
	// Attach event to height to see if user enters a valid number
	jQuery(".wrap").on("change", 'input[name$="_height]"]', function(e) { 
		var str = jQuery(this).val(); 
		// If number isn't valid, set hNaN to true which throws an error when user clicks submit
		if(!numberRegex.test(str)) { 
			hNaN=true; 
			jQuery(this).closest('tr').addClass('err');
			jQuery(this).closest('tr').removeClass('noerr');
		// Else remove any existing height related errors
		} else { 
			hNaN=false; 
			jQuery(this).closest('tr').addClass('noerr');
			jQuery(this).closest('tr').removeClass('err');
			jQuery("#height_err").fadeOut('slow');
			jQuery("#height_err").remove();
		} 
	});
	// Variables
	// Test to see if any valid option has been selected in the categorical variable select box
	jQuery(".wrap").on("change", 'select[name$="_axis0]"]', function(e) {
		if(jQuery(this).val()!="") {
			if(jQuery(this).hasClass('err')) {
				jQuery(this).removeClass('err');
				jQuery('#axis0_error').fadeOut('slow');
				jQuery('#axis0_error').remove();
			}
		}
	});
	// Test to see if any valid option has been selected in the scale variable select box
	jQuery(".wrap").on("change", 'select[name$="_axis1]"]', function(e) {
		if(jQuery(this).val()!="") {
			if(jQuery(this).hasClass('err')) {
				jQuery(this).removeClass('err');
				jQuery('#axis1_error').fadeOut('slow');
				jQuery('#axis1_error').remove();
			}
		}
	});
	
	// Controls
	jQuery('.slider_var').hide(); // Hide our slider variable picker by default
	// Test to see if any slider has been selected. If it has, display that table's slider variable picker.
	jQuery('input[name$="_slider]"]').each(function(i,e) {
		if(jQuery(this).is(':checked')) {
			jQuery(this).closest('tr').next().show();
		} else {
			jQuery(this).closest('tr').next().hide();
		}
	});
	// Use change event to see if the user selects slider. If slider is clicked remove any errors related to missing controls.
	jQuery(".wrap").on("change", 'input[name$="_slider]"]', function(e) {
		if(jQuery(this).is(':checked')) {
			jQuery(this).closest('tr').next().show();
			if(jQuery(this).closest('tr').hasClass('err')) {
				jQuery(this).closest('tr').removeClass('err');
				jQuery('#control_error').fadeOut('slow');
				jQuery('#control_error').remove();
			}
		} else {
			jQuery(this).closest('tr').next().hide();
			jQuery(this).closest('tr').next().removeClass('err');
		}
	});
	// Use change event to see if the user selects picker. If picker is clicked remove any errors related to missing controls.
	jQuery(".wrap").on("change", 'input[name$="_picker]"]', function(e) {
		if(jQuery(this).is(':checked')) {
			if(jQuery(this).closest('tr').hasClass('err')) {
				jQuery(this).closest('tr').removeClass('err');
			}
		}
	});
	// Use change event to see if the user selects a slider variable. If slider is unchecked or if valid option is selected remove any errors related to missing slider var.
	jQuery(".wrap").on("change", 'select[name$="_svar]"]', function() {
		if(jQuery(this).val()!="") {
			if(jQuery(this).hasClass('err')) {
				jQuery(this).removeClass('err');
				jQuery('#svar_error').fadeOut('slow');
				jQuery('#svar_error').remove();
			}
		}
	});
	// Attach color pickers to numeric vars
	jQuery(".color_picker").miniColors();
});