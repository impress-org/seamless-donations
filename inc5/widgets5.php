<?php
/**
 * Seamless Donations by David Gewirtz, adopted from Allen Snook
 *
 * Lab Notes: http://zatzlabs.com/lab-notes/
 * Plugin Page: http://zatzlabs.com/seamless-donations/
 * Contact: http://zatzlabs.com/contact-us/
 *
 * Copyright (c) 2015-2020 by David Gewirtz
 *
 */

//	Exit if .php file accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Adds widget: Donations This Month
class DonationsThisMonth_Widget extends WP_Widget
{
    function __construct() {
        parent::__construct(
            'donationsthismonth_widget',
            esc_html__('Donations This Month', 'seamless-donations'),
            array('description' => esc_html__('Displays the total of all donations made in the current month.', 'seamless-donations'),) // Args
        );
    }

    private $widget_fields = array(
        array(
            'label'   => 'Title',
            'id'      => 'title',
            'default' => 'Donations This Month',
            'type'    => 'text',
        ),
        array(
            'label'   => 'Month Total Name',
            'id'      => 'month_total_name',
            'default' => 'Month Total',
            'type'    => 'text',
        ),
        array(
            'label'   => 'Separator Between Name and Total',
            'id'      => 'month_total_separator',
            'default' => ' - ',
            'type'    => 'text',
        ),
    );

    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // Output generated fields
        //echo '<p>' . $instance['title'] . '</p>';
        echo $instance['month_total_name'];
        echo $instance['month_total_separator'];
        echo $this->get_month_total();

        echo $args['after_widget'];
    }

    public function form($instance) {
        $output = '';

        // promo
        $promo_text = 'Basic Widget Pack provides five additional informative and helpful widgets.';
        $promo_url  = 'http://zatzlabs.com/project/seamless-donations-basic-widget-pack/';
        $promo_desc = seamless_donations_get_feature_promo($promo_text, $promo_url);

        foreach ($this->widget_fields as $widget_field) {
            $default = '';
            if (isset($widget_field['default'])) {
                $default = $widget_field['default'];
            }
            $widget_value = !empty($instance[$widget_field['id']]) ? $instance[$widget_field['id']] : esc_html__($default, 'seamless-donations');
            switch ($widget_field['type']) {
                default:
                    $output .= '<p>';
                    $output .= '<label for="' . esc_attr($this->get_field_id($widget_field['id'])) . '">' .
                        esc_attr($widget_field['label'], 'seamless-donations') . ':</label> ';

                    $output .= '<input class="widefat" id="' . esc_attr($this->get_field_id($widget_field['id'])) .
                        '" name="' . esc_attr($this->get_field_name($widget_field['id'])) .
                        '" type="' . $widget_field['type'] .
                        '" value="' . esc_attr($widget_value) . '">';
                    $output .= '</p>';
            }
        }

        echo $output;
        echo $promo_desc . '<BR><BR>';
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        //  $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        foreach ($this->widget_fields as $widget_field) {
            switch ($widget_field['type']) {
                default:
                    $instance[$widget_field['id']] =
                        (!empty($new_instance[$widget_field['id']])) ? strip_tags($new_instance[$widget_field['id']]) : '';
            }
        }
        return $instance;
    }

    private function get_month_total() {
        // Set up a date interval object for 6 months ago (you can change as required)
        $interval         = new DateInterval('P1M');
        $interval->invert = 1;

        // TODO - this might actually be buggy. Could be that it pulls previous month or
        // TODO - it pulls previous 30/31 days. Check into it.
        $date = new DateTime(date('Y-m-d'));
        $date->add($interval);

        /////
        // select the donations to show

        $args = array(
            'post_type'   => 'donation',
            'post_status' => 'publish',
            'nopaging'    => true,
            'order_by'    => 'date',
            'date_query'  => array(
                'after' => $date->format('Y-m-d'),
            ),
        );

        $donations_array = get_posts($args);

        $donation_total = 0.0;

        // loop through a list of donations
        for ($i = 0; $i < count($donations_array); ++$i) {
            // extract the fund id from the donation and fund records
            $donation_id = $donations_array[$i]->ID;

            $amount         = get_post_meta($donation_id, '_dgx_donate_amount', true);
            $donation_total += floatval($amount);
        }

        $donation_total = dgx_donate_get_escaped_formatted_amount(floatval($donation_total), 0);
        return $donation_total;
    }
}

function register_donationsthismonth_widget() {
    register_widget('DonationsThisMonth_Widget');
}

add_action('widgets_init', 'register_donationsthismonth_widget');
