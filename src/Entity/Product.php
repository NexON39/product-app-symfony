<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    // nazwa użytkownika zamiast relacji -> po zmianie nazwy użytkownika, przestanie działać
    // tu powinna być relacja do encji użytkownika, użytkownik nie powinien być pusty
    #[ORM\Column(length: 255)]
    private ?string $ownerName = null;

    // nazwa produktu nie powinna być pusta
    #[ORM\Column(length: 255)]
    private ?string $productName = null;

    // cena nie powinna być pusta, powinna być typu float
    #[ORM\Column]
    private ?int $price = null;

    // tu powinna być kolekcja, relacja do osobnej encji Opinion, nie string
    #[ORM\Column(length: 10000, nullable: true)]
    private ?string $opinions = null;

    #[ORM\OneToMany(mappedBy: 'content', targetEntity: Reviews::class)]
    private Collection $reviews;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnerName(): ?string
    {
        return $this->ownerName;
    }

    public function setOwnerName(string $ownerName): self
    {
        $this->ownerName = $ownerName;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOpinions(): ?string
    {
        return $this->opinions;
    }

    public function setOpinions(?string $opinions): self
    {
        $this->opinions = $opinions;

        return $this;
    }

    /**
     * @return Collection<int, Reviews>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Reviews $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setContent($this);
        }

        return $this;
    }

    public function removeReview(Reviews $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getContent() === $this) {
                $review->setContent(null);
            }
        }

        return $this;
    }
}
