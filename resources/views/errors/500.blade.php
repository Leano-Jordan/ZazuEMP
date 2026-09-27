@include('errors.layout', [
    'requestId' => $requestId ?? request()->attributes->get('zazu_request_id'),
    'code' => $code ?? 'APP-001',
    'headline' => $headline ?? 'Zazu could not complete that request.',
    'messageText' => $messageText ?? 'We could not complete your request. Please try again, and use the reference below if the problem continues.',
])
