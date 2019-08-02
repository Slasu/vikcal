<?php

namespace VikCal;

use WP_Query;

class VikCalPostType {

    use VikCalTrait;

    const VikCalTextDomain = 'vikcal';

    private $isAdmin;
    private $day;
    private $month;
    private $year;
    private $daysInMonthAmount;
    private $firstDay;
    private $VikCalSettings;

    public function __construct( bool $isAdmin)
    {
        $this->isAdmin = $isAdmin;
        $this->init();

        if( $this->isAdmin ) {
            $this->initAdmin();
        }
    }

    /**
     * Actions and equeues for common scripts and actions
     */
    private function init()
    {
        add_action( 'init', [ $this, 'RegisterPostVikCal' ] );
//        add_theme_support('post-thumbnails');
        add_action( 'wp_ajax_nopriv_VikCalChangeMonth', [ $this, 'VikCalChangeMonth' ] );
        add_action( 'wp_ajax_VikCalChangeMonth', [ $this, 'VikCalChangeMonth' ] );
        add_action( 'wp_head', [$this, 'RegisterAjax' ] );

        add_filter( 'single_template', [$this, 'RegisterVikCalSingleEventTemplate'] );
        add_filter( 'archive_template', [$this, 'RegisterVikCalArchiveEventTemplate'] );

        wp_register_style( 'VikCalStyle', VikCalPath() . 'assets/style.css' );
        wp_enqueue_style( 'VikCalStyle' );

        wp_register_script( 'VikCalScript', VikCalPath() . 'js/vikcal.js', array('jquery') );
        wp_enqueue_script( 'VikCalScript' );

        add_shortcode('vikcal', [ $this, 'DisplayVikCalSc' ] );

    }

    /**
     * Register ajaxurl
     */
    public function RegisterAjax() {
        ?>
        <script type="text/javascript">
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
        <?php
    }

