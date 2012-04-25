<?php
/*
Plugin Name: Google Visualization
Plugin URI: NONE YET
Description: A plugin allowing a user to create google visualizations from data in WP DB
Version: 0.1 alpha
Author: David Bell-Feins
License: GPL2 or later
*/
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

<?php
// Define our custom table for read/write (CHANGE NAME LATER) - FEATURE: ALLOW USER TO SELECT TABLE
if (!isset($wpdb->gviz_dummydata)) { $wpdb->gviz_dummydata = $tableprefix.'gviz_dummydata'; }

// Add neccessary JSAPI scripts to the page header
add_action('init', 'gviz_queue_scripts');
function gviz_queue_scripts() { wp_enqueue_script('google.ajax', 'http://www.google.com/jsapi'); wp_enqueue_script ( 'jquery' ); }
// Create shortcode
add_shortcode( 'gvizchart', array('Gviz_Chart', 'gviz_shortcode' ) );
// Provide a place for the admin to specify a user's organization
if ( is_admin() ) {
	add_action('show_user_profile', array('Gviz_Chart', 'gviz_show_user_profile'));
	add_action('edit_user_profile', array('Gviz_Chart', 'gviz_show_user_profile'));
	add_action('personal_options_update', array('Gviz_Chart', 'gviz_save_org_data'));
	add_action('edit_user_profile_update', array('Gviz_Chart', 'gviz_save_org_data'));
}
// Define our chart class
class Gviz_Chart {
	public $title;
	public $height;
	public $width;
	public $controls;
	public $id;
	public $type;
	public $col_id;
	public $line_id;
	public $gauge_id;
	public $pie_id;
	public $svar_name;
	public $svar;
	public $axis0;
	public $axis0_name;
	public $axis1;
	public $axis1_name;
	public $num_tables;
	public $data_table;
	public $data;
	public $user_org;
	
