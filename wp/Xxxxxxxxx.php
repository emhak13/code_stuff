<?php
/**
 * @package Xxxxxxxx
 */
/*
Plugin Name: Xxxxxxxx
Plugin URI: Xxxxxxxxxxxxxxxxxxxx
Description: Xxxxxxxx allows a site to display property listings for a realtor association
Version: 1.4
Author: SavvyCard LLC
Author URI: Xxxxxxxxxxxxxxxxxxxx
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi !  It\'s  a plugin, not much It can do when called directly.';
	exit;
}

define('Xxxxxxxx', '1.0.0');
define('Xxxxxxxx_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('BASE_URL', get_bloginfo('url'));
require_once( ABSPATH . "wp-includes/pluggable.php" );
require_once( ABSPATH . "wp-includes/query.php" );

session_start();
include_once dirname( __FILE__ ) . '/config.php';
include_once dirname( __FILE__ ) . '/widget.php';

if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

$adsr_db_version = "1.0";
global $wpdb;
$table_name = $wpdb->prefix . "Xxxxxxxx_data";
$settings_table_name = $wpdb->prefix . "Xxxxxxxx_settings";
$results_api_key = $wpdb->get_results("SELECT data FROM {$settings_table_name} WHERE id = 1 ",ARRAY_N);
if(!empty($results_api_key)){
	$data_api_key = json_decode($results_api_key[0][0],true);
	  $api_key = $data_api_key['api_key_url'] ;
}

require dirname( __FILE__ ) .'/savvy_idx-update-checker/plugin-update-checker.php';
$MyUpdateChecker = PucFactory::buildUpdateChecker(
	UPDATE_URL.'/idx_plugin_updates/?action=get_metadata&key=1&slug=savvy_idx&api_key='.$api_key,
	__FILE__,
	'savvy_idx'
);

function idx_update_checks($queryArgs) {
	$settings = get_option('my_plugin_settings');
	if ( !empty($settings['api_key']) ) {
		$queryArgs['api_key'] = $settings['api_key'];
	}
	return $queryArgs;
}
$MyUpdateChecker->addQueryArgFilter('idx_update_checks');


function savvy_idx_install() {

	require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	global $wpdb;
	$table_name = $wpdb->prefix . "Xxxxxxxx_data";
	$settings_table_name = $wpdb->prefix . "Xxxxxxxx_settings";
    $messanger_table_name = $wpdb->prefix."Xxxxxxxx_messages";
	$sql1 = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
	 id mediumint(9) NOT NULL AUTO_INCREMENT,
	 user_id mediumint(9) NOT NULL,
	 type varchar(50) NOT NULL,
	 name tinytext NOT NULL,
	 data text NOT NULL,
	 UNIQUE KEY id (id)
	);";
	$sql2 = " CREATE TABLE IF NOT EXISTS " . $settings_table_name . " (
	 id mediumint(9) NOT NULL AUTO_INCREMENT,
	 name varchar(50) NOT NULL,
	 data text NOT NULL,
	 UNIQUE KEY id (id)
	);";
    $sql3 = "CREATE TABLE IF NOT EXISTS ".$messanger_table_name." (
    id int(11) NOT NULL AUTO_INCREMENT,
    sender_id int(11) NOT NULL,
    receiver_id int(11) NOT NULL,
    subject varchar(255) NOT NULL,
    message text NOT NULL,
    folder int(1) NOT NULL DEFAULT '1',
    sender_deleted int(1) NOT NULL DEFAULT '0',
    receiver_deleted int(1) NOT NULL DEFAULT '0',
    created_at int(14) NOT NULL,
    readed_at int(11) NOT NULL,
    UNIQUE KEY id (id)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8  ;";
    
$sql4 = "ALTER TABLE ".$messanger_table_name."
 ADD PRIMARY KEY (id), ADD KEY sender_id (sender_id,receiver_id,folder,sender_deleted), ADD KEY created_at (created_at), ADD KEY readed_at (readed_at), ADD KEY receiver_deleted (receiver_deleted), ADD KEY sender_deleted (sender_deleted);";
	$wpdb->query($sql1);
	$wpdb->query($sql2);
    $wpdb->query($sql3);
    $wpdb->query($sql4);
	add_option('savvy_idx_do_activation_redirect', true);

}

register_activation_hook(__FILE__, 'savvy_idx_install');

add_action('admin_init', 'idx_plugin_redirect');
function idx_plugin_redirect() {
	if (get_option('savvy_idx_do_activation_redirect', false)) {
		delete_option('savvy_idx_do_activation_redirect');
		wp_redirect('admin.php?page=Xxxxxxxx_API_config');
	}
}
function savvy_idx_uninstall() {
	global $wpdb;
	$table_name = $wpdb->prefix . "Xxxxxxxx_data";
	$settings_table_name = $wpdb->prefix . "Xxxxxxxx_settings";
	$sql = "DROP TABLE IF EXISTS " . $table_name.";";
	$sql1 = "DROP TABLE IF EXISTS " . $settings_table_name.";";
	$wpdb->query($sql);
	$wpdb->query($sql1);
	register_uninstall_hook(__FILE__, 'savvy_idx_uninstall');

}

register_activation_hook(__FILE__, 'savvy_idx_install');

function savvy_idx_init() {
	wp_enqueue_style('savvy_idx-admin-ui-css', plugins_url('savvy_IDX.css', __FILE__));
	wp_enqueue_style('savvy_idx-css',
		'//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/flick/jquery-ui.min.css',
		false,
		1,
		false
	);
	wp_enqueue_style('dataTable-css', plugins_url('jquery.dataTables.css', __FILE__));
    wp_enqueue_style('bootstrap-min-css', API_URL.'/toolv2/css?pr=1&p_count=0');
//    wp_enqueue_style('bootstrap-theme-min-css', '//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-tabs');
	//wp_enqueue_script('jquery_min', 'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' );
    wp_enqueue_script('bootstrap-min','//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js');
	wp_enqueue_script('idx_image', plugins_url('js/idx_image.js',__FILE__) );
	wp_enqueue_script('dataTable', plugins_url('js/jquery.dataTables.min.js',__FILE__) );
	wp_enqueue_media();

}

add_action('init', 'savvy_idx_init');
if(!is_user_logged_in()){
    setcookie('log_in',0,0,'/');
} else{
    setcookie('log_in',1,0,'/');
}


add_action('wp_ajax_nopriv_idx_login_action', 'idx_login_action');
add_action('wp_ajax_idx_login_action', 'idx_login_action');
function idx_login_action(){
    $idx_username=isset ($_POST['idx_username']) ? $_POST['idx_username'] : "";
    $idx_pass=isset ($_POST['idx_pass']) ? $_POST['idx_pass'] : "";
    if(!empty($idx_username) && !empty($idx_pass) ){
        if ( !function_exists('wp_idx_authenticate') ) :
            function wp_idx_authenticate($username, $password) {
                $username = sanitize_user($username);
                $password = trim($password);

                $user = apply_filters('authenticate', null, $username, $password);

                if ( $user == null ) {
                  $user = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));
                } elseif ( get_user_meta( @$user->ID, 'has_to_be_activated', true ) != false ) {
                    $user = new WP_Error('activation_failed', __('<strong>ERROR</strong>: User is not activated.'));
                }

                $ignore_codes = array('empty_username', 'empty_password');

                if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes) ) {
                    do_action('wp_login_failed', $username);
                }

                return $user;
            }
        endif;


        $user = wp_idx_authenticate( $idx_username, $idx_pass );
        if ( is_wp_error($user) ){
            echo $user->get_error_message();
        }else {
            $creds = array();
            $creds['user_login'] = $idx_username;
            $creds['user_password'] = $idx_pass;
            $creds['remember'] = false;
            $user_verify = wp_signon($creds,false);

            /*
            everything looks ok, we need:
            1. Transfer DB saved searches of registered user to cookies
            2. Combine saved searches from this PC to his own searches.
            */

            $user_id= $user_verify->ID;
            
            saved_searches_record_cookies($user_id);

            saved_searches_transfer_cookies_to_database($user_id);
          
            

        }


    }else {
        echo "Username and password cannot be blank";
    }
