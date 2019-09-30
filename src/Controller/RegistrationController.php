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
 * Register a new User.
 * This documentation never appears anywhere, does it?
 * See App\Entity\UserDocumentation
 *
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

        $this->adaptRequest($request);

        $form = $formFactory->createNamed('', RegistrationType::class, $data, ['csrf_protection' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $bus->dispatch(new CreateUser($data));

            return new JsonResponse([
                'message' => "api.registration.success",
                'data' => $data,  // maybe instead output the user, with id?
            ], JsonResponse::HTTP_CREATED);
            // also provide ['Location' => $locationUrl]
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

    /**
     * Since ApiPlatform generated doc recommends usage of the Content instead of POST vars,
     * let's convert what's in the content to POST vars before feeding it to the form.
     * Whe also handle here the password['plain'] array quirk.
     *
     * This mutates the $request.
     *
     * @param Request $request
     */
    protected function adaptRequest(Request $request)
    {
        $content = $request->getContent();
        if ( ! empty($content)) {
            try {
                $content = json_decode($content, true, 3, JSON_BIGINT_AS_STRING&JSON_OBJECT_AS_ARRAY);
            }
            catch (\JsonException $e) { return; }
            catch (\Exception $e) { return; }

            if (isset($content["email"])) {
                $request->request->set('email', $content["email"]);
            }
            if (isset($content["password"])) {
                $request->request->set('password', ['plain' => $content["password"]]);
            }
        }
    }
}
