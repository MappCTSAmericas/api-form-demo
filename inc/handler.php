<?php

/**
 * A demonstration form handler using the PHP REST Client class for DMC
 * API integration. Update the values in inc/config.php for your
 * specific instance.
 *
 * @author Nick Silva <nick.silva@teradata.com>
 * @copyright Copyright (c) 2014, Teradata Interactive
 * @link http://marketing.teradata.com/DMC/API-Home/
 * @version v1.0.0
 * @filesource
 */

if ( !class_exists( 'API_Config' ) )
    include_once './config.php';

if ( !class_exists( 'API' ) )
    require_once './api.php';

class Handler extends API {

    /**
     * @var int Surrogate user profile ID.
     * @access private
     */
    private $surrogate = array();


    /**
     * @var array Form input.
     * @access private
     */
    private $input = array();


    /**
     * @var int Message ID.
     * @access private
     */
    private $message;


    /**
     * @var string Email destination address.
     * @access private
     */
    private $sendto;


    /**
     * Constructs the handler object, initiates form processing.
     *
     * @param array   $input Form $_POST input
     * @param int     $surrogate_id Surrogate member for message sending
     * @return void
     *
     * @access public
     * @since v1.0.0
     *
     * @uses DMC_SoapClient::__construct()
     * @uses Handler::handle_form()
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function __construct() {
        $config = new API_Config;

        parent::__construct(
            $config->rest_url,
            $config->api_user,
            $config->api_pass,
            $config->benchmarking,
        );
        $this->surrogate = $config->surrogate_member_id;
        $this->message = $_POST['messageId'];
        $this->sendto = $_POST['AlternateEmail'];

        $form = [];
        foreach ( json_decode( $_POST['fields'] ) as $f ) {
            $raw = explode( '.', $f );
            $form[] = $raw[( count( $raw ) - 1 )];
        }

        foreach ( $form as $f ) {
            $this->input[$f] = $_POST[$f];
        }

        $this->handle_form();
    }

    /**
     * Updates surrogate profile with correct AlternateEmail value, then
     * sends specified message.
     *
     * @return void
     *
     * @access private
     * @since v1.0.0
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    private function handle_form() {
        $this->update_profile( $this->surrogate, ['user.CustomAttribute.AlternateEmail' => $this->sendto] );
        $this->send_single_message( $this->message, $this->surrogate, $this->input );
    }


}


// HERE BE DRAGONS
new Handler();
