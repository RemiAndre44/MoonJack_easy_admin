<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /**
     * @param Category|null $category
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/category", name="category")
     */
    public function category(Category $category = null, Request $request, ObjectManager $manager){

        if(!$category){
            $category = new Category();
        }

        $categoryForm = $this->createForm(CategoryType::class, $category);

        $categoryForm->handleRequest($request);

        if($categoryForm->isSubmitted() && $categoryForm->isValid()){
            $manager->persist($category);
            $manager->flush();

            return $this->redirectToRoute("actus");
        }

        return $this->render('admin/category.html.twig',[
            'form' => $categoryForm->createView()
        ]);
    }

    /**
     * @param Article|null $article
     * @param Request $request
     * @param ObjectManager $manager
     * @param CategoryRepository $catRepo
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/new", name= "blog_create")
     * @Route("/{id}/edit", name= "blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $manager, CategoryRepository $catRepo)
    {
        $request = Request::createFromGlobals();

        $categories = $catRepo->findAll();

        if($request->request->get('action')){
            switch ($request->request->get('action')):
                case "create":
                    $args = [
                        'titre' => FILTER_SANITIZE_STRING,
                        'lien' => FILTER_SANITIZE_STRING,
                        'categorie' => FILTER_VALIDATE_INT
                    ];
                    $POST = filter_input_array(INPUT_POST, $args, false);
                    $file = $request->files->get('image');
                    $filePath = $this->checkFileImg($file);
                    if(isset($filePath['message'])){
                        return $this->render('admin/create.html.twig', [
                            'categories' => $categories,
                            'error' => $filePath['message']
                        ]);
                    }
                    try{
                        $article = new Article();
                        $article->setTitle($POST['titre']);
                        $article->setContent($request->request->get('contenu'));
                        $categorie = $manager->getRepository(Category::class)->find($POST['categorie']);
                        $article->setCategory($categorie);
                        $article->setCreatedAt(new \DateTime());
                        $article->setLink($POST['lien']);
                        $article->setImage($filePath);
                        $file->move("../public/img/", $file->getClientOriginalName());
                        $manager->persist($article);
                        $manager->flush();
                        return $this->redirectToRoute('actus');
                    }catch(\Exception $e){
                        return $this->render('admin/create.html.twig', [
                            'categories' => $categories,
                            'error' => 'erreur'
                        ]);
                    }

                    break;
            endswitch;
        }

        return $this->render('admin/create.html.twig', [
            'categories' => $categories
        ]);
    }

    //check file format
    function checkFileImg($file){
        //directory image
        $path = "/../img/";
        $target_file_img = $path . basename($file->getClientOriginalName());
        $uploadOk = 1;
        //bring back extension
        $imageFileType_img = pathinfo($target_file_img, PATHINFO_EXTENSION);
        //file image?
        $size = $file->getSize();
        if (file_exists($target_file_img)) {
            return $ERROR = [
                "message" => "L'image existe déjà"
            ];
        }elseif ($size > 5000000) {
            return $ERROR = [
                "message" => "La taille de l'image est trop importante"
            ];
        }elseif ($imageFileType_img != "jpg" &&$imageFileType_img != "JPG"&& $imageFileType_img != "png" && $imageFileType_img != "PNG" && $imageFileType_img != "jpeg" && $imageFileType_img != "JPEG" ) {
            return $ERROR = [
                "message" => "Erreur dans le type de format attendu"
            ];
        }else {
            return $target_file_img;
        }
    }

}
