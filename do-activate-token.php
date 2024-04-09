<?php
require_once('../../config.php');
global $DB;

// Process, validate form inputs ///////////////////////////////////////////////////////////////////////////////////////
$email = required_param('email', PARAM_EMAIL);
$tokenCodes = required_param_array('token_code', PARAM_TEXT);

// Validate email
if (!validate_email($email)) {
  // Redirect back to the form with an error message
  redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('erroremail', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
}

// Validate each token code
foreach (array_unique($tokenCodes) as $code) {
  $sql = "SELECT * FROM {enrollment_tokens} WHERE " . $DB->sql_compare_text('code') . " = ?";
  $params = [$code];
  
  // Execute the query
  $token = $DB->get_record_sql($sql, $params);
  if (empty($token)) {
    // Redirect back to the form with an error message
    redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('errortokennotfound', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
  if (!empty($token->user_enrolments_id)) {
    // Redirect back to the form with an error message
    redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('errortokenused', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
}

// Create user if not exists ///////////////////////////////////////////////////////////////////////////////////////////
$createdNewUser = false;
$user = $DB->get_record('user', ['email' => $email]);
if (empty($user)) {
  $user = new stdClass();
  $user->email = $email;
  $user->username = $email;
  $user->firstname = 'NOT SET';
  $user->lastname = 'NOT SET';
  $user->id = $DB->insert_record('user', $user);
  $createdNewUser = true;
}

// Enroll user in courses //////////////////////////////////////////////////////////////////////////////////////////////
foreach ($tokenCodes as $code) {
  $sql = "SELECT * FROM {enrollment_tokens} WHERE " . $DB->sql_compare_text('code') . " = ?";
  $params = [$code];
  $token = $DB->get_record_sql($sql, $params);

  $course = $DB->get_record('course', ['id' => $token->course_id]);
  $context = context_course::instance($course->id);
  $enrolinstances = enrol_get_instances($course->id, true);
  $enrolinstance = null;
  foreach ($enrolinstances as $instance) {
    if ($instance->enrol === 'manual') {
      $enrolinstance = $instance;
      break;
    }
  }
  if (empty($enrolinstance)) {
    // Manual enrolment plugin missing on Moodle server
    redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('errormanualenrolment', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
  $roleId = 5; // roleId for student
  //TODO: should we also have our own enrol plugin? should this be an "enrol" plugin instead of a "local"?
  $enrolPlugin = enrol_get_plugin('manual');
  $enrolPlugin->enrol_user($enrolinstance, $user->id, $roleId); // throws on error

  //TODO: error out if this student is already enrolled in this course with this enrolment method

  // Mark token as used by updating user_enrolments_id to user_enrolments.id
  $userEnrolment = $DB->get_record('user_enrolments', ['userid' => $user->id, 'enrolid' => $enrolinstance->id]);
  if ($userEnrolment) {
    $token->user_enrolments_id = $userEnrolment->id;
    $DB->update_record('enrollment_tokens', $token);
  }
}

// Redirect to main page ///////////////////////////////////////////////////////////////////////////////////////////////

// Existing account the user is already logged in to
if (!$createdNewUser && $USER->email === $email) {
  redirect(new moodle_url('/'), get_string('enrolmentdone', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Course assigned to an existing student, go back to activate other tokens
redirect(new moodle_url('/local/enrollment_tokens/activate.php'), get_string('enrolmentdoneforotheruser', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_SUCCESS);