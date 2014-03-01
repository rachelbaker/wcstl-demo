<?php
/**
 * Template Name: Location Demo Template
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 */
get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<?php the_content(); ?>
						<h3>Location Information</h3>

						<h5>Name:</h5>
						<?php
							$name = get_post_meta( get_the_ID(), '_wcstl_name', true );
							echo esc_attr( $name );
						?>

						<h5>Email:</h5>
						<?php
							$email= get_post_meta( get_the_ID(), '_wcstl_email', true );
							$email = antispambot( $email );
							echo esc_html( $email );
						?>

						<h5>Phone:</h5>
						<?php 
							$phone = get_post_meta( get_the_ID(), '_wcstl_phone', true );
							echo esc_attr( $phone);
						?>

						<h5>Address:</h5>
						<?php
							$address = get_post_meta( get_the_ID(), '_wcstl_address', true );
							$address = wptexturize( $address );
							$address = convert_chars( $address );
							echo wpautop( $address );
						?>

						<h5>Description:</h5>
						<?php
							$description = get_post_meta( get_the_ID(), '_wcstl_description', true );
							$description = wptexturize( $description );
							$description = convert_chars( $description );
							echo wpautop( $description );
						?>

						<h5>Map:</h5>
						<?php
							$map_url = get_post_meta( get_the_ID(), '_wcstl_map_url', true );
							echo esc_url( $map_url); 
						?>

					</div><!-- .entry-content -->
				</article><!-- #post -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); 
