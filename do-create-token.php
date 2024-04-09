<?php
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

// Process, validate form inputs ///////////////////////////////////////////////////////////////////////////////////////
require_sesskey();
$course_id = required_param('course_id', PARAM_INT);
$extra_json = optional_param('extra_json', '', PARAM_RAW); // Sanitize as per your requirement
$quantity = required_param('quantity', PARAM_INT);

// Validate course ID
$course = $DB->get_record('course', array('id' => $course_id));
if (empty($course)) {
  // Redirect back to the form with an error message
  redirect(new moodle_url('/local/enrolltokens/tokens.php'), get_string('errorcourse', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
}

// Validate JSON
if (!empty($extra_json)) {
  $extra_json = json_decode($extra_json);
  if (json_last_error() !== JSON_ERROR_NONE) {
    // Redirect back to the form with an error message
    redirect(new moodle_url('/local/enrolltokens/tokens.php'), get_string('errorjson', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
}

// Validate quantity
if ($quantity < 1) {
  // Redirect back to the form with an error message
  redirect(new moodle_url('/local/enrolltokens/tokens.php'), get_string('errorquantity', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
}

// Create tokens ///////////////////////////////////////////////////////////////////////////////////////////////////////
for ($i = 0; $i < $quantity; $i++) {
  $token = new stdClass();
  $token->course_id = $course_id;
  $token->extra_json = empty($extra_json) ? null : json_encode($extra_json);
  $course_id_number = $DB->get_field('course', 'idnumber', array('id' => $course_id));

  // Token format is (course_id_number or course_id)-(4 random hex digits)-(4 random hex digits)-(4 random hex digits)
  // eg CPR-1234-ABCD-EF12
  $tokenPrefix = $course_id_number ? $course_id_number : $course_id;
  $token->code = $tokenPrefix . '-' . bin2hex(openssl_random_pseudo_bytes(2)) . '-' . bin2hex(openssl_random_pseudo_bytes(2)) . '-' . bin2hex(openssl_random_pseudo_bytes(2));
  $token->timecreated = time();
  $token->timemodified = time();
  $DB->insert_record('enrollment_tokens', $token);
}

// Redirect back to the form with a success message
redirect(new moodle_url('/local/enrolltokens/index.php'), get_string('tokenscreated', 'local_enrollment_tokens', $quantity), null, \core\output\notification::NOTIFY_SUCCESS);
