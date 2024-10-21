<?php

namespace App\Tests;

use App\Controller\TicketController;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TicketControllerTest extends WebTestCase
{
    private $ticketController;
    private $entityManager;
    private $ticketRepository;
    private $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->ticketRepository = $this->createMock(TicketRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->ticketController = new TicketController();
    }

    public function testGetAllTickets()
    {
        $tickets = [new Ticket(), new Ticket()];
        $this->ticketRepository->method('findAll')->willReturn($tickets);

        $response = $this->ticketController->getAllTickets($this->ticketRepository);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($tickets), $response->getContent());
    }

    public function testGetTicketByIdSuccess()
    {
        $ticket = new Ticket();
        $ticket->setType('VIP');
        $ticket->setPrice(100);

        $this->ticketRepository->method('find')->willReturn($ticket);

        $response = $this->ticketController->getTicketById($this->ticketRepository, 1);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($ticket), $response->getContent());
    }

    public function testGetTicketByIdNotFound()
    {
        $this->ticketRepository->method('find')->willReturn(null);

        $response = $this->ticketController->getTicketById($this->ticketRepository, 999);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Ticket not found']), $response->getContent());
    }

    public function testCreateTicketSuccess()
    {
        $request = new Request([], [], [], [], [], [], json_encode(['type' => 'Regular', 'price' => 50, 'event_id' => 1]));
        $event = new \App\Entity\Event(); // Assuming Event is a valid entity
        $this->entityManager->method('getRepository')->willReturn($this->createMock(\App\Repository\EventRepository::class));
        $this->entityManager->method('getRepository')->willReturn($this->createMock(\App\Repository\EventRepository::class));
        $this->entityManager->method('persist')->willReturn(null);
        $this->entityManager->method('flush')->willReturn(null);

        $this->validator->method('validate')->willReturn([]);

        $this->entityManager->getRepository(\App\Entity\Event::class)->method('find')->willReturn($event);

        $response = $this->ticketController->createTicket($request, $this->entityManager, $this->validator);
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCreateTicketEventNotFound()
    {
        $request = new Request([], [], [], [], [], [], json_encode(['type' => 'Regular', 'price' => 50, 'event_id' => 999]));
        $this->entityManager->getRepository(\App\Entity\Event::class)->method('find')->willReturn(null);

        $response = $this->ticketController->createTicket($request, $this->entityManager, $this->validator);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Event not found']), $response->getContent());
    }

    public function testUpdateTicketSuccess()
    {
        $ticket = new Ticket();
        $ticket->setType('Regular');
        $ticket->setPrice(50);

        $request = new Request([], [], [], [], [], [], json_encode(['type' => 'VIP', 'price' => 100]));
        $this->ticketRepository->method('find')->willReturn($ticket);

        $this->validator->method('validate')->willReturn([]);

        $response = $this->ticketController->updateTicket(1, $request, $this->ticketRepository, $this->entityManager, $this->validator);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($ticket), $response->getContent());
    }

    public function testUpdateTicketNotFound()
    {
        $this->ticketRepository->method('find')->willReturn(null);

        $request = new Request([], [], [], [], [], [], json_encode(['type' => 'VIP', 'price' => 100]));
        $response = $this->ticketController->updateTicket(999, $request, $this->ticketRepository, $this->entityManager, $this->validator);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Ticket not found']), $response->getContent());
    }

    public function testDeleteTicketSuccess()
    {
        $ticket = new Ticket();
        $this->ticketRepository->method('find')->willReturn($ticket);

        $response = $this->ticketController->deleteTicket(1, $this->ticketRepository, $this->entityManager);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['message' => 'Ticket deleted successfully']), $response->getContent());
    }

    public function testDeleteTicketNotFound()
    {
        $this->ticketRepository->method('find')->willReturn(null);

        $response = $this->ticketController->deleteTicket(999, $this->ticketRepository, $this->entityManager);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode(['error' => 'Ticket not found']), $response->getContent());
    }
}
