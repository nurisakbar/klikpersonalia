@if($component->type == 'earning')
    <span class="badge badge-success">{{ $component->type_text }}</span>
@else
    <span class="badge badge-warning">{{ $component->type_text }}</span>
@endif
