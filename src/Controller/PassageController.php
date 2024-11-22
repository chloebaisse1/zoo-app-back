<?php

namespace App\Controller;

use App\Entity\Passage;
use App\Repository\PassageRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/passage', name: 'app_api_passage_')]
class PassageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private PassageRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(name: 'new', methods: ['POST', 'OPTIONS'])]
    #[IsGranted('ROLE_EMPLOYEE')] // Restreindre l'accès à la création d'un passage
    #[OA\Post(
        path: "/api/passage",
        summary: "Creation d'un passage employé(e)",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du passage à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "nourriture donnée"),
                    new OA\Property(property: "quantitee", type: "string", example: "quantitée donnée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "date du passage"),
                    new OA\Property(property: "heure", type: "string", format: "time", example: "heure du passage"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Passage crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "nourriture", type: "string", example: "nourriture donnée"),
                        new OA\Property(property: "quantitee", type: "string", example: "quantitée donnée"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example:"date du passage"),
                        new OA\Property(property: "heure", type: "string", format: "time", example:"heure du passage"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $passage = $this->serializer->deserialize($request->getContent(), Passage::class, 'json');

        $this->manager->persist($passage);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($passage, 'json');
        $location = $this->urlGenerator->generate(
            'app_api_passage_show',
            ['id' => $passage->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET', 'OPTIONS'])]
    #[OA\Get(
        path: "/api/passage/{id}",
        summary: "Afficher un passage par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du passage à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Passage trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "nourriture", type: "string", example: "nourriture donnée"),
                        new OA\Property(property: "quantitee", type: "string", example: "quantitée donnée"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example:"date du passage"),
                        new OA\Property(property: "heure", type: "string", format: "time", example:"heure du passage"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Passage non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $passage = $this->repository->findOneBy(['id' => $id]);
        if ($passage) {
            $responseData = $this->serializer->serialize($passage, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT', 'OPTIONS'])]
    #[IsGranted('ROLE_EMPLOYEE')] // Restreindre l'accès à la modification d'un passage
    #[OA\Put(
        path: "/api/passage/{id}",
        summary: "Modifier un passage par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du passage à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données du passage à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "nourriture donnée"),
                    new OA\Property(property: "quantitee", type: "string", example: "quantitée donnée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "date du passage"),
                    new OA\Property(property: "heure", type: "string", format: "time", example: "heure du passage"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Passage modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Passage non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): Response
    {
        $passage = $this->repository->findOneBy(['id' => $id]);
        if (!$passage) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Passage::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $passage]);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE', 'OPTIONS'])]
    #[IsGranted('ROLE_EMPLOYEE')] // Restreindre l'accès à la suppression d'un passage
    #[OA\Delete(
        path: "/api/passage/{id}",
        summary: "Supprimer un passage par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du passage à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Passage supprimé avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Passage non trouvé"
            )
        ]
    )]
    public function delete(int $id): Response
    {
        $passage = $this->repository->findOneBy(['id' => $id]);
        if (!$passage) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($passage);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}