<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Form;
use AppBundle\Entity\Value;
use AppBundle\Form\FormType;
use AppBundle\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->redirect($this->generateUrl('admin'));
    }

    /**
     * Renders a new form
     *
     * @Route("/form/{id}", name="getForm")
     */
    public function renderFormAction(Form $formEntity, Request $request, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(FormType::class, null, ['formEntity' => $formEntity]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valueEntity = new Value();

            $formData = $this->handleFiles($form->getData(), $fileUploader, false);

            $valueEntity->setJson($this->handleDates($formData, false));
            $valueEntity->setForm($formEntity);

            $em->persist($valueEntity);
            $em->flush();

            $this->addFlash(
                'success',
                'Die Eingaben wurden gespeichert'
            );

            return $this->redirect($this->generateUrl('getValue', ['id' => $valueEntity->getId()]));
        }


        return $this->render('default/form.html.twig', array(
            'form'          => $form->createView(),
            'formEntity'    => $formEntity,
        ));
    }

    /**
     * Renders a form with existing form data
     *
     * @Route("/value/{id}", name="getValue")
     */
    public function getValueAction(Value $valueEntity, Request $request, FileUploader $fileUploader)
    {
        $em = $this->getDoctrine()->getManager();

        $json = $valueEntity->getJson();
        $json = $this->handleFiles($json, $fileUploader);
        $valueEntity->setJson($this->handleDates($json));

        $form = $this->createForm(FormType::class, $valueEntity->getJson(), ['formEntity' => $valueEntity->getForm()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = array_merge($valueEntity->getJson(), array_filter($form->getData()));
            $formData = $this->handleFiles($formData, $fileUploader, false);
            $valueEntity->setJson($this->handleDates($formData, false));

            $em->persist($valueEntity);
            $em->flush();

            $this->addFlash(
                'success',
                'Die Eingaben wurden gespeichert'
            );
        }


        return $this->render('default/form.html.twig', array(
            'form'          => $form->createView(),
            'formEntity'    => $valueEntity->getForm(),
            'valueEntity'   => $valueEntity,
        ));
    }

    /**
     * Delete value
     *
     * @Route("/value/{id}/delete", name="deleteValue")
     */
    public function deleteValueAction(Value $valueEntity, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $redirect = $this->generateUrl('builderFormValues', ['id' => $valueEntity->getForm()->getId()]);

        $em->remove($valueEntity);
        $em->flush();

        $this->addFlash(
            'success',
            'Die Eingaben wurden gelöscht'
        );

        return $this->redirect($redirect);
    }

    /**
     * Format date fields for saving or form
     *
     * @param   array   $data
     * @param   bool    $object
     * @return  array
     */
    protected function handleDates(array $data, $object = true)
    {
        $dateTypes = preg_grep('/^date-/', array_keys($data));

        foreach ($dateTypes as $dateType) {
            if ($data[$dateType] && $object) {
                $data[$dateType] = new \DateTime($data[$dateType]);
            } else if ($data[$dateType]) {
                $data[$dateType] = $data[$dateType]->format('Y-m-d');
            }
        }

        return $data;
    }

    /**
     * Format file fields for saving or form
     *
     * @param   array           $data
     * @param   FileUploader    $fileUploader
     * @param   bool            $object
     * @return  array
     */
    protected function handleFiles(array $data, FileUploader $fileUploader, $object = true)
    {
        $fileTypes = preg_grep('/^file-/', array_keys($data));

        foreach ($fileTypes as $fileType) {
            if (!$data[$fileType]) {
                continue;
            }

            if ($object) {
                $data[$fileType] = new File($fileUploader->getTargetDirectory().$data[$fileType]);
            } else if ($data[$fileType] instanceof UploadedFile) {
                $data[$fileType] = $fileUploader->upload($data[$fileType]);
            } else if ($data[$fileType] instanceof File) {
                $data[$fileType] = $data[$fileType]->getFilename();
            }
        }

        return $data;
    }
}
