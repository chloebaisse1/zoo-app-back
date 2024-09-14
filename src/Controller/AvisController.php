<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/avis', name: 'app_api_avis_')]
class AvisController extends AbstractController
{
    public function __construct(
    private EntityManagerInterface $manager,
    private AvisRepository $repository,
    private SerializerInterface $serializer,
    private UrlGeneratorInterface $urlGenerator,
    )
    {
    }


    #[Route( methods: 'POST')]
    #[OA\Post(
        path: "/api/avis",
        summary: "Creation d'un avis",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'avis à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "votre nom"),
                    new OA\Property(property: "prenom", type: "string", example: "votre prenom"),
                    new OA\Property(property: "message", type: "string", example: "laissez votre avis"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Avis crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "votre nom"),
                        new OA\Property(property: "prenom", type: "string", example: "votre prenom"),
                        new OA\Property(property: "message", type: "string", example: "laissez votre avis"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $avis = $this->serializer->deserialize($request->getContent(), Avis::class, 'json');
        $avis->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($avis);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($avis, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_avis_show',
        ['id' => $avis->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }


    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/avis/{id}",
        summary: "Afficher un avis par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Avis trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "votre nom"),
                        new OA\Property(property: "prenom", type: "string", example: "votre prenom"),
                        new OA\Property(property: "message", type: "string", example: "laisez votre avis"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Avis non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $avis = $this->repository->findOneBy(['id' => $id]);
        if($avis){
            $responseData = $this->serializer->serialize($avis, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
}



    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/avis/{id}",
        summary: "Supprimer un avis par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'avis à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Avis supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Avis non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $avis = $this->repository->findOneBy(['id' => $id]);
        if($avis){
        $this->manager->remove($avis);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
