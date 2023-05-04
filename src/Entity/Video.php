<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{

    public static $ORIGINAL = 1; //本家
    public static $CLIP = 2;     //切り抜き
    public static $MMD = 3;      //MMD
    public static $DRAW = 4;     //描いてみた
    public static $DANCE = 5;     //踊ってみた


    public static $category_list = [
        2 => '切り抜き',
        3 => 'MMD',
        4 => '描いてみた',
        5 => '踊ってみた',
    ];


    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $channel_id;

    #[ORM\Column(type: 'string', nullable: true, length: 1500)]
    private $description;

    #[ORM\Column(type: 'datetime')]
    private $published_at;

    #[ORM\Column(type: 'string')]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    private $path;

    #[ORM\Column(type: 'bigint')]
    private $category;

    #[ORM\Column(type: 'bigint')]
    private $member;


    public function setId($id)
    {
        $this->id = $id;
        self::$category_list;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getChannelId()
    {
        return $this->channel_id;
    }

    public function setChannelId($channel_id)
    {
        $this->channel_id = $channel_id;

        return $this;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getPublishedAt()
    {
        return $this->published_at;
    }

    public function setPublishedAt($published_at)
    {
        $this->published_at = new \DateTime($published_at);
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of category
     */ 
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the value of category
     *
     * @return  self
     */ 
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the value of member
     */ 
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set the value of member
     *
     * @return  self
     */ 
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }
}
