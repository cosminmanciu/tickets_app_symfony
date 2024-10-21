<?php


namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['ticket:list', 'ticket:item', 'event:item'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Ticket type should not be blank.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Ticket type cannot be longer than 255 characters."
    )]
    #[Groups(['ticket:list', 'ticket:item', 'event:item'])]
    private ?string $type = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank(message: "Price should not be blank.")]
    #[Assert\Positive(message: "Price must be a positive number.")]
    #[Groups(['ticket:list', 'ticket:item', 'event:item'])]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['ticket:item'])]
    private ?Event $event = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }
}
