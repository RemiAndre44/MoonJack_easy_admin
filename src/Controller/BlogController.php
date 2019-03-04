<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Commentaire;
use App\Form\CommentaireFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentaireRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


require __DIR__.'/../../vendor/PHPMailer/PHPMailer/src/SMTP.php';
require __DIR__.'/../../vendor/PHPMailer/PHPMailer/src/PHPMailer.php';
require __DIR__.'/../../vendor/PHPMailer/PHPMailer/src/OAuth.php';

class BlogController extends AbstractController
{

    /**
     * @param ArticleRepository $repo
     * @param Request $request
     * @param ObjectManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/actus", name="actus")
     */
    public function actus(ArticleRepository $repo, Request $request, ObjectManager $manager)
    {
        $commentaire = new Commentaire();

        $form = $this->createForm(CommentaireFormType::class, $commentaire);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $commentaire->setDateCreation(new \DateTime());
            $commentaire->setCompteur(0);
            $user = $this->getUser();
            $articleId = $request->get("article");
            $article = $repo->find($articleId);
            echo $article;
            $commentaire->setArticle($article);
            $commentaire->setAuteur($user->getUserName());
            $manager->persist($commentaire);
            $manager->flush();
        }

        $articles = $repo->findAll();

        return $this->render('blog/actus.html.twig', [
            'articles' => $articles,
            'form' => $form->createView()
        ]);

    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/", name="home")
     */
    public function home()
    {

        return $this->render('blog/home.html.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param ObjectManager $manager
     * @param UserRepository $uRepo
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/init", name="init")
     */
    public function init(Request $request, ObjectManager $manager, UserRepository $uRepo)
    {
        if ($request->get("usernameChange")) {
            $email = $request->get("usernameChange");
            $user = $uRepo->findOneBySomeField($email);
            if ($user != NULL) {
                $token = $this->generateToken('form');
                $user->setToken($token);
                $mail = $this->sendMail($user, "changeMDP");
                if($mail == true){
                    $success = "Un mail vient de vous être envoyé avec la procédure à suivre.";
                    try{
                        $result = $uRepo->updateUser($user);
                    }catch (\Exception $e){
                        $ERROR = "Echec lors de la mise à jour de l'utilisateur";
                        return $this->render('blog/init.html.twig', [
                            'error' => $ERROR
                        ]);
                    }
                    return $this->render('blog/init.html.twig', [
                        'success' => $success
                    ]);
                }else{
                    $ERROR = "Echec lors de l'envoie";

                    return $this->render('blog/init.html.twig', [
                        'error' => $ERROR
                    ]);
                }
            } else {
                $ERROR = "Aucun utilisateur d'inscrit avec cette adresse mail";

                return $this->render('blog/init.html.twig', [
                    'error' => $ERROR
                ]);
            }
        }
        return $this->render('blog/init.html.twig');
    }

    /**
     * @Route("/change_password", name="changePwd")
     * @param Request $request
     * @param UserRepository $uRepo
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function change_password(Request $request, UserRepository $uRepo, UserPasswordEncoderInterface $encoder){

        $request = Request::createFromGlobals();

        if($request->query->get("token") || $request->get("newPwd")){

            //variables
            if($request->get('token')){
                $token = $request->get('token');
            }else{
                $token = $request->query->get("token");
            }

            $user = $this->checkToken($token, $uRepo);

            //En cas de changement de mot de passe
            if ($request->get("newPwd")){
                $checkPassword = strcmp($request->get("newPwd"), $request->get("newPwd2"));
                if($checkPassword === 0 && strlen ($request->get("newPwd"))> 6){
                    $tokenBeforeDelete = $token;
                    $hash = $encoder->encodePassword($user, $request->get("newPwd"));
                    echo $hash;
                    $user->setPassword($hash);
                    $user->setOpen(0);
                    $user->setToken(null);
                    try{
                        $result = $uRepo->updateUser($user);
                        return $this->render('security/login.html.twig');
                    }catch (\Exception $e){
                        $ERROR = "Erreur dans la mise à jour";
                        return $this->render('blog/change_password.html.twig', [
                            'error' => $ERROR,
                            'token' => $tokenBeforeDelete
                        ]);
                    }
                }else {
                    $ERROR = "Les mots de passe doivent être identiques et doivent faire 6 caractères minimum";
                    return $this->render('blog/change_password.html.twig', [
                        'error' => $ERROR,
                        'token' => $user->getToken()
                    ]);
                }
            }

            return $this->render('blog/change_password.html.twig', [
                'token' => $user->getToken()
            ]);
        }else{
            return $this->render('blog/home.html.twig');
        }
    }

    function sendMail($user, $request, $body = null)
    {
        switch ($request) {
            case "changeMDP":
                $adresse ="http://127.0.0.1:8000/change_password?token=".$user->getToken();
                $token = $this->generateToken('form');
                $body = "<h1>Vous venez de faire une demande de changement de mot de passe</h1>
                         <p>Vous trouverez sur le lien ci-dessous une procédure pour le réinitialiser.</p>
                         <p><a href='".$adresse."'>Cliquez ici</a> </p>
                         <p>Le lien n'est accessible que pendant 15 minutes.</p>
                         <p>MoonJack</p>";
                $subject = 'MoonJack Changement de Mot de passe';
                $from = "remi.andre@oniris-nantes.fr";
                $to = $user->getEmail();
                break;
        }

        //Create a new PHPMailer instance
        $mail = new PHPMailer();
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();
        //Enable SMTP debugging
        $mail->SMTPDebug = 0;
        //Set the hostname of the mail server
        $mail->Host = "";
        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 465;
        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = "ssl";
        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = "";
        //Password to use for SMTP authentication
        $mail->Password = "";
        $mail->IsHTML(true);
        //Set who the message is to be sent from
        $mail->setFrom("", 'MoonJack');
        //Set who the message is to be sent to
        $mail->addAddress($to, 'New User');
        //Set the subject line
        $mail->Subject = $subject;
        //Replace the plain text body with one created manually
        $mail->Body = $body;
        //send the message, check for errors
        $mail->CharSet = 'utf8';

        if ($mail->send()) {
            return true;
        } else {
            return false;
        }
    }

    function generateToken($form)
    {
        $token = sha1(uniqid(microtime(), true));
        return $token;
    }

    function checkToken($token, UserRepository $uRepo){

        $user = $uRepo->findByExampleField("token", $token);
        $requestDate = $user->getRequest();
        $diff = date_diff($requestDate, new \DateTime());
        $diffMin= $diff->format('%i');
        $diffH= $diff->format('%h');
        $diffJour= $diff->format('%d');

        //check si le token est toujours valable
        if($diffMin > 55 || $diffH >= 1 || $diffJour >= 1){
            $ERROR = "Le lien n'est plus valable. Veuillez réitérer votre requête";
            return $this->render('blog/init.html.twig', [
                'error' => $ERROR
            ]);
        }
        //si le token ne correspond à aucun utilisateur
        else if($user == false){
            $ERROR = "Utilisateur inconnue";
            return $this->render('blog/init.html.twig', [
                'error' => $ERROR
            ]);
        }else{
            return $user;
        }
    }

}
