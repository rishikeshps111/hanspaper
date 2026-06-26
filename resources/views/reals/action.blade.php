<div class="action-buttons d-flex gap-2">
     {{-- Manage Stock Button --}}
   <!-- <button type="button" class="btn btn-sm btn-warning manage-stock-btn" data-id="{{ $row->id }}"
        data-real="{{ $row->real_no }}" title="Manage Stock">
        <i class="bx bx-package"></i>
    </button>-->
  <!--  <button type="button" class="btn btn-sm btn-warning manage-stock-btn" data-id="{{ $row->id }}"
        data-real="{{ $row->real_no }}" title="Stock Report">
        <i class="bx bx-box"></i>-->

        <button type="button" class="btn btn-sm btn-primary manage-stock-btn" data-id="{{ $row->id }}"
        data-real="{{ $row->real_no }}" title="Manage Stock">
        <i class="bx bx-package"></i>
    </button>

         <a href="{{ route('reals.report',  ['id' => $row->id]) }}" class="btn btn-sm btn-warning" title="Stock Report">
        <i class="bx bx-box"></i>

    </a>

    </button>
    {{-- Edit Button --}}
    <a href="{{ route('reals.edit', $row->id) }}" class="btn btn-sm btn-primary" title="Edit">
        <i class="bx bx-edit"></i>
    </a>

    {{-- Delete Button --}}
    <form action="{{ route('reals.destroy', $row->id) }}" method="POST" class="delete-form" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete">
            <i class="bx bx-trash"></i>
        </button>
    </form>
</div>