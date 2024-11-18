<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`DOR_PREORDER`')]
class Order
{
    /**
     * @var int|null
     */
    // #[ORM\Id]
    // #[ORM\Column]
    // private ?int $id = null;
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    /**
     * @var string|null
     */
    // #[ORM\Column(length: 8)]
    // private ?int $cheque_number = null;
    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $cheque_number;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ov;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_ov = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12)]
    private ?string $reference = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $choiceLivraison = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $choiceMeeting = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $userRt = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $nameTierce = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $firstnameTierce = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $cinTierce = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $tierceCivility = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $tierceBirthday = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $tierceAddress = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $tierceDuplicata = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $tiercePhone = null;

    /**
     * @var integer|null
     */
    #[ORM\Column]
    private ?int $flagStatus = null;

    /**
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $isDeleted = false;

    /**
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $isPpex = false;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $createdAt = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $updatedAt = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $deletedAt = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $dateCalendar = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $typeCalendar = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $otp = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $dateRdv = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $datePaiement = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $dateLivraison = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $dateOv = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_accuse = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_prelevement = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_depositContract = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_depositAttestation = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comments = null;

    /**
     * @var User|null
     */
    #[ORM\OneToOne(inversedBy: 'preorder', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var OrStock|null
     */
    #[ORM\OneToOne(inversedBy: 'preOrder', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?OrStock $orStock = null;

    public function __construct()
    {
        $this->isDeleted = false;
        $this->isPpex = false;
        $this->createdAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
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
    public function setId(int $id): Order
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getCheque_number(): ?string
    {
        return $this->cheque_number;
    }

    /**
     * @param string $cheque_number
     * @return $this
     */
    public function setCheque_number(string $cheque_number): Order
    {
        $this->cheque_number = $cheque_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOv(): ?string
    {
        return $this->ov;
    }

    /**
     * @param string $ov
     * @return $this
     */
    public function setOv(string $ov): Order
    {
        $this->ov = $ov;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileOv(): ?string
    {
        return $this->file_ov;
    }

    /**
     * @param string|null $file_ov
     * @return $this
     */
    public function setFileOv(?string $file_ov): Order
    {
        $this->file_ov = $file_ov;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     * @return $this
     */
    public function setReference(string $reference): Order
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getChoiceLivraison(): ?int
    {
        return $this->choiceLivraison;
    }

    /**
     * @param int|null $choiceLivraison
     * @return $this
     */
    public function setChoiceLivraison(?int $choiceLivraison): Order
    {
        $this->choiceLivraison = $choiceLivraison;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getChoiceMeeting(): ?int
    {
        return $this->choiceMeeting;
    }

    /**
     * @param int|null $choiceMeeting
     * @return $this
     */
    public function setChoiceMeeting(?int $choiceMeeting): Order
    {
        $this->choiceMeeting = $choiceMeeting;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUserRt(): ?int
    {
        return $this->userRt;
    }

    /**
     * @param int|null $userRt
     * @return $this
     */
    public function setUserRt(?int $userRt): Order
    {
        $this->userRt = $userRt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameTierce(): ?string
    {
        return $this->nameTierce;
    }

    /**
     * @param string|null $nameTierce
     * @return Order
     */
    public function setNameTierce(?string $nameTierce): Order
    {
        $this->nameTierce = $nameTierce;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstnameTierce(): ?string
    {
        return $this->firstnameTierce;
    }

    /**
     * @param string|null $firstnameTierce
     * @return Order
     */
    public function setFirstnameTierce(?string $firstnameTierce): Order
    {
        $this->firstnameTierce = $firstnameTierce;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCinTierce(): ?string
    {
        return $this->cinTierce;
    }

    /**
     * @param string|null $cinTierce
     * @return Order
     */
    public function setCinTierce(?string $cinTierce): Order
    {
        $this->cinTierce = $cinTierce;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTierceCivility(): ?string
    {
        return $this->tierceCivility;
    }

    /**
     * @param string|null $tierceCivility
     * @return Order
     */
    public function setTierceCivility(?string $tierceCivility): Order
    {
        $this->tierceCivility = $tierceCivility;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTierceBirthday(): ?string
    {
        return $this->tierceBirthday;
    }

    /**
     * @param string|null $tierceBirthday
     * @return Order
     */
    public function setTierceBirthday(?string $tierceBirthday): Order
    {
        $this->tierceBirthday = $tierceBirthday;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTiercePhone(): ?string
    {
        return $this->tiercePhone;
    }

    /**
     * @param string|null $tiercePhone
     * @return Order
     */
    public function setTiercePhone(?string $tiercePhone): Order
    {
        $this->tiercePhone = $tiercePhone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTierceDuplicata(): ?string
    {
        return $this->tierceDuplicata;
    }

    /**
     * @param string|null $tierceDuplicata
     * @return Order
     */
    public function setTierceDuplicata(?string $tierceDuplicata): Order
    {
        $this->tierceDuplicata = $tierceDuplicata;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTierceAddress(): ?string
    {
        return $this->tierceAddress;
    }

    /**
     * @param string|null $tierceDuplicata
     * @return Order
     */
    public function setTierceAddress(?string $tierceAddress): Order
    {
        $this->tierceAddress = $tierceAddress;
        return $this;
    }


    /**
     * @return integer|null
     */
    public function getFlagStatus(): ?int
    {
        return $this->flagStatus;
    }

    /**
     * @param integer $flagStatus
     * @return Order
     */
    public function setFlagStatus(int $flagStatus): Order
    {
        $this->flagStatus = $flagStatus;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     * @return Order
     */
    public function setIsDeleted(bool $isDeleted): Order
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPpex(): bool
    {
        return $this->isPpex;
    }

    /**
     * @param bool $isPpex
     * @return Order
     */
    public function setIsPpex(bool $isPpex): Order
    {
        $this->isPpex = $isPpex;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return Order
     */
    public function setCreatedAt(string $createdAt): Order
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     * @return Order
     */
    public function setUpdatedAt(string $updatedAt): Order
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }

    /**
     * @param string|null $deletedAt
     * @return Order
     */
    public function setDeletedAt(?string $deletedAt): Order
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Order
     */
    public function setUser(User $user): Order
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return OrStock|null
     */
    public function getOrStock(): ?OrStock
    {
        return $this->orStock;
    }

    /**
     * @param OrStock|null $orStock
     * @return Order
     */
    public function setOrStock(OrStock|null $orStock): Order
    {
        $this->orStock = $orStock;
        return $this;
    }

    // Les autres getters et setters sont inchangés...

    /**
     * @return string|null
     */
    public function getDateCalendar(): ?string
    {
        return $this->dateCalendar;
    }

    /**
     * @param string|null $dateCalendar
     * @return $this
     */
    public function setDateCalendar(?string $dateCalendar): Order
    {
        $this->dateCalendar = $dateCalendar;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTypeCalendar(): ?string
    {
        return $this->typeCalendar;
    }

    /**
     * @param string|null $typeCalendar
     * @return $this
     */
    public function setTypeCalendar(?string $typeCalendar): Order
    {
        $this->typeCalendar = $typeCalendar;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOtp(): ?string
    {
        return $this->otp;
    }

    /**
     * @param string|null $otp
     * @return $this
     */
    public function setOtp(?string $otp): Order
    {
        $this->otp = $otp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateRdv(): ?string
    {
        return $this->dateRdv;
    }

    /**
     * @param string|null $dateRdv
     * @return $this
     */
    public function setDateRdv(?string $dateRdv): Order
    {
        $this->dateRdv = $dateRdv;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDatePaiement(): ?string
    {
        return $this->datePaiement;
    }

    /**
     * @param string|null $datePaiement
     * @return $this
     */
    public function setDatePaiement(?string $datePaiement): Order
    {
        $this->datePaiement = $datePaiement;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateLivraison(): ?string
    {
        return $this->dateLivraison;
    }

    /**
     * @param string|null $dateLivraison
     * @return $this
     */
    public function setDateLivraison(?string $dateLivraison): Order
    {
        $this->dateLivraison = $dateLivraison;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateOv(): ?string
    {
        return $this->dateOv;
    }

    /**
     * @param string|null $dateOv
     * @return $this
     */
    public function setDateOv(?string $dateOv): Order
    {
        $this->dateOv = $dateOv;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileAccuse(): ?string
    {
        return $this->file_accuse;
    }

    /**
     * @param string|null $file_accuse
     * @return Order
     */
    public function setFileAccuse(?string $file_accuse): Order
    {
        $this->file_accuse = $file_accuse;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileDepositContract(): ?string
    {
        return $this->file_depositContract;
    }

    /**
     * @param string|null $file_depositContract
     * @return Order
     */
    public function setFileDepositContract(?string $file_depositContract): Order
    {
        $this->file_depositContract = $file_depositContract;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileDepositAttestation(): ?string
    {
        return $this->file_depositAttestation;
    }

    /**
     * @param string|null $file_depositAttestation
     * @return Order
     */
    public function setFileDepositAttestation(?string $file_depositAttestation): Order
    {
        $this->file_depositAttestation = $file_depositAttestation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePrelevement(): ?string
    {
        return $this->file_prelevement;
    }

    /**
     * @param string|null $file_prelevement
     * @return Order
     */
    public function setFilePrelevement(?string $file_prelevement): Order
    {
        $this->file_prelevement = $file_prelevement;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     * @return Order
     */
    public function setComments(?string $comments): Order
    {
        $this->comments = $comments;
        return $this;
    }
}
