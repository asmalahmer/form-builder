<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Form;
use AppBundle\Entity\Value;
use AppBundle\Form\FormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('builder'));
    }

    /**
     * @Route("/builder/", name="builder")
     */
    public function builderAction(Request $request)
    {
        return $this->render('default/builder.html.twig', []);
    }

    /**
     * @Route("/builder/{id}", name="builderForm")
     */
    public function builderFormAction(Request $request, Form $formEntity)
    {
        return $this->render('default/builder.html.twig', [
            'formEntity' => $formEntity
        ]);
    }

    /**
     * @Route("ajax/builder/save", name="builderFormSave", methods={"POST"})
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

        return new JsonResponse([
            'success'   => true,
        ]);
    }

    /**
     * @Route("ajax/builder/delete", name="builderFormDelete", methods={"POST"})
     */
    public function builderFormDeleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $success = false;

        $form = null;
        if ($id = $request->request->get('id')) {
            $form = $em->getRepository('AppBundle:Form')->findOneBy(['id' => $id]);
        }

        if ($form) {
            $em->remove($form);
            $em->flush();
            $success = true;
        }

        return new JsonResponse([
            'success'   => $success,
        ]);
    }

    /**
     * @Route("/form/{id}", name="getForm")
     */
    public function getFormAction(Form $formEntity, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FormType::class, null, ['formEntity' => $formEntity]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valueEntity = new Value();

            $valueEntity->setJson($form->getData());
            $valueEntity->setForm($formEntity);

            $em->persist($valueEntity);
            $em->flush();

            return $this->redirect($this->generateUrl('getValue', ['id' => $valueEntity->getId()]));
        }


        return $this->render('default/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/value/{id}", name="getValue")
     */
    public function getValueAction(Value $valueEntity, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FormType::class, $valueEntity->getJson(), ['formEntity' => $valueEntity->getForm()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valueEntity->setJson($form->getData());

            $em->persist($valueEntity);
            $em->flush();
        }


        return $this->render('default/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
