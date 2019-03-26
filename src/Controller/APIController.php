<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class APIController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function index()
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'APIController',
        ]);
    }

    /**
     * @Route("/categories", name="create_category", methods={"POST"})
     * @param CategoryRepository $repo
     * @param Request $request
     * @param ObjectManager $manager
     * @return Response
     */
    public function categories(CategoryRepository $repo, Request $request, ObjectManager $manager){

        $data = $request->getContent();
        $categorie = $this->get('serializer')->deserialize($data, 'App\Entity\Category', 'json');

        $manager->persist($categorie);

        $manager->flush();

        return new Response('', Response::HTTP_CREATED);

    }

    /**
     * @Route("/categories/{id}", name="categorie_show", methods={"GET"})
     * @param Category $category
     * @return Response
     */
    public function showCategories(Category $category){

        $data = $this->get('serializer')->serialize($category, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;

    }

    /**
     * @Route("/categories", name="category_list", methods={"GET"})
     * @param CategoryRepository $repository
     * @return Response
     */
    public function listCategories(CategoryRepository $repository){
        $categories = $repository->findAll();

        $data = $this->get('serializer')->serialize->serialize($categories, 'json');

        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

}
