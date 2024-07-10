<?php

namespace App\Entity;

use App\Repository\MemberStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberStatusRepository::class)]
#[ORM\Table(name: 'staatused')]
class MemberStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'nimi', length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $prefix = null;

    #[ORM\Column(length: 10)]
    private ?string $suffix = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }
}
