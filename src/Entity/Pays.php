<?php

namespace App\Entity;

use App\Repository\PaysRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: PaysRepository::class)]
#[ORM\Table(name: '`DOR_PAYS`')]
class Pays
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $libelle = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 3, nullable: true)]
    private ?string $code = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 20, nullable: false)]
    private ?string $dateInsert = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $dateModif = null;

    /**
     * @var ArrayCollection|Collection
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: User::class)]
    private Collection|ArrayCollection $users;

    public function __construct()
    {
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
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * @param string|null $libelle
     * @return $this
     */
    public function setLibelle(?string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return $this
     */
    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of dateInsert
     */
    public function getDateInsert()
    {
        return $this->dateInsert;
    }

    /**
     * Set the value of dateInsert
     *
     * @return  self
     */
    public function setDateInsert($dateInsert)
    {
        $this->dateInsert = $dateInsert;

        return $this;
    }

    /**
     * Get the value of dateModif
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set the value of dateModif
     *
     * @return  self
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;

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
     * @return Pays
     */
    public function addUser(User $user): Pays
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCountry($this);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Pays
     */
    public function removeUser(User $user): Pays
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCountry() === $this) {
                $user->setCountry(null);
            }
        }

        return $this;
    }
}
