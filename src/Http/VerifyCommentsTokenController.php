<?php

namespace Skimpy\Comments\Http;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Skimpy\Comments\Entities\Email;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Laravel\Lumen\Routing\Controller as BaseController;
use LaravelDoctrine\ORM\IlluminateRegistry as Registry;

class VerifyCommentsTokenController extends BaseController
{
    private ObjectManager $om;
    private ObjectRepository $emails;

    public function __construct(Registry $registry)
    {
        $this->om = $registry->getManager('comments');
        $this->emails = $this->om->getRepository(Email::class);
    }

    public function show(Request $request): JsonResponse
    {
        $token = $request->input('token');

        $email = $this->emails->findOneBy([
            'token' => $token,
            'expiresAt' => ['>' => (new \DateTime())->format('Y-m-d H:i:s')],
            'verifiedAt' => null,
        ]);

        if (!$email) {
            return new JsonResponse([
                'error' => 'Token not found or expired',
            ], 400);
        }

        $email->verify();

        return new JsonResponse([
            'message' => 'Token is valid',
        ], 200);
    }
}