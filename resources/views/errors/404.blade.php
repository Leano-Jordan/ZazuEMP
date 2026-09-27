@include('errors.layout', [
    'requestId' => $requestId ?? request()->attributes->get('zazu_request_id'),
    'code' => 'ROUTE-001',
    'headline' => 'We could not find that page.',
    'messageText' => 'The page may have moved, the record may no longer exist, or it may belong to another business workspace.',
])
