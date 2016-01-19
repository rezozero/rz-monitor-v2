<?php

namespace WebsiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use WebsiteBundle\Entity\Website;
use WebsiteBundle\Form\WebsiteType;

/**
 * Website controller.
 *
 * @Route("/website")
 */
class WebsiteController extends Controller
{
    /**
     * Lists all Website entities.
     *
     * @Route("/", name="website_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $websites = $em->getRepository('WebsiteBundle:Website')->findBy(
            array(),
            array('status' => 'DESC', 'name' => 'ASC')
        );

        return $this->render('website/index.html.twig', array(
            'websites' => $websites,
        ));
    }

    /**
     * Creates a new Website entity.
     *
     * @Route("/new", name="website_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $website = new Website();
        $form = $this->createForm('WebsiteBundle\Form\WebsiteType', $website);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($website);
            $em->flush();

            return $this->redirectToRoute('website_index');
            //return $this->redirectToRoute('website_show', array('id' => $website->getId()));
        }

        return $this->render('website/new.html.twig', array(
            'website' => $website,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Website entity.
     *
     * @Route("/{id}", name="website_show")
     * @Method("GET")
     */
    public function showAction(Website $website)
    {
        $deleteForm = $this->createDeleteForm($website);

        return $this->render('website/show.html.twig', array(
            'website' => $website,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Website entity.
     *
     * @Route("/{id}/edit", name="website_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Website $website)
    {
        $deleteForm = $this->createDeleteForm($website);
        $editForm = $this->createForm('WebsiteBundle\Form\WebsiteType', $website);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($website);
            $em->flush();

            return $this->redirectToRoute('website_edit', array('id' => $website->getId()));
        }

        return $this->render('website/edit.html.twig', array(
            'website' => $website,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Website entity.
     *
     * @Route("/{id}", name="website_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Website $website)
    {
        $form = $this->createDeleteForm($website);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($website);
            $em->flush();
        }

        return $this->redirectToRoute('website_index');
    }

    /**
     * Crawls a Website entity.
     *
     * @Route("/{id}/crawl", name="website_crawl")
     * @Method("GET")
     */
    public function crawlAction(Request $request, Website $website)
    {
        $producer = $this->get('old_sound_rabbit_mq.crawl_website_producer');
        $producer->publish($website->getMessage());

        return $this->redirectToRoute('website_index');
    }

    /**
     * Crawls all Website entities.
     *
     * @Route("/crawl/all", name="website_crawl_all")
     * @Method("GET")
     */
    public function crawlAllAction()
    {
        $em = $this->getDoctrine()->getManager();

        $websites = $em->getRepository('WebsiteBundle:Website')->findAll();

        foreach ($websites as $website) {
            $producer = $this->get('old_sound_rabbit_mq.crawl_website_producer');
            $producer->publish($website->getMessage());
        }

        return $this->redirectToRoute('website_index');
    }

    /**
     * Creates a form to delete a Website entity.
     *
     * @param Website $website The Website entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Website $website)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('website_delete', array('id' => $website->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
