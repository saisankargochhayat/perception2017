<?php
namespace AppBundle\Controller\Admin;

use AppBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * File controller.
 *
 * @Route("admin/file")
 */
class FileController extends Controller
{
    /**
     * Lists all file entities.
     *
     * @Route("/", name="admin_file_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $files = $em->getRepository('AppBundle:File')->findAll();

        return $this->render('admin/file/index.html.twig', array(
            'files' => $files,
        ));
    }

    /**
     * Creates a new file entity.
     *
     * @Route("/new", name="admin_file_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $file = new File();
        $form = $this->createForm('AppBundle\Form\FileType', $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();

            return $this->redirectToRoute('admin_file_show', array('id' => $file->getId()));
        }

        return $this->render('admin/file/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a file entity.
     *
     * @Route("/{id}", name="admin_file_show")
     * @Method("GET")
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(File $file)
    {
        $deleteForm = $this->createDeleteForm($file);

        return $this->render('admin/file/show.html.twig', array(
            'file' => $file,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing file entity.
     *
     * @Route("/{id}/edit", name="admin_file_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, File $file)
    {
        $deleteForm = $this->createDeleteForm($file);
        $editForm = $this->createForm('AppBundle\Form\FileType', $file);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_file_edit', array('id' => $file->getId()));
        }

        return $this->render('admin/file/edit.html.twig', array(
            'file' => $file,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a file entity.
     *
     * @Route("/{id}", name="admin_file_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param File $file
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, File $file)
    {
        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();
        }

        return $this->redirectToRoute('admin_file_index');
    }

    /**
     * Creates a form to delete a file entity.
     *
     * @param File $file The file entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(File $file)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_file_delete', array('id' => $file->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
