<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Form;
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
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
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
     * @Route("/api/form/get", name="apiGetForm")
     */
    public function apiGetFormAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $formEntity = $em->getRepository('AppBundle:Form')->findOneBy([], ['id' => 'DESC']);

//        $object = json_decode(json_encode($formEntity->getJson()), false);
//        var_dump($object[0]);

        /**
         * Geht nicht: https://symfony.com/doc/3.4/forms.html#form-validation
         * Das hier testen: https://symfony.com/doc/3.4/form/without_class.html
         */
        $form = $this->createForm(FormType::class, null, ['formEntity' => $formEntity]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            var_dump($form->getData());

            exit;
        }


        return $this->render('default/form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
