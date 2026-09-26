@php($headline = 'Something went wrong inside Zazu.')
@php($messageText = 'The system could not complete that request. No technical details are shown here. The incident has a traceable request reference for troubleshooting.')
@php($code = '500')
@include('errors.layout')