<?php

namespace EventsBundle\Controller;

use EventsBundle\Entity\Events;
use EventsBundle\Entity\Participer;
use EventsBundle\Form\EventsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use EventsBundle\Entity\Comment;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
{
    $em = $this->getDoctrine()->getManager();

    $event = $em->getRepository('EventsBundle:Events')->findAll();
    ///////////// Pagination
    $paginator = $this->get('knp_paginator');
    $pagination = $paginator->paginate(
        $event, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        2 /*limit per page*/
    );

    return $this->render('@Events/Default/index.html.twig',array('events'=>$pagination));

}
    public function DetailsAction($id,Request $request)
    {
        $sn = $this->getDoctrine()->getManager();
        $event = $sn->getRepository('EventsBundle:Events')->find($id);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if ($request->isMethod('post')) {
            $comment = new Comment();
            $comment->setUser($user);
            $comment->setEvents($event);
            $comment->setContent($request->get('comment-content'));
            $comment->setPublishdate(new \DateTime('now'));
            $event->setInterstednumber($event->getInterstednumber() + 1);
            $sn->persist($event);
            $sn->persist($comment);
            $sn->flush();
            return $this->redirectToRoute('event_details', array('id' => $event->getId()));
        }
        $comments = $sn->getRepository('EventsBundle:Comment')->findByEvents($event);
        return $this->render('@Events/Default/DetailsEvent.html.twig',array('events'=>$event,'comm'=>$comments));

    }
    public function ParticiperAction($id)
    {
        $sn = $this->getDoctrine()->getManager();
        $event = $sn->getRepository('EventsBundle:Events')->find($id);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $check = $sn->getRepository('EventsBundle:Participer')->findby(array('user'=>$user,'eventtP'=>$event));
        if (!$check){
        $p = new Participer();
        $p->setEventtP($event);
        $p->setUser($user);
        $sn->persist($p);
        $sn->flush();
        return $this->redirectToRoute('events_homepage');
        }
        return $this->redirectToRoute('events_homepage');

    }

    public function AjouterAction(Request $request)
    {
        $events = new Events();
        $form = $this->createForm('EventsBundle\Form\EventsType', $events);
        $form->handleRequest($request);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $events->setAssocation($user);
            $events->setParticipernumber(0);
            $events->setInterstednumber(0);



            $em->persist($events);

            $em->flush();

            return $this->redirectToRoute('events_show', array('id' => $events->getId()));
        }

        return $this->render('@Events/Default/Publier.html.twig', array(
            'events' => $events,
            'form' => $form->createView(),
        ));
    }

    public function FormulaireAction()
    {



        return $this->render('@Events/Default/Formulaire.html.twig');

    }


    public function editAction($id, Request $request)
    {


    $p= $this->getDoctrine()->getRepository(Events::class) ->find($id);
        $form= $this->createForm(EventsType::class,$p);
        $form->handleRequest($request);
        if ($form->isSubmitted())
        { $ef= $this->getDoctrine()->getManager(); $ef->persist($p);
            $ef->flush();
            return $this->redirectToRoute("events_show");
        }


        return $this->render('@Events/Default/editEvent.html.twig', array(

            "form"=>$form->createView()));

    }

    public function deleteAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $product=$em->getRepository(Events::class)->find($id);
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute('events_show');
    }




}
