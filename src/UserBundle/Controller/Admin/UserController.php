<?php

namespace UserBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{
    /**
     *
     * @Route("admin/user", name="user_profile_show")
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $profiles = $em->getRepository('UserBundle:Profile')->findAll();

        return $this->render('profile/index.html.twig', array(
            'profiles' => $profiles,
        ));
    }
}
