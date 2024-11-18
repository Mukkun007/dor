<?php

namespace App\Entity;

use App\Repository\RapproRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RapproRepository::class)]
#[ORM\Table(name: '`DOR_RAPPRO`')]
class Rappro
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
    #[ORM\Column(length: 12, unique: true)]
    private ?string $reference = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 12, nullable: true)]
    private ?string $numDossier = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $nom = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $prenom = null;

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
    private ?string $ibanSwift = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $statutChqVir = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: '4000', nullable: true)]
    private ?string $raisonRejet = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $montant = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $typeRappro = null;

    /**
     * @var integer|null
     */
    #[ORM\Column]
    private ?int $statutRappro = null;

    /**
     * @var string|null
     */
    #[ORM\Column]
    private ?string $dateRappro = null;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private ?string $dateTraitement = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @return string|null
     */
    public function getNumDossier(): ?string
    {
        return $this->numDossier;
    }

    /**
     * @return string|null
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @return string|null
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @return string|null
     */
    public function getRib(): ?string
    {
        return $this->rib;
    }

    /**
     * @return string|null
     */
    public function getAffiliation(): ?string
    {
        return $this->affiliation;
    }

    /**
     * @return string|null
     */
    public function getIbanSwift(): ?string
    {
        return $this->ibanSwift;
    }

    /**
     * @return string|null
     */
    public function getStatutChqVir(): ?string
    {
        return $this->statutChqVir;
    }

    /**
     * @return string|null
     */
    public function getRaisonRejet(): ?string
    {
        return $this->raisonRejet;
    }

    /**
     * @return string|null
     */
    public function getMontant(): ?string
    {
        return $this->montant;
    }

    /**
     * @return string|null
     */
    public function getTypeRappro(): ?string
    {
        return $this->typeRappro;
    }

    /**
     * @return int|null
     */
    public function getStatutRappro(): ?int
    {
        return $this->statutRappro;
    }

    /**
     * @return string|null
     */
    public function getDateRappro(): ?string
    {
        return $this->dateRappro;
    }

    /**
     * @return string|null
     */
    public function getDateTraitement(): ?string
    {
        return $this->dateTraitement;
    }

    /**
     * @param int|null $statutRappro
     */
    public function setStatutRappro(?int $statutRappro): void
    {
        $this->statutRappro = $statutRappro;
    }

    /**
     * @param string|null $dateTraitement
     */
    public function setDateTraitement(?string $dateTraitement): void
    {
        $this->dateTraitement = $dateTraitement;
    }

    /**
     * @param string|null $numDosiier
     */
    public function setNumDossier(?string $numDosiier): void
    {
        $this->numDossier = $numDosiier;
    }
}
