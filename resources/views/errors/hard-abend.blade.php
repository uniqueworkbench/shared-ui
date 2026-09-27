@include('shared-ui::errors._page', [
    'code' => 500,
    'heading' => 'Something went wrong',
    'message' => "We've hit an unexpected error and our team has been notified. Please try again in a moment.",
    'errorId' => $errorId ?? null,
])
