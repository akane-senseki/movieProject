<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $kind;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $int_value;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $string_value;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $date_value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKind(): ?int
    {
        return $this->kind;
    }

    public function setKind(int $kind): self
    {
        $this->kind = $kind;

        return $this;
    }

    public function getIntValue(): ?int
    {
        return $this->int_value;
    }

    public function setIntValue(int $int_value): self
    {
        $this->int_value = $int_value;

        return $this;
    }

    public function getStringValue(): ?string
    {
        return $this->string_value;
    }

    public function setStringValue(?string $string_value): self
    {
        $this->string_value = $string_value;

        return $this;
    }

    public function getDateValue(): ?\DateTimeImmutable
    {
        return $this->date_value;
    }

    public function setDateValue(?\DateTimeImmutable $date_value): self
    {
        $this->date_value = $date_value;

        return $this;
    }
}
