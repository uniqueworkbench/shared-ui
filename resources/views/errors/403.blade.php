@include('shared-ui::errors._page', [
    'code' => 403,
    'heading' => 'Access denied',
    'message' => "You don't have permission to view this page. If you think this is a mistake, contact support.",
    'errorId' => $errorId ?? null,
])
