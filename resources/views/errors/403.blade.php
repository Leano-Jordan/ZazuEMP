@php($headline = 'That action is not available to you.')
@php($messageText = 'Your account or workspace role does not have permission to perform this action. Your business data has not been exposed.')
@php($code = '403')
@include('errors.layout')