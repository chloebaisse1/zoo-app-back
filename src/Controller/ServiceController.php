<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
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

#[Route('api/service', name: 'app_api_service_')]
class ServiceController extends AbstractController
{
    public function __construct(
    private EntityManagerInterface $manager,
    private ServiceRepository $repository,
    private SerializerInterface $serializer,
    private UrlGeneratorInterface $urlGenerator,
        )
    {
    }


    #[Route(name: 'new', methods: 'POST')]
    #[OA\Post(
        path: "/api/service",
        summary: "Creation d'un service",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données du service à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "nom du service"),
                    new OA\Property(property: "description", type: "string", example: "description du service"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Service crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                        new OA\Property(property: "description", type: "string", example: "Description du service"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $service = $this->serializer->deserialize($request->getContent(), Service::class, 'json');
        $service->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($service);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($service, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_service_show',
        ['id' => $service->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);

    }


    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/service/{id}",
        summary: "Afficher un service par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Service trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom du service"),
                        new OA\Property(property: "description", type: "string", example: "Description du service"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]

    public function show(int $id): JsonResponse
    {
        $service = $this->repository->findOneBy(['id' => $id]);
        if($service){
        $responseData = $this->serializer->serialize($service, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
}


    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/service/{id}",
        summary: "Modifier un service par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données du service à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nouveau nom du service"),
                    new OA\Property(property: "description", type: "string", example: "Nouvelle description du service"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Service modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]

    public function edit(int $id, Request $request): JsonResponse
    {
        $service = $this->repository->findOneBy(['id' => $id]);
        if($service){
            $service = $this->serializer->deserialize(
                $request->getContent(),
                Service::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $service]
            );
            $service->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }




    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/service/{id}",
        summary: "Supprimer un service par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID du service à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Service supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Service non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $service = $this->repository->findOneBy(['id' => $id]);
        if($service){

        $this->manager->remove($service);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    return new JsonResponse(null, Response::HTTP_NOT_FOUND);
}
}