die();
}
    function saved_searches_transfer_cookies_to_database($user_id)
    {
              if(isset($_COOKIE['idx_search'])){
            
            delete_user_meta( $user_id, 'idx_search'); 
            // Now after all is transfered to cookies we need to combine them with saved searchs. Easyest way is to record cookie and delete saved searches.
            foreach ($_COOKIE['idx_search'] as $name => $value)
            {   
                date_default_timezone_set('UTC');
                $my_idx_search['name'] = $name;
                $my_idx_search['value'] = $value;
                $my_idx_search['time']  = date('l jS \of F Y h:i:s A');
                $searchData = json_encode(str_replace('"', '\\"', idx_parse_json($my_idx_search)));
                add_user_meta( $user_id, 'idx_search', $searchData );
            }



            
       }
    }

    function saved_searches_record_cookies($user_id)
    {
        $tenYears = 10*365*24*60*60;
        $data = get_user_meta($user_id, 'idx_search', false);
        foreach ($data as $key => $value) 
        {
            $partial_data = json_decode($value,true);
            
            $url = str_replace(' ', '%20', $partial_data['value']);
            $check_cookie = @$_COOKIE['idx_search'];
            if(empty($check_cookie) OR @$check_cookie[($partial_data['name'])]=='')// We need to be sure that cookie doesnt exist already and then record value. Repeatedly overwriten cookie gets broken...
                 {
                    //setcookie is replacing spaces with +, wich make errors later, so we need to use this instead...took me eternity to find the problem:)
                    setrawcookie('idx_search['.rawurlencode($partial_data['name']).']',$url,time()+$tenYears,'/');
                 }
        }
            
    }

