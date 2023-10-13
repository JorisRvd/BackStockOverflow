<?php

namespace App\Entity;

use App\Enums\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /**
     * 
     *@Groups({"get_orders"})
     */
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    /**
     *@Groups({"get_orders"})
     */
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    /**
     * 
     *@Groups({"get_orders"})
     */
    private ?int $quantity = null;

    #[ORM\Column(type: "string", enumType: OrderStatus::class)]
    /**
     * 
     *@Groups({"get_orders"})
     */
    private OrderStatus $status;
    
    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    /**
     * 
     *@Groups({"get_orders"})
     */
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    /**
     * 
     *@Groups({"get_orders"})
     */
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

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

    public function __construct()
    {
        $this->status = OrderStatus::En_attente;
    }
    public function getStatus() : ?OrderStatus
    {
        
        return $this->status;
    }

    public function setStatus(?OrderStatus $status) : static
    {
        $this->status = $status;
        
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
