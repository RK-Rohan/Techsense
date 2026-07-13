<?php

namespace Tests\Unit;

use Dompdf\Dompdf;
use PHPUnit\Framework\TestCase;

class ProductDescriptionPdfHtmlTest extends TestCase
{
    public function test_it_preserves_supported_editor_formatting(): void
    {
        $html = '<p style="text-align: center; color: red"><strong>Title</strong></p>'
            .'<ul><li><em>Item</em></li></ul>'
            .'<table style="width: 900px"><tr><th colspan="2">Head</th></tr><tr><td>A</td><td>B</td></tr></table>';

        $result = sanitizeEditorHtmlForPdf($html);

        $this->assertStringContainsString('<p style="text-align: center"><strong>Title</strong></p>', $result);
        $this->assertStringContainsString('<ul><li><em>Item</em></li></ul>', $result);
        $this->assertStringContainsString('<table><tr><th colspan="2">Head</th></tr>', $result);
        $this->assertStringNotContainsString('color:', $result);
        $this->assertStringNotContainsString('900px', $result);
    }

    public function test_it_removes_unsafe_markup_and_attributes(): void
    {
        $html = '<script>alert(1)</script><p onclick="alert(2)">Safe '
            .'<a href="javascript:alert(3)" style="font-weight: bold; background-image: url(x)">link</a></p>';

        $result = sanitizeEditorHtmlForPdf($html);

        $this->assertStringNotContainsString('script', $result);
        $this->assertStringNotContainsString('alert(', $result);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringNotContainsString('background-image', $result);
        $this->assertStringContainsString('<a style="font-weight: bold">link</a>', $result);
    }

    public function test_it_keeps_line_breaks_in_legacy_plain_text_descriptions(): void
    {
        $result = sanitizeEditorHtmlForPdf("First line\nSecond & line");

        $this->assertSame("First line<br />\nSecond &amp; line", $result);
    }

    public function test_dompdf_renders_the_supported_formatting(): void
    {
        $description = sanitizeEditorHtmlForPdf(
            '<p><strong>Bold heading</strong></p>'
            .'<ol><li>First</li><li><u>Second</u></li></ol>'
            .'<table><thead><tr><th>Model</th><th>Value</th></tr></thead>'
            .'<tbody><tr><td>A-1</td><td style="text-align: right">25</td></tr></tbody></table>'
        );
        $html = '<style>'
            .'table{border-collapse:collapse;table-layout:fixed;width:100%}'
            .'th,td{border:1px solid #777;padding:2px 3px;vertical-align:top}'
            .'ol{margin:2px 0 3px 15px;padding:0}'
            .'</style><div style="width:240px;font-size:12px">'.$description.'</div>';

        $pdf = new Dompdf();
        $pdf->loadHtml($html);
        $pdf->render();
        $output = $pdf->output();

        $this->assertStringStartsWith('%PDF-', $output);
        $this->assertGreaterThan(1000, strlen($output));
        $this->assertSame(1, $pdf->getCanvas()->get_page_count());
    }
}
