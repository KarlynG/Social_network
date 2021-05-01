<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use MainBundle\Entity\User;
use AppBundle\Form\RegisterType;
use AppBundle\Form\UserType;

class UserController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request) {
        if (is_object($this->getUser())) {
            return $this->redirect('home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:User:login.html.twig', array(
                    'last_username' => $lastUsername,
                    'error' => $error
        ));
    }

    public function registerAction(Request $request) {
        if (is_object($this->getUser())) {
            return $this->redirect('home');
        }

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                //$user_repo = $em->getRepository("MainBundle:User");

                $query = $em->createQuery('SELECT u FROM MainBundle:User u WHERE u.email = :email OR u.nick = :nick')
                        ->setParameter('email', $form->get("email")->getData())
                        ->setParameter('nick', $form->get("nick")->getData());

                $user_isset = $query->getResult();

                if (count($user_isset) == 0) {

                    $factory = $this->get("security.encoder_factory");
                    $encoder = $factory->getEncoder($user);

                    $password = $encoder->encodePassword($form->get("password")->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE_USER");
                    $user->setImage(null);

                    $em->persist($user);
                    $flush = $em->flush();

                    if ($flush == null) {
                        $status = "Tu cuenta ha sido registrada, por favor activela en su correo";
                        $this->SendMail($user->getEmail(), $user->getId());

                        $this->session->getFlashBag()->add('status', $status);
                        return $this->redirect("login");
                    } else {
                        $status = "Ha ocurrido un problema al registrarse";
                    }
                } else {
                    $status = "El Usuario ya existe";
                }
            } else {
                $status = "Ha ocurrido un problema, intentelo de nuevo";
            }

            $this->session->getFlashBag()->add('status', $status);
        }

        return $this->render('AppBundle:User:register.html.twig', array(
                    "form" => $form->createView()
        ));
    }

    public function nickTestAction(Request $request) {
        $nick = $request->get("nick");

        $em = $this->getDoctrine()->getManager();
        $user_repo = $em->getRepository("MainBundle:User");
        $user_isset = $user_repo->findOneBy(array("nick" => $nick));

        $result = "used";
        if (count($user_isset) >= 1 && is_object($user_isset)) {
            $result = "used";
        } else {
            $result = "unused";
        }

        return new Response($result);
    }

    public function editUserAction(Request $request) {
        $user = $this->getUser();
        $user_image = $user->getImage();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $query = $em->createQuery('SELECT u FROM MainBundle:User u WHERE u.email = :email OR u.nick = :nick')
                        ->setParameter('email', $form->get("email")->getData())
                        ->setParameter('nick', $form->get("nick")->getData());

                $user_isset = $query->getResult();

                if (count($user_isset) == 0 || $user->getEmail() == $user_isset[0]->getEmail() && $user->getNick() == $user_isset[0]->getNick()) {

                    // upload file
                    $file = $form["image"]->getData();

                    if (!empty($file) && $file != null) {
                        $ext = $file->guessExtension();
                        if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                            $file_name = $user->getId() . time() . '.' . $ext;
                            $file->move("uploads/user", $file_name);

                            $user->setImage($file_name);
                        }
                    } else {
                        $user->setImage($user_image);
                    }

                    $em->persist($user);
                    $flush = $em->flush();

                    if ($flush == null) {
                        $status = "Se han modificado los datos";
                    } else {
                        $status = "No se han cambiado los datos";
                    }
                } else {
                    $status = "El Usuario ya existe";
                }
            } else {
                $status = "No se actualizaron los datos";
            }

            $this->session->getFlashBag()->add('status', $status);
            return $this->redirect('my-data');
        }

        return $this->render('AppBundle:User:edit_user.html.twig', array(
                    "form" => $form->createView()
        ));
    }

    public function userAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT u FROM MainBundle:User u ORDER BY u.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 5
        );

        return $this->render('AppBundle:User:users.html.twig', array(
                    'pagination' => $pagination
        ));
    }

    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $search = trim($request->query->get("search", null));


        if ($search == null) {
            return $this->redirect($this->generateUrl('home_publications'));
        }


        $dql = "SELECT u FROM MainBundle:User u "
                . "WHERE u.name LIKE :search OR u.surname LIKE :search"
                . " OR u.nick LIKE :search ORDER BY u.id ASC";
        $query = $em->createQuery($dql)->setParameter('search', "%$search%");
        $user_isset = $query->getResult();

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 5
        );

        if (count($user_isset) == 0) {
            $status = "Este usuario no existe en el sistema";
            $this->session->getFlashBag()->add('status', $status);
            return $this->render('AppBundle:User:users.html.twig', array(
                        'pagination' => $pagination
            ));
        } else {
            return $this->render('AppBundle:User:users.html.twig', array(
                        'pagination' => $pagination
            ));
        }
    }

    public function profileAction(Request $request, $nickname = null) {
        $em = $this->getDoctrine()->getManager();

        if ($nickname != null) {
            $user_repo = $em->getRepository("MainBundle:User");
            $user = $user_repo->findOneBy(array("nick" => $nickname));
        } else {
            $user = $this->getUser();
        }

        if (empty($user) || !is_object($user)) {
            return $this->redirect($this->generateUrl('home_publications'));
        }

        $user_id = $user->getId();
        $dql = "SELECT p FROM MainBundle:Publication p WHERE p.user = $user_id ORDER BY p.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $publications = $paginator->paginate($query, $request->query->getInt('page', 1), 5);

        return $this->render('AppBundle:User:profile.html.twig', array(
                    'user' => $user,
                    'pagination' => $publications
        ));
    }
    
    public function activeUserAction(){

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT u FROM MainBundle:User u WHERE u.id = :id')
                ->setParameter('id', $_GET['id']);

            $user_isset = $query->getResult();

            if ($user_isset[0]->getActive() == '1') {

                $this->session->getFlashBag()->add('status', 'Tu cuenta ha sido activada, incia sesion!');

                $qb = $em->createQueryBuilder();

                $q = $qb->update('MainBundle:User', 'u')
                        ->set('u.active', ':active')
                        ->where('u.id = :editId')
                        ->setParameter('active', '2')
                        ->setParameter('editId', $_GET['id'])
                        ->getQuery();
                $p = $q->execute(); 

            }

            return $this->redirect('login');

    }
    
    private function SendMail($mailDestiny, $id){

        $message = 'Hola, gracias por unirte a nostros. Acabas de registrarte en The Real Social Network. Da click al siguiente enlace para activar el usuario: http://localhost/curso-social-network/web/app_dev.php/verification?id=' . $id;

        mail($mailDestiny, "Activación De Usuario", $message);
    }

}
