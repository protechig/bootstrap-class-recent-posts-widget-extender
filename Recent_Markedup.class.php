<?php
class Recent_Markedup extends WP_Widget {
    function __construct() {
        $widget_ops = array(
            'classname'     => 'widget_recent_markedup',
            'description'   => __('Marked up recent posts area')
        );

        parent::__construct('recent-markedup', __('Recent Marked Up'), $widget_ops);

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    function widget($args, $instance) {
        $cache = array();
        if (!$this->is_preview()) {
            $cache = wp_cache_get('widget_recent_markedup_posts', 'widget');
        }

        if (!is_array($cache)) {
            $cache = array();
        }

        if (!isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        if (isset($cache[$args['widget_id']])) {
            echo $cache[$args['widget_id']];
            return;
        }

        ob_start();
        extract($args);

        $title = (!empty($instance['title'])) ? $instance['title'] : __('Recent Posts');

        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        $number = (!empty($instance['number'])) ? absint($instance['number']) : 5;
        if (!$number) {
            $number = 5;
        }

        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

        $r = new WP_Query(apply_filters('widget_posts_args', array(
            'posts_per_page'        => 4,
            'no_found_rows'         => true,
            'post_status'           => 'publish',
            'ignore_sticky_posts'   => true
        )));
?>
        <section id="recent-posts">
            <div class="container">
                <?php if ($r->have_posts()): ?>
                <?php if ($title): ?>
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="section-title"><?php echo $title; ?></h3>
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <ul>
                        <?php while ($r->have_posts()): $r->the_post(); ?>
                        <li class="col-sm-6 col-md-3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                        <?php endwhile; ?>
                    </ul>
                </div>    
                <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>
        </section>
<?php
        if (!$this->is_preview()) {
            $cache[$args['widget_id']] = ob_get_flush();
            wp_cache_set('widget_recent_markedup_posts', $cache, 'widget');
        } else {
            ob_end_flush();
        }
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['number'] = (int) $new_instance['number'];
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        $this->flush_widget_cache();

        $alloptions = wp_cache_get('alloptions', 'options');
        if (isset($alloptions['widget_recent_markedup_entries'])) {
            delete_option('widget_recent_markedup_entries');
        }

        return $instance;
    }

    function flush_widget_cache() {
        wp_cache_delete('widget_recent_markedup_posts', 'widget');
    }

    function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_date); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
            <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Display post date?'); ?></label>
        </p>
<?php
    }
}
