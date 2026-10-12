<?php

namespace Tests\Feature;

use App\Http\Controllers\MushakBookController;
use App\Http\Controllers\MushakInvoiceController;
use App\Http\Controllers\SellPosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SalesDocumentPermissionsTest extends TestCase
{
    public function test_view_only_book_actions_hide_edit_and_delete()
    {
        Gate::shouldReceive('check')->andReturnUsing(fn ($permission) => str_ends_with($permission, '.view'));
        foreach (['6-1', '6-2'] as $type) {
            $html = view('mushak.books.actions', ['type' => $type, 'book' => (object) ['id' => 1]])->render();
            $this->assertStringContainsString('View PDF', $html);
            $this->assertStringNotContainsString('> Edit</a>', $html);
            $this->assertStringNotContainsString('delete_mushak', $html);
        }
    }

    public function test_role_form_renders_all_document_permissions_and_saved_selection()
    {
        $html = view('role.partials.sales_document_permissions', ['role_permissions' => ['mushak_6_3.view']])->render();
        foreach (['mushak_6_1', 'mushak_6_2', 'mushak_6_3'] as $prefix) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                $this->assertStringContainsString('value="'.$prefix.'.'.$action.'"', $html);
            }
        }
        $this->assertStringContainsString('value="invoice_download.view"', $html);
        $this->assertStringContainsString('value="delivery_challan.view"', $html);
        $this->assertMatchesRegularExpression('/<input(?=[^>]*value="mushak_6_3.view")(?=[^>]*checked)[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input(?=[^>]*value="mushak_6_3.delete")(?=[^>]*checked)[^>]*>/', $html);
    }

    /** @dataProvider protectedActions */
    public function test_each_action_rejects_missing_permission_before_accessing_data($class, $method, $arguments, $permission)
    {
        // Legacy print/sales permissions must not bypass the new controls.
        $user = \Mockery::mock(\App\User::class);
        $user->shouldReceive('can')->with($permission)->once()->andReturn(false);
        Auth::shouldReceive('user')->andReturn($user);
        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $arguments = array_map(fn ($arg) => $arg === 'request' ? new Request() : $arg, $arguments);

        try {
            $controller->$method(...$arguments);
            $this->fail('An unauthorized action must not proceed.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public static function protectedActions()
    {
        $cases = [];
        foreach (['6-1', '6-2'] as $type) {
            foreach (['index' => 'view', 'pdf' => 'view', 'create' => 'create', 'store' => 'create', 'edit' => 'update', 'update' => 'update', 'destroy' => 'delete'] as $method => $action) {
                $args = ['request', $type];
                if (in_array($method, ['pdf', 'edit', 'update', 'destroy'])) {
                    $args[] = 1;
                }
                $cases[$type.' '.$method] = [MushakBookController::class, $method, $args, 'mushak_'.str_replace('-', '_', $type).'.'.$action];
            }
        }
        foreach (['index' => 'view', 'create' => 'create', 'store' => 'create', 'edit' => 'update', 'update' => 'update', 'destroy' => 'delete'] as $method => $action) {
            $args = in_array($method, ['store', 'update']) ? ['request'] : [];
            if (in_array($method, ['edit', 'update', 'destroy'])) {
                $args[] = 1;
            }
            $cases['6-3 '.$method] = [MushakInvoiceController::class, $method, $args, 'mushak_6_3.'.$action];
        }
        foreach (['downloadInvoicePdf' => 'invoice_download.view', 'downloadPackingListPdf' => 'delivery_challan.view', 'downloadMushak63Pdf' => 'mushak_6_3.view'] as $method => $permission) {
            $cases[$method] = [SellPosController::class, $method, [1], $permission];
        }
        return $cases;
    }
}
