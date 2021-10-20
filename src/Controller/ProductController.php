<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\PaginatedRepresentation;
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

    #[Route('/api/products', name: 'product_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $productCount = $this->productRepository->countAll();
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

    #[Route('/api/products/{slug}', name: 'product_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($product, 'json')
        );
    }
}
