<?php

use VikCal\VikCalAdminSettings;

get_header();
$eventDate = get_post_meta( $post->ID, 'eventDate', true);
?>
    <div class="vikcal--pageholder">
        <div class="vikcal--page__content">
            <?php if ( have_posts() ) : ?>

                <h1><?php _e('Events list', VikCalAdminSettings::VikCalTextDomain);?></h1>

                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                <div class="vikcal--eventsList__single">

                    <div class="vikcal--eventsList__singleImg">
                        <?php the_post_thumbnail();?>
                    </div>
                    <div class="vikcal--eventsList__singleContent">
                        <h3><?php echo the_title();?></h3>
                        <?php $eventDate = get_post_meta( get_the_ID(), 'eventDate', true); ?>
                        <span><?php echo $eventDate;?></span>
                        <?php the_excerpt(); ?>
                        <div class="vikcal--eventsList__readmore">
                            <a href="<?php the_permalink();?>"
                               title="<?php _e( 'Read more of', VikCalAdminSettings::VikCalTextDomain);?> <?php echo the_title();?>"
                               >
                                <?php _e( 'Check details', VikCalAdminSettings::VikCalTextDomain );?>
                            </a>
                        </div>
                    </div>

                </div>
                <?php
                endwhile;

            else :
                _e('No available events found', VikCalAdminSettings::VikCalTextDomain);
            endif;
            ?>
        </div>
    </div>

<?php
get_footer();
?>