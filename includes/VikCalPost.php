<?php

namespace VikCal;

class VikCalPost
{
    private $postId;
    private $postEventDay;
    private $postUrl;
    private $postTitle;

    public function __construct($id, $day, $url, $title)
    {
        $this->postId = $id;
        $this->postEventDay = $day;
        $this->postUrl = $url;
        $this->postTitle = $title;
    }

    public function SetPostId($id)
    {
        $this->postId = $id;
    }

    public function SetPostEventDay($day)
    {
        $this->postEventDay = $day;
    }

    public function SetPostUrl($url)
    {
        $this->postUrl = $url;
    }

    public function SetPostTitle($title)
    {
        $this->postTitle = $title;
    }

    public function GetPostId()
    {
        return $this->postId;
    }

    public function GetPostEventDay()
    {
        return $this->postEventDay;
    }

    public function GetPostUrl()
    {
        return $this->postUrl;
    }

    public function GetPostTitle()
    {
        return $this->postTitle;
    }
}
