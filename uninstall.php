<?php
//удаление данных из базы данных при удалении плагина
if(!defined('WP_UNINSTALL_PLUGIN')){
	die;
}
//delete post type from bd. ПЕРВЫЙ СПОСОБ
//global $wpdb;
//$wpdp->query("DELETE FROM{$wpdp->posts} WHERE post_type IN ('room');");
//FROM{wp_posts}....  - так нельзя т.к. мы не знаем префикс wр_, если кто нибудь будет исп.плагин
 
 //использ.$wpdb дост. опасно для взлома. ВТОРОЙ СПОСОБ, более безопасный 

$rooms = get_posts(['post_type'=>'room', 'numberposts'=>-1]);// все посты передать в перем.$rooms
foreach($rooms as $room){
	wp_delete_post($room->ID,true);// true удаляет не только публичные посты
}



