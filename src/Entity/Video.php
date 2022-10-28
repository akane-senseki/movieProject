<?php

namespace App\Entity;

use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $channel_id;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private $description;

    #[ORM\Column(type: 'datetime_immutable')]
    private $published_at;

    #[ORM\Column(type: 'string', length: 1000)]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    private $path;

    public function setId($id)
    {
        $this->id = $id;

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

    public function setChannelId(int $channel_id)
    {
        $this->channel_id = $channel_id;

        return $this;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(\DateTimeImmutable $published_at)
    {
        $this->published_at = $published_at;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }
}
