<?php

namespace OHMedia\FormBundle\Controller;

use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\FormBundle\Service\FormBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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
        // TODO: Form $formEntity mapped
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

        $formData = $form->getData();

        $recipients = $formEntity->getRecipients();

        try {
            $to = [];

            foreach ($recipients as $recipients) {
                $to[] = new EmailAddress($recipient);
            }

            // $replyTo = new EmailAddress($formData['email']);

            $email = (new Email())
                ->setSubject($formEntity->getSubject())
                ->setTemplate('@OHMediaForm/email/form_email.html.twig', [
                    'data' => $formData,
                    'subject' => $subject,
                ])
                ->setTo($to)
                // ->setReplyTo($replyTo)
            ;

            $emailRepository->save($email, true);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }

        return new JsonResponse(['success' => true]);
    }
}
