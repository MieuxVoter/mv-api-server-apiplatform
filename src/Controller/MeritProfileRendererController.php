<?php

declare(strict_types=1);


namespace App\Controller;


use App\Form\DataTransformer\TallyTransformer;
use Exception;
use Miprem\Model\Poll;
use Miprem\Model\SvgConfig;
use Miprem\Renderer\PngGDRenderer;
// use Miprem\Renderer\PngIMRenderer; // dropped because we get 0 supported formats -- needs docker work
//    $ php --ri imagick
//
//    imagick
//
//    imagick module => enabled
//    imagick module version => 3.5.0
//    imagick classes => Imagick, ImagickDraw, ImagickPixel, ImagickPixelIterator, ImagickKernel
//    Imagick compiled with ImageMagick version => ImageMagick 7.0.10-48 Q16 x86_64 2020-12-12 https://imagemagick.org
//    Imagick using ImageMagick library version => ImageMagick 7.0.10-48 Q16 x86_64 2020-12-12 https://imagemagick.org
//    ImageMagick copyright => © 1999-2020 ImageMagick Studio LLC
//    ImageMagick release date => 2020-12-12
//    ImageMagick number of supported formats:  => 0
//
//    Directive => Local Value => Master Value
//    imagick.locale_fix => 0 => 0
//    imagick.skip_version_check => 0 => 0
//    imagick.progress_monitor => 0 => 0
//    imagick.set_single_thread => 1 => 1
//    imagick.shutdown_sleep_count => 10 => 10
//    imagick.allow_zero_dimension_images => 0 => 0
///////////////////////////////////////////////////////////////////////////////////////////////////////
use Miprem\Renderer\SvgRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



/**
 * This route renders a SVG|PNG of the merit profile of the provided tally.
 * See the related \App\Swagger\Documenter for ApiPlatform documentation and more information.
 */
final class MeritProfileRendererController extends AbstractController
{
    /**
     * @Route("/render/merit-profile.svg", name="merit_profile_svg_query")
     *
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function svgTallyFromGet(Request $request, TallyTransformer $tallyTransformer): Response
    {
        $tally_thing = $request->get('tally', ''); // string, array of string, array of array of int
        return $this->respondSvgForTally(
            $tally_thing, $request, $tallyTransformer
        );
    }

    /**
     * @Route("/render/merit-profile.png", name="merit_profile_png_query")
     *
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function pngTallyFromGet(Request $request, TallyTransformer $tallyTransformer): Response
    {
        $tally_thing = $request->get('tally', ''); // string, array of string, array of array of int
        return $this->respondPngForTally(
            $tally_thing, $request, $tallyTransformer
        );
    }

    /**
     * @Route("/{filepath}.svg", name="merit_profile_svg_path")
     *
     * @param string $filepath
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function svgtallyFromFilepath(string $filepath, Request $request, TallyTransformer $tallyTransformer): Response
    {
        $filepath = str_replace('-', ',', $filepath);
        return $this->respondSvgForTally($filepath, $request, $tallyTransformer);
    }

    /**
     * @Route("/{filepath}.png", name="merit_profile_png_path")
     *
     * @param string $filepath
     * @param Request $request
     * @param TallyTransformer $tallyTransformer
     * @return Response
     */
    public function pngTallyFromFilepath(string $filepath, Request $request, TallyTransformer $tallyTransformer): Response
    {
        $filepath = str_replace('-', ',', $filepath);
        return $this->respondPngForTally($filepath, $request, $tallyTransformer);
    }

    public function respondSvgForTally($tally_thing, Request $request, TallyTransformer $tallyTransformer): Response
    {
        return $this->respondImageForTally('svg', $tally_thing, $request, $tallyTransformer);
    }

    public function respondPngForTally($tally_thing, Request $request, TallyTransformer $tallyTransformer): Response
    {
        return $this->respondImageForTally('png', $tally_thing, $request, $tallyTransformer);
    }

