<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Event');
        $flagships = $repo->getFlagships();
        $workshops = $repo->getWorkshops();
        $guestLectures = $repo->getGuestLectures();
        $celebrityAppearances = $repo->getCelebrityAppearances();

        $hasFlagships = count($flagships);
        $hasWorkshops = count($workshops);
        $hasGuestLectures = count($guestLectures);
        $hasCelebrityAppearances = count($celebrityAppearances);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'flagships' => $flagships,
            'workshops' => $workshops,
            'guestLectures' => $guestLectures,
            'celebrityAppearances' => $celebrityAppearances,
            'hasFlagships' => $hasFlagships,
            'hasGuestLectures' => $hasGuestLectures,
            'hasWorkshops' => $hasWorkshops,
            'hasCelebrityAppearances' => $hasCelebrityAppearances
        ]);
    }
}
