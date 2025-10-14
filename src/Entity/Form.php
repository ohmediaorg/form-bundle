<?php

namespace OHMedia\FormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\FormBundle\Repository\FormRepository;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;

// use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormRepository::class)]
class Form
{
    use BlameableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $published_at = null;

    public function __toString(): string
    {
        return 'Form #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeImmutable $published_at): static
    {
        $this->published_at = $published_at;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published_at && DateTimeUtil::isPast($this->published_at);
    }

    public function isScheduled(): bool
    {
        return $this->published_at && DateTimeUtil::isFuture($this->published_at);
    }
}
