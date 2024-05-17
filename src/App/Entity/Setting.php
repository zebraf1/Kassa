<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: 'ollekassa_setting')]
class Setting
{
    public const OBJECT_CONVENT = 'convent';
    public const REFERENCE_CURRENT_CASH = 'currentCash';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $object = null;

    #[ORM\Column(name: 'object_id')]
    private ?int $objectId = null;

    #[ORM\Column(length: 100)]
    public ?string $reference = null; // public for array_column access

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $value = null; // public for array_column access

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): static
    {
        $this->object = $object;

        return $this;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function setObjectId(int $objectId): static
    {
        $this->objectId = $objectId;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function setConvent(Convent $convent): static
    {
        return $this
            ->setObject(self::OBJECT_CONVENT)
            ->setObjectId($convent->getId())
        ;
    }
}
