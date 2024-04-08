<?php
require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/tokens.php'));
$PAGE->set_title(get_string('activatecoursetokens', 'local_enrollment_tokens'));
$PAGE->set_heading(get_string('activatecoursetokens', 'local_enrollment_tokens'));
$PAGE->requires->js_call_amd('local_enrollment_tokens/activate', 'init');

$email = '';
// If the visitor is logged in, set the email address
if (isloggedin()) {
    $email = $USER->email;
}

// Handle form post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get email
    $email = optional_param('email', '', PARAM_EMAIL);
    // get token(s)
    $tokensCodes = optional_param_array('token_code', []);

    // find which tokens are valid (voided=0, user_enrolments_id=null)
    $validTokens = [];
    foreach ($tokensCodes as $tokenCode) {
        $tokenRecord = $DB->get_record('enrollment_tokens', ['code' => $tokenCode, 'voided' => 0, 'user_enrolments_id' => null]);
        if ($tokenRecord) {
            $validTokens[] = $tokenRecord;
        }
    }

    // if no valid tokens
    if (empty($validTokens)) {
        echo get_string('errortokennotfound', 'local_enrollment_tokens');
    } else {
        // if valid tokens

        // get/create user

        // TODO
        // enroll to those courses, and update corresponding enrollment tokens
        // then redirect the user to login or if they are already logged in send to the course page, or if multiple courses, send to the dashboard
        // ...
        
        echo 'todo: enroll to those courses, and update corresponding enrollment tokens';
    }

    // exit
    exit;
}

// Start output
echo $OUTPUT->header();
echo '<p class="lead my-5">' . s(get_string('activateintroduction', 'local_enrollment_tokens')) . '</p>';

echo '<form class="lead" method="post">';
// input email
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="email">' . s(get_string('studentemailaddress', 'local_enrollment_tokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="email" class="form-control form-control-lg" id="email" name="email" value="' . s($email) . '">';
echo '  </div>';
echo '</div>';
// input token[] (the first in an array)
// it shows a minus at the right to remove the token
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="token_001">' . s(get_string('tokens', 'local_enrollment_tokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <div id="tokens">';
echo '      <div class="input-group">';
echo '        <input type="text" class="form-control form-control-lg" id="token_001" name="token_code[]">';
echo '        <div class="input-group-append">';
echo '          <button class="btn btn-outline-danger" type="button" class="remove-token">&minus;</button>';
echo '        </div>';
echo '      </div>';
echo '    </div>';
// plus button to add more tokens, an icon
echo '    <button class="btn btn-outline-primary mt-3" type="button" class="add-token"><i class="fa fa-plus"></i></button>';
echo '  </div>';
echo '</div>';
// enroll button
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right"></div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="submit" class="btn btn-primary btn-lg" value="' . s(get_string('enroll', 'local_enrollment_tokens')) . '">';
echo '  </div>';
echo '</div>';
echo '</form>';
echo $OUTPUT->footer();
