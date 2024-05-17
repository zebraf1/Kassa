<?php

namespace App\Entity;

use App\Repository\ConventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConventRepository::class)]
#[ORM\Table(name: 'koondised')]
class Convent implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'nimi', length: 100)]
    private ?string $name = null;

    #[ORM\Column(name: 'kassa_aktiivne', type: Types::INTEGER, length: 1, nullable: true)]
    private ?int $isActive = 0;

    /**
     * @var Collection<int, ProductInfo>
     */
    #[ORM\OneToMany(targetEntity: ProductInfo::class, mappedBy: 'convent', orphanRemoval: true)]
    private Collection $productInfos;

    public function __construct()
    {
        $this->productInfos = new ArrayCollection();
    }

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

    public function isActive(): bool
    {
        return (bool)$this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = (int)$isActive;

        return $this;
    }

    /**
     * @return Collection<int, ProductInfo>
     */
    public function getProductInfos(): Collection
    {
        return $this->productInfos;
    }

    public function addProductInfo(ProductInfo $productInfo): static
    {
        if (!$this->productInfos->contains($productInfo)) {
            $this->productInfos->add($productInfo);
            $productInfo->setConvent($this);
        }

        return $this;
    }

    public function removeProductInfo(ProductInfo $productInfo): static
    {
        if ($this->productInfos->removeElement($productInfo)) {
            // set the owning side to null (unless already changed)
            if ($productInfo->getConvent() === $this) {
                $productInfo->setConvent(null);
            }
        }

        return $this;
    }

    /**
     * @var Setting[]
     */
    private array $settings = [];

    /**
     * @param Setting[] $settings
     * @return $this
     * @throws \RuntimeException
     */
    public function setSettings(array $settings): static
    {
        if (!empty($settings) && !(reset($settings) instanceof Setting)) {
            throw new \RuntimeException('Invalid object given, Setting expected');
        }
        $this->settings = $settings;
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        /** @see Setting::$reference and value must have public access */
        $settings = array_column($this->settings, 'value', 'reference');

        return  [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'settings' => (object)$settings,
        ];
    }
}