    /**
     * Actions and enqueues for admin-only scripts and actions
     */
    private function initAdmin()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

//        add_action( 'wp_ajax_VikCalChangeMonth', [ $this, 'VikCalChangeMonth' ] );
        add_action( 'add_meta_boxes', [ $this, 'RegisterVikCalMetaBoxes'] );
        add_action( 'save_post_vikcal', [ $this, 'VikCalSavePost' ] );
    }

    /**
     * Get all needed date information based on given month & year
     *
     * @param string $month
     * @param string $year
     * @param string $changeMonth
     * @return array
     */
    private function parseDateArguments($month, $year, $changeMonth)
    {
        if( $changeMonth == 'next' ) {
            $month = intval($month);

            if( $month == 12 ) {
                $year = intval($year) +1;
                $month = 1;
            } else {
                $month++;
            }

            if( strlen($month) != 2 )
                $month = "0" . $month;
        } else {
            $month = intval($month);

            if( $month == 1 ) {
                $year = intval($year) -1;
                $month = 12;
            } else {
                $month--;
            }

            if( strlen($month) != 2 )
                $month = "0" . $month;
        }

        return ['month' => (string) $month, 'year' => (string) $year];
    }

    /**
     * Get available events for the given date
     *
     * @param string $month
     * @param string $year
     * @return VikCalPost[]
     */
    private function getVikCalPosts($month, $year)
    {
        $VikCalQueryArgs = array(
            'post_type' => $this->VikCalPostTypeName,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'eventDate',
                    'value' => $month.'-'.$year,
                    'compare' => 'LIKE',
                ),
            ),
        );

        $VikCalQuery = new WP_Query( $VikCalQueryArgs );

        /**
         * @var VikCalPost[] $VikCalPosts
         */
        $VikCalPosts = [];

        if( $VikCalQuery->have_posts() )
        {
            while( $VikCalQuery->have_posts() ) : $VikCalQuery->the_post();
                $VikCalId = get_the_ID();
                $eventDate = (int) substr(get_post_meta( $VikCalId, 'eventDate', true), 0, 2);

                $VikCalPosts[$eventDate] = new VikCalPost(
                        $VikCalId,
                        substr(get_post_meta( $VikCalId, 'eventDate', true), 0, 2),
                        get_permalink(),
                        get_the_title()
                    );
            endwhile;
        }

        return $VikCalPosts;
    }

    /**
     * Register VikCal post type
     */
    public function RegisterPostVikCal()
    {
        register_post_type( $this->VikCalPostTypeName,
            array(
                'labels' => array(
                    'name' => __( 'VikCal' ),
                    'singular_name' => __( 'VikCal Event', 'vikcal' ),
                    'add_new_item' => __( 'Add new VikCal Event', 'vikcal' )
                ),
                'capability_type' => 'post',
                'supports' => array( 'title', 'thumbnail', 'editor'),
                'public' => true,
                'has_archive' => true,
                'rewrite' => array(
                    'slug' => 'vcevent',
                ),
                'menu_icon' => 'dashicons-calendar-alt',
            )
        );
    }

    /**
     * Register things strictly related to POST_TYPE
     */
    public function RegisterVikCalMetaBoxes()
    {
        add_meta_box(
            "VikCalDateBox",
            __( "Pick your date", $this->VikCalPostTypeName ),
            [ $this, "VikCalDateBox" ],
            $this->VikCalPostTypeName,
            "side",
            "low");
    }

    /**
     * Output date picker in post edit
     */
    public function VikCalDateBox()
    {
        global $post;
        $custom = get_post_custom($post->ID);
        $eventDate = $custom["eventDate"][0];

        ?>
        <input type="text" id="VikCalDate" name="VikCalDate" value="<?php echo $eventDate;?>"/>
        <?php
    }

    /**
     * Callback to save VikCal post data
     */
    public function VikCalSavePost()
    {
        global $post;
        update_post_meta( $post->ID, "eventDate", $_POST["VikCalDate"]);
    }

    /**
     * Change month ajax action
     */
    public function VikCalChangeMonth()
    {
        $month = $_POST['month'];
        $year = $_POST['year'];
        $changeMonth = $_POST['changeMonth'];

        if( strlen($month) != 2 && intval($month) == 0 )
            die();

        if( intval($year) == 0 )
            die();

        if( $changeMonth != 'next' && $changeMonth != 'prev' )
            die();

        $date = $this->parseDateArguments($month, $year, $changeMonth);
        $this->DisplayVikCal($date['month'], $date['year']);

        die();
    }

    /**
     * Register single vcevent page template
     */
    public function RegisterVikCalSingleEventTemplate($orgtpl)
    {
        global $post;

        if ( $post->post_type == $this->VikCalPostTypeName ) {
            $singleTemplate = VikCalUrl() . 'templates/single-vcevent.php';
            return $singleTemplate;
        }

        return $orgtpl;
    }

    /**
     * Register archive vcevent page template
     */
    public function RegisterVikCalArchiveEventTemplate($orgtpl)
    {
        global $post;

        if ( $post->post_type == $this->VikCalPostTypeName ) {
            $archiveTemplate = VikCalUrl() . 'templates/archive-vcevent.php';
            return $archiveTemplate;
        }

        return $orgtpl;
    }

    /**
     * @param string $month
     * @param string $year
     * Print event calendar on front-end
     */
    public function DisplayVikCal(string $month = null, string $year = null)
    {
        $this->month = !isset($month) || empty($month) ? date('m') : $month;
        $this->year = !isset($year) || empty($year) ? date('Y') : $year;
        $this->daysInMonthAmount = date('t');

        $monthName = date_i18n(
            'F',
            mktime(0,0,0, $this->month, 1, $this->year)
        );

        $calDaysBeforeFirst = date('w', strtotime("$this->year-$this->month-0")); //cal_days_in_month
        $calDaysInMonth = date( 't', strtotime("$this->year-$this->month-01"));

        $day = strtotime('next Monday');
        $weekDays = [];
        for( $i = 0; $i < 7; $i++)
        {
            $weekDays[] = date_i18n( 'D', $day );
            $day = strtotime( '+1 day', $day );
        }

        $this->VikCalSettings = get_option( 'vikcalSettings' );
        $vikcalArrowsPosition = $this->VikCalSettings['DisplaySettings']['ArrowsPosition'] ?? 2;

        /**
         * @var VikCalPost[] $VikCalPosts
         */
        $VikCalPosts = $this->getVikCalPosts($this->month, $this->year);

        ?>

        <div id="VikCal" class="VikCal">
            <input type="hidden" id="VCMonth" name="VCMonth" value="<?php echo $this->month;?>" />
            <input type="hidden" id="VCYear" name="VCYear" value="<?php echo $this->year;?>" />

            <div class="VikCal--holder">
                <div class="VikCal--header">
                    <span><?php echo $monthName . ', ' . $this->year;?></span>
                </div>

                <?php if($this->VikCalSettings['DisplaySettings']['SetShowHeaderDays'] == "1") { ?>
                    <div class="VikCal--days">
                        <?php foreach( $weekDays as $day ) {
                            echo '<span>' . $day . '</span>';
                        } ?>
                    </div>
                <?php } ?>

                <div class="VikCal--body">
                    <ul class="VikCal--row">
                        <?php
                        for( $i = 0; $i < $calDaysBeforeFirst; $i++ ) {
                            echo '<li class="VikCal--cell VikCal--row__emptyCell"></li>';
                        }

                        for( $i = 1; $i <= $calDaysInMonth; $i++ )
                        {
                            $isEventDay = false;
                            $VikCalPost = null;
                            if( isset( $VikCalPosts[$i]) && $VikCalPosts[$i] instanceof VikCalPost )
                            {
                                $isEventDay = true;
                                $VikCalPost = $VikCalPosts[$i];
                            }

                            echo '<li class="VikCal--cell' . ($isEventDay === true ? ' VikCal--cell__event' : '') . '">';
                            echo '<span class="VikCal--cell__day">' . $i . '</span>';
                            if( $isEventDay ) {
                                echo '<a href="' . $VikCalPost->GetPostUrl() . '" class="VikCal--day__event" title="' . $VikCalPost->GetPostTitle() . '">';
                                echo $VikCalPost->GetPostTitle();
                                echo '</a>';
                            }

                            echo '</li>';
                        }

                        for( $i = 0; ($i + $calDaysBeforeFirst + $calDaysInMonth) % 7 != 0; $i++ ) {
                            echo '<li class="VikCal--cell VikCal--row__emptyCell"></li>';
                        }

                        ?>
                    </ul>
                </div>
            </div>

            <?php if( $vikcalArrowsPosition == 1 ) {
                $this->displaySideArrows();
            } else if ( $vikcalArrowsPosition == 2) {
                $this->displayArrowsBelowCalendar(true);
            } else if ( $vikcalArrowsPosition == 3) {
                $this->displayArrowsBelowCalendar(false);
            } else if( $vikcalArrowsPosition == 4 ) {
                $this->displayCalHeaderArrows();
            }?>

        </div>
    <?php
    }

    /**
     * Shortcode handler
     *
     * @param string|null $month
     * @param string|null $year
     * @return false|string
     */
    public function DisplayVikCalSc(string $month = null, string $year = null)
    {
        ob_start();
        $this->DisplayVikCal($month, $year);
        $output = ob_get_clean();

        return $output;
    }

    /**
     * Print out calendar arrows under the calendar
     */
    private function displayArrowsBelowCalendar(bool $withText)
    {
        $arrowsUrls = $this->getArrowsImagesURL();
        ?>

        <div class="VikCal--arrows__below">
            <div class="VikCal--arrows__holder">
                <div class="VikCal--arrows__leftArrow">
                    <span onclick="VikCalPreviousMonth();">
                        <img src="<?php echo $arrowsUrls['RightArrow'];?>" />
                        <?php if($withText) _e( "Previous Month", VikCalPostType::VikCalTextDomain );?>
                    </span>
                </div>
                <div class="VikCal--arrows__rightArrow">
                    <span onclick="VikCalNextMonth();">
                        <?php if($withText) _e( "Next Month", VikCalPostType::VikCalTextDomain );?>
                        <img src="<?php echo $arrowsUrls['LeftArrow'];?>" />
                    </span>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Print out calendar arrows on the sides
     */
    private function displaySideArrows()
    {
        ?>

        <div class="VikCal--arrows__side arrow--side__left">
            <span onclick="VikCalPreviousMonth();">
                <img src="<?php echo VikCalPath() . 'assets/arrow-prev.png';?>" alt="Previous month" />
            </span>
        </div>
        <div class="VikCal--arrows__side arrow--side__right">
            <span onclick="VikCalNextMonth();">
                <img src="<?php echo VikCalPath() . 'assets/arrow-next.png';?>" alt="Next month" />
            </span>
        </div>
        <?php
    }

    /**
     * Print out calendar arrows in the header
     */
    private function displayCalHeaderArrows()
    {
        ?>

        <div class="VikCal--arrows__header arrow--header__left">
            <span onclick="VikCalPreviousMonth();">
                <img src="<?php echo VikCalPath() . 'assets/arrow-prev.png';?>" alt="Previous month" />
            </span>
        </div>
        <div class="VikCal--arrows__header arrow--header__right">
            <span onclick="VikCalNextMonth();">
                <img src="<?php echo VikCalPath() . 'assets/arrow-next.png';?>" alt="Next month" />
            </span>
        </div>
        <?php
    }

    /**
     * Get calendar arrows images url
     *
     * @return array
     */
    private function getArrowsImagesURL() : array
    {
        $urls = [];

        if( isset($this->VikCalSettings['DisplaySettings']['LeftArrowImage'])
            && !empty($this->VikCalSettings['DisplaySettings']['LeftArrowImage']) )
        {
            $leftArrowImage = wp_get_attachment_image_src( $this->VikCalSettings['DisplaySettings']['LeftArrowImage']);
        }

        if( isset($this->VikCalSettings['DisplaySettings']['RightArrowImage'])
            && !empty($this->VikCalSettings['DisplaySettings']['RightArrowImage']) )
        {
            $rightArrowImage = wp_get_attachment_image_src( $this->VikCalSettings['DisplaySettings']['RightArrowImage']);
        }

        $leftArrowImageUrl = $leftArrowImage[0] ?? VikCalPath() . 'assets/arrow-prev.png';
        $rightArrowImageUrl = $rightArrowImage[0] ?? VikCalPath() . 'assets/arrow-next.png';

        return [
                'RightArrow' => $leftArrowImageUrl,
                'LeftArrow' => $rightArrowImageUrl,
        ];
    }

}