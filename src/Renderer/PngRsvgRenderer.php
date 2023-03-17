<?php

namespace App\Renderer;

use Miprem\Renderer\SvgRenderer;
use SVG\SVG;

/**
 * Provides accurate rasters.
 * Requires librsvg2-bin on Debian, librsvg on Alpine
 *
 * We may perhaps shave the shell_exec() call with https://github.com/gtkforphp/rsvg
 */
class PngRsvgRenderer extends SvgRenderer
{
    public function render(\Miprem\Model\Poll $poll, array $opt = []): string
    {
        $svg = SVG::fromString(parent::render($poll, $opt));
        $doc = $svg->getDocument();
        $doc->addFont(
            __DIR__ . '/../../vendor/roipoussiere/miprem-php/src/Renderer/DejaVuSans.ttf',
            null,
            'sans-serif'
        );

        $tmpSvg = tmpfile();
        fwrite($tmpSvg, $svg->toXMLString());
        fseek($tmpSvg, 0);
        $tmpSvgPath = stream_get_meta_data($tmpSvg)['uri'];

        $command = [
            "rsvg-convert",
            escapeshellarg($tmpSvgPath),
        ];
        $out = shell_exec(join(" ", $command));
        if ($out === false) {
            throw new \Exception("rsvg-convert failed (false)");
        }
        if ($out === null) {
            throw new \Exception("rsvg-convert failed (null)");
        }

        fclose($tmpSvg); // this removes the tmp file

        return $out;
    }

    public function getFileExtension() : string
    {
        return '.png';
    }
}