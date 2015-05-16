<?php
include_once 'inc/api.php';
include_once 'inc/config.php';

$config = new API_Config;

// API methods instantiated using hard-coded credentials.
// Refer to ./inc/api.php to see it in action.
$dmc = new API(
    $config->rest_url,
    $config->api_user,
    $config->api_pass,
    $config->benchmarking,
);

// Get personalizations for prepared messages from test group
foreach ( json_decode( $dmc->get_prepared_messages( $config->form_group_id ) ) as $m_id ) {
    $field_json = $dmc->get_message_personalizations( $m_id );

// begin form construction
?>
<form method="post" action="./inc/handler.php">
    <div>
        <label for="messageId">Message ID: <?php echo $m_id; ?></label><br />
        <input id="form_messageId" type="hidden" name="messageId" value="<?php echo $m_id; ?>" />
        <input id="form_fields" type="hidden" name="fields" value='<?php echo $field_json; ?>' />
    </div><?php
// build a form field for each message personalization
    foreach ( json_decode($field_json) as $f ) {
        $raw = explode( '.', $f );
        $name = $raw[( count( $raw ) - 1 )];
?>
    <div>
        <label for="<?php echo $name; ?>"><?php echo $f; ?></label><br />
        <input id="form_<?php echo $name; ?>" type="text" name="<?php echo $name; ?>" value="" />
    </div><?php
     }
// AlternateEmail field is used to set user.CustomAttribute['AlternateEmail']
?>
    <div>
        <label for="AlternateEmail">Send email to</label><br />
        <input id="form_AlternateEmail" type="text" name="AlternateEmail" value="" />
    </div>
    <div>
        <button type="submit" class="call_to_action">SEND THIS MESSAGE</button>
    </div>
</form>
<hr />
<?php }
