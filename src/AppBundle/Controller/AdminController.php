<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Form;
use AppBundle\Entity\Value;
use AppBundle\Form\FormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * List all created builders
     *
     * @Route("/", name="admin")
     */
    public function adminAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $builders = $em->getRepository('AppBundle:Form')->findAll();

        return $this->render('default/builders-list.html.twig', [
            'builders' => $builders,
        ]);
    }

    /**
     * Renders the builder page (new)
     *
     * @Route("/builder/", name="builder")
     */
    public function builderAction(Request $request)
    {
        return $this->render('default/builder.html.twig');
    }

    /**
     * Renders the builder page from a existing builder (edit)
     *
     * @Route("/builder/{id}/", name="builderForm")
     */
    public function builderFormAction(Request $request, Form $formEntity)
    {
        return $this->render('default/builder.html.twig', [
            'formEntity' => $formEntity
        ]);
    }

    /**
     * Lists all related values from a builder
     *
     * @Route("/builder/{id}/values", name="builderFormValues")
     */
    public function builderFormValuesAction(Request $request, Form $formEntity)
    {
        $em = $this->getDoctrine()->getManager();

        $values = $em->getRepository('AppBundle:Value')->findValuesByForm($formEntity);

        $fields = [];
        foreach ($formEntity->getJson() as $json) {
            $fields[] = $json['name'];
        }

        return $this->render('default/builder-values.html.twig', [
            'formEntity' => $formEntity,
            'values'     => $values,
            'fields'     => $fields,
        ]);
    }

    /**
     * Saves a new or existing builder
     *
     * @Route("/ajax/builder/save", name="builderFormSave", methods={"POST"})
     */
    public function builderFormSaveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $json = $request->request->get('formData');

        if ($id = $request->request->get('id')) {
            $form = $em->getRepository('AppBundle:Form')->findOneBy(['id' => $id]);
        }

        if (empty($form)) {
            $form = new Form();
        }

        $form->setJson(json_decode($json, true));
        $em->persist($form);
        $em->flush();

        $this->addFlash(
            'success',
            'Das Formular wurde gespeichert'
        );

        return new JsonResponse([
            'success'   => true,
            'redirect'  => $this->generateUrl('builderForm', ['id' => $form->getId()]),
        ]);
    }

    /**
     * Deletes a builder
     *
     * @Route("/ajax/builder/delete", name="builderFormDelete", methods={"POST"})
     */
    public function builderFormDeleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $success = false;
        $redirect = false;

        $form = null;
        if ($id = $request->request->get('id')) {
            $form = $em->getRepository('AppBundle:Form')->findOneBy(['id' => $id]);
        }

        if ($form) {
            $em->remove($form);
            $em->flush();
            $success = true;
        }

        if ($success) {
            $redirect = $this->generateUrl('admin');
            $this->addFlash(
                'success',
                'Das Formular wurde gelöscht'
            );
        }

        return new JsonResponse([
            'success'   => $success,
            'redirect'  => $redirect,
        ]);
    }
}
