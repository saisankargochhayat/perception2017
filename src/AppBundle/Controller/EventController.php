<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/{id}", name="event_show_by_id")
     * @Route("/{slug}", name="event_show")
     * @Method("GET")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Event $event)
    {
        $id = $event->getId();
        $cache = $this->get('doctrine_cache.providers.md_cache');
        $content = $cache->fetch('event:$id');
        if(!$content) {
            $content = $this->get('markdown.parser')->transformMarkdown($event->getContent());
            $cache->save("event:$id", $content);
        }
        return $this->render('event/show.html.twig', array(
            'event' => $event,
            'content' => $content,
            'registered' => false
        ));
    }

    /**
     * Registers for an event.
     *
     * @Route("/{id}/register", name="event_register")
     * @Method("POST")
     * @Security("'ROLE_USER'")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Event $event)
    {
        if($event->isTeamEvent()) {
            $this->addFlash('error','Only teams can register for a team event');
        } else if($this->get('event_manager')->isRegistered($event,$this->getUser())) {
            $this->addFlash('info','You have already registered for this event');
        } else {
            $this->get('event_manager')->registerUser($event, $this->getUser());
            $this->addFlash('success','You have successfully registered for this event');
        }

        return $this->redirectToRoute('event_show', $event->getId());
    }

    /**
     * Unregister for an event.
     *
     * @Route("/{id}/unregister", name="event_unregister")
     * @Method("POST")
     * @Security("'ROLE_USER'")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function unregisterAction(Event $event)
    {
        if($event->isTeamEvent()) {
            $this->addFlash('error','Only teams can register for a team event');
        } else if(!$this->get('event_manager')->isRegistered($event,$this->getUser())) {
            $this->addFlash('info','You have not registered for this event');
        } else {
            $this->get('event_manager')->registerUser($event, $this->getUser());
            $this->addFlash('success','You have successfully revoked your registration for this event');
        }

        return $this->redirectToRoute('event_show', $event->getId());
    }

}
