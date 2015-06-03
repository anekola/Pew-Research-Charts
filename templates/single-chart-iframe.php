<?php
global $post;
get_pew_charts_header(); 
?>

<!--googleon: index-->

<div id="content" class="content" role="main">
	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<h3><?php echo wptexturize($post->post_title);?></h3>
		<?php echo wpautop(get_post_field('post_content', $chart->ID)); ?>
	<?php endwhile; endif; ?>
</div>

<!--googleoff: index-->

<?php get_footer( 'iframe' ); ?>
