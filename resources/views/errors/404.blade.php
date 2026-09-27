@include('shared-ui::errors._page', [
    'code' => 404,
    'heading' => 'Page not found',
    'message' => "The page you're looking for doesn't exist or may have moved.",
    'errorId' => $errorId ?? null,
])
