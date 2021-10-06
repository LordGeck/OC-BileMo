<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserManager $userManager,
        private SerializerInterface $serializer,
    ) {}

    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findByClient($this->getUser());

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($users, 'json')
        );
    }
    
    #[Route('/api/users/{slug}', name: 'user_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $user = $this->userRepository->findBySlug($slug);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($user, 'json')
        );
    }

    #[Route('/api/users', name: 'user_new', methods: ['POST'])]
    public function new(Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($userData);
        if ($form->isValid()) {
            if ($this->userRepository->findByUsername($user->getUsername())) {
                return new JsonResponse(['message' => 'Unvalid data'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            $this->userManager->create($user, $this->getUser());

            return new JsonResponse(['message' => 'Succes'], JsonResponse::HTTP_CREATED);
        }        
        
        return new JsonResponse(['message' => 'Unvalid data'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    #[Route('/api/users/{slug}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(string $slug): JsonResponse
    {
        $user = $this->userRepository->findOneBySlug($slug);
        if (!$user) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }
        $this->userManager->delete($user);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
