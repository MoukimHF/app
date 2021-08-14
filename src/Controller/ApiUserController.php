<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ApiUserController extends AbstractController
{
    /**
     * @Route("/api/user", name="api_user")
     */
    public function index(UserRepository $users): Response
    {
        $serializer = $this->get('serializer');

        $allUsers = $users->findAll();
        // dd($allUsers);
        $json = $serializer->serialize($allUsers, 'json');
        $reponse = new Response($json, 200, [
            "content-type" => "application/json"
        ]);
        return $reponse;
    }

    /**
     * @Route("/api/user/{cin}", name="api_user/cin")
     */
    public function getUserByCin(UserRepository $users, $cin): Response
    {
        $serializer = $this->get('serializer');
        $user = $users->findByCin($cin);
        $json = $serializer->serialize($user, 'json');
        $reponse = new Response($json, 200, [
            "content-type" => "application/json"
        ]);
        return $reponse;
    }

    /**
     * @Route("/api/user/add", name="api_user/cin", methods="POST")
     */
    public function add(
        EntityManagerInterface $entityManager,
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $contenu = $request->getContent();
        try {
            $personne = $serializer->deserialize($contenu, User::class, "json");
            $errors = $validator->validate($personne);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            $entityManager->persist($personne);
            $entityManager->flush();
            return $this->json($personne, 201, [], [
                "groups" => "personne:read"
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                "status" => 400,
                "message" => $e->getMessage()
            ]);
        }
    }
}
