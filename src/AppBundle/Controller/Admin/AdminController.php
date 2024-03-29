<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_USER')")
 * @Route("admin")
 */
class AdminController extends Controller
{
    /**
     * Lists all event entities.
     *
     * @Route("/", name="admin_home")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }
}
