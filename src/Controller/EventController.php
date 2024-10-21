<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Organizer;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EventController extends AbstractController
{
    #[Route('/api/events', methods: ['GET'])]
    public function getAllEvents(EventRepository $eventRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $events = $eventRepository->findAll();

        $data = $normalizer->normalize($events, null, [
            AbstractNormalizer::GROUPS => ['event:list'],
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            }
        ]);

        return $this->json($data, 200);
    }

    #[Route('/api/events/{id}', methods: ['GET'])]
    public function getEventById(EventRepository $eventRepository, int $id): JsonResponse
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($event, 200, [], [
            AbstractNormalizer::GROUPS => ['event:item']
        ]);
    }

    #[Route('/api/events', methods: ['POST'])]
    public function createEvent(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $event = new Event();
        $event->setName($data['name']);
        $event->setDate(new \DateTime($data['date']));
        // Assuming organizer is passed as ID, fetch it
        $organizer = $entityManager->getRepository(Organizer::class)->find($data['organizerId']);
        if (!$organizer) {
            return $this->json(['error' => 'Organizer not found'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $event->setOrganizer($organizer);

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->json($event, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/events/{id}', methods: ['PUT'])]
    public function updateEvent(int $id, Request $request, EventRepository $eventRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $event->setName($data['name'] ?? $event->getName());
        $event->setDate(new \DateTime($data['date'] ?? $event->getDate()->format('Y-m-d H:i:s')));

        $errors = $validator->validate($event);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($event);
    }

    #[Route('/api/events/{id}', methods: ['DELETE'])]
    public function deleteEvent(int $id, EventRepository $eventRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return $this->json(['error' => 'Event not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        return $this->json(['message' => 'Event deleted successfully']);
    }
}
