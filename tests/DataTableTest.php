<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Illuminate\Support\Facades\Blade;

/** <x-data-table>: hidden columns, phone cards and the row's ⋯ menu */
class DataTableTest extends TestCase
{
    private function render(string $template): string
    {
        $this->app['session']->start();

        return Blade::render($template);
    }

    public function test_a_hidden_column_drops_out_but_its_cells_still_show_on_phone_cards(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-data-table sticky-first scroll>
                <x-slot name="head">
                    <x-data-table.th>Name</x-data-table.th>
                    <x-data-table.th hide="lg">Contact</x-data-table.th>
                </x-slot>
                <x-data-table.row>
                    <x-data-table.td primary>Ann Lee</x-data-table.td>
                    <x-data-table.td hide="lg" label="Contact">ann@example.com</x-data-table.td>
                </x-data-table.row>
            </x-data-table>
            BLADE);

        $this->assertStringContainsString('hidden lg:table-cell', $html);       // the heading
        $this->assertStringContainsString('block sm:hidden lg:table-cell', $html); // the cell: on the card, hidden until lg
        $this->assertStringContainsString('sm:max-h-[calc(100vh-8rem)]', $html);
        $this->assertStringContainsString('sm:[&_tr>*:first-child]:sticky', $html);
        // The label is for the phone card; the primary cell has none
        $this->assertSame(1, substr_count($html, 'sm:hidden text-xs'));
    }

    public function test_menu_items_are_links_forms_with_a_method_and_a_question_or_disabled(): void
    {
        $html = $this->render(<<<'BLADE'
            <x-data-table.actions label="Actions for Ann">
                <x-data-table.action href="/messages/1" icon="fa-regular fa-comment">Message</x-data-table.action>
                <x-data-table.action action="/users/1" method="DELETE" confirm="Remove Ann's login?" danger>Remove</x-data-table.action>
                <x-data-table.action disabled="No phone number on file">Text</x-data-table.action>
            </x-data-table.actions>
            BLADE);

        $this->assertStringContainsString('aria-label="Actions for Ann"', $html);
        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('href="/messages/1"', $html);
        $this->assertStringContainsString('action="/users/1"', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString("confirm-action", $html);
        $this->assertStringContainsString("Ann\x5Cu0027s login?", $html); // the question, its quote escaped for the attribute
        $this->assertStringContainsString('aria-disabled="true" title="No phone number on file"', $html);
    }
}
