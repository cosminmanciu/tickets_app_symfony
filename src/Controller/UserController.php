<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }


    #[Route('/api/users/register', name: 'api_register', methods: ['POST'])]
    public function register(
            Request $request,
            UserPasswordHasherInterface $passwordHasher,
            EntityManagerInterface $entityManager,
            ValidatorInterface $validator
        ): JsonResponse {

            $data = json_decode($request->getContent(), true);

            $email = $data['username'] ?? null;
            $password = $data['password'] ?? null;

            if (!$email || !$password) {
                return new JsonResponse(['error' => 'Email and password are required.'], 400);
            }

            if ($entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
                return new JsonResponse(['error' => 'User already exists.'], 400);
            }

            $user = new User();
            $user->setEmail($email);

            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashedPassword);

            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                return new JsonResponse(['error' => (string) $errors], 400);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse(['success' => 'User registered successfully.'], 201);
    }

    #[Route('/api/users/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not logged in.'], 401);
        }

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail()
        ], 200);
    }


}
