<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Illuminate\View\View;

class QrCodeController extends Controller
{
    public function show(): View
    {
        return view('admin.qr-code', ['visitorUrl' => route('visitors.create'), 'svg' => $this->svg()]);
    }

    public function download(): Response
    {
        return response($this->svg(), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="qr-code-visiteurs-tegv.svg"',
        ]);
    }

    public function poster(): View
    {
        return view('admin.poster', ['visitorUrl' => route('visitors.create'), 'svg' => $this->svg()]);
    }

    private function svg(): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'svgAddXmlHeader' => false,
            'drawLightModules' => false,
        ]);

        return (new QRCode($options))->render(route('visitors.create'));
    }
}
