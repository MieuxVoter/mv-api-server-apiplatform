<?php


namespace App\Form\DataTransformer;


use App\Serializer\TallyDeserializer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Helps supporting multiple formats for tallies in GET query parameters.
 *
 * Class TallyTransformer
 * @package App\Form\DataTransformer
 */
class TallyTransformer implements DataTransformerInterface
{

    // FIXME: Add these to messages YAML
    const MSG_TALLY_EMPTY = "error.transformer.tally.empty";
    const MSG_TALLY_INVALID = "error.transformer.tally.invalid";
    const MSG_TALLY_NOT_STRING = "error.transformer.tally.not_a_string";
    const MSG_TALLY_NOT_CONSISTENT = "error.transformer.tally.not_consistent";

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * TallyTransformer constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * This method is called when the form field is initialized with its default data, on
     * two occasions for two types of transformers:
     *
     * 1. Model transformers which normalize the model data.
     *    This is mainly useful when the same form type (the same configuration)
     *    has to handle different kind of underlying data, e.g The DateType can
     *    deal with strings or \DateTime objects as input.
     *
     * 2. View transformers which adapt the normalized data to the view format.
     *    a/ When the form is simple, the value returned by convention is used
     *       directly in the view and thus can only be a string or an array. In
     *       this case the data class should be null.
     *
     *    b/ When the form is compound the returned value should be an array or
     *       an object to be mapped to the children. Each property of the compound
     *       data will be used as model data by each child and will be transformed
     *       too. In this case data class should be the class of the object, or null
     *       when it is an array.
     *
     * All transformers are called in a configured order from model data to view value.
     * At the end of this chain the view data will be validated against the data class
     * setting.
     *
     * This method must be able to deal with empty values. Usually this will
     * be NULL, but depending on your implementation other empty values are
     * possible as well (such as empty strings). The reasoning behind this is
     * that data transformers must be chainable. If the transform() method
     * of the first data transformer outputs NULL, the second must be able to
     * process that value.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function transform($value)
    {
        // Implementation should e trivial but is not needed for now.
        throw new TransformationFailedException("Not implemented");
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * This method is called when {@link Form::submit()} is called to transform the requests tainted data
     * into an acceptable format.
     *
     * The same transformers are called in the reverse order so the responsibility is to
     * return one of the types that would be expected as input of transform().
     *
     * This method must be able to deal with empty values. Usually this will
     * be an empty string, but depending on your implementation other empty
     * values are possible as well (such as NULL). The reasoning behind
     * this is that value transformers must be chainable. If the
     * reverseTransform() method of the first value transformer outputs an
     * empty string, the second value transformer must be able to process that
     * value.
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
//            return null;
            $this->fail(self::MSG_TALLY_EMPTY);  // can we break convention?
        }

        // Support for ?tally[]=0,2,5&tally[]=4,1,2
        //         and ?tally[0]=0,2,5&tally[1]=4,1,2
        //         and ?tally[0,0]=0&tally[0,1]=2 … (etc.)
        if (is_array($value)) {
            $amount_separator = ",";
            $proposal_separator = "/";
            $flat = "";
            foreach ($value as $proposal_tally_string) {
                if (is_array($proposal_tally_string)) {
                    foreach ($proposal_tally_string as $amount) {
                        if (is_numeric($amount)) {
                            $flat .= $amount . $amount_separator;
                        } else {
                            $this->fail(self::MSG_TALLY_NOT_CONSISTENT);
                        }
                    }
                    $flat .= $proposal_separator;
                } else {
                    $flat .= $proposal_tally_string . $proposal_separator;
                }
            }
            $value = $flat;
        }

        if ( ! is_string($value)) {
            $this->fail(self::MSG_TALLY_NOT_STRING);
        }

        $tally = null;
        try {
            $td = new TallyDeserializer();
            $tally = $td->deserialize($value);
        } catch (\InvalidArgumentException $e) {
            $this->fail(self::MSG_TALLY_INVALID);
        }

        return $tally;
    }

    protected function fail($messageKey)
    {
        throw new TransformationFailedException(
            $this->translator->trans($messageKey)
        );
    }
}