add_action( 'template_redirect', 'wpse8170_activate_user' );
function wpse8170_activate_user() {

   global $wp_query, $wpdb;

  
    // Karen this wasnt working properly in your code...
    if(str_replace('activate', '', $_SERVER['REQUEST_URI'])!=$_SERVER['REQUEST_URI'] AND isset($_GET['key']) AND isset($_GET['user'])) {

        $user_id = filter_input( INPUT_GET, 'user', FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        
        if ( $user_id ) {

            $code = get_user_meta( $user_id, 'has_to_be_activated', true );

            if ( $code == filter_input( INPUT_GET, 'key' ) ) {
               delete_user_meta( $user_id, 'has_to_be_activated' );

            }
        }
    }
}


    add_action('wp_ajax_idx_delete_favorites', 'idx_delete_favorites');
    function idx_delete_favorites()
    {
        $user_id=get_current_user_id();
        $delete_id = isset ($_POST['idx_id']) ? $_POST['idx_id'] : "";
         $sysid = get_user_meta( $user_id, 'idx_favorites', true );
         $sysid = str_replace($delete_id.',', '' , $sysid);
         if($sysid=='""')
         {
            delete_user_meta( $user_id, 'idx_favorites');
         } else {
            update_user_meta( $user_id, 'idx_favorites', $sysid);
         }
         if(isset($_COOKIE['idx_favorites']))
         {
            foreach ($_COOKIE['idx_favorites'] as $name => $value)
            {

                if($value==$delete_id)
                {
                     print 'idx_favorites['.$name.']';
                setcookie('idx_favorites['.$name.']',0,time()-100000,'/');

                }
            }
         }

    }

add_action('wp_ajax_nopriv_idx_register_action', 'idx_register_action');
add_action('wp_ajax_idx_register_action', 'idx_register_action');
function idx_register_action(){

    $idx_username=isset ($_POST['idx_username']) ? $_POST['idx_username'] : "";
    $idx_pass=isset ($_POST['idx_pass']) ? $_POST['idx_pass'] : "";
    $idx_pass_confirm=isset ($_POST['idx_pass_confirm']) ? $_POST['idx_pass_confirm'] : "";
    $idx_terms_agree=isset ($_POST['idx_terms_agree']) ? $_POST['idx_terms_agree'] : "";
    $idx_Name=isset ($_POST['idx_Name']) ? $_POST['idx_Name'] : "";
    $idx_LName=isset ($_POST['idx_LName']) ? $_POST['idx_LName'] : "";
      if(!empty($idx_username) && !empty($idx_pass) && !empty($idx_pass_confirm) && $idx_terms_agree == true ){
        if($idx_pass ==  $idx_pass_confirm){

            $userdata = array(
                'user_login'  =>  $idx_username,
                'user_pass'   =>  $idx_pass  ,
                'first_name'  =>  $idx_Name,
                'last_name'   =>  $idx_LName,
                'user_url'    =>  'http://change_me.com',
                'user_email'  =>  $idx_username,
                'show_admin_bar_front' => false ,
                'role' => 'pending'
            );

            $user_id = wp_insert_user( $userdata ) ;

            $arrUserData = $_POST;

            unset($arrUserData['user_id']);
            unset($arrUserData['idx_register_action']);
            unset($arrUserData['idx_username']);
            unset($arrUserData['idx_pass']);
            unset($arrUserData['idx_pass_confirm']);
            unset($arrUserData['idx_terms_agree']);
            unset($arrUserData['idx_Name']);
            unset($arrUserData['idx_LName']);
            $strUserData = json_encode($arrUserData);

            if( $user_id && !is_wp_error($user_id) ) {
                add_user_meta( $user_id, 'userRegData', $strUserData , true );
                $code = sha1( $user_id . time() );
                $activation_link = add_query_arg( array( 'key' => $code, 'user' => $user_id ));
                add_user_meta( $user_id, 'has_to_be_activated', $code, true );
                $headers[] = 'From: <noreply@'.get_site_url().'>';
                wp_mail( $userdata['user_email'], 'Activation link for'.get_site_url(),'Welcome,'.$userdata['first_name'].' '.$userdata['last_name']. 'n/ CONGRATS!!! HERE IS YOUR ACTIVATION LINK : ' .get_site_url().'/activate'. $activation_link,$headers );
                echo "Your account is created, visit your mail to activate it!";
            } else {
                print $user_id->errors['existing_user_login'][0] ;
            }
        } else {
            echo "Passwords do not mutch";
        }
      } else{
          echo "Some fields are required";
          }
    die();
   }

    add_action('wp_ajax_delete_single_message', 'delete_single_message');
    function delete_single_message()
    {
        global $wpdb;
        $user_id=get_current_user_id();
        $message_id=isset ($_POST['message_id']) ? $_POST['message_id'] : "";
        $delete_type=isset ($_POST['delete_type']) ? $_POST['delete_type'] : "";
        $messages_table = $wpdb->prefix . "Xxxxxxxx_messages";
        if($delete_type == 'sender_deleted')
        {
            $sender_receiver = 'sender_id';

        } else {

            $sender_receiver = 'receiver_id';
        }

        $where = array(
            'id'             => $message_id,
            $sender_receiver => $user_id,
            );
        $update = array(
            $delete_type => 1,
            );
        
        // soft delete for sender or reciever
        $wpdb->update( $messages_table, $update, $where );
        // hard delete if both parties already soft delete it...
        $wpdb->delete( $messages_table , array( 'sender_deleted' => 1, 'receiver_deleted' => 1 ) );
        die;
    }

    add_action('wp_ajax_load_message', 'load_message');

    function load_message()
    {
        global $wpdb;
        $user_id=get_current_user_id();
        $message_id=isset ($_POST['message_id']) ? $_POST['message_id'] : "";
        $messages_table = $wpdb->prefix . "Xxxxxxxx_messages";
        $users_table = $wpdb->prefix . "users";
        $mail_query = "SELECT * FROM $messages_table
        LEFT JOIN wp_users  ON $messages_table.sender_id = $users_table.id
        WHERE receiver_id=$user_id 
        AND receiver_deleted = 0
        AND $messages_table.id=$message_id";
        $inbox_results=$wpdb->get_results( $mail_query,OBJECT);
        print '<b>Sender: '.$inbox_results[0]->display_name.'<br/>';
        print 'Date: '.date('m/d/Y h:i A',$inbox_results[0]->created_at).'<br/>';
        print 'Subject: '.$inbox_results[0]->subject.'</b><br/><br/>';
        print nl2br($inbox_results[0]->message);
        print '<div class="clearfix"></div><br/><br/><br/><div class="col-md-2"><button onclick="jQuery(\'#read'.$message_id.'\').remove();" type="button" class="btn btn-primary">Close</button></div><div class="col-md-2"><button onclick="jQuery(\'#read'.$message_id.'\').remove();deleteCurrent('.$message_id.')" type="button" class="btn btn-danger">Delete</button></div><div class="col-md-2"><button onclick="reply('.$message_id.')" type="button" class="btn btn-success">Reply</button></div>';
        die;
    }

    add_action('wp_ajax_reply_message', 'reply_message');

    function reply_message()
    {
        global $wpdb;
        $user_id=get_current_user_id();
        $message_id=isset ($_POST['message_id']) ? $_POST['message_id'] : "";
        $messages_table = $wpdb->prefix . "Xxxxxxxx_messages";
        $users_table = $wpdb->prefix . "users";
        $mail_query = "SELECT * FROM $messages_table
        LEFT JOIN wp_users  ON $messages_table.sender_id = $users_table.id
        WHERE receiver_id=$user_id 
        AND receiver_deleted = 0
        AND $messages_table.id=$message_id";
        $inbox_results=$wpdb->get_results( $mail_query,OBJECT);
        $html ='<br/>====================<br/>';
        $html.= 'Sender: '.$inbox_results[0]->display_name.'<br/>';
        $html.= 'Date: '.date('m/d/Y h:i A',$inbox_results[0]->created_at).'<br/>';
        $html.= 'Subject: '.$inbox_results[0]->subject.'<br/><br/>';
        $html.= $inbox_results[0]->message;
        $html = str_replace("\n", '', $html);
        $html = str_replace("\r", '', $html);
        $subject = 'Re:'.$inbox_results[0]->subject;
        print "jQuery('#message$message_id').val('".addslashes($html)."');jQuery('#subject$message_id').val('".addslashes($subject)."');";
        
        die;
    }

    add_action('wp_ajax_autocomplete', 'idx_autocomplete_callback');
    add_action('wp_ajax_nopriv_autocomplete', 'idx_autocomplete_callback');


    function idx_autocomplete_callback() {
        ob_clean();
       
        global $wpdb;
        $answer = array();
        $temporal = array();
        $user_id=get_current_user_id();
        $query=isset ($_GET['query']) ? $_GET['query'] : "";
        $users_table = $wpdb->prefix . "users";
        $mail_query = "SELECT ID as data,display_name as value FROM $users_table
        WHERE display_name LIKE '%$query%'";
        $autocomplete=$wpdb->get_results( $mail_query,ARRAY_A);


        $answer['query']=$query;
        $answer['suggestions'] = $autocomplete;
        exit(json_encode($answer));
        die();
    }

     add_action('wp_ajax_send_email', 'send_email');

    function send_email()
    {
        global $wpdb;
        $user_id=get_current_user_id();
        $receiver=isset ($_POST['receiver']) ? $_POST['receiver'] : "";
        $subject=isset ($_POST['subject']) ? stripslashes($_POST['subject']) : "";
        $message=isset ($_POST['message']) ? stripslashes($_POST['message']) : "";
        $messages_table = $wpdb->prefix . "Xxxxxxxx_messages";
        $users_table = $wpdb->prefix . "users";
        
        $values = array(
            'sender_id'     => $user_id,
            'receiver_id'   => $receiver,
            'subject'       => $subject,
            'message'       => $message,
            'created_at'    => time(),
            );
        $wpdb->insert($messages_table,$values);
        
       
        die;
    }

         add_action('wp_ajax_reply_action', 'reply_action');

    function reply_action()
    {
        global $wpdb;
        $user_id=get_current_user_id();
        $message_id=isset ($_POST['message_id']) ? $_POST['message_id'] : "";
        $subject=isset ($_POST['subject']) ? stripslashes($_POST['subject']) : "";
        $message=isset ($_POST['message']) ? stripslashes($_POST['message']) : "";
        $messages_table = $wpdb->prefix . "Xxxxxxxx_messages";
        $users_table = $wpdb->prefix . "users";
        $mail_query = "SELECT sender_id 
        FROM $messages_table
        WHERE $messages_table.id=$message_id";
        $receiver_id=$wpdb->get_results( $mail_query,OBJECT);
        $receiver_id = $receiver_id[0]->sender_id;
        $values = array(
            'sender_id'     => $user_id,
            'receiver_id'   => $receiver_id,
            'subject'       => $subject,
            'message'       => $message,
            'created_at'    => time(),
            );
        $wpdb->insert($messages_table,$values);
        
        print "jQuery('#reply$message_id').html('<td colspan=5 ><span style=\"color:green;\" >Message sent</span></td>');setTimeout('jQuery(\'#reply$message_id\').remove()',3000)";
        die;
    }

//if(isset($_POST['idx_update_user_action'])){
//add_action('wp_ajax_nopriv_idx_update_user_action', 'idx_update_user_action');
add_action('wp_ajax_idx_update_user_action', 'idx_update_user_action');
    function idx_update_user_action(){
    $update_user=wp_get_current_user();

    $idx_userID=$update_user->ID;
    $idx_username=$update_user->user_login;
    $idx_pass=$update_user->user_pass;
    $idx_pass_confirm=isset ($_POST['idx_pass_confirm']) ? $_POST['idx_pass_confirm'] : "";
    $idx_Name=isset ($_POST['idx_Name']) ? $_POST['idx_Name'] : "";
    $idx_LName=isset ($_POST['idx_LName']) ? $_POST['idx_LName'] : "";
    if(!empty($idx_username) && !empty($idx_pass_confirm)){

        if(wp_check_password($idx_pass_confirm, $idx_pass)){

            $userdata = array(
                'ID'          =>  $idx_userID,
                'first_name'  =>  $idx_Name,
                'last_name'   =>  $idx_LName,

            );

            $user_id = wp_update_user( $userdata ) ;

            $arrUserData = $_POST;

            unset($arrUserData['idx_update_user_action']);
            unset($arrUserData['idx_username']);
            unset($arrUserData['idx_pass_confirm']);
            unset($arrUserData['idx_Name']);
            unset($arrUserData['idx_LName']);
            $strUserData = json_encode($arrUserData);

            if( !is_wp_error($user_id) ) {
                update_user_meta( $user_id, 'userRegData', $strUserData );
               // echo "Data was updated successfully!";

            }else {
                echo "The data can't be updated, try again!";
            }
        }else {
            echo "Password is wrong!";
        }
    }
die();
}


//add_action('wp_ajax_nopriv_idx_change_user_pass_action', 'idx_change_user_pass_action');
add_action('wp_ajax_idx_change_user_pass_action', 'idx_change_user_pass_action');
 function idx_change_user_pass_action(){
    $update_user = wp_get_current_user();
    $idx_userID = $update_user->ID;
    $idx_pass = $update_user->user_pass;
    $idx_pass_old = isset ($_POST['idx_pass_old']) ? $_POST['idx_pass_old'] : "";
    $idx_pass_new = isset ($_POST['idx_pass_new']) ? $_POST['idx_pass_new'] : "";
    $idx_pass_new_confirm = isset ($_POST['idx_pass_new_confirm']) ? $_POST['idx_pass_new_confirm'] : "";
    if(wp_check_password($idx_pass_old, $idx_pass)){

        if($idx_pass_new == $idx_pass_new_confirm){
            $user_id = wp_set_password( $idx_pass_new, $idx_userID );
            if( !is_wp_error($user_id) ) {

                echo "Password was updated successfully!";
                header( "refresh:2" );
            }

        } else {
            echo "Passwords Do not match!!!";
        }

    } else {
        echo "Old Password is wrong!";
    }
 die();
}

function idx_parse_json($text) {
    // Damn pesky carriage returns...
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);

    // JSON requires new line characters be escaped
    $text = str_replace("\n", "", $text);
    return $text;
}

