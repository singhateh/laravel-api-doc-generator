@include('api-doc-generator::errors.base', [
    'code' => 403,
    'title' => 'Access Denied',
    'message' => 'You are not authorized to access API documentation.',
    'icon' => 'fas fa-lock'
])
