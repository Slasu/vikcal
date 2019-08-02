<?php

namespace VikCal;

class VikCalAdminSettings
{
    use VikCalTrait;

    const VikCalTextDomain = 'vikcal';

    public function __construct()
    {
        $this->InitSettings();
    }

    /**
     * @param array $vikcalDisplaySettings
     * @param array $cssSettings
     */
    private function generateCssFile( $vikcalDisplaySettings, $cssSettings )
    {
        $cssFile = file( plugin_dir_path( __FILE__ ) . '../assets/style.css' );

        $settingValue = '';
        $changeSetting = false;
        $output = [];
        foreach( $cssFile as $line )
        {
            if( $changeSetting ) {
                echo $line . ' => ';
                $changeSetting = false;
                $lineSplit = explode( ';', $line);
                $lineSplit[0] = substr($lineSplit[0], 0, strpos($lineSplit[0], ':')) .': '. $settingValue . ';';
                $line = implode($lineSplit);
                echo $line . '<br />';
                $output[] = $line;
            } else {
                $output[] = $line;
            }

            foreach( $cssSettings as $setting ) {
                if( strpos($line, $setting) ) {
                    $changeSetting = true;
                    $settingValue = $vikcalDisplaySettings[$setting];
                }
            }
        }

        $cssFile = fopen( plugin_dir_path( __FILE__ ) . '../assets/style.css', 'r+' );
        flock($cssFile, LOCK_EX);

        foreach( $output as $line ) {
            fwrite($cssFile, $line);
        }

        flock($cssFile, LOCK_UN);
        fclose($cssFile);
    }

