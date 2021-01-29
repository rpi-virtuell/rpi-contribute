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
			    // Metabox nur wenn Beitrag Publiziert ist
                global $post;
                //if ( $post->post_status == 'publish' ) {
	                /*
                    add_meta_box(
		                'contribute_metabox_description',
		                __( 'Kurzbeschreibung', RPI_Contribute::$textdomain ),
		                array( 'RPI_Contribute_Posts', 'kurzbeschreibung' ),
		                'post',
		                'normal',
		                'default'
	                );
	                */
                    add_meta_box(
                        'contribute_metabox',
                        __( 'Materialpool zuliefern', RPI_Contribute::$textdomain ),
                        array( 'RPI_Contribute_Posts', 'metabox' ),
                        'post',
                        'side',
                        'default'
                    );
                //}


			}
		}
	}

	static public function save_metaboxes( $post_id ) {


	    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		$material_id = get_post_meta($post_id, 'materialpool_id', true );


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

		$chk = isset( $_POST['mpc_check'] );
		if(!$chk){
			delete_post_meta($post_id, 'materialpool_sync');
			return;
		}else{
			update_post_meta($post_id, 'materialpool_sync', 'checked');
        }

		$post = get_post( $post_id);
		if ( $post->post_status != 'publish' ) return;

		$content= force_balance_tags(
		        html_entity_decode( wp_trim_words( htmlentities(
		                get_the_content(null,false,$post_id)
               ), 80, '...' ) )
        );

		//var_dump($content);die();

		$servercheck = RPI_Contribute_API::remote_say_hello();


		if ( $servercheck->answer == 'Connected') {
		    $hash = get_user_meta( get_current_user_id(), 'author' . $prefix, true  );
			$usercheck = RPI_Contribute_API::check_user();
			$image = '';
			if ( has_post_thumbnail() ) {
				$image = get_the_post_thumbnail_url( $post_id, 'full' );
            }

			if ( $usercheck == true  && $hash != '' && $_POST['kurzbeschreibung'] != '' ) {


                $data = array(
	                'url' =>  parse_url(network_site_url( ), PHP_URL_HOST),
	                'user' => get_current_user_id(),
	                'material_id' => $material_id,
	                'material_user' => $hash,
	                'material_url' => get_permalink( $post_id),
	                'material_title' => $post->post_title,
	                'material_shortdescription' =>  $_POST['kurzbeschreibung'] ,
	                //'material_description' =>  json_encode(wp_trim_excerpt("", $post_id )),
	                'material_description' =>  json_encode($content, JSON_HEX_TAG ),
	                'material_interim_keywords' => implode( ', ', wp_get_post_tags( $post_id,  array( 'fields' => 'names' ) ) ),
                    'material_bildungsstufe' => json_encode( $_POST['bildungsstufe'] ) ,
                    'material_altersstufe' => json_encode($_POST['altersstufe'] ) ,
	                'material_screenshot' => $image ,
                    'material_medientyp' => json_encode( $_POST['medientypen'] ) ,
                );
                $save = RPI_Contribute_API::send_post( $data );


				if  ($save->status  ) {
	                update_post_meta($post_id, 'material_send', time() );
	                update_post_meta($post_id, 'materialpool_url', $save->url );
	                update_post_meta($post_id, 'materialpool_id', $save->id );
                }



			}
		}
	}

	static public function metabox() {

		global $post;

		$send = get_post_meta($post->ID, 'material_send', true );

		$material_id = get_post_meta( $post->ID, 'materialpool_id', true );

		$checked = (string) get_post_meta( $post->ID, 'materialpool_sync', true );



		$prefix = '';
		if ( is_multisite() ) {
			global $wpdb;
			$prefix = '_' . $wpdb->blogid;
		}

		echo '<div>';
		echo '<input name="mpc_check" type="checkbox" id="mpc_check" '.$checked.' value="checked">';
		echo '<label for="mpc_check"><b>An den Materialpool senden</b></label>';
		echo '</div>';


		if ( intval($material_id) < 1 ) {

		    $altersstufen_user   = get_user_meta( get_current_user_id(), 'author_altersstufen' . $prefix, true );
            $bildungsstufen_user = get_user_meta( get_current_user_id(), 'author_bildungsstufen' . $prefix, true );

			echo '<p>Es müssen Textauszug und Kurzbeschreibung müssen ausgefüllt sein.</p>';


            echo '<hr><div class="postbox-header">Kurzbeschreibung</div>';

            echo self::kurzbeschreibung();


			echo "<hr><div class=\"postbox-header\">Medientypen</div>";
			$medientypen_user = get_post_meta( $post->ID, 'medientypen', true );


			?>
            <input type="hidden" name="medientype">
			<?php
			$medientypen = RPI_Contribute_API::list_medientypen();
			foreach ( $medientypen as $medientyp ) {
				if ( $medientyp->parent == 0 ) {
					echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='medientypen[]' ";
					if ( is_array( $medientypen_user ) && in_array( $medientyp->name, $medientypen_user ) ) {
						echo " checked ";
					}
					echo "value='" . $medientyp->name . "' >" . $medientyp->name . "<br>";

					foreach ( $medientypen as $medientyp2 ) {
						if ( $medientyp2->parent == $medientyp->term_id ) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='medientypen[]' ";
							if ( is_array( $medientypen_user ) && in_array( $medientyp2->name, $medientypen_user ) ) {
								echo " checked ";
							}
							echo "value='" . $medientyp2->name . "' >" . $medientyp2->name . "<br>";
						}
					}

				}
			}


			echo "<hr><div class=\"postbox-header\">Bildungsstufe</div>";
            ?>
            <input type="hidden" name="bildungsstufe">
            <?php
            $bildungsstufen = RPI_Contribute_API::list_bildungsstufen();
            foreach ( $bildungsstufen as $stufe ) {
                if ( $stufe->parent == 0 ) {
                    echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='bildungsstufe[]'";
                    if ( in_array( $stufe->name, $bildungsstufen_user ) ) {
                        echo " checked ";
                    }
                    echo "value='" . $stufe->name . "'>" . $stufe->name . "<br>";
                    foreach ( $bildungsstufen as $stufe2 ) {
                        if ( $stufe2->parent == $stufe->term_id ) {
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='bildungsstufe[]' ";
                            if ( in_array( $stufe2->name, $bildungsstufen_user ) ) {
                                echo " checked ";
                            }
                            echo "value='" . $stufe2->name . "' >" . $stufe2->name . "<br>";
                        }
                    }
                }
            }
            echo "<hr><div class=\"postbox-header\">Altersstufe</div>";
            ?>
            <input type="hidden" name="altersstufe">
            <?php
            $altersstufen = RPI_Contribute_API::list_altersstufen();
            foreach ( $altersstufen as $alter ) {
                if ( $alter->parent == 0 ) {
                    echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='altersstufe[]' ";
                    if ( in_array( $alter->name, $altersstufen_user ) ) {
                        echo " checked ";
                    }
                    echo "value='" . $alter->name . "' >" . $alter->name . "<br>";
                }
            }
            $values = get_post_custom( $post->ID );
            $check  = isset( $values['mpc_check'] ) ? esc_attr( $values['mpc_check'] ) : '';




		} else {

			$materialpoolurl = get_post_meta( $post->ID, 'materialpool_url', true );

			echo "Beitrag wurde am ". date("d.m.Y ", $send ) . ' an den Materialpool übermittelt.';
			echo "Die URL ist: <a href='".$materialpoolurl."'>".$materialpoolurl."</a>";

		    echo '<hr><div class="postbox-header">Kurzbeschreibung</div>';

			echo self::kurzbeschreibung();

			?><input type="hidden" name="material_id" value="<?php echo $material_id;?>"><?php



        }
        ?>
        <script>
            jQuery( document ).ready( function($)
            {
                $( "#postexcerpt" ).removeClass( "hide-if-js" );
            });

        </script>
        <?php
	}


	static public function kurzbeschreibung() {
	    global $post;

	    $text = get_post_meta( $post->ID, 'kurzbeschreibung', true );

	    ?>
          <textarea name="kurzbeschreibung" id="kurzbeschreibung" style="width: 99%;border-radius: 0;" rows="2" class="components-textarea-control__input" placeholder="Interaktive Selbstlernaufgaben für die Sekundarstufe 1 "><?php echo $text; ?></textarea>

        <?php
    }
}
