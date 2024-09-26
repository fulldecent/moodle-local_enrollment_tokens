<?php
require_once('../../config.php');
global $DB, $USER, $PAGE, $OUTPUT;

// Ensure the user is logged in
require_login();

// Set the URL of the page
$PAGE->set_url(new moodle_url('/local/enrollment_tokens/view_tokens.php'));
$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_title('My course tokens');
$PAGE->set_heading('My course tokens');

// Fetch tokens associated with the logged-in user
$sql = "SELECT t.*, u.email as enrolled_user_email
        FROM {enrollment_tokens} t
        LEFT JOIN {user_enrolments} ue ON t.user_enrolments_id = ue.id
        LEFT JOIN {user} u ON ue.userid = u.id
        WHERE t.user_id = ?";

$tokens = $DB->get_records_sql($sql, [$USER->id]);

echo $OUTPUT->header();

echo html_writer::tag('h3', 'My course tokens', array('class' => 'mb-3'));

if (!empty($tokens)) {
    // Start a Bootstrap table
    echo html_writer::start_tag('table', array('class' => 'table table-striped table-hover'));
    echo html_writer::start_tag('thead');
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', 'Token code');
    echo html_writer::tag('th', 'Course');
    echo html_writer::tag('th', 'Status');
    echo html_writer::tag('th', 'Used by');
    echo html_writer::tag('th', 'Used on');
    echo html_writer::tag('th', 'Use for yourself');
    echo html_writer::tag('th', 'Share token');
    echo html_writer::end_tag('tr');
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody');

    foreach ($tokens as $token) {
        // Fetch course details
        $course = $DB->get_record('course', ['id' => $token->course_id], 'fullname');
        $course_name = $course ? $course->fullname : 'Unknown Course';
    
        // Determine token status
        if (!empty($token->used_on)) {
            $status = 'Used';
            $used_by = $token->enrolled_user_email;
            // Format the used_on date without leading zeros in the month and day
            $used_on = date('Y-n-j', $token->used_on);
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
    
        // Show "Use for yourself" button only for available tokens
        if ($status === 'Available') {
            $use_token_url = new moodle_url('/local/enrollment_tokens/use_token.php', ['token_code' => $token->code]);
            $use_button = html_writer::tag('a', 'Use for yourself', array(
                'href' => $use_token_url->out(),
                'class' => 'btn btn-primary'
            ));
            echo html_writer::tag('td', $use_button);

            // Add "Share Token" button
            $share_token_url = "https://learn.pacificmedicaltraining.com/local/enrollment_tokens/re-assign.php?token=" . $token->code;
            $share_button = html_writer::tag('button', 'Share token', array(
                'class' => 'btn btn-secondary',
                'onclick' => "navigator.clipboard.writeText('{$share_token_url}').then(function() { alert('Link copied to clipboard. Share the link.'); });"
            ));
            echo html_writer::tag('td', $share_button);
        } else {
            echo html_writer::tag('td', '-');
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
