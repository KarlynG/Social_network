<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use MainBundle\Entity\Publication;
use MainBundle\Entity\User;
use MainBundle\Entity\Like;

class LikeController extends Controller {

    public function likeAction($id = null) {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $publication_repo = $em->getRepository('MainBundle:Publication');
        $publication = $publication_repo->find($id);

        $like = new Like();
        $like->setUser($user);
        $like->setPublication($publication);

        $em->persist($like);
        $flush = $em->flush();

        if ($flush == null) {
            $notification = $this->get('app.notification_service');
            $notification->set($publication->getUser(), 'like', $user->getId(), $publication->getId());
            
            $status = 'Se dio like a la publicacion';
        } else {
            $status = 'Ha ocurrido un error al dar like';
        }

        return new Response($status);
    }

    public function unlikeAction($id = null) {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $like_repo = $em->getRepository('MainBundle:Like');
        $like = $like_repo->findOneBy(array(
            'user' => $user,
            'publication' => $id
        ));

        $em->remove($like);
        $flush = $em->flush();

        if ($flush == null) {
            $status = 'Se dio dislike a la publicacion';
        } else {
            $status = 'Ha ocurrido un error al dar dislike';
        }

        return new Response($status);
    }

    public function likesAction(Request $request, $nickname = null) {
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
        $dql = "SELECT l FROM MainBundle:Like l WHERE l.user = $user_id ORDER BY l.id DESC";
        $query = $em->createQuery($dql);

        $paginator = $this->get('knp_paginator');
        $likes = $paginator->paginate($query, $request->query->getInt('page', 1), 5);

        return $this->render('AppBundle:Like:likes.html.twig', array(
                    'user' => $user,
                    'pagination' => $likes
        ));
    }

}