	public function gviz_shortcode( $atts ) {
		$options = get_option('gviz_opts');
		foreach ($atts as $i=>$v) {
			if(strtoupper($i)=="NUM") $chart_num=$v;
			if(strtoupper($i)=="WIDTH") $width=$v;
			if(strtoupper($i)=="HEIGHT") $height=$v;
		}
		// Parse our options (from admin page) to generate charts
		$temp=array();
		foreach(array_keys($options) as $i=>$v) {
			$temp[] = substr($v,0,2);
		}
		$num_tables = count(array_unique($temp));
		// Instantiate our new instance of this class
		$chart = new Gviz_Chart;
		$chart->user_org = esc_attr( get_the_author_meta( 'gviz_org', get_current_user_id()));
		// Get our data so we can get axis names and indexes
		$data = $chart->gen_chart_data();
		if(current_user_can('administrator')==false) {
			foreach($data as $i=>&$v) {
				$num = $i+1;
				//krumo(array_keys($v));
				if ( is_user_logged_in() ) { 
					if(array_search($chart->user_org,$v) == false) {
						$v['OrgName'] = "Organization ".$num;
						$num++;
					}
				} else {
					$v['OrgName'] = "Organization ".$num;
					$num++;
				}
			}
			unset($v);
		}
		$cols = json_encode(array_keys($data[0]));
		$rows = '';
		foreach ($data as $i=>$v) {
			if ($i != count($data)-1) {
				$rows .= json_encode(array_values($v)) . ','."\n";
			} else {
				$rows .= json_encode(array_values($v));
			}
		}
		$chart->data_table = $cols.','."\n".$rows;
		$chart->data = $data;
		// Loop through the number of tables in options to separate each table's options
		for($i=1;$i<=$num_tables;$i++) {
			if($i==$chart_num) {
				// Text field attributes
				$chart->title=$options['f'.$i.'_title'];
				if(!$width) { $chart->width=$options['f'.$i.'_width']; } else { $chart->width = $width; }
				if(!$height) { $chart->height=$options['f'.$i.'_height']; } else { $chart->height = $height; }
				// Select box attributes
				$chart->svar=array_search($options['f'.$i.'_svar'], array_keys($data[0]));
				$chart->svar_name=$options['f'.$i.'_svar'];
				// Axes
				$chart->axis0=array_search($options['f'.$i.'_axis0'], array_keys($data[0]));
				$chart->axis0_name=$options['f'.$i.'_axis0'];
				$chart->axis1=array_search($options['f'.$i.'_axis1'], array_keys($data[0]));
				$chart->axis1_name=$options['f'.$i.'_axis1'];
				
				// Chart type
				switch ($options['f'.$i.'_type']) {
				case 'column': 
					$chart->type = $options['f'.$i.'_type']; 
					$chart->col_id = uniqid();
					break;
				case 'line':
					$chart->type = $options['f'.$i.'_type']; 
					$chart->line_id = uniqid();
					break;
				case 'gauge':
					$chart->type = $options['f'.$i.'_type'];
					$chart->gauge_id = uniqid();
					break;
				case 'pie':
					$chart->type = $options['f'.$i.'_type'];
					$chart->pie_id = uniqid();
					break;
				}
				// Chart options
				if($options['f'.$i.'_slider']=='slider') $chart->controls[] = $options['f'.$i.'_slider'];
				if($options['f'.$i.'_picker']=='picker') $chart->controls[] = $options['f'.$i.'_picker'];
				// Generate unique ID for controls
				$chart->id = uniqid();
			}
		}
		// Get the number of tables that should be displayed based on the number of elements in our $type array
		$chart->num_tables = count($chart->type);
		
		// Build our visualization
		$chart->generate_visualization();
	}
	private function build_slider() {
		return "var slider = new google.visualization.ControlWrapper({
			'controlType': 'NumberRangeFilter',
			'containerId': '".$this->id."_control1',
			'options': {
				'filterColumnLabel': '".$this->svar_name."',
				'ui': {'labelStacking': 'vertical'}}});";
	}
	private function build_picker() {
		return 
		"var picker = new google.visualization.ControlWrapper({
			'controlType': 'CategoryFilter',
			'containerId': '".$this->id."_control2',
			'options': {
				'filterColumnLabel': '".$this->axis0_name."',
				'ui': {
					'labelStacking': 'vertical',
					'allowTyping': false,
					'allowMultiple': false
				}
			}
		});";
	}
	private function build_column() {
		return "var column = new google.visualization.ChartWrapper({
					'chartType': 'ColumnChart',
					'containerId': '".$this->col_id."_chart1',
					'options': {
					'width':".$this->width.",
					'height':".$this->height.",
					'legend': 'none',
					'vAxis': {'title': '".$this->axis1_name."', 'titleTextStyle': {'fontSize': 16}, 'textPosition': 'out' },
					'hAxis': {'title': '".$this->axis0_name."', 'titleTextStyle': {'fontSize': 16}, 'textPosition': 'out' },
					'colors': ['#".$this->random_color()."','#".$this->random_color()."','#".$this->random_color()."'],
					'chartArea': { left:40, top: 30, width: '75%', height: '75%'},
					}
					});";
	}
	private function build_line() {
		return "var line = new google.visualization.ChartWrapper({
					'chartType': 'LineChart',
					'containerId': '".$this->line_id."_chart1',
					'options': {
					'width':".$this->width.",
					'height':".$this->height.",
					'legend': 'none',
					'vAxis': {'title': '".$this->axis1_name."', 'titleTextStyle': {'fontSize': 16} },
					'hAxis': {'title': '".$this->axis0_name."', 'titleTextStyle': {'fontSize': 16} },
					'colors': ['#".$this->random_color()."'],
					'chartArea': { left:40, top: 30, width: '75%', height: '75%'}
					}
					});";
	}
	private function build_table() {
		return "var table = new google.visualization.ChartWrapper({
				'chartType': 'Table',
				'containerId': '".$this->table_id."_chart1',
				'options': {
					'width': ".$this->width.",
					'height': ".$this->height."
				}
			});";
	}
	private function build_gauge() {
		$groupby = array();
		foreach($this->data as $i=>$v) {
			$groupby[$v[$this->axis0_name]][] = $v[$this->axis1_name];
		}
		foreach($groupby as $i=>&$v) {
			$v = array_sum($v);
		}
		unset($v);
		asort($groupby);
		$last = array_pop($groupby);
		$max = ceil($last*0.2)+$last;
		return "var gauge = new google.visualization.ChartWrapper({
					'chartType': 'Gauge',
					'containerId': '".$this->gauge_id."_chart1',
					'options': {
					'width':".$this->width.",
					'height':".$this->height.",
					'legend': 'none',
					'max': ".$max.",
					'vAxis': {'title': '".$this->axis1_name."', 'titleTextStyle': {'fontSize': 16} },
					'hAxis': {'title': '".$this->axis0_name."', 'titleTextStyle': {'fontSize': 16} },
					'chartArea': { left:0, top: 0, width: '80%', height: '80%'},
					}
					});";
	}
	private function build_pie() {
		return "var pie = new google.visualization.ChartWrapper({
					'chartType': 'PieChart',
					'containerId': '".$this->pie_id."_chart1',
					'options': {
					'width':".$this->width.",
					'height':".$this->height.",
					'legend': 'none',
					'vAxis': {'title': '".$this->axis1_name."', 'titleTextStyle': {'fontSize': 16} },
					'hAxis': {'title': '".$this->axis0_name."', 'titleTextStyle': {'fontSize': 16} },
					'chartArea': { left:40, top: 30, width: '80%', height: '75%'},
					'legend': {position: 'right' },
					'is3D': true,
					'pieSliceText': 'label'
					}
					});";
	}
	private function generate_visualization() {
		if(!$this->controls) {
			$slider = false; 
			$picker = false; 
		} else {
			foreach ($this->controls as $i=>$v) { 
					if (strtoupper($v) == "SLIDER") $slider = true; 
					if (strtoupper($v) == "PICKER") $picker = true;
			} 
		}
			?>
<script type='text/javascript'>google.load('visualization', '1.1', {packages: ['controls', 'table']});</script>
<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('table[id$="_gviz_dashboard"] th').css('font-size','1.1em');
});
function drawVisualization() {
	var pie='PieChart';
	var column='ColumnChart';
	var line='LineChart';
	var gauge='Gauge';
	var data = google.visualization.arrayToDataTable([
<?php echo $this->data_table; ?>
]);
<?php if ($slider) echo $this->build_slider(); ?>

<?php if ($picker) echo $this->build_picker(); ?>
var grouped_data = google.visualization.data.group(data, 
[<?php echo $this->axis0 ?>],
[
{'column': <?php echo $this->axis1 ?>, 'aggregation': google.visualization.data.sum, 'type': 'number'}<?php if($this->axis1 != $this->svar && $slider) { ?>,
{'column': <?php echo $this->svar ?>, 'aggregation': google.visualization.data.sum, 'type': 'number'} <?php } ?>


]);
<?php 
	if ($this->type=="column") echo $this->build_column(); 
	if ($this->type=="gauge") echo $this->build_gauge();
	if ($this->type=="line") echo $this->build_line();
	if ($this->type=="pie") echo $this->build_pie(); ?>
	
<?php if($this->controls) { ?>
var table = new google.visualization.Table(document.getElementById('<?php echo $this->id."_"?>gviz_dashboard'));
//table.draw(grouped_data, null);
new google.visualization.Dashboard(document.getElementById('<?php echo $this->id."_"?>gviz_dashboard')).
// Establish bindings
bind([<?php echo implode(", ", $this->controls); ?>],[<?php echo $this->type; ?>]).
// Draw the entire dashboard.
draw(grouped_data, null);
<?php } ?>
}
google.setOnLoadCallback(drawVisualization);
</script>
<table id="<?php echo $this->id."_"?>gviz_dashboard">
<thead><tr><th><?php echo $this->title; ?></th></tr></thead>
<tr><?php 
	if($this->num_tables==1) {
		$colspan=4;
	} else { 
		$colspan=2;
	} 
	if($this->type=='column') { ?>
<td colspan=<?php echo $colspan ?> id="<?php echo $this->col_id."_"?>chart1"></td></tr>
<?php } if($this->type=='line') { ?>
<td colspan=<?php echo $colspan ?> id="<?php echo $this->line_id."_"?>chart1"></td></tr>
<?php } if($this->type=='pie') { ?>
<td colspan=<?php echo $colspan ?> id="<?php echo $this->pie_id."_"?>chart1"></td></tr>
<?php } if($this->type=='gauge') { ?>
<td colspan=<?php echo $colspan ?> id="<?php echo $this->gauge_id."_"?>chart1"></td></tr>
<?php } ?></tr>
<tr><td colspan=2><div id="<?php echo $this->id."_"?>control1"></div></td><td colspan=2><div id="<?php echo $this->id."_"?>control2"></div></td></tr>
</table>
<?php
	}
	// Retrieve data from our database and convert to valid JSON that charts can read
	public function gen_chart_data() {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->gviz_dummydata" ), ARRAY_N );
		$new_result = array();
		foreach ($rows as $row) {
			$new_row = array();
			foreach ($row as $i => $value) {
				if ( $wpdb->col_info[$i]->numeric ) {
					if ( $wpdb->col_info[$i]->type == 'float' || $wpdb->col_info[$i]->type == 'double' || $wpdb->col_info[$i]->type == 'real' )
						$new_row[$wpdb->col_info[$i]->name] = (float)$value;
					elseif ( $wpdb->col_info[$i]->type == 'bool' || $wpdb->col_info[$i]->type == 'boolean' )
						$new_row[$wpdb->col_info[$i]->name] = (bool)$value;
					else
						$new_row[$wpdb->col_info[$i]->name] = (int)$value;
				}
				elseif ( $wpdb->col_info[$i]->type == 'datetime' )
					$new_row[$wpdb->col_info[$i]->name] = strtotime($value);
				else
					$new_row[$wpdb->col_info[$i]->name] = $value;
			}
			$new_result[] = $new_row;
		}
		$data_table = $new_result;
		return $data_table;
	}
	// Add html to user options page
	function gviz_show_user_profile( $user ) {
		$chart = new Gviz_Chart;
		// Get our data so we can get axis names and indexes
		$data = $chart->gen_chart_data();
		$orgs = array();
		foreach($data as $i=>$v) {
			$orgs[] = $v['OrgName'];
		}
		if ( current_user_can( 'administrator' ) ) {
	?>
		<h3><?php _e('Organization') ?></h3>
		<table class="form-table">
			<tr>
				<td>
					<select name="gviz_org">
						<option value="">Select...</option>
						<?php 
							foreach($orgs as $i=>$v) {
								echo '<option value="'.$v.'" '.selected( esc_attr( get_the_author_meta( 'gviz_org', $user->ID ) ), $v ).'>'.$v.'</option>';
							}
						?>
					</select>
				</td>
			</tr>
		</table>
	<?php
		}
	}
	function gviz_save_org_data( $user_id ) {
		update_usermeta( $user_id, 'gviz_org', $_POST['gviz_org'] );
	}
	// Tests the user's organization - INCOMPLETE FEATURE
	public function user_test($org) {
		global $current_user;
		get_currentuserinfo();
		$user_org = esc_attr( get_the_author_meta( 'gviz_org', $user->ID));
		if (strtoupper($org) == strtoupper($user_org)) return true;
		else return false;
	}
	// Random color generator
	public function random_color() {
		mt_srand((double)microtime()*1000000);
		$c = '';
		while(strlen($c)<6){
			$c .= sprintf("%02X", mt_rand(0, 255));
		}
		return $c;
	}
	// File handler for quick data uploads - MOST LIKELY DEPRECIATED
	/*public function uploader() {
		global $wpdb;
		if ($_FILES["file"]["error"] > 0) {
			echo "Error: " . $_FILES["file"]["error"] . "<br />";
		}
		else {
			if(($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
				while(($line = fgets($handle)) !== FALSE) {
					$string = explode(",",$line);
					$str[] = $string;
				}
			}
			fclose($handle);
			// Set up our cols
			$cols = explode(",\n",$this->gen_chart_data());
			krumo(json_decode($cols[0]));
			krumo($str);
			foreach ($str as $i=>$v) {
				$wpdb->insert($wpdb->gviz_dummydata,$v,array('%s','%s','%d','%d'));
			}
		}
	}*/
}
if ( is_admin() ) {	require_once ( 'google-viz-admin.php' ); }

?>