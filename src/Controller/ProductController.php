<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private SerializerInterface $serializer,
    ) {}

    /**
     * @Route("/api/products", name="product_list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Return a list of products",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
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
     * @OA\Tag(name="Product")
     */
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $productCount = $this->productRepository->count([]);
        $products = $this->productRepository->findBy(
            [],
            [],
            $limit,
            $limit*$page-$limit,
        );
        $paginatedProducts = new PaginatedRepresentation(
            new CollectionRepresentation($products),
            'product_list',
            [],
            $page,
            $limit,
            ceil($productCount/$limit),
        );

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($paginatedProducts, 'json')
        );
    }

    /**
     * @Route("//api/products/{slug}", name="product_show", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Return a product",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Product::class))
     *     )
     * )
     * @OA\Tag(name="Product")
     */
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($product, 'json')
        );
    }
}
