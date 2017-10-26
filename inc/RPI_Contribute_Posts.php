<?php

/**
 * Class RPI_Contribute_Options
 *
 * Contains some helper code for plugin options
 *
 */

class RPI_Contribute_Posts {

	static public function add_metaboxes() {
		$servercheck = RPI_Contribute_API::remote_say_hello();
		if ( $servercheck->answer == 'Connected') {
			add_meta_box(
				'contribute_metabox',
				__( 'Materialpool contribute', RPI_Contribute::$textdomain ),
				array( 'RPI_Contribute_Posts', 'metabox' ),
				'post',
				'side',
				'default'
			);
		}
	}

	static public function metabox() {

		echo "<h2>Bildungsstufe</h2>";
		$bildungsstufen = RPI_Contribute_API::list_bildungsstufen();
		foreach ( $bildungsstufen as $stufe ) {
			if ( $stufe->parent == 0 ) {
				echo "<input type='checkbox'>". $stufe->name . "<br>";
				foreach ( $bildungsstufen as $stufe2 ) {
					if ( $stufe2->parent == $stufe->term_id ) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox'>". $stufe2->name . "<br>";
					}
				}
			}
		}
		echo "<h2>Altersstufestufe</h2>";

		$altersstufen = RPI_Contribute_API::list_altersstufen();
		foreach ( $altersstufen as $alter ) {
			if ( $alter->parent == 0 ) {
				echo "<input type='checkbox'>". $alter->name . "<br>";
				foreach ( $altersstufen as $alter2 ) {
					if ( $alter2->parent == $alter->term_id ) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox'>". $stufe2->name . "<br>";
					}
				}
			}
		}
		echo "<a target='_new' href='". $url ."' class='preview button' >Send to materialpool</a><br><br>";
	}

}