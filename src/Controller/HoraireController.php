<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
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

#[Route('api/horaire', name: 'app_api_horaire_')]
class HoraireController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private HoraireRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }


    #[Route( methods: 'POST')]
    #[OA\Post(
        path: "/api/horaire",
        summary: "Creation d'un horaire",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'horaire à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "jour", type: "string", example: "jour"),
                    new OA\Property(property: "ouverture", type: "string", format: "time"),
                    new OA\Property(property: "fermeture", type: "string", format: "time"),

                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Horaire crée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "jour", type: "string", example: "Jour"),
                        new OA\Property(property: "ouverture", type: "string", format: "time"),
                        new OA\Property(property: "fermeture", type: "string", format: "time"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )
            )
        ]
    )]

    public function new(Request $request): JsonResponse
    {
        $horaire = $this->serializer->deserialize($request->getContent(), Horaire::class, 'json');
        $horaire->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($horaire);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($horaire, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_horaire_show',
        ['id' => $horaire->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }


    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/horaire/{id}",
        summary: "Afficher un Horaire par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'horaire à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Horaire trouvé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "jour", type: "string", example: "Jour"),
                        new OA\Property(property: "ouverture", type: "string", format: "time"),
                        new OA\Property(property: "fermeture", type: "string", format: "time"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Horaire non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $horaire = $this->repository->findOneBy(['id' => $id]);
        if($horaire){
            $responseData = $this->serializer->serialize($horaire, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
}


    #[Route('/{id}', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/horaire/{id}",
        summary: "Modifier un horaire par ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'horaire à modifier",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'horaire à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "jour", type: "string", example: "Nouvel horaire"),
                    new OA\Property(property: "ouverture", type: "string", format: "time", example: "Nouvelle heure d'ouverture"),
                    new OA\Property(property: "fermeture", type: "string", format: "time", example: "Nouvelle heure de fermeture"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Horaire modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Horaire non trouvé"
            )
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $horaire = $this->repository->findOneBy(['id' => $id]);
        if($horaire){
            $horaire = $this->serializer->deserialize(
                $request->getContent(),
                Horaire::class,
                'json',
                [AbstractNormalizer::OBJECT_TO_POPULATE => $horaire]
            );
            $horaire->setUpdatedAt(new \DateTimeImmutable());

            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }



    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/horaire/{id}",
        summary: "Supprimer un horaire par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'horaire à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Horaire supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Horaire non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $horaire = $this->repository->findOneBy(['id' => $id]);
        if($horaire){
        $this->manager->remove($horaire);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
