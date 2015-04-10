<?php
/**
 * Plugin Name: Buddypress Friend of a Friend (FOAF)
 * Plugin URI: http://ifs-net.de
 * Description: Includes information into other user profiles that tells you the "social path" to the visited profile. It also includes a widget showing random friends of a user's friends to increase networking at your website
 * Version: 2.4
 * Author: Florian Schiessl
 * Author URI: http://ifs-net.de
 * License: GPL2
 * Text Domain: buddypress-foaf
 * Domain Path: /languages/
 */
// recreate pot file? excute this in the plugin's directory  
// xgettext --language=PHP --from-code=utf-8 --keyword=__ --keyword=_e *.php -o languages/buddypressfoaf.pot
// Load translations and text domain
add_action('init', 'buddypressfoaf_load_textdomain');

/**
 * This function just loads language files
 */
function buddypressfoaf_load_textdomain() {
    load_plugin_textdomain('buddypressfoaf', false, dirname(plugin_basename(__FILE__)) . "/languages/");
}

add_action('bp_before_member_header', 'buddypressfoaf_action');
add_shortcode('buddypressfoaf_show_potential_friends', 'buddypressfoaf_show_potential_friends');

/**
 * This function displays information if you know a visited profile via some friends
 * @global type $bp
 * @global type $wpdb
 */
function buddypressfoaf_action() {
    $current_user = wp_get_current_user();
    $noConnectionFound = false;
    $output = "";
    // Do nothing if the own profile is viewed or viewing user is not logged in
    if (is_user_logged_in() && ($current_user->ID != bp_displayed_user_id())) {
        // User A visits User B
        $friendsA = friends_get_friend_user_ids($current_user->ID);
        if (count($friendsA) > 0) {
            // A has friends, we can continue. Does B have some friends?
            $friendsB = friends_get_friend_user_ids(bp_displayed_user_id());
            if (count($friendsB) > 0) {
                // Maybe A is a friend of B?
                if (in_array($current_user->ID, $friendsB)) {
                    $output.=buddypressfoaf_output(array(array($current_user->ID, bp_displayed_user_id())));
                } else {
                    // A <=> U1 <=> B?
                    $success = false;
                    $sharedFriendsAxB = array_intersect($friendsA, $friendsB);
                    if (count($sharedFriendsAxB) > 0) {
                        $args = array();
                        foreach ($sharedFriendsAxB as $id) {
                            $args[] = array($current_user->ID, $id, bp_displayed_user_id());
                        }
                        $output.=buddypressfoaf_output($args);
                    } else {
                        // We need a connection between A's friends and B's friends
                        // Buddypress API won't help us now...
                        $friendsA_imploded = implode(', ', $friendsA);
                        $friendsB_imploded = implode(', ', $friendsB);
                        //$bp_tool = new ;
                        $friendsComp = new BP_Friends_Component();

                        global $bp;
                        $query = "
                            SELECT DISTINCT * FROM `" . $bp->friends->table_name . "` 
                            WHERE ( `initiator_user_id` in ( " . $friendsA_imploded . ")
                            AND `friend_user_id` in ( " . $friendsB_imploded . ") 
                            AND `is_confirmed` = 1 )
                            OR ( `friend_user_id` in ( " . $friendsA_imploded . ") 
                            AND `initiator_user_id` in (" . $friendsB_imploded . ") 
                            AND `is_confirmed` = 1 )
                            ORDER BY RAND() LIMIT 1";
                        // Now we need to know if the FOUND initiator_user_id is friend of friendA or not
                        // if not we have to reverse the values
                        global $wpdb;
                        if (!$result = $wpdb->get_results($query)) {
                            $noConnectionFound = true;
                        } else {
                            // A <=> U1 <=> U2 <=> B?
                            // initiator_id is a friend of A and friend_id is friend of B
                            $sharedFriendAxxB = $result[0];
                            if (in_array($sharedFriendAxxB->initiator_user_id, $friendsA) && in_array($sharedFriendAxxB->friend_user_id, $friendsB)) {
                                $args = array(array($current_user->ID, $sharedFriendAxxB->initiator_user_id, $sharedFriendAxxB->friend_user_id, bp_displayed_user_id()));
                                $output.= buddypressfoaf_output($args);
                            } else if (in_array($sharedFriendAxxB->initiator_user_id, $friendsB) && in_array($sharedFriendAxxB->friend_user_id, $friendsA)) {
                                $args = array(array($current_user->ID, $sharedFriendAxxB->friend_user_id, $sharedFriendAxxB->initiator_user_id, bp_displayed_user_id()));
                                $output.= buddypressfoaf_output($args);
                            } else {
                                // if nothing matches something went totally wrong..
                                $output.=__('Internal Error, Code:', 'buddypressfoaf') . ' x04';
                            }
                        }
                    }
                }
            } else {
                $noConnectionFound = true;
            }
        }
        if ($noConnectionFound) {
            $output.=__('No connection found', 'buddypressfoaf');
        }
        $title = __('Your connection to this user', 'buddypressfoaf');
        echo '<div style="float:right;">' . $title . ':<br />' . $output . '</div>';
    }
}

