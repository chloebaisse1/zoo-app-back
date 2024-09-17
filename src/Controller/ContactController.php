<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('api/contact', name: 'app_api_contact_')]
class ContactController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ContactRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
        )
    {
    }


    #[Route( methods: 'POST')]
    #[OA\Post(
        path: "/api/contact",
        summary: "Creation d'une demande de contact",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de la demande à creer",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "votre nom"),
                    new OA\Property(property: "email", type: "string", example: "email@email.com"),
                    new OA\Property(property: "demande", type: "string", example: "demande de renseignements"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Demande de contact créée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "votre nom"),
                        new OA\Property(property: "email", type: "string", example: "email@email.com"),
                        new OA\Property(property: "demande", type: "string", example: "Description de la demande"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                    ]
                )

            )
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $contact = $this->serializer->deserialize($request->getContent(), Contact::class, 'json');

        $this->manager->persist($contact);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($contact, 'json');
        $location = $this->urlGenerator->generate(
        'app_api_contact_show',
        ['id' => $contact->getId()],
        UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location"=> $location], true);
    }



    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/contact/{id}",
        summary: "Afficher une demande par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la demande à afficher",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Demande de contact trouvée avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "votre nom"),
                        new OA\Property(property: "email", type: "string", example: "email@email.com"),
                        new OA\Property(property: "demande", type: "string", example: "Description de la demande"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Demande de contact non trouvé"
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $contact = $this->repository->findOneBy(['id' => $id]);
            if($contact){
                $responseData = $this->serializer->serialize($contact, 'json');

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
            }

            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }



    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/contact/{id}",
        summary: "Supprimer une demande de contact par son ID",
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de la demande de contact à supprimer",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Demande de contact supprimé avec succès",
            ),
            new OA\Response(
                response: 404,
                description: "Demande de contact non trouvé"
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $contact = $this->repository->findOneBy(['id' => $id]);
        if($contact){
        $this->manager->remove($contact);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
