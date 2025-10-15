<?php

namespace OHMedia\FormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FormBundle\Repository\FormFieldRepository;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField
{
    use BlameableEntityTrait;

    public const TYPE_CHOICE = 'choice';
    public const TYPE_DATE = 'date';
    public const TYPE_EMAIL = 'email';
    public const TYPE_NUMBER = 'number';
    public const TYPE_PHONE = 'phone';
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $ordinal = 9999;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    private ?string $label = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $help = null;

    #[ORM\Column(nullable: true)]
    private ?array $data = null;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Form $form = null;

    #[ORM\Column]
    private ?bool $required = true;

    public function __toString(): string
    {
        return (string) $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdinal(): ?int
    {
        return $this->ordinal;
    }

    public function setOrdinal(int $ordinal): self
    {
        $this->ordinal = $ordinal;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getName(): string
    {
        $slugger = new AsciiSlugger();

        return $slugger->slug(strtolower($this->label).' '.$this->id, '_');
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public static function getTypeChoices(): array
    {
        return [
            'Text' => self::TYPE_TEXT,
            'Email' => self::TYPE_EMAIL,
            'Phone Number' => self::TYPE_PHONE,
            'Textarea' => self::TYPE_TEXTAREA,
            'Choice' => self::TYPE_CHOICE,
            'Date' => self::TYPE_DATE,
            'Number' => self::TYPE_NUMBER,
        ];
    }

    public function getTypeReadable(): string
    {
        $types = array_flip(self::getTypeChoices());

        return $types[$this->type] ?? $this->type;
    }

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    public function isTypeText(): bool
    {
        return $this->isType(self::TYPE_TEXT);
    }

    public function isTypeNumber(): bool
    {
        return $this->isType(self::TYPE_NUMBER);
    }

    public function isTypePhone(): bool
    {
        return $this->isType(self::TYPE_PHONE);
    }

    public function isTypeEmail(): bool
    {
        return $this->isType(self::TYPE_EMAIL);
    }

    public function isTypeDate(): bool
    {
        return $this->isType(self::TYPE_DATE);
    }

    public function isTypeTextarea(): bool
    {
        return $this->isType(self::TYPE_TEXTAREA);
    }

    public function isTypeChoice(): bool
    {
        return $this->isType(self::TYPE_CHOICE);
    }

    public function getHelp(): ?string
    {
        return $this->help;
    }

    public function setHelp(?string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }
}
