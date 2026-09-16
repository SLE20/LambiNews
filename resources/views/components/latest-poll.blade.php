@include('front.polls._styles')
@include('front.polls._poll', [
    'poll'     => $poll,
    'recorder' => $recorder,
    'compact'  => $compact,
])
