@include('errors.generic', [
    'status' => 500,
    'title' => __('Server error'),
    'message' => __('A server error occurred. Please try again.'),
])
