<?php
require_once(__DIR__ . '/../../config.php');
require_login();
global $USER;

$PAGE->set_url(new moodle_url('/local/enrolltokens/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_enrolltokens'));
$PAGE->set_heading(get_string('pluginname', 'local_enrolltokens'));

echo $OUTPUT->header();
echo 'Welcome to the Enroll Tokens Module, ' . fullname($USER) . '.';
echo $OUTPUT->footer();
