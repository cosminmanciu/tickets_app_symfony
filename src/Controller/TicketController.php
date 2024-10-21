<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TicketController extends AbstractController
{
    #[Route('/api/tickets', methods: ['GET'])]
    public function getAllTickets(TicketRepository $ticketRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $tickets = $ticketRepository->findAll();
        $data = $normalizer->normalize($tickets, null, [
            AbstractNormalizer::GROUPS => ['ticket:list'],
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {

                return $object->getId();
            }
        ]);

        return $this->json($data, 200);
    }

    #[Route('/api/tickets/{id}', methods: ['GET'])]
    public function getTicketById(TicketRepository $ticketRepository, int $id): JsonResponse
    {
        $ticket = $ticketRepository->find($id);
        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($ticket);
    }

    #[Route('/api/tickets', methods: ['POST'])]
    public function createTicket(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $ticket = new Ticket();
        $ticket->setType($data['type']);
        $ticket->setPrice($data['price']);
        $event = $entityManager->getRepository('App:Event')->find($data['event_id']);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $ticket->setEvent($event);

        $errors = $validator->validate($ticket);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($ticket);
        $entityManager->flush();

        return $this->json($ticket, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/tickets/{id}', methods: ['PUT'])]
    public function updateTicket(int $id, Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $ticket = $ticketRepository->find($id);
        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $ticket->setType($data['type'] ?? $ticket->getType());
        $ticket->setPrice($data['price'] ?? $ticket->getPrice());

        $errors = $validator->validate($ticket);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($ticket);
    }

    #[Route('/api/tickets/{id}', methods: ['DELETE'])]
    public function deleteTicket(int $id, TicketRepository $ticketRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $ticket = $ticketRepository->find($id);
        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($ticket);
        $entityManager->flush();

        return $this->json(['message' => 'Ticket deleted successfully']);
    }
}
