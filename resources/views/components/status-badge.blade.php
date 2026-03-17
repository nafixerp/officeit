@props([
    'status' => 'DRAFT',
])

@php
    $status = strtoupper($status);

    $styles = [
        'DRAFT'      => 'bg-secondary bg-opacity-10 text-secondary',
        'PENDING'    => 'bg-warning bg-opacity-10 text-warning',
        'APPROVED'   => 'bg-info bg-opacity-10 text-info',
        'CONFIRMED'  => 'bg-info bg-opacity-10 text-info',
        'PROCESSING' => 'bg-primary bg-opacity-10 text-primary',
        'COMPLETED'  => 'bg-success bg-opacity-10 text-success',
        'PAID'       => 'bg-success bg-opacity-10 text-success',
        'PARTIAL'    => 'bg-warning bg-opacity-10 text-warning',
        'OVERDUE'    => 'bg-danger bg-opacity-10 text-danger',
        'CANCELLED'  => 'bg-danger bg-opacity-10 text-danger',
        'REJECTED'   => 'bg-danger bg-opacity-10 text-danger',
        'CLOSED'     => 'bg-dark bg-opacity-10 text-dark',
        'ACTIVE'     => 'bg-success bg-opacity-10 text-success',
        'INACTIVE'   => 'bg-secondary bg-opacity-10 text-secondary',
    ];

    $badgeClass = $styles[$status] ?? 'bg-secondary bg-opacity-10 text-secondary';
@endphp

<span {{ $attributes->merge(['class' => 'badge rounded-pill fw-semibold ' . $badgeClass]) }} style="font-size: 0.75rem; padding: 0.35em 0.75em;">
    {{ ucfirst(strtolower($status)) }}
</span>
