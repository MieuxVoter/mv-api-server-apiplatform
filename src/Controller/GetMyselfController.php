<?php

declare(strict_types=1);

namespace App\Controller;


use App\Serializer\ApiNormalizer;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;


/** @noinspection PhpUnused */
/**
 * TODO
 * Ah, this also needs doc.
 * But since the next version of ApiPlatform changes doc significantly,
 * we'd best wait for the apiplatform_bump branch to merge into master.
 *
 * @Route(
 *     path="/me",
 *     methods={"GET"},
 *     name="get_myself",
 * )
 *
 * Class AcceptInvitationController
 * @package App\Controller
 */
final class GetMyselfController
{
    use Is\EntityAware;
    use Is\UserAware;

    public function __invoke(
        Request $request,
        ApiNormalizer $normalizer
    ) : Response
    {
        $user = $this->getUser();

        if (null === $user) {
            // Maybe 401 instead ?  Perhaps we're behind the firewall and this will never trigger anyway.
            throw new NotFoundHttpException();
        }

        try {
            $output = $normalizer->normalize($user);
        } catch (ExceptionInterface $e) {
//            throw new NotFoundHttpException();
            throw $e; // I wanna know when this happens  (use sentry?)
        } catch (Exception $e) {
//            throw new NotFoundHttpException();
            throw $e; // I wanna know when this happens
        }

        // Hardcoded to JSON here ; figure out what service is used in ApiPlatform and use it!
        return JsonResponse::create($output);
    }
}
