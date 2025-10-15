<?php

namespace OHMedia\FormBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FormBundle\Repository\FormFieldRepository;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use OHMedia\UtilityBundle\Form\PhoneType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField
{
    use BlameableEntityTrait;

    public const TYPE_CHOICES = [
        'Text' => TextType::class,
        'Number' => NumberType::class,
        'Phone Number' => PhoneType::class,
        'Email' => EmailType::class,
        'Date' => DateType::class,
        'Textarea' => TextareaType::class,
        'Choice' => ChoiceType::class,
    ];

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

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $help = null;

    #[ORM\Column(nullable: true)]
    private ?array $options = null;

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

    public function getTypeReadable(): string
    {
        $types = array_flip(self::TYPE_CHOICES);

        return $types[$this->type] ?? $this->type;
    }

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    public function isTypeText(): bool
    {
        return $this->isType(TextType::class);
    }

    public function isTypeNumber(): bool
    {
        return $this->isType(NumberType::class);
    }

    public function isTypePhone(): bool
    {
        return $this->isType(PhoneType::class);
    }

    public function isTypeEmail(): bool
    {
        return $this->isType(EmailType::class);
    }

    public function isTypeDate(): bool
    {
        return $this->isType(DateType::class);
    }

    public function isTypeTextarea(): bool
    {
        return $this->isType(TextareaType::class);
    }

    public function isTypeChoice(): bool
    {
        return $this->isType(ChoiceType::class);
    }

    public function getTypeChoice(): string
    {
        return self::TYPE_CHOICES['Choice'];
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

    // TODO: rename this to $data
    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

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
