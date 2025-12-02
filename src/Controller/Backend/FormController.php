<?php

namespace OHMedia\FormBundle\Controller\Backend;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Entity\FormField;
use OHMedia\FormBundle\Form\FormEntityType;
use OHMedia\FormBundle\Repository\FormRepository;
use OHMedia\FormBundle\Security\Voter\FormFieldVoter;
use OHMedia\FormBundle\Security\Voter\FormVoter;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Form\DeleteType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class FormController extends AbstractController
{
    public function __construct(private FormRepository $formRepository)
    {
    }

    #[Route('/forms', name: 'form_index', methods: ['GET'])]
    public function index(
        Paginator $paginator,
        Request $request,
    ): Response {
        $newForm = new Form();

        $this->denyAccessUnlessGranted(
            FormVoter::INDEX,
            $newForm,
            'You cannot access the list of forms.'
        );

        $qb = $this->formRepository->createQueryBuilder('f');
        $qb->orderBy('CASE WHEN f.published_at IS NULL THEN 0 ELSE 1 END', 'ASC');
        $qb->addOrderBy('f.name', 'ASC');

        $searchForm = $this->getSearchForm($request);

        $this->applySearch($searchForm, $qb);

        return $this->render('@OHMediaForm/form/form_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_form' => $newForm,
            'attributes' => $this->getAttributes(),
            'search_form' => $searchForm,
        ]);
    }

    private function getSearchForm(Request $request): FormInterface
    {
        $formBuilder = $this->container->get('form.factory')
            ->createNamedBuilder('', FormType::class, null, [
                'csrf_protection' => false,
            ]);

        $formBuilder->setMethod('GET');

        $formBuilder->add('search', TextType::class, [
            'required' => false,
        ]);

        $formBuilder->add('status', ChoiceType::class, [
            'required' => false,
            'choices' => [
                'All' => '',
                'Published' => 'published',
                'Scheduled' => 'scheduled',
                'Draft' => 'draft',
            ],
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        return $form;
    }

    private function applySearch(FormInterface $form, QueryBuilder $qb): void
    {
        $search = $form->get('search')->getData();

        if ($search) {
            $qb->leftJoin('f.fields', 'ff');

            $searchFields = [
                'f.name',
                'f.description',
                'f.recipients',
                'f.agreement_text',
                'f.subject',
                'f.success_message',
                'f.email_message',
                'ff.label',
                'ff.help',
            ];

            $searchLikes = [];
            foreach ($searchFields as $searchField) {
                $searchLikes[] = "$searchField LIKE :search";
            }

            $qb->andWhere('('.implode(' OR ', $searchLikes).')')
                ->setParameter('search', '%'.$search.'%');
        }

        $status = $form->get('status')->getData();

        if ('published' === $status) {
            $qb->andWhere('f.published_at IS NOT NULL');
            $qb->andWhere('f.published_at <= :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('scheduled' === $status) {
            $qb->andWhere('f.published_at IS NOT NULL');
            $qb->andWhere('f.published_at > :now');
            $qb->setParameter('now', DateTimeUtil::getDateTimeUtc());
        } elseif ('draft' === $status) {
            $qb->andWhere('f.published_at IS NULL');
        }
    }

    #[Route('/form/create', name: 'form_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $formEntity = new Form();

        $this->denyAccessUnlessGranted(
            FormVoter::CREATE,
            $formEntity,
            'You cannot create a new form.'
        );

        $form = $this->createForm(FormEntityType::class, $formEntity);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->save($formEntity, true);

                $this->addFlash('notice', 'The form was created successfully.');

                return $this->redirectForm($formEntity, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_create.html.twig', [
            'form' => $form->createView(),
            'form_entity' => $formEntity,
        ]);
    }

    #[Route('/form/{id}', name: 'form_view', methods: ['GET'])]
    public function view(
        #[MapEntity(id: 'id')] Form $formEntity,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::VIEW,
            $formEntity,
            'You cannot view this form.'
        );

        return $this->render('@OHMediaForm/form/form_view.html.twig', [
            'form_entity' => $formEntity,
            'new_form_field' => (new FormField())->setForm($formEntity),
            'attributes' => $this->getAttributes(),
            'csrf_token_name' => FormFieldController::CSRF_TOKEN_REORDER,
        ]);
    }

    #[Route('/form/{id}/edit', name: 'form_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] Form $formEntity,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::EDIT,
            $formEntity,
            'You cannot edit this form.'
        );

        $form = $this->createForm(FormEntityType::class, $formEntity);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->save($formEntity, true);

                $this->addFlash('notice', 'The form was updated successfully.');

                return $this->redirectForm($formEntity, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_edit.html.twig', [
            'form' => $form->createView(),
            'form_entity' => $formEntity,
        ]);
    }

    #[Route('/form/{id}/duplicate', name: 'form_duplicate', methods: ['GET', 'POST'])]
    public function duplicate(
        Request $request,
        #[MapEntity(id: 'id')] Form $existingFormEntity,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::DUPLICATE,
            $existingFormEntity,
            'You cannot duplicate this form.'
        );

        $newFormEntity = clone $existingFormEntity;

        $form = $this->createForm(FormEntityType::class, $newFormEntity);

        $form->add('save', MultiSaveType::class, [
            'add_another' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->save($newFormEntity, true);

                $this->addFlash('notice', 'The form was duplicated successfully.');

                return $this->redirectForm($formEntity, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_duplicate.html.twig', [
            'form' => $form->createView(),
            'existing_form_entity' => $existingFormEntity,
            'form_entity' => $newFormEntity,
        ]);
    }

    private function redirectForm(Form $formEntity, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('form_edit', [
                'id' => $formEntity->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('form_create');
        } else {
            return $this->redirectToRoute('form_view', [
                'id' => $formEntity->getId(),
            ]);
        }
    }

    #[Route('/form/{id}/delete', name: 'form_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Form $formEntity,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::DELETE,
            $formEntity,
            'You cannot delete this form.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->remove($formEntity, true);

                $this->addFlash('notice', 'The form was deleted successfully.');

                return $this->redirectToRoute('form_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_delete.html.twig', [
            'form' => $form->createView(),
            'form_entity' => $formEntity,
        ]);
    }

    public static function getAttributes(): array
    {
        return [
            'form' => [
                'view' => FormVoter::VIEW,
                'create' => FormVoter::CREATE,
                'delete' => FormVoter::DELETE,
                'edit' => FormVoter::EDIT,
                'duplicate' => FormVoter::DUPLICATE,
            ],
            'form_field' => [
                'create' => FormFieldVoter::CREATE,
                'delete' => FormFieldVoter::DELETE,
                'edit' => FormFieldVoter::EDIT,
            ],
        ];
    }
}
