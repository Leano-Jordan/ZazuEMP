@php($headline = 'We could not find that page.')
@php($messageText = 'The page may have moved, the record may no longer exist, or it may belong to another business workspace.')
@php($code = '404')
@include('errors.layout', ['requestId' => $requestId ?? null])
