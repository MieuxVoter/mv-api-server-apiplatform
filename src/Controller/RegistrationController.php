<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use MsgPhp\User\Command\CreateUser;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/users", name="api_register", methods={"POST"})
 */
final class RegistrationController
{
    public function __invoke(
        Request $request,
        FormFactoryInterface $formFactory,
        MessageBusInterface $bus,
        EntityManagerInterface $em
    ): Response {
        $data = [];
//        if (\is_string($token = $request->query->get('token')) && null !== $invitation = $em->find(UserInvitation::class, $token)) {
//            $data['email'] = $invitation->getEmail();
//        }

        $form = $formFactory->createNamed('', RegistrationType::class, $data, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
//            $data['invitation_token'] = $token;
            $bus->dispatch(new CreateUser($data));

            return new JsonResponse([
                'message' => "api.registration.success",
                'data' => $data,  // maybe instead output the user, with id?
            ], Response::HTTP_OK);
        }

        $error = "api.registration.failure.message";

        if ( ! $form->isSubmitted()) {
            $error .= "\n- "."api.registration.failure.missing_post_vars";
        } else {
            foreach ($form->getErrors() as $formError) {
                $error .= "\n"."- ".$formError->getCause(); // FIXME: I'm just as insecure about this as it is
            }
        }

        return new JsonResponse(['error' => $error], Response::HTTP_BAD_REQUEST);
    }
}
