<?php
/*
Plugin Name: VLW Tabelle
Plugin URI: 
Description: Tabelle einer beliebigen Staffel/Saison des Volleyball-Landesverband Württemberg
Author: Pascal Schumann
Version: 1.0

Copyright 2016 Pascal Schumann

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');
	
	
add_action( 'widgets_init', function(){
     register_widget( 'Vlw_tabelle_widget' );
});	

/**
 * Adds Vlw_tabelle_widget widget.
 */
class Vlw_tabelle_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Vlw_tabelle_widget', // Base ID
			__('VLW Tabelle', 'text_domain'), // Name
			array('description' => __( 'Tabelle einer beliebigen Staffel/Saison des Volleyball-Landesverband Württemberg', 'text_domain' ),) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// get the excerpt of the required story
				
		if ( array_key_exists('before_widget', $args) ) echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if (empty($instance['saison']) || empty($instance['staffel_id'])) {
			echo "Staffel ID und/oder Saison sind nicht konfiguriert!";
		}
		else
		{
			$xml = simplexml_load_file("https://vlw.it4sport.de/data/vbwb/aufsteiger/public/tabelle_".$instance['saison']."_".$instance['staffel_id'].".xml");

			if (sizeof( $xml->element ) > 0 ) {
				$team = str_replace(" ", "", $instance['team_name']);
				echo "<table> <tr><th></th><th>Verein</th><th>Spiele</th><th>Sätze</th><th>Ballpunkte</th><th>Punkte</th></tr>";
		        foreach ($xml->element as $element)
		        {
		        	$tab_team = str_replace(" ", "", $element->team->__toString());
		        	echo "<tr>";
		        	if ($tab_team == $team) {
		        		echo "<td><strong>$element->platz</strong></td>";
						echo "<td><strong>$element->team</strong></td>";
						echo "<td><strong>$element->spiele</strong></td>";
						echo "<td><strong>$element->plussaetze:$element->minussaetze</strong></td>";
						echo "<td><strong>$element->plusbaelle:$element->minusbaelle</strong></td>";
						echo "<td><strong>$element->dppunkte</strong></td>";
					}
					else {
						echo "<td>$element->platz</td>";
						echo "<td>$element->team</td>";
						echo "<td>$element->spiele</td>";
						echo "<td>$element->plussaetze:$element->minussaetze</td>";
						echo "<td>$element->plusbaelle:$element->minusbaelle</td>";
						echo "<td>$element->dppunkte</td>";
					}
					
					echo "</tr>";  
		        }
		 
		    	echo "</table>";
			}
		}

		if ( array_key_exists('after_widget', $args) ) echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'staffel_id' ] ) ) {
			$staffel_id = $instance[ 'staffel_id' ];
		}
		else {
			$staffel_id = "";
		}

		if ( isset( $instance[ 'saison' ] ) ) {
			$saison = $instance[ 'saison' ];
		}
		else {
			$saison = "";
		}

		if ( isset( $instance[ 'team_name' ] ) ) {
			$team_name = $instance[ 'team_name' ];
		}
		else {
			$team_name = "";
		}

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = "";
		}

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			
			<input class="widefat" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" value="<?php echo $title; ?>"/> 
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'staffel_id' ); ?>"><?php _e( 'Staffel ID:' ); ?></label> 
			
			<input type="text" name="<?php echo $this->get_field_name( 'staffel_id' ); ?>" id="<?php echo $this->get_field_id( 'staffel_id' ); ?>" value="<?php echo $staffel_id; ?>" /> 
			(ID der Staffel, für welche die Tabelle abgerufen werden soll z.B. "2889")
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'saison' ); ?>"><?php _e( 'Saison:' ); ?></label> 
			
			<input type="text" name="<?php echo $this->get_field_name( 'saison' ); ?>" id="<?php echo $this->get_field_id( 'saison' ); ?>" value="<?php echo $saison; ?>" /> 
			(Saison für welche die Tabelle angezeigt werden soll. z.B. "2016")
		</p>


		<p>
			<label for="<?php echo $this->get_field_id( 'team_name' ); ?>"><?php _e( 'Team:' ); ?></label> 
			
			<input type="text" name="<?php echo $this->get_field_name( 'team_name' ); ?>" id="<?php echo $this->get_field_id( 'team_name' ); ?>" value="<?php echo $team_name; ?>"/> 

			(Name der Mannschaft die in der Tabelle hervorgehoben wird. z.B.: "MTV Stuttgart 4")
		</p>

		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		if (!empty($new_instance['staffel_id']) && is_numeric($new_instance['staffel_id'])) {
			$instance['staffel_id'] = strip_tags( $new_instance['staffel_id']);
		}
		else {
			$instance['staffel_id'] = "";
		}
		if (!empty($new_instance['saison']) && is_numeric($new_instance['saison'])) {
			$instance['saison'] = strip_tags( $new_instance['saison']);
		}
		else {
			$instance['saison'] = "";
		}
		if (!empty($new_instance['team_name']) ) {
			$instance['team_name'] = strip_tags($new_instance['team_name']);
		}
		else {
			$instance['team_name'] = "";
		}

		if (!empty($new_instance['title']) ) {
			$instance['title'] = strip_tags($new_instance['title']);
		}
		else {
			$instance['title'] = "";
		}

		return $instance;
	}

} // class Vlw_tabelle_widget