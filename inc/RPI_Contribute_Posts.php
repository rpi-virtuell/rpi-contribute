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
			$usercheck = RPI_Contribute_API::check_user();
			if ( $usercheck == true) {
				add_meta_box(
					'contribute_metabox',
					__( 'Materialpool contribute', RPI_Contribute::$textdomain ),
					array( 'RPI_Contribute_Posts', 'metabox' ),
					'post',
					'side',
					'default'
				);
				add_meta_box(
					'contribute_metabox_description',
					__( 'Kurzbeschreibung', RPI_Contribute::$textdomain ),
					array( 'RPI_Contribute_Posts', 'kurzbeschreibung' ),
					'post',
					'normal',
					'default'
				);

			}
		}
	}


	static public function save_metaboxes( $post_id ) {

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		$chk = isset( $_POST['mpc_check'] );

		if ( isset( $_POST['kurzbeschreibung'] ) ) {
			update_post_meta($post_id, 'kurzbeschreibung', $_POST['kurzbeschreibung']);
        }

		$servercheck = RPI_Contribute_API::remote_say_hello();
		if ( $servercheck->answer == 'Connected') {
			$usercheck = RPI_Contribute_API::check_user();
			if ( $usercheck == true  && $chk ) {
                $post = get_post( $post_id);
                $data = array(
	                'url' =>  parse_url(network_site_url( ), PHP_URL_HOST),
	                'user' => get_current_user_id(),
	                'material_user' => get_user_meta( get_current_user_id(), 'author', true  ),
	                'material_url' => get_permalink( $post_id),
	                'material_title' => $post->post_title,
	                'material_shortdescription' =>  $_POST['kurzbeschreibung'] ,
	                'material_description' =>  get_the_excerpt( $post_id ) ,
	                'material_interim_keywords' => implode( ', ', wp_get_post_tags( $post_id,  array( 'fields' => 'names' ) ) ),
                    'material_bildungsstufe' => serialize( $_POST['bildungsstufe'] ) ,
                    'material_altersstufe' => serialize( $_POST['altersstufe'] ) ,
                );
				$save = RPI_Contribute_API::send_post( $data );
			}
		}
	}

	static public function metabox() {

		global $post;

		$altersstufen_user = get_user_meta( get_current_user_id(), 'author_altersstufen', true );
		$bildungsstufen_user = get_user_meta( get_current_user_id(), 'author_bildungsstufen', true );

		echo "<h2>Bildungsstufe</h2>";
		?>
        <input type="hidden" name="bildungsstufe">
		<?php
		$bildungsstufen = RPI_Contribute_API::list_bildungsstufen();
		foreach ( $bildungsstufen as $stufe ) {
			if ( $stufe->parent == 0 ) {
				echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='bildungsstufe[]'";
				if ( in_array( $stufe->name, $bildungsstufen_user ) ) echo " checked ";
				echo "value='". $stufe->name . "'>". $stufe->name . "<br>";
				foreach ( $bildungsstufen as $stufe2 ) {
					if ( $stufe2->parent == $stufe->term_id ) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='bildungsstufe[]' ";
						if ( in_array( $stufe2->name, $bildungsstufen_user ) ) echo " checked ";
						echo "value='". $stufe2->name . "' >". $stufe2->name . "<br>";
					}
				}
			}
		}
		echo "<h2>Altersstufe</h2>";
		?>
		<input type="hidden" name="altersstufe">
        <?php
		$altersstufen = RPI_Contribute_API::list_altersstufen();
		foreach ( $altersstufen as $alter ) {
			if ( $alter->parent == 0 ) {
				echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='altersstufe[]' ";
				if ( in_array( $alter->name, $altersstufen_user ) ) echo " checked ";
				echo "value='". $alter->name . "' >". $alter->name . "<br>";
			}
		}
		$values = get_post_custom( $post->ID );
		$check = isset( $values['mpc_check'] ) ? esc_attr( $values['mpc_check'] ) : '';

		?>
		<br><br>
		<input type="checkbox" id="mpc_check" name="mpc_check" <?php checked( $check, 'on' ); ?> />
		<label for="mpc_check">Beitrag an Materialpool übermitteln</label>

<?php
	}


	static public function kurzbeschreibung() {
	    global $post;

	    $text = get_post_meta( $post->ID, 'kurzbeschreibung', true );

	    ?>
        <textarea name="kurzbeschreibung" id="kurzbeschreibung" style="width: 100%;" rows="1" cols="80"  class=""><?php echo $text; ?></textarea>
        Die Kurzbeschreibung wird als kurzbeschreibung an den Materialpool übermittelt. Der Auszug wird als Beschreibung an den Materialpool übermittelt.
        <?php
    }
}