/**
 * This function displays the link between A and B
 * Argument is an array (all connections between A and B) of arrays (A,...,...,B)
 * @param type $args
 * @return string
 */
function buddypressfoaf_output($args) {
    $connections = count($args);
    $length = count($args[0]);
    // randomize array
    $randomNumber = rand(0, count($args) - 1);
    $connection = $args[$randomNumber];
    // show single connection
    $i = 0;
    $ii = 0;
    $ii++;
    $content = "";
    foreach ($connection as $id) {
        $i++;
        $actualUser = new BP_Core_User($id);
        // get avatar
        //$content.= $actualUser->avatar_thumb;
        $userdata = get_userdata($id);
        $data = $userdata->data;
        $content.= '<a href="' . bp_core_get_userlink($data->ID, false, true) . '">' . bp_core_fetch_avatar(array('object' => 'user', 'type' => 'thumb', 'item_id' => $data->ID));

        if ($i != count($connection)) {
            $content.= ' <span style="float:left;padding-top: 20px;margin-left:-15px;font-size: 22px;"/>&harr;</span>';
        }
    }
    return $content;
}

/**
 * This shortcode displays friends of friends a user might now
 * 
 * @global type $wpdb
 * @global type $bp
 * @return string
 */
function buddypressfoaf_show_potential_friends() {

    $current_user = wp_get_current_user();

    // get friends
    $friends = friends_get_friend_user_ids($current_user->ID);

    // get friends of friends
    global $wpdb;
    global $bp;
    $sqlPartExcludeMeAndMyFriends = implode(',', array_merge(array($current_user->ID,1), $friends));
    $query = '
        SELECT u.ID, u.user_nicename, count(nested.id) as commonContacts
        FROM (
            SELECT friend_user_id as id
            FROM ' . $bp->friends->table_name . ' 
            WHERE initiator_user_id IN (' . implode(', ', $friends) . ')
            AND friend_user_id NOT IN (' . $sqlPartExcludeMeAndMyFriends . ')
            AND  is_confirmed = 1

            UNION ALL

            SELECT initiator_user_id as id
            FROM ' . $bp->friends->table_name . '
            WHERE friend_user_id IN (' . implode(', ', $friends) . ')
            AND initiator_user_id NOT IN (' . $sqlPartExcludeMeAndMyFriends . ')
            AND  is_confirmed = 1
            ) AS nested

        INNER JOIN ' . $wpdb->users . ' as u
        ON u.ID = nested.id
        GROUP BY nested.id
        HAVING commonContacts > 1 
        ';

    // Random friends of your friends
    $result = $wpdb->get_results($query . " ORDER BY RAND() LIMIT 10");
    $output.="<h3>" . __('Random friends of your friends you might know', 'buddypressfoaf') . "</h3>";
    if ($result) {
        foreach ($result as $obj) {
            // get avatar
            $i++;
            $actualUser = new BP_Core_User($obj->ID);
            $output.= '<div style="float:left; text-align: center; margin-bottom: 10px;"><a href="' . $actualUser->user_url . '">' . $actualUser->avatar . '</a><br/>' . bp_core_get_userlink($obj->ID) . '<br />
                ' . $obj->commonContacts . ' ' . __('common contacts', 'buddypressfoaf') . '<br />' . bp_get_add_friend_button($actualUser->id) . '</div>';
        }
        //print "<pre>" . var_dump($usersWithCommonFriends);
    } else {
        $output.= "<p>" . __('No friends found. Search for some users, add them as friends and come back to this page!', 'buddypressfoaf') . "</p>";
    }
    $output.='<br style="clear:both">';
    $output.='<p><a href="' . get_permalink() . '?' . time() . '">' . __('You want to see more? Reload this page!', 'buddypressfoaf') . '</a></p>';

    // now we will show the top ten
    $result = $wpdb->get_results($query . " ORDER BY commonContacts DESC LIMIT 10");
    $output.="<h3>" . __('You have most common friends with these users', 'buddypressfoaf') . "</h3>";
    if ($result) {
        foreach ($result as $obj) {
            // get avatar
            $actualUser = new BP_Core_User($obj->ID);
            $output.= '<div style="float:left; text-align: center; margin-bottom: 10px;"><a href="' . $actualUser->user_url . '">' . $actualUser->avatar . '</a><br/>' . bp_core_get_userlink($obj->ID) . '<br />
                ' . $obj->commonContacts . ' ' . __('common contacts', 'buddypressfoaf') . '</div>';
        }
    } else {
        $output.= "<p>" . __('No friends found. Search for some users, add them as friends and come back to this page!', 'buddypressfoaf') . "</p>";
    }

    $output.='<br style="clear:both">';

    return $output;
}

