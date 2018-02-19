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

		if ( isset( $_POST['medientypen'] ) ) {
			update_post_meta($post_id, 'medientypen', $_POST['medientypen']);
        }
		if ( isset( $_POST['kurzbeschreibung'] ) ) {
			update_post_meta($post_id, 'kurzbeschreibung', $_POST['kurzbeschreibung']);
		}
		$prefix = '';
		if ( is_multisite() ) {
			global $wpdb;
			$prefix = '_'.$wpdb->blogid;
		}

        $p = get_post( $post_id);
		if ( $p->post_status != 'publish' ) return;
		$servercheck = RPI_Contribute_API::remote_say_hello();
		if ( $servercheck->answer == 'Connected') {
		    $hash = get_user_meta( get_current_user_id(), 'author' . $prefix, true  );
			$usercheck = RPI_Contribute_API::check_user();
			$image = '';
			if ( has_post_thumbnail() ) {
				$image = get_the_post_thumbnail_url( $post_id, 'full' );
            }

			if ( $usercheck == true  && $chk && $hash != '' ) {
                $post = get_post( $post_id);
                $data = array(
	                'url' =>  parse_url(network_site_url( ), PHP_URL_HOST),
	                'user' => get_current_user_id(),
	                'material_user' => $hash,
	                'material_url' => get_permalink( $post_id),
	                'material_title' => $post->post_title,
	                'material_shortdescription' =>  $_POST['kurzbeschreibung'] ,
	                'material_description' =>  get_the_excerpt( $post_id ) ,
	                'material_interim_keywords' => implode( ', ', wp_get_post_tags( $post_id,  array( 'fields' => 'names' ) ) ),
                    'material_bildungsstufe' => serialize( $_POST['bildungsstufe'] ) ,
                    'material_altersstufe' => serialize( $_POST['altersstufe'] ) ,
	                'material_screenshot' => $image ,
                    'material_medientyp' => serialize( $_POST['medientypen'] ) ,
                );
				$save = RPI_Contribute_API::send_post( $data );
			}
		}
	}

	static public function metabox() {

		global $post;
		$prefix = '';
		if ( is_multisite() ) {
			global $wpdb;
			$prefix = '_'.$wpdb->blogid;
		}
		$altersstufen_user = get_user_meta( get_current_user_id(), 'author_altersstufen' . $prefix, true );
		$bildungsstufen_user = get_user_meta( get_current_user_id(), 'author_bildungsstufen' . $prefix, true );

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


        echo "<h2>Medientypen</h2>";
		$medientypen_user = get_post_meta($post->ID, 'medientypen', true );
        ?>
        <input type="hidden" name="medientype">
		<?php
		$medientypen = RPI_Contribute_API::list_medientypen();
		foreach ( $medientypen as $medientyp ) {
			if ( $medientyp->parent == 0 ) {
				echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='medientypen[]' ";
				if ( is_array( $medientypen_user ) && in_array( $medientyp->name, $medientypen_user ) ) echo " checked ";
				echo "value='". $medientyp->name . "' >". $medientyp->name . "<br>";

				foreach ( $medientypen as $medientyp2 ) {
					if ( $medientyp2->parent == $medientyp->term_id ) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='medientypen[]' ";
						if ( is_array( $medientypen_user ) && in_array( $medientyp2->name, $medientypen_user ) ) echo " checked ";
						echo "value='". $medientyp2->name . "' >". $medientyp2->name . "<br>";
					}
				}

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
