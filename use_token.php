<?php
require_once('../../config.php');
global $DB, $USER, $PAGE, $OUTPUT;

// Ensure the user is logged in
require_login();

// Set the URL of the page
$PAGE->set_url(new moodle_url('/local/enrollment_tokens/use_token.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title('Use Token');
$PAGE->set_heading('Use Token');

// Get the token code from URL parameter
$token_code = required_param('token_code', PARAM_TEXT);

// Use sql_compare_text() for text column comparisons
$sql = "SELECT * FROM {enrollment_tokens} WHERE " . $DB->sql_compare_text('code') . " = ? AND user_id = ?";
$params = [$token_code, $USER->id];

// Check if the token exists and is associated with the current user
$token = $DB->get_record_sql($sql, $params);

if (!$token) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Invalid token or token not associated with your account.', 'error');
    echo $OUTPUT->footer();
    exit();
}

// Check if the token is already used
if (!empty($token->user_enrolments_id)) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('This token has already been used.', 'error');
    echo $OUTPUT->footer();
    exit();
}

// Enroll the user in the course
$course = $DB->get_record('course', ['id' => $token->course_id]);
if (!$course) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Course not found.', 'error');
    echo $OUTPUT->footer();
    exit();
}

$context = context_course::instance($course->id);
$enrolinstances = enrol_get_instances($course->id, true);
$enrolinstance = null;
foreach ($enrolinstances as $instance) {
    if ($instance->enrol === 'manual') {
        $enrolinstance = $instance;
        break;
    }
}

if (!$enrolinstance) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('Manual enrollment method not enabled for this course.', 'error');
    echo $OUTPUT->footer();
    exit();
}

$roleId = 5; // Role ID for student
$enrolPlugin = enrol_get_plugin('manual');
$enrolPlugin->enrol_user($enrolinstance, $USER->id, $roleId); // Throws on error

// Mark token as used
$userEnrolment = $DB->get_record('user_enrolments', ['userid' => $USER->id, 'enrolid' => $enrolinstance->id]);
if ($userEnrolment) {
    $token->user_enrolments_id = $userEnrolment->id;
    $DB->update_record('enrollment_tokens', $token);
}

// Redirect to the dashboard
redirect(new moodle_url('/my/'));
