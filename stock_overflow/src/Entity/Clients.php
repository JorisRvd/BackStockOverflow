<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientsRepository::class)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /**
     *@Groups({"get_client"})
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    /**
     *@Groups({"get_client", "get_shippings"})
     */
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    /**
     *@Groups({"get_client", "get_shippings"})
     */
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    /**
     *@Groups({"get_client", "get_shippings"})
     */
    private ?string $address = null;

    #[ORM\Column]
    /**
     *@Groups({"get_client", "get_shippings"})
     */
    private ?int $zip_code = null;

    #[ORM\Column(length: 255)]
    /**
     *@Groups({"get_client", "get_shippings"})
     */
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'clients', targetEntity: Shipping::class)]
    /**
     *@Groups({"get_client"})
     */
    private Collection $shippings;

    public function __construct()
    {
        $this->shippings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?int
    {
        return $this->zip_code;
    }

    public function setZipCode(int $zip_code): static
    {
        $this->zip_code = $zip_code;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Shipping>
     */
    public function getShippings(): Collection
    {
        return $this->shippings;
    }

    public function addShipping(Shipping $shipping): static
    {
        if (!$this->shippings->contains($shipping)) {
            $this->shippings->add($shipping);
            $shipping->setClients($this);
        }

        return $this;
    }

    public function removeShipping(Shipping $shipping): static
    {
        if ($this->shippings->removeElement($shipping)) {
            // set the owning side to null (unless already changed)
            if ($shipping->getClients() === $this) {
                $shipping->setClients(null);
            }
        }

        return $this;
    }
}
