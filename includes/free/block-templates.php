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
                'placeholder' => __('Describe this volunteer opportunity...', 'campaignpress'),
            )),
            array('core/heading', array(
                'level' => 3,
                'content' => __('Responsibilities', 'campaignpress'),
            )),
            array('core/list', array(
                'placeholder' => __('List the responsibilities...', 'campaignpress'),
            )),
            array('core/heading', array(
                'level' => 3,
                'content' => __('Requirements', 'campaignpress'),
            )),
            array('core/list', array(
                'placeholder' => __('List the requirements...', 'campaignpress'),
            )),
            array('core/group', array(
                'backgroundColor' => 'neutral-50',
                'layout' => array('type' => 'constrained'),
            ), array(
                array('core/heading', array(
                    'level' => 3,
                    'content' => __('Ready to Apply?', 'campaignpress'),
                )),
                array('core/paragraph', array(
                    'content' => __('If you are ready to make a difference, click the button below to sign up.', 'campaignpress'),
                )),
                array('core/buttons', array(
                    'layout' => array('type' => 'flex', 'justifyContent' => 'center'),
                ), array(
                    array('core/button', array(
                        'text' => __('Sign Up Now', 'campaignpress'),
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
                'content' => '<strong>' . __('FOR IMMEDIATE RELEASE', 'campaignpress') . '</strong>',
            )),
            array('core/paragraph', array(
                'placeholder' => 'CITY, State — Date of release',
            )),
            array('core/paragraph', array(
                'placeholder' => __('Lead paragraph: Start with a strong hook that summarizes the news.', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Body content: Provide more details about the announcement.', 'campaignpress'),
            )),
            array('core/quote', array(
                'placeholder' => __('Insert a quote from the candidate or spokesperson.', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Closing details: Final information or call to action.', 'campaignpress'),
            )),
            array('core/separator'),
            array('core/heading', array(
                'level' => 4,
                'content' => __('About the Campaign', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Short boilerplate about the candidate and campaign mission.', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'content' => '<strong>' . __('Media Contact:', 'campaignpress') . '</strong><br>' . __('Name', 'campaignpress') . '<br>press@campaign.test<br>(555) 123-4567',
            )),
        );
    }

    // Issues Template
    $issue_obj = get_post_type_object('cp_issue');
    if ($issue_obj) {
        $issue_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Brief summary of the issue and why it matters.', 'campaignpress'),
                'fontSize' => 'lg',
            )),
            array('core/heading', array(
                'level' => 2,
                'content' => __('The Problem', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Describe the current challenges facing our community regarding this issue.', 'campaignpress'),
            )),
            array('core/heading', array(
                'level' => 2,
                'content' => __('Our Solution', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Detail our plan to address these challenges and improve outcomes.', 'campaignpress'),
            )),
            array('core/list', array(
                'placeholder' => __('Key policy points...', 'campaignpress'),
            )),
        );
    }

    // Events Template
    $event_obj = get_post_type_object('cp_event');
    if ($event_obj) {
        $event_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Description of the event...', 'campaignpress'),
            )),
            array('core/group', array(
                'layout' => array('type' => 'flex', 'flexWrap' => 'nowrap'),
            ), array(
                array('core/paragraph', array('content' => '<strong>' . __('Date:', 'campaignpress') . '</strong> [Event Date]')),
                array('core/paragraph', array('content' => '<strong>' . __('Time:', 'campaignpress') . '</strong> [Event Time]')),
            )),
            array('core/paragraph', array('content' => '<strong>' . __('Location:', 'campaignpress') . '</strong> [Event Location]')),
            array('core/buttons', array(), array(
                array('core/button', array('text' => __('RSVP for this Event', 'campaignpress'))),
            )),
        );
    }

    // Team Members Template
    $team_obj = get_post_type_object('cp_team');
    if ($team_obj) {
        $team_obj->template = array(
            array('core/paragraph', array(
                'placeholder' => __('Biography and background of the team member...', 'campaignpress'),
            )),
            array('core/heading', array(
                'level' => 4,
                'content' => __('Contact Information', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Email, phone, or social media links...', 'campaignpress'),
            )),
        );
    }

    // Endorsements Template
    $endorsement_obj = get_post_type_object('cp_endorsement');
    if ($endorsement_obj) {
        $endorsement_obj->template = array(
            array('core/paragraph', array(
                'content' => '<strong>' . __('Endorser Information', 'campaignpress') . '</strong>',
                'fontSize' => 'lg',
            )),
            array('core/paragraph', array(
                'placeholder' => __('Title/Position and Organization (use the Endorsement Details box on the right)', 'campaignpress'),
                'className' => 'endorsement-subtitle',
            )),
            array('core/quote', array(
                'placeholder' => __('"I am proud to endorse [Candidate Name] because..."', 'campaignpress'),
                'className' => 'endorsement-quote',
            )),
            array('core/heading', array(
                'level' => 3,
                'content' => __('Why This Matters', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Explain why this endorsement is significant for the campaign...', 'campaignpress'),
            )),
            array('core/separator'),
            array('core/heading', array(
                'level' => 4,
                'content' => __('About the Endorser', 'campaignpress'),
            )),
            array('core/paragraph', array(
                'placeholder' => __('Background information about the endorser or organization...', 'campaignpress'),
            )),
        );
    }
}
add_action('init', 'campaignpress_register_block_templates', 20); // Run after post types are registered
