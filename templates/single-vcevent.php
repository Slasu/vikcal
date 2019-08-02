<?php

use VikCal\VikCalAdminSettings;

get_header();
$eventDate = get_post_meta( $post->ID, 'eventDate', true);
?>

<div class="vikcal--pageholder">
    <div class="vikcal--page__content">
        <h1><?php echo $post->post_title;?></h1>
        <h3><?php _e('Event date', VikCalAdminSettings::VikCalTextDomain);?>: <?php echo $eventDate;?></h3>
        <p>
            <?php echo apply_filters('the_content', $post->post_content);?>
        </p>
    </div>
</div>

<?php
get_footer();
?>