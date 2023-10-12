<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    /**
     *  @Groups({"get_products"})
     *  @Groups({"get_category"})
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    /**
     *  @Groups({"get_category"})
     *  @Groups({"get_products", "get_orders"})
     */
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    /**
     *  @Groups({"get_products"})
     *  @Groups({"get_category"})
     */
    private ?string $description = null;

    #[ORM\Column]
    /**
     *  @Groups({"get_products"})
     *  @Groups({"get_category"})
     */
    private ?int $quantity = null;

    #[ORM\Column]
    /**
     *  @Groups({"get_products"})
     *  @Groups({"get_category"})
     */
    private ?int $price = null;

    #[ORM\Column]
    /**
     *  @Groups({"get_products"})
     *  @Groups({"get_category"})
     */
    private ?bool $is_active = null;

    #[ORM\ManyToOne(inversedBy: 'products', cascade:['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    /**
     *  @Groups({"get_products"})
     */
    private ?ProductCategory $product_category = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ShippingItem::class)]
    private Collection $shippingItems;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Order::class)]
    private Collection $orders;

    public function __construct()
    {
        $this->shippingItems = new ArrayCollection();
        $this->orders = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getProductCategory(): ?ProductCategory
    {
        return $this->product_category;
    }

    public function setProductCategory(?ProductCategory $product_category): static
    {
        $this->product_category = $product_category;

        return $this;
    }

    /**
     * @return Collection<int, ShippingItem>
     */
    public function getShippingItems(): Collection
    {
        return $this->shippingItems;
    }

    public function addShippingItem(ShippingItem $shippingItem): static
    {
        if (!$this->shippingItems->contains($shippingItem)) {
            $this->shippingItems->add($shippingItem);
            $shippingItem->setProduct($this);
        }

        return $this;
    }

    public function removeShippingItem(ShippingItem $shippingItem): static
    {
        if ($this->shippingItems->removeElement($shippingItem)) {
            // set the owning side to null (unless already changed)
            if ($shippingItem->getProduct() === $this) {
                $shippingItem->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setProduct($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getProduct() === $this) {
                $order->setProduct(null);
            }
        }

        return $this;
    }
}
