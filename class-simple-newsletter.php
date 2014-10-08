<?php
/**
* Simple Newsletter.
*
* @package   Simple_Newsletter_Widget
* @author    Constantine Kiriaze, hello@kiriaze.com
* @license   GPL-2.0+
* @link      http://getsimple.io
* @copyright 2013 Constantine Kiriaze
*
*
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Simple_Newsletter_Widget' ) ) :


    // ADD FUNTION TO WIDGETS_INIT
    add_action( 'widgets_init', 'simple_newsletter' );

    // REGISTER WIDGET
    function simple_newsletter() {
        register_widget( 'Simple_Newsletter_Widget' );
    }

    // WIDGET CLASS
    class Simple_Newsletter_Widget extends WP_Widget {


        /*--------------------------------------------------------------------*/
        /*  WIDGET SETUP
        /*--------------------------------------------------------------------*/
        public function __construct() {
            parent::__construct(
                'simple_newsletter',
                'Newsletter (Simple)',
                array(
                    'description' => __( 'A custom widget that displays a newsletter subscribe field', 'simple' ),
                )
            );

            add_action( 'wp_ajax_newsletter_ajax_request', array( &$this, 'newsletter_ajax_request' ) );
            add_action( 'wp_ajax_nopriv_newsletter_ajax_request', array( &$this, 'newsletter_ajax_request' ) );
        }


        function newsletter_ajax_request( $instance ) {

            $settings = $this->get_settings();

            // The $_REQUEST contains all the data sent via ajax
            if ( isset($_REQUEST) ) {

                // If you're debugging, it might be useful to see what was sent in the $_REQUEST
                // sp($_REQUEST);

                $apiKey         = $settings['2']['apiKey'];
                $listId         = $settings['2']['listID'];
                $double_optin   = false;
                $send_welcome   = false;
                $email_type     = 'html';
                $email          = $_POST['email'];

                //replace us2 with your actual datacenter
                $submit_url = "http://us3.api.mailchimp.com/1.3/?method=listSubscribe";
                $data = array(
                    'email_address' => $email,
                    'apikey'        => $apiKey,
                    'id'            => $listId,
                    'double_optin'  => $double_optin,
                    'send_welcome'  => $send_welcome,
                    'email_type'    => $email_type
                );

                $payload = json_encode($data);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $submit_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, urlencode($payload));

                $result = curl_exec($ch);
                curl_close ($ch);
                $data = json_decode($result);
                if ($data->error){
                    echo $data->error;
                } else {
                    echo "Got it, you've been added to our email list.";
                }

            }

            // Always die in functions echoing ajax content
           die();
        }

        /*--------------------------------------------------------------------*/
        /*  DISPLAY WIDGET
        /*--------------------------------------------------------------------*/
        function widget( $args, $instance ) {

            wp_enqueue_script(
                'widget-newsletter',
                plugins_url( 'newsletter.js', __FILE__ ),
                array('jquery')
            );

            extract( $args );

            $title = apply_filters('widget_title', $instance['title'] );

            // widget vars
            $placeholder    = $instance['placeholder'];
            $desc           = $instance['desc'];

            // before widget
            echo $before_widget;

            ?>

            <?php if ( $title ) echo $before_title . $title . $after_title; ?>

                <?php if ( $desc != '' ) : ?>
                    <p><?php echo $desc; ?></p>
                <?php endif; ?>

                <form action="" method="post" id="mc-subscribe-form" class="validate">

                    <input type="email" name="email" class="email-newsletter" id="mce-email" placeholder="<?php echo $placeholder; ?>" required data-validate="validate(required, email)">

                    <input type="submit" value="<?php _e('Subscribe','simple'); ?>" class="btn large">

                </form>

                <p id="response"></p>

            <?php

            // after widget
            echo $after_widget;
        }


        /*--------------------------------------------------------------------*/
        /*  UPDATE WIDGET
        /*--------------------------------------------------------------------*/
        function update( $new_instance, $old_instance ) {

            $instance = $old_instance;

            // STRIP TAGS TO REMOVE HTML - IMPORTANT FOR TEXT IMPUTS
            $instance['title']          = strip_tags( $new_instance['title'] );
            $instance['apiKey']         = stripslashes( $new_instance['apiKey'] );
            $instance['listID']         = stripslashes( $new_instance['listID'] );
            $instance['placeholder']    = stripslashes( $new_instance['placeholder'] );
            $instance['desc']           = stripslashes( $new_instance['desc'] );

            return $instance;
        }


        /*--------------------------------------------------------------------*/
        /*  WIDGET SETTINGS (FRONT END PANEL)
        /*--------------------------------------------------------------------*/
        function form( $instance ) {

            // WIDGET DEFAULTS
            $defaults = array(
                'title'         => 'Newsletter.',
                'placeholder'   => 'Enter your email address...',
                'apiKey'        => '',
                'listID'        => '',
                'desc'          => 'This is a nice and simple  email newsletter widget.'
            );

            $instance = wp_parse_args( (array) $instance, $defaults ); ?>

            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title / Intro:', 'simple') ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
            </p>


            <p>
                <textarea class="widefat" rows="5" cols="15" id="<?php echo $this->get_field_id( 'desc' ); ?>" name="<?php echo $this->get_field_name( 'desc' ); ?>"><?php echo $instance['desc']; ?></textarea>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'placeholder' ); ?>"><?php _e('Placeholder Text:', 'simple') ?></label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'placeholder' ); ?>" name="<?php echo $this->get_field_name( 'placeholder' ); ?>" value="<?php echo $instance['placeholder']; ?>" />
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'apiKey' ); ?>"><?php _e('API Key:', 'simple') ?> (<a href="https://us3.admin.mailchimp.com/account/api/">API Key</a>)</label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'apiKey' ); ?>" name="<?php echo $this->get_field_name( 'apiKey' ); ?>" value="<?php echo $instance['apiKey']; ?>" />
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'listID' ); ?>"><?php _e('List ID:', 'simple') ?> (<a href="https://us3.admin.mailchimp.com/lists/">Under Settings->List Name &amp; Defaults</a>)</label>
                <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'listID' ); ?>" name="<?php echo $this->get_field_name( 'listID' ); ?>" value="<?php echo $instance['listID']; ?>" />
            </p>

            <?php

        } // form


    } // class

    if ( !function_exists('simple_newsletter_shortcode') ) {

        function simple_newsletter_shortcode( $atts ) {

            extract( shortcode_atts( array(
                'before'    => '',
                'after'     => '',
                'wrapper'   => 'div',
                'class'     => '',
            ), $atts ) );

            $output = '';

            $output .= $before;

            $output .= '<'.$wrapper.' class="'.$class.'">';

            // $output .= new Simple_Newsletter_Widget;

            $output .= '</'.$wrapper.'>';

            $output .= $after;

            return $output;

        }

        add_shortcode('newsletter', 'simple_newsletter_shortcode');

    }


endif;