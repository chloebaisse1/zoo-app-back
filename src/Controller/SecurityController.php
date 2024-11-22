<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: "/api/registration",
        summary: "Inscription d'un nouvel utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l'utilisateur à inscrire",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "firstName", type: "string", example: "Doe"),
                    new OA\Property(property: "lastName", type: "string", example: "John"),
                    new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "mot de passe"),
                    new OA\Property(property: "role", type: "string", example: "ROLE_USER")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Utilisateur inscrit",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "email"),
                        new OA\Property(property: "apiToken", type: "string", example: "0123ErfoEOR54033"),
                        new OA\Property(
                            property: "roles",
                            type: "array",
                            items: new OA\Items(type: "string", example: "ROLE_USER")
                        )
                    ]
                )
            )
        ]
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // liste des roles autorisés
        $allowedRoles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_VETERINAIRE', 'ROLE_EMPLOYEE'];
        $role = $data['role'] ?? 'ROLE_USER';

        if (!in_array($role, $allowedRoles)) {
            return new JsonResponse(['message' => 'Role non autorisé'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setRoles([$role]);
        $user->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            ['user' => $user->getUserIdentifier(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: "/api/login",
        summary: "Connecter un utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Données de l’utilisateur pour se connecter",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "username", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Connexion réussie",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                        new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                        new OA\Property(
                            property: "roles",
                            type: "array",
                            items: new OA\Items(type: "string", example: "ROLE_USER")
                        )
                    ]
                )
            )
        ]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles()
        ]);
    }

    #[Route('/account/me', name: 'me', methods: 'GET')]
    #[OA\Get(
        path: "/api/account/me",
        summary: "Récupérer toutes les informations de l'objet User",
        responses: [
            new OA\Response(
                response: 200,
                description: "Tous les champs utilisateurs retournés"
            )
        ]
    )]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        $responseData = $this->serializer->serialize($user, 'json');

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/edit', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/account/edit",
        summary: "Modifier son compte utilisateur avec l'un ou tous les champs",
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données éventuelles de l'utilisateur à mettre à jour",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "firstName", type: "string", example: "Nouveau prénom")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Utilisateur modifié avec succès"
            )
        ]
    )]
    public function edit(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser()],
        );
        $user->setUpdatedAt(new \DateTimeImmutable());

        if (isset($request->toArray()['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        }

        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/users', name: 'get_users', methods: 'GET')]
    #[OA\Get(
        path: "/api/users",
        summary: "Récupérer la liste des utilisateurs",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des utilisateurs retournée",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object", properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "firstName", type: "string", example: "John"),
                        new OA\Property(property: "lastName", type: "string", example: "Doe"),
                        new OA\Property(property: "email", type: "string", example: "adresse@email.com"),
                        new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER")),
                    ])
                )
            )
        ]
    )]
    public function getUsers(): JsonResponse
    {
        $users = $this->manager->getRepository(User::class)->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ];
        }

        return new JsonResponse($userData, Response::HTTP_OK);
    }
#[Route('/users/{id}', name: 'delete_user', methods: 'DELETE')]
#[OA\Delete(
    path: "/api/users/{id}",
    summary: "Supprimer un utilisateur",
    responses: [
        new OA\Response(
            response: 204,
            description: "Utilisateur supprimé avec succès"
        ),
        new OA\Response(
            response: 404,
            description: "Utilisateur non trouvé"
        )
    ]
)]
public function deleteUser(int $id): JsonResponse
{
    $user = $this->manager->getRepository(User::class)->find($id);

    if (!$user) {
        return new JsonResponse(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
    }

    $this->manager->remove($user);
    $this->manager->flush();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT); // Renvoie 204 No Content
}
}
