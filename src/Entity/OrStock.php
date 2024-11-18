<?php

namespace App\Entity;

use App\Repository\OrStockRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrStockRepository::class)]
#[ORM\Table(name: '`DOR_OR`')]
class OrStock
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $ref = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12)]
    private ?string $referencePreorder = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12)]
    private ?string $dateInsert = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12, nullable: true)]
    private ?string $dateUpdate = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12, nullable: true)]
    private ?string $dateVente = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $estVendu = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $isLast = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $isPreOrder = null;

    /**
     * @var Order|null
     */
    #[ORM\OneToOne(mappedBy: 'orStock', cascade: ['persist', 'remove'])]
    private ?Order $preOrder = null;

    public function __construct()
    {
        $this->isLast = 1;
        $this->estVendu = 0;
        $this->isPreOrder = 0;
        $this->dateInsert = (new DateTimeImmutable())->format('Y-m-d');
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRef(): ?string
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     * @return $this
     */
    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

      /**
     * @return string|null
     */
    public function getReferencePreorder(): ?string
    {
        return $this->referencePreorder;
    }

    /**
     * @param string $referencePreorder
     * @return $this
     */
    public function setReferencePreorder(string $referencePreorder): OrStock
    {
        $this->referencePreorder = $referencePreorder;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getDateInsert(): ?string
    {
        return $this->dateInsert;
    }

    /**
     * @param string $dateInsert
     * @return $this
     */
    public function setDateInsert(string $dateInsert): static
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateUpdate(): ?string
    {
        return $this->dateUpdate;
    }

    /**
     * @param string|null $dateUpdate
     * @return $this
     */
    public function setDateUpdate(?string $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateVente(): ?string
    {
        return $this->dateVente;
    }

    /**
     * @param string|null $dateVente
     * @return $this
     */
    public function setDateVente(?string $dateVente): static
    {
        $this->dateVente = $dateVente;

        return $this;
    }

    /**
     * @return int|null
     */
    public function isEstVendu(): ?int
    {
        return $this->estVendu;
    }

    /**
     * @param int|null $estVendu
     * @return $this
     */
    public function setEstVendu(?int $estVendu): static
    {
        $this->estVendu = $estVendu;

        return $this;
    }

    /**
     * @return int|null
     */
    public function isIsLast(): ?int
    {
        return $this->isLast;
    }

    /**
     * @param int|null $isLast
     * @return $this
     */
    public function setIsLast(?int $isLast): static
    {
        $this->isLast = $isLast;

        return $this;
    }

    /**
     * @return Order|null
     */
    public function getPreOrder(): ?Order
    {
        return $this->preOrder;
    }

    /**
     * @param int|null $preOrder
     * @return $this
     */
    public function setPreOrder(?int $preOrder): OrStock
    {
        $this->preOrder = $preOrder;

        return $this;
    }

    /**
     * @return int|null
     */
    public function isIsPreOrder(): ?int
    {
        return $this->isPreOrder;
    }

    /**
     * @param int|null $isPreOrder
     * @return $this
     */
    public function setIsPreOrder(?int $isPreOrder): static
    {
        $this->isPreOrder = $isPreOrder;

        return $this;
    }
}
