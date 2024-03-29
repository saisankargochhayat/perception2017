<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

/**
 * Profile controller.
 *
 */
class ProfileController extends Controller
{
    /**
     * Lists all profile entities.
     *
     * @Route("me", name="profile_index")
     * @Method("GET")
     */
    /**
     * @Route("/account", name="my_account")
     * @Route("/me", name="user_profile_my")
     * @Security("'ROLE_USER'")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function meAction()
    {
        return $this->redirectToRoute('user_profile_show', [ 'id' => $this->getUser()->getId() ] );
    }

    /**
     * Finds and displays a profile entity.
     *
     * @Route("user/{id}", name="user_profile_show")
     * @ParamConverter("profile", class="UserBundle:Profile", options={ "repository_method":"getProfileByUserId"})
     * @Method("GET")
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Profile $profile)
    {
        return $this->render('profile/show.html.twig', array(
            'profile' => $profile
        ));
    }

    /**
     * Displays a form to edit an existing profile entity.
     *
     * @Route("user/{user_id}/edit_profile", name="user_edit_profile")
     * @ParamConverter("profile", class="UserBundle:Profile", options={"repository_method":"getProfileByUserId"})
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param Profile $profile
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editProfileAction(Request $request, Profile $profile)
    {
        $editForm = $this->createForm('UserBundle\Form\ProfileType', $profile);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('profile_edit', array('id' => $profile->getId()));
        }

        return $this->render('profile/edit.html.twig', array(
            'profile' => $profile,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("user/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param User $user
     */
    public function editAction(Request $request, User $user) {

    }

    /**
     * Displays a welcome message.
     *
     * @Security("has_role('ROLE_USER')")
     * @Route("welcome", name="user_welcome_edit_profile")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function welcomeToProfileAction(Request $request)
    {
        $profile = $this->getDoctrine()->getRepository('UserBundle:Profile')->getProfileByUserId($this->getUser()->getId());
        $editForm = $this->createForm('UserBundle\Form\ProfileType', $profile);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect($this->getRedirect($request));
        }

        return $this->render('profile/welcome.html.twig', array(
            'profile' => $profile,
            'form' => $editForm->createView()
        ));
    }

    private function getRedirect(Request $request)
    {
        $redirect = '';
        if ($request->hasPreviousSession() && $request->getSession()
                ->has('_security.main.target_path')
        ) {
            $redirect = $request->getSession()
                ->get('_security.main.target_path');
        }

        $request->getSession()
            ->set('_security.main.target_path', '');

        if (empty($redirect)) {
            $redirect = '/';
        }

        return $redirect;
    }
}
