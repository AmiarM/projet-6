<?php

namespace App\Entity;

use App\Entity\Trick;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\Table(name="`images`")
 */
class Image
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Trick", inversedBy="images")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $trick;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isFirst;

    public function __construct()
    {
        $this->isFirst = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrick(): ?Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    public function isIsFirst(): ?bool
    {
        return $this->isFirst;
    }

    public function setIsFirst(bool $isFirst): self
    {
        $this->isFirst = $isFirst;

        return $this;
    }
}
