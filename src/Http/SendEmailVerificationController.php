<?php

namespace Skimpy\Comments\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Skimpy\Comments\Entities\Email;
use Skimpy\Comments\Mail\MailerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Illuminate\Validation\Factory as ValidatorFactory;
use Laravel\Lumen\Routing\Controller as BaseController;
use LaravelDoctrine\ORM\IlluminateRegistry as Registry;

class SendEmailVerificationController extends BaseController
{
    private ValidatorFactory $validator;
    private MailerInterface $mailer;
    private ObjectManager $em;
    private ObjectRepository $emails;

    public function __construct(
        ValidatorFactory $validator,
        MailerInterface $mailer,
        Registry $registry
    ) {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->em = $registry->getManager('comments');
        $this->emails = $this->em->getRepository(Email::class);
    }

    public function store(Request $request): JsonResponse
    {
        $validationResult = $this->validateRequest($request);
        if ($validationResult !== null) {
            return $validationResult;
        }

        $data = $this->sanitizeData($request);
        $email = $this->getEmail($data);

        $this->persistEmail($email);
        $this->sendVerificationEmail($email, $request->input('email'));

        return $this->successResponse();
    }

    private function validateRequest(Request $request): ?JsonResponse
    {
        $validator = $this->validator->make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'entry_uri' => 'required',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'error' => $validator->errors()->first()
            ], 400);
        }

        return null;
    }

    /**
     * @return array{name: string, email: string, entry_uri: string}
     */
    private function sanitizeData(Request $request): array
    {
        return [
            'name' => strip_tags($request->input('name')),
            'email' => filter_var($request->input('email'), FILTER_SANITIZE_EMAIL),
            'entry_uri' => strip_tags($request->input('entry_uri')),
        ];
    }

    private function persistEmail(Email $email): void
    {
        $this->em->persist($email);
        $this->em->flush();
    }

    private function sendVerificationEmail(Email $email, string $recipientEmail): void
    {
        $this->mailer->send(
            $recipientEmail,
            config('comments.mail_subject'),
            $this->body($email)
        );
    }

    private function successResponse(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Verification email sent',
            'status' => 'success'
        ], 200);
    }

    /**
     * @param array{name: string, email: string, entry_uri: string} $data
     */
    private function getEmail(array $data): Email
    {
        $existingEmail = $this->emails->findOneBy(['email' => $data['email']]);

        if ($existingEmail) {
            $existingEmail->updateName($data['name']);
            $existingEmail->resetToken();

            return $existingEmail;
        }

        return new Email(
            $data['name'],
            $data['email'],
            $data['entry_uri']
        );
    }

    private function body(Email $email): string
    {
        $url = url('/comments/email-verification');
        $name = $email->name();
        $verificationLink = $url . '?token=' . $email->token();

        return <<<EOT
        Hey $name,

        Thanks for checking out my blog. I'm looking forward to your comment contributions!
        Please click the link below to verify your email.

        $verificationLink

        Best,
        Justin
        EOT;
    }
}