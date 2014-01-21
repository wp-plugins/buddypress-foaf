<?php

/**
 * Plugin Name: Buddypress Friend of a Friend (FOAF)
 * Plugin URI: http://ifs-net.de
 * Description: Includes information into other user profiles that tells you the "social path" to the visited profile
 * Version: 1.0
 * Author: Florian Schiessl
 * Author URI: http://ifs-net.de
 * License: GPL2
 * Text Domain: buddypress-foaf
 * Domain Path: /languages/
 */
// recreate pot file? excute this in the plugin's directory  
// xgettext --language=PHP --from-code=utf-8 --keyword=__ *.php -o languages/buddypressfoaf.pot
// Load translations and text domain
add_action('init', 'buddypressfoaf_load_textdomain');

function buddypressfoaf_load_textdomain() {
    load_plugin_textdomain('buddypressfoaf', false, dirname(plugin_basename(__FILE__)) . "/languages/");
}

add_action('bp_before_member_header', 'buddypressfoaf_action');
add_shortcode('buddypressfoaf_show_potential_friends', 'buddypressfoaf_show_potential_friends');

function buddypressfoaf_action() {
    $current_user = wp_get_current_user();
    $noConnectionFound = false;
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
                        if (!$result = $wpdb->get_results($wpdb->prepare($query))) {
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
            }
        } else {
            $output.= "Go out and find some friends ;-)";
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
    $ii++;
    foreach ($connection as $id) {
        $i++;
        $actualUser = new BP_Core_User($id);
        // get avatar
        $content.= $actualUser->avatar_thumb;

        if ($i != count($connection)) {
            $content.= ' <span style="float:left;padding-top: 20px;margin-left:-15px;font-size: 22px;"/>&harr;</span>';
        }
    }
    return $content;
}

function buddypressfoaf_show_potential_friends() {

    $current_user = wp_get_current_user();

    // get friends
    $friends = friends_get_friend_user_ids($current_user->ID);

    // get friends of friends
    global $wpdb;
    global $bp;
    $sqlPartExcludeMeAndMyFriends = implode(',', array_merge(array($current_user->ID), $friends));
    $query = '
        SELECT u.ID, u.user_login, count(nested.id) as commonContacts
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

        INNER JOIN '.$wpdb->users.' as u
        ON u.ID = nested.id
        GROUP BY nested.id
        HAVING commonContacts > 1 
        ';

    // Random friends of your friends
    $result = $wpdb->get_results($wpdb->prepare($query . " ORDER BY RAND() LIMIT 10"));
    $output.="<h3>" . __('Random friends of your friends you might know', 'buddypressfoaf') . "</h3>";
    if ($result) {
        foreach ($result as $obj) {
            // get avatar
            $i++;
            $actualUser = new BP_Core_User($obj->ID);
            $output.= '<div style="float:left; text-align: center; margin-bottom: 10px;"><a href="' . $actualUser->user_url . '">' . $actualUser->avatar . '<br /><small>' . $actualUser->profile_data['user_login'] . '</small></a><br />
                ' . $obj->commonContacts . ' ' . __('common contacts', 'buddypressfoaf') . '</div>';
        }
        //print "<pre>" . var_dump($usersWithCommonFriends);
    } else {
        $output.= "<p>" . __('No friends found. Search for some users, add them as friends and come back to this page!', 'buddypressfoaf') . "</p>";
    }
    $output.='<br style="clear:both">';
    $output.='<p><a href="' . get_permalink() . '?' . time() . '">' . __('You want to see more? Reload this page!', 'buddypressfoaf') . '</a></p>';

    // now we will show the top ten
    $result = $wpdb->get_results($wpdb->prepare($query . " ORDER BY commonContacts DESC LIMIT 10"));
    $output.="<h3>" . __('You have most common friends with these users', 'buddypressfoaf') . "</h3>";
    if ($result) {
        foreach ($result as $obj) {
            // get avatar
            $actualUser = new BP_Core_User($obj->ID);
            $output.= '<div style="float:left; text-align: center; margin-bottom: 10px;"><a href="' . $actualUser->user_url . '">' . $actualUser->avatar . '<br /><small>' . $actualUser->profile_data['user_login'] . '</small></a><br />
                ' . $obj->commonContacts . ' ' . __('common contacts', 'buddypressfoaf') . '</div>';
        }
    } else {
        $output.= "<p>" . __('No friends found. Search for some users, add them as friends and come back to this page!', 'buddypressfoaf') . "</p>";
    }

    $output.='<br style="clear:both">';

    return $output;
}

