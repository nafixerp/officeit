@props([
    'type' => 'info',
    'dismissible' => true,
    'icon' => null,
])

@php
    $icons = [
        'success' => 'fas fa-check-circle',
        'danger'  => 'fas fa-times-circle',
        'error'   => 'fas fa-times-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'info'    => 'fas fa-info-circle',
    ];

    $alertType = $type === 'error' ? 'danger' : $type;
    $alertIcon = $icon ?? ($icons[$type] ?? $icons['info']);
@endphp

<div {{ $attributes->merge(['class' => 'alert alert-' . $alertType . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    <i class="{{ $alertIcon }} me-2"></i>
    {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
