<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: '`DOR_SETTING`')]
class Setting
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
    #[ORM\Column]
    private ?string $name = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: '4000', nullable: true)]
    private ?string $value = null;

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
    public function setId(int $id): Setting
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
     * @param string|null $name
     * @return Setting
     */
    public function setName(?string $name): Setting
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return Setting
     */
    public function setValue(?string $value): Setting
    {
        $this->value = $value;
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
     * @return Setting
     */
    public function setCreatedAt(string $createdAt): Setting
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
     * @return Setting
     */
    public function setUpdatedAt(string $updatedAt): Setting
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
