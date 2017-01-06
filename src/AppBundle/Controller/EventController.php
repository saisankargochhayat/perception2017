<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Event controller.
 *
 * @Route("event")
 */
class EventController extends Controller
{
    /**
     * Lists all event entities.
     *
     * @Route("s", name="event_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $events = $em->getRepository('AppBundle:Event')->findAll();

        return $this->render('event/index.html.twig', array(
            'events' => $events,
        ));
    }

    /**
     * Finds and displays a event entity.
     *
     * @Route("/{id}", name="event_show")
     * @Method("GET")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Event $event)
    {
        return $this->render('event/show.html.twig', array(
            'event' => $event,
            'registered' => false
        ));
    }

    /**
     * Registers for an event.
     *
     * @Route("/{id}/register", name="event_register")
     * @Method("GET")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Event $event)
    {
        return $this->render('event/show.html.twig', array(
            'event' => $event,
            'registered' => true
        ));
    }

    /**
     * Unregister for an event.
     *
     * @Route("/{id}/unregister", name="event_unregister")
     * @Method("GET")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unregisterAction(Event $event)
    {
        return $this->render('event/show.html.twig', array(
            'event' => $event,
            'registered' => false
        ));
    }

}
