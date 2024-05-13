<?php

namespace App\Entity;

use App\Repository\OllekassaProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OllekassaProductRepository::class)]
#[ORM\Table(name: 'ollekassa_product')]
class Product implements \JsonSerializable
{
    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_DISABLED = 'DISABLED';

    public const AMOUNT_TYPE_PIECE = 'PIECE';
    public const AMOUNT_TYPE_LITRE = 'LITRE';
    public const AMOUNT_TYPE_CENTI_LITRE = 'CENTI_LITRE';
    public const AMOUNT_TYPE_KG = 'KG';
    public const AMOUNT_TYPE_G = 'G';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $productCode = null;

    #[ORM\Column(nullable: true)]
    private ?int $productGroupId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 50)]
    private ?string $amountType = self::AMOUNT_TYPE_PIECE;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?float $amount = 1.00;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = self::STATUS_DISABLED; // TODO: is it used?

    #[ORM\Column(nullable: true)]
    private ?int $seq = null;

    /**
     * @var Collection<int, ProductInfo>
     */
    #[ORM\OneToMany(targetEntity: ProductInfo::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productInfos;
    public static ?int $activeConventId;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?ProductGroup $productGroup = null;

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

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function setProductCode(?string $productCode): static
    {
        $this->productCode = $productCode;

        return $this;
    }

    public function getProductGroupId(): ?int
    {
        return $this->productGroupId;
    }

    public function setProductGroupId(?int $productGroupId): static
    {
        $this->productGroupId = $productGroupId;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAmountType(): ?string
    {
        return $this->amountType;
    }

    public function setAmountType(string $amountType): static
    {
        $this->amountType = $amountType;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSeq(): ?int
    {
        return $this->seq;
    }

    public function setSeq(?int $seq): static
    {
        $this->seq = $seq;

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
            $productInfo->setProduct($this);
        }

        return $this;
    }

    public function removeProductInfo(ProductInfo $productInfo): static
    {
        if ($this->productInfos->removeElement($productInfo)) {
            // set the owning side to null (unless already changed)
            if ($productInfo->getProduct() === $this) {
                $productInfo->setProduct(null);
            }
        }

        return $this;
    }

    public function getProductGroup(): ?ProductGroup
    {
        return $this->productGroup;
    }

    public function setProductGroup(?ProductGroup $productGroup): static
    {
        $this->productGroup = $productGroup;

        return $this;
    }

    public function jsonSerialize(): array
    {
        /** @var ProductInfo $activeProductInfo */
        $activeProductInfo = null;
        $conventId = self::$activeConventId;

        /** @var ProductInfo $productInfo */
        foreach ($this->getProductInfos() as $productInfo) {
            if ($productInfo->getConventId() === $conventId) {
                $activeProductInfo = $productInfo;
            }
        }

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'productCodes' => !empty($this->getProductCode()) ? explode(',', $this->getProductCode()) : [],
            'price' => $activeProductInfo ? round($activeProductInfo->getPrice(), 2) : null,
            'amount' => round($this->getAmount(), 2),
            'amountType' => $this->getAmountType(),
            'status' => $activeProductInfo ? $activeProductInfo->getStatus() : null,
            'productGroupId' => $this->getProductGroupId(),
            'inventoryCounts' => $activeProductInfo ?
                [
                    'warehouse' => $activeProductInfo->getWarehouseCount(),
                    'storage' => $activeProductInfo->getStorageCount(),
                ] : null,
            'resourceType' => $activeProductInfo ? $activeProductInfo->getResourceType() : null,
        ];
    }
}
