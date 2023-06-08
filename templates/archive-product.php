<?php
get_header('blog');
$options = get_option('choice_settings_options');// cтандартная функция вордпресса
$instance = new MylearnPl();
                              //taxonomy у нас это type и formats/ metaboxы - это поля для ввода данных в админке, типа Posts per page 
                              //термы у нас это - PNG, JPG, portrait и др
?>
<p style="text-align:center; font-size:10px;">archive-product plugin</p>
<div class="wrapper"> 
    
      <h1 id="archRoomH1"><?php if(isset($options['title_for_products'])){ echo $options['title_for_products']; }?></h1>
	  
	 <!-------------------------------filter select type and format--------------------------------------->
		<div class="filter">
				<form method="post" action="<?php echo get_post_type_archive_link('product'); ?>">
				
						
					 <select name="type_option">
					   <option value=""><?php esc_html_e('Select Type:','mylearnpl'); ?></option>
					   <?php 
						  $instance->get_terms_hierarchical('type', $_POST['type_option']);
					   ?>
					</select>
							
					<select name="format_option">
					   <option value=""><?php esc_html_e('Select format:','mylearnpl'); ?></option>
					   <?php 
						  $instance->get_terms_hierarchical('format', $_POST['format_option']);
					   ?>
					</select>
					
					<input type="submit" name="submit" value="<?php esc_html_e('Filter','mylearnpl');?>" />
				
				</form>
		</div>
	<div class="booking_rooms">  
<?php

	$posts_per_page = -1;//т.е. вывод всех постов
	
	if( $options['posts_per_page']){   // если сущ.$options['posts_per_page']
		$posts_per_page = $options['posts_per_page'];
	}
	$args=[
		'post_type' => 'product',
		'posts_per_page' => -1, 
		'tax_query' => array('relation'=> 'AND'),
		];
	// типов может быть больше чем 2 type и formats их можно вывод.динамически а не их простыню вниз	
		if(isset($_POST['type_option'])&& $_POST['type_option']!= ''){  //если перем.$_POST['type'] сущ и она не пустая то:
			array_push( $args['tax_query'], array(
				'taxonomy' => 'type',
				'terms' => $_POST['type_option'],
			
			));
		}	
		if(isset($_POST['format_option'])&& $_POST['format_option']!= ''){  //если перем.$_POST['format'] сущ и она не пустая то:
			array_push( $args['tax_query'], array(
				'taxonomy' => 'format',
				'terms' => $_POST['format_option'],
			));
		}
		
	if(!empty($_POST['submit'])){  // если submit не пустой(нажат) вывод искомого
	
		$search_listing = new WP_Query($args);
		
			   if( $search_listing->have_posts()){
				   while($search_listing->have_posts()){$search_listing->the_post();?>
				<div class="artprod row">  
				    <div class="col-12 col-md-6 col-lg-4">
					   <?php if(get_the_post_thumbnail(get_the_ID(), 'large')){
						    echo '<div class="imgArRoom">'. get_the_post_thumbnail(get_the_ID(),'medium') . '</div>';
					   }?>
					</div>
					<div class="col-12 col-md-6 col-lg-7" id="artprodinfo">
						<h3><a href="<?php the_permalink(); ?> "><?php the_title();?></a></h3>
					   <div class="descipArRoom">
							<?php the_excerpt();?>
					   </div>
					   <?php
					  
					        $types = get_the_terms(get_the_ID(), 'type');
							if(!empty($types)){
								foreach($types as $type){
									echo '<span class="typeArRoom">' . '<b>' . esc_html__('Type: ', 'mylearnpl') . '</b>' . $type->name . '</span>';
								}
							}
							
							$formats = get_the_terms(get_the_ID(), 'format');
							if(!empty($formats)){
								foreach($formats as $format){
									echo '<span class="locArRoom">' . '<b>' . esc_html__('format: ', 'mylearnpl') . '</b>' . $format->name . '</span>';
								}
							}
							
					   ?>
				   </div>
				  
				</div>
			
				<?php   }
				
				//--------------------------------------------------------------------------------------
				  
				} else {
				   echo esc_html__('No posts!','mylearnpl');
				}	
	} else {    // если submit пустой( не нажат) вывод всего содерж продукта
				$paged = 1;
				if(get_query_var('paged')){$paged = get_query_var('paged'); }
				if(get_query_var('page')){ $paged = get_query_var('page'); }
				
				$default_listing =[
						'post_type' => 'product',
						'posts_per_page' => esc_attr($posts_per_page ),
						'paged' => $paged
						];
				$products_listing = new WP_Query($default_listing);
				
		
			   if( $products_listing->have_posts()){
				   while($products_listing->have_posts()){$products_listing->the_post();?>
						 <div class="row artprod ">   
							<div class="col-12 col-md-6 col-lg-4">
								<?php if(get_the_post_thumbnail(get_the_ID(), 'large')){
									echo '<div class="imgArRoom">'. get_the_post_thumbnail(get_the_ID(),'portfolio-thumbnail') . '</div>';
								}?>
							</div>
							<div class="col-12 col-md-6 col-lg-7"  id="artprodinfo">
								<h3><a href="<?php the_permalink(); ?> "><?php the_title();?></a></h3>
							   <div class="descipArRoom">
									<?php the_excerpt();?>
							   </div>
							   <?php
									$formats = get_the_terms(get_the_ID(), 'format');
									if(!empty($formats)){
										foreach($formats as $format){
											echo '<span class="locArRoom">' . '<b>' .esc_html__('Format: ', 'mylearnpl') . '</b>' . $format->name . '</span>';
										}
									}
									$types = get_the_terms(get_the_ID(), 'type');
									if(!empty($types)){
										foreach($types as $type){
											echo '<span class="typeArRoom">' . '<b>' . esc_html__('Type: ', 'mylearnpl') . '</b>' . $type->name . '</span>';
										}
									}
							   ?>
						 
						   </div>
						   
						 </div>
				
			
				
				
			  <?php } ?>
					
					<div class="prodpag"> <?php
						$big = 999999999; // need an unlikely integer
										
						echo paginate_links( array(
							'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
							'format' => '?paged=%#%',
							'current' => max( 1, get_query_var('paged') ),
							'total' => $products_listing->max_num_pages
						) );?>
					</div>
					<?php
				  
				}else{
				   echo esc_html__('No posts!','mylearnpl');//esc_html__ - для автом. перевода на др.яз.
			   }
		    }



?>
    </div>   
 </div>   
<?php

get_footer();
?>