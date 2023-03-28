<?php
$settings->add(new admin_setting_heading(
            'headerconfig',
            get_string('headerconfig', 'block_report'),
            get_string('descconfig', 'block_report')
        ));

$settings->add(new admin_setting_configcheckbox(
            'report/Allow_HTML',
            get_string('labelallowhtml', 'block_report'),
            get_string('descallowhtml', 'block_report'),
            '0'

        ));
