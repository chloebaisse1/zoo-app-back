<?php

namespace App\Controller;

use App\Document\LikeCounter;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LikeCounterController extends AbstractController
{
    #[Route('/like/{animalId}', methods: ['POST', 'OPTIONS'])]
    public function likeAnimal(Request $request, string $animalId, DocumentManager $dm): JsonResponse
    {
        // Si la méthode est OPTIONS, renvoie les en-têtes CORS
        if ($request->isMethod('OPTIONS')) {
            return new JsonResponse(null, 200, [
                'Access-Control-Allow-Origin' => 'http://localhost:8000',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                'Access-Control-Max-Age' => '3600',
            ]);
        }

        // Recherche le compteur de likes pour l'animal
        $likeCounter = $dm->getRepository(LikeCounter::class)->findOneBy(['animalId' => $animalId]);

        if (!$likeCounter) {
            // Crée un nouveau compteur de likes si aucun n'existe
            $likeCounter = new LikeCounter($animalId);
        }

        // Incrémente le compteur de likes
        $likeCounter->increment();

        // Sauvegarde le compteur dans la base de données
        $dm->persist($likeCounter);
        $dm->flush();

        return new JsonResponse(['message' => 'Like added', 'likes' => $likeCounter->getCount()], 201);
    }
}
