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
     * Renders a new form
     *
     * @Route("/form/{id}", name="getForm")
     */
    public function renderFormAction(Form $formEntity, Request $request)
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
     * Renders a form with existing form data
     *
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
