<?php
/**
 * Block Templates for CampaignPress Post Types
 *
 * Defines the initial block layout when creating new campaign-specific content.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Block Templates for Custom Post Types
 */
function campaignpress_register_block_templates() {
    // Volunteer Opportunities Template
    $volunteer_obj = get_post_type_object('cp_volunteer');
    if ($volunteer_obj) {
        $volunteer_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Describe this volunteer opportunity...', 'campaign-office'),
            )),
            array('core/heading', array(
                'level' => 3,
                'content' => __('Responsibilities', 'campaign-office'),
            )),
            array('core/list', array(
                'placeholder' => __('List the responsibilities...', 'campaign-office'),
            )),
            array('core/heading', array(
                'level' => 3,
                'content' => __('Requirements', 'campaign-office'),
            )),
            array('core/list', array(
                'placeholder' => __('List the requirements...', 'campaign-office'),
            )),
            array('core/group', array(
                'backgroundColor' => 'neutral-50',
                'layout' => array('type' => 'constrained'),
            ), array(
                array('core/heading', array(
                    'level' => 3,
                    'content' => __('Ready to Apply?', 'campaign-office'),
                )),
                array('core/paragraph', array(
                    'content' => __('If you are ready to make a difference, click the button below to sign up.', 'campaign-office'),
                )),
                array('core/buttons', array(
                    'layout' => array('type' => 'flex', 'justifyContent' => 'center'),
                ), array(
                    array('core/button', array(
                        'text' => __('Sign Up Now', 'campaign-office'),
                    )),
                )),
            )),
        );
    }

    // Press Releases Template
    $press_obj = get_post_type_object('cp_press_release');
    if ($press_obj) {
        $press_obj->template = array(
            array('core/paragraph', array(
                'content' => '<strong>' . __('FOR IMMEDIATE RELEASE', 'campaign-office') . '</strong>',
            )),
            array('core/paragraph', array(
                'placeholder' => 'CITY, State — Date of release',
            )),
            array('core/paragraph', array(
                'placeholder' => __('Lead paragraph: Start with a strong hook that summarizes the news.', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Body content: Provide more details about the announcement.', 'campaign-office'),
            )),
            array('core/quote', array(
                'placeholder' => __('Insert a quote from the candidate or spokesperson.', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Closing details: Final information or call to action.', 'campaign-office'),
            )),
            array('core/separator'),
            array('core/heading', array(
                'level' => 4,
                'content' => __('About the Campaign', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Short boilerplate about the candidate and campaign mission.', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'content' => '<strong>' . __('Media Contact:', 'campaign-office') . '</strong><br>' . __('Name', 'campaign-office') . '<br>press@campaign.test<br>(555) 123-4567',
            )),
        );
    }

    // Issues Template
    $issue_obj = get_post_type_object('cp_issue');
    if ($issue_obj) {
        $issue_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Brief summary of the issue and why it matters.', 'campaign-office'),
                'fontSize' => 'lg',
            )),
            array('core/heading', array(
                'level' => 2,
                'content' => __('The Problem', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Describe the current challenges facing our community regarding this issue.', 'campaign-office'),
            )),
            array('core/heading', array(
                'level' => 2,
                'content' => __('Our Solution', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Detail our plan to address these challenges and improve outcomes.', 'campaign-office'),
            )),
            array('core/list', array(
                'placeholder' => __('Key policy points...', 'campaign-office'),
            )),
        );
    }

    // Events Template
    $event_obj = get_post_type_object('cp_event');
    if ($event_obj) {
        $event_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Description of the event...', 'campaign-office'),
            )),
            array('core/group', array(
                'layout' => array('type' => 'flex', 'flexWrap' => 'nowrap'),
            ), array(
                array('core/paragraph', array('content' => '<strong>' . __('Date:', 'campaign-office') . '</strong> [Event Date]')),
                array('core/paragraph', array('content' => '<strong>' . __('Time:', 'campaign-office') . '</strong> [Event Time]')),
            )),
            array('core/paragraph', array('content' => '<strong>' . __('Location:', 'campaign-office') . '</strong> [Event Location]')),
            array('core/buttons', array(), array(
                array('core/button', array('text' => __('RSVP for this Event', 'campaign-office'))),
            )),
        );
    }

    // Team Members Template
    $team_obj = get_post_type_object('cp_team');
    if ($team_obj) {
        $team_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Biography and background of the team member...', 'campaign-office'),
            )),
            array('core/heading', array(
                'level' => 4,
                'content' => __('Contact Information', 'campaign-office'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Email, phone, or social media links...', 'campaign-office'),
            )),
        );
    }
}
add_action('init', 'campaignpress_register_block_templates', 20); // Run after post types are registered
