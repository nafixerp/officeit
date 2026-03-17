@props([
    'id' => 'deleteModal',
    'title' => 'Confirm Deletion',
    'message' => 'Are you sure you want to delete this record? This action cannot be undone.',
    'action' => '#',
    'method' => 'DELETE',
    'buttonText' => 'Delete',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="{{ $id }}Label">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>{{ $title }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $message }}</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <form id="{{ $id }}Form" action="{{ $action }}" method="POST" class="d-inline">
                    @csrf
                    @method($method)
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> {{ $buttonText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@once
@push('modals')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('{{ $id }}');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (button) {
                    const action = button.getAttribute('data-action');
                    if (action) {
                        document.getElementById('{{ $id }}Form').setAttribute('action', action);
                    }
                    const itemName = button.getAttribute('data-name');
                    if (itemName) {
                        modal.querySelector('.modal-body p').textContent =
                            'Are you sure you want to delete "' + itemName + '"? This action cannot be undone.';
                    }
                }
            });
        }
    });
</script>
@endpush
@endonce
