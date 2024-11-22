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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api/compteR', name: 'app_api_compteR_')]
class CompteRController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private CompteRRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

   // Méthode pour lister tous les comptes-rendus, accessible aux vétérinaires et administrateurs
    #[Route(name: 'list', methods: 'GET')]
    #[OA\Get(
        path: "/api/compteR",
        summary: "Lister tous les comptes-rendus vétérinaires",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des comptes-rendus récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                            new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                            new OA\Property(property: "habitat", type: "string", example: "Habitat de l'animal"),
                            new OA\Property(property: "nourriture", type: "string", example: "Nourriture proposée"),
                            new OA\Property(property: "quantitee", type: "string", example: "Quantité proposée"),
                            new OA\Property(property: "date", type: "string", format: "date-time", example: "Date du compte-rendu"),
                            new OA\Property(property: "commentaire", type: "string", example: "Commentaire"),
                        ]
                    )
                )
            )
        ]
    )]
    public function list(): JsonResponse
    {
        // Récupérer tous les comptes-rendus
        $comptesRendus = $this->repository->findAll();
        $responseData = $this->serializer->serialize($comptesRendus, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    // Méthode pour récupérer un compte-rendu par son ID
    #[Route('/{id}', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/compteR/{id}",
        summary: "Récupérer un compte-rendu par son ID",
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte-rendu récupéré avec succès",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                        new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                        new OA\Property(property: "habitat", type: "string", example: "Habitat de l'animal"),
                        new OA\Property(property: "nourriture", type: "string", example: "Nourriture proposée"),
                        new OA\Property(property: "quantitee", type: "string", example: "Quantité proposée"),
                        new OA\Property(property: "date", type: "string", format: "date-time", example: "Date du compte-rendu"),
                        new OA\Property(property: "commentaire", type: "string", example: "Commentaire"),
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
        $compteR = $this->repository->find($id);

        if (!$compteR) {
            return new JsonResponse(['message' => 'Compte-rendu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $responseData = $this->serializer->serialize($compteR, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    // Méthode pour créer un compte-rendu
    #[Route(name: 'create', methods: 'POST')]
    #[OA\Post(
        path: "/api/compteR",
        summary: "Créer un nouveau compte-rendu vétérinaire",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "Habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "Nourriture proposée"),
                    new OA\Property(property: "quantitee", type: "string", example: "Quantité proposée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "Date du compte-rendu"),
                    new OA\Property(property: "commentaire", type: "string", example: "Commentaire"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Compte-rendu créé avec succès"
            ),
            new OA\Response(
                response: 400,
                description: "Erreur lors de la création du compte-rendu"
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $compteR = new CompteR();
        $compteR->setNom($data['nom']);
        $compteR->setRace($data['race']);
        $compteR->setHabitat($data['habitat']);
        $compteR->setNourriture($data['nourriture']);
        $compteR->setQuantitee($data['quantitee']);
        $compteR->setDate(new \DateTime($data['date']));
        $compteR->setCommentaire($data['commentaire']);

        $this->manager->persist($compteR);
        $this->manager->flush();

        $location = $this->urlGenerator->generate('app_api_compteR_show', ['id' => $compteR->getId()]);

        return new JsonResponse(null, Response::HTTP_CREATED, ['Location' => $location]);
    }

    #[Route('/search/{nom}', name: 'search', methods: 'GET')]
    #[OA\Get(
        path: "/api/compteR/search/{nom}",
        summary: "Rechercher des comptes-rendus par le nom de l'animal",
        responses: [
            new OA\Response(
                response: 200,
                description: "Comptes-rendus trouvés avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                            new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                            new OA\Property(property: "habitat", type: "string", example: "Habitat de l'animal"),
                            new OA\Property(property: "nourriture", type: "string", example: "Nourriture proposée"),
                            new OA\Property(property: "quantitee", type: "string", example: "Quantité proposée"),
                            new OA\Property(property: "date", type: "string", format: "date-time", example: "Date du compte-rendu"),
                            new OA\Property(property: "commentaire", type: "string", example: "Commentaire"),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 404,
                description: "Aucun compte-rendu trouvé"
            )
        ]
    )]
    public function search(string $nom): JsonResponse
    {
        // Recherche des comptes-rendus par le nom de l'animal
        $comptesRendus = $this->repository->findOneBy(['nom' => $nom]);

        // Vérification si des comptes-rendus ont été trouvés
        if (empty($comptesRendus)) {
            return new JsonResponse(['message' => 'Aucun compte-rendu trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Sérialisation des comptes-rendus trouvés
        $responseData = $this->serializer->serialize($comptesRendus, 'json');

        // Retourner les comptes-rendus en réponse
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    // Méthode pour mettre à jour un compte-rendu
    #[Route('/{id}', name: 'update', methods: 'PUT')]
    #[OA\Put(
        path: "/api/compteR/{id}",
        summary: "Mettre à jour un compte-rendu vétérinaire",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "nom", type: "string", example: "Nom de l'animal"),
                    new OA\Property(property: "race", type: "string", example: "Race de l'animal"),
                    new OA\Property(property: "habitat", type: "string", example: "Habitat de l'animal"),
                    new OA\Property(property: "nourriture", type: "string", example: "Nourriture proposée"),
                    new OA\Property(property: "quantitee", type: "string", example: "Quantité proposée"),
                    new OA\Property(property: "date", type: "string", format: "date-time", example: "Date du compte-rendu"),
                    new OA\Property(property: "commentaire", type: "string", example: "Commentaire"),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Compte-rendu mis à jour avec succès"
            ),
            new OA\Response(
                response: 400,
                description: "Erreur lors de la mise à jour du compte-rendu"
            )
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $compteR = $this->repository->find($id);

        if (!$compteR) {
            return new JsonResponse(['message' => 'Compte-rendu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $compteR->setNom($data['nom']);
        $compteR->setRace($data['race']);
        $compteR->setHabitat($data['habitat']);
        $compteR->setNourriture($data['nourriture']);
        $compteR->setQuantitee($data['quantitee']);
        $compteR->setDate(new \DateTime($data['date']));
        $compteR->setCommentaire($data['commentaire']);

        $this->manager->flush();

        return new JsonResponse(['message' => 'Compte-rendu mis à jour'], Response::HTTP_OK);
    }

    // Méthode pour supprimer un compte-rendu
    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/compteR/{id}",
        summary: "Supprimer un compte-rendu vétérinaire",
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
        $compteR = $this->repository->find($id);

        if (!$compteR) {
            return new JsonResponse(['message' => 'Compte-rendu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $this->manager->remove($compteR);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}