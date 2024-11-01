<?php get_header(); ?>

	<div id="content" class="narrowcolumn" role="main">
		<div class="post" id="post-<?php the_ID(); ?>">
		<h2><?php the_taglist_title(); ?></h2>
			
			<div id="taglist">
				<?php the_content(); ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>
