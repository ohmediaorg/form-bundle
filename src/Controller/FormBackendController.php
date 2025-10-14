<?php

namespace OHMedia\FormBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Form\FormType;
use OHMedia\FormBundle\Repository\FormRepository;
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
class FormBackendController extends AbstractController
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
        $qb->addOrderBy('f.published_at', 'DESC');

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
            $searchFields = [
                // TODO: put your search fields here
                'f.created_by',
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
        $form = new Form();

        $this->denyAccessUnlessGranted(
            FormVoter::CREATE,
            $form,
            'You cannot create a new form.'
        );

        $form = $this->createForm(FormType::class, $form);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->save($form, true);

                $this->addFlash('notice', 'The form was created successfully.');

                return $this->redirectToRoute('form_view', [
                    'id' => $form->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_create.html.twig', [
            'form' => $form->createView(),
            'form' => $form,
        ]);
    }

    #[Route('/form/{id}', name: 'form_view', methods: ['GET'])]
    public function view(
        #[MapEntity(id: 'id')] Form $form,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::VIEW,
            $form,
            'You cannot view this form.'
        );

        return $this->render('@OHMediaForm/form/form_view.html.twig', [
            'form' => $form,
            'attributes' => $this->getAttributes(),
        ]);
    }

    #[Route('/form/{id}/edit', name: 'form_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] Form $form,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::EDIT,
            $form,
            'You cannot edit this form.'
        );

        $form = $this->createForm(FormType::class, $form);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->save($form, true);

                $this->addFlash('notice', 'The form was updated successfully.');

                return $this->redirectToRoute('form_view', [
                    'id' => $form->getId(),
                ]);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_edit.html.twig', [
            'form' => $form->createView(),
            'form' => $form,
        ]);
    }

    #[Route('/form/{id}/delete', name: 'form_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Form $form,
    ): Response {
        $this->denyAccessUnlessGranted(
            FormVoter::DELETE,
            $form,
            'You cannot delete this form.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->formRepository->remove($form, true);

                $this->addFlash('notice', 'The form was deleted successfully.');

                return $this->redirectToRoute('form_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaForm/form/form_delete.html.twig', [
            'form' => $form->createView(),
            'form' => $form,
        ]);
    }

    private function getAttributes(): array
    {
        return [
            'view' => FormVoter::VIEW,
            'create' => FormVoter::CREATE,
            'delete' => FormVoter::DELETE,
            'edit' => FormVoter::EDIT,
        ];
    }
}
