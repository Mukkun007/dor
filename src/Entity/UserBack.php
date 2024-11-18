<?php

namespace App\Entity;

use App\Repository\UserBackRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserBackRepository::class)]
#[ORM\Table(name: '`DOR_USER_BACK`')]
class UserBack implements UserInterface, PasswordAuthenticatedUserInterface
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
     * @var Group|null
     */
    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Group $group = null;

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
     * @return int|nullPays
     * @return $this
     */
    public function setId(int $id): UserBack
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
     * @return UserBack
     */
    public function setEmail(string $email): UserBack
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Group|null
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @param Group|null $group
     * @return UserBack
     */
    public function SetGroup(?Group $group): UserBack
    {
        $this->group = $group;
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
     * @return Role|null
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param Role|null $role
     * @return UserBack
     */
    public function setRole(?Role $role): UserBack
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
     * @return UserBack
     */
    public function setRoles(array $roles): UserBack
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
     * @return UserBack
     */
    public function setPassword(string $password): UserBack
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return UserBack
     * @see UserInterface
     */
    public function eraseCredentials(): UserBack
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
     * @return UserBack
     */
    public function setPlainPassword(?string $plainPassword): UserBack
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
     * @return UserBack
     */
    public function setName(?string $name): UserBack
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
     * @return UserBack
     */
    public function setFirstname(?string $firstname): UserBack
    {
        $this->firstname = $firstname;
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
     * @return UserBack
     */
    public function setIsDeleted(bool $isDeleted): UserBack
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
     * @return UserBack
     */
    public function setCreatedAt(?string $createdAt): UserBack
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
     * @return UserBack
     */
    public function setUpdatedAt(?string $updatedAt): UserBack
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
     * @return UserBack
     */
    public function setDeletedAt(?string $deletedAt): UserBack
    {
        $this->deletedAt = $deletedAt;
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
     * @param string $id
     * @return bool
     */
    public function hasMenu(string $id): ?bool
    {
        if ($this->getId() === 1) {
            return true;
        }

        if ($this->getGroup() && $this->getGroup()->getMenus()) {
            foreach ($this->getGroup()->getMenus() as $menu) {
                if ($menu->getId() === (int) $id) {
                    return true;
                }
            }
        }

        return false;
    }
}
