<?php
/*
 * Plugin Name: Markedup Recent
 */

require('Recent_Markedup.class.php');

add_action('widgets_init', function () {
    register_widget('Recent_Markedup');
});
