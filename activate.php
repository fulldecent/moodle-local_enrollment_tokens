<?php
require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/tokens.php'));
$PAGE->set_title(get_string('activatecoursetokenss', 'local_enrolltokens'));
$PAGE->set_heading(get_string('activatecoursetokens', 'local_enrolltokens'));

$email = '';
// If the visitor is logged in, set the email address
if (isloggedin()) {
    $email = $USER->email;
}

// Start output
echo $OUTPUT->header();
echo '<p class="lead my-5">' . s(get_string('activateintroduction', 'local_enrolltokens')) . '</p>';

echo '<form class="lead" action="activate-submit.php" method="post">';
// input email
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="email">' . s(get_string('studentemailaddress', 'local_enrolltokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="email" class="form-control form-control-lg" id="email" name="email" value="' . s($email) . '">';
echo '  </div>';
echo '</div>';
// input token[] (the first in an array)
// it shows a minus at the right to remove the token
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="token_001">' . s(get_string('tokens', 'local_enrolltokens')) . '</label>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <div class="input-group">';
echo '      <input type="text" class="form-control form-control-lg" id="token_001" name="token_code">';
echo '      <div class="input-group-append">';
echo '        <button class="btn btn-outline-danger" type="button" onclick="removeToken(this)">&minus;</button>';
echo '      </div>';
echo '    </div>';
// plus button to add more tokens, an icon
echo '    <button class="btn btn-outline-primary mt-3" type="button" onclick="addToken(this)"><i class="fa fa-plus"></i></button>';
echo '  </div>';
echo '</div>';
// enroll button
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right"></div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="submit" class="btn btn-primary btn-lg" value="' . s(get_string('enroll', 'local_enrolltokens')) . '">';
echo '  </div>';
echo '</div>';
echo '</form>';
