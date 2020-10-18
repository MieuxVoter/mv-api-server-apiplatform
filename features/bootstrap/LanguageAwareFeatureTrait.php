<?php


use App\Translator\TwiggyTranslator;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;


/**
 * Traits work well with Behat and Feature Contexts.
 *
 * Since different Feature Contexts don't (and should not) share context,
 * (unless we inject them all with the same dependency)
 * each must re-detect the language since any context may use
 * the `$this->number()` or `$this->print()` tools.
 *
 * We could just put this directly in the BaseFeatureContext, all in all.
 *
 * Trait LanguageAwareTrait
 */
trait LanguageAwareFeatureTrait
{
    protected $language = 'en';

    /** @var Translator */
    protected $translator;

    /** @var TwiggyTranslator */
    protected $twiggy_translator;

    /**
     * @BeforeScenario
     */
    public function doLanguageDetection(BeforeScenarioScope $scope)
    {
        $this->language = $scope->getFeature()->getLanguage();
        $this->translator = new Translator($this->language);
        $yamlLoader = new YamlFileLoader();

        $this->translator->addLoader('yml', $yamlLoader);
        $this->translator->addResource(
            'yml',
            'translations/features.'.$this->language.'.yml',
            $this->language
        );
        $this->translator->addResource(
            'yml',
            'translations/features.en.yml',
            'en'
        );
        $this->translator->setFallbackLocales(['en']);
    }

    /**
     * Since kernel is not available in BeforeScenario…
     */
    protected function lazyLoadTwiggyTranslator()
    {
        if ( ! $this->twiggy_translator) {
            $this->twiggy_translator = new TwiggyTranslator([
                'messages', '', null
            ], $this->translator, $this->get('twig'));
        }
    }

    /**
     * Translate the provided key.
     *
     * @param string $key
     * @param array $vars
     * @param null $domain Defaults to 'features' when not provided or null.
     * @param null $locale
     * @return string
     */
    public function t(string $key, $vars = [], $domain = null, $locale = null): string
    {
        //$domain = (null == $domain) ? "features" : $domain;
        $this->lazyLoadTwiggyTranslator();
        return $this->twiggy_translator->trans($key, $vars, $domain, $locale);
    }

    public function failTrans(string $key, $vars = [])
    {
        $this->fail($this->t("test.failure.".$key, $vars));
    }
}