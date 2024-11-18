<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`DOR_GROUP`')]
class Group
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
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

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
    #[ORM\OneToMany(mappedBy: 'group', targetEntity: UserBack::class)]
    private Collection|ArrayCollection $users;

    /**
     * @var ArrayCollection|Collection
     */
    #[ORM\ManyToMany(mappedBy: 'groups', targetEntity: Menu::class)]
    private Collection|ArrayCollection $menus;

    public function __construct()
    {
        $this->dateInsert = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->users = new ArrayCollection();
        $this->menus = new ArrayCollection();
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
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $code
     * @return $this
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
     * @return Collection<int, UserBack>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param UserBack $user
     * @return Group
     */
    public function addUser(UserBack $user): Group
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setGroup($this);
        }

        return $this;
    }

    /**
     * @param UserBack $user
     * @return Group
     */
    public function removeUser(UserBack $user): Group
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getGroup() === $this) {
                $user->setGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    /**
     * @param Menu $menu
     * @return Group
     */
    public function addMenu(Menu $menu): Group
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
        }

        return $this;
    }

    /**
     * @param Menu $user
     * @return Group
     */
    public function removeMenu(Menu $menu): Group
    {
        $this->menus->removeElement($menu);

        return $this;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasMenu(string $id): ?bool
    {
        if ($this->getMenus()) {
            foreach ($this->getMenus() as $menu) {
                if ($menu->getId() === (int) $id) {
                    return true;
                }
            }
        }

        return false;
    }
}
