<?php /*
* Plugin Name: My learning plugin
* Plugin URI: http://mywdproject.fun/mylearnpl
* Description: учебный плагин
* Version: 1.0
* Requires at least: 6.0
* Requires PHP: 7.1.7
* Author: Wowa
* Author URI: http://instagram.com/vladimirwcar
* License: GPLv2 or later
* Text Domain: my-learning-plugin
* Domain Path:  /lang
*/
//Text Domain должен совпадать с Plugin Name без учета регистра. пробелы замен. на -
//если хочешь плагин сдел. публ. нужно добав. ниже описание и отред.его

// unserialize просм данных массива в базе видео 2ч 9мин

//защита от взлома
/*if(!function_exists('add_action')){ // 1 var
	exit;
}*/
if(!defined('ABSPATH')){   //2 var. конст.ABSPATH формир. вордпрессом
	die;
}
/*defined('ABSPATH') or die;   //3 var*/

class MylearnPl
{   

	public function register(){
		                         //хук для подкл.польз.типов постов
		add_action('init',[$this,'custom_post_type']);
		
		                          //хук для подкл. стилей и скриптов плагина в мом. подключения скриптов в админке - хук admin_enqueue_scripts
		add_action('admin_enqueue_scripts',[$this,'enqueue_admin']);
		                          //хук для подкл.скриптов и стилей для главн. стр. в front
		 add_action('wp_enqueue_scripts',[$this,'enqueue_front']);
		                       //хук к фильтру room_template
		add_filter('template_include', [$this,'product_template']);
                               //хук для уст стр. в админке
		add_action('admin_menu', [$this, 'add_admin_menu']);
                //добавл.ссылку на плагин в его описании
		add_filter('plugin_action_links_' . plugin_basename(__FILE__),[$this,'add_plugin_setting_link']);
		                   //plugin_basename(__FILE__) выводит назв.нашего файла и . присоед к 'plugin_action_links_'		
		                   //хук для создания полей настройки в стр. плаг
		add_action('admin_init', [$this, 'settings_init']); 
		
	}
	
		                            //function activation() - когда добавл новые посты в room и при вкл.в админке "постоянн.ссылки- название записи" переходишь на стр. поста выск ошибка 404
	static function activation(){
		                          //$this->custom_post_type()- функция пользоват. локальная, поэтому только через $this->
		flush_rewrite_rules();//функция глобальная, поэтому вызыв. и внутри
	}
	static function deactivation(){
		flush_rewrite_rules();
	}
	             //функция вывода термов в  <select name="type_option"> в archive-product.php 3ч 7 мин
	public function get_terms_hierarchical($tax_name, $current_term){
		
		$taxonomy_terms = get_terms($tax_name, ['hide_empty'=>false,'parent'=>0]);//false - не пок.пустые terms 'parent'=>0 - чтобы доставать первый уровень
		
		if(!empty($taxonomy_terms)){
			
			foreach($taxonomy_terms as $term){
				//этот if для того, что бы в окнах оставались выбранные значения городов и типов жилья
				if($current_term == $term->term_id){
					echo '<option value="' . $term->term_id .'" selected>' . $term->name . '</option>';
				}else{
				    echo '<option value="' . $term->term_id .'">' . $term->name . '</option>';//вывод нулев.уровня
				}
				$child_terms = get_terms($tax_name,['hide_empty'=>false,'parent'=>$term->term_id]);//для вывода подтермов
				if(!empty($child_terms)){
					foreach($child_terms as $child){
						echo '<option value="'.$child->term_id.'"> - '.$child->name . '</option>';
						
					}
				}				
			}
		}
	}
	
	                               //регистрация полей для настройки
	public function settings_init(){
		register_setting('choice_settings', 'choice_settings_options');
		
		add_settings_section('choice_settings_section', esc_html__('Settings', 'mylearnpl'), [$this,'settings_section_html'], 'mylearnpl_settings');
		
		        add_settings_field('posts_per_page',esc_html__('Posts per page', 'mylearnpl'), [$this, 'posts_per_page_html'], 'mylearnpl_settings', 'choice_settings_section' );
		
		add_settings_field('title_for_products',esc_html__('Archive page title', 'mylearnpl'), [$this, 'title_for_products_html'],'mylearnpl_settings', 'choice_settings_section' );
		
	}
	
	
	                          //функц.задает html код поля в админке для описания
	public function settings_section_html(){
		//echo esc_html__('hello world!', 'mylearnpl');
	}
                                 //функц.задает html код поля в админке для зад колл постов на стр
	public function posts_per_page_html(){
		$options = get_option('choice_settings_options'); ?>
		
		<input type="text" name="choice_settings_options[posts_per_page]" value = " <?php echo isset($options['posts_per_page']) ? $options['posts_per_page'] : ""; ?> " />
		
	<?php }

                                  //функц. код поля в админке html для вывода назв.archive page title
	public function title_for_products_html(){
		$options = get_option('choice_settings_options'); ?>
		    <input type="text" name="choice_settings_options[title_for_products]" value = " <?php echo isset($options['title_for_products']) ? $options['title_for_products'] : ""; ?> " />
	<?php }	
	
                                   //в массив всех ссылок $link добав.нашу ссылку на Settings
	public function add_plugin_setting_link($link){    //$link - массив всех ссылок и к ней нужно добав.нашу
		$custom_link = '<a href="admin.php?page=mylearnpl_settings">' . esc_html__('Settings', 'mylearnpl') . '</a>';
	    array_push($link, $custom_link);  //в массив всех ссылок добав.нашу
	    return $link;
	}
	
