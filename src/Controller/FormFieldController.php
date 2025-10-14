<?php

namespace OHMedia\FormBundle\Controller\Backend;

use Doctrine\DBAL\Connection;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\FormBundle\Entity\FormField;
use OHMedia\FormBundle\Form\FormFieldType;
use OHMedia\FormBundle\Repository\FormFieldRepository;
use OHMedia\FormBundle\Security\Voter\FormFieldVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class FormFieldController extends AbstractController
{
    public function __construct(private FormFieldRepository $formFieldRepository)
    {
    }

    private const CSRF_TOKEN_REORDER = 'form_field_reorder';

    #[Route('/form-fields', name: 'form_field_index', methods: ['GET'])]
    public function index(): Response
    {
        $newFormField = new FormField();

        $this->denyAccessUnlessGranted(
            FormFieldVoter::INDEX,
            $newFormField,
            'You cannot access the list of form fields.'
        );

        $formFields = $this->formFieldRepository->createQueryBuilder('ff')
            ->orderBy('ff.ordinal', 'asc')
            ->getQuery()
            ->getResult();

        return $this->render('@backend/form_field/form_field_index.html.twig', [
            'form_fields' => $formFields,
            'new_form_field' => $newFormField,
            'attributes' => $this->getAttributes(),
            'csrf_token_name' => self::CSRF_TOKEN_REORDER,
        ]);
    }

    #[Route('/form-fields/reorder', name: 'form_field_reorder_post', methods: ['POST'])]
    public function reorderPost(
        Connection $connection,
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormFieldVoter::INDEX,
            new FormField(),
            'You cannot reorder the form fields.'
        );

        $csrfToken = $request->request->get(self::CSRF_TOKEN_REORDER);

        if (!$this->isCsrfTokenValid(self::CSRF_TOKEN_REORDER, $csrfToken)) {
            return new JsonResponse('Invalid CSRF token.', 400);
        }

        $formFields = $request->request->all('order');

        $connection->beginTransaction();

        try {
            foreach ($formFields as $ordinal => $id) {
                $formField = $this->formFieldRepository->find($id);

                if ($formField) {
                    $formField->setOrdinal($ordinal);

                    $this->formFieldRepository->save($formField, true);
                }
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();

            return new JsonResponse('Data unable to be saved.', 400);
        }

        return new JsonResponse();
    }

    #[Route('/form-field/create', name: 'form_field_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $formField = new FormField();

        $this->denyAccessUnlessGranted(
            FormFieldVoter::CREATE,
            $formField,
            'You cannot create a new form field.'
        );

        $form = $this->createForm(FormFieldType::class, $formField);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formFieldRepository->save($formField, true);

                $this->addFlash('notice', 'The form field was created successfully.');

                return $this->redirectToRoute('form_field_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@backend/form_field/form_field_create.html.twig', [
            'form' => $form->createView(),
            'form_field' => $formField,
        ]);
    }

    #[Route('/form-field/{id}/edit', name: 'form_field_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] FormField $formField,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormFieldVoter::EDIT,
            $formField,
            'You cannot edit this form field.'
        );

        $form = $this->createForm(FormFieldType::class, $formField);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formFieldRepository->save($formField, true);

                $this->addFlash('notice', 'The form field was updated successfully.');

                return $this->redirectToRoute('form_field_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@backend/form_field/form_field_edit.html.twig', [
            'form' => $form->createView(),
            'form_field' => $formField,
        ]);
    }

    #[Route('/form-field/{id}/delete', name: 'form_field_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] FormField $formField,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormFieldVoter::DELETE,
            $formField,
            'You cannot delete this form field.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formFieldRepository->remove($formField, true);

                $this->addFlash('notice', 'The form field was deleted successfully.');

                return $this->redirectToRoute('form_field_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@backend/form_field/form_field_delete.html.twig', [
            'form' => $form->createView(),
            'form_field' => $formField,
        ]);
    }

    private function getAttributes(): array
    {
        return [
            'create' => FormFieldVoter::CREATE,
            'delete' => FormFieldVoter::DELETE,
            'edit' => FormFieldVoter::EDIT,
        ];
    }
}
