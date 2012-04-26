<?php
	// Loads the plugin
	function gviz_init() {
		add_action ( 'admin_menu', 'gviz_menus' );
		add_action( 'admin_init', 'gviz_opt_init' );
	}
	function gviz_menus() {
		add_options_page( 'Google Visualization Options', 'Google Visualization', 'manage_options', __FILE__, 'gviz_options' );
		// add javascript
		wp_enqueue_script ( 'google-viz', path_join ( WP_PLUGIN_URL, basename ( dirname ( __FILE__ ) ) . '/gviz_script.js' ), array ('jquery' ) );
		wp_enqueue_script ( 'color-picker', path_join ( WP_PLUGIN_URL, basename ( dirname ( __FILE__ ) ) . '/jquery.miniColors.min.js' ), array ('jquery' ) );
		// add css
		
	}
	function gviz_opt_init(){
		register_setting( 'gviz_options', 'gviz_opts', 'gviz_opts_validate' );
	}
	function gviz_options() {
		echo '<link type="text/css" rel="stylesheet" href="'.path_join ( WP_PLUGIN_URL, basename ( dirname ( __FILE__ ) )).'/gviz_style.css'.'" />' . "\n";
		echo '<link type="text/css" rel="stylesheet" href="'.path_join ( WP_PLUGIN_URL, basename ( dirname ( __FILE__ ) )).'/jquery.miniColors.css'.'" />' . "\n";
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		// Put the variable types in an array so later we can split categorical vars away from continuous vars
		$data = Gviz_Chart::gen_chart_data();
		foreach($data as $i=>$v) {
			foreach($v as $j=>$w) {
				$var_types[$j] = gettype($w);
			}
		}
?>
<div id="icon-tools" class="icon32"></div>
<div class="wrap">
	<?php echo "<h2>" . __( 'Google Visualization Options' ) . "</h2>"; ?>

	<?php    echo "<h3>" . __( 'Chart Options' ) . "</h3>"; ?>
	<form action="options.php" method="post">
		<?php settings_fields('gviz_options'); ?>
		<?php $options = get_option('gviz_opts'); krumo($options); 
		foreach ($options as $k => $v) {
			if (substr($k, 3, 6) == "axis1_" && strlen($k)==10) {
				$temp[] = substr($k, -1);
			}
		}
		sort($temp);
		$num_vars = array_pop($temp);
?>
	<table class="form_table"><thead><tr><th colspan=6><?php echo "<h3>" . __( 'Chart 1' ) . "</h3>"; ?></th><th colspan=5><?php if($options) echo "<h3>" . __( 'Table Preview' ) . "</h3>"; ?></th></tr></thead>
	<tbody><tr><td><label for="gviz_opts[f1_title]"><?php echo __( 'Title' ) . ":"; ?></label></td><td colspan=4><input type="text" name="gviz_opts[f1_title]" value="<?php echo $options['f1_title']; ?>" /></td><td class="t_divider">&nbsp;</td><?php if($options) { ?><td rowspan=13><?php Gviz_Chart::gviz_shortcode(array("num" => "1", "width" => 575, "height" => 250)); ?></td><?php } ?></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Chart types' ) . "</h4>"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="type_r1"><td><label for="gviz_opts[f1_type]"><?php echo __( 'Column' ) . ":"; ?></label></td><td><input type="radio" name="gviz_opts[f1_type]" value="column" <?php checked('column', $options['f1_type']); ?> /></td><td><label for="gviz_opts[f1_type]"><?php echo __( 'Line' ) . ":"; ?></label></td><td colspan=2><input type="radio" name="gviz_opts[f1_type]" value="line" <?php checked('line', $options['f1_type']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="type_r2"><td><label for="gviz_opts[f1_type]"><?php echo __( 'Gauge' ) . ":"; ?></label></td><td><input type="radio" name="gviz_opts[f1_type]" value="gauge" <?php checked('gauge', $options['f1_type']); ?> /></td><td><label for="gviz_opts[f1_type]"><?php echo __( 'Pie' ) . ":"; ?></label></td><td colspan=2><input type="radio" name="gviz_opts[f1_type]" value="pie" <?php checked('pie', $options['f1_type']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Dimensions' ) . "</h4>"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f1_width]"><?php echo __( 'Width' ) . ":"; ?></label></td><td colspan=3><input type="text" name="gviz_opts[f1_width]" value="<?php echo $options['f1_width']; ?>" /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f1_height]"><?php echo __( 'Height' ) . ":"; ?></label></td><td colspan=3><input type="text" name="gviz_opts[f1_height]" value="<?php echo $options['f1_height']; ?>" /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Variables and Controls' ) . "</h4"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f1_axis0]"><?php echo __( 'Category' ) . ":"; ?></label></td><td colspan=3><select name="gviz_opts[f1_axis0]"><option value="">Select...</option><?php foreach($var_types as $i=>$v) { if($v == 'string') { echo '<option value="'.$i.'" '.selected( $options['f1_axis0'], $i ).'>'.$i.'</option>'; }} ?></select></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f1_axis1]"><?php echo __( 'Numeric' ) . ":"; ?></label></td><td colspan=2><select name="gviz_opts[f1_axis1]"><option value="">Select...</option><?php foreach($var_types as $i=>$v) { if($v != 'string') { echo '<option value="'.$i.'" '.selected( $options['f1_axis1'], $i ).'>'.$i.'</option>'; }} ?></select></td><td><input type="hidden" class="color_picker" value="<?php echo $options['f1_axis1_color']; ?>" name="gviz_opts[f1_axis1_color]"></td><td><a href='' class='add_numvar button-secondary'>+</a></td><td class="t_divider">&nbsp;</td></tr>
	
	<?php 
		for($a=1;$a<=$num_vars;$a++) { ?>
		<tr class="new_var">
			<td colspan=2>
				<label for="gviz_opts[f1_axis1_<?php echo $a ?>]">Numeric:</label>
			</td>
			<td colspan=2>
				<select name="gviz_opts[f1_axis1_<?php echo $a ?>]">
					<option value="">Select...</option>
					<?php 
					foreach($var_types as $i=>$v) {
						if($v != 'string') {
							echo '<option value="'.$i.'" '.selected( $options['f1_axis1_'.$a.''], $i ).'>'.$i.'</option>'; 
						}
					} 
					?>
				</select>
			</td>
			<td>
				<input type="hidden" class="color_picker" name="gviz_opts[f1_axis1_<?php echo $a ?>_color]" value="<?php echo $options['f1_axis1_'.$a.'_color']; ?>">
			</td>
			<td>
				<a href="" class="del_numvar button-secondary">-</a>
			</td>
			<td class="t_divider">&nbsp;</td>
		</tr>
	<?php }
	?>
	
	<tr><td><label for="gviz_opts[f1_slider]"><?php echo __( 'Slider' ) . ":"; ?></label></td><td><input type="checkbox" name="gviz_opts[f1_slider]" value="slider" <?php checked('slider', $options['f1_slider']); ?> /></td><td><label for="gviz_opts[f1_picker]"><?php echo __( 'Picker' ) . ":"; ?></label></td><td colspan=2><input type="checkbox" name="gviz_opts[f1_picker]" value="picker" <?php checked('picker', $options['f1_picker']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="slider_var"><td colspan=2><label for="gviz_opts[f1_svar]"><?php echo __( 'Slider variable' ) . ":"; ?></label></td><td colspan=2><select name="gviz_opts[f1_svar]"><option value="">Select...</option><?php foreach($var_types as $i=>$v) { if($v != 'string') { echo '<option value="'.$i.'" '.selected( $options['f1_svar'], $i ).'>'.$i.'</option>'; } } ?></select></td><td><input type="hidden" class="color_picker" value="<?php echo $options['f1_svar_color']; ?>" name="gviz_opts[f1_svar_color]"></td><td class="t_divider">&nbsp;</td></tr>
	</tbody></table>
	<?php 
	$temp=array();
	if($options) {
	foreach(array_keys($options) as $i=>$v) {
		$temp[] = substr($v,0,2);
	}
	$num_tables = count(array_unique($temp));
	for($i=2;$i<=$num_tables;$i++) { ?>
	<table class="deletable form_table">
	<thead>
	<tr><th colspan=5><?php echo "<h3>" . __( 'Chart '.$i.'' ) . "</h3>"; ?></th><th><a href='' class='del_table button-secondary'>X</a></th><th colspan=5><?php echo "<h3>" . __( 'Table Preview' ) . "</h3>"; ?></th></tr>
	</thead>
	<tbody>
	<tr><td><label for="gviz_opts[f<?php echo $i ?>_title]"><?php echo __( 'Title' ) . ":"; ?></label></td><td colspan=4><input type="text" name="gviz_opts[f<?php echo $i ?>_title]" value="<?php echo $options['f'.$i.'_title']; ?>" /></td><td class="t_divider">&nbsp;</td><?php if($options) { ?><td rowspan=13><?php Gviz_Chart::gviz_shortcode(array("num" => $i, "width" => 575, "height" => 250)); ?></td><?php } ?></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Chart types' ) . "</h4>"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="type_r1"><td><label for="gviz_opts[f<?php echo $i ?>_type]"><?php echo __( 'Column' ) . ":"; ?></label></td><td><input type="radio" name="gviz_opts[f<?php echo $i ?>_type]" value="column" <?php checked('column', $options['f'.$i.'_type']); ?> /></td><td><label for="gviz_opts[f<?php echo $i ?>_type]"><?php echo __( 'Line' ) . ":"; ?></label></td><td colspan=2><input type="radio" name="gviz_opts[f<?php echo $i ?>_type]" value="line" <?php checked('line', $options['f'.$i.'_type']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="type_r2"><td><label for="gviz_opts[f<?php echo $i ?>_type]"><?php echo __( 'Gauge' ) . ":"; ?></label></td><td><input type="radio" name="gviz_opts[f<?php echo $i ?>_type]" value="gauge" <?php checked('gauge', $options['f'.$i.'_type']); ?> /></td><td><label for="gviz_opts[f<?php echo $i ?>_type]"><?php echo __( 'Pie' ) . ":"; ?></label></td><td colspan=2><input type="radio" name="gviz_opts[f<?php echo $i ?>_type]" value="pie" <?php checked('pie', $options['f'.$i.'_type']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Dimensions' ) . "</h4>"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f<?php echo $i ?>_width]"><?php echo __( 'Width' ) . ":"; ?></label></td><td colspan=3><input type="text" name="gviz_opts[f<?php echo $i ?>_width]" value="<?php echo $options['f'.$i.'_width']; ?>" /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f<?php echo $i ?>_height]"><?php echo __( 'Height' ) . ":"; ?></label></td><td colspan=3><input type="text" name="gviz_opts[f<?php echo $i ?>_height]" value="<?php echo $options['f'.$i.'_height']; ?>" /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=5><?php echo "<h4>" .__( 'Variables and Controls' ) . "</h4"; ?></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f<?php echo $i ?>_axis0]"><?php echo __( 'Category' ) . ":"; ?></label></td><td colspan=3><select name="gviz_opts[f<?php echo $i ?>_axis0]"><option value="">Select...</option><?php foreach($var_types as $j=>$v) { if($v == 'string') { echo '<option value="'.$j.'" '.selected( $options['f'.$i.'_axis0'], $j ).'>'.$j.'</option>'; }} ?></select></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td colspan=2><label for="gviz_opts[f<?php echo $i ?>_axis1]"><?php echo __( 'Numeric' ) . ":"; ?></label></td><td colspan=2><select name="gviz_opts[f<?php echo $i ?>_axis1]"><option value="">Select...</option><?php foreach($var_types as $j=>$v) { if($v != 'string') { echo '<option value="'.$j.'" '.selected( $options['f'.$i.'_axis1'], $j ).'>'.$j.'</option>'; }} ?></select></td><td><input type="hidden" class="color_picker" value="<?php echo $options['f'.$i.'_axis1_color']; ?>" name="gviz_opts[f<?php echo $i ?>_axis1_color]"></td><td><a href='' class='add_numvar button-secondary'>+</a></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr><td><label for="gviz_opts[f<?php echo $i ?>_slider]"><?php echo __( 'Slider' ) . ":"; ?></label></td><td><input type="checkbox" name="gviz_opts[f<?php echo $i ?>_slider]" value="slider" <?php checked('slider', $options['f'.$i.'_slider']); ?> /></td><td><label for="gviz_opts[f<?php echo $i ?>_picker]"><?php echo __( 'Picker' ) . ":"; ?></label></td><td colspan=2><input type="checkbox" name="gviz_opts[f<?php echo $i ?>_picker]" value="picker" <?php checked('picker', $options['f'.$i.'_picker']); ?> /></td><td class="t_divider">&nbsp;</td></tr>
	
	<tr class="slider_var"><td colspan=2><label for="gviz_opts[f<?php echo $i ?>_svar]"><?php echo __( 'Slider variable' ) . ":"; ?></label></td><td colspan=2><select name="gviz_opts[f<?php echo $i ?>_svar]"><option value="">Select...</option><?php foreach($var_types as $j=>$v) { if($v != 'string') { echo '<option value="'.$j.'" '.selected( $options['f'.$i.'_svar'], $j ).'>'.$j.'</option>'; } } ?></select></td><td><input type="hidden" class="color_picker" value="<?php echo $options['f'.$i.'_svar_color']; ?>" name="gviz_opts[f<?php echo $i ?>_svar_color]"></td><td class="t_divider">&nbsp;</td></tr>
	</tbody></table>
	<?php } } ?>
	<div id="tbl_end"></div>
	<br />
	<input class="button-primary" type="submit" name="Save" value="<?php _e('Save Options'); ?>" id="submitbutton" />
	</form>
	<br />
	<a href="" id="new_form" class="button-secondary"><?php echo __( 'Add new form' ) ?></a>
	<br />
	<br />
	<form name="deleter" action="options.php" method="post">
	<input type="hidden" name="del_input" value="yes">
	<a href="" id="del_options" type="submit" name="Del" class="button-secondary"><?php echo __( 'Delete options' ); //delete_option('gviz_opts'); ?></a>
	</form>
	<br />
	<hr />
	<p>Some icons by <a href="http://p.yusukekamiyamane.com/">Yusuke Kamiyamane</a>. All rights reserved.</p>
	<?php /*    echo "<h4>" . __( 'File Uploader' ) . "</h4>"; ?>

	<form action="" method="post" enctype="multipart/form-data">
		<label for="file">Filename:</label>
		<input type="file" name="file" id="file" /> 
		<br />
		<input type="submit" name="submit" value="Submit" id="upload_submit" />
	</form>
	<br /><?php */ 
	?>
</div>
<?php 
	}
	
	function gviz_opts_validate($input) {
		$temp=array();
		foreach(array_keys($input) as $i=>$v) {
			$temp[] = substr($v,0,2);
		}
		$num_tables = count(array_unique($temp));
		foreach($input as $key=>$val) {
				if(substr($key, -5) == "color") {
					$colors[$key] = $val;
				}
			}
		for($i=1;$i<=$num_tables;$i++) {
			$input['f'.$i.'_title'] =  wp_filter_nohtml_kses($input['f'.$i.'_title']);
			if($input['f'.$i.'_title']=='') $input['f'.$i.'_title']='Chart '.$i;
			if(!is_numeric($input['f'.$i.'_height'])) $input['f'.$i.'_height']=200;
			if(!is_numeric($input['f'.$i.'_width'])) $input['f'.$i.'_width']=300;
			foreach($colors as $k=>&$v) {
				if(strtoupper($v) == "#FFFFFF") {
					$v = "";
				}
			}
			unset($v);
		}
		$input = array_merge($input, $colors);
		return $input;
	}
	add_action( 'init', 'gviz_init' );

?>