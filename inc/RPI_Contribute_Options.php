<?php

/**
 * Class RPI_Contribute_Options
 *
 * Contains some helper code for plugin options
 *
 */

class RPI_Contribute_Options {


	/**
	 * Register all settings
	 *
	 * Register all the settings, the plugin uses.
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  void
	 */
	static public function register_settings() {
	}

	/**
	 * save all network settings
	 *
	 * Register all the settings, the plugin uses.
	 *
	 * @since   0.1.0
	 * @access  public
	 * @static
	 * @return  void
	 * @useaction  admin_post_rw_remote_auth_client_network_settings
	 */
	static public function network_settings() {
	}

	/**
	 * Add a settings link to the  pluginlist
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @param   string array links under the pluginlist
	 * @return  array
	 */
	static public function plugin_settings_link( $links ) {
		if(is_multisite()){
			$settings_link = '<a href="network/settings.php?page=' . RPI_Contribute::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
			if(is_super_admin()){
				array_unshift($links, $settings_link);
			}
		}else{
			$settings_link = '<a href="options-general.php?page=' . RPI_Contribute::$plugin_base_name . '">' . __( 'Settings' )  . '</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	/**
	 * Get the API Endpoint
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  string
	 */
	static public function get_endpoint() {
	    return "http://material.dev/mp_contribute/";
    }

	/**
	 * Generate the options menu page
	 *
	 * Generate the options page under the options menu
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 * @return  void
	 */
	static public function options_menu() {
		if ( is_multisite() ) {
			add_submenu_page(
				'settings.php',
				'MP Contribute',
				__('MP Contribute', RPI_Contribute::$textdomain ),
				'manage_network_options',
				RPI_Contribute::$plugin_base_name,
				array( 'RPI_Contribute_Options','create_options')
			);

		} else {
			add_options_page(
				'MP Contribute',
				__('MP Contribute', RPI_Contribute::$textdomain ),
				'manage_options',
				RPI_Contribute::$plugin_base_name,
				array( 'RPI_Contribute_Options' , 'create_options' )
			);
		}
	}


	/**
	 * Generate the options page for the plugin
	 *
	 * @since   0.1
	 * @access  public
	 * @static
	 *
	 * @return  void
	 */
	static public function create_options() {

		$servercheck = RPI_Contribute_API::remote_say_hello();

		?>
		<div class="wrap"  id="rpicontributeoptions">
			<h2><?php _e( 'rpi-virtuell Materialpool contribute', RPI_Contribute::$textdomain ); ?></h2>

            <?php
                if ( $servercheck->notice == 'warning' &&  $servercheck->answer == 'Connected' ) {
	            ?>
                    <button style="background-color: green"><?php _e( 'Cooperation approved', RPI_Contribute::$textdomain ); ?></button>
	                <?php _e( 'Your cooperation is approved. Please go to the user profile and make the settings there.', RPI_Contribute::$textdomain ); ?>
	            <?php

            }
             elseif ( $servercheck->notice == 'error') {
	            ?>
                <button style="background-color: yellow"><?php _e( 'No connection', RPI_Contribute::$textdomain ); ?></button>
	            <?php echo $servercheck->answer; ?>
	            <?php
            }
            elseif ( $servercheck->answer != 'Connected') {
	            ?>
                <button style="background-color: red"><?php _e( 'No connection', RPI_Contribute::$textdomain ); ?></button>
	            <?php _e( 'Please check connection to the materialpool.', RPI_Contribute::$textdomain ); ?>
	            <?php
            }

            ?>
		</div>
		<?php
	}

    static public function edit_user_profile( $profiluser ) {

	    $servercheck = RPI_Contribute_API::remote_say_hello();
	    if ( $servercheck->answer == 'Connected') {
            $autors_selected = get_user_meta( $profiluser->data->ID, 'author', true );
            ?>
            <h2><?php _e( 'Contribution defaults', RPI_Contribute::$textdomain ); ?></h2>
            <table class="form-table">
                <tr id="author" >
                    <th><?php _e( 'Author',RPI_Contribute::$textdomain ); ?></th>
                    <td>
                        <select name="author" id="author">
                            <option value=""><?php _e( 'none', RPI_Contribute::$textdomain ); ?></option>
                            <?php

                            $authors = RPI_Contribute_API::list_authors();
                            foreach ($authors as $author ) {
                                $selected = '';
                                if ( $author->id == $autors_selected ) {
                                    $selected = ' selected="selected" ';
                                }

                                ?>
                                <option value="<?php echo $author->id; ?>" <?php echo $selected; ?>><?php echo htmlentities2( $author->name); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr id="bildungsstufen">
                    <th><?php _e( 'Bildungsstufen', RPI_Contribute::$textdomain ); ?></th>
                    <td>
                        <?php
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
                        ?>
                    </td>
                </tr>
                <tr id="alterssstufen">
                    <th><?php _e( 'Altersstufen', RPI_Contribute::$textdomain ); ?></th>
                    <td>
			            <?php
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
			            ?>
                    </td>
                </tr>
            </table>
            <?php
	    }
    }


	/**
	 * save profile fields
	 *
	 * @param $user_id
	 * @return bool
	 */
	static public function save_profile_fields( $user_id ) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        if (empty($_POST['author'])) {
            delete_user_meta($user_id, 'author');
        }
        update_user_meta($user_id, 'author', $_POST['author']);
	}

}