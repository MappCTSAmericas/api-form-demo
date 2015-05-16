# API Form Send Message Demo

## Introduction

This paper documents the test sending of a single email to an arbitrary
email address using the Teradata DMC ReST API using personalization
information from a PHP webform. Please refer to the provided source code
for specific implementation details.

While this implementation is specific to a PHP webform, the appropriate API calls and responses have been documented here to aid in translation to the language of your choosing.

To test this demo on your own site, extract the source code to a folder on your webhost and update the configuration parameters in `inc/config.php[30-39]` to reflect the actual values for your DMC instance.

## Part 0: DMC Users and Group Setup

Set up a test group in your DMC instance containing the messages you'd like to test. Then create a new API user as a manager for the test group, and a new recipient user to act as a surrogate recipient profile to provide a user ID to facilitate message sending.

The surrogate member's information is not important, as it will be updated for every send with values from the form.

## Part 1: Message Template Setup

Because the API only returns personalizations that are coded as profile attributes (`<%${user[‘FirstName’]}%>` or `<%${user.CustomAttribute[‘TierCode’]}%>`), all profile attribute placeholders were replaced in the test message templates with a conditional statement containing both attribute and parameter values (`<%parameter.FirstName%>` or `<%parameter.TierCode%>`).

```
/*
 * "If parameter.FirstName is null, check user.FirstName; if
 * user.FirstName is null, use a default value, otherwise, use
 * user.FirstName. Otherwise, use parameter.FirstName."
 */
<%InsertIf expression="${( (parameter.FirstName == null) )}"%>
    <%InsertIf expression="${( (user.FirstName == null) )}"%>
        First
    <%/InsertIf%>
    <%InsertElse%>
        <%${user['FirstName']}%>
    <%/InsertElse%>
<%/InsertIf%>
<%InsertElse%>
    <%parameter.FirstName%>
<%/InsertElse%>
```

## Part 2: Constructing the webform

An initial API call is made on page load to fetch a list of prepared messages from the test group.

```
# Request (GET)
https://sslc.teradatadmc.com/[dmc\_instance]/api/rest/v2/group/getPreparedMessages?groupId=[test\_group\_id]

# Response
[ ... message ids from your test group ... ]
```

The form script then iterates through the returned array to get personalizations used in the returned messages and construct a form for each message’s personalizations:

```
# Request (GET)
https://sslc.teradatadmc.com/[dmc\_instance]/api/rest/v2/message/getUsedPersonalizations?messageId=[message\_id]

# Response
["user.CustomAttribute.TierCode","user.LastName","user.CustomAttribute.LyltyMbrNum","user.FirstName","user.CustomAttribute.Points"]
```

During form construction, each returned attribute is stripped of its prefixes to be used as personalization tokens. The following example is written in PHP and will vary from language to language:

```
foreach ( json\_decode($field\_json) as $f ) {
    // break the attributes into array items separated by '.'
    $raw = explode( '.', $f );

    // use the last array item as the field name
    $name = $raw[( count( $raw ) - 1 )];

    // refer to index.php for the remainder of the form field code.
}
```

Two hidden fields are provided to provide a list of used personalizations and message IDs to the form handling script (this also keeps the API from making extra calls):

```
<input id="form_messageId" type="hidden" name="messageId" value="[message_id]" />

<input id="form_fields" type="hidden" name="fields" value='["user.CustomAttribute.TierCode","user.LastName","user.CustomAttribute.LyltyMbrNum","user.FirstName","user.CustomAttribute.Points"]' />
```

Additionally, a final hard-coded field is provided to set user.CustomAttribute[’AlternateEmail’] to our recipient's address:

```
<input id="form_AlternateEmail" type="text" name="AlternateEmail" value="" />
```

## Part 3: Form Handling

When the form is filled in and submitted, the handler instantiates itself and uses the hidden `'fields'` field to determine which fields to look for, then iterates through `$\_POST` fields to gather personalization data from the form submission. (see handler.php[69-90]).

The handler then updates the surrogate profile with the value provided by the ’AlternateEmail’ form field (see handler.php[92], api.php[89]).

```
# Request (POST)
https://sslc.teradatadmc.com/[dmc\_instance]/api/rest/v2/user/updateProfile?userId=[surrogate\_id]

# Message Body
[{"name":"user.CustomAttribute.AlternateEmail","value":"nick.silva@teradata.com"}]
```

The Custom Attribute ’AlternateEmail’ is used for email sends from everywhere in the custom DMC instance, so the updated value will be used as the sendto address of the message. Since it’s only a stand-in for a real profile, there is no existing profile information, and nothing else is stored in the DMC data warehouse.

Once the surrogate profile is successfully updated with the correct delivery address, the call is made to trigger a single message using the personalization values in the form (see handler.php[105], api.php[161]).

```
# Request (POST)
https://sslc.teradatadmc.com/[dmc\_instance]/api/rest/v2/message/sendSingle?messageId=[message\_id]&recipientId=[surrogate\_id]

# Message Body
{"parameters":[{"name":"TierCode","value":"TestTier"},{"name":"LastName","value":"TestLastName"},{"name":"LyltyMbrNum","value":"TestLyltyMbrNum"},{"name":"FirstName","value":"TestFirstName"},{"name":"Points","value":"TestPoints"}]}
```

## Part 4: _Caveat Emptor_

While the solution documented here accomplishes the task of sending single messages to users on the fly, it has been noted that this is at the cost of less accurate reporting statistics, as only one user is being used for all sends. It is therefore recommended that this solution only be used as a guide for demonstration and educational purposes, and not be implemented in a production environment where accurate reporting matters.

Contact your Teradata Implementation & Architecture professional for more details.
