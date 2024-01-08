<?php
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/tokens.php'));
$PAGE->set_title(get_string('pluginname', 'local_enrolltokens'));
$PAGE->set_heading(get_string('pluginname', 'local_enrolltokens'));

// Load from databases
$tokens = $DB->get_records('enrol_token');
$courses = $DB->get_records_select_menu('course', '', null, '', 'id, fullname');

// Start output
echo $OUTPUT->header();
echo '<p>' . s(get_string('introduction', 'local_enrolltokens')) . '</p>';

// UI to create a token
echo '<h2 class="my-3">' . s(get_string('createtokens', 'local_enrolltokens')) . '</h2>';
echo '<form action="token-create-submit.php" method="post">';
// select a course
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="course_id">' . s(get_string('selectcourse', 'local_enrolltokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrol_token | course_id</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo html_writer::select($courses, 'course_id', '', get_string('selectcourse', 'local_enrolltokens'));
echo '  </div>';
echo '</div>';
// extra JSON
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="extra_json">' . s(get_string('extrajson', 'local_enrolltokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrol_token | extra_json</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <textarea class="form-control" id="extra_json" name="extra_json"></textarea>';
echo '    <div class="form-defaultinfo text-muted">Default: Empty</div>';
echo '  </div>';
echo '</div>';
// quantity
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="quantity">' . s(get_string('quantity', 'local_enrolltokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrol_token | quantity</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="number" class="form-control" id="quantity" name="quantity" min="1">';
echo '  </div>';
echo '</div>';
// submit
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right"></div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="submit" class="btn btn-primary" value="' . s(get_string('createtokens', 'local_enrolltokens')) . '">';
echo '  </div>';
echo '</div>';
// Prevent CSRF
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '"/>';
echo '</form>';

// Show existing tokens
echo '<h2 class="my-3">' . s(get_string('existingtokens', 'local_enrolltokens')) . '</h2>';
echo '<table class="table">';
echo '<tr>';
echo '  <th>' . s(get_string('id', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('code', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('courseid', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('coursefullname', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('timecreated', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('enrollment', 'local_enrolltokens')) . '</th>';
echo '  <th>' . s(get_string('extrajson', 'local_enrolltokens')) . '</th>';
echo '</tr>';
foreach ($tokens as $token) {
    echo '<tr>';
    echo '<td>' . $token->id . '</td>';
    echo '<td>' . $token->code . '</td>';
    echo '<td>' . $token->course_id . '</td>';
    echo '<td>' . s($courses[$token->course_id]) . '</td>';
    // format date as ISO8601
    echo '<td>' . userdate($token->timecreated, '%Y-%m-%dT%H:%M:%S%z') . '</td>';
    echo '<td>' . ($token->user_enrolments_id ?? 'none') . '</td>';
    echo '<td>' . s($courses[$token->extra_json]) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo $OUTPUT->footer();