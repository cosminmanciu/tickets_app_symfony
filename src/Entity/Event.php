<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['event:list', 'event:item'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "Event name should not be blank.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Event name cannot be longer than 255 characters."
    )]
    #[Groups(['event:list', 'event:item'])]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank(message: "Event date should not be blank.")]
    #[Assert\Type("\DateTimeInterface", message: "Invalid date format.")]
    #[Groups(['event:list', 'event:item'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Organizer::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Organizer must be assigned to the event.")]
    #[Groups(['event:item'])]
    private ?Organizer $organizer = null;

    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'event', cascade: ['persist', 'remove'])]
    #[Groups(['event:item'])]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getOrganizer(): ?Organizer
    {
        return $this->organizer;
    }

    public function setOrganizer(?Organizer $organizer): self
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                $ticket->setEvent(null);
            }
        }

        return $this;
    }
}
