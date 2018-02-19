<?php

class RPI_Contribute_API {



	/**
	 * @use wp_remote_get
	 * @param $json
	 * @return stdClass (response data) || WP_Error
	 */

	static function remote_get( $json ){

		$error = false;

		if(!is_string($json)){
			try{
				$json = rawurlencode( json_encode( $json) );
			}catch(Exception $e){
				$error = __('Error: Server can not encode server response.', RPI_Contribute::get_textdomain());
			}
		}

		//validat the answer
		$response = wp_remote_get(RPI_Contribute_Options::get_endpoint() . ( $json ) );
 		if ( !is_wp_error( $response ) ) {
			if(
				isset($response['headers']["content-type"]) && strpos($response['headers']["content-type"],'application/json') !==false )
			{
				try {
					$json = json_decode($response['body']);
					if (is_a($json, 'stdClass') && isset($json->errors) && $json->errors ) {
						$sever_error = $json->errors;
						if(is_a($sever_error,'stdClass')){
							$error = $sever_error->message;
							$data = $sever_error->data;
							if($data->mp_contribute_key){
								// remote auth service suspends client and sends a new api-key
								// save the new api-key in the options
								update_site_option('mp_contribute_key',$data->mp_contribute_key);
							}
						}else{
							$error  = $sever_error;
						}

					}else{
						return $json;
					}

				} catch ( Exception $ex ) {
					$error = __('Error: Can not decode response.', RPI_Contribute::get_textdomain());
				}
			}else{
				$error =  __('Error. Wrong Content Type. Check the API Server Endpoint', RPI_Contribute::get_textdomain()) ;
			}

		}else{
			$error =  __('Error. Check the API Server Endpoint URL in the Settingspage', RPI_Contribute::get_textdomain()) ;
		}

		return $response;
	}

	/**
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * sets special user_agent infos for connecting with remote auth server
	 * deals with wp_remote_get
	 *
	 * @user_agend_args: (seperated by ; )
	 *
	 *         Clientinfos  ( Class Version )
	 *         api_key      ( option )
	 *         domain       ( setted domain of multisite or songlesite )
	 *         IP           ( of the hosting server )
	 *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * @param $args
	 * @param $url
	 * @return mixed
	 *
	 * @usefilter http_request_args
	 */
	static function set_http_request_args($args, $url){

		if( strpos( urldecode($url),RPI_Contribute_Options::get_endpoint() ) !== false ){

			$domain = parse_url(network_site_url( ), PHP_URL_HOST);
			$key = get_site_option('mp_contribute_key');

			//modify user-agent with @user_agend_args
			$args['user-agent']=  'RPI_MP_Contribute '.RPI_Contribute::$version	.';' . $domain .';'. $_SERVER['SERVER_ADDR'] .';'. $key;

			$args['sslverify']=  false;

		}


		return $args;
	}

	/**
	 *  Test the server connection
	 *
	 * @return stdClass( $notice, $answer )
	 *
	 */
	public static function remote_say_hello(  ) {

		$request = array(   'cmd' => 'say_hello',
		                    'data' => array (
			                    'question' => 'Can you here me'
		                    )
		);
		$response = self::remote_get( $request );
		if(is_wp_error($response) || !isset($response->data) || $response->data === false ){

			$data = new stdClass();
			$data->notice = 'error';

			if(!isset($response->data) && ! is_wp_error($response) ){     //unknown error

				$data->notice = 'error';
				$data->answer = 'Serveresponse: '.json_encode($response);

			}elseif(isset($response->data) && $response->data === false) { //whitelisting is not active

				$data->answer = 'it works';
				$data->notice = 'info';

			}else{
				$data->answer = $response->get_error_message();
				$data->data =   $response->get_error_data();
			}


			return $data;

		}
		return  $response->data;
	}

	/**
	 * @return mixed
	 */
	public static function list_authors() {
		$request = array(   'cmd' => 'list_authors' ,
			'data' => array() );

		$response = self::remote_get( $request );
		return  $response->data->answer;
	}

	/**
	 * @return mixed
	 */
	public static function list_bildungsstufen() {
		$request = array(   'cmd' => 'list_bildungsstufen' ,
		                    'data' => array() );

		$response = self::remote_get( $request );
		return  $response->data->answer;
	}

	/**
	 * @return mixed
	 */
	public static function list_altersstufen() {
		$request = array(   'cmd' => 'list_altersstufen' ,
		                    'data' => array() );

		$response = self::remote_get( $request );
		return  $response->data->answer;
	}

	/**
	 * @return mixed
	 */
	public static function list_medientypen() {
		$request = array(   'cmd' => 'list_medientypen' ,
		                    'data' => array() );

		$response = self::remote_get( $request );
		return  $response->data->answer;
	}


	/**
	 * @return mixed
	 */
	public static function add_user( $user_id, $remote_user ) {
		$user_info = get_userdata($user_id);
		$request = array(   'cmd' => 'add_user' ,
		                    'data' => array(
		                    	'url' =>  parse_url(network_site_url( ), PHP_URL_HOST),
			                    'user' => $user_id,
			                    'material_user' => $remote_user,
			                    'name' =>  $user_info->user_login,
		                    ) );
		$json = urlencode( json_encode( $request ) );

		$response = self::remote_get( $request );


		return  $response->data->answer;
	}

	/**
	 * @return mixed
	 */
	public static function check_user() {
		$user = get_user_meta( get_current_user_id(), 'author', true  );
		if ($user != '') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return mixed
	 */
	public static function send_post( $data ) {

		$request = array(   'cmd' => 'send_post' ,
		                    'data' => array(
		                    	'url' => $data[ 'url' ],
			                    'user' => $data[ 'user' ],
			                    'material_url' => urlencode( $data[ 'material_url' ] ),
			                    'material_user' => base64_encode( $data[ 'material_user' ] ),
			                    'material_title' => urlencode( $data[ 'material_title' ] ),
			                    'material_shortdescription' => base64_encode( $data[ 'material_shortdescription' ] ),
			                    'material_description' => base64_encode( $data[ 'material_description' ] ),
								'material_interim_keywords' => urlencode($data[ 'material_interim_keywords' ] ),
			                    'material_altersstufe' => base64_encode( $data[ 'material_altersstufe' ] ),
			                    'material_bildungsstufe' => base64_encode( $data[ 'material_bildungsstufe' ] ),
			                    'material_screenshot' => base64_encode( $data[ 'material_screenshot' ] ),
			                    'material_medientyp' => base64_encode( $data[ 'material_medientyp' ] ),
		                    )
		);

		$json = urlencode( json_encode( $request ) );

		$response = self::remote_get( $json );

		return  $response->data->answer;
	}


}
