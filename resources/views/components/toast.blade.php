@props(['message' => null])
@if ($message)
    <div role="status" {{ $attributes->class('bg-leaf px-5 py-2.5 text-sm font-bold text-white') }}>{{ $message }}</div>
@endif
