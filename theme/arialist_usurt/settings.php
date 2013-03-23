<?php

/**
 * Settings for the arialist theme
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Logo file setting
    $name = 'theme_arialist_usurt/logo';
    $title = get_string('logo','theme_arialist_usurt');
    $description = get_string('logodesc', 'theme_arialist_usurt');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $settings->add($setting);

    // Tagline setting
    $name = 'theme_arialist_usurt/tagline';
    $title = get_string('tagline','theme_arialist_usurt');
    $description = get_string('taglinedesc', 'theme_arialist_usurt');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $settings->add($setting);

    // Link colour setting
    $name = 'theme_arialist_usurt/linkcolor';
    $title = get_string('linkcolor','theme_arialist_usurt');
    $description = get_string('linkcolordesc', 'theme_arialist_usurt');
    $default = '#f25f0f';
    $previewconfig = array('selector'=>'.block .content', 'style'=>'linkcolor');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $settings->add($setting);

    // Block region width
    $name = 'theme_arialist_usurt/regionwidth';
    $title = get_string('regionwidth','theme_arialist_usurt');
    $description = get_string('regionwidthdesc', 'theme_arialist_usurt');
    $default = 250;
    $choices = array(180=>'180px', 190=>'190px', 200=>'200px', 210=>'210px', 220=>'220px', 240=>'240px', 250=>'250px');
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    // Custom CSS file
    $name = 'theme_arialist_usurt/customcss';
    $title = get_string('customcss','theme_arialist_usurt');
    $description = get_string('customcssdesc', 'theme_arialist_usurt');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $settings->add($setting);
}
