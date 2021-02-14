<?php


namespace App\Controller;


use App\Serializer\TallyDeserializer;
use Miprem\Renderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/render/merit-profile.svg", name="merit_profile_renderer_svg")
 */
class MeritProfileRendererController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $tally_string = $request->get('tally', '');
        $default_width = 800;
        $default_height = round($default_width*0.618);
        $svg_w = (int) $this->getAnyFromRequest($request, ['width', 'w', 'x'], $default_width);
        $svg_h = (int) $this->getAnyFromRequest($request, ['height', 'h', 'y'], $default_height);

        // Support for ?tally[]=0,2,5&tally[]=4,1,2
        //         and ?tally[0]=0,2,5&tally[1]=4,1,2
        //         and ?tally[0,0]=0&tally[0,1]=2 … (etc.)
        // Refacto ideas to keep the Controller short:
        // → combine this and the TallyDenormalizer into a Request parameter handler?
        // → Forms?
        if (is_array($tally_string)) {
            $amount_separator = ",";
            $proposal_separator = "/";
            $flat = "";
            foreach ($tally_string as $proposal_tally_string) {
                if (is_array($proposal_tally_string)) {
                    foreach ($proposal_tally_string as $amount) {
                        if (is_numeric($amount)) {
                            $flat .= $amount.$amount_separator;
                        } else {
                            return $this->respondDemoUsage($svg_w, $svg_h,"Tally is not consistent.");
                        }
                    }
                    $flat .= $proposal_separator;
                } else {
                    $flat .= $proposal_tally_string.$proposal_separator;
                }
            }
            $tally_string = $flat;
        }

        if ( ! is_string($tally_string)) {
            return $this->respondDemoUsage($svg_w, $svg_h,"Tally is not a string.");
        }

        try {
            $td = new TallyDeserializer();
            $tally = $td->deserialize($tally_string);
        } catch (\InvalidArgumentException $e) {
            // → Generate a SVG with usage documentation
            return $this->respondDemoUsage($svg_w, $svg_h, "Invalid Tally.");
        }

        // From JSON in request body ; should we bother merging?
//        try {
//            $content = json_decode($request->getContent(), true);
//            $tally = $content['tally'];
//        } catch (\Exception $e) {
//            // Perhaps render a svg here as well?
//            return new Response("Invalid request content.", Response::HTTP_BAD_REQUEST);
//        }

        $css = "";

        $options = [
            "width" => $svg_w,
            "height" => $svg_h,
//            "sidebar_width" => 5,
//            "colors" => ["#ff0000", "#ff5500", "#ffaa00", "#ffff00", "#bbff00", "#55ff00", "#00dd00"],
//            "ext_border" => 5,
//            "int_border" => 2,
//            "header_height" => 10,
//            "grades_gap" => 1
        ];

        $poll = [
            'question' => [
                'label' => $request->get('question', ""),
            ],
            'tally' => $tally,
            'proposals' => array_map(function ($i) {
                $label = chr($i+65); // A, B, C, …
                return ['label' => $label];
            }, range(0, count($tally)-1)),
//            'grades' => array_map(function ($t, $i) {return "grade $i";}, $tally[0], range(0, count($tally[0])-1)),
        ];


        try {
            $miprem = new Renderer(Renderer::getMeritProfileTemplate(), $options, $css);
            $svg = $miprem->render($poll);
        } catch (\Exception $e) {
            return $this->respondDemoUsage($svg_w, $svg_h, "Miprem:".$e->getMessage());
        }

        return new Response($svg, Response::HTTP_OK, ['Content-Type' => 'text/svg']);
    }

    public function respondDemoUsage($w, $h, $msg="")
    {
        $svg = <<<DEMOSVG
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="$w" height="$h">

<text y="20">$msg</text>
<text y="40">📖 Usage:</text>
<text y="60">merit-profile.svg?tally=11,4,5,3/7,4,10,2/7,5,8,3</text>
<text y="80">{tally} = {proposal A tally} / {proposal B tally} / …</text>
<text y="100">{proposal tally} = {worse grade count}, …, {best grade count}</text>
<text y="120"></text>
<text y="140">🛠 Settings</text>
<text y="160">merit-profile.svg?width=800&height=600&tally=…</text>
<text y="200">🔗 <a href="/render/merit-profile.svg?tally=11,4,5,3/7,4,10,2/7,5,8,3&w=800&h=600">Complete example</a></text>

</svg>
DEMOSVG;

//        return Response::create($svg, Response::HTTP_OK);
        return Response::create($svg, Response::HTTP_OK, ['Content-Type' => 'text/svg']);
        // should we send back a 400 and not a 200?
    }

    // RequestSugar trait?
    /**
     * Tries, in sequence, to get GET request data from the provided $keys,
     * falling back on the next key when not found and returning when found.
     *
     * Useful for making aliases.
     *
     * @param Request $request
     * @param array $keys Eg: ['width', 'w', 'x']
     * @param null $default The default value to set when none was found in any key.
     * @return mixed|null
     */
    protected function getAnyFromRequest(Request $request, array $keys, $default=null)
    {
        foreach ($keys as $key) {
            $found = $request->get($key, null);
            if (
                (null === $found)
                ||
                ('' == $found)
            ) {
                continue;
            }
            return $found;
        }

        return $default;
    }
}
