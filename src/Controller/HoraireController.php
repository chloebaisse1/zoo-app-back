<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/horaire')]
class HoraireController extends AbstractController
{
    private HoraireRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(HoraireRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    #[Route('', methods: 'GET')]
    #[OA\Get(
        path: "/api/horaire",
        summary: "Récupérer tous les horaires",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des horaires récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "jour", type: "string", example: "Lundi"),
                            new OA\Property(property: "ouverture", type: "string", format: "time"),
                            new OA\Property(property: "fermeture", type: "string", format: "time"),
                        ]
                    )
                )
            )
        ]
    )]
    public function index(): JsonResponse
    {
        $horaires = $this->repository->findAll();

        // Formatage de la réponse
        $responseData = [];
        foreach ($horaires as $horaire) {
            $responseData[] = [
                'id' => $horaire->getId(),
                'jour' => $horaire->getJour(),
                'ouverture' => $horaire->getOuverture()->format('H:i:s'),
                'fermeture' => $horaire->getFermeture()->format('H:i:s'),
            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    #[Route('', methods: 'POST')]
    #[OA\Post(
        path: "/api/horaire",
        summary: "Ajouter un nouvel horaire",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "jour", type: "string", example: "Lundi"),
                    new OA\Property(property: "ouverture", type: "string", format: "time"),
                    new OA\Property(property: "fermeture", type: "string", format: "time"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Horaire créé avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "jour", type: "string"),
                        new OA\Property(property: "ouverture", type: "string"),
                        new OA\Property(property: "fermeture", type: "string"),
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Requête invalide")
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['jour'], $data['ouverture'], $data['fermeture'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        $horaire = new Horaire();
        $horaire->setJour($data['jour']);
        $horaire->setOuverture(new \DateTime($data['ouverture']));
        $horaire->setFermeture(new \DateTime($data['fermeture']));

        $this->entityManager->persist($horaire);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $horaire->getId(),
            'jour' => $horaire->getJour(),
            'ouverture' => $horaire->getOuverture()->format('H:i:s'),
            'fermeture' => $horaire->getFermeture()->format('H:i:s'),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: 'PUT')]
    #[OA\Put(
        path: "/api/horaire/{id}",
        summary: "Modifier un horaire existant",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID de l'horaire"),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "jour", type: "string", example: "Lundi"),
                    new OA\Property(property: "ouverture", type: "string", format: "time"),
                    new OA\Property(property: "fermeture", type: "string", format: "time"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Horaire modifié avec succès"),
            new OA\Response(response: 404, description: "Horaire non trouvé"),
        ]
    )]
    public function edit(Request $request, $id): JsonResponse
    {
        $horaire = $this->repository->find($id);
        if (!$horaire) {
            return new JsonResponse(['error' => 'Horaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['jour'])) {
            $horaire->setJour($data['jour']);
        }
        if (isset($data['ouverture'])) {
            $horaire->setOuverture(new \DateTime($data['ouverture']));
        }
        if (isset($data['fermeture'])) {
            $horaire->setFermeture(new \DateTime($data['fermeture']));
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Horaire modifié avec succès'], Response::HTTP_OK);
    }

    #[Route('/{id}', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/horaire/{id}",
        summary: "Supprimer un horaire",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "ID de l'horaire"),
        ],
        responses: [
            new OA\Response(response: 204, description: "Horaire supprimé avec succès"),
            new OA\Response(response: 404, description: "Horaire non trouvé"),
        ]
    )]
    public function delete($id): JsonResponse
    {
        $horaire = $this->repository->find($id);
        if (!$horaire) {
            return new JsonResponse(['error' => 'Horaire non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($horaire);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}