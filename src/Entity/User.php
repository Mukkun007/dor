<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Utilities\Util;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`DOR_USER`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
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
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12, unique: true)]
    private ?string $reference = null;

    /**
     * @var Role|null
     */
    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Role $role = null;

    /**
     * @var array
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $plainPassword = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $firstname = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $civility = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $marital_status = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $passport = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $passportExp = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $rib = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $affiliation = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $iban = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $swift = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $birthday = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $address = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $city = null;

    /**
     * @var Pays|null
     */
    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Pays $country = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $phone = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $account = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $orQuantity = null;

    /**
     * @var int|null
     */
    #[ORM\Column(nullable: true)]
    private ?int $paymentMethod = null;


    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $partner_cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $partner_name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $partner_firstname = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $father_cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $father_name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $father_firstname = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $mother_cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $mother_name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $mother_firstname = null;

    /**
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $passwordChanged = false;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_passport = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_partner_cin = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_rib = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_affiliation = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_iban = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_family_record_book = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $file_residence = null;

    /**
     * @var bool|null
     */
    #[ORM\Column]
    private ?bool $isDeleted = false;

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
     * @var Order|null
     */
    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Order $preorder = null;

    /**
     * @var int|null
     */
    private ?int $age = 0;

    public function __construct()
    {
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
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
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
    public function setReference(string $reference): User
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     * @return User
     */
    public function setRole(?Role $role): User
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the value of passwordChanged
     *
     * @return bool|null
     */
    public function getPasswordChanged(): ?bool
    {
        return $this->passwordChanged;
    }

    /**
     * Set the value of passwordChanged
     *
     * @param bool|null $passwordChanged
     * @return self
     */
    public function setPasswordChanged(?bool $passwordChanged): self
    {
        $this->passwordChanged = $passwordChanged;

        return $this;
    }

    /**
     * @return User
     * @see UserInterface
     */
    public function eraseCredentials(): User
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
     * @return User
     */
    public function setPlainPassword(?string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return User
     */
    public function setName(?string $name): User
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param string|null $firstname
     * @return User
     */
    public function setFirstname(?string $firstname): User
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCivility(): ?int
    {
        return $this->civility;
    }

    /**
     * @param int|null $civility
     * @return User
     */
    public function setCivility(?int $civility): User
    {
        $this->civility = $civility;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaritalStatus(): ?int
    {
        return $this->marital_status;
    }

    /**
     * @param int|null $marital_status
     * @return User
     */
    public function setMaritalStatus(?int $marital_status): User
    {
        $this->marital_status = $marital_status;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCin(): ?string
    {
        return $this->cin;
    }

    /**
     * @param string|null $cin
     * @return User
     */
    public function setCin(?string $cin): User
    {
        $this->cin = $cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassport(): ?string
    {
        return $this->passport;
    }

    /**
     * @param string|null $passport
     * @return $this
     */
    public function setPassport(?string $passport): User
    {
        $this->passport = $passport;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportExp(): ?string
    {
        return $this->passportExp;
    }

    /**
     * @param string|null $passportExp
     * @return $this
     */
    public function setPassportExp(?string $passportExp): User
    {
        $this->passportExp = $passportExp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRib(): ?string
    {
        return $this->rib;
    }

    /**
     * @param string|null $rib
     * @return User
     */
    public function setRib(?string $rib): User
    {
        $this->rib = $rib;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    /**
     * @param string|null $affiliation
     * @return $this
     */
    public function setAffiliation(?string $affiliation): User
    {
        $this->affiliation = $affiliation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     * @return $this
     */
    public function setIban(?string $iban): User
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSwift(): ?string
    {
        return $this->swift;
    }

    /**
     * @param string|null $swift
     * @return $this
     */
    public function setSwift(?string $swift): User
    {
        $this->swift = $swift;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    /**
     * @param string|null $birthday
     * @return User
     */
    public function setBirthday(?string $birthday): User
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return User
     */
    public function setAddress(?string $address): User
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return User
     */
    public function setCity(?string $city): User
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return Pays|null
     */
    public function getCountry(): ?Pays
    {
        return $this->country;
    }

    /**
     * @param Pays|null $country
     * @return User
     */
    public function SetCountry(?Pays $country): User
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return User
     */
    public function setPhone(?string $phone): User
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getAccount(): ?int
    {
        return $this->account;
    }

    /**
     * @param int|null $account
     * @return $this
     */
    public function setAccount(?int $account): User
    {
        $this->account = $account;
        return $this;
    }


    /**
     * @return int|null
     */
    public function getOrQuantity(): ?int
    {
        return $this->orQuantity;
    }

    /**
     * @param int|null $orQuantity
     * @return $this
     */
    public function setOrQuantity(?int $orQuantity): User
    {
        $this->orQuantity = $orQuantity;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaymentMethod(): ?int
    {
        return $this->paymentMethod;
    }

    /**
     * @param int|null $paymentMethod
     * @return $this
     */
    public function setPaymentMethod(?int $paymentMethod): User
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerCin(): ?string
    {
        return $this->partner_cin;
    }

    /**
     * @param string|null $partner_cin
     * @return User
     */
    public function setPartnerCin(?string $partner_cin): User
    {
        $this->partner_cin = $partner_cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerName(): ?string
    {
        return $this->partner_name;
    }

    /**
     * @param string|null $partner_name
     * @return User
     */
    public function setPartnerName(?string $partner_name): User
    {
        $this->partner_name = $partner_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPartnerFirstname(): ?string
    {
        return $this->partner_firstname;
    }

    /**
     * @param string|null $partner_firstname
     * @return User
     */
    public function setPartnerFirstname(?string $partner_firstname): User
    {
        $this->partner_firstname = $partner_firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFatherCin(): ?string
    {
        return $this->father_cin;
    }

    /**
     * @param string|null $father_cin
     * @return User
     */
    public function setFatherCin(?string $father_cin): User
    {
        $this->father_cin = $father_cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFatherName(): ?string
    {
        return $this->father_name;
    }

    /**
     * @param string|null $father_name
     * @return User
     */
    public function setFatherName(?string $father_name): User
    {
        $this->father_name = $father_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFatherFirstname(): ?string
    {
        return $this->father_firstname;
    }

    /**
     * @param string|null $father_firstname
     * @return User
     */
    public function setFatherFirstname(?string $father_firstname): User
    {
        $this->father_firstname = $father_firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMotherCin(): ?string
    {
        return $this->mother_cin;
    }

    /**
     * @param string|null $mother_cin
     * @return User
     */
    public function setMotherCin(?string $mother_cin): User
    {
        $this->mother_cin = $mother_cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMotherName(): ?string
    {
        return $this->mother_name;
    }

    /**
     * @param string|null $mother_name
     * @return User
     */
    public function setMotherName(?string $mother_name): User
    {
        $this->mother_name = $mother_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMotherFirstname(): ?string
    {
        return $this->mother_firstname;
    }

    /**
     * @param string|null $mother_firstname
     * @return User
     */
    public function setMotherFirstname(?string $mother_firstname): User
    {
        $this->mother_firstname = $mother_firstname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileCin(): ?string
    {
        return $this->file_cin;
    }

    /**
     * @param string|null $file_cin
     * @return User
     */
    public function setFileCin(?string $file_cin): User
    {
        $this->file_cin = $file_cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePassport(): ?string
    {
        return $this->file_passport;
    }

    /**
     * @param string|null $file_passport
     * @return $this
     */
    public function setFilePassport(?string $file_passport): User
    {
        $this->file_passport = $file_passport;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePartnerCin(): ?string
    {
        return $this->file_partner_cin;
    }

    /**
     * @param string|null $file_partner_cin
     * @return User
     */
    public function setFilePartnerCin(?string $file_partner_cin): User
    {
        $this->file_partner_cin = $file_partner_cin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileRib(): ?string
    {
        return $this->file_rib;
    }

    /**
     * @param string|null $file_rib
     * @return User
     */
    public function setFileRib(?string $file_rib): User
    {
        $this->file_rib = $file_rib;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileAffiliation(): ?string
    {
        return $this->file_affiliation;
    }

    /**
     * @param string|null $file_affiliation
     * @return User
     */
    public function setFileAffiliation(?string $file_affiliation): User
    {
        $this->file_affiliation = $file_affiliation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileIban(): ?string
    {
        return $this->file_iban;
    }

    /**
     * @param string|null $file_iban
     * @return User
     */
    public function setFileIban(?string $file_iban): User
    {
        $this->file_iban = $file_iban;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileFamilyRecordBook(): ?string
    {
        return $this->file_family_record_book;
    }

    /**
     * @param string|null $file_family_record_book
     * @return User
     */
    public function setFileFamilyRecordBook(?string $file_family_record_book): User
    {
        $this->file_family_record_book = $file_family_record_book;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileResidence(): ?string
    {
        return $this->file_residence;
    }

    /**
     * @param string|null $file_residence
     * @return User
     */
    public function setFileResidence(?string $file_residence): User
    {
        $this->file_residence = $file_residence;
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
     * @return User
     */
    public function setIsDeleted(bool $isDeleted): User
    {
        $this->isDeleted = $isDeleted;
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
     * @param string|null $createdAt
     * @return User
     */
    public function setCreatedAt(?string $createdAt): User
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
     * @param string|null $updatedAt
     * @return User
     */
    public function setUpdatedAt(?string $updatedAt): User
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
     * @return User
     */
    public function setDeletedAt(?string $deletedAt): User
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return Order|null
     */
    public function getPreorder(): ?Order
    {
        return $this->preorder;
    }

    /**
     * @param Order $preorder
     * @return User
     */
    public function setPreorder(Order $preorder): User
    {
        // set the owning side of the relation if necessary
        if ($preorder->getUser() !== $this) {
            $preorder->setUser($this);
        }

        $this->preorder = $preorder;

        return $this;
    }

    /**
     * @return true
     */
    public function isAccountNonExpired(): true
    {
        return true;
    }

    /**
     * @return true
     */
    public function isAccountNonLocked(): true
    {
        return true;
    }

    /**
     * @return true
     */
    public function isCredentialsNonExpired(): true
    {
        return true;
    }

    /**
     * @return bool|null
     */
    public function isEnabled(): ?bool
    {
        return !$this->isDeleted;
    }

    /**
     *
     */
    public function getAge(): ?int
    {
        $this->age = Util::getAgeCurrentAndBirth($this->birthday);
        return $this->age;
    }
}