    /**
     * Actions and enqueues for admin-only scripts and actions
     */
    public function InitSettings()
    {
        add_action( 'admin_menu', [ $this, 'VikCalOptionsPage' ] );
        add_action( 'admin_init', [ $this, 'RegisterCSSSettings' ]);

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'VikCalAdminScripts', VikCalPath() . 'js/adminScripts.js', array( 'wp-color-picker' ), false, true );
        wp_enqueue_style( 'VikCalAdminStyles', VikCalPath() . 'assets/adminstyle.css' );
    }

    /**
     * Initialize options page
     */
    public function VikCalOptionsPage() {
        add_submenu_page(
            'edit.php?post_type=' . $this->VikCalPostTypeName,
            __('Display Settings', $this->VikCalPostTypeName),
            __('Display Settings', $this->VikCalPostTypeName),
            'manage_options',
            'VikCalDisplaySettings',
            [ $this, 'DisplayCssSettingsPage']
        );
    }

    /**
     * Register settings
     */
    public function RegisterCSSSettings()
    {

        register_setting(
            $this->VikCalPostTypeName . 'DisplaySettings', //setting page name?
            $this->VikCalPostTypeName . 'Settings', //setting name in database
            [
                'type' => 'string',
                'group' => $this->VikCalPostTypeName . 'DisplaySettings',
                'description' => 'VikCal CSS Settings',
                'sanitize_callback' => [ $this, 'SaveVikCalDisplaySettings' ],
                'show_in_rest' => false,
            ]
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'HeaderMonthFontColor',
            'Header date (month & year) font color',
            array( $this, 'SetMonthFontColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'HeaderMonthFontWeight',
            'Header date (month & year) font weight',
            array( $this, 'SetMonthFontWeight' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'ShowHeaderDays',
            'Show day names?',
            array( $this, 'SetShowHeaderDays' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'HeaderDaysFontColor',
            'Header day names font color',
            array( $this, 'SetDayNameFontColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'HeaderDaysFontWeight',
            'Header day names font weight',
            array( $this, 'SetDayNameFontWeight' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'HeaderBGColor',
            'Header background & calendar lines color',
            array( $this, 'SetHeaderBGColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'DayFontColor',
            'Calendar Day numbers font color',
            array( $this, 'SetCalDayFontColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'DayFontWeight',
            'Calendar Day numbers font weight',
            array( $this, 'SetCalDayFontWeight' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'EventTitleFontColor',
            'Event title font color',
            array( $this, 'SetEventFontColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'EventTitleFontWeight',
            'Event title font weight',
            array( $this, 'SetEventFontWeight' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );


        add_settings_section(
            $this->VikCalPostTypeName . 'EventBackgroundColor',
            'EventBackgroundColor',
            array( $this, 'SetEventBackgroundColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'NextPrevColor',
            'Next / Prev text & arrows color',
            array( $this, 'SetNextPrevColor' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'NextPrevFontWeight',
            'Next / Prev text font weight',
            array( $this, 'SetNextPrevFontWeight' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'LeftArrowImage',
            'Left arrow image',
            array( $this, 'SetLeftArrowImage' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'RightArrowImage',
            'Right arrow image',
            array( $this, 'SetRightArrowImage' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );

        add_settings_section(
            $this->VikCalPostTypeName . 'ArrowsPosition',
            'Calendar arrows settings',
            array( $this, 'SetArrowsPosition' ),
            $this->VikCalPostTypeName . 'DisplaySettings'
        );
    }

/*    public function VikCalChangeMonth()
    {
        $month = $_POST['VCMonth'];
        $year = $_POST['VCYear'];
    }*/

    /**
     * Settings page
     */
    public function DisplayCssSettingsPage()
    {
        wp_enqueue_media();
        echo '<form method="post" action="options.php">';
            echo '<h1>VikCal Display Settings</h1>';
            settings_fields($this->VikCalPostTypeName . 'DisplaySettings');
            do_settings_sections($this->VikCalPostTypeName . 'DisplaySettings');
            submit_button();
        echo '</form>';
    }

//region DisplaySettings
    public function SetMonthFontColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $headerMonthFontColor = $vikcalDisplaySettings['DisplaySettings']['HeaderMonthFontColor'];

        ?>
        <label for="HeaderMonthFontColor"><?php _e('Calendar header Month & Year font color:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="HeaderMonthFontColor" name="HeaderMonthFontColor" class="vikcal--colorfield" value="<?php echo $headerMonthFontColor;?>"/>
        <?php
    }

    public function SetMonthFontWeight()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $HeaderMonthFontWeight = $vikcalDisplaySettings['DisplaySettings']['HeaderMonthFontWeight'];
        ?>
        <label for="HeaderMonthFontWeight"><?php _e('Calendar header Month & Year font weight:', VikCalAdminSettings::VikCalTextDomain);?></label>
            <select id="HeaderMonthFontWeight" name="HeaderMonthFontWeight">
                <option value="100"<?php if( $HeaderMonthFontWeight == '100' ) echo ' selected';?>>Thick</option>
                <option value="400"<?php if( $HeaderMonthFontWeight == '400' ) echo ' selected';?>>Normal</option>
                <option value="600"<?php if( $HeaderMonthFontWeight == '600' ) echo ' selected';?>>Semi-Bold</option>
                <option value="900"<?php if( $HeaderMonthFontWeight == '900' ) echo ' selected';?>>Bold</option>
            </select>
        <?php
    }

    public function SetShowHeaderDays()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $SetShowHeaderDays = $vikcalDisplaySettings['DisplaySettings']['SetShowHeaderDays'];
        ?>
        <label for="SetShowHeaderDays"><?php _e('Show day names in calendar header?', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="SetShowHeaderDays" name="SetShowHeaderDays">
            <option value="1"<?php if( $SetShowHeaderDays == '1' ) echo ' selected';?>>Yes</option>
            <option value="2"<?php if( $SetShowHeaderDays == '2' ) echo ' selected';?>>No</option>
        </select>
        <?php
    }

    public function SetDayNameFontColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $DayNameFontColor = $vikcalDisplaySettings['DisplaySettings']['DayNameFontColor'];

        ?>
        <label for="DayNameFontColor"><?php _e( 'Calendar days font color:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="DayNameFontColor" name="DayNameFontColor" class="vikcal--colorfield" value="<?php echo $DayNameFontColor;?>"/>
        <?php
    }

    public function SetDayNameFontWeight()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $DayNameFontWeight = $vikcalDisplaySettings['DisplaySettings']['HeaderDaysFontWeight'];
        ?>
        <label for="HeaderDaysFontWeight"><?php _e('Calendar days names font weight:', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="HeaderDaysFontWeight" name="HeaderDaysFontWeight">
            <option value="100"<?php if( $DayNameFontWeight == '100' ) echo ' selected';?>>Thick</option>
            <option value="400"<?php if( $DayNameFontWeight == '400' ) echo ' selected';?>>Normal</option>
            <option value="600"<?php if( $DayNameFontWeight == '600' ) echo ' selected';?>>Semi-Bold</option>
            <option value="900"<?php if( $DayNameFontWeight == '900' ) echo ' selected';?>>Bold</option>
        </select>
        <?php
    }

    public function SetCalDayFontColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $CalDayFontColor = $vikcalDisplaySettings['DisplaySettings']['CalDayFontColor'];

        ?>
        <label for="CalDayFontColor"><?php _e( 'Calendar days font color:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="CalDayFontColor" name="CalDayFontColor" class="vikcal--colorfield" value="<?php echo $CalDayFontColor;?>"/>
        <?php
    }

    public function SetEventFontColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $EventFontColor = $vikcalDisplaySettings['DisplaySettings']['EventFontColor'];

        ?>
        <label for="EventFontColor"><?php _e( 'Event title font color:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="EventFontColor" name="EventFontColor" class="vikcal--colorfield" value="<?php echo $EventFontColor;?>"/>
        <?php
    }

    public function SetEventBackgroundColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $EventBackgroundColor = $vikcalDisplaySettings['DisplaySettings']['EventBackgroundColor'];

        ?>
        <label for="EventBackgroundColor"><?php _e( 'Event background color:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="EventBackgroundColor" name="EventBackgroundColor" class="vikcal--colorfield" value="<?php echo $EventBackgroundColor;?>"/>
        <?php
    }

    public function SetCalDayFontWeight()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $CalDayFontWeight = $vikcalDisplaySettings['DisplaySettings']['CalDayFontWeight'];
        ?>
        <label for="CalDayFontWeight"><?php _e('Calendar days font weight:', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="CalDayFontWeight" name="CalDayFontWeight">
            <option value="100"<?php if( $CalDayFontWeight == '100' ) echo ' selected';?>>Thick</option>
            <option value="400"<?php if( $CalDayFontWeight == '400' ) echo ' selected';?>>Normal</option>
            <option value="600"<?php if( $CalDayFontWeight == '600' ) echo ' selected';?>>Semi-Bold</option>
            <option value="900"<?php if( $CalDayFontWeight == '900' ) echo ' selected';?>>Bold</option>
        </select>
        <?php
    }

    public function SetEventFontWeight()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $EventTitleFontWeight = $vikcalDisplaySettings['DisplaySettings']['EventTitleFontWeight'];
        ?>
        <label for="EventTitleFontWeight"><?php _e('Event title font weight:', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="EventTitleFontWeight" name="EventTitleFontWeight">
            <option value="100"<?php if( $EventTitleFontWeight == '100' ) echo ' selected';?>>Thick</option>
            <option value="400"<?php if( $EventTitleFontWeight == '400' ) echo ' selected';?>>Normal</option>
            <option value="600"<?php if( $EventTitleFontWeight == '600' ) echo ' selected';?>>Semi-Bold</option>
            <option value="900"<?php if( $EventTitleFontWeight == '900' ) echo ' selected';?>>Bold</option>
        </select>
        <?php
    }

    public function SetNextPrevColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $NextPrevColor = $vikcalDisplaySettings['DisplaySettings']['NextPrevColor'];

        ?>
        <label for="NextPrevColor"><?php _e( 'Next/Prev arrows and/or text colors:', VikCalAdminSettings::VikCalTextDomain );?></label>
        <input type="text" id="NextPrevColor" name="NextPrevColor" class="vikcal--colorfield" value="<?php echo $NextPrevColor;?>"/>
        <?php
    }

    public function SetNextPrevFontWeight()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $NextPrevFontWeight = $vikcalDisplaySettings['DisplaySettings']['NextPrevFontWeight'];
        ?>
        <label for="NextPrevFontWeight"><?php _e('Calendar days names font weight:', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="NextPrevFontWeight" name="NextPrevFontWeight">
            <option value="100"<?php if( $NextPrevFontWeight == '100' ) echo ' selected';?>>Thick</option>
            <option value="400"<?php if( $NextPrevFontWeight == '400' ) echo ' selected';?>>Normal</option>
            <option value="600"<?php if( $NextPrevFontWeight == '600' ) echo ' selected';?>>Semi-Bold</option>
            <option value="900"<?php if( $NextPrevFontWeight == '900' ) echo ' selected';?>>Bold</option>
        </select>
        <?php
    }

    public function SetHeaderBGColor()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $HeaderBGColor = $vikcalDisplaySettings['DisplaySettings']['HeaderBGColor'];

        ?>
        <label for="HeaderBGColor"><?php _e( 'Calendar Header (date&days) background color:', VikCalAdminSettings::VikCalTextDomain );?>r:</label>
        <input type="text" id="HeaderBGColor" name="HeaderBGColor" class="vikcal--colorfield" value="<?php echo $HeaderBGColor;?>"/>
        <?php
    }

    public function SetLeftArrowImage()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $LeftArrowImage = $vikcalDisplaySettings['DisplaySettings']['LeftArrowImage'];
        $LeftArrowImageURL = wp_get_attachment_image_src($LeftArrowImage)[0] ?? '';
        ?>
        <div class="vc--ArrowImage__left">
            <div class='image-preview-wrapper'>
                <img id='VCLeftArrowImagePreview' class="vc--image__preview" src='<?php echo $LeftArrowImageURL;?>' width='100' height='100' style='max-height: 100px; width: 100px;'>
            </div>
            <input id="VCLeftArrowImageButton" type="button" class="button VCMediaSelectorButton" value="<?php _e( 'Upload image' ); ?>" />
            <input type='hidden' name='LeftArrowImage' class="vc--image" id='LeftArrowImage' value='<?php echo $LeftArrowImage;?>'>
        </div>
        <?php
    }

    public function SetRightArrowImage()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $RightArrowImage = $vikcalDisplaySettings['DisplaySettings']['RightArrowImage'];
        $RightArrowImageURL = wp_get_attachment_image_src($RightArrowImage)[0] ?? '';
        ?>
        <div class="vc--ArrowImage__right">
            <div class='image-preview-wrapper'>
                <img id='VCRightArrowImagePreview' class="vc--image__preview" src='<?php echo $RightArrowImageURL;?>' width='100' height='100' style='max-height: 100px; width: 100px;'>
            </div>
            <input id="VCRightArrowImageButton" type="button" class="button VCMediaSelectorButton" value="<?php _e( 'Upload image' ); ?>" />
            <input type='hidden' name='RightArrowImage' class="vc--image" id='RightArrowImage' value='<?php echo $RightArrowImage;?>'>
        </div>
        <?php
    }

    public function SetArrowsPosition()
    {
        $vikcalDisplaySettings = get_option('vikcalSettings');
        $ArrowsPosition = $vikcalDisplaySettings['DisplaySettings']['ArrowsPosition'];
        ?>
        <label for="ArrowsPosition"><?php _e('Choose calendar arrows type:', VikCalAdminSettings::VikCalTextDomain);?></label>
        <select id="ArrowsPosition" name="ArrowsPosition">
            <option value="1"<?php if( $ArrowsPosition == '1' ) echo ' selected';?>>On the sides</option>
            <option value="2"<?php if( $ArrowsPosition == '2' ) echo ' selected';?>>Below calendar - with text</option>
            <option value="3"<?php if( $ArrowsPosition == '3' ) echo ' selected';?>>Below calendar - without text</option>
            <option value="4"<?php if( $ArrowsPosition == '4' ) echo ' selected';?>>In calendar header</option>
        </select>
        <?php
    }
//endregion DisplaySettings

    /**
     * Save DisplaySettings to DB and perform actions on data
     */
    public function SaveVikCalDisplaySettings()
    {
        $vikcalSettings = get_option('vikcalSettings');
        $vikcalDisplaySettings = $vikcalSettings['DisplaySettings'] ?? [];

        $cssColorSettings = [
            'HeaderMonthFontColor',
            'DayNameFontColor',
            'CalDayFontColor',
            'EventFontColor',
            'EventBackgroundColor',
            'NextPrevColor',
            'HeaderBGColor',
        ];

        foreach( $cssColorSettings as $setting ) {
            if( isset( $_POST[$setting] ) && !empty( $_POST[$setting]) )
            {
                $color = sanitize_hex_color( $_POST[$setting] );

                if( !empty($color) )
                    $vikcalDisplaySettings[$setting] = $_POST[$setting];
            }
        }

        $cssFontWeightSettings = [
            'HeaderMonthFontWeight',
            'HeaderDaysFontWeight',
            'CalDayFontWeight',
            'EventTitleFontWeight',
            'NextPrevFontWeight',
        ];

        foreach( $cssFontWeightSettings as $setting ) {
            if( isset( $_POST[$setting] ) && !empty( $_POST[$setting]) ) {
                if( in_array($_POST[$setting], ['100', '400', '600', '900']) ) {
                    $vikcalDisplaySettings[$setting] = $_POST[$setting];
                }
            }
        }

        $displaySettings = [
            'ArrowsPosition',
            'SetShowHeaderDays',
            'LeftArrowImage',
            'RightArrowImage',
        ];

        foreach( $displaySettings as $setting ) {
            if( isset( $_POST[$setting] ) && !empty( $_POST[$setting]) ) {
                $vikcalDisplaySettings[$setting] = sanitize_text_field( $_POST[$setting] );
            }
        }


        $vikcalSettings['DisplaySettings'] = $vikcalDisplaySettings;

        $this->generateCssFile( $vikcalDisplaySettings, array_merge($cssColorSettings, $cssFontWeightSettings) );

        return $vikcalSettings;
    }
}