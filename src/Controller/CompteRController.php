<?php

namespace App\Controller;

use App\Entity\CompteR;
use App\Repository\CompteRRepository;
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

#[Route('api/compteR', name: 'app_api_compteR_')]
class CompteRController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CompteRRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,)
    {
    }

    #[Route(name: 'options', methods: 'OPTIONS')]
    public function options(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT, [
            'Access-Control-Allow-Origin' => 'http://127.0.0.1:3307',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, PUT, DELETE',
        ]);
    }

    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/compteR",
        summary: "Creation d'un compte-rendu vétérinaire",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du compte-rendu à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "nourriture proposée"),
                    new OA\Property(property: "quantitee", type: "string", example: "quantitée proposée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "date du compte-rendu"),
                    new OA\Property(property: "commentaire", type: "string", example: "commentaire"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Compte-rendu crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "nourriture", type: "string", example: "nourriture proposée"),
                        new OA\Property(property: "quantitee", type: "string", example: "quantitée proposée"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example:"date du compte-rendu"),
                        new OA\Property(property: "commentaire", type: "string", example: "commentaire"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $compteR = $this->serializer->deserialize($request->getContent(), CompteR::class, 'json');

        $this->manager->persist($compteR);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($compteR, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_compteR_show',
        ['id' => $compteR->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/compteR/{id}",
        summary: "Afficher un compte-rendu par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte-rendu à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "compte-rendu trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "nourriture", type: "string", example: "nourriture proposée"),
                        new OA\Property(property: "quantitee", type: "string", example: "quantitée proposée"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example:"date du compte-rendu"),
                        new OA\Property(property: "commentaire", type: "string", example: "commentaire"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Compte-rendu non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $compteR = $this->repository->findOneBy(['id' => $id]);
            if($compteR){
                $responseData = $this->serializer->serialize($compteR, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
            }

            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }



    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/compteR/{id}",
        summary: "Modifier un compte-rendu par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte-rendu à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données du compte-rendu à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "integer", example: 1),
                    new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "nourriture proposée"),
                    new OA\Property(property: "quantitee", type: "string", example: "quantitée proposée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "date du compte-rendu"),
                    new OA\Property(property: "commentaire", type: "string", example: "commentaire"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Compte-rendu modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Compte-rendu non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $compteR = $this->repository->findOneBy(['id' => $id]);
        if($compteR){
            $compteR = $this->serializer->deserialize(
                $request->getContent(),
                CompteR::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $compteR]
            );

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/compteR/{id}",
        summary: "Supprimer un compte-rendu par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du compte-rendu à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Compte-rendu supprimé avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Compte-rendu non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $compteR = $this->repository->findOneBy(['id' => $id]);
        if ($compteR) {
            $this->manager->remove($compteR);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
