<?php
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/tokens.php'));
$PAGE->set_title(get_string('pluginname', 'local_enrolltokens'));
$PAGE->set_heading(get_string('pluginname', 'local_enrolltokens'));

// Retrieve tokens from the database table
$tokens = $DB->get_records('enrol_token');

// Display tokens in a table
echo $OUTPUT->header();
echo '<table>';
echo '<tr><th>ID</th><th>Code</th><th>Course ID</th></tr>';
foreach ($tokens as $token) {
    echo '<tr>';
    echo '<td>' . $token->id . '</td>';
    echo '<td>' . $token->code . '</td>';
    echo '<td>' . $token->course_id . '</td>';
    echo '</tr>';
}
echo '</table>';
echo $OUTPUT->footer();