    public function respondImageForTally($type, $tally_thing, Request $request, TallyTransformer $tallyTransformer): Response
    {
        $default_width = 800;
        $default_height = round($default_width*0.618);
        $img_w = (int) $this->getAnyFromRequest($request, ['width', 'w', 'x'], $default_width);
        $img_h = (int) $this->getAnyFromRequest($request, ['height', 'h', 'y'], $default_height);

        $tally = null;
        try {
            $tally = $tallyTransformer->reverseTransform($tally_thing);
        } catch (Exception $e) {
            // → Generate an image with usage documentation
            return $this->respondDemoUsage($type, $img_w, $img_h, $e->getMessage());
        }

        if (empty($tally)) {
            return $this->respondDemoUsage($type, $img_w, $img_h, "Provided tally is empty.");
        }

        // From JSON in request body ; should we even bother merging?
//        try {
//            $content = json_decode($request->getContent(), true);
//            $tally = $content['tally'];
//        } catch (\Exception $e) {
//            // Perhaps render a img here as well?
//            return new Response("Invalid request content.", Response::HTTP_BAD_REQUEST);
//        }

        $subject = $request->get('subject', "");

        $proposals = array_map(function ($i) {
            $label = chr($i+65); // A, B, C, …
            return ['label' => $label];
        }, range(0, count($tally)-1));

        $queryProposals = $request->get('proposals', []);
        if ( ! empty($queryProposals) && count($queryProposals) === count($tally)) {
            $proposals = array_map(function ($p) {
                $label = mb_strimwidth($p, 0, 20);
                return ['label' => $label];
            }, $queryProposals);
        }

        $options = [
            "width" => $img_w,
            "height" => $img_h,
//            "sidebar_width" => 5,
//            "colors" => ["#0000ff", "#ff5500", "#ffaa00", "#ffff00", "#bbff00", "#55ff00", "#00dd00"],
//            "ext_border" => 5,
//            "int_border" => 2,
//            "header_height" => 10,
//            "grades_gap" => 1
        ];

        $poll = Poll::fromArray([
            'subject' => [
                'label' => $subject,
            ],
            'tally' => $tally,
            'proposals' => $proposals,
//            'grades' => array_map(function ($t, $i) {return "grade $i";}, $tally[0], range(0, count($tally[0])-1)),
        ]);

        $config = SvgConfig::sample()->setSidebarWidth(0);
        if (empty($subject)) {
            $config->setHeaderHeight(0);
        }
        $config->setWidth($img_w);
        $config->setHeight($img_h);
        $css = <<<CSS

.tally-watermark {
    text-shadow: 0 0 10px white;
    opacity: 1.0 !important;
}

CSS;
        $config->setCustomCss($css);

        try {
            if ('png' === $type) {
                $miprem = new PngGdRenderer($config);
            } else {
                $miprem = new SvgRenderer($config);
            }
            $img = $miprem->render($poll);
        } catch (Exception $e) {
            return $this->respondDemoUsage($type, $img_w, $img_h, "Miprem:".$e->getMessage());
        }

        $contentType = 'image/svg';
        if ('png' === $type) {
            $contentType = 'image/png';
        }
        return new Response($img, Response::HTTP_OK, [
            'Content-Disposition' => 'inline',
            'Content-Type' => $contentType,
        ]);
    }

    public function respondDemoUsage($type, $w, $h, $msg="")
    {
        // TODO: handle $type === 'png'

        $svg = <<<DEMOSVG
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="$w" height="$h">

<text y="20" class="error">$msg</text>
<text y="40">📖 Usage:</text>
<text y="60">merit-profile.svg?tally=11,4,5,3/7,4,10,2/7,5,8,3</text>
<text y="80">{tally} = {proposal A tally} / {proposal B tally} / …</text>
<text y="100">{proposal tally} = {worse grade count}, …, {best grade count}</text>
<text y="120"></text>
<text y="140">🛠 Settings</text>
<text y="160">merit-profile.svg?width=800&amp;height=600&amp;tally=…</text>
<text y="200">🔗 <a href="/render/merit-profile.svg?tally=11,4,5,3/7,4,10,2/7,5,8,3&amp;w=800&amp;h=600">Complete example</a></text>

</svg>
DEMOSVG;

        return Response::create($svg, Response::HTTP_OK, [
            'Content-Type' => 'text/svg',
            'Content-Disposition' => 'inline',
        ]);
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
