<?php
require_once('../../config.php');
global $DB, $USER, $PAGE, $OUTPUT;

// Ensure the user is logged in
require_login();

// Set the URL of the page
$PAGE->set_url(new moodle_url('/local/enrollment_tokens/view_tokens.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title('My Enrollment Tokens');
$PAGE->set_heading('My Enrollment Tokens');

// Fetch tokens associated with the logged-in user
$sql = "SELECT t.*, ue.userid as enrolled_user_id, ue.timecreated as enrolment_time, u.email as enrolled_user_email 
        FROM {enrollment_tokens} t
        LEFT JOIN {user_enrolments} ue ON t.user_enrolments_id = ue.id
        LEFT JOIN {user} u ON ue.userid = u.id
        WHERE t.user_id = ?";

$tokens = $DB->get_records_sql($sql, [$USER->id]);

echo $OUTPUT->header();

echo html_writer::tag('h3', 'My Enrollment Tokens', array('class' => 'mb-3'));

if (!empty($tokens)) {
    // Start a Bootstrap table
    echo html_writer::start_tag('table', array('class' => 'table table-striped table-hover'));
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Token Code');
    echo html_writer::tag('th', 'Course');
    echo html_writer::tag('th', 'Status');
    echo html_writer::tag('th', 'Used By');
    echo html_writer::tag('th', 'Used On');
    echo html_writer::tag('th', 'Action');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($tokens as $token) {
        // Fetch course details
        $course = $DB->get_record('course', ['id' => $token->course_id], 'fullname');
        $course_name = $course ? $course->fullname : 'Unknown Course';

        // Determine token status
        if (!empty($token->user_enrolments_id)) {
            $status = 'Used';
            $used_by = $token->enrolled_user_email;
            $used_on = userdate($token->enrolment_time);
        } else {
            $status = 'Available';
            $used_by = '-';
            $used_on = '-';
        }

        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', format_string($token->code));
        echo html_writer::tag('td', format_string($course_name));
        echo html_writer::tag('td', $status);
        echo html_writer::tag('td', format_string($used_by));
        echo html_writer::tag('td', $used_on);

        // Show "Use this token" button only for available tokens
        if ($status === 'Available') {
            $use_token_url = new moodle_url('/local/enrollment_tokens/use_token.php', ['token_code' => $token->code]);
            $button = html_writer::tag('a', 'Use this token', array(
                'href' => $use_token_url->out(),
                'class' => 'btn btn-primary'
            ));
            echo html_writer::tag('td', $button);
        } else {
            echo html_writer::tag('td', '-');
        }

        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');

} else {
    echo html_writer::tag('p', 'No tokens available.', array('class' => 'alert alert-info'));
}

echo $OUTPUT->footer();
?>