	//добавл.стр.плагина в админ. меню
	public function add_admin_menu(){
		add_menu_page(
		     esc_html__('My Learn Plagin', 'mylearnpl'),
			 esc_html__('MyLearnPl', 'mylearnpl'),
			 'manage_options',
			 'mylearnpl_settings',
			 [$this,'mylearnpl_page'],     //вставить так 'mylearnpl_page' нельзя, т.к. мы внутри класса
			 'dashicons-admin-multisite',
			 100
		);
		
	}
	
	                         // my learn plugin код HTML в admin
	public function mylearnpl_page(){
		require_once plugin_dir_path(__FILE__) . 'admin/admin.php';//подкл.файл с HTML плагина
	}
	
	
	// фильтр product_template - провер.есть ли у польз.свои файлы archive-product.php или mylearnpl/archive-product.php. Ели нет, исп. из нашего плагина файл templates/archive-product.php
	
	public function product_template($template){
		if(is_post_type_archive('product')){
			$theme_files = ['archive-product.php','mylearnpl/archive-product.php'];
			$exist = locate_template($theme_files, false);
			if($exist != ''){
			return $exist;
			}else{
				return plugin_dir_path(__FILE__) . 'templates/archive-product.php';//возвр.наш файл archive-room.php
			}
		}
		return $template;//я не понял зачем 1ч.33мин видео
	}
	
			                           //подкл.скриптов и стилей в админке
	public function enqueue_admin(){
		wp_enqueue_script('mylearnplScript', plugins_url('/assets/admin/scripts.js', __FILE__));
		wp_enqueue_style('mylearnplStyle', plugins_url('/assets/admin/styles.css', __FILE__));
		
	}
	                         //подкл.скриптов и стилей для главн. стр. в front
	public function enqueue_front(){
        wp_enqueue_style('mylearnplStyle', plugins_url('/assets/front/stylesFr.css', __FILE__));
        wp_enqueue_script('mylearnplScript', plugins_url('/assets/front/scripts.js', __FILE__));
    }
	//----------------------------------------------------------
	                          //регистр. пользов.тип поста
	function custom_post_type(){
		register_post_type('product',[
		  'public'=> true,
		  'has_archive'=> true,
		  'rewrite'=>['slug'=>'products'],
		  'label'=> esc_html__('Product', 'mylearnpl'),
		  'supports' => ['title', 'editor', 'thumbnail', 'comments'],
		 ]);
	$labels_formats = array(
		'name'              => _x( 'formats', 'taxonomy general name', 'mylearnpl' ),
		'singular_name'     => _x( 'format', 'taxonomy singular name', 'mylearnpl' ),
		'search_items'      => __( 'Search formats', 'mylearnpl' ),
		'all_items'         => __( 'All formats', 'mylearnpl' ),
		'view_item'         => __( 'View format', 'mylearnpl' ),
		'parent_item'       => __( 'Parent format', 'mylearnpl' ),
		'parent_item_colon' => __( 'Parent format:', 'mylearnpl' ),
		'edit_item'         => __( 'Edit format', 'mylearnpl' ),
		'update_item'       => __( 'Update format', 'mylearnpl' ),
		'add_new_item'      => __( 'Add New format', 'mylearnpl' ),
		'new_item_name'     => __( 'New format Name', 'mylearnpl' ),
		'not_found'         => __( 'No format Found', 'mylearnpl' ),
		'back_to_items'     => __( 'Back to format', 'mylearnpl' ),
		'menu_name'         => __( 'format', 'mylearnpl' ),
	);
	$args = array(
		'labels'            => $labels_formats,
		'hierarchical'      => true, //позв. подкл. подкатегории для термов
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'format' ),
		'show_in_rest'      => true,
	);
	register_taxonomy('format','product',$args);
		 
	$labels_type = array(
		'name'              => _x( 'Types', 'taxonomy general name', 'mylearnpl' ),
		'singular_name'     => _x( 'Type', 'taxonomy singular name', 'mylearnpl' ),
		'search_items'      => __( 'Search Types', 'mylearnpl' ),
		'all_items'         => __( 'All Types', 'mylearnpl' ),
		'view_item'         => __( 'View Type', 'mylearnpl' ),
		'parent_item'       => __( 'Parent Type', 'mylearnpl' ),
		'parent_item_colon' => __( 'Parent Type:', 'mylearnpl' ),
		'edit_item'         => __( 'Edit Type', 'mylearnpl' ),
		'update_item'       => __( 'Update Type', 'mylearnpl' ),
		'add_new_item'      => __( 'Add New Type', 'mylearnpl' ),
		'new_item_name'     => __( 'New Type Name', 'mylearnpl' ),
		'not_found'         => __( 'No Type Found', 'mylearnpl' ),
		'back_to_items'     => __( 'Back to Type', 'mylearnpl' ),
		'menu_name'         => __( 'Type', 'mylearnpl' ),
	);
	$args_type = array(
		'labels'            => $labels_type,
		'hierarchical'      => true,//true - позв.добавлять иерархию - подкатегории к категориям
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'format' ),
		'show_in_rest'      => true,
	);
	register_taxonomy('type','product',$args_type);
   }	
}
                             //создаем новый объект , если такой класс есть
if(class_exists('MylearnPl')){
   $mylearnpl = new MylearnPl();
   $mylearnpl->register();//вызов метода register
}

                             //хуки при активации и деактивации плагина
register_activation_hook(__FILE__,[$mylearnpl, 'activation']);
register_deactivation_hook(__FILE__,[$mylearnpl, 'deactivation']);



?>