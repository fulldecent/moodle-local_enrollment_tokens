<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'local/enrolltokens:manage' => array(
        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
);
