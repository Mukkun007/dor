<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: '`DOR_ROLE`')]
class Role
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
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 50)]
    private ?string $role = null;

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
     * @var ArrayCollection|Collection
     */
    #[ORM\OneToMany(mappedBy: 'role', targetEntity: User::class)]
    private Collection|ArrayCollection $users;

    public function __construct()
    {
        $this->createdAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->users = new ArrayCollection();
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
    public function setId(int $id): Role
    {
        $this->id = $id;
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
     * @param string $name
     * @return Role
     */
    public function setName(string $name): Role
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string $role
     * @return Role
     */
    public function setRole(string $role): Role
    {
        $this->role = $role;
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
     * @return Role
     */
    public function setIsDeleted(bool $isDeleted): Role
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
     * @param string $createdAt
     * @return Role
     */
    public function setCreatedAt(string $createdAt): Role
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
     * @return Role
     */
    public function setUpdatedAt(string $updatedAt): Role
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
     * @return Role
     */
    public function setDeletedAt(?string $deletedAt): Role
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return Role
     */
    public function addUser(User $user): Role
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setRole($this);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Role
     */
    public function removeUser(User $user): Role
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRole() === $this) {
                $user->setRole(null);
            }
        }

        return $this;
    }
}
