<?php

/**
 * Teradata provides programmatic access to our email marketing tool, Digital
 * Messaging Center via ReST.
 *
 * @package API-Form-Demo
 * @author Nick Silva <nick.silva@teradata.com>
 * @copyright Copyright (c) 2015, Teradata Interactive
 * @link http://marketing.teradata.com/DMC/API-Home/
 * @version v0.0.1
 * @filesource
 */

/**
 * Provides basic access to DMC REST API functions.
 *
 * To use this client to access your instance of Teradata's Digital Messaging
 * Center, include it in your script then instantiate it by providing your
 * REST URL and login credentials.
 *
 * Similarly, if you'd like to use this without providing your credentials for
 * each instance, you can update the class constructor to reflect your login
 * information.
 *
 * @author Nick Silva <nick.silva@teradata.com>
 * @since v0.0.1
 *
 */
class API {

    private $rest_url;
    private $rest_login;
    private $return;
    private $start_time;

    /**
     * Constructs class object and sets up options.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function __construct(
        $url,
        $user,
        $password,
        $benchmark = true
    ) {
        $this->rest_url = $url;
        $this->rest_login = $user.":".$password;
        $benchmark = $benchmark;
        if ( $benchmark ) {
            $this->start_time = microtime( true );
        }
    }


    /**
     * If class is instantiated with $benchmark = true, displays seconds
     * taken to complete class actions on script completion.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function __destruct() {
        if ( isset( $this->start_time ) ) {
            $seconds = microtime( true ) - $this->start_time;
            echo "<p>Action completed in $seconds seconds.</p>";
        }
    }


    /**
     * Returns all of the prepared messages for a specific group.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function get_prepared_messages( $group_id ) {
        return $this->get( "group/getPreparedMessages?groupId=$group_id" );
    }

    /**
     * Updates the user's profile by user ID.
     *
     * Update a user's profile with the information saved in the
     * attribute list.
     *
     * This method only changes the information that is explicitly mentioned.
     * Attributes that are not mentioned are not changed.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function update_profile( $user_id, $attributes = null ) {
        $postdata = $attributes === null ? '[]' : $this->build_attributes_object( $attributes );

        return $this->post( 'user/updateProfile?userId=' . $user_id, $postdata );
    }


    /**
     * Returns message personalizations used in the given message.
     *
     * Returns all the personalization references within the message given by
     * the message ID.
     *
     * If message ID is not valid, returns false.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function get_message_personalizations( $message_id ) {
        return $this->get( "message/getUsedPersonalizations?messageId=$message_id" );

    }


    /**
     * Sends a single prepared message..
     *
     * Sends a prepared message as a single message to a specific user.
     * Prepared messages are saved copies of a message to which a group has
     * been assigned. They are used to automatically send messages to single
     * users (e.g. for birthday messages). Because sendouts in DMC are
     * always managed via a group, this method automatically adds the user
     * who receives the email to the group of the prepared message. Be aware
     * that there are legal issues associated with contacting users. The
     * system usually provides automated opt-in settings to satisfy these
     * requirements. However, this particular method does not include an opt-
     * in process. The method changes memberships without any additional
     * processes. You may avoid legal complications if the user is already
     * part of the group that is sending the single message (and has
     * successfully completed an opt-in process), or if the group is only
     * used for sending single messages.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function send_single_message( $message_id, $recipient_id, $parameters = null ) {
        $parameters = $parameters == null ? [] : '{"parameters":'.$this->build_attributes_object( $parameters ).'}';

        return $this->post( 'message/sendSingle?messageId='.$message_id.'&recipientId='.$recipient_id, $parameters );
    }


    // UTILITY METHODS
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Prepares attributes for upload to profile
     *
     * @param array   $array Profile attributes array
     * @return obj stdClass
     *
     * @access private
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    protected function build_attributes_object( $attributes = [ ], $json = true ) {
        $obj = [];
        $i = 0;
        foreach ( $attributes as $name => $value ) {
            $obj[$i] = new stdClass();
            $obj[$i]->name = $name;
            $obj[$i]->value = $value;
            $i++;
        }

        return $json === true ? json_encode( $obj ) : $obj;
    }


    // INTERNAL METHODS
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Performs cURL GET request to DMC ReST API server on specified path.
     *
     * @param string  $request_path Domain and path for cURL request
     * @return string|obj cURL response
     *
     * @uses DMC_ReSTClient::call()
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    private function get( $request_path ) {
        return $this->call( $request_path, 'GET' );
    }


    /**
     * Performs cURL POST request to DMC ReST API server on specified path.
     *
     * @param string  $request_path Domain and path for cURL request
     * @param string  $postfields   Domain and path for cURL request
     * @param string  $request_body Domain and path for cURL request
     * @return string|obj cURL response
     *
     * @uses DMC_ReSTClient::call()
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    private function post( $request_path, $request_body = [ ] ) {
        return $this->call( $request_path, 'POST', [ CURLOPT_POSTFIELDS => $request_body ] );
    }


    /**
     * Performs cURL request to DMC ReST API server on specified path.
     *
     * @param string  $request_path Domain and path for cURL request
     * @param string  $method       HTTP method to use (GET, POST, DELETE)
     * @param array|bool $curlopts     Additional cURL parameters to set
     * @param string  $request_body Data to send request body (default: false)
     * @return string|obj cURL response
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    private function call( $request_path, $method, $curlopts = false ) {
        $ch = curl_init();
        $opt = [
            CURLOPT_URL => $this->rest_url . $request_path,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->rest_login,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ]
        ];

        curl_setopt_array( $ch, $opt );

        if ( is_array( $curlopts ) ) {
            curl_setopt_array( $ch, $curlopts );
        }


        $response = curl_exec( $ch );
        curl_close( $ch );

        return $this->parse_response( $response );
    }

    /**
     * Filters JSON response for errors.
     *
     * @param string  $response JSON object.
     * @return json|str JSON response or error string.
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    private function parse_response( $response ) {
        $json_errors = array(
            JSON_ERROR_NONE => 'No error has occurred',
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
        );
        return json_last_error() ? 'JSON error: ' . $json_errors[json_last_error()] : $return;
    }


}


// End of class: API
// End of file: api.php
