<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Repository\AnimalRepository;
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

#[Route('api/animal', name: 'app_api_animal_')]
class AnimalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private AnimalRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }


    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/animal",
        summary: "Creation d'un animal",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'animal à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                    new OA\Property(property: "etat", type: "string", example: "etat de santé de l'animal"),

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Animal crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "etat", type: "string", example: "etat de santé de l'animal"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        $animal = $this->serializer->deserialize($request->getContent(), Animal::class, 'json');
        $animal->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($animal);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($animal, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_animal_show',
        ['id' => $animal->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/animal/{id}",
        summary: "Afficher un animal par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Animal trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "habitat de l'animal"),
                        new OA\Property(property: "etat", type: "string", example: "etat de santé de l'animal"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
            if($animal){
                $responseData = $this->serializer->serialize($animal, 'json');

                return new JsonResponse($responseData, Response::HTTP_OK, [], true);
                }

                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }



    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/animal/{id}",
        summary: "Modifier un animal par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'animal à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nouveau nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "Nouvelle race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "Nouveau habitat de l'animal"),
                    new OA\Property(property: "etat", type: "string", example: "Nouveau etat de santé de l'animal"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Animal modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if($animal){
            $animal = $this->serializer->deserialize(
                $request->getContent(),
                Animal::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $animal]
            );
            $animal->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }




    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/animal/{id}",
        summary: "Supprimer un animal par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'animal à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Animal supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Animal non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $animal = $this->repository->findOneBy(['id' => $id]);
        if($animal){
        $this->manager->remove($animal);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    return new JsonResponse(null, Response::HTTP_NOT_FOUND);
}
}
