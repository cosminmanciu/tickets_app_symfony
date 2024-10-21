<?php

namespace App\Controller;

use App\Entity\Organizer;
use App\Repository\OrganizerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OrganizerController extends AbstractController
{
    #[Route('/api/organizers', methods: ['GET'])]
    public function getAllOrganizers(OrganizerRepository $organizerRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $organizers = $organizerRepository->findAll();

        $data = $normalizer->normalize($organizers, null, [
            AbstractNormalizer::GROUPS => ['organizer:list'],
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {

                return $object->getId();
            }
        ]);

        return $this->json($data, 200);
    }

    #[Route('/api/organizers/{id}', methods: ['GET'])]
    public function getOrganizerById(OrganizerRepository $organizerRepository, int $id): JsonResponse
    {
        $organizer = $organizerRepository->find($id);
        if (!$organizer) {
            return $this->json(['error' => 'Organizer not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json($organizer);
    }

    #[Route('/api/organizers', methods: ['POST'])]
    public function createOrganizer(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $organizer = new Organizer();
        $organizer->setName($data['name']);

        $errors = $validator->validate($organizer);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($organizer);
        $entityManager->flush();

        return $this->json($organizer, JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/organizers/{id}', methods: ['PUT'])]
    public function updateOrganizer(int $id, Request $request, OrganizerRepository $organizerRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $organizer = $organizerRepository->find($id);
        if (!$organizer) {
            return $this->json(['error' => 'Organizer not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $organizer->setName($data['name'] ?? $organizer->getName());

        $errors = $validator->validate($organizer);
        if (count($errors) > 0) {
            return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return $this->json($organizer);
    }

    #[Route('/api/organizers/{id}', methods: ['DELETE'])]
    public function deleteOrganizer(int $id, OrganizerRepository $organizerRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $organizer = $organizerRepository->find($id);
        if (!$organizer) {
            return $this->json(['error' => 'Organizer not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($organizer);
        $entityManager->flush();

        return $this->json(['message' => 'Organizer deleted successfully']);
    }
}