function idx_reg_login(){
?>
    <style type="text/css">
        .idx_log_reg{display: none;}
        body .idx_log_reg{display: block;}
        html{margin-top:32px!important}
        html,.site-header{top: 32px !important;}
        #head-panel{top: 0;min-height: 32px;z-index: 999999;}
        @media screen and (max-width: 782px) {#head-panel{min-height: 46px;}html{top: 46px!important;margin-top:46px!important}.site-header{top: 0!important;}#head-panel .head-feature,#head-panel .head-feature ul,#head-panel .head-welcome{line-height: 45px;height: 46px;}}
    </style>
 <?php


if (is_user_logged_in() && !is_admin()){
    $current_user = wp_get_current_user();
    ?>
        <div id="head-panel">
            <div class="head-welcome">Welcome,<?php echo $current_user->first_name . $current_user->last_name;?>!</div>
            <div class="head-feature">
                <ul><li><a href="#" onclick="idxj('#idx_Dashboard').modal();">My Dashboard</a> |</li><li><a href="<?php echo wp_logout_url( home_url() ); ?>" title="Logout">Logout</a></li></ul>
            </div>
        </div>
<?php
    require_once dirname( __FILE__ ) . '/idx_user_dashboard.php';

}elseif(!is_user_logged_in()){

?>
    <div id="head-panel">
        <div class="head-welcome"></div>
        <div class="head-feature">
            <ul><li><a href="#" onclick="idxj('#idx_SignIn').modal();">Sign In</a> |</li><li><a href="#" onclick="idxj('#idx_Register').modal();" >Register</a></li></ul>
        </div>
    </div>
    <?php
    require_once dirname( __FILE__ ) . '/idx_user_login_register.php';

    ?>

<?php
    }
}
add_action('wp_footer', 'idx_reg_login');
do_action( 'IDX_log_reg');
//function to get the remote data
function idx_url_get_contents ($url) {
	if (function_exists('curl_exec')) {
		$conn = curl_init($url);
		curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($conn, CURLOPT_FOLLOWLOCATION, true);
        $url_get_contents_data = (curl_exec($conn));
		curl_close($conn);
	} elseif (function_exists('file_get_contents')) {
		$url_get_contents_data = file_get_contents($url);
	} elseif (function_exists('fopen') && function_exists('stream_get_contents')){
		$handle = fopen ($url, "r");
		$url_get_contents_data = stream_get_contents($handle);
	} else {
		$url_get_contents_data = false;
	}
	return $url_get_contents_data;
}


if (isset($_POST['set_api_key_action'])) {
	$api_key_url=isset ($_POST['api_key_url']) ? $_POST['api_key_url'] : "";
		if (!empty($api_key_url)) {
			$results = $wpdb->get_results("SELECT data FROM {$settings_table_name} WHERE id = 1 ",ARRAY_N);
			if(!empty($results)){
				$data = json_decode($results[0][0],true);
				$data['api_key_url'] = $api_key_url ;
				$strData = json_encode($data);
				$wpdb->query("UPDATE  {$settings_table_name} SET data = '{$strData}' WHERE id = 1");
    			} else {
				$data = array();
				$data['api_key_url'] = $api_key_url ;
				$strData = json_encode($data);
				$wpdb->query("INSERT INTO {$settings_table_name} (id,name,data) VALUES (1,'General Settings','{$strData}')");
			}
		} else {
			echo "API Key cannot be blank";
		}
}
if (isset($_POST['my_savvy_IDX_action'])) {
	$id = isset ($_POST['select_cse']) ? $_POST['select_cse'] : "";
	$tab_id = isset ($_POST['tab_id']) ? $_POST['tab_id'] : "";

	if ($_POST['Control'] == "Edit") {

		$results = $wpdb->get_results("SELECT data,id,name,type FROM {$table_name} WHERE id = {$id} ",ARRAY_N);
		$_SESSION['results'] = $results;
		$_SESSION['tab_id'] = $tab_id;
		$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
		header('HTTP/1.1 301 Moved Permanently');
		header("Location:".$go_to_url."");
		exit();
	}
	if ($_POST['Control'] == "Delete") {

		$wpdb->query("DELETE FROM ".$table_name." WHERE id='".$id."'");
		$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
		header('HTTP/1.1 301 Moved Permanently');
		header("Location:".$go_to_url."");
		exit();
	}
	if ($_POST['Control'] == "Copy") {
		$results = $wpdb->get_results("SELECT *,id,name,type FROM {$table_name} WHERE id = {$id} ",ARRAY_N);
		$my_search_name = $results[0][3].' - copy';
		$type = $results[0][2];
		$strData = $results[0][4];
		$user_id = $results[0][1];
		$query = $wpdb->query("INSERT INTO {$table_name} (user_id,name,type,data) VALUES ('{$user_id}','{$my_search_name}','{$type}','{$strData}')");
		$result = $wpdb->get_results("SELECT data,id,name,type FROM {$table_name} WHERE user_id = {$user_id} ORDER BY id DESC",ARRAY_N);
		$_SESSION['results'] = $result;
		$_SESSION['tab_id'] = $tab_id;
		$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
		header('HTTP/1.1 301 Moved Permanently');
		header("Location:".$go_to_url."");
		exit();
	}
}
if (isset($_POST['i_action_tab'])) {
	if ($_POST['tab_data'] == "config" ) {
		$my_action = $_POST['i_action_tab'];
		$settings_name = isset ($_POST['tab_name']) ? $_POST['tab_name'] : "";
		$tab_id = isset ($_POST['tab_id']) ? $_POST['tab_id'] : "";
		$arrData = $_POST;

		unset($arrData['user_id']);
		unset($arrData['tab_data']);
		unset($arrData['tab_name']);
		unset($arrData['i_action_tab']);
		unset($arrData['tab_id']);

		// Need to escape data going to database - have to handle HTML data with double quotes!
		$strData = json_encode(str_replace('"', '\\"', idx_parse_json($arrData)));
		
		if ($my_action == "createtab") {
			$wpdb->query("INSERT INTO {$settings_table_name} (name,data) VALUES ('{$settings_name}','{$strData}')");
			$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
			header('HTTP/1.1 301 Moved Permanently');
			header("Location:".$go_to_url."");
			exit();
		} elseif ($my_action == "updatetab") {
			$wpdb->query("UPDATE  {$settings_table_name} SET data = '{$strData}' WHERE name = '{$settings_name}'");
			$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
			header('HTTP/1.1 301 Moved Permanently');
			header("Location:".$go_to_url."");
			exit();
		}
	} else {
		$my_action = $_POST['i_action_tab'];
		$my_search_name = $_POST['search_name'];
		$user_id = isset ($_POST['user_id']) ? $_POST['user_id'] : "";
		$type = isset ($_POST['tab_name']) ? $_POST['tab_name'] : "";
		$tab_id = isset ($_POST['tab_id']) ? $_POST['tab_id'] : "";
		$search_id = isset ($_POST['search_id']) ? $_POST['search_id'] : "";
		$arrData = $_POST;

		unset($arrData['user_id']);
		unset($arrData['search_name']);
		unset($arrData['tab_name']);
		unset($arrData['i_action_tab']);
		unset($arrData['tab_id']);
		unset($arrData['search_id']);
		unset($arrData['tab_data']);

		$strData = json_encode($arrData);

		if($my_action =="createtab") {
			$wpdb->query("INSERT INTO {$table_name} (user_id,name,type,data) VALUES ('{$user_id}','{$my_search_name}','{$type}','{$strData}')");
			$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
			header('HTTP/1.1 301 Moved Permanently');
			header("Location:".$go_to_url."");
			exit();
		} elseif($my_action == "updatetab") {

			$wpdb->query("UPDATE  {$table_name} SET user_id = '{$user_id}', name = '{$my_search_name}', type = '{$type}', data = '{$strData}' WHERE id = {$search_id} ");
			$go_to_url = $_SERVER['HTTP_REFERER'].$tab_id;
			header('HTTP/1.1 301 Moved Permanently');
			header("Location:".$go_to_url."");
			exit();
		}

	}
}

function gallery_frontend($search_id) {

	global $wpdb;

	$table_name = $wpdb->prefix . "Xxxxxxxx_data";
	$settings_table_name = $wpdb->prefix . "Xxxxxxxx_settings";
	$result = $wpdb->get_results("SELECT data FROM {$table_name} WHERE id = {$search_id}",ARRAY_N);
	$set_results = $wpdb->get_results("SELECT data FROM {$settings_table_name} ",ARRAY_N);
	$wpdb->get_results( "SELECT data FROM {$settings_table_name} ");
	$count_set_tab=$wpdb->num_rows;


if (!empty($result) && !empty($set_results)) {
        $data = json_decode($result[0][0],true);
        $url = '';
        $jsvarstring = '';
        $slider_mode = false ;
        $search_mode = false ;
        
        for ($i = 0; $i < $count_set_tab; $i++) 
        {

            $set_data[$i] = json_decode($set_results[$i][0], true);
            // RSH - Cast the variable to an array to stop the PHP warning
            // when $value was empty
            foreach ((array) $set_data[$i] as $key => $value):
                if (substr($key,-3) == "url") {
                    $newval = str_replace(' ', '',$value);
                    $url .= $key .'='. $newval .'&amp;' ;
                } else {
                    $jsvarstring .=  $key.":'".$value."',";
                }
            endforeach;
        }
        
        foreach ($data as $key => $value):
        
            if (substr($key,-3) == "url" and !is_array($value)) {


                $newval = $value;
                $url .= $key . '=' . $newval . '&amp;';
                
            } else if (substr($key,-3) == "url" and is_array($value)) {
                $newval = $value;
                $url .= $key  . '=' . (implode(',', $newval)) . '&amp;' ;
            } else if (!is_array($value)) {
                $jsvarstring .= $key . ":'" . $value . "',";

            } else {
                $jsvarstring .= $key . ":'" . (implode(',', $value)) . "',";
            }
        endforeach;
        $plugin_count = @$_SESSION['plugin_count'];


        if($plugin_count == '') {//escaping 0 for count of page plugins
            $plugin_count = 1;
        } 

        $url1 = API_URL."/toolv2?&amp;p_count=".$plugin_count."&amp;action_url=plugin&amp;".$url."attach=Xxxxxxxx_loadsearch";
        
    } else {
        echo "The search with id: $search_id doesn't exist, put the right shortcode ";
    }

    ?>
     <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
     <script>
     if (typeof idxj === 'undefined') {

             var idxj = jQuery.noConflict();

            } 
     /** * jquery.detectSwipe v2.1.1 * jQuery Plugin to obtain touch gestures from iPhone, iPod Touch, iPad and Android * http://github.com/marcandre/detect_swipe * Based on touchwipe by Andreas Waltl, netCU Internetagentur (http://www.netcu.de) */(function($) {  $.detectSwipe = {    version: '2.1.1',    enabled: 'ontouchstart' in document.documentElement,    preventDefault: true,    threshold: 20  };  var startX,    startY,    isMoving = false;  function onTouchEnd() {    this.removeEventListener('touchmove', onTouchMove);    this.removeEventListener('touchend', onTouchEnd);    isMoving = false;  }  function onTouchMove(e) {    if ($.detectSwipe.preventDefault) { e.preventDefault(); }    if(isMoving) {      var x = e.touches[0].pageX;      var y = e.touches[0].pageY;      var dx = startX - x;      var dy = startY - y;      var dir;      if(Math.abs(dx) >= $.detectSwipe.threshold) {        dir = dx > 0 ? 'left' : 'right'      }      else if(Math.abs(dy) >= $.detectSwipe.threshold) {        dir = dy > 0 ? 'down' : 'up'      }      if(dir) {        onTouchEnd.call(this);        $(this).trigger('swipe', dir).trigger('swipe' + dir);      }    }  }  function onTouchStart(e) {    if (e.touches.length == 1) {      startX = e.touches[0].pageX;      startY = e.touches[0].pageY;      isMoving = true;      this.addEventListener('touchmove', onTouchMove, false);      this.addEventListener('touchend', onTouchEnd, false);    }  }  function setup() {    this.addEventListener && this.addEventListener('touchstart', onTouchStart, false);  }  function teardown() {    this.removeEventListener('touchstart', onTouchStart);  }  $.event.special.swipe = { setup: setup };  $.each(['left', 'up', 'down', 'right'], function () {    $.event.special['swipe' + this] = { setup: function(){      $(this).on('swipe', $.noop);    } };  });})(jQuery);

     </script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js" ></script>

    <script type="text/javascript">
    
        
        <?php echo "var idx_data$plugin_count = { \n".$jsvarstring."}"; ?>
        
    </script>
    <script type="text/javascript" src="<?php echo $url1; ?>"></script>


    <div id = "Xxxxxxxx_loadsearch<?php print $plugin_count;?>"></div>


<?php
$plugin_count ++ ; //incrementing number and saving for next initialisation
$_SESSION['plugin_count'] = $plugin_count;
}

add_action('wp_ajax_idx_user_save_search', 'idx_user_save_search_callback');

function idx_user_save_search_callback() {

    ob_clean();

    if(is_user_logged_in()){
        $user_id=get_current_user_id();

        if(isset($_COOKIE['idx_search'])){
            delete_user_meta( $user_id, 'idx_search'); // if cookie is set we need to delete them first to keep all in sync, otherwise they are dublicated
            foreach ($_COOKIE['idx_search'] as $name => $value)
            {   date_default_timezone_set('UTC');
                $my_idx_search['name'] = $name;
                $my_idx_search['value'] = $value;
                $my_idx_search['time']  = date('l jS \of F Y h:i:s A');
                $searchData = json_encode(str_replace('"', '\\"', idx_parse_json($my_idx_search)));
               
                

                add_user_meta( $user_id, 'idx_search', $searchData );

            }
            echo  "See your saved search data in your dashboard";
       } else {
            echo "Login please, to save your search data";
    }
  }
  die();
}



add_action('wp_ajax_idx_user_save_favorites', 'idx_user_save_favorites_callback');

function idx_user_save_favorites_callback() {

    ob_clean();

    if(is_user_logged_in())
    {
        $user_id=get_current_user_id();
        $saved = get_user_meta( $user_id, 'idx_favorites', true );
        $saved = str_replace('"', '', $saved);
        $saved  = explode(',', $saved);
        $cookies = $_COOKIE['idx_favorites'];
        $tenYears = 10*365*24*60*60;

        foreach ($saved as  $savedValue) {
            if($savedValue=='')
            {
                continue;
            }

            if(!in_array($savedValue,$cookies))
            {
                $max_number = max(array_keys($cookies));
                $max_number = intval($max_number);
                $max_number++;
                setrawcookie('idx_favorites['.$max_number.']',$savedValue,time()+$tenYears,'/');
            }
               
        }

        if(isset($_COOKIE['idx_favorites']))
        {
            $id_list = '';
            date_default_timezone_set('UTC');

            foreach ($_COOKIE['idx_favorites'] as $name => $value)
                {   
                
                $id_list.= $value.',';
                
                $searchData = json_encode(str_replace('"', '\\"', idx_parse_json($id_list)));
                if( get_user_meta( $user_id, 'idx_favorites', true ) != false) {

                    update_user_meta( $user_id, 'idx_favorites', $searchData );
                } else {
                    add_user_meta( $user_id, 'idx_favorites', $searchData );
                }

                }

        } 
    }

    die();
}

add_action('wp_ajax_idx_delete_saved_search', 'idx_delete_saved_search_callback');

function idx_delete_saved_search_callback() {
    ob_clean();
    // echo 1231256;

    $user_id=get_current_user_id();
    $meta_value=isset ($_POST['value']) ? $_POST['value'] : "";
    //echo $meta_value;
    $user_meta=delete_user_meta( $user_id, 'idx_search', $meta_value );
    if( !is_wp_error($user_meta) ) {
        echo "Your search is deleted successfully";
    } else {
        echo "Your search can't be deleted";
    }
    die();
}
function Xxxxxxxx_frontend($atts) {
	extract( shortcode_atts( array(
		'search_id' => '',
	), $atts, 'Xxxxxxxx' ));
	$return='';
	ob_start();

	echo '<div class="Xxxxxxxx_div" style="display:none;">';
	gallery_frontend($atts['search_id']);
	echo '</div>';

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

add_shortcode( 'Xxxxxxxx', 'Xxxxxxxx_frontend', 10,2);

function Xxxxxxxx_agent($colorscheme) {
	global $wpdb;

	$settings_table_name = $wpdb->prefix . "Xxxxxxxx_settings";
	$set_results = $wpdb->get_results("SELECT data FROM {$settings_table_name} WHERE name = 'Agent Profile' ",ARRAY_N);
	if(!empty($set_results)) {
		$data_agent = json_decode($set_results[0][0],true);

		?>
		<div class="vizitka <?php echo $colorscheme; ?>_scheme">
			<img src="<?php echo $data_agent['idx_agent_agent_picture'];?>" alt="<?php echo $data_agent['idx_agent_name']; ?>"/>
			<h2><?php echo $data_agent['idx_agent_name']; ?></h2>
			<p><?php echo $data_agent['idx_agent_office_name']; ?></p>
			<p><?php echo $data_agent['idx_agent_email_address']; ?></p>
			<p><?php echo $data_agent['idx_agent_telephone']; ?></p>
			<a href="<?php echo $data_agent['idx_agent_facebook']; ?>"><div class="social_icon fb_icon">
				</div></a>
			<a href="<?php echo $data_agent['idx_agent_twitter']; ?>"><div class="social_icon twitter_icon">
				</div></a>
			<a href="<?php echo $data_agent['idx_agent_in']; ?>"><div class="social_icon linkedin_icon">
				</div></a>
			<a href="<?php echo $data_agent['idx_agent_google']; ?>"><div class="social_icon google_icon">
				</div></a>
		</div>


	<?php
	}

}

function Xxxxxxxx_agent_info($atts) {
	extract( shortcode_atts( array(
		'colorscheme' => '',
	), $atts, 'Xxxxxxxx' ));
	$return='';
	ob_start();

	echo '<div class="Xxxxxxxx_Agent_div" id="Xxxxxxxx_Agent_div">';
	Xxxxxxxx_agent($atts['colorscheme']);
	echo '</div>';

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

add_shortcode( 'Xxxxxxxx_Agent', 'Xxxxxxxx_agent_info', 10,2);

// Add settings link on plugin page
function Xxxxxxxx_api_settings_link($links) {
	$settings_link = '<a href="admin.php?page=Xxxxxxxx_API_config">Settings</a>';
	array_unshift($links, $settings_link);
	return $links;
}


$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'Xxxxxxxx_api_settings_link' );


add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
