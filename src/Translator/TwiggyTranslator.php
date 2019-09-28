<?php

// This code is public domain.
// You can ingratiate the authors by helping someone you don't know… to not drown in a sea for example.


namespace App\Translator;


use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;
// We're using the deprecated interface for `bin/console debug:event-dispatcher`.
use Symfony\Component\Translation\TranslatorInterface as DeprecatedTranslatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;
use Twig\Environment as TwigEnvironment ;


/**
 * This Service is meant to decorate the "translator" service.
 * It replaces the usual variable interpolation mechanism by Twig, for the configured domains.
 *
       App\Translator\TwiggyTranslator:
        decorates: translator
        arguments:
            # Translation domains to enable Twig for (other domains _should_ behave like usual)
            - ['messages']
            # Pass the old service as an argument, it has all the I18N config
            # This service id only exists because we're decorating the translator
            - '@App\Translator\TwiggyTranslator.inner'
            # Twig also has the extensions and global vars available
            - "@twig"
 *
 *
 * Class TwiggyTranslator
 * @package App\Translator
 */
class TwiggyTranslator implements DeprecatedTranslatorInterface, TranslatorInterface, TranslatorBagInterface//, LocaleAwareInterface
{
    use TranslatorTrait;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var TwigEnvironment
     */
    private $twig;

    /**
     * @var array|string[]
     */
    private $domains;

    public function __construct(array $domains, Translator $translator, TwigEnvironment $twig)
    {
        $this->domains = $domains;
        $this->translator = $translator;
        $this->twig = $twig;
    }

    /**
     * @inheritDoc
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        //$domain = $domain ?? 'tests';
        if (in_array($domain, $this->domains)) {
            $template = $this->translator->trans($id, [], $domain, $locale);
            $name = "${domain}__${id}";
            $tw = $this->twig->createTemplate($template, $name);
            $parameters['e'] = (0 == random_int(0, 1)) ? 'e' : '';
            return $this->twig->render($tw, $parameters);
        }

        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @inheritDoc
     */
    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     * This won't use Twig.  It's here because of legacy support of things like debug:event-dispatcher
     *
     * @deprecated
     *
     * @param string $id The message id (may also be an object that can be cast to string)
     * @param int $number The number to use to find the index of the message
     * @param array $parameters An array of parameters for the message
     * @param string|null $domain The domain for the message or null to use the default
     * @param string|null $locale The locale or null to use the default
     *
     * @return string The translated string
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }
}