add_action('widgets_init', 'buddypressfoaf_widget_random');

function buddypressfoaf_widget_random() {
    register_widget('BuddypressFOAF_Widget_Random');
}

class BuddypressFOAF_Widget_Random extends WP_Widget {

    function BuddypressFOAF_Widget_Random() {
        $widget_ops = array('classname' => 'buddypressfoaf', 'description' => __('A widget that displays a random friend of a friend', 'buddypressfoaf'));

        $control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'buddypressfoaf-widget-random');

        $this->WP_Widget('buddypressfoaf-widget-random', __('Buddypress FOAF (random)', 'buddypressfoaf'), $widget_ops, $control_ops);
    }

    function widget($args, $instance) {
        extract($args);

        //Our variables from the widget settings.
        $title = apply_filters('widget_title', $instance['title']);
        $content_before = $instance['content_before'];
        $url_potential_friends = $instance['url_potential_friends'];

        echo $before_widget;

        // Display the widget title 
        if ($title)
            echo $before_title . $title . $after_title;

        // Display content below main output
        if ($content_before)
            echo $content_before;


        // Main content
        $current_user = wp_get_current_user();

        // get friends
        $friends = friends_get_friend_user_ids($current_user->ID);

        // get friends of friends
        global $wpdb;
        global $bp;

        if (count($friends) > 0) {
            $sqlPartExcludeMeAndMyFriends = implode(',', array_merge(array($current_user->ID,1), $friends));
            // build SQL query with friends of friends that have been active in the last 6 months of the current user
            $query = '
                SELECT u.ID, u.user_nicename, count(nested.id) as commonContacts, m.meta_value as last_activity
                FROM (
                    SELECT friend_user_id as id
                    FROM ' . $bp->friends->table_name . ' 
                    WHERE initiator_user_id IN (' . implode(', ', $friends) . ')
                    AND friend_user_id NOT IN (' . $sqlPartExcludeMeAndMyFriends . ')
                    AND  is_confirmed = 1

                    UNION ALL

                    SELECT initiator_user_id as id
                    FROM ' . $bp->friends->table_name . '
                    WHERE friend_user_id IN (' . implode(', ', $friends) . ')
                    AND initiator_user_id NOT IN (' . $sqlPartExcludeMeAndMyFriends . ')
                    AND  is_confirmed = 1
                    ) AS nested

                INNER JOIN ' . $wpdb->users . ' as u
                ON u.ID = nested.id
                INNER JOIN ' . $wpdb->usermeta . ' as m
                ON m.user_id = u.ID
                WHERE m.meta_key = "last_activity"
                AND m.meta_value > "' . date("Y-m-d 00:00:00", (time() - 60 * 60 * 24 * 30 * 6)) . '"
                GROUP BY nested.id
                HAVING commonContacts > 2 
                ';

            // Random friend of friend that have been active in the last 6 months
            $result = $wpdb->get_results($query . " ORDER BY RAND() LIMIT 1");
        }
        if (!$result) {
            // No user was found or user does not have friends. We will now take a random user to proceed

            $query = '
            SELECT u.ID, u.user_nicename, 0 as commonContacts
            FROM ' . $wpdb->users . ' as u
            INNER JOIN ' . $wpdb->usermeta . ' as m
            ON m.user_id = u.ID
            WHERE m.meta_key = "last_activity"
            AND m.meta_value > "' . date("Y-m-d 00:00:00", (time() - 60 * 60 * 24 * 30 * 6)) . '"
            AND u.ID != ' . $current_user->ID . '
            ORDER BY RAND()
            LIMIT 1
            ';
            $result = $wpdb->get_results($query);
        }
        // friends or random user found, take them!
        foreach ($result as $obj) {
            // get avatar
            $i++;
            $actualUser = new BP_Core_User($obj->ID);
            $output.= '<div style="width: 150px;margin-left: auto; margin-right: auto; text-align:center;"><div><a href="' . $actualUser->user_url . '">' . $actualUser->avatar . '<br style="clear:both;"/></a>
                ' . bp_core_get_userlink($obj->ID) . '<br/>' . $obj->commonContacts . ' ' . __('common contacts', 'buddypressfoaf') . '<br />' . bp_get_add_friend_button($actualUser->id) . '</div></div>';
        }
        $output.='<br style="clear:both">';

        echo $output;

        // Display content below main output
        if ($url_potential_friends)
            echo '<a href="' . $url_potential_friends . '">' . __('Show more users I might know!', 'buddypressfoaf') . '</a>';

        echo $after_widget;
    }

    //Update the widget 

    function update($new_instance, $old_instance) {
        $instance = $old_instance;

        //Strip tags from title and name to remove HTML 
        $instance['title'] = strip_tags($new_instance['title']);

        // content may contain html
        $instance['content_before'] = strip_tags($new_instance['content_before']);
        $instance['url_potential_friends'] = strip_tags($new_instance['url_potential_friends']);

        return $instance;
    }

    function form($instance) {

        //Set up some default widget settings.
        $defaults = array('title' => __('Do you know this user?', 'buddypressfoaf'), 'url_potential_friends' => '', 'content_before' => '');
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'buddypressfoaf'); ?>:</label>
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('content_before'); ?>"><?php _e('Optional content to be shown before output', 'buddypressfoaf'); ?>:</label>
            <input id="<?php echo $this->get_field_id('content_before'); ?>" name="<?php echo $this->get_field_name('content_before'); ?>" value="<?php echo $instance['content_before']; ?>" style="width:100%;" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('url_potential_friends'); ?>"><?php $txt = _e('URL (optional) for page that shows more potential friends (use the shortcode at this page)', 'buddypressfoaf'); ?>:</label>
            <input id="<?php echo $this->get_field_id('url_potential_friends'); ?>" name="<?php echo $this->get_field_name('url_potential_friends'); ?>" value="<?php echo $instance['url_potential_friends']; ?>" style="width:100%;" />
        </p>
        <?php
    }

}
?>