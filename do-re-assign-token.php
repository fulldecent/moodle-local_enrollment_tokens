<?php
require_once('../../config.php');
global $DB;

// Process, validate form inputs
$email = required_param('email', PARAM_EMAIL);
$tokenCodes = required_param_array('token_code', PARAM_TEXT);

// Validate email
if (!validate_email($email)) {
    $url = new moodle_url('/local/enrollment_tokens/re-assign.php', ['token' => implode(',', $tokenCodes)]);
    redirect($url, get_string('erroremail', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
}

// Validate each token code
foreach (array_unique($tokenCodes) as $code) {
    $sql = "SELECT * FROM {enrollment_tokens} WHERE " . $DB->sql_compare_text('code') . " = ?";
    $params = [$code];
    $token = $DB->get_record_sql($sql, $params);

    // Check if the token exists
    if (empty($token)) {
        redirect(new moodle_url('/local/enrollment_tokens/re-assign.php'), get_string('errortokennotfound', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
    }

    // Check if the token is already used
    if (!empty($token->user_enrolments_id)) {
        redirect(new moodle_url('/local/enrollment_tokens/re-assign.php'), get_string('errortokenused', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

// Create user if not exists
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

// Re-assign tokens to the new email address
foreach ($tokenCodes as $code) {
    $sql = "SELECT * FROM {enrollment_tokens} WHERE " . $DB->sql_compare_text('code') . " = ?";
    $params = [$code];
    $token = $DB->get_record_sql($sql, $params);

    if ($token) {
        // Reassign the token to the new user
        $token->user_id = $user->id;
        error_log('Re-assigning token with ID ' . $token->id . ' to user ID ' . $user->id);
        try {
            $result = $DB->update_record('enrollment_tokens', $token);
            if (!$result) {
                throw new moodle_exception('Failed to update token record');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            redirect(new moodle_url('/local/enrollment_tokens/re-assign.php'), get_string('enrolmenterror', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}

// Success message
redirect(new moodle_url('/local/enrollment_tokens/re-assign.php'), get_string('enrolmentdoneforotheruser', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_SUCCESS);
