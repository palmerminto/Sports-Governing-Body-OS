<?php
/*
Plugin Name: Governing Body OS
Description: WordPress plugin for the Governing Body OS
Version: 1.0.0
Author: Chris Palmer
*/

if (!defined('ABSPATH')) {
    exit;
}

// Register the widget
function governing_body_os_team_list_widget() {
    register_widget('My_SportsPress_Team_List_Widget');
}
add_action('widgets_init', 'governing_body_os_team_list_widget');

// Define the widget class
class My_SportsPress_Team_List_Widget extends WP_Widget {

    // Construct the widget
    public function __construct() {
        $widget_ops = array(
            'classname' => 'governing_body_os_team_list_widget',
            'description' => __('A widget to display a list of Teams.', 'governing-body-os-widget'),
        );
        parent::__construct('governing_body_os_team_list_widget', __('Team List', 'governing-body-os-widget'), $widget_ops);
    }

    // Display the widget output
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // Handle pagination
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        // Custom query to fetch the teams
        $query_args = array(
            'post_type'      => 'sp_team',
            'posts_per_page' => !empty($instance['number']) ? absint($instance['number']) : 5,
            'paged'          => $paged,
        );

        $teams = new WP_Query($query_args);

        if ($teams->have_posts()) {
            echo '<div class="elementor-widget-wrap elementor-element-populated sc_recent_news">';
            
            while ($teams->have_posts()) {
                $teams->the_post();

                // Get the team site URL (assuming it's stored in a custom field called 'team_site_url')
                $team_site_url = get_post_meta(get_the_ID(), 'team_site_url', true);

                // Only display the team if the 'team_site_url' is defined
                if ($team_site_url) {
                    // Get the team logo
                    $team_logo = get_the_post_thumbnail(null, 'thumbnail'); // Assuming the logo is the post thumbnail

                    // Get the team excerpt
                    $team_excerpt = get_the_excerpt();

                    $team_permalink = esc_url($team_site_url);

                    echo '<article class="post_item post_layout_news-excerpt post_format_standard sc_recent_news_style_news-excerpt">';
                    
                    // Featured image and link
                    echo '<div class="post_featured with_thumb hover_simple">';
                    if ($team_logo) {
                        echo '<div class="team-logo">' . $team_logo . '</div>';
                    }
                    echo '<a href="' . esc_url($team_permalink) . '" class="icons" aria-hidden="true"></a>';
                    echo '<div class="mask"></div>';
                    echo '</div>'; // .post_featured

                    // Post content
                    echo '<div class="post_body">';
                    echo '<div class="post_header entry-header">';
                    echo '<h4 class="post_title entry-title"><a href="' . esc_url($team_permalink) . '" rel="bookmark">' . get_the_title() . '</a></h4>';
                    echo '</div>'; // .post_header
                    echo '<div class="post_content entry-content">';
                    if ($team_excerpt) {
                        echo '<p>' . $team_excerpt . '</p>';
                    }
                    echo '<a href="' . esc_url($team_permalink) . '" class="sc_icons_item_button button" style="margin-top: 20px;" target="_blank">Find Out More</a>';
                    echo '</div>'; // .post_content
                    echo '</div>'; // .post_body

                    echo '</article>';
                }
            }

            echo '</div>'; // .elementor-widget-wrap

            // Pagination links
            $pagination_args = array(
                'total'   => $teams->max_num_pages,
                'current' => $paged,
                'format'  => '?paged=%#%',
            );
            echo paginate_links($pagination_args);
        } else {
            echo '<p>' . __('No teams found.', 'governing-body-os-widget') . '</p>';
        }

        wp_reset_postdata();

        echo $args['after_widget'];
    }

    // Handle widget settings form
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Team List', 'governing-body-os-widget');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'governing-body-os-widget'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of teams to show:', 'governing-body-os-widget'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    // Save widget settings
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;

        return $instance;
    }
}
