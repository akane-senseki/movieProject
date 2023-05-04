<?php

namespace App\Entity;

use App\Repository\LiverRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LiverRepository::class)]
class Liver
{
    #[ORM\Id]
    #[ORM\Column(type: 'bigint', length: 255)]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $twitter_id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $channel_id;

    #[ORM\Column(type: 'boolean')]
    private $can_select;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    public function setTwitterId(?string $twitter_id)
    {
        $this->twitter_id = $twitter_id;

        return $this;
    }

    public function getChannelId()
    {
        return $this->channel_id;
    }

    public function setChannelId(int $channel_id)
    {
        $this->channel_id = $channel_id;

        return $this;
    }

    /**
     * Get the value of can_select
     */ 
    public function getCanSelect()
    {
        return $this->can_select;
    }

    /**
     * Set the value of can_select
     *
     * @return  self
     */ 
    public function setCanSelect($can_select)
    {
        $this->can_select = $can_select;

        return $this;
    }
}
