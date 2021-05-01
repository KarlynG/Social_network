<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use MainBundle\Entity\Publication;
use MainBundle\Entity\PrivateMessage;
use AppBundle\Form\PublicationType;
use AppBundle\Form\CommentType;

class PublicationController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // upload image
                $file = $form['image']->getData();

                if (!empty($file) && $file != null) {
                    $ext = $file->guessExtension();

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                        $file_name = $user->getId() . time() . "." . $ext;
                        $file->move("uploads/publications/images", $file_name);

                        $publication->setImage($file_name);
                    } else {
                        $publication->setImage(null);
                    }
                } else {
                    $publication->setImage(null);
                }

                $publication->setUser($user);
                $publication->setCreatedAt(new \DateTime("now"));

                $em->persist($publication);
                $flush = $em->flush();

                if ($flush == null) {
                    $status = 'La publicación se ha creado correctamente';
                } else {
                    $status = 'Error al crear la publicacion';
                }
            } else {
                $status = 'La publicacion no se ha creado';
            }

            $this->session->getFlashBag()->add("status", $status);
            return $this->redirectToRoute('home_publications');
        }

        $publications = $this->getPublications($request);

        return $this->render('AppBundle:Publication:home.html.twig', array(
                    'form' => $form->createView(),
                    'pagination' => $publications
        ));
    }

    public function getPublications($request) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $publications_repo = $em->getRepository('MainBundle:Publication');
        $following_repo = $em->getRepository('MainBundle:Following');

        $following = $following_repo->findBy(array(
            'user' => $user
        ));
        $following_array = array();
        foreach ($following as $follow) {
            $following_array[] = $follow->getFollowed();
        }

        $query = $publications_repo->createQueryBuilder('p')
                ->where('p.user = (:user_id) OR p.user IN (:following)')
                ->setParameter('user_id', $user->getId())
                ->setParameter('following', $following_array)
                ->orderBy('p.id', 'DESC')
                ->getQuery();

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                5
        );

        return $pagination;
    }

    public function statsPublicationAction(Request $request, $id = null) {
        $em = $this->getDoctrine()->getManager();
        $publication_repo = $em->getRepository('MainBundle:Publication');
        $publication = $publication_repo->find($id);
        $logged_user = $this->getUser();

        $user_repo = $em->getRepository("MainBundle:User");
        $user = $user_repo->findOneBy(array("nick" => $publication->getUser()->getNick()));

        $dql = "SELECT p FROM MainBundle:Publication p WHERE p.id = $id ORDER BY p.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 1
        );

        $comment = new PrivateMessage();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment->setCreatedAt(new \DateTime("now"));
                $comment->setEmitter($logged_user);
                $comment->setReceiver($publication);

                $em->persist($comment);
                $flush = $em->flush();

                if ($flush == null) {
                    $notification = $this->get('app.notification_service');
                    $notification->set($publication->getUser(), 'comment', $logged_user->getId(), $publication->getId());
                    
                    $status = "Se agregó el comentario en la publicación";
                } else {
                    $status = "No funcionó xd :(";
                }
            } else {
                $status = 'Ha ocurrido un error al hacer el comentario';
            }
            $this->session->getFlashBag()->add("status", $status);
            return $this->redirectToRoute('home_publications');
        } else {
            $dql = "SELECT p FROM MainBundle:PrivateMessage p WHERE p.receiver = $id ORDER BY p.id DESC";
            $query = $em->createQuery($dql);
            $pagination2 = $paginator->paginate(
                    $query, $request->query->getInt('page', 1), 5
            );
        }

        return $this->render('AppBundle:Publication:statsPublication.html.twig', array(
                    'test' => $pagination,
                    'test2' => $user,
                    'form' => $form->createView(),
                    'comment' => $pagination2
        ));
    }

    public function removePublicationAction(Request $request, $id = null) {
        $em = $this->getDoctrine()->getManager();
        $publication_repo = $em->getRepository('MainBundle:Publication');
        $publication = $publication_repo->find($id);
        $user = $this->getUser();

        if ($user->getId() == $publication->getUser()->getId()) {
            $em->remove($publication);
            $flush = $em->flush();

            if ($flush == null) {
                $status = 'La publicación se ha borrado correctamente';
            } else {
                $status = 'Hubo un error al intentar borrar la publicación';
            }
        } else {
            $status = 'Hubo un error al intentar borrar la publicación';
        }

        return new Response($status);
    }

}
