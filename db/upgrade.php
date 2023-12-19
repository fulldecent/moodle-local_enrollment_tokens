<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_enrolltokens_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023122001) {
        // Define table enrol_token to be created
        $table = new xmldb_table('enrol_token');

        // Adding fields to table enrol_token
        $id = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->addField($id);

        $timecreated = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->addField($timecreated);

        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->addField($timemodified);

        $code = new xmldb_field('code', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->addField($code);

        $course_id = new xmldb_field('course_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->addField($course_id);

        $user_enrolments_id = new xmldb_field('user_enrolments_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->addField($user_enrolments_id);

        $group_id = new xmldb_field('group_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->addField($group_id);

        $extraJson = new xmldb_field('extra_json', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->addField($extraJson);

        // Add keys to the table
        $table->addKey(new xmldb_key('primary', XMLDB_KEY_PRIMARY, array('id')));
        $table->addKey(new xmldb_key('course_id_fk', XMLDB_KEY_FOREIGN, array('course_id'), 'course', array('id')));
        $table->addKey(new xmldb_key('user_enrolments_id_fk', XMLDB_KEY_FOREIGN, array('user_enrolments_id'), 'user_enrolments', array('id')));

        // Create table
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Enrolltokens savepoint reached
        upgrade_plugin_savepoint(true, 2023122001, 'local', 'enrolltokens');
    }

    return true;
}
