<?php
add_filter('show_admin_bar', '__return_false');
//Removes the CSS that adds 28px margin-top to <html>
remove_action('wp_head', '_admin_bar_bump_cb');

function add_classes_to_body($class) {
	$class[] = 'iframe';
	return $class;
}
add_filter('body_class', 'add_classes_to_body');
?>
<!DOCTYPE html>
<!--[if lt IE 7 ]> <html class="ie ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
<head profile="http://gmpg.org/xfn/11" prefix="og: http://ogp.me/ns#">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width">
<title><?php wp_title(); ?></title>
<meta name="date" content="<?php echo date('Ymd',strtotime($post->post_date)); ?>">
<link href="<?=get_template_directory_uri();?>/img/favicon.png" rel="shortcut icon" type="image/x-icon"/>
<?php wp_head(); ?>

<?php do_action('iframe_header_content'); ?>
</head>
<!--googleoff: index-->
<body <?php body_class(); ?>>
