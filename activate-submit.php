<?php
require_once('../../config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/enrolltokens/tokens.php'));
$PAGE->set_title(get_string('activatecoursetokens', 'local_enrollment_tokens'));
$PAGE->set_heading(get_string('activatecoursetokens', 'local_enrollment_tokens'));

// test 


require_once('../../config.php');
global $DB;

$userEmail = 'will+2@pacificmedicaltraining.com';
$courseId = 1; // Set the course ID

$user = $DB->get_record('user', ['email' => $userEmail]);
if (!$user) {
    die("User not found.");
}

$course = $DB->get_record('course', ['id' => $courseId]);
$context = context_course::instance($course->id);

$enrolments = $DB->get_records_sql("
    SELECT e.*, ue.status AS enrolstatus
    FROM {enrol} e
    JOIN {user_enrolments} ue ON e.id = ue.enrolid
    WHERE ue.userid = :userid AND e.courseid = :courseid",
    ['userid' => $user->id, 'courseid' => $course->id]);

echo "Enrolments for user in course {$courseId}:<br>";
foreach ($enrolments as $enrolment) {
    echo "Enrolment method: {$enrolment->enrol}<br>";
    echo "Status: " . ($enrolment->enrolstatus == ENROL_USER_ACTIVE ? 'Active' : 'Inactive') . "<br>";
}


// end
echo 'yo';
$roles = get_user_roles($context, $user->id);
echo "Roles for user in course context:<br>";
foreach ($roles as $role) {
    echo "Role: {$role->shortname}<br>";
}
echo 'done';

// test
$allRoles = get_user_roles(context_system::instance(), $user->id, true);
echo "All roles for user across all contexts:<br>";
foreach ($allRoles as $role) {
    echo "Role: {$role->shortname}, Context Level: {$role->contextlevel}<br>";
}

// moar
$sql = "SELECT ra.*, r.shortname, c.instanceid, c.contextlevel
        FROM {role_assignments} ra
        JOIN {context} c ON ra.contextid = c.id
        JOIN {role} r ON ra.roleid = r.id
        WHERE ra.userid = :userid";
$roleAssignments = $DB->get_records_sql($sql, ['userid' => $user->id]);

echo "Direct DB check - Role assignments for user:<br>";
foreach ($roleAssignments as $assignment) {
    echo "Role: {$assignment->shortname}, Context ID: {$assignment->contextid}, Context Level: {$assignment->contextlevel}, Instance ID: {$assignment->instanceid}<br>";
}


// Process, validate form inputs ///////////////////////////////////////////////////////////////////////////////////////
$email = required_param('email', PARAM_EMAIL);
$token_code = required_param('token_code', PARAM_RAW); // Sanitize as per your requirement

// Get user, may be null
$user = $DB->get_record('user', ['email' => $email]);

$token_codes = [$token_code]; // todo: allow multiple tokens

// Validate enrollment tokens
$enrollmentTokens = [];
// For each token, check in enrollment_tokens table, and ensure user_enrolments_id is null, if not found then error
foreach ($token_codes as $token_code) {
  echo 'checking token ' . $token_code . '...';

  $enrollmentToken = $DB->get_record('enrollment_tokens', ['code' => $token_code]);
  if (!$enrollmentToken) {
    // Redirect back to the form with an error message
    redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('errortokennotfound', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
  if (!empty($enrollmentToken->user_enrolments_id)) {
    // Redirect back to the form with an error message
    redirect(new moodle_url('/local/enrolltokens/activate.php'), get_string('errortokenused', 'local_enrollment_tokens'), null, \core\output\notification::NOTIFY_ERROR);
  }
  // If user is actively enrolled in course, silently proceed, but don't spend that token. It is still unspent.
  if (!empty($user)) {
    $context = context_course::instance($enrollmentToken->course_id);
    var_dump($context);
    // This is_enrolled checks if they are ACTIVE and not expired
    $isEnrolled = is_enrolled($context, $user->id, '', true);
    echo 'isEnrolled: ' . $isEnrolled . PHP_EOL;
    
    echo 'isEnrolled: ' . is_enrolled($context, $user->id, '', false) . PHP_EOL;
    if ($isEnrolled) {
      echo "already enrolled";
      continue;
    }
  }
  echo 'will enroll it';
  $enrollmentTokens[] = $enrollmentToken;
}

// Create user if not exists
// todo: maybe there is some API to create users maybe??
// ? https://github.com/moodle/moodle/blob/9587029a4609b3222a3564207aada02716d3332d/user/lib.php#L42
if (!$user) {
  $user = new stdClass();
  $user->email = $email;
  $user->username = $email;
  $user->firstname = 'NOT SET';
  $user->lastname = 'NOT SET';
  $user->id = $DB->insert_record('user', $user);
}

// Enroll user in courses
// If they are new to the course, that's easy
// If they were already enrolled but expired or other status!=active, then we need to re-enroll them which includes
// resetting progress
foreach ($enrollmentTokens as $token) {
  echo 'Using token: ' . $token->code . "<br>";

  $course = $DB->get_record('course', ['id' => $token->course_id]);
  $context = context_course::instance($course->id);
  $enrolinstances = enrol_get_instances($course->id, true);
  $enrolinstance = null;

  // Find manual enrolment instance
  foreach ($enrolinstances as $instance) {
      if ($instance->enrol === 'manual') {
          $enrolinstance = $instance;
          break;
      }
  }

  if ($enrolinstance === null) {
      echo 'No manual enrolment instance found for course: ' . $course->fullname . "<br>";
      continue;
  }

  $enrolPlugin = enrol_get_plugin('manual');

  // If any enrollment (including inactive or expired) then reset all progress
  $isEnrolledActiveOrNot = is_enrolled($context, $user->id, '', false);
  if ($isEnrolledActiveOrNot) {
      // Clear grades
      require_once($CFG->libdir.'/gradelib.php');
      grade_delete_grades($course->id, 'user', $user->id);
      
      // Reset activity completion
      require_once($CFG->dirroot.'/completionlib.php');
      $completion = new completion_info($course);
      $activities = $completion->get_activities();
      foreach ($activities as $activity) {
          $completion->update_state($activity, COMPLETION_INCOMPLETE, $user->id);
      }
      // Remove activity submissions
      // todo: Specific code needed based on the activity type
  }

  // Enroll them
  $roleId = 5; // roleId for student
  $enrolPlugin->enrol_user($enrolinstance, $user->id, $roleId);

  // Mark token as used
  $token->user_enrolments_id = $user->id;
  $DB->update_record('enrollment_tokens', $token);
}


echo 'done';