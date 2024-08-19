<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class My_SportsPress_Team_List_Widget extends WP_Widget {

    // Constructor method for setting up the widget
    public function __construct() {
        $widget_ops = array(
            'classname' => 'my_sportspress_team_list_widget',
            'description' => __('A widget to display a list of teams from SportsPress.', 'my-sportspress-extension'),
        );
        parent::__construct('my_sportspress_team_list_widget', __('SportsPress Team List', 'my-sportspress-extension'), $widget_ops);
    }

    // The widget() method handles the output of the widget on the frontend
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        // Custom query to fetch the teams from SportsPress
        $query_args = array(
            'post_type'      => 'sp_team', // SportsPress teams post type
            'posts_per_page' => !empty($instance['number']) ? absint($instance['number']) : 5,
            'order'          => 'ASC',
            'orderby'        => 'title',
        );

        $teams = new WP_Query($query_args);

        if ($teams->have_posts()) {
            echo '<ul class="sportspress-team-list">';
            while ($teams->have_posts()) {
                $teams->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('No teams found.', 'my-sportspress-extension') . '</p>';
        }

        wp_reset_postdata();

        echo $args['after_widget'];
    }

    // The form() method generates the widget settings form in the admin dashboard
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Team List', 'my-sportspress-extension');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'my-sportspress-extension'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php _e('Number of teams to show:', 'my-sportspress-extension'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    // The update() method saves the widget settings when the form is submitted
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;

        return $instance;
    }
}
