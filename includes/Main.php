<?php

namespace VikCal;

class Main {

    private static $instance;
    private $VikCalPostType;
    private $VikCalAdminSettings;
    private $isAdmin;

    public function __construct()
    {
        $this->isAdmin = is_admin();
        $this->VikCalPostType = new VikCalPostType( $this->isAdmin );

        if( $this->isAdmin ) {
            $this->VikCalAdminSettings = new VikCalAdminSettings();
        }
    }

    public static function init()
    {
        if( !isset( self::$instance ) && !( self::$instance instanceof Main) ) {
            self::$instance = new Main();
        }

        return self::$instance;
    }
}