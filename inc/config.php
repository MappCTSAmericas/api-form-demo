<?php

/**
 * Configuration object for API Form Demonstration. Update the values in
 * this class to reflect your own DMC instance
 *
 * @package API-Form-Demo
 * @author Nick Silva <nick.silva@teradata.com>
 * @copyright Copyright (c) 2015, Teradata Interactive
 * @link http://marketing.teradata.com/DMC/API-Home/
 * @version v0.0.1
 * @filesource
 */

class API_Config {
    public $rest_url,
    public $api_user,
    public $api_pass,
    public $form_group_id,
    public $surrogate_member_id,
    public $form_group_id,
    public $benchmarking,

    /**
     * Constructs configuration object and sets up options.
     *
     * @access public
     * @since v0.0.1
     *
     * @author Nick Silva <nick.silva@teradata.com>
     */
    public function __construct() {
        $this->rest_url = '';
        $this->api_user = '';
        $this->api_pass = '';
        $this->form_group_id = '';
        $this->surrogate_member_id = '';
        $this->form_group_id = '';
        $this->benchmarking = false;
    }

}
