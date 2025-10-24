<?php

namespace OHMedia\FormBundle\Controller;

use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\FormBundle\Entity\Form;
use OHMedia\FormBundle\Service\FormBuilder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FormPostController extends AbstractController
{
    #[Route('/form/{id}/post', name: 'form_post', methods: ['POST'])]
    public function __invoke(
        FormBuilder $formBuilder,
        EmailRepository $emailRepository,
        Request $request,
        #[MapEntity(id: 'id')] Form $formEntity,
    ) {
        $form = $formBuilder->buildForm($formEntity);

        if (!$form) {
            return new JsonResponse('Form not found.', 500);
        }

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse('Form not submitted.', 500);
        }

        if (!$form->isValid()) {
            $formErrorIterator = $form->getErrors(true);

            $errorCount = $formErrorIterator->count();

            $errors = [];

            for ($i = 0; $i < $errorCount; ++$i) {
                $error = $formErrorIterator->offsetGet($i);

                if ($error instanceof FormError) {
                    $errors[] = $error->getMessage();
                } else {
                    $errors[] = (string) $error;
                }
            }

            return new JsonResponse([
                'success' => false,
                'errors' => $errors,
            ]);
        }

        try {
            $this->sendEmails($emailRepository, $formEntity, $form);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse(['success' => true]);
    }

    private function sendEmails(
        EmailRepository $emailRepository,
        Form $formEntity,
        FormInterface $form,
    ): void {
        $to = [];

        foreach ($formEntity->getRecipients() as $recipient) {
            $to[] = new EmailAddress($recipient);
        }

        list($replyTo, $copy) = $this->getReplyToAndCopyEmails(
            $formEntity,
            $form,
        );

        $formData = [];

        foreach ($formEntity->getFields() as $field) {
            $label = $field->getLabel();
            $value = $form->get($field->getName())->getData();

            if ($value instanceof \DateTimeInterface) {
                $formData[$label] = $value->format('M j, Y');
            } elseif (is_array($value)) {
                $formData[$label] = htmlspecialchars(implode(', ', $value));
            } elseif ($field->isTypeTextarea()) {
                $formData[$label] = nl2br(htmlspecialchars($value ?? ''));
            } else {
                $formData[$label] = htmlspecialchars($value ?? '');
            }
        }

        $subject = $formEntity->getSubject();

        $email = (new Email())
            ->setSubject($subject)
            ->setTemplate('@OHMediaForm/email/form_internal.html.twig', [
                'form_entity' => $formEntity,
                'form_data' => $formData,
                'subject' => $subject,
            ])
            ->setTo(...$to)
            ->setReplyTo(...$replyTo)
        ;

        $emailRepository->save($email, true);

        if ($copy) {
            $email = (new Email())
                ->setSubject($subject)
                ->setTemplate('@OHMediaForm/email/form_external.html.twig', [
                    'form_entity' => $formEntity,
                    'form_data' => $formData,
                    'subject' => $subject,
                ])
                ->setTo(...$copy)
            ;

            $emailRepository->save($email, true);
        }
    }

    private function getReplyToAndCopyEmails(
        Form $formEntity,
        FormInterface $form,
    ): array {
        $replyTo = [];
        $copy = [];

        foreach ($formEntity->getFields() as $field) {
            if (!$field->isTypeEmail()) {
                continue;
            }

            $value = $form->get($field->getName())->getData();

            if (!$value) {
                continue;
            }

            $fieldData = $field->getData();

            if ($fieldData['reply']) {
                $replyTo[] = new EmailAddress($value);
            }

            if ($fieldData['copy']) {
                $copy[] = new EmailAddress($value);
            }
        }

        return [$replyTo, $copy];
    }
}
