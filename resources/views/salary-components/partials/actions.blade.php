<div class="btn-group" role="group">
    <a href="{{ route('salary-components.show', $component->id) }}" 
       class="btn btn-sm btn-info" 
       title="Lihat Detail">
        <i class="fas fa-eye"></i>
    </a>
    
    <a href="{{ route('salary-components.edit', $component->id) }}" 
       class="btn btn-sm btn-warning" 
       title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    
    <form action="{{ route('salary-components.toggle-status', $component->id) }}" 
          method="POST" 
          class="d-inline" 
          onsubmit="return confirm('Apakah Anda yakin ingin mengubah status komponen ini?')">
        @csrf
        <button type="submit" 
                class="btn btn-sm {{ $component->is_active ? 'btn-warning' : 'btn-success' }}" 
                title="{{ $component->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
            <i class="fas {{ $component->is_active ? 'fa-times' : 'fa-check' }}"></i>
        </button>
    </form>
    
    <form action="{{ route('salary-components.destroy', $component->id) }}" 
          method="POST" 
          class="d-inline" 
          onsubmit="return confirm('Apakah Anda yakin ingin menghapus komponen ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="btn btn-sm btn-danger" 
                title="Hapus">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
