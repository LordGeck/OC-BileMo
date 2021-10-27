<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\UserType;
use App\Service\UserManager;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
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

    /**
     * @Route("/api/users", name="user_list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Return a list of users",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Item by page",
     *     @OA\Schema(type="string")
     * )
     * @OA\Tag(name="User")
     */
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $userCount = $this->userRepository->count(['client' => $this->getUser()]);
        $users = $this->userRepository->findByClient(
            $this->getUser(),
            [],
            $limit,
            $limit*$page-$limit,
        );
        $paginatedUsers = new PaginatedRepresentation(
            new CollectionRepresentation($users),
            'user_list',
            [],
            $page,
            $limit,
            ceil($userCount/$limit),
        );

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($paginatedUsers, 'json')
        );
    }

    /**
     * @Route("//api/users/{slug}", name="user_show", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Return a user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=User::class))
     *     )
     * )
     * @OA\Tag(name="User")
     */
    public function show(string $slug): JsonResponse
    {
        $user = $this->userRepository->findBySlug($slug);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($user, 'json')
        );
    }

    /**
     * @Route("/api/users", name="user_create", methods={"POST"})
     * @OA\Response(
     *     response=201,
     *     description="Create a user",
     * )
     * @OA\RequestBody(
     *     description="Create a new user",
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(
     *             ref=@Model(type=User::class)
     *         )
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Invalid data",
     *
     * )
     * @OA\Tag(name="User")
     */
    public function create(Request $request): JsonResponse
    {
        $userData = json_decode($request->getContent(), true);
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($userData);
        if ($form->isValid()) {
            if ($this->userRepository->findByUsername($user->getUsername())) {
                return new JsonResponse(['message' => 'Invalid data'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            $this->userManager->create($user, $this->getUser());

            return new JsonResponse([], JsonResponse::HTTP_CREATED);
        }
        
        return new JsonResponse(['message' => 'Invalid data'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @Route("/api/users/{slug}", name="user_delete", methods={"DELETE"})
     * @OA\Response(
     *     response=204,
     *     description="Delete a user",
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="Unauthorized action",
     * )
     * @OA\Tag(name="User")
     */
    public function delete(string $slug): JsonResponse
    {
        $user = $this->userRepository->findOneBySlug($slug);
        if (!$user) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }
        if ($user->getClient() === $this->getUser()) {
            $this->userManager->delete($user);

            return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
        }

        return new JsonResponse([], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
