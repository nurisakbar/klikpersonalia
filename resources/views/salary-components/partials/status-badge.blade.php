@if($component->is_active)
    <span class="badge badge-success">{{ $component->status_text }}</span>
@else
    <span class="badge badge-danger">{{ $component->status_text }}</span>
@endif
