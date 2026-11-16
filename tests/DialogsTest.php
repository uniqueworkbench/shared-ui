<?php

namespace UniqueWorkbench\SharedUi\Tests;

/** No native browser dialogs: confirms, alerts and prompts are the package's own modals (workbench/dialogs, the *-modal components) */
class DialogsTest extends TestCase
{
    private function views(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../resources/views', \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (str_ends_with($file->getFilename(), '.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function test_no_view_calls_the_browsers_own_alert_confirm_or_prompt(): void
    {
        foreach ($this->views() as $path) {
            $source = file_get_contents($path);
            // Arguments to the call itself (the docs and Alpine's own confirm() methods have none)
            $this->assertDoesNotMatchRegularExpression(
                '/return\s+confirm\(|window\.(alert|confirm|prompt)\s*\(|(?<![\w.$])(alert|prompt)\((?!\))|(?<![\w.$])confirm\((?!\))/',
                $source,
                basename($path) . ' uses a native browser dialog: use data-confirm, uwConfirm, uwAlert or uwPrompt',
            );
        }
    }

    public function test_the_layouts_include_the_dialogs(): void
    {
        $this->assertStringContainsString("workbench.dialogs", file_get_contents(__DIR__ . '/../resources/views/components/workbench-layout.blade.php'));
        $this->assertStringContainsString("workbench.dialogs", file_get_contents(__DIR__ . '/../resources/views/layouts/app.blade.php'));
    }

    public function test_the_dialogs_give_scripts_promises_and_forms_data_confirm(): void
    {
        $html = view('shared-ui::workbench.dialogs')->render();

        foreach (['window.uwConfirm', 'window.uwAlert', 'window.uwPrompt', "'submit'", 'data-confirm', 'confirm-action', 'alert-action', 'input-action'] as $needle) {
            $this->assertStringContainsString($needle, str_replace('dataset.confirm', 'data-confirm', $html));
        }
        // The modals themselves
        $this->assertStringContainsString('@alert-action.window', $html);
        $this->assertStringContainsString('@input-action.window', $html);
    }

    public function test_the_confirm_modal_takes_a_button_label_and_tells_when_it_is_cancelled(): void
    {
        $html = view('shared-ui::components.confirm-modal')->render();

        $this->assertStringContainsString('confirmLabel', $html);
        $this->assertStringContainsString('onCancel', $html);
    }
}
