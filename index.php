<?php
require_once('../../config.php');
require_login();
require_capability('moodle/site:config', context_system::instance());

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrollment_tokens/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_enrollment_tokens'));
$PAGE->set_heading(get_string('pluginname', 'local_enrollment_tokens'));

// Load from databases
$tokens = $DB->get_records('enrollment_tokens');
$courses = $DB->get_records_select_menu('course', '', null, '', 'id, fullname');

// Start output
echo $OUTPUT->header();
echo '<p>' . s(get_string('introduction', 'local_enrollment_tokens')) . '</p>';

// UI to create a token
echo '<h2 class="my-3">' . s(get_string('createtokens', 'local_enrollment_tokens')) . '</h2>';
echo '<form action="do-create-token.php" method="post">';
// Select a course
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="course_id">' . s(get_string('coursename', 'local_enrollment_tokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrollment_tokens | course_id</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo html_writer::select($courses, 'course_id', '', get_string('coursename', 'local_enrollment_tokens'));
echo '  </div>';
echo '</div>';
// Email
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="email">' . s(get_string('email', 'local_enrollment_tokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrollment_tokens | email</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="email" class="form-control" id="email" name="email" required>';
echo '  </div>';
echo '</div>';
// Corporate Account (optional)
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="corporate_account">' . s(get_string('corporateaccount', 'local_enrollment_tokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrollment_tokens | corporate_account</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="text" class="form-control" id="corporate_account" name="corporate_account">';
echo '    <div class="form-defaultinfo text-muted">Optional: Corporate Account</div>';
echo '  </div>';
echo '</div>';
// Extra JSON
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="extra_json">' . s(get_string('extrajson', 'local_enrollment_tokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrollment_tokens | extra_json</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <textarea class="form-control" id="extra_json" name="extra_json"></textarea>';
echo '    <div class="form-defaultinfo text-muted">Default: Empty</div>';
echo '  </div>';
echo '</div>';
// Quantity
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right">';
echo '    <label for="quantity">' . s(get_string('quantity', 'local_enrollment_tokens')) . '</label>';
echo '    <span class="form-shortname d-block small text-muted">enrollment_tokens | quantity</span>';
echo '  </div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>';
echo '  </div>';
echo '</div>';
// Submit
echo '<div class="form-item row mb-3">';
echo '  <div class="form-label col-sm-3 text-sm-right"></div>';
echo '  <div class="form-setting col-sm-9">';
echo '    <input type="submit" class="btn btn-primary" value="' . s(get_string('createtokens', 'local_enrollment_tokens')) . '">';
echo '  </div>';
echo '</div>';
// Prevent CSRF
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '"/>';
echo '</form>';

// Show existing tokens
echo '<h2 class="my-3">' . s(get_string('existingtokens', 'local_enrollment_tokens')) . '</h2>';
echo '<table class="table">';
echo '<tr>';
echo '  <th>' . s(get_string('token', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('course', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('createdby', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('createdat', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('purchaser', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('corporateaccount', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('usedby', 'local_enrollment_tokens')) . '</th>';
echo '  <th>' . s(get_string('usedat', 'local_enrollment_tokens')) . '</th>';
echo '</tr>';
foreach ($tokens as $token) {
    echo '<tr>';
    echo '<td>' . s($token->code) . '</td>';
    echo '<td>' . s($courses[$token->course_id]) . '</td>';

    // Fetch user who created the token
    $creator = $DB->get_record('user', array('id' => $token->created_by), 'email');
    $created_by = $creator ? s($creator->email) : 'none';
    echo '<td>' . $created_by . '</td>';

    // Format "Created at" in ISO date format
    $created_at = date('Y-m-d', $token->timecreated);
    echo '<td>' . $created_at . '</td>';

    // Fetch purchaser (assigned to)
    $purchaser_email = $DB->get_field('user', 'email', array('id' => $token->user_id));
    echo '<td>' . s($purchaser_email) . '</td>';

    // Display the Corporate Account if available
    $corporate_account = !empty($token->corporate_account) ? s($token->corporate_account) : '-';
    echo '<td>' . $corporate_account . '</td>';

    // Fetch used by and used at
    if (!empty($token->user_enrolments_id)) {
        // Fetch the user linked to the enrollment
        $enrollment = $DB->get_record('user_enrolments', array('id' => $token->user_enrolments_id));
        $used_by_user = $DB->get_record('user', array('id' => $enrollment->userid), 'email');
        $used_by = $used_by_user ? s($used_by_user->email) : 'none';
        // Format "Used at" in ISO date format
        $used_at = date('Y-m-d', $token->used_on);
    } else {
        $used_by = '-';
        $used_at = '-';
    }

    echo '<td>' . s($used_by) . '</td>';
    echo '<td>' . s($used_at) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo $OUTPUT->footer();
