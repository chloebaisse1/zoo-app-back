<?php

namespace App\Controller;

use App\Entity\Habitat;
use App\Repository\HabitatRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/habitat', name: 'app_api_habitat_')]
class HabitatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private HabitatRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }

    #[Route( methods: 'POST')]
    #[OA\Post(
        path: "/api/habitat",
        summary: "Creation d'un habitat",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'habitat à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "name", type: "string", example: "nom de l'habitat"),
                    new OA\Property(property: "description", type: "string", example: "description de l'habitat"),
                    new OA\Property(property: "animaux", type: "string", example: "animaux de l'habitat"),

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Habitat crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                        new OA\Property(property: "description", type: "string", example: "Description de l'habitat"),
                        new OA\Property(property: "animaux", type: "string", example: "animaux de l'habitat"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        $habitat = $this->serializer->deserialize($request->getContent(), Habitat::class, 'json');
        $habitat->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($habitat);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($habitat, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_habitat_show',
        ['id' => $habitat->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }


    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/habitat/{id}",
        summary: "Afficher un habitat par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Habitat trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Nom de l'habitat"),
                        new OA\Property(property: "description", type: "string", example: "Description de l'habitat"),
                        new OA\Property(property: "animaux", type: "string", example: "animaux de l'habitat"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
            if($habitat){
                $responseData = $this->serializer->serialize($habitat, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
            }

            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
    path: "/api/habitat/{id}",
    summary: "Modifier un habitat par ID",
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            description: "ID de l'habitat à modifier",
            schema: new OA\Schema(type: "integer")
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        description: "Nouvelles données de l'habitat à mettre à jour",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "name", type: "string", example: "Nouveau nom de l'habitat"),
                new OA\Property(property: "description", type: "string", example: "Nouvelle description du l'habitat"),
                new OA\Property(property: "animaux", type: "string", example: "Nouveau animaux de l'habitat"),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 204,
            description: "Habitat modifié avec succès"
        ),
        new OA\Response(
            response: 404,
            description: "Habitat non trouvé"
        )
    ]
)]

    public function edit(int $id, Request $request): JsonResponse
    {
    $habitat = $this->repository->findOneBy(['id' => $id]);
        if($habitat){
            $habitat = $this->serializer->deserialize(
                $request->getContent(),
                Habitat::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $habitat]
            );
            $habitat->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/habitat/{id}",
        summary: "Supprimer un habitat par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'habitat à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Habitat supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Habitat non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $habitat = $this->repository->findOneBy(['id' => $id]);
        if($habitat){
        $this->manager->remove($habitat);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
