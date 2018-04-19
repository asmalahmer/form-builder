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
    public function builderFormAction(Form $formEntity, Request $request)
    {
        return $this->render('default/builder.html.twig', [
            'formEntity' => $formEntity
        ]);
    }

    /**
     * @Route("/api/form/save", name="apiSaveForm")
     */
    public function apiSaveFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $json = $request->request->get('formData');

        $form = new Form();
        $form->setJson(json_decode($json, true));
        $em->persist($form);
        $em->flush();

        return new JsonResponse([
            'success'   => true,
            'formData'  => json_decode($json, true)
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
