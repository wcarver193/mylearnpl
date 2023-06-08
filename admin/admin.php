<h1 class="mylearnpl_title"><?php esc_html_e('Choice Settings','mylearnpl'); ?></h1>
<!-----вывод ошибок----->
<?php settings_errors(); ?>
<div class="mylearnpl_content">
    <form method="post" action="options.php"><!---action="options.php" всегда, когда хотим сохр.настройки--->
        <?php 
            settings_fields('choice_settings');
            do_settings_sections('mylearnpl_settings');  //'mylearnpl_settings'- это ID страницы и мы к нему подгруж.поля
            submit_button();
        ?>
    </form>
</div>