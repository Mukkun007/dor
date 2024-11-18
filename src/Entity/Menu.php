<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
#[ORM\Table(name: '`DOR_MENU`')]
class Menu
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Menu|null
     */
    #[ORM\ManyToOne(inversedBy: 'menus')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Menu $parent = null;

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
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Menu::class)]
    private Collection|ArrayCollection $menus;

    /**
     * @var ArrayCollection|Collection
     */
    #[ORM\ManyToMany(inversedBy: 'menus', targetEntity: Group::class)]
    private Collection|ArrayCollection $groups;

    public function __construct()
    {
        $this->dateInsert = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->groups = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Menu|null
     */
    public function getParent(): ?Menu
    {
        return $this->parent;
    }

    /**
     * @param Menu|null $role
     * @return Menu
     */
    public function setParent(?Menu $parent): Menu
    {
        $this->parent = $parent;
        return $this;
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
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    /**
     * @param Menu $menu
     * @return Menu
     */
    public function addMenu(Menu $menu): Menu
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setParent($this);
        }

        return $this;
    }

    /**
     * @param Menu $menu
     * @return Menu
     */
    public function removeMenu(Menu $menu): Menu
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getParent() === $this) {
                $menu->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param Group $group
     * @return Menu
     */
    public function addGroup(Group $group): Menu
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * @param Group $group
     * @return Menu
     */
    public function removeGroup(Group $group): Menu
    {
        $this->groups->removeElement($group);

        return $this;
    }
}
