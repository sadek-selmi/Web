<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AdminBundle:Default:index.html.twig');
    }

    public function makeASSOCIATIONAction($id){
        $sn = $this->getDoctrine()->getManager();
        $user = $sn->getRepository('AppBundle:User')->find($id);
        $user->setRoles(['ROLE_ASSOCIATION']);
        $sn->persist($user);
        $sn->flush();
        return $this->redirectToRoute('fos_user_security_logout');
